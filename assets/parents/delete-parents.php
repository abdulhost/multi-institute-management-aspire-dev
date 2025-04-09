<?php
// parent-list.php
function delete_parents_institute_dashboard_shortcode($atts) {
 
    if (is_teacher($atts)) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
    
    if (!$educational_center_id) {
        wp_redirect(home_url('/login'));
        exit();
    }

    // $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
    require_once(ABSPATH . 'wp-admin/includes/image.php');

    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <?php
        if (is_teacher($atts)) { 
        } else {
            echo render_admin_header(wp_get_current_user());
          if (!is_center_subscribed($educational_center_id)) {
              return render_subscription_expired_message($educational_center_id);
          }
        $active_section = 'delete-parent';
        include(plugin_dir_path(__FILE__) . '../sidebar.php');}
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
                                       
                                      <button class="button button-secondary delete-parent-btn"
                                                data-parent-post-id="' . esc_attr($parent->ID) . '"
                                                data-edu-center-id="' . esc_attr($educational_center_id) . '"
                                                data-nonce="' . wp_create_nonce('delete_parent_' . $parent->ID) . '">Delete</button>
                                        <span class="delete-message" style="display: none; margin-left: 10px;"></span>
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
        $(document).on('click', '.delete-parent-btn', function(e) {
            e.preventDefault();

            var $button = $(this);
            var parentPostId = $button.data('parent-post-id');
            var eduCenterId = $button.data('edu-center-id');
            var nonce = $button.data('nonce');
            var $row = $button.closest('tr');
            var $message = $row.find('.delete-message');

            if (!confirm('Are you sure you want to delete this parent?')) {
                return;
            }

            $button.prop('disabled', true);
            $message.hide().removeClass('success error').text('Deleting...').show();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'delete_parent',
                    parent_post_id: parentPostId,
                    educational_center_id: eduCenterId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $message.text(response.data).addClass('success').show();
                        setTimeout(function() {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                if ($('#parents-table tbody tr').length === 0) {
                                    $('#parents-table').replaceWith('<tr><td colspan="4">No parents found for this Educational Center.</td></tr>');
                                }
                            });
                        }, 1000);
                    } else {
                        $message.text(response.data).addClass('error').show();
                        $button.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    $message.text('Error occurred while deleting: ' + error).addClass('error').show();
                    $button.prop('disabled', false);
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