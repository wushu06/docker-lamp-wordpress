<?php

namespace cspImportExport\cspImport;

include_once(plugin_dir_path(__FILE__) . 'class-wdm-abstract-process-csv-batches.php');

class WdmProcessRoleSpecificCSVBatches extends WdmAbstractProcessCSVBatches
{

    private $fetchedUsers = array();

    public function __construct()
    {
        add_action('wp_ajax_import_role_specific_file', array($this, 'processBatch'));
    }

    protected function processRow($productId, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt)
    {

        extract($this->rowStatusMessages);

        global $wpdb, $subruleManager, $ruleManager;

        $wdmRoleMapping  = $wpdb->prefix . 'wusp_role_pricing_mapping';

        $updatePrice = 0;
        $role = strtolower(trim($rowData[ 1 ]));
        $minQty = trim($rowData[ 2 ]);

        //check all values valid or not
        if ($flag !== false && $priceType !== false) {
            //check if product exists or not
            if ($this->isValidProduct($productId)) {
                $minQty = (int) $minQty;
                // Check if Role exists
                if ($GLOBALS['wp_roles']->is_role($role)) {
                    //Update price for existing one
                    $result = $wpdb->get_row($wpdb->prepare("SELECT * from {$wdmRoleMapping} WHERE role = '%s' AND product_id = %d AND min_qty = %d", $role, intval($productId), $minQty));
                    if ($result != null) {
                        if (($result->batch_numbers <= $batchNumber) &&
                                ($price != $result->price || $priceType != $result->flat_or_discount_price)
                            ) {
                            $updatePrice = $wpdb->update(
                                $wdmRoleMapping,
                                array(
                                'price' => $price,
                                'flat_or_discount_price' => $priceType,
                                'batch_numbers' => $batchNumber,
                                ),
                                array(
                                'id' => $result->id,
                                ),
                                array(
                                '%f',
                                '%d',
                                '%d',
                                ),
                                array(
                                '%d',
                                )
                            );
                            if ($updatePrice != 0) {
                                $status = $recordUpdated;
                                $subruleManager->deactivateSubrulesOfRoleForProduct($productId, $role, $minQty);
                                $updateCnt ++;
                            } else {
                                $status = $recordExists;
                                $skipCnt ++;
                            }
                        } elseif ($result->batch_numbers > $batchNumber) {
                            $status = $recordSkipped;
                            $skipCnt ++;
                        } else {
                            $status = $recordExists;
                            $skipCnt ++;
                        }
                    } else {
                        //add entry in our table
                        if ($wpdb->insert(
                            $wdmRoleMapping,
                            array(
                            'product_id' => $productId,
                            'role'   => $role,
                            'price' => $price,
                            'flat_or_discount_price' => $priceType,
                            'batch_numbers' => $batchNumber,
                            'min_qty'   => $minQty,
                            ),
                            array(
                            '%d',
                            '%s',
                            '%s',
                            '%d',
                            '%d'
                            )
                        )) { //if record inserted
                            $status = $recordInserted;
                            $insertCnt ++;
                        } else {
                            $status = $couldNotInsert;
                            $skipCnt ++;
                        }
                    }
                } else {
                    $status = $roleDoesNotExist;
                    $skipCnt ++;
                }
            } else {
                $status = $productDoesNotExist;
                $skipCnt ++;
            }
        } else {
            $status = $invalidFieldValues;
        }

        return array(
            'product_id'    =>  $productId,
            'applicable_entity'     =>  $role,
            'min_qty'   => $minQty,
            'active_price'  =>  ($priceType == 2) ? $price . __('% of regular price', CSP_TD) : wc_price($price),
            'discount_type' =>  $this->discountOptions[ $priceType ],
            'record_status' =>  $status,
            'counts'    =>  array(
                'skip_cnt'      =>  $skipCnt,
                'insert_cnt'    =>  $insertCnt,
                'update_cnt'    =>  $updateCnt,
            ),
        );
    }
}

new WdmProcessRoleSpecificCSVBatches();
