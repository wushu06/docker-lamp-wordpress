<?php

namespace cspImportExport\cspImport;

abstract class WdmAbstractProcessCSVBatches
{

    public $discountOptions = array();

    public $rowStatusMessages = array();

    /**
     * Initiates Batch Processing. It does basic validations and if validations are passed, reads batch row by row and processes the data.
     * After processing the batch completely, it returns the json array, so that status can be shown on the screen.
     */
    public function processBatch()
    {

        ini_set('max_execution_time', 0);
        wp_suspend_cache_addition(true);

        $nonce = $_REQUEST['_wp_import_nonce'];
        $nonce_verification = wp_verify_nonce($nonce, 'import_nonce');

        global $wpdb, $ruleManager;

        //Override nonce verification for extending import functionality in any third party extension
        $nonce_verification = apply_filters('csp_import_nonce_verification', $nonce_verification);
        if (! $nonce_verification) {
             echo "Security Check";
             exit;
        } else {
            $maxBatchesallowed = apply_filters('csp_max_concurrent_batches_allowed', 2);
            if (absint($maxBatchesallowed) == 0) {
                $maxBatchesallowed = 2;
            }
            while (true) {
                $numberOfBatchesInProgress = absint(get_option('csp_batches_in_progress', 0));
                if ($numberOfBatchesInProgress > $maxBatchesallowed) {
                    continue;
                } else {
                    update_option('csp_batches_in_progress', $numberOfBatchesInProgress + 1);
                    break;
                }
            }

            //Allow only admin to import csv files
            $capabilityToImport = apply_filters('csp_import_allowed_user_capability', 'manage_options');
            $can_user_import = apply_filters('csp_can_user_import_csv', current_user_can($capabilityToImport));

            if (!$can_user_import) {
                echo "Security Check";
                update_option('csp_batches_in_progress', $numberOfBatchesInProgress - 1);
                exit;
            }


            $upload = wp_upload_dir();
            $batchDir = $upload['basedir'] . '/importCsv';
            $csvFile = "";

            if (isset($_POST['file_name']) && file_exists($batchDir. "/" .$_POST['file_name'])) {
                $csvFile = $batchDir. "/" .$_POST['file_name'];
            } else {
                echo 'Invalid File';
                update_option('csp_batches_in_progress', $numberOfBatchesInProgress - 1);
                exit();
            }

            $batchNumber = 0;

            if (isset($_POST['batch_number']) && absint($_POST['batch_number']) > 0) {
                  $batchNumber = $_POST['batch_number'];
            } else {
                  echo 'Invalid Batch Number';
                  update_option('csp_batches_in_progress', $numberOfBatchesInProgress - 1);
                  exit();
            }

            $this->discountOptions = array(
                "0"=>"-",
                "1"=>__('Flat', CSP_TD),
                "2"=>"%"
            );

            $this->rowStatusMessages = array(
                'invalidFieldValues'    => __('Flat Price or % or Min Qty Invalid', CSP_TD),
                'productDoesNotExist'   => __('Invalid Product Id', CSP_TD),
                'couldNotInsert'        => __('Record could not be inserted', CSP_TD),
                'recordInserted'        => __('Record Inserted', CSP_TD),
                'recordUpdated'         => __('Record Updated', CSP_TD),
                'recordExists'          => __('Record already exists', CSP_TD),
                'userDoesNotExist'      =>  __('User does not exist', CSP_TD),
                'recordSkipped'         => __('Record Skipped', CSP_TD),
                'roleDoesNotExist'      => __('Role does not exist', CSP_TD),
                'groupDoesNotExist'     => __('Group does not exist', CSP_TD),
            );

            if (false !== ($getfile = fopen($csvFile, 'r') )) {
                $updateCnt  = 0;
                $insertCnt  = 0;
                $skipCnt    = 0;
                $count=0;
                $responseData = array();
                $recordsRead = array();
                while (false !== ($data      = fgetcsv($getfile, 0, ','))) {
                    $count++;
                    $result                  = $data;
                    $str                     = implode(',', $result);
                    $rowData                 = array_map('trim', explode(',', $str));
                    $columnCount             = count($rowData);
                    $productId              = (int) $rowData[ 0 ];
                    $minQty                 = $rowData[ 2 ];
                    $flatPrice              = $rowData[ 3 ];
                    $percentPrice          = isset($rowData[ 4 ]) ? $rowData[ 4 ] : 0;
                    $price  = 0;
                    $status = null;
                    
                    $flag = $this->shouldRowBeProcessed($flatPrice, $percentPrice, $minQty);

                    if ($flag === false) {
                        $skipCnt++;
                        
                        $status = $this->rowStatusMessages['invalidFieldValues'];
                    }

                    $priceType = $this->selectFlatPriceOrDiscount($flatPrice, $percentPrice);

                    if ($priceType == 1) {
                        $price = $flatPrice;
                    }

                    if ($priceType == 2) {
                        $price = $percentPrice;
                    }

                    $recordsRead[$count] = $this->processRow($productId, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt);

                    $skipCnt = $recordsRead[$count]['counts']['skip_cnt'];
                    $insertCnt = $recordsRead[$count]['counts']['insert_cnt'];
                    $updateCnt = $recordsRead[$count]['counts']['update_cnt'];

                    unset($recordsRead[$count]['skip_count']['counts']);

                    $wpdb->flush();
                }//end of while

                fclose($getfile);
                unlink($csvFile);

                $ruleManager->setUnusedRulesAsInactive();
                
                $responseData['records'] = $recordsRead;
                $responseData['rows_read'] = $count;
                $responseData['insert_cnt'] = $insertCnt;
                $responseData['update_cnt'] = $updateCnt;
                $responseData['skip_cnt'] = $skipCnt;

                echo json_encode($responseData);
                die();
            }
        }
    }

    /**
     * Reads the row data. Checks in the database if current row pair already exists. If it does not exist, then adds that pair in database.
     * @param  int $productId       Product Id of row
     * @param  boolean $flag        if flag is false, row is ommitted
     * @param  mix $price           This can either be integer or float. It is a flat or percentage price to be applied
     * @param  int $priceType       if 1, then it is a flat price. If 2, then it is a percentage discount. If false, row is ommitted
     * @param  string $status       Row status
     * @param  int $batchNumber     Current Batch Number
     * @param  array $rowData       Current row data
     * @param  int $skipCnt         Number of rows skipped till now
     * @param  int $updateCnt       Number of rows updated till now
     * @param  int $insertCnt       Number of rows inserted till now
     * @return array                Complete information about the row after proceesing.
     */
    abstract protected function processRow($productId, $flag, $price, $priceType, $status, $batchNumber, $rowData, $skipCnt, $updateCnt, $insertCnt);

    /**
     * Decides whether csv row should be processed or not
     *
     * @param  float $flatPrice    Flat Price mentioned in CSV row
     * @param  float $percentPrice Percentage discount mentioned in CSV row
     * @param  int $minQty       Min Quantity associated with row
     * @return boolean               If Flat price, Percentage (Discount) Price and Min Qty values are valid, return true. Else returns false
     */
    protected function shouldRowBeProcessed($flatPrice, $percentPrice, $minQty)
    {
                
        $priceType = static::selectFlatPriceOrDiscount($flatPrice, $percentPrice);

        if ($priceType === false) {
            return false;
        }

        if (empty($minQty)) {
            return false;
        }

        if (!is_numeric($minQty)) {
            return false;
        }

        if (is_float($minQty + 0)) {
            return false;
        }

        $minQty = (int) $minQty;

        if ($minQty > 0) {
            return true;
        }

        return false;
    }

    protected function selectFlatPriceOrDiscount($flatPrice, $percentPrice)
    {

        if (empty($flatPrice) && empty($percentPrice)) {
            return false;
        }

        if (!empty($flatPrice)) {
            if (is_numeric($flatPrice) && $flatPrice >= 0) {
                return 1;
            }
        }

        if (!empty($percentPrice)) {
            if (is_numeric($percentPrice)) {
                if ($percentPrice >= 0 && $percentPrice <= 100) {
                    return 2;
                }
            }
        }

        return false;
    }

    protected function isValidProduct($productId)
    {
        $productObject = wc_get_product($productId);
        if ($productObject !== false) {
            $productType = $productObject->get_type();
            if ($productType == 'simple' || $productType == 'variation') {
                return true;
            }
        }
        return false;
    }
}
