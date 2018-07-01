<?php

namespace cspCategoryPricing\deleteData;

if (!class_exists('WdmWuspDeleteCategoryData')) {
    class WdmWuspDeleteCategoryData
    {
        private static $instance;
        public $errors;
        public $userPriceTable;
        public $rolePriceTable;
        public $groupPriceTable;

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

        public function __construct()
        {
            global $wpdb;
            $this->userPriceTable    = $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
            $this->rolePriceTable    = $wpdb->prefix . 'wcsp_role_category_pricing_mapping';
            $this->groupPriceTable    = $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
        }

        public function deleteUserCatEntries($category)
        {
            global $wpdb;
            $query = "DELETE FROM $this->userPriceTable WHERE `cat_slug` = '$category'";
            $wpdb->get_results($query);
        }

        public function deleteRoleCatEntries($category)
        {
            global $wpdb;
            $query = "DELETE FROM $this->rolePriceTable WHERE `cat_slug` = '$category'";
            $wpdb->get_results($query);
        }

        public function deleteGroupCatEntries($category)
        {
            global $wpdb;
            $query = "DELETE FROM $this->groupPriceTable WHERE `cat_slug` = '$category'";
            $wpdb->get_results($query);
        }

    	public function deleteUserCategoryQtyRecords($userId, $minQty, $category)
    	{
            global $wpdb;
            $query = "DELETE FROM $this->userPriceTable WHERE user_id = %d AND min_qty = %d AND cat_slug = %s";
            $wpdb->get_results($wpdb->prepare($query, $userId, $minQty, $category));
    	}

    	public function deleteRoleCategoryQtyRecords($role, $minQty, $category)
    	{
            global $wpdb;
            $query = "DELETE FROM $this->rolePriceTable WHERE role = %s AND min_qty = %d AND cat_slug = %s";
            $wpdb->get_results($wpdb->prepare($query, $role, $minQty, $category));    		
    	}

    	public function deleteGroupCategoryQtyRecords($groupId, $minQty, $category)
    	{
            global $wpdb;
            $query = "DELETE FROM $this->groupPriceTable WHERE group_id = %d AND min_qty = %d AND cat_slug = %s";
            $wpdb->get_results($wpdb->prepare($query, $groupId, $minQty, $category));    		
    	}

    	public function deleteAllUserRecords()
    	{
            global $wpdb;
    		$query = "DELETE FROM ".$this->userPriceTable;
            $wpdb->get_results($query);
    	}

    	public function deleteAllRoleRecords()
    	{
            global $wpdb;
    		$query = "DELETE FROM ".$this->rolePriceTable;
            $wpdb->get_results($query);
    	}

    	public function deleteAllGroupRecords()
    	{
            global $wpdb;
    		$query = "DELETE FROM ".$this->groupPriceTable;
            $wpdb->get_results($query);
    	}

    	// Deletes the records from databse which are not present in current page submission
		public function removeUserCatQtyList($catArray, $userIdsArray, $minQtyArray)
		{
            global $wpdb, $getCatRecords, $cspFunctions;
            
            $deleteUsers              = array();
            $deleteQty                = array();
            $deletedValues            = array();
            $newArray                 = array();
            $userType = 'user_id';

            if (!empty($userIdsArray)) {
                //array of curremt records
				$newArray = $this->getNewArray($userIdsArray, $catArray, $minQtyArray, $userType);
                // $user_names = "('" . implode("','", $_POST[ 'wdm_woo_username' ]) . "')";
                // $qty = "(" . implode(",", $_POST[ 'wdm_woo_qty' ]) . ")";


                //Fetch existing records from databse
                $existing = $getCatRecords->getCatUserQtyRecords($catArray, $userIdsArray, $minQtyArray);

                //Seperating records to be deleted, i.e the records which are in DB but not in current submission
                $deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType, true);

                foreach ($deletedValues as $key => $value) {
                    $deleteUsers[] = $existing[$key][$userType];
                    $deleteQty[]   = $existing[$key]['min_qty'];
                    $deleteCategory[]   = $existing[$key]['cat_slug'];
                }

                //delete records which are not in submission but saved in the DB
                if (count($deletedValues) > 0) {
                	$this->deleteRecords($deleteUsers, $deleteQty, $deleteCategory, 'User');
                }
            }
		}

		public function removeRoleCatQtyList($catArray, $rolesArray, $minQtyArray)
		{
            global $wpdb, $getCatRecords, $cspFunctions;
            
            $deleteRoles              = array();
            $deleteQty                = array();
            $deletedValues            = array();
            $userType = 'role';

            if (isset($rolesArray)) {
                //array of current records
            	$newArray = $this->getNewArray($rolesArray, $catArray, $minQtyArray, $userType);

                // $role_names = "('" . implode("','", $_POST[ 'wdm_woo_rolename' ]) . "')";
                // $qty = "(" . implode(",", $_POST[ 'wdm_woo_role_qty' ]) . ")";
 
                //Fetch existing records from databse
                $existing = $getCatRecords->getCatRoleQtyRecords($catArray, $rolesArray, $minQtyArray);

                $deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType, true);
                foreach ($deletedValues as $key => $value) {
                    $deleteRoles[] = $existing[$key][$userType];
                    $deleteCategory[] = $existing[$key]['cat_slug'];
                    $deleteQty[]   = $existing[$key]['min_qty'];
                }

                $mapping_count = count($deletedValues);
                if ($mapping_count > 0) {
                    $this->deleteRecords($deleteRoles, $deleteQty, $deleteCategory, "Role");
                }
            }
		}

		public function removeGroupCatQtyList($catArray, $groupIdsArray, $minQtyArray)
		{
            global $wpdb, $getCatRecords, $cspFunctions;

            $deleteGroups           = array();
            $deleteQty              = array();
            $deletedValues          = array();

            $userType               = 'group_id';
            if (!empty($groupIdsArray)) {
                //array of current records
                $newArray = $this->getNewArray($groupIdsArray, $catArray, $minQtyArray, $userType);

                // $user_names = "('" . implode("','", $_POST[ 'wdm_woo_groupname' ]) . "')";
                // $qty = "(" . implode(",", $_POST[ 'wdm_woo_group_qty' ]) . ")";

                $existing = $getCatRecords->getCatGroupQtyRecords($catArray, $groupIdsArray, $minQtyArray);

                $deletedValues = $cspFunctions->multiArrayDiff($newArray, $existing, $userType, true);

                foreach ($deletedValues as $key => $value) {
                    $deleteGroups[] = $existing[$key][$userType];
                    $deleteCategory[]   = $existing[$key]['cat_slug'];
                    $deleteQty[]   = $existing[$key]['min_qty'];
                }

                $mapping_count = count($deletedValues);
                if ($mapping_count > 0) {
                    $this->deleteRecords($deleteGroups, $deleteQty, $deleteCategory, 'Group');
                }
            }
		}

		public function getNewArray($userArray, $catArray, $minQtyArray, $type)
		{
            $newArray                 = array();

            foreach ($userArray as $index => $wdmSingleUser) {
                $newArray[] = array(
                    $type    => $wdmSingleUser,
                    'cat_slug'    => $catArray[ $index ],
                    'min_qty' => $minQtyArray[ $index ]
                );
            }

            return $newArray;
		}

		public function deleteRecords($deleteUsers, $deleteQty, $deleteCategory, $type)
		{
            foreach ($deleteUsers as $index => $singleUser) {
        		$function = "delete".$type."CategoryQtyRecords";
            	$this->$function($singleUser, $deleteQty[$index], $deleteCategory[$index]);
            }			
		}

    }
}
$GLOBALS['deleteCatRecords'] = WdmWuspDeleteCategoryData::getInstance();
