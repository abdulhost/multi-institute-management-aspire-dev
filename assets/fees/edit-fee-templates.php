<?php
if (!defined('ABSPATH')) {
    exit;
}

function edit_fee_templates_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    if (isset($_POST['edit_fee_template']) && wp_verify_nonce($_POST['nonce'], 'edit_fee_template_nonce')) {
        $template_id = intval($_POST['template_id']);
        $name = sanitize_text_field($_POST['template_name']);
        $amount = floatval($_POST['template_amount']);
        $frequency = sanitize_text_field($_POST['template_frequency']);
        $class_id = sanitize_text_field($_POST['class_id']);
        // $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;

        $wpdb->update($wpdb->prefix . 'fee_templates', [
            'name' => $name,
            'amount' => $amount,
            'frequency' => $frequency,
            'class_id' => $class_id,
        ], ['id' => $template_id]);
    }

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <!-- <?php
        // $active_section = 'edit-fee-template';
        // include plugin_dir_path(__FILE__) . '../sidebar.php'; // Adjust path as needed
        ?> -->
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <div class="form-group search-form">
                <div class="input-group">
                    <span class="input-group-addon">Search</span>
                    <input type="text" id="search_text_template" placeholder="Search by Template Details" class="form-control" />
                </div>
            </div>

            <div id="result">
                <h3>Fee Templates List</h3>
                <table id="fee-templates-table" border="1" cellpadding="10" cellspacing="0" width="100%">
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
                                        <a href="#edit-fee-template" class="edit-btn-template"
                                           data-template-id="' . $template->id . '"
                                           data-name="' . esc_attr($template->name) . '"
                                           data-amount="' . $template->amount . '"
                                           data-frequency="' . $template->frequency . '"
                                           data-class-id="' . esc_attr($template->class_id) . '">Edit</a> |
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

    <div id="edit-fee-template-modal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Edit Fee Template</h3>
            <form id="edit-fee-template-form" method="POST">
                <input type="hidden" name="template_id" id="edit_template_id">
                <label for="edit_template_name">Template Name:</label>
                <input type="text" name="template_name" id="edit_template_name" required><br>
                <label for="edit_template_amount">Amount:</label>
                <input type="number" name="template_amount" id="edit_template_amount" step="0.01" required><br>
                <label for="edit_template_frequency">Frequency:</label>
                <select name="template_frequency" id="edit_template_frequency" required>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="annual">Annual</option>
                </select><br>
                <label for="edit_class_id">Class ID (Optional):</label>
                <input type="text" name="class_id" id="edit_class_id"><br>
                <?php wp_nonce_field('edit_fee_template_nonce', 'nonce'); ?>
                <input type="submit" name="edit_fee_template" value="Update Fee Template">
            </form>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#search_text_template').keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $('#fee-templates-table tbody tr').each(function() {
                var name = $(this).find('td').eq(0).text().toLowerCase();
                var amount = $(this).find('td').eq(1).text().toLowerCase();
                var frequency = $(this).find('td').eq(2).text().toLowerCase();
                if (name.includes(searchText) || amount.includes(searchText) || frequency.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });

        $('.edit-btn-template').click(function() {
            var templateId = $(this).data('template-id');
            var name = $(this).data('name');
            var amount = $(this).data('amount');
            var frequency = $(this).data('frequency');
            var classId = $(this).data('class-id');
            $('#edit_template_id').val(templateId);
            $('#edit_template_name').val(name);
            $('#edit_template_amount').val(amount);
            $('#edit_template_frequency').val(frequency);
            $('#edit_class_id').val(classId);
            $('#edit-fee-template-modal').css('display', 'block');
        });

        $('.close-modal').click(function() {
            $('#edit-fee-template-modal').css('display', 'none');
        });

        $(window).click(function(event) {
            if (event.target.id === 'edit-fee-template-modal') {
                $('#edit-fee-template-modal').css('display', 'none');
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('edit_fee_templates_institute_dashboard', 'edit_fee_templates_institute_dashboard_shortcode');