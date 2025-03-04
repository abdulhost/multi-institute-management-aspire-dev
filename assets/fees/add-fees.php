<?php
if (!defined('ABSPATH')) {
    exit;
}

function add_fees_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    if (isset($_POST['add_fee']) && wp_verify_nonce($_POST['nonce'], 'add_fee_nonce')) {
        $student_id = intval($_POST['student_id']);
        $template_id = intval($_POST['template_id']);
        $months = array_map('sanitize_text_field', $_POST['months']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $cheque_number = $payment_method === 'cheque' ? sanitize_text_field($_POST['cheque_number']) : '';

        foreach ($months as $month) {
            $template = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}fee_templates WHERE id = %d AND education_center_id = %d", 
                $template_id, $education_center_id
            ));
            $wpdb->insert($wpdb->prefix . 'student_fees', [
                'student_id' => $student_id,
                'education_center_id' => $education_center_id,
                'template_id' => $template_id,
                'amount' => $template->amount,
                'month_year' => $month,
                'status' => $payment_method === 'cheque' ? 'pending' : 'paid',
                'paid_date' => $payment_method === 'cheque' ? null : current_time('Y-m-d'),
                'payment_method' => $payment_method,
                'cheque_number' => $cheque_number,
            ]);
        }
        return '<p>' . ($payment_method === 'cheque' ? 'Cheque submitted! Awaiting verification.' : 'Fee added successfully!') . '</p>';
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <?php
        // $active_section = 'add-fees';
        // include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?> -->
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <h3>Add Fee</h3>
            <form method="POST">
                <div class="form-section">
                    <div class="section-header" onclick="toggleSection('fee-details')">
                        <h4>Fee Details</h4>
                        <span class="toggle-icon">▼</span>
                    </div>
                    <div class="section-content" id="fee-details">
                        <label for="student_id">Student:</label>
                        <select name="student_id" required>
                            <?php
                            $students = get_users(['role' => 'subscriber']);
                            foreach ($students as $student) {
                                echo '<option value="' . $student->ID . '">' . esc_html($student->display_name) . '</option>';
                            }
                            ?>
                        </select><br>
                        <label for="template_id">Fee Template:</label>
                        <select name="template_id" required>
                            <?php
                            $templates = $wpdb->get_results($wpdb->prepare(
                                "SELECT * FROM {$wpdb->prefix}fee_templates WHERE education_center_id = %d", 
                                $education_center_id
                            ));
                            foreach ($templates as $template) {
                                echo '<option value="' . $template->id . '">' . esc_html($template->name) . ' ($' . $template->amount . ')</option>';
                            }
                            ?>
                        </select><br>
                        <label>Months:</label><br>
                        <?php for ($i = 0; $i < 12; $i++) : $month = date('Y-m', strtotime("+$i months")); ?>
                            <label><input type="checkbox" name="months[]" value="<?php echo $month; ?>"> <?php echo date('F Y', strtotime($month)); ?></label><br>
                        <?php endfor; ?>
                        <label for="payment_method">Payment Method:</label>
                        <select name="payment_method" required>
                            <option value="cod">Cash on Delivery</option>
                            <option value="cheque">Cheque</option>
                        </select><br>
                        <input type="text" name="cheque_number" id="cheque_number" placeholder="Cheque Number" style="display:none;">
                    </div>
                </div>
                <?php wp_nonce_field('add_fee_nonce', 'nonce'); ?>
                <input type="submit" name="add_fee" value="Add Fee">
            </form>

            <script>
            function toggleSection(sectionId) {
                var content = document.getElementById(sectionId);
                content.style.display = content.style.display === 'none' ? 'block' : 'none';
            }
            document.querySelector('[name="payment_method"]').addEventListener('change', function() {
                document.getElementById('cheque_number').style.display = this.value === 'cheque' ? 'block' : 'none';
            });
            </script>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('add_fees_institute_dashboard', 'add_fees_institute_dashboard_shortcode');