<?php



function hookMeUp_remove_database()
{
    global $wpdb;
    $table_name = $wpdb->prefix.'hookMeUp_oauth_one';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    delete_option('hookMeUp_time_card_version');
}


 ?>
