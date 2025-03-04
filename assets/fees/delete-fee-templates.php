<?php
if (!defined('ABSPATH')) {
    exit;
}

function delete_fee_templates_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        return '<p>No Educational Center found.</p>';
    }

    ob_start();
    ?>
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
                                    <a href="' . home_url('/institute-dashboard/fees/?section=delete-fee-template&action=delete&template_id=' . $template->id) . '" 
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
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('delete_fee_templates_institute_dashboard', 'delete_fee_templates_institute_dashboard_shortcode');