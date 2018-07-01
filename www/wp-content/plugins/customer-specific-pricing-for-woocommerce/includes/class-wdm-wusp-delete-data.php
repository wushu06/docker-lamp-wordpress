<?php

namespace WuspDeleteData;

class WdmWuspDeleteData
{
    //Deleting/Syncing pricing for users when they are deleted.
    public static function deleteCustomerMappingForUsers($userId)
    {
        global $subruleManager, $ruleManager, $wpdb;
        $ruleIds = array();
        $subrules = $subruleManager->getAllRuleInfoForAssociatedEntity($userId, 'customer');
        foreach ($subrules as $key) {
            $ruleIds[] = $key['rule_id'];
        }
        $ruleIds = array_unique($ruleIds);
        foreach ($ruleIds as $ruleId) {
            $ruleManager->deleteRule($ruleId);
        }
        $wpdb->delete($wpdb->prefix . 'wusp_user_pricing_mapping', array('user_id' => $userId), array('%d'));
    }

    //Deleting/Syncing pricing for products when they are deleted.
    public static function deleteMappingForProducts($productId)
    {
        global $wpdb, $subruleManager, $ruleManager;
        $subruleManager->deleteSubruleIdsForProduct($productId);
        $wpdb->delete($wpdb->prefix . 'wusp_user_pricing_mapping', array('product_id' => $productId), array('%d'));
        $wpdb->delete($wpdb->prefix . 'wusp_role_pricing_mapping', array('product_id' => $productId), array('%d'));
        $wpdb->delete($wpdb->prefix . 'wusp_group_product_price_mapping', array('product_id' => $productId), array('%d'));
        $ruleManager->setUnusedRulesAsInactive();
        $ruleManager->deleteRuleWithZeroNumberOfSubrules();
    }

    //Deleting/Syncing pricing for groups when they are deleted.
    public static function deleteMappingForGroups($groupId)
    {
        global $subruleManager, $ruleManager, $wpdb;
        $ruleIds = array();
        $subrules = $subruleManager->getAllRuleInfoForAssociatedEntity($groupId, 'group');
        foreach ($subrules as $key) {
            $ruleIds[] = $key['rule_id'];
        }
        $ruleIds = array_unique($ruleIds);
        foreach ($ruleIds as $ruleId) {
            $ruleManager->deleteRule($ruleId);
        }
        $wpdb->delete($wpdb->prefix . 'wusp_group_product_price_mapping', array('group_id' => $groupId), array('%d'));
    }

    public static function deleteCustomerMapping($selections)
    {
        global $wpdb;

        foreach ($selections as $single_selection) {
            $wpdb->delete($wpdb->prefix . 'wusp_user_pricing_mapping', array('id' => $single_selection));
        }
    }

    public static function deleteRoleGroupMapping($table_name, $selections)
    {
        global $wpdb;

        foreach ($selections as $single_selection) {
            $wpdb->delete($table_name, array('id' => $single_selection));
        }
    }
}
