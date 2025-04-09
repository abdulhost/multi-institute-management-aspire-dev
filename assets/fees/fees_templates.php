<?php
if (!defined('ABSPATH')) {
    exit;
}

function fee_templates_institute_dashboard_shortcode() {
    if (!is_user_logged_in()) {
        wp_redirect(home_url('/login'));
        exit();    }
    global $wpdb;

    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();    }

    // Handle fee template deletion
    // if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['template_id'])) {
    //     $template_id = intval($_GET['template_id']);
    //     $wpdb->delete($wpdb->prefix . 'fee_templates', ['id' => $template_id]);
    //     wp_redirect(home_url('/institute-dashboard/#fee-templates'));
    //     exit;
    // }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <?php
        // $active_section = 'view-fee-templates';
        // include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?> -->
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <a href="/institute-dashboard/fees/?section=fees-templates">+ Add Fee Template</a>

            <!-- Search Form -->
            <?php
            $args = [
                'table_id' => 'fee-templates-table',
                'search_input_id' => 'search_text',
            ];
            include plugin_dir_path(__FILE__) . '../searchbar.php'; // Adjust path as needed
            ?>

            <!-- Fee Templates List Table -->
            <div id="result">
                <h3>Fee Templates List</h3>
                <table id="fee-templates-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>Frequency</th>
                            <th>Class ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $templates = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}fee_templates WHERE education_center_id = %d", 
                            $education_center_id
                        ));

                        if (!empty($templates)) {
                            foreach ($templates as $template) {
                                echo '<tr class="fee-template-row">
                                    <td>' . esc_html($template->name) . '</td>
                                    <td>' . esc_html($template->amount) . '</td>
                                    <td>' . esc_html($template->frequency) . '</td>
                                    <td>' . esc_html($template->class_id ?: 'N/A') . '</td>
                                    <td>
                                        <a href="/institute-dashboard/edit-fee-template/#template-' . $template->id . '" class="edit-btn">Edit</a> |
                                        <a href="?action=delete&template_id=' . $template->id . '" 
                                           onclick="return confirm(\'Are you sure you want to delete this fee template?\')">Delete</a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5">No fee templates found for this Educational Center.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('fee_templates_institute_dashboard', 'fee_templates_institute_dashboard_shortcode');