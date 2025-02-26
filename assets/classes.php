<?php 
function classes_institute_dashboard_shortcode() {

?>
<div class="attendance-main-wrapper" style="display: flex;">
<!-- <div class="institute-dashboard-wrapper"> -->
    <?php
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