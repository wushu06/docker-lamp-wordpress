<?php

namespace cspImportExport\cspImport;

include_once(plugin_dir_path(__FILE__) . 'class-wdm-abstract-process-csv-batches.php');

class WdmProcessGroupSpecificCSVBatches extends WdmAbstractProcessCSVBatches
{

    private $fetchedUsers = array();

    public function __construct()
    {
        add_action('wp_ajax_import_group_specific_file', array($this, 'processBatch'));
    }

    protected function processRow($productId, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt)
    {

        extract($this->rowStatusMessages);

        global $wpdb, $subruleManager, $ruleManager;

        $wdmGroupMapping  = $wpdb->prefix . 'wusp_group_product_price_mapping';
        $wdmGroupsGroup = $wpdb->prefix . 'groups_group';

        $updatePrice = 0;
        $group = strtolower(trim($rowData[ 1 ]));
        $minQty = trim($rowData[ 2 ]);

        //check all values valid or not
        if ($flag !== false && $priceType !== false) {
            $minQty = (int) $minQty;
            //check if product exists or not
            if ($this->isValidProduct($productId)) {
                $groupId = $wpdb->get_var($wpdb->prepare("SELECT group_id FROM {$wdmGroupsGroup} WHERE name = %s", $group));

                // Check if Role exists
                if ($groupId !== null) {
                    //Update price for existing one
                    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wdmGroupMapping} WHERE group_id = %d AND product_id = %d AND min_qty = %d", $groupId, intval($productId), $minQty));

                    if ($result !== null) {
                        if (($result->batch_numbers <= $batchNumber) &&
                                ($price != $result->price || $priceType != $result->flat_or_discount_price)
                            ) {
                            $updatePrice = $wpdb->update(
                                $wdmGroupMapping,
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
                                $subruleManager->deactivateSubrulesOfGroupForProduct($productId, $groupId, $minQty);
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
                            $wdmGroupMapping,
                            array(
                            'product_id' => $productId,
                            'group_id'   => $groupId,
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
                    $status = $groupDoesNotExist;
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
            'applicable_entity'     =>  $group,
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

new WdmProcessGroupSpecificCSVBatches();
