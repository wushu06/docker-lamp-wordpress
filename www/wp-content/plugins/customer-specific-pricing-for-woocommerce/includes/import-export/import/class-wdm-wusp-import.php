<?php

namespace cspImportExport\cspImport;

/**
 * check if class already exists
 *  @author WisdmLabs
 */
if (!class_exists('WdmWuspImport')) {
    /**
     * Provides import option for importing customer specific,role specific and group specific csv files
     */
    class WdmWuspImport
    {

        //  @var array save the optins value pairs
        private $_class_value_pairs = array();

        /**
         * call function for display import form
         */
        public function __construct()
        {
            add_action('show_import', array($this, 'wdmShowImportOptions'));
        }

        public static function showImportNotices()
        {
            $importNoticeDismissalFlag = get_option('csp_import_notice_dismissed', 0);
            if ($importNoticeDismissalFlag != 1) {
                ?>
                <div class="notice error wusp-import-notice is-dismissible" >
                    <p><?php _e('We have changed the CSV Import Format. Please download the sample csv below to know the new format and make appropriate changes in your CSV.', CSP_TD); ?></p>
                </div>
                <?php
            }
        }
        /**
         * Set the option value pairs
         * @param array $class_value_pairs
         */
        public function setOptionValuesPair($class_value_pairs)
        {
            $this->_class_value_pairs = $class_value_pairs;
        }

        private function enqueueScripts()
        {
            wp_enqueue_style('wdm_csp_import_css', plugins_url('/css/import-css/wdm-csp-import.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_style('bootstrap_fileinput_css', plugins_url('/css/import-css/fileinput.min.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_script('bootstrap_fileinput_js', plugins_url('/js/import-js/fileinput.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION, true);
            wp_enqueue_style('bootstrap_css', plugins_url('/css/import-css/bootstrap.css', dirname(dirname(dirname(__FILE__)))), array(), CSP_VERSION);
            wp_enqueue_script('bootstrap_js', plugins_url('/js/import-js/bootstrap.min.js', dirname(dirname(dirname(__FILE__)))), array('jquery'), CSP_VERSION, true);

            wp_localize_script(
                'bootstrap_fileinput_js',
                'wdm_csp_translation',
                array(
                    'removeLabel' => __('Remove', CSP_TD),
                    'removeTitle' => __('Clear selected files', CSP_TD),
                    'browseLabel' => sprintf(__('Browse %s', CSP_TD), '&hellip;'),
                    'uploadLabel' => __('Upload', CSP_TD),
                    'uploadTitle' => __('Upload selected files', CSP_TD),
                    'msgSizeTooLarge' => sprintf(__('File "%s" %s exceeds maximum allowed upload size of %s. Please retry your upload!', CSP_TD), '{name}', '(<b>{size} KB</b>)', '<b>{maxSize} KB</b>'),
                    'msgFilesTooLess' => sprintf(__('You must select at least %s to upload. Please retry your upload!', CSP_TD), '<b>{n}</b> {files}'),
                    'msgFileNotFound' => sprintf(__('File "%s" not found!', CSP_TD), '{name}'),
                    'msgFileSecured' => sprintf(__('Security restrictions prevent reading the file "%s".', CSP_TD), '{name}'),
                    'msgFileNotReadable' => sprintf(__('File "%s" is not readable.', CSP_TD), '{name}'),
                    'msgInvalidFileType' => sprintf(__('Invalid type for file "%s". Only "%s" files are supported.', CSP_TD), '{name}', '{types}'),
                    'msgInvalidFileExtension'=> sprintf(__('Invalid extension for file "%s". Only "%s" files are supported.', CSP_TD), '{name}', '{extensions}'),
                    'msgValidationError' => __('File Upload Error', CSP_TD),
                    'dropZoneTitle' => sprintf(__('Drag & drop files here %s', CSP_TD), '&hellip;'),
                    'msgSelected' => sprintf(__('%s selected', CSP_TD), '{n} {files}'),
                )
            );
        }

        /**
         * display import form
         * @global type $post
         */
        public function wdmShowImportOptions()
        {
            $this->enqueueScripts();

            ?>
            <div class="wrap"><h3 class="import-export-header import-header"><?php _e('CSP Import', CSP_TD) ?></h3>

                <div class="update-nag wdm-import-info">

                    <?php echo sprintf(__('If the customer specific price already exists, the existing values will be overwritten by the new values.<br/>Download <span class="import-type">%s</span> import template', CSP_TD), __('User Specific Sample', CSP_TD)) ?>
                     <a class='sample-csv-import-template-link' href=
                     "<?php echo plugins_url('/templates/user_specific_pricing_sample.csv', dirname(dirname(dirname(__FILE__)))); ?>"><?php _e('here', CSP_TD) ?>.</a>

                </div>
                  <div id='wdm_message' class='updated hidePrev'><p class="wdm_message_p"></p></div>
            </div>

            <div id='wdm_import_form'>
                <form name="import_form" class="wdm_import_form" method="POST" enctype="multipart/form-data">
                <?php wp_nonce_field('import_upload_nonce'); ?>
                    <div class="wdm-input-group">
                                <label for="dd_show_import_options"><?php _e('Select Import Type :', CSP_TD) ?> </label>
                                <select name="dd_show_import_options" id="dd_show_import_options">
                                    <?php
                                    foreach ($this->_class_value_pairs as $key => $val) {
                                        echo '<option value=' . $key . '>' . $val . '</option>';
                                    }
                                    ?>
                                </select>
                    </div>

                    <input type="file" name="csv" id="csv" class="file" accept=".csv" data-show-preview="false" data-show-upload="false" required title="<?php _e('Select File', CSP_TD); ?>">
                    <div class="wdm-input-group">
                        <input type="submit" id="wdm_import" name="wdm_import_csp" class="button button-primary" value="<?php _e('Import', CSP_TD) ?>">
                    </div>
                </form>
            </div>
            <div id="wdm_import_data">
                <?php
                if (! empty($_POST)) {
                    $this->createBatches();
                }
                ?>
            </div>
            <?php
        }

        /**
         * Divides CSV in small csv files i.e. batches and uploads them in uploads/importCSV folder. After creating a batch, it triggers
         * its processing by calling the javascript function senddata().
         *
         * @return [type] [description]
         */
        private function createBatches()
        {
            wp_suspend_cache_addition(true);
            $fileType = "";
            $nonce_verification = check_admin_referer('import_upload_nonce', '_wpnonce');
            //Override nonce verification for extending import functionality in any third party extension
            $nonce_verification = apply_filters('csp_import_upload_nonce_verification', $nonce_verification);
            if (! $nonce_verification) {
                echo "Security Check";
                exit;
            }

            if (isset($_POST['wdm_import_csp'])) {
                //Allow only admin to import csv files
                $capabilityToUpload = apply_filters('csp_import_allowed_user_upload', 'manage_options');
                $canUserUpload = apply_filters('csp_can_user_upload_csv', current_user_can($capabilityToUpload));
                if (!$canUserUpload) {
                    echo "Security Check";
                    exit;
                }

                // upload dir path
                $upload = wp_upload_dir();
                $batchDir = $upload['basedir'] . '/importCsv';

                // Clear the importCsv dir if the process was terminated abruptly
                $all_files = glob($batchDir . "minpoints*.csv");
                if ($all_files) {
                    foreach ($all_files as $file) {
                        unlink($file);
                    }
                }

                //Creating importCsv dir in uploads dir to save batch/chunks files
                if (!file_exists($batchDir)) {
                    wp_mkdir_p($batchDir);
                }

                //get files temp location path
                if ($_FILES['csv']['error'] == 0) {
                    $csvFile = $_FILES[ 'csv' ][ 'tmp_name' ];
                }
                $csv = array();
                $batchsize = apply_filters('csp_import_batch_size', 2000) ; //split huge CSV file by 2,000, we can modify this based on the need
                $fptr = fopen($csvFile, 'r');
                $firstLine = fgets($fptr); //get first line of csv file
                fclose($fptr);
                $foundHeaders = str_getcsv(trim($firstLine), ',', '"'); //parse to array
                //Remove unwanted spaces from header
                $foundHeaders = array_map("trim", $foundHeaders);
                $fileUploadType = $_POST['dd_show_import_options'];
                $filePointer = file($csvFile);
                $noOfRecords = count($filePointer) - 1;

                //for checking selected file is valid or not.
                $fileType = $this->checkFileType($fileUploadType, $foundHeaders, $noOfRecords);
                
                if (!$fileType) {
                    return;
                }

                //delete batch column if already exists
                $cspAjaxObject = new \cspAjax\WdmWuspAjax();
                $cspAjaxObject->deleteBatchColumn($fileType);
            
                //Add batch column in table for importing records in the sequence they occur
                $this->addBatchColumn($fileType);
            
                if (($handle = fopen($csvFile, 'r')) !== false) {
                    $firstLineHeader        = fgetcsv($handle, 0, ',');
                    ini_set('max_execution_time', 0);
                    $row = 0;
                    $batchNumber = 0;
                    $file = null;
                    update_option('csp_batches_in_progress', 0);
                    while (($data = fgetcsv($handle)) !== false) {
                        $data  = array_map('trim', $data);
                        //splitting of CSV file :
                        if ($row % $batchsize == 0) {
                            //closing the previous file handler
                            if ($file != null) {
                                fclose($file);
                            }
                            $newBatch = "minpoints$row.csv";
                            $fileName = $batchDir. "/" .$newBatch;
                            $file = fopen($fileName, "w");
                        }
                        $productId = isset($data[0]) ? $data[0] : "";
                        $applicableEntity = isset($data[1]) ? $data[1] : "";
                        $minQty    = isset($data[2]) ? $data[2] : "";
                        ;
                        $flatPrice = isset($data[3]) ? $data[3] : "";
                        $percentPrice = isset($data[4]) ? $data[4] : "";
                        $json = "$productId, $applicableEntity, $minQty, $flatPrice, $percentPrice";
                        fwrite($file, $json.PHP_EOL);
                        //sending the splitted CSV files, batch by batch...
                        if ($row % $batchsize == 0) {
                            $batchNumber++;
                            echo "<script>senddata('$newBatch', '$fileType', $batchNumber); </script>";
                        }
                        $row++;
                    }

                    unset($firstLineHeader);
                    
                    fclose($handle);
                }
            }
        }

        /**
         * Creates column in tables to hold batch number temporarily
         * @param string $fileType Type of import
         */
        public function addBatchColumn($fileType)
        {
            global $wpdb;
            $cspTable = "";

            if ($fileType == "user") {
                $cspTable = $wpdb->prefix . 'wusp_user_pricing_mapping';
            } elseif ($fileType == "role") {
                $cspTable = $wpdb->prefix . 'wusp_role_pricing_mapping';
            } elseif ($fileType == "group") {
                $cspTable = $wpdb->prefix . 'wusp_group_product_price_mapping';
            }

            // Get the columns of the table
            $existingColumn = $wpdb->get_var("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '{$cspTable}' AND column_name = 'batch_numbers'");

            // If the column does not exist then add the column
            if (empty($existingColumn)) {
                $wpdb->query("ALTER TABLE {$cspTable} ADD batch_numbers TINYINT(1) UNSIGNED NOT NULL DEFAULT 0");
            }
        }


        /**
        * Checks Whether the file selected is valid file or not, on the basis of file headers
        */
        public function checkFileHeaders($foundHeaders, $requiredHeaders2, $requiredHeaders1)
        {
            if ($foundHeaders !== $requiredHeaders2 && $foundHeaders !== $requiredHeaders1) {
                echo '<div class="wdm_message_p error"><p>'.__("Please Select valid file", CSP_TD).'</p></div>';
                return false;
            }

            return true;
        }

        private function printTableHeaders($importType, $foundHeaders, $headerFormat1, $headerFormat2, $noOfRecords)
        {
            $importTypeHeader = array(
                'user'  =>  __('User', CSP_TD),
                'role'  =>  __('Role', CSP_TD),
                'group' =>  __('Group', CSP_TD),
            );
             
            $correctHeader = $this->checkFileHeaders($foundHeaders, $headerFormat1, $headerFormat2);
            if (!$correctHeader) {
                 return false;
            }
            ?>
             <input type = "hidden" name = "counters" data-no_of_rows = "<?php echo $noOfRecords ?>" />
             <table cellpadding="10" id = "import_table">
                <thead class="stick">
                    <tr>
                        <th><?php echo __('Product id', CSP_TD); ?></th>
                        <th><?php echo $importTypeHeader[$importType]; ?></th>
                        <th><?php echo __('Min Qty', CSP_TD); ?></th>
                        <th><?php echo __('Active Price', CSP_TD); ?></th>
                        <th><?php echo __('Discount Type', CSP_TD); ?></th>
                        <th><?php echo __('Status', CSP_TD); ?></th>
                    </tr>
                </thead>
                <tbody></tbody>
             </table>
            <?php
            return $importType;
        }

        public function checkFileType($fileUploadType, $foundHeaders, $noOfRecords)
        {
            //for checking headers
            $userHeaders1 = array( 'product id', 'user', 'min qty', 'price' );
            $userHeaders2 = array( 'product id', 'user', 'min qty', 'flat', '%' );
            $roleHeaders1 = array( 'product id', 'role', 'min qty', 'price' );
            $roleHeaders2 = array( 'product id', 'role', 'min qty', 'flat', '%' );
            $groupHeaders1 = array('product id', 'group name', 'min qty', 'price');
            $groupHeaders2 = array('product id', 'group name', 'min qty', 'flat', '%');
            $foundHeaders = array_map('strtolower', $foundHeaders);

            switch ($fileUploadType) {
                case 'Wdm_User_Specific_Pricing_Import':
                    return $this->printTableHeaders('user', $foundHeaders, $userHeaders1, $userHeaders2, $noOfRecords);

                case 'Wdm_Role_Specific_Pricing_Import':
                    return $this->printTableHeaders('role', $foundHeaders, $roleHeaders1, $roleHeaders2, $noOfRecords);

                case 'Wdm_Group_Specific_Pricing_Import':
                        $activePluginsArray = apply_filters('active_plugins', get_option('active_plugins'));
                    if (in_array('groups/groups.php', $activePluginsArray)) {
                        return $this->printTableHeaders('group', $foundHeaders, $groupHeaders1, $groupHeaders2, $noOfRecords);
                    }
                        echo '<div class="wdm_message_p error"><p>'.__('Please Activate the Groups Plugin ', CSP_TD).'</p></div>';
                    return false;
            }
        }
    }
}

/**
 * Include all batch processing files
 */
include_once('process-import/class-wdm-process-user-specific-csv-batches.php');
include_once('process-import/class-wdm-process-role-specific-csv-batches.php');
include_once('process-import/class-wdm-process-group-specific-csv-batches.php');