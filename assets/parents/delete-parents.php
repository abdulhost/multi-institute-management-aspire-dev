<?php
// parent-list.php
function delete_parents_institute_dashboard_shortcode() {
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    $educational_center = get_posts(array(
        'post_type' => 'educational-center',
        'meta_key' => 'admin_id',
        'meta_value' => $admin_id,
        'posts_per_page' => 1,
    ));

    if (empty($educational_center)) {
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        $active_section = 'delete-parent';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');
        ?>
        <div class="form-container attendance-entry-wrapper attendance-content-wrapper">
            <div class="form-group search-form">
                <div class="input-group">
                    <span class="input-group-addon">Search</span>
                    <input type="text" id="search_text_parent" placeholder="Search by Parent Details" class="form-control" />
                </div>
            </div>

            <div id="result">
                <h3>Parent List</h3>
                <table id="parents-table" border="1" cellpadding="10" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Parent ID</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $parents = get_posts(array(
                            'post_type' => 'parent',
                            'meta_key' => 'educational_center_id',
                            'meta_value' => $educational_center_id,
                            'posts_per_page' => -1,
                        ));

                        if (!empty($parents)) {
                            foreach ($parents as $parent) {
                                $parent_id = get_post_meta($parent->ID, 'parent_id', true);
                                $parent_email = get_post_meta($parent->ID, 'parent_email', true);
                                $parent_phone_number = get_post_meta($parent->ID, 'parent_phone_number', true);

                                echo '<tr class="parent-row">
                                    <td>' . esc_html($parent_id) . '</td>
                                    <td>' . esc_html($parent_email) . '</td>
                                    <td>' . esc_html($parent_phone_number) . '</td>
                                    <td>
                                        <a href="?action=delete&parent_post_id=' . $parent->ID . '" onclick="return confirm(\'Are you sure you want to delete this parent?\')">Delete</a>
                                    </td>
                                  </tr>';
                            }
                        } else {
                            echo '<tr><td colspan="4">No parents found for this Educational Center.</td></tr>';
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
        $('#search_text_parent').keyup(function() {
            var searchText = $(this).val().toLowerCase();
            $('#parents-table tbody tr').each(function() {
                var parentID = $(this).find('td').eq(0).text().toLowerCase();
                var parentEmail = $(this).find('td').eq(1).text().toLowerCase();
                var parentPhone = $(this).find('td').eq(2).text().toLowerCase();
                if (parentID.includes(searchText) || parentEmail.includes(searchText) || parentPhone.includes(searchText)) {
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

add_shortcode('delete_parents_institute_dashboard', 'delete_parents_institute_dashboard_shortcode');
?>