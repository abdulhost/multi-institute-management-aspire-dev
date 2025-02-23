<?php

// Create table (moved outside function for immediate testing)
global $wpdb;
$table_name = $wpdb->prefix . 'class_sections';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    education_center_id varchar(255) NOT NULL,
    class_name varchar(255) NOT NULL,
    sections text NOT NULL,
    PRIMARY KEY (id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Insert sample data if table is empty
$count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
if ($count == 0) {
    $wpdb->insert($table_name, array('education_center_id'=>'AFC46B9CEE17','class_name' => 'Class 1', 'sections' => 'a,b,c'));
    $wpdb->insert($table_name, array('education_center_id'=>'AFC46B9CEE17','class_name' => 'Class 2', 'sections' => 'x,y,z'));
}


//education center id
function get_educational_center_data() {
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    $educational_center = get_posts(array(
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1,
    ));

    if (empty($educational_center)) {
        // return '<p>No Educational Center found for this Admin ID.</p>';
        wp_redirect(home_url('/')); 
    }

    return get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
}