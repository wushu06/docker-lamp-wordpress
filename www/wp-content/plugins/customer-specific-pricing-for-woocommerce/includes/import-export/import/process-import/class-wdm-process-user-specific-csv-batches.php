<?php

namespace cspImportExport\cspImport;

include_once(plugin_dir_path(__FILE__) . 'class-wdm-abstract-process-csv-batches.php');

class WdmProcessUserSpecificCSVBatches extends WdmAbstractProcessCSVBatches
{

    private $fetchedUsers = array();

    public function __construct()
    {
        add_action('wp_ajax_import_customer_specific_file', array($this, 'processBatch'));
    }

    protected function processRow($productId, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt)
    {

        extract($this->rowStatusMessages);

        global $wpdb, $subruleManager, $ruleManager;

        $wdmUserMapping  = $wpdb->prefix . 'wusp_user_pricing_mapping';
        $wdmUsers        = $wpdb->prefix . 'users';

        $updatePrice = 0;
        $user = trim($rowData[ 1 ]);
        $minQty = trim($rowData[ 2 ]);

        //check all values valid or not
        if ($flag !== false && $priceType !== false) {
            $minQty = (int) $minQty;
            //check if product exists or not
            if ($this->isValidProduct($productId)) {
                if (!isset($this->fetchedUsers[$user])) {
                    //get user id
                    $this->fetchedUsers[$user] = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wdmUsers} where user_login=%s", $user));
                }

                $getUserId = $this->fetchedUsers[$user];

                if ($getUserId == null) {
                     $status = $userDoesNotExist;
                     $skipCnt ++;
                } else {
                    //Update price for existing one
                    $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wdmUserMapping} where product_id=%d and user_id=%d and min_qty=%d", $productId, $getUserId, $minQty));
                    if ($result != null) {
                        if (($result->batch_numbers <= $batchNumber) &&
                                ($price != $result->price || $priceType != $result->flat_or_discount_price)
                            ) {
                            $updatePrice = $wpdb->update(
                                $wdmUserMapping,
                                array(
                                'price' => $price,
                                'flat_or_discount_price' => $priceType,
                                'batch_numbers' => $batchNumber,
                                ),
                                array(
                                'id'    =>  $result->id,
                                ),
                                array(
                                '%f',
                                '%d',
                                '%d'
                                ),
                                array(
                                '%d',
                                )
                            );
                            if ($updatePrice != 0) {
                                $status = $recordUpdated;
                                $subruleManager->deactivateSubrulesOfCustomerForProduct($productId, $getUserId, $minQty);
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
                            $wdmUserMapping,
                            array(
                            'product_id' => $productId,
                            'user_id'   => $getUserId,
                            'price' => $price,
                            'flat_or_discount_price' => $priceType,
                            'batch_numbers' => $batchNumber,
                            'min_qty'   => $minQty,
                            ),
                            array(
                            '%d',
                            '%d',
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
            'applicable_entity'     =>  $user,
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

new WdmProcessUserSpecificCSVBatches();
