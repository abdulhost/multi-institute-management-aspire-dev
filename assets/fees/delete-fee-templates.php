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

    // Handle deletion if form is submitted
    if (isset($_POST['delete_template_id']) && !empty($_POST['delete_template_id'])) {
        $template_id = intval($_POST['delete_template_id']);
        $nonce = $_POST['_wpnonce'] ?? '';
        
        if (wp_verify_nonce($nonce, 'delete_fee_template_nonce')) {
            $deleted = $wpdb->delete(
                $wpdb->prefix . 'fee_templates',
                ['id' => $template_id]
            );
        }
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
                            ?>
                            <tr class="fee-template-row">
                                <td><?php echo esc_html($template->name); ?></td>
                                <td><?php echo esc_html($template->amount); ?></td>
                                <td><?php echo esc_html($template->frequency); ?></td>
                                <td><?php echo esc_html($template->class_id ?: 'N/A'); ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Are you sure you want to delete this fee template?');">
                                        <input type="hidden" name="delete_template_id" value="<?php echo $template->id; ?>">
                                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('delete_fee_template_nonce'); ?>">
                                        <button type="submit" class="delete-template">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="5">No fee templates found for this Educational Center.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    // Basic search functionality without jQuery dependency
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search_text_template');
        const rows = document.querySelectorAll('#fee-templates-table tbody tr');

        searchInput.addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            
            rows.forEach(row => {
                const name = row.cells[0].textContent.toLowerCase();
                const amount = row.cells[1].textContent.toLowerCase();
                const frequency = row.cells[2].textContent.toLowerCase();
                
                if (name.includes(searchText) || amount.includes(searchText) || frequency.includes(searchText)) {
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
add_shortcode('delete_fee_templates_institute_dashboard', 'delete_fee_templates_institute_dashboard_shortcode');
?>