<?php
if (!defined('ABSPATH')) {
    exit;
}

function edit_fees_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    if (isset($_POST['edit_fee']) && wp_verify_nonce($_POST['nonce'], 'edit_fee_nonce')) {
        $fee_id = intval($_POST['fee_id']);
        $status = sanitize_text_field($_POST['status']);
        $cheque_number = sanitize_text_field($_POST['cheque_number']);
        $data = ['status' => $status];
        if ($status === 'paid') {
            $data['paid_date'] = current_time('Y-m-d');
        }
        $wpdb->update($wpdb->prefix . 'student_fees', $data, ['id' => $fee_id]);
        if ($cheque_number) {
            $wpdb->update($wpdb->prefix . 'student_fees', ['cheque_number' => $cheque_number], ['id' => $fee_id]);
        }
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <?php
        // $active_section = 'update-fees';
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
                                        <a href="#edit-fee" class="edit-btn-fee"
                                           data-fee-id="' . $fee->id . '"
                                           data-status="' . $fee->status . '"
                                           data-cheque-number="' . esc_attr($fee->cheque_number) . '">Edit</a> |
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

    <div id="edit-fee-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Edit Fee</h3>
            <form id="edit-fee-form" method="POST">
                <input type="hidden" name="fee_id" id="edit_fee_id">
                <label for="edit_status">Status:</label>
                <select name="status" id="edit_status">
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                </select><br>
                <label for="edit_cheque_number">Cheque Number (if applicable):</label>
                <input type="text" name="cheque_number" id="edit_cheque_number"><br>
                <?php wp_nonce_field('edit_fee_nonce', 'nonce'); ?>
                <input type="submit" name="edit_fee" value="Update Fee">
            </form>
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

        $('.edit-btn-fee').click(function() {
            var feeId = $(this).data('fee-id');
            var status = $(this).data('status');
            var chequeNumber = $(this).data('cheque-number');
            $('#edit_fee_id').val(feeId);
            $('#edit_status').val(status);
            $('#edit_cheque_number').val(chequeNumber);
            $('#edit-fee-modal').css('display', 'block');
        });

        $('.close-modal').click(function() {
            $('#edit-fee-modal').css('display', 'none');
        });

        $(window).click(function(event) {
            if (event.target.id === 'edit-fee-modal') {
                $('#edit-fee-modal').css('display', 'none');
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('edit_fees_institute_dashboard', 'edit_fees_institute_dashboard_shortcode');