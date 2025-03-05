<?php
if (!defined('ABSPATH')) {
    exit;
}

function add_fee_templates_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    if (isset($_POST['add_fee_template']) && wp_verify_nonce($_POST['nonce'], 'add_fee_template_nonce')) {
        $name = sanitize_text_field($_POST['template_name']);
        $amount = floatval($_POST['template_amount']);
        $frequency = sanitize_text_field($_POST['template_frequency']);
        $class_id = sanitize_text_field($_POST['class_id']);

        $wpdb->insert($wpdb->prefix . 'fee_templates', [
            'name' => $name,
            'amount' => $amount,
            'frequency' => $frequency,
            'class_id' => $class_id,
            'education_center_id' => $education_center_id,
        ]);
        // return '<p>Fee template added successfully!</p>';
        wp_redirect(home_url('/institute-dashboard/fees/?section=add-fees-template'));

    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <?php
        // $active_section = 'add-fee-template';
        // include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?> -->
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <h3>Add Fee Template</h3>
            <form method="POST">
                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('template-details')">
                        <h4>Template Details</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="template-details">
                        <label for="template_name">Template Name:</label>
                        <input type="text" name="template_name" required><br>
                        <label for="template_amount">Amount:</label>
                        <input type="number" name="template_amount" step="0.01" required><br>
                        <label for="template_frequency">Frequency:</label>
                        <select name="template_frequency" required>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="annual">Annual</option>
                        </select><br>
                        <label for="class_id">Class ID (Optional):</label>
                        <input type="text" name="class_id"><br>
                    </div>
                </div>
                <?php wp_nonce_field('add_fee_template_nonce', 'nonce'); ?>
                <input type="submit" name="add_fee_template" value="Add Fee Template">
            </form>

            <script>
            function toggleSection(sectionId) {
                var content = document.getElementById(sectionId);
                content.style.display = content.style.display === 'none' ? 'block' : 'none';
            }
            </script>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('add_fee_templates_institute_dashboard', 'add_fee_templates_institute_dashboard_shortcode');