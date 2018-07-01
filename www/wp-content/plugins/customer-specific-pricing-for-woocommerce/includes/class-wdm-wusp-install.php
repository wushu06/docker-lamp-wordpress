<?php

namespace WdmWuspInstall;

if (! class_exists('WdmWuspInstall')) {
    class WdmWuspInstall
    {
        /*
		 * Creates all tables required for the plugin. It creates three
		 * tables in the database. wusp_user_pricing_mapping table stores the mapping of
         * User, Product and specific pricing. wusp_group_product_price_mapping table stores
         * the mapping of Group, Product and specific pricing.
         * wusp_role_pricing_mapping table stores the mapping of Role, Product and specific
         * pricing. Also creates two tables which handles the pricing manager rules. rule
         * table and subrule table. rule table used to store rules associated to the
         * products and subrule stores single subrules of main rule.
		 */

        public static function createTables()
        {
            global $wpdb;
            $wpdb->hide_errors();

            $collate = self::getWpCharsetCollate();

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            $user_pricing_table = $wpdb->prefix . 'wusp_user_pricing_mapping';
            $group_price_table   = $wpdb->prefix . 'wusp_group_product_price_mapping';
            $role_price_table    = $wpdb->prefix . 'wusp_role_pricing_mapping';
            $wdm_rules_table = $wpdb->prefix . "wusp_rules";
            $wdm_subrules_table = $wpdb->prefix . "wusp_subrules";
            $user_category_pricing_table = $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
            $group_category_price_table   = $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
            $role_category_price_table    = $wpdb->prefix . 'wcsp_role_category_pricing_mapping';

            //Create CSP tables

            //Create User Pricing Table
            self::createUserProductPricingTable($user_pricing_table, $collate);

            //Create Group Pricing Table
            self::createGroupProductPricingTable($group_price_table, $collate);

            //Create Role Pricing Table
            self::createRoleProductPricingTable($role_price_table, $collate);

            self::createUserCartegoryPricingTable($user_category_pricing_table, $collate);
            self::createGroupCartegoryPricingTable($group_category_price_table, $collate);
            self::createRoleCartegoryPricingTable($role_category_price_table, $collate);
            //Changes added by Sumit Starts here

            //Create Rule Log Table
            self::createRuleTable($wdm_rules_table, $collate);

            //Create SubRules Log Table
            self::createSubruleTable($wdm_subrules_table, $collate);

            //Check if wp_wusp_user_mapping && wp_wusp_pricing_mapping exist or not. If exist then do following.
            //Check if wp_wusp_user_pricing_mapping table exists. If it does not
            //exist, then create table wp_wusp_user_pricing_mapping from the wp_wusp_pricing_mapping and wp_wusp_user_mapping
            // OR merge both table wp_wusp_pricing_mapping and wp_wusp_user_mapping in single table wp_wusp_user_pricing_mapping.

            $user_mapping_table          = $wpdb->prefix . 'wusp_user_mapping';
            $pricing_mapping_table       = $wpdb->prefix . 'wusp_pricing_mapping';
            $emptyPricingTable           = $wpdb->get_var("SELECT COUNT(*) FROM $user_pricing_table");

            if (($wpdb->get_var("SHOW TABLES LIKE '$user_mapping_table';") || $wpdb->get_var("SHOW TABLES LIKE '$pricing_mapping_table';")) && empty($emptyPricingTable)) {
                self::importToNewUspTables();
            }

            if (! $wpdb->get_var("SHOW TABLES LIKE '$user_pricing_table'") || ! $wpdb->get_var("SHOW TABLES LIKE '$wdm_subrules_table'") || ! $wpdb->get_var("SHOW TABLES LIKE '$wdm_rules_table'") || ! $wpdb->get_var("SHOW TABLES LIKE '$group_price_table'") || ! $wpdb->get_var("SHOW TABLES LIKE '$role_price_table'")) {
                add_action('admin_notices', array('self','cspTablesCreated'));
            }
            self::cleanupDatabase();
        }

        protected static function createUserProductPricingTable($user_pricing_table, $collate)
        {
            /*global $wpdb;
   if (!$wpdb->get_var("SHOW COLUMNS FROM `$enquiryTableName` LIKE 'enquiry_hash';")) {
       $wpdb->query("ALTER TABLE $enquiryTableName ADD enquiry_hash VARCHAR(75)");
       $wpdb->query("ALTER TABLE $enquiryTableName ADD UNIQUE INDEX `enquiry_hash` (`enquiry_hash`)");
   }*/
            global $wpdb;
            if (! $wpdb->get_var("SHOW TABLES LIKE '$user_pricing_table';")) {
                $user_table_query  = "
                CREATE TABLE IF NOT EXISTS {$user_pricing_table} (
                                    id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                    product_id bigint(20),
                                    user_id bigint(20),
                                    price numeric(13,4) UNSIGNED,
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    flat_or_discount_price TINYINT(1),
                                    INDEX product_id (product_id),
                                    INDEX user_id (user_id),
                                    INDEX min_qty (min_qty)
                                ) $collate;
                                ";
                @dbDelta($user_table_query);
            } elseif (!$wpdb->get_var("SHOW COLUMNS FROM `$user_pricing_table` LIKE 'min_qty';")) {
                $wpdb->query("ALTER TABLE $user_pricing_table ADD min_qty BIGINT(20) UNSIGNED NOT NULL DEFAULT 1, ADD INDEX min_qty (min_qty)");
            }
        }

        protected static function createGroupProductPricingTable($group_price_table, $collate)
        {
            global $wpdb;
            $table_present_result = $wpdb->get_var("SHOW TABLES LIKE '{$group_price_table}'");

            if ($table_present_result !== null || $table_present_result == $group_price_table) {
                $wpdb->query("ALTER TABLE {$group_price_table}
                  MODIFY `price` numeric(13,4)");

                if (!$wpdb->get_var("SHOW COLUMNS FROM `$group_price_table` LIKE 'min_qty';")) {
                    $wpdb->query("ALTER TABLE $group_price_table ADD min_qty BIGINT(20) UNSIGNED NOT NULL DEFAULT 1");
                }

                $keyExist = $wpdb->get_results("SHOW KEYS FROM {$group_price_table} where Key_name = 'unique_group_product_price'");
                if ($keyExist) {
                    $wpdb->query("ALTER TABLE $group_price_table 
                    DROP INDEX unique_group_product_price, 
                    ADD UNIQUE KEY `unique_group_product_qty` (`product_id`,`group_id`,`min_qty`)");
                }
            } else {
                $group_table_query = "
                    CREATE TABLE IF NOT EXISTS {$group_price_table} (
                                        id bigint(20) NOT NULL AUTO_INCREMENT,
                                        group_id bigint(20),
                                        product_id bigint(20),
                                        price numeric(13,4),
                                        min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                        flat_or_discount_price TINYINT(1),
                                        UNIQUE KEY unique_group_product_qty (product_id,group_id,min_qty),
                                        PRIMARY KEY  (id)
                                    ) $collate;";
                @dbDelta($group_table_query);
            }
        }

        protected static function createRoleProductPricingTable($role_price_table, $collate)
        {
            global $wpdb;
            $table_present_result = $wpdb->get_var("SHOW TABLES LIKE '{$role_price_table}'");

            if ($table_present_result !== null || $table_present_result == $role_price_table) {
                $wpdb->query("ALTER TABLE {$role_price_table}
                  MODIFY `role` VARCHAR(60),
                  MODIFY `price` numeric(13,4)");

               
                if (!$wpdb->get_var("SHOW COLUMNS FROM `$role_price_table` LIKE 'min_qty';")) {
                    $wpdb->query("ALTER TABLE $role_price_table ADD min_qty BIGINT(20) UNSIGNED NOT NULL DEFAULT 1");
                }

                $keyExist = $wpdb->get_results("SHOW KEYS FROM {$role_price_table} where Key_name = 'unique_role_product_price'");

                if ($keyExist) {
                    $wpdb->query("ALTER TABLE $role_price_table 
                    DROP INDEX unique_role_product_price, 
                    ADD UNIQUE KEY `unique_role_product_qty` (`product_id`,`role`,`min_qty`)");
                }
            } else {
                $role_table_query = "
                CREATE TABLE IF NOT EXISTS {$role_price_table} (
                                    id bigint(20) NOT NULL AUTO_INCREMENT,
                                    role varchar(60) NOT NULL,
                                    price numeric(13,4),
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    flat_or_discount_price TINYINT(1),
                                    product_id bigint(20),
                                    UNIQUE KEY unique_role_product_qty (product_id,role,min_qty),
                                    PRIMARY KEY  (id)
                            ) $collate;
                            ";
                @dbDelta($role_table_query);
            }
        }

        protected static function createUserCartegoryPricingTable($user_category_pricing_table, $collate)
        {
            global $wpdb;
            if (! $wpdb->get_var("SHOW TABLES LIKE '$user_category_pricing_table';")) {
                $user_table_query  = "
                CREATE TABLE IF NOT EXISTS {$user_category_pricing_table} (
                                    id bigint(20) PRIMARY KEY NOT NULL AUTO_INCREMENT,
                                    cat_slug varchar(60) NOT NULL,
                                    user_id bigint(20),
                                    price numeric(13,4) UNSIGNED,
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    flat_or_discount_price TINYINT(1),
                                    INDEX cat_slug (cat_slug),
                                    INDEX user_id (user_id),
                                    INDEX min_qty (min_qty)
                                ) $collate;
                                ";
                @dbDelta($user_table_query);
            }
        }

        protected static function createGroupCartegoryPricingTable($group_category_price_table, $collate)
        {
            global $wpdb;
            
            if (! $wpdb->get_var("SHOW TABLES LIKE '$group_category_price_table';")) {
                $group_table_query = "
                    CREATE TABLE IF NOT EXISTS {$group_category_price_table} (
                                        id bigint(20) NOT NULL AUTO_INCREMENT,
                                        group_id bigint(20),
                                        cat_slug varchar(60) NOT NULL,
                                        price numeric(13,4),
                                        min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                        flat_or_discount_price TINYINT(1),
                                        UNIQUE KEY unique_group_product_qty (cat_slug,group_id,min_qty),
                                        PRIMARY KEY  (id)
                                    ) $collate;";
                @dbDelta($group_table_query);
            }
        }

        protected static function createRoleCartegoryPricingTable($role_category_price_table, $collate)
        {
            global $wpdb;
            
            if (! $wpdb->get_var("SHOW TABLES LIKE '$role_category_price_table';")) {
                $role_table_query = "
                CREATE TABLE IF NOT EXISTS {$role_category_price_table} (
                                    id bigint(20) NOT NULL AUTO_INCREMENT,
                                    role varchar(60) NOT NULL,
                                    price numeric(13,4),
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    flat_or_discount_price TINYINT(1),
                                    cat_slug varchar(60) NOT NULL,
                                    UNIQUE KEY unique_role_product_qty (cat_slug,role,min_qty),
                                    PRIMARY KEY  (id)
                            ) $collate;
                            ";
                @dbDelta($role_table_query);
            }
        }

        protected static function createRuleTable($wdm_rules_table, $collate)
        {
            $wdm_rules_query = "
            CREATE TABLE IF NOT EXISTS {$wdm_rules_table} (
                                rule_id bigint(20) NOT NULL AUTO_INCREMENT,
                                rule_title  text,
                                rule_type varchar(20),
                                rule_creation_time datetime,
                                rule_modification_time datetime,
                                active TINYINT(1),
                                total_subrules SMALLINT(5) UNSIGNED NOT NULL,
                                PRIMARY KEY  (rule_id),
                                INDEX active (active),
                                INDEX rule_type (rule_type)
                        ) $collate;
                        ";
            @dbDelta($wdm_rules_query);
        }

        protected static function createSubruleTable($wdm_subrules_table, $collate)
        {
            global $wpdb;
            $table_present_result = $wpdb->get_var("SHOW TABLES LIKE '{$wdm_subrules_table}'");
            if ($table_present_result === null || $table_present_result != $wdm_subrules_table) {
                $wdm_subrules_query = "
                CREATE TABLE IF NOT EXISTS {$wdm_subrules_table} (
                                    subrule_id bigint(20) NOT NULL AUTO_INCREMENT,
                                    rule_id bigint(20) UNSIGNED NOT NULL,
                                    product_id bigint(20) UNSIGNED NOT NULL,
                                    rule_type varchar(20),
                                    associated_entity varchar(50),
                                    flat_or_discount_price TINYINT(1),
                                    price numeric(13,4) UNSIGNED,
                                    min_qty bigint(20) UNSIGNED NOT NULL DEFAULT 1,
                                    active TINYINT(1),
                                    PRIMARY KEY  (subrule_id),
                                    INDEX rule_id (rule_id),
                                    INDEX product_id (product_id),
                                    INDEX rule_type (rule_type),
                                    INDEX associated_entity (associated_entity),
                                    INDEX active (active)
                            ) $collate;
                            ";
                @dbDelta($wdm_subrules_query);
            } elseif (!$wpdb->get_var("SHOW COLUMNS FROM `$wdm_subrules_table` LIKE 'min_qty';")) {
                $wpdb->query("ALTER TABLE $wdm_subrules_table ADD min_qty BIGINT(20) UNSIGNED NOT NULL DEFAULT 1");
            }
        }

        protected static function getWpCharsetCollate()
        {

            global $wpdb;
            $charset_collate = '';

            if (! empty($wpdb->charset)) {
                $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if (! empty($wpdb->collate)) {
                $charset_collate .= " COLLATE $wpdb->collate";
            }

            return $charset_collate;
        }

        public static function cspTablesCreated()
        {
            ?>
            <div id="message" class="error">
                <p><?php printf(__("Please try to deactivate and then activate the plugin again.")); ?></p>
            </div>
        <?php
        }

        public static function importToNewUspTables()
        {
            global $wpdb;
            $fetched_users               = array();
            $wdm_users                   = $wpdb->prefix . 'users';
            $user_pricing_table  = $wpdb->prefix . 'wusp_user_pricing_mapping';
            $user_mapping_table          = $wpdb->prefix . 'wusp_user_mapping';
            $pricing_mapping_table       = $wpdb->prefix . 'wusp_pricing_mapping';

            $find_user_mapping           = $wpdb->get_results("SELECT user_id, product_id, price, flat_or_discount_price FROM {$user_mapping_table}, {$pricing_mapping_table} WHERE {$user_mapping_table}.id = {$pricing_mapping_table}.user_product_id");
            if ($find_user_mapping) {
                foreach ($find_user_mapping as $single_user_mapping) {
                    // $user = $single_user_mapping->user_id;

                    if (! isset($fetched_users[ $single_user_mapping->user_id ])) {
                        $fetched_users[ $single_user_mapping->user_id ] = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wdm_users} where id=%d", $single_user_mapping->user_id));
                    }
                    $get_user_id = $fetched_users[ $single_user_mapping->user_id ];
                    if ($get_user_id !== null) {
                        $wpdb->insert($user_pricing_table, array(
                            'price'                  => $single_user_mapping->price,
                            'product_id'             => $single_user_mapping->product_id,
                            'user_id'                => $single_user_mapping->user_id,
                            'flat_or_discount_price' => $single_user_mapping->flat_or_discount_price,
                        ), array(
                            '%s',
                            '%d',
                            '%d',
                            '%d',
                        ));
                    }
                }// end of foreach
            }// end of if find_user_mapping
            // }
        }

        public static function cleanupDatabase()
        {
            if (CSP_VERSION >= '4.1.0') {
                self::deleteEntriesOfCatDeletedUsers();
                self::deleteEntriesOfDeletedCategories();
                self::deleteEntriesOfCatDeletedGroups();
            }
            self::deleteEntriesOfProductDeletedUsers();
            self::deleteEntriesOfDeletedGroups();
            self::deleteEntriesOfDeletedProducts();
        }

        public static function deleteEntriesOfCatDeletedUsers()
        {
            global $wpdb;
            $deletedCatUsers = array();

            $wdm_users                   = $wpdb->prefix . 'users';
            $wcspCatTable        = $wpdb->prefix . 'wcsp_user_category_pricing_mapping';

            $mappedCatUserIds = $wpdb->get_col("SELECT DISTINCT user_id FROM {$wcspCatTable}");

            $AllAvailableUsers = $wpdb->get_col("SELECT DISTINCT ID FROM {$wdm_users}");

            if ($mappedCatUserIds && $AllAvailableUsers && is_array($mappedCatUserIds) && is_array($AllAvailableUsers)) {
                $deletedCatUsers = array_diff($mappedCatUserIds, $AllAvailableUsers);
            }

            if ($deletedCatUsers) {
                $combineDeletedCatUsers = implode(', ', $deletedCatUsers);
                $wpdb->query("DELETE FROM $wcspCatTable WHERE user_id IN ($combineDeletedCatUsers)");
            }
        }

        public static function deleteEntriesOfProductDeletedUsers()
        {
            global $wpdb;
            $deletedProductUsers = array();
            $deletedCatUsers = array();

            $wdm_users                   = $wpdb->prefix . 'users';
            $user_pricing_table  = $wpdb->prefix . 'wusp_user_pricing_mapping';

            $mappedProductUserIds = $wpdb->get_col("SELECT DISTINCT user_id FROM {$user_pricing_table}");

            $AllAvailableUsers = $wpdb->get_col("SELECT DISTINCT ID FROM {$wdm_users}");

            if ($mappedProductUserIds && $AllAvailableUsers && is_array($mappedProductUserIds) && is_array($AllAvailableUsers)) {
                $deletedProductUsers = array_diff($mappedProductUserIds, $AllAvailableUsers);
            }

            if ($deletedProductUsers) {
                $combineDeletedProUsers = implode(', ', $deletedProductUsers);
                $wpdb->query("DELETE FROM $user_pricing_table WHERE user_id IN ($combineDeletedProUsers)");
            }
        }

        public static function deleteEntriesOfCatDeletedGroups()
        {
            global $wpdb;
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                $deletedGroups       = array();
                $wdm_groups_group    = $wpdb->prefix . 'groups_group';
                $wcspCatTable        = $wpdb->prefix . 'wcsp_group_category_pricing_mapping';

                $mappedGroupIds = $wpdb->get_col("SELECT DISTINCT group_id FROM {$wcspCatTable}");

                $AllAvailableGroups = $wpdb->get_col("SELECT DISTINCT group_id FROM {$wdm_groups_group}");

                //find out deleted groups
                if ($mappedGroupIds && $AllAvailableGroups && is_array($mappedGroupIds) && is_array($AllAvailableGroups)) {
                    $deletedGroups = array_diff($mappedGroupIds, $AllAvailableGroups);
                }

                // Delete them from {$wpdb->prefix}wusp_group_product_price_mapping
                if ($deletedGroups) {
                    $combineDeletedGroups = implode(', ', $deletedGroups);
                    $wpdb->query("DELETE FROM $wcspCatTable WHERE group_id IN ($combineDeletedGroups)");
                }
            }
        }

        public static function deleteEntriesOfDeletedGroups()
        {
            global $wpdb;
            $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
            if (in_array('groups/groups.php', $active_plugins)) {
                $deletedGroups       = array();
                $wpusp_group_table   = $wpdb->prefix . 'wusp_group_product_price_mapping';
                $wdm_groups_group    = $wpdb->prefix . 'groups_group';

                $mappedGroupIds = $wpdb->get_col("SELECT DISTINCT group_id FROM {$wpusp_group_table}");

                $AllAvailableGroups = $wpdb->get_col("SELECT DISTINCT group_id FROM {$wdm_groups_group}");

                //find out deleted groups
                if ($mappedGroupIds && $AllAvailableGroups && is_array($mappedGroupIds) && is_array($AllAvailableGroups)) {
                    $deletedGroups = array_diff($mappedGroupIds, $AllAvailableGroups);
                }

                // Delete them from {$wpdb->prefix}wusp_group_product_price_mapping
                if ($deletedGroups) {
                    $combineDeletedGroups = implode(', ', $deletedGroups);
                    $wpdb->query("DELETE FROM $wpusp_group_table WHERE group_id IN ($combineDeletedGroups)");
                }
            }
        }

        public static function deleteEntries($deleteTable, $deletedProducts)
        {
            global $wpdb;
            if ($deletedProducts) {
                $combineDelProducts = implode(', ', $deletedProducts);
                $wpdb->query("DELETE FROM $deleteTable WHERE product_id IN ($combineDelProducts)");
            }
        }

        public static function deleteUserEntries($ProductsInUserTable, $userPricingTable, $allProducts)
        {
            if ($ProductsInUserTable && $allProducts) {
                $deletedProducts = array_diff($ProductsInUserTable, $allProducts);
                self::deleteEntries($userPricingTable, $deletedProducts);
            }
        }

        public static function deleteRoleEntries($ProductsInRoleTable, $rolePricingTable, $allProducts)
        {
            if ($ProductsInRoleTable && $allProducts) {
                $deletedProducts = array_diff($ProductsInRoleTable, $allProducts);
                self::deleteEntries($rolePricingTable, $deletedProducts);
            }
        }

        public static function deleteGroupEntries($ProductsInGroupTable, $groupPricingTable, $allProducts)
        {
            if ($ProductsInGroupTable && $allProducts) {
                $deletedProducts = array_diff($ProductsInGroupTable, $allProducts);
                self::deleteEntries($groupPricingTable, $deletedProducts);
            }
        }

        public static function deleteEntriesOfDeletedProducts()
        {
            global $wpdb;
            $postsTable          = $wpdb->prefix . 'posts';
            $userPricingTable    = "{$wpdb->prefix}wusp_user_pricing_mapping";
            $rolePricingTable    = "{$wpdb->prefix}wusp_role_pricing_mapping";
            $groupPricingTable   = "{$wpdb->prefix}wusp_group_product_price_mapping";
            $deletedProducts     = array();

            $allProducts = $wpdb->get_col("SELECT ID FROM $postsTable WHERE post_type IN ('product', 'product_variation')");

            $ProductsInUserTable = $wpdb->get_col("SELECT product_id FROM {$userPricingTable}");

            // Delete them from wusp_user_pricing_mapping {$wpdb->prefix}wusp_group_product_price_mapping
            self::deleteUserEntries($ProductsInUserTable, $userPricingTable, $allProducts);

            foreach ($deletedProducts as $deleteProductId) {
                \WuspDeleteData\WdmWuspDeleteData::deleteMappingForProducts($deleteProductId);
            }
            //reset $deletedProducts
            $deletedProducts = array();

            $ProductsInRoleTable = $wpdb->get_col("SELECT product_id FROM {$rolePricingTable}");

            // Delete them from {$wpdb->prefix}wusp_user_pricing_mapping
            self::deleteUserEntries($ProductsInRoleTable, $rolePricingTable, $allProducts);

            foreach ($deletedProducts as $deleteProductId) {
                \WuspDeleteData\WdmWuspDeleteData::deleteMappingForProducts($deleteProductId);
            }
            //reset $deletedProducts
            $deletedProducts = array();

            $ProductsInGroupTable = $wpdb->get_col("SELECT product_id FROM {$groupPricingTable}");

            // Delete them from {$wpdb->prefix}wusp_user_pricing_mapping
            self::deleteUserEntries($ProductsInGroupTable, $groupPricingTable, $allProducts);

            foreach ($deletedProducts as $deleteProductId) {
                \WuspDeleteData\WdmWuspDeleteData::deleteMappingForProducts($deleteProductId);
            }
        }

        public static function deleteEntriesOfDeletedCategories()
        {
            global $wpdb;
            $groupCatTable = $wpdb->prefix . 'wcsp_group_category_pricing_mapping';
            $roleCatTable = $wpdb->prefix . 'wcsp_role_category_pricing_mapping';
            $userCatTable = $wpdb->prefix . 'wcsp_user_category_pricing_mapping';
            
            $catSlugArray = self::getCategoryArray();

            if (empty($catSlugArray)) {
                return;
            }

            $combineCategories = "('" . implode("', '", $catSlugArray) . "')";

            if (!empty($combineCategories)) {
                self::deleteCatEntries($combineCategories, $userCatTable);
                self::deleteCatEntries($combineCategories, $roleCatTable);
                self::deleteCatEntries($combineCategories, $groupCatTable);
            }
        }

        public static function deleteCatEntries($combineCategories, $table)
        {
            global $wpdb;
            $query = "DELETE FROM $table WHERE `cat_slug` NOT IN $combineCategories";
            $wpdb->get_results($query);
        }

        public static function getCategoryArray()
        {
            $catSlugArray = array();
            $taxonomy     = 'product_cat';
            $orderby      = 'name';  
            $show_count   = 0;      // 1 for yes, 0 for no
            $pad_counts   = 0;      // 1 for yes, 0 for no
            $hierarchical = 1;      // 1 for yes, 0 for no  
            $title        = '';  
            $empty        = 0;

            $args = array(
                 'taxonomy'     => $taxonomy,
                 'orderby'      => $orderby,
                 'show_count'   => $show_count,
                 'pad_counts'   => $pad_counts,
                 'hierarchical' => $hierarchical,
                 'title_li'     => $title,
                 'hide_empty'   => $empty
            );
            $all_categories = get_categories( $args );

            foreach ($all_categories as $cat) {
                $catSlugArray[] = $cat->slug;   
            }

            return $catSlugArray;
        }
    }

}

