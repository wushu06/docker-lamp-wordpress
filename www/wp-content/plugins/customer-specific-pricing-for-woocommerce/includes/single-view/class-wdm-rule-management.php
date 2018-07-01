<?php

namespace cspSingleView;

if (! class_exists('WdmRuleManagement')) {

    class WdmRuleManagement
    {

        /**
         * @var Singleton The reference to *Singleton* instance of this class
         */
        private static $instance;
        public $errors;
        public $ruleTable;

        /**
         * Returns the *Singleton* instance of this class.
         *
         * @return Singleton The *Singleton* instance.
         */
        public static function getInstance()
        {
            if (null === static::$instance) {
                static::$instance = new static();
            }

            return static::$instance;
        }

        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        protected function __construct()
        {
            global $wpdb;
            $this->ruleTable = $wpdb->prefix . 'wusp_rules';
            add_action('woocommerce_process_product_meta_simple', array( $this, 'setUnusedRulesAsInactive' ), 9999);
            add_action('woocommerce_ajax_save_product_variations', array( $this, 'setUnusedRulesAsInactive' ), 9999);
            //$this->getCompleteRuleData( 25 );
        }

        private function addError($message)
        {
            $this->errors .= $message;
        }

        public function addRule($ruleTitle, $ruleType)
        {
            global $wpdb;
            $creationTime    = date('Y-m-d h:i:s', current_time('timestamp'));
            $insertStatus    = $wpdb->insert(
                $this->ruleTable,
                array(
                'rule_title'             => $ruleTitle,
                'rule_type'              => ucfirst(strtolower($ruleType)),
                'rule_creation_time'     => $creationTime,
                'rule_modification_time' => $creationTime,
                'active'                 => 1,
                ),
                array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%d'
                )
            );
            if ($insertStatus !== false) {
                return $wpdb->insert_id;
            }
            $this->addError(__('Could not insert rule in the database', CSP_TD));
            return false;
        }

        public function updateRule($ruleId, $dataTobeUpdated)
        {
            global $wpdb;
            $noOfRowsUpdated = 0;

            if (! isset($dataTobeUpdated[ 'active' ])) {
                $dataTobeUpdated[ 'active' ] = 1;
            }
            $dataTobeUpdated[ 'rule_modification_time' ] = date('Y-m-d h:i:s', current_time('timestamp'));
            $sizeOfData                                  = count($dataTobeUpdated);
            $queryPlaceholders                           = array_fill(0, $sizeOfData, '%s');
            $columnsTobeUpdated                          = array_keys($dataTobeUpdated);

            $positionOfActiveFlag = array_search('active', $columnsTobeUpdated);
            if ($positionOfActiveFlag !== false) {
                $queryPlaceholders[ $positionOfActiveFlag ] = '%d';
            }

            $checkRuleTypeInData = array_search('rule_type', $columnsTobeUpdated);
            if ($checkRuleTypeInData !== false) {
                $dataTobeUpdated[ 'rule_type' ] = ucfirst(strtolower($dataTobeUpdated[ 'rule_type' ]));
            }

            if ($noOfRowsUpdated = $wpdb->update($this->ruleTable, $dataTobeUpdated, array(
                'rule_id' => $ruleId,
            ), $queryPlaceholders, array(
                '%d'
            )) || $noOfRowsUpdated == 0 ) {
                return true;
            }
            $this->addError(__('Could not update rule in the database. Please check if correct data is added in the form.', CSP_TD));
            return false;
        }

        public function deleteRule($ruleId)
        {
            global $wpdb, $subruleManager;
            $ruleType = $this->getRuleType($ruleId);
            if ($ruleType !== null) {
                $subruleManager->deleteSubrulesOfRule($ruleId, $ruleType);
            }
            $wpdb->delete($this->ruleTable, array(
                'rule_id' => $ruleId,
            ), array(
                '%d'
            ));
        }

        //deletes the rule whose total_subrules are 0
        public function deleteRuleWithZeroNumberOfSubrules()
        {
            global $wpdb;
            $wpdb->delete($this->ruleTable, array('total_subrules' => 0));
        }
        public function setUnusedRulesAsInactive()
        {
            global $wpdb, $subruleManager;
            $findActiveRules             = $wpdb->get_results($wpdb->prepare("SELECT rule_id, total_subrules FROM {$this->ruleTable} WHERE active = %d", 1), ARRAY_A);
            $rulesNumOfSubrules   = array();
            if ($findActiveRules) {
                foreach ($findActiveRules as $singleActiveRule) {
                    $rulesNumOfSubrules[ $singleActiveRule[ 'rule_id' ] ] = $singleActiveRule[ 'total_subrules' ];
                }

                //find keys of $rulesNumOfSubrules and search for number of inactive rules for those rule ids
                $rulesCountOfInactive = $subruleManager->getCountOfInactiveSubrulesForRules(array_keys($rulesNumOfSubrules));
                if ($rulesCountOfInactive === false) {
                    return;
                }
                //Find out all those rule ids which are active but whose all subrules are inactive or 0
                $rulesNumOfSubrules = array_intersect_assoc($rulesCountOfInactive, $rulesNumOfSubrules);

                $ruleIds = array_keys($rulesNumOfSubrules);
                if ($ruleIds) {
                    $ruleIds = implode(', ', $ruleIds);
                    $wpdb->query("UPDATE {$this->ruleTable} SET `active`= 0 WHERE rule_id IN ($ruleIds)");
                }
            }
        }

        public function updateTotalNumberOfSubrules($ruleId)
        {
            global $wpdb, $subruleManager;
            $subrulesTotal = $subruleManager->countSubrules($ruleId);
            $wpdb->update($this->ruleTable, array(
                'total_subrules' => $subrulesTotal,
            ), array(
                'rule_id' => $ruleId,
            ), array(
                '%d',
            ), array(
                '%d',
            ));
        }

        public function getCompleteRuleData($ruleId)
        {
            global $wpdb, $subruleManager;
            $completeRuleInfo    = array();
            $mainRuleInfo        = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$this->ruleTable} WHERE rule_id=%d LIMIT 1", $ruleId), ARRAY_A);
            if ($mainRuleInfo == null) {
                return false;
            }
            $completeRuleInfo                = $mainRuleInfo[ 0 ];
            $completeRuleInfo[ 'subrules' ]  = array();
            $subrulesInfo                    = $subruleManager->getAllSubrulesInfoForRule($ruleId);
            if ($subrulesInfo != null) {
                $completeRuleInfo[ 'subrules' ] = $subrulesInfo;
            }
            return $completeRuleInfo;
        }

        public function getRuleTitle($ruleId)
        {
            global $wpdb;
            $ruleTitle = $wpdb->get_results($wpdb->prepare("SELECT rule_title FROM {$this->ruleTable} WHERE rule_id=%d LIMIT 1", $ruleId), ARRAY_A);
            return $ruleTitle[ 0 ];
        }

        public function getRuleType($ruleId)
        {
            global $wpdb;
            $ruleType = $wpdb->get_var($wpdb->prepare("SELECT rule_type FROM {$this->ruleTable} WHERE rule_id = %d", $ruleId));
            return $ruleType;
        }
    }

}
$GLOBALS['ruleManager'] = WdmRuleManagement::getInstance();
