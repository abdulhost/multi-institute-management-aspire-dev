<?php
// Create the database table on plugin activation
register_activation_hook(__FILE__, 'create_subjects_table');
function create_subjects_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        subject_id mediumint(9) NOT NULL AUTO_INCREMENT,
        education_center_id varchar(255) NOT NULL,
        subject_name VARCHAR(255) NOT NULL,
        PRIMARY KEY (subject_id),
        INDEX (education_center_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Add a menu item for the admin interface
add_action('admin_menu', 'subjects_management_admin_menu');
function subjects_management_admin_menu() {
    add_menu_page(
        'Subjects Management',
        'Subjects Management',
        'manage_options',
        'subjects-management',
        'subjects_management_admin_page',
        'dashicons-book',
        7
    );
}

// Display the admin interface
function subjects_management_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';

    // Handle form submissions
    if (isset($_POST['submit_subject'])) {
        $data = [
            'education_center_id' => sanitize_text_field($_POST['education_center_id']),
            'subject_name' => sanitize_text_field($_POST['subject_name'])
        ];

        if (!empty($_POST['subject_id'])) {
            $subject_id = intval($_POST['subject_id']);
            $wpdb->update($table_name, $data, ['subject_id' => $subject_id]);
            echo '<div class="updated"><p>Subject updated successfully!</p></div>';
        } else {
            $wpdb->insert($table_name, $data);
            echo '<div class="updated"><p>Subject added successfully!</p></div>';
        }
    }

    // Handle subject deletion
    if (isset($_GET['delete']) && current_user_can('manage_options')) {
        $subject_id = intval($_GET['delete']);
        $wpdb->delete($table_name, ['subject_id' => $subject_id]);
        echo '<div class="updated"><p>Subject deleted successfully!</p></div>';
    }

    // Handle filtering
    $filters = [
        'education_center_id' => isset($_POST['education_center_id']) ? sanitize_text_field($_POST['education_center_id']) : '',
        'subject_name' => isset($_POST['subject_name']) ? sanitize_text_field($_POST['subject_name']) : ''
    ];

    // Build query
    $query = "SELECT * FROM $table_name WHERE 1=1";
    $query_args = [];

    if (!empty($filters['education_center_id'])) {
        $query .= " AND education_center_id = %s";
        $query_args[] = $filters['education_center_id'];
    }
    if (!empty($filters['subject_name'])) {
        $query .= " AND subject_name LIKE %s";
        $query_args[] = '%' . $wpdb->esc_like($filters['subject_name']) . '%';
    }

    if (!empty($query_args)) {
        $query = $wpdb->prepare($query, $query_args);
    }

    $results = $wpdb->get_results($query);

    // Get edit record if selected
    $edit_record = null;
    if (isset($_GET['edit']) && !empty($_GET['edit'])) {
        $edit_id = intval($_GET['edit']);
        $edit_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE subject_id = %d", $edit_id));
    }

    ?>
    <div class="wrap">
        <h1>Subjects Management</h1>

        <!-- Add/Edit Form -->
        <h2><?php echo $edit_record ? 'Edit Subject' : 'Add New Subject'; ?></h2>
        <form method="post" action="">
            <?php if ($edit_record) : ?>
                <input type="hidden" name="subject_id" value="<?php echo esc_attr($edit_record->subject_id); ?>">
            <?php endif; ?>
            <table class="form-table">
                <tr>
                    <th><label for="education_center_id">Education Center ID</label></th>
                    <td><input type="text" name="education_center_id" id="education_center_id" value="<?php echo esc_attr($edit_record->education_center_id ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="subject_name">Subject Name</label></th>
                    <td><input type="text" name="subject_name" id="subject_name" value="<?php echo esc_attr($edit_record->subject_name ?? ''); ?>" required></td>
                </tr>
            </table>
            <?php submit_button($edit_record ? 'Update Subject' : 'Add Subject', 'primary', 'submit_subject'); ?>
        </form>

        <!-- Filter Form -->
        <form method="post" action="">
            <div class="search-filters">
                <input type="text" name="education_center_id" placeholder="Education Center ID" value="<?php echo esc_attr($filters['education_center_id']); ?>">
                <input type="text" name="subject_name" placeholder="Subject Name" value="<?php echo esc_attr($filters['subject_name']); ?>">
                <input type="submit" name="filter" class="button button-primary" value="Filter">
            </div>
        </form>

        <!-- Subjects Table -->
        <div style="overflow-x: auto;">
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Education Center ID</th>
                        <th>Subject Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($results)) : ?>
                        <?php foreach ($results as $row) : ?>
                            <tr>
                                <td><?php echo esc_html($row->subject_id); ?></td>
                                <td><?php echo esc_html($row->education_center_id); ?></td>
                                <td><?php echo esc_html($row->subject_name); ?></td>
                                <td>
                                    <a href="?page=subjects-management&edit=<?php echo esc_attr($row->subject_id); ?>" class="button">Edit</a>
                                    <a href="?page=subjects-management&delete=<?php echo esc_attr($row->subject_id); ?>" class="button" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4">No subjects found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

// Enqueue styles and scripts
add_action('admin_enqueue_scripts', 'subjects_management_admin_styles');
function subjects_management_admin_styles($hook) {
    if ($hook !== 'toplevel_page_subjects-management') {
        return;
    }
    
    $custom_css = "
        .search-filters input, .search-filters select {
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .wp-list-table th, .wp-list-table td {
            padding: 8px;
            text-align: center;
        }
        .wp-list-table th {
            background-color: #f5f5f5;
        }
        .wrap {
            overflow-x: auto;
        }
    ";
    wp_add_inline_style('wp-admin', $custom_css);
}

// Insert initial data (optional)
register_activation_hook(__FILE__, 'insert_initial_subjects');
function insert_initial_subjects() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'subjects';
    
    $initial_subjects = [
        ['education_center_id' => '1', 'subject_name' => 'Mathematics'],
        ['education_center_id' => '1', 'subject_name' => 'Science'],
        ['education_center_id' => '1', 'subject_name' => 'English'],
        ['education_center_id' => '1', 'subject_name' => 'History'],
        ['education_center_id' => 'AFC46B9CEE17', 'subject_name' => 'Physical Education']
    ];

    foreach ($initial_subjects as $subject) {
        $wpdb->insert($table_name, $subject);
    }
}