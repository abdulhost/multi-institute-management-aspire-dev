<?php 
function classes_institute_dashboard_shortcode() {
    $educational_center_id = get_educational_center_data();

    if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
        return $educational_center_id;
    }
    if (empty($educational_center_id)) {
        wp_redirect(home_url('/login')); // Redirect to login page
        exit();
    }
?>
<div class="attendance-main-wrapper" style="display: flex;">
<!-- <div class="institute-dashboard-wrapper"> -->
    <?php
    echo render_admin_header(wp_get_current_user());
    if (!is_center_subscribed($educational_center_id)) {
        return render_subscription_expired_message($educational_center_id);
    }
$active_section = 'classes';
include(plugin_dir_path(__FILE__) . 'sidebar.php');
    ?>
<!-- </div> -->
<div id="add-student-form" style="display: block; width:100%">
<?php  require_once 'search-class-table.php';
?>
<div>
    <?php render_class_table('class'); ?>
</div>
</div>
<?php
}
add_shortcode('classes_institute_dashboard', 'classes_institute_dashboard_shortcode');