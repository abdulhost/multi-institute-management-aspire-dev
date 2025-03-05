<?php
if (!defined('ABSPATH')) {
    exit;
}

function delete_fees_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    // Handle deletion if form is submitted
    if (isset($_POST['delete_fee_id']) && !empty($_POST['delete_fee_id'])) {
        $fee_id = intval($_POST['delete_fee_id']);
        $nonce = $_POST['_wpnonce'] ?? '';
        
        if (wp_verify_nonce($nonce, 'delete_fee_nonce')) {
            $wpdb->delete(
                $wpdb->prefix . 'student_fees',
                ['id' => $fee_id]
            );
        }
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
                                ?>
                                <tr class="fee-row">
                                    <td><?php echo esc_html($student_name); ?></td>
                                    <td><?php echo esc_html($fee->template_name); ?></td>
                                    <td><?php echo esc_html($fee->amount); ?></td>
                                    <td><?php echo esc_html($fee->month_year); ?></td>
                                    <td><?php echo esc_html($fee->status); ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Are you sure you want to delete this fee?');">
                                            <input type="hidden" name="delete_fee_id" value="<?php echo $fee->id; ?>">
                                            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('delete_fee_nonce'); ?>">
                                            <button type="submit" class="delete-fee">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
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

    <script>
    // Search functionality without jQuery
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search_text_fee');
        const rows = document.querySelectorAll('#fees-table tbody tr');

        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            
            rows.forEach(row => {
                const student = row.cells[0].textContent.toLowerCase();
                const template = row.cells[1].textContent.toLowerCase();
                const amount = row.cells[2].textContent.toLowerCase();
                
                if (student.includes(searchText) || template.includes(searchText) || amount.includes(searchText)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('delete_fees_institute_dashboard', 'delete_fees_institute_dashboard_shortcode');
?>