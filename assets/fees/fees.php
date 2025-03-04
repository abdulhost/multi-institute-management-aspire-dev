<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_education_erp_dashboard_shortcode() {
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'view-fees';
    $education_center_id = get_educational_center_data();

    ob_start();
    ?>
    <div class="erp-dashboard" style="display: flex;">
        <?php
        // Dynamically set active section for sidebar
        $active_section = str_replace('-', '-', $section); // e.g., "add-fee" -> "add_fee"
        include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path
        ?>
        <div class="dashboard-content" style="flex: 1; padding: 20px;">
            <?php
            switch ($section) {
                case 'view-fees':
                    echo fees_institute_dashboard_shortcode();
                    break;
                case 'add-fees':
                    echo add_fees_institute_dashboard_shortcode();
                    break;
                case 'update-fees':
                    echo edit_fees_institute_dashboard_shortcode();
                    break;
                case 'delete-fees':
                    echo delete_fees_institute_dashboard_shortcode();
                    break;
                case 'fees-templates':
                    echo fee_templates_institute_dashboard_shortcode();
                    break;
                case 'add-fees-template':
                    echo add_fee_templates_institute_dashboard_shortcode();
                    break;
                case 'update-fees-template':
                    echo edit_fee_templates_institute_dashboard_shortcode();
                    break;
                case 'delete-fees-template':
                    echo delete_fee_templates_institute_dashboard_shortcode();
                    break;
                default:
                    echo '<p>Section not found. Please select a valid option.</p>';
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('my_education_erp_dashboard', 'my_education_erp_dashboard_shortcode');

function fees_institute_dashboard_shortcode() {
    global $wpdb;

    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        return '<p>No Educational Center found.</p>';
    }

    // Handle fee deletion
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['fee_id'])) {
        $fee_id = intval($_GET['fee_id']);
        $wpdb->delete($wpdb->prefix . 'student_fees', ['id' => $fee_id]);
        wp_redirect(home_url('/institute-dashboard/fees'));
        exit;
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <?php
        // $active_section = 'view-fees';
        // include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?> -->
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <a href="/institute-dashboard/fees/?section=add-fees">+ Add Fee</a>

            <!-- Search Form -->
            <?php
            $args = [
                'table_id' => 'fees-table',
                'search_input_id' => 'search_text',
            ];
            include plugin_dir_path(__FILE__) . '../searchbar.php'; // Adjust path as needed
            ?>

            <!-- Fees List Table -->
            <div id="result">
                <h3>Fees List</h3>
                <table id="fees-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Template</th>
                            <th>Amount</th>
                            <th>Month</th>
                            <th>Status</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $fees = $wpdb->get_results($wpdb->prepare(
                            "SELECT sf.*, ft.name AS template_name FROM {$wpdb->prefix}student_fees sf 
                             JOIN {$wpdb->prefix}fee_templates ft ON sf.template_id = ft.id 
                             WHERE sf.education_center_id = %d", 
                            $education_center_id
                        ));

                        if (!empty($fees)) {
                            foreach ($fees as $fee) {
                                $student_name = get_userdata($fee->student_id)->display_name;
                                echo '<tr class="fee-row">
                                    <td>' . esc_html($student_name) . '</td>
                                    <td>' . esc_html($fee->template_name) . '</td>
                                    <td>' . esc_html($fee->amount) . '</td>
                                    <td>' . esc_html($fee->month_year) . '</td>
                                    <td>' . esc_html($fee->status) . '</td>
                                    <td>' . esc_html($fee->payment_method ?: 'N/A') . '</td>
                                    <td>
                                        <a href="/institute-dashboard/edit-fee/#fee-' . $fee->id . '" class="edit-btn">Edit</a> |
                                        <a href="?action=delete&fee_id=' . $fee->id . '" 
                                           onclick="return confirm(\'Are you sure you want to delete this fee?\')">Delete</a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="7">No fees found for this Educational Center.</td></tr>';
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
add_shortcode('fees_institute_dashboard', 'fees_institute_dashboard_shortcode');