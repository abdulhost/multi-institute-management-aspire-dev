
function fees_institute_dashboard_shortcode_2() {
    global $wpdb;

    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        return '<p>No Educational Center found.</p>';
    }

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['fee_id'])) {
        $fee_id = intval($_GET['fee_id']);
        $wpdb->delete($wpdb->prefix . 'student_fees', ['id' => $fee_id]);
        wp_redirect(home_url('/institute-dashboard/?section=fees'));
        exit;
    }

    ob_start();
    ?>
    <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
        <a href="?section=add-fees">+ Add Fee</a>
        <?php
        $args = ['table_id' => 'fees-table', 'search_input_id' => 'search_text'];
        include plugin_dir_path(__FILE__) . 'assets/searchbar.php';
        ?>
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
                                    <a href="?section=edit-fee#fee-' . $fee->id . '" class="edit-btn">Edit</a> |
                                    <a href="?section=fees&action=delete&fee_id=' . $fee->id . '" 
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
    <?php
    return ob_get_clean();
}