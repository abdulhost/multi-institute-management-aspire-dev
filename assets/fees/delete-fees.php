<?php
if (!defined('ABSPATH')) {
    exit;
}

function delete_fees_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['fee_id'])) {
        $fee_id = intval($_GET['fee_id']);
        $wpdb->delete($wpdb->prefix . 'student_fees', ['id' => $fee_id]);
        wp_redirect(home_url('/institute-dashboard/#delete-fees'));
        exit;
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <?php
        // $active_section = 'delete-fees';
        // include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?> -->
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <div class="form-group search-form">
                <div class="input-group">
                    <span class="input-group-addon">Search</span>
                    <input type="text" id="search_text_fee" placeholder="Search by Fee Details" class="form-control" />
                </div>
            </div>

            <div id="result">
                <h3>Fee List</h3>
                <table id="fees-table" border="1" cellpadding="10" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Template</th>
                            <th>Amount</th>
                            <th>Month</th>
                            <th>Status</th>
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
                                    <td>
                                        <a href="?action=delete&fee_id=' . $fee->id . '" 
                                           onclick="return confirm(\'Are you sure you want to delete this fee?\')">Delete</a>
                                    </td>
                                </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="6">No fees found for this Educational Center.</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#search_text_fee').keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $('#fees-table tbody tr').each(function() {
                var student = $(this).find('td').eq(0).text().toLowerCase();
                var template = $(this).find('td').eq(1).text().toLowerCase();
                var amount = $(this).find('td').eq(2).text().toLowerCase();
                if (student.includes(searchText) || template.includes(searchText) || amount.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('delete_fees_institute_dashboard', 'delete_fees_institute_dashboard_shortcode');