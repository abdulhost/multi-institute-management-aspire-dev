<?php

// Create the database table on plugin activation
register_activation_hook(__FILE__, 'create_attendance_table');
function create_attendance_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        sa_id mediumint(9) NOT NULL AUTO_INCREMENT,
        education_center_id varchar(255) NOT NULL,
        student_id VARCHAR(50) NOT NULL,
        student_name VARCHAR(255) NOT NULL,
        class varchar(255) NOT NULL,
        section text NOT NULL,
        date DATE NOT NULL,
        status VARCHAR(50) NOT NULL,
        teacher_id VARCHAR(50) NOT NULL,
        subject VARCHAR(50) NOT NULL,
        PRIMARY KEY (sa_id),
        INDEX (student_id, date)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Add a menu item for the admin interface
add_action('admin_menu', 'attendance_management_admin_menu');
function attendance_management_admin_menu() {
    add_menu_page(
        'Attendance Management',
        'Attendance Management',
        'manage_options',
        'attendance-management',
        'attendance_management_admin_page',
        'dashicons-list-view',
        6
    );
}

// Display the admin interface
function attendance_management_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';

    // Handle form submissions
    if (isset($_POST['submit_attendance'])) {
        $data = [
            'education_center_id' => sanitize_text_field($_POST['education_center_id']),
            'student_id' => sanitize_text_field($_POST['student_id']),
            'student_name' => sanitize_text_field($_POST['student_name']),
            'class' => sanitize_text_field($_POST['class']),
            'section' => sanitize_text_field($_POST['section']),
            'date' => sanitize_text_field($_POST['date']),
            'status' => sanitize_text_field($_POST['status']),
            'teacher_id' => sanitize_text_field($_POST['teacher_id'])
        ];

        if (!empty($_POST['sa_id'])) {
            $sa_id = intval($_POST['sa_id']);
            $wpdb->update($table_name, $data, ['sa_id' => $sa_id]);
            echo '<div class="updated"><p>Attendance record updated successfully!</p></div>';
        } else {
            $wpdb->insert($table_name, $data);
            echo '<div class="updated"><p>Attendance record added successfully!</p></div>';
        }
    }

    // Handle attendance deletion
    if (isset($_GET['delete']) && current_user_can('manage_options')) {
        $sa_id = intval($_GET['delete']);
        $wpdb->delete($table_name, ['sa_id' => $sa_id]);
        echo '<div class="updated"><p>Attendance record deleted successfully!</p></div>';
    }

    // Handle filtering
    $filters = [
        'student_id' => isset($_POST['student_id']) ? sanitize_text_field($_POST['student_id']) : '',
        'student_name' => isset($_POST['student_name']) ? sanitize_text_field($_POST['student_name']) : '',
        'class' => isset($_POST['class']) ? sanitize_text_field($_POST['class']) : '',
        'section' => isset($_POST['section']) ? sanitize_text_field($_POST['section']) : '',
        'month' => isset($_POST['month']) ? intval($_POST['month']) : date('n'),
        'year' => isset($_POST['year']) ? intval($_POST['year']) : date('Y')
    ];

    // Build query
    $query = "SELECT * FROM $table_name WHERE 1=1";
    $query_args = [];

    if (!empty($filters['student_id'])) {
        $query .= " AND student_id = %s";
        $query_args[] = $filters['student_id'];
    }
    if (!empty($filters['student_name'])) {
        $query .= " AND student_name LIKE %s";
        $query_args[] = '%' . $wpdb->esc_like($filters['student_name']) . '%';
    }
    if (!empty($filters['class'])) {
        $query .= " AND class = %s";
        $query_args[] = $filters['class'];
    }
    if (!empty($filters['section'])) {
        $query .= " AND section = %s";
        $query_args[] = $filters['section'];
    }
    if ($filters['month'] > 0 && $filters['year'] > 0) {
        $query .= " AND MONTH(date) = %d AND YEAR(date) = %d";
        $query_args[] = $filters['month'];
        $query_args[] = $filters['year'];
    }

    if (!empty($query_args)) {
        $query = $wpdb->prepare($query, $query_args);
    }

    $results = $wpdb->get_results($query);

    // Process results
    $student_data = [];
    $days_in_month = ($filters['month'] > 0 && $filters['year'] > 0) ? cal_days_in_month(CAL_GREGORIAN, $filters['month'], $filters['year']) : 31;

    foreach ($results as $row) {
        $student_key = $row->student_id;
        if (!isset($student_data[$student_key])) {
            $student_data[$student_key] = [
                'records' => [],
                'name' => $row->student_name,
                'class' => $row->class,
                'section' => $row->section,
                'attendance' => array_fill(1, $days_in_month, ''),
                'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0]
            ];
        }
        $student_data[$student_key]['records'][$row->sa_id] = [
            'sa_id' => $row->sa_id,
            'date' => $row->date,
            'status' => $row->status
        ];
        $day = (int) date('j', strtotime($row->date));
        $status_short = '';
        switch ($row->status) {
            case 'Present': $status_short = 'P'; $student_data[$student_key]['counts']['P']++; break;
            case 'Late': $status_short = 'L'; $student_data[$student_key]['counts']['L']++; break;
            case 'Absent': $status_short = 'A'; $student_data[$student_key]['counts']['A']++; break;
            case 'Full Day': $status_short = 'F'; $student_data[$student_key]['counts']['F']++; break;
            case 'Holiday': $status_short = 'H'; $student_data[$student_key]['counts']['H']++; break;
        }
        $student_data[$student_key]['attendance'][$day] = $status_short;
    }

    $classes = $wpdb->get_col("SELECT DISTINCT class FROM $table_name");
    $sections = $wpdb->get_col("SELECT DISTINCT section FROM $table_name");

    // Get edit record if selected
    $edit_record = null;
    if (isset($_POST['select_record']) && !empty($_POST['record_id'])) {
        $edit_id = intval($_POST['record_id']);
        $edit_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE sa_id = %d", $edit_id));
    }

    ?>
    <div class="wrap">
        <!-- Edit Selection Form -->
        <?php if (isset($_GET['edit_student'])) : ?>
            <?php $edit_student_id = sanitize_text_field($_GET['edit_student']); ?>
            <?php if (isset($student_data[$edit_student_id])) : ?>
                <div class="edit-selection">
                    <h2>Select Attendance Record to Edit for <?php echo esc_html($student_data[$edit_student_id]['name']); ?></h2>
                    <form method="post" action="">
                        <select name="record_id" required>
                            <option value="">Select a date</option>
                            <?php foreach ($student_data[$edit_student_id]['records'] as $record) : ?>
                                <option value="<?php echo esc_attr($record['sa_id']); ?>">
                                    <?php echo esc_html($record['date'] . ' - ' . $record['status']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="submit" name="select_record" class="button button-primary" value="Edit Selected Record">
                    </form>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <h2><?php echo $edit_record ? 'Edit Attendance' : 'Add Attendance'; ?></h2>
        <form method="post" action="">
            <?php if ($edit_record) : ?>
                <input type="hidden" name="sa_id" value="<?php echo esc_attr($edit_record->sa_id); ?>">
            <?php endif; ?>
            <table class="form-table">
                <tr>
                    <th><label for="education_center_id">Education Center ID</label></th>
                    <td><input type="text" name="education_center_id" id="education_center_id" value="<?php echo esc_attr($edit_record->education_center_id ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="student_id">Student ID</label></th>
                    <td><input type="text" name="student_id" id="student_id" value="<?php echo esc_attr($edit_record->student_id ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="student_name">Student Name</label></th>
                    <td><input type="text" name="student_name" id="student_name" value="<?php echo esc_attr($edit_record->student_name ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="class">Class</label></th>
                    <td><input type="text" name="class" id="class" value="<?php echo esc_attr($edit_record->class ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="section">Section</label></th>
                    <td><input type="text" name="section" id="section" value="<?php echo esc_attr($edit_record->section ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="date">Date</label></th>
                    <td><input type="date" name="date" id="date" value="<?php echo esc_attr($edit_record->date ?? ''); ?>" required></td>
                </tr>
                <tr>
                    <th><label for="status">Status</label></th>
                    <td>
                        <select name="status" id="status" required>
                            <option value="Present" <?php selected($edit_record->status ?? '', 'Present'); ?>>Present</option>
                            <option value="Late" <?php selected($edit_record->status ?? '', 'Late'); ?>>Late</option>
                            <option value="Absent" <?php selected($edit_record->status ?? '', 'Absent'); ?>>Absent</option>
                            <option value="Full Day" <?php selected($edit_record->status ?? '', 'Full Day'); ?>>Full Day</option>
                            <option value="Holiday" <?php selected($edit_record->status ?? '', 'Holiday'); ?>>Holiday</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="teacher_id">Teacher ID</label></th>
                    <td><input type="text" name="teacher_id" id="teacher_id" value="<?php echo esc_attr($edit_record->teacher_id ?? ''); ?>" required></td>
                </tr>
            </table>
            <?php submit_button($edit_record ? 'Update Attendance' : 'Add Attendance', 'primary', 'submit_attendance'); ?>
        </form>

        <h1>Attendance Management</h1>
        <form method="post" action="">
            <div class="search-filters">
                <input type="text" name="student_id" placeholder="Student ID" value="<?php echo esc_attr($filters['student_id']); ?>">
                <input type="text" name="student_name" placeholder="Student Name" value="<?php echo esc_attr($filters['student_name']); ?>">
                <select name="class">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $cls) : ?>
                        <option value="<?php echo esc_attr($cls); ?>" <?php selected($filters['class'], $cls); ?>><?php echo esc_html($cls); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="section">
                    <option value="">All Sections</option>
                    <?php foreach ($sections as $sec) : ?>
                        <option value="<?php echo esc_attr($sec); ?>" <?php selected($filters['section'], $sec); ?>><?php echo esc_html($sec); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="month">
                    <option value="0">All Months</option>
                    <?php for ($m = 1; $m <= 12; $m++) : ?>
                        <option value="<?php echo $m; ?>" <?php selected($filters['month'], $m); ?>><?php echo date('F', mktime(0, 0, 0, $m, 10)); ?></option>
                    <?php endfor; ?>
                </select>
                <select name="year">
                    <option value="0">All Years</option>
                    <?php for ($y = date('Y'); $y >= 2020; $y--) : ?>
                        <option value="<?php echo $y; ?>" <?php selected($filters['year'], $y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>
                <input type="submit" name="filter" class="button button-primary" value="Filter">
            </div>
        </form>

        <div style="overflow-x: auto;">
            <table class="wp-list-table widefat striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Admission No</th>
                        <th>Class</th>
                        <th>P</th>
                        <th>L</th>
                        <th>A</th>
                        <th>F</th>
                        <th>H</th>
                        <th>%</th>
                        <th>Actions</th>
                        <?php if ($filters['month'] > 0 && $filters['year'] > 0) : ?>
                            <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                                <?php
                                $date = sprintf('%04d-%02d-%02d', $filters['year'], $filters['month'], $day);
                                $day_name = date('D', strtotime($date));
                                ?>
                                <th><?php echo "$day<br>$day_name"; ?></th>
                            <?php endfor; ?>
                        <?php endif; ?>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($student_data)) : ?>
                        <?php foreach ($student_data as $student_id => $data) : ?>
                            <?php
                            $total_days = ($filters['month'] > 0 && $filters['year'] > 0) ? $days_in_month - $data['counts']['H'] : array_sum($data['counts']);
                            $percent = $total_days > 0 ? round(($data['counts']['P'] / $total_days) * 100) : 0;
                            ?>
                            <tr>
                                <td><?php echo esc_html($data['name']); ?></td>
                                <td><?php echo esc_html($student_id); ?></td>
                                <td><?php echo esc_html($data['class'] . ' - ' . $data['section']); ?></td>
                                <td><?php echo esc_html($data['counts']['P']); ?></td>
                                <td><?php echo esc_html($data['counts']['L']); ?></td>
                                <td><?php echo esc_html($data['counts']['A']); ?></td>
                                <td><?php echo esc_html($data['counts']['F']); ?></td>
                                <td><?php echo esc_html($data['counts']['H']); ?></td>
                                <td><?php echo esc_html($percent . '%'); ?></td>
                                <td>
                                    <button class="button edit-attendance" data-student-id="<?php echo esc_attr($student_id); ?>">Edit</button>
                                    <a href="?page=attendance-management&delete=<?php echo esc_attr(key($data['records'])); ?>" class="button" onclick="return confirm('Are you sure you want to delete this record?');">Delete</a>
                                </td>
                                <?php if ($filters['month'] > 0 && $filters['year'] > 0) : ?>
                                    <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                                        <td><?php echo esc_html($data['attendance'][$day] ?? ''); ?></td>
                                    <?php endfor; ?>
                                <?php endif; ?>
                               
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="<?php echo ($filters['month'] > 0 && $filters['year'] > 0) ? (10 + $days_in_month) : 10; ?>">No records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
    </div>
    <?php
}

// Enqueue styles and scripts
add_action('admin_enqueue_scripts', 'attendance_management_admin_styles');
function attendance_management_admin_styles($hook) {
    if ($hook !== 'toplevel_page_attendance-management') {
        return;
    }
    
    wp_enqueue_style('attendance-management-styles', plugin_dir_url(__FILE__) . 'attendance-styles.css');
    
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
        .edit-selection {
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border: 1px solid #ddd;
        }
        .edit-selection select {
            margin-right: 10px;
        }
            
    ";
    wp_add_inline_style('attendance-management-styles', $custom_css);

    // Add JavaScript for edit button handling
    wp_enqueue_script('attendance-management-script', plugin_dir_url(__FILE__) . 'attendance-script.js', ['jquery'], null, true);
    wp_add_inline_script('attendance-management-script', "
        jQuery(document).ready(function($) {
            $('.edit-attendance').on('click', function(e) {
                e.preventDefault();
                var studentId = $(this).data('student-id');
                window.location.href = '?page=attendance-management&edit_student=' + encodeURIComponent(studentId);
            });
        });
    ");
}