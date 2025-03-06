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

//fees ,transport
add_action('admin_menu', 'my_education_erp_add_admin_menu');

function my_education_erp_add_admin_menu() {
    add_menu_page('Fee Management', 'Fee Management', 'manage_options', 'fee-management', 'my_education_erp_render_admin_page', 'dashicons-money-alt');
}

function my_education_erp_render_admin_page() {
    global $wpdb;
    if (isset($_POST['record_payment']) && wp_verify_nonce($_POST['nonce'], 'record_payment_nonce')) {
        $student_id = intval($_POST['student']);
        $template_id = intval($_POST['template']);
        $months = array_map('sanitize_text_field', $_POST['months']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $cheque_number = $payment_method == 'cheque' ? sanitize_text_field($_POST['cheque_number']) : '';
        
        $template = my_education_erp_get_template($template_id);
        $function = $template->fee_type == 'transport' ? 'my_education_erp_record_transport_fee_payment' : 'my_education_erp_record_fee_payment';
        foreach ($months as $month) {
            $function($student_id, $template_id, $month, $payment_method, $cheque_number);
        }
        echo '<div class="updated"><p>Payment recorded!</p></div>';
    }

    if (isset($_GET['action']) && $_GET['action'] == 'verify') {
        $fee_id = intval($_GET['fee_id']);
        $table = $_GET['type'] == 'transport' ? 'transport_fees' : 'student_fees';
        $wpdb->update($wpdb->prefix . $table, ['status' => 'paid', 'paid_date' => current_time('Y-m-d')], ['id' => $fee_id]);
        echo '<div class="updated"><p>Cheque verified!</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Record Fee Payment</h1>
        <form method="post">
            <label>Student:</label>
            <select name="student" required>
                <?php
                $students = get_users(['role' => 'subscriber']);
                foreach ($students as $s) {
                    echo '<option value="' . $s->ID . '">' . $s->display_name . '</option>';
                }
                ?>
            </select><br>
            <label>Template:</label>
            <select name="template" required>
                <?php
                $templates = my_education_erp_get_templates();
                foreach ($templates as $t) {
                    echo '<option value="' . $t->id . '">' . $t->name . '</option>';
                }
                ?>
            </select><br>
            <label>Months:</label><br>
            <?php
            for ($i = 0; $i < 12; $i++) {
                $month = date('Y-m', strtotime("+$i months"));
                echo '<label><input type="checkbox" name="months[]" value="' . $month . '"> ' . date('F Y', strtotime($month)) . '</label><br>';
            }
            ?>
            <label>Payment Method:</label>
            <select name="payment_method" required>
                <option value="cash">Cash</option>
                <option value="cheque">Cheque</option>
            </select><br>
            <input type="text" name="cheque_number" placeholder="Cheque Number" style="display:none;" id="cheque_number">
            <script>
                document.querySelector('[name="payment_method"]').addEventListener('change', function() {
                    document.getElementById('cheque_number').style.display = this.value === 'cheque' ? 'block' : 'none';
                });
            </script>
            <?php wp_nonce_field('record_payment_nonce', 'nonce'); ?>
            <input type="submit" name="record_payment" value="Record Payment" class="button-primary">
        </form>

        <h2>Pending Cheque Payments</h2>
        <?php
        $pending = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}student_fees WHERE payment_method = 'cheque' AND status = 'pending'");
        foreach ($pending as $fee) {
            echo '<p>Student: ' . get_userdata($fee->student_id)->display_name . ' | Cheque: ' . $fee->cheque_number . ' | <a href="?page=fee-management&action=verify&fee_id=' . $fee->id . '&type=tuition">Verify</a></p>';
        }
        $pending_transport = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}transport_fees WHERE payment_method = 'cheque' AND status = 'pending'");
        foreach ($pending_transport as $fee) {
            echo '<p>Student: ' . get_userdata($fee->student_id)->display_name . ' | Cheque: ' . $fee->cheque_number . ' | <a href="?page=fee-management&action=verify&fee_id=' . $fee->id . '&type=transport">Verify</a></p>';
        }
        ?>
    </div>
    <?php
}


// Add admin menu items
add_action('admin_menu', 'education_erp_admin_menu');
function education_erp_admin_menu() {
    // Parent menu
    add_menu_page(
        'Education ERP',
        'Education ERP',
        'manage_options',
        'education-erp',
        'education_erp_dashboard_page', // No callback for parent
        'dashicons-admin-generic',
        6
    );

    // Submenu: Fees
    add_submenu_page(
        'education-erp',
        'Fees Management',
        'Fees',
        'manage_options',
        'education-erp-fees',
        'education_erp_fees_page'
    );

    // Submenu: Fees Templates
    add_submenu_page(
        'education-erp',
        'Fees Templates Management',
        'Fees Templates',
        'manage_options',
        'education-erp-fees-templates',
        'education_erp_fees_templates_page'
    );

    // Submenu: Transport Enrollments
    add_submenu_page(
        'education-erp',
        'Transport Enrollments Management',
        'Transport Enrollments',
        'manage_options',
        'education-erp-transport-enrollments',
        'education_erp_transport_enrollments_page'
    );

    // Submenu: Transport Fees
    add_submenu_page(
        'education-erp',
        'Transport Fees Management',
        'Transport Fees',
        'manage_options',
        'education-erp-transport-fees',
        'education_erp_transport_fees_page'
    );
}
// Dummy Dashboard Page for Parent Menu
function education_erp_dashboard_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    ?>
    <div class="wrap education-erp-dashboard">
        <h1 class="wp-heading-inline">Education ERP Dashboard</h1>
        <div class="welcome-panel">
            <h2>Welcome to Education ERP Developed By Aspire Dev</h2>
            <p>This is the main dashboard for managing educational resources. Use the menu items below "Education ERP" to access specific sections:</p>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=education-erp-fees'); ?>">Fees</a> - Manage student fees.</li>
                <li><a href="<?php echo admin_url('admin.php?page=education-erp-fees-templates'); ?>">Fees Templates</a> - Configure fee templates.</li>
                <li><a href="<?php echo admin_url('admin.php?page=education-erp-transport-enrollments'); ?>">Transport Enrollments</a> - Handle transport enrollments.</li>
                <li><a href="<?php echo admin_url('admin.php?page=education-erp-transport-fees'); ?>">Transport Fees</a> - Manage transport-related fees.</li>
            </ul>
        </div>
    </div>
    <style>
        .education-erp-dashboard .welcome-panel {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        .education-erp-dashboard .welcome-panel h2 {
            margin-top: 0;
        }
        .education-erp-dashboard .welcome-panel ul {
            list-style: disc;
            margin-left: 20px;
        }
    </style>
    <?php
}
// Fees Page
function education_erp_fees_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    global $wpdb;
    render_fees_ui($wpdb);
}

// Fees Templates Page
function education_erp_fees_templates_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    global $wpdb;
    render_fees_templates_ui($wpdb);
}

// Transport Enrollments Page
function education_erp_transport_enrollments_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    global $wpdb;
    render_transport_enrollments_ui($wpdb);
}

// Transport Fees Page
function education_erp_transport_fees_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    global $wpdb;
    render_transport_fees_ui($wpdb);
}

// Shared Styles and Scripts
function render_admin_styles_and_scripts() {
    ?>
    <style>
        .education-erp-dashboard .table {
            margin-top: 20px;
        }
        .education-erp-dashboard .form-group {
            margin-bottom: 15px;
        }
        .education-erp-dashboard .btn {
            margin-right: 10px;
        }
        .education-erp-dashboard .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .education-erp-dashboard .modal-content {
            background: #fff;
            margin: 5% auto;
            padding: 20px;
            width: 90%;
            max-width: 600px;
            border-radius: 5px;
            position: relative;
        }
        .education-erp-dashboard .modal-close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 20px;
            cursor: pointer;
        }
        .education-erp-dashboard .search-bar {
            margin-bottom: 20px;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        // Modal handling
        $('.edit-btn, .add-btn').on('click', function(e) {
            e.preventDefault();
            var modalId = $(this).data('modal');
            $('#' + modalId).show();
        });

        $('.modal-close').on('click', function() {
            $(this).closest('.modal').hide();
        });

        $(window).on('click', function(event) {
            if ($(event.target).hasClass('modal')) {
                $('.modal').hide();
            }
        });

        // Delete confirmation
        $('.delete-form').on('submit', function(e) {
            if (!confirm('Are you sure you want to delete the selected items?')) {
                e.preventDefault();
            }
        });

        // Search functionality
        $('.search-bar input').on('keyup', function() {
            var searchValue = $(this).val().toLowerCase();
            var tableId = $(this).data('table');
            $('#' + tableId + ' tbody tr').each(function() {
                var studentId = $(this).find('td:eq(1)').text().toLowerCase();
                $(this).toggle(studentId.indexOf(searchValue) > -1);
            });
        });
    });
    </script>
    <?php
}

// Fees UI
function render_fees_ui($wpdb) {
    $table_name = $wpdb->prefix . 'student_fees';
    $templates_table = $wpdb->prefix . 'fee_templates';

    // Handle CRUD
    if (isset($_POST['add_fee']) && check_admin_referer('add_fee_nonce')) {
        $wpdb->insert($table_name, [
            'student_id' => sanitize_text_field($_POST['student_id']),
            'education_center_id' => sanitize_text_field($_POST['education_center_id']),
            'template_id' => sanitize_text_field($_POST['template_id']),
            'amount' => floatval($_POST['amount']),
            'month_year' => sanitize_text_field($_POST['month_year']),
            'status' => sanitize_text_field($_POST['status']),
            'paid_date' => !empty($_POST['paid_date']) ? sanitize_text_field($_POST['paid_date']) : null,
            'payment_method' => sanitize_text_field($_POST['payment_method']),
            'cheque_number' => !empty($_POST['cheque_number']) ? sanitize_text_field($_POST['cheque_number']) : null,
        ]);
        echo '<div class="notice notice-success"><p>Fee added successfully!</p></div>';
    }

    if (isset($_POST['update_fee']) && check_admin_referer('update_fee_nonce')) {
        foreach ($_POST['fee_id'] as $index => $id) {
            $wpdb->update($table_name, [
                'template_id' => sanitize_text_field($_POST['template_id'][$index]),
                'amount' => floatval($_POST['amount'][$index]),
                'status' => sanitize_text_field($_POST['status'][$index]),
                'paid_date' => !empty($_POST['paid_date'][$index]) ? sanitize_text_field($_POST['paid_date'][$index]) : null,
                'payment_method' => sanitize_text_field($_POST['payment_method'][$index]),
                'cheque_number' => !empty($_POST['cheque_number'][$index]) ? sanitize_text_field($_POST['cheque_number'][$index]) : null,
            ], ['id' => intval($id)]);
        }
        echo '<div class="notice notice-success"><p>Fees updated successfully!</p></div>';
    }

    if (isset($_POST['delete_fees']) && check_admin_referer('delete_fees_nonce')) {
        $ids = array_map('intval', $_POST['fee_ids'] ?? []);
        if (!empty($ids)) {
            $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")", ...$ids));
            echo '<div class="notice notice-success"><p>Fees deleted successfully!</p></div>';
        }
    }

    // Fetch all fees
    $fees = $wpdb->get_results(
        "SELECT sf.*, ft.name as template_name 
         FROM $table_name sf 
         LEFT JOIN $templates_table ft ON sf.template_id = ft.id 
         ORDER BY sf.month_year DESC"
    );
    $templates = $wpdb->get_results("SELECT id, name, amount FROM $templates_table");

    ?>
    <div class="wrap education-erp-dashboard">
        <h1 class="wp-heading-inline">Fees Management</h1>
        <div class="search-bar">
            <input type="text" class="form-control" placeholder="Search by Student ID" data-table="fees-table">
        </div>
        <button class="btn btn-primary add-btn" data-modal="add-fee-modal">Add Fee</button>
        <table id="fees-table" class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th><input type="checkbox" id="select-all-fees"></th>
                    <th>Student ID</th>
                    <th>Center ID</th>
                    <th>Template</th>
                    <th>Amount</th>
                    <th>Month/Year</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                    <th>Payment Method</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($fees) : ?>
                    <?php foreach ($fees as $fee) : ?>
                        <tr>
                            <td><input type="checkbox" name="fee_ids[]" value="<?php echo esc_attr($fee->id); ?>" class="fee-checkbox"></td>
                            <td><?php auxin_print($fee->student_id); ?></td>
                            <td><?php auxin_print($fee->education_center_id); ?></td>
                            <td><?php auxin_print($fee->template_name ?: 'N/A'); ?></td>
                            <td><?php auxin_print($fee->amount); ?></td>
                            <td><?php auxin_print($fee->month_year); ?></td>
                            <td><?php auxin_print($fee->status); ?></td>
                            <td><?php auxin_print($fee->paid_date ?: '-'); ?></td>
                            <td><?php auxin_print($fee->payment_method ?: '-'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-modal="edit-fee-modal-<?php echo esc_attr($fee->id); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="10">No fees found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <form method="post" class="delete-form">
            <?php wp_nonce_field('delete_fees_nonce'); ?>
            <button type="submit" name="delete_fees" class="btn btn-danger">Delete Selected</button>
        </form>

        <!-- Add Fee Modal -->
        <div id="add-fee-modal" class="modal">
            <div class="modal-content">
                <span class="modal-close">×</span>
                <h3>Add Fee</h3>
                <form method="post">
                    <?php wp_nonce_field('add_fee_nonce'); ?>
                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" class="form-control" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label for="education_center_id">Education Center ID</label>
                        <input type="text" class="form-control" name="education_center_id" required>
                    </div>
                    <div class="form-group">
                        <label for="template_id">Template</label>
                        <select class="form-control" name="template_id" required>
                            <option value="">Select Template</option>
                            <?php foreach ($templates as $template) : ?>
                                <option value="<?php echo esc_attr($template->id); ?>"><?php auxin_print($template->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label for="month_year">Month/Year</label>
                        <input type="text" class="form-control" name="month_year" placeholder="YYYY-MM" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paid_date">Paid Date</label>
                        <input type="date" class="form-control" name="paid_date">
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" name="payment_method">
                            <option value="">Select</option>
                            <option value="cod">COD</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cheque_number">Cheque Number</label>
                        <input type="text" class="form-control" name="cheque_number">
                    </div>
                    <button type="submit" name="add_fee" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>

        <!-- Edit Fee Modals -->
        <?php foreach ($fees as $fee) : ?>
            <div id="edit-fee-modal-<?php echo esc_attr($fee->id); ?>" class="modal">
                <div class="modal-content">
                    <span class="modal-close">×</span>
                    <h3>Edit Fee</h3>
                    <form method="post">
                        <?php wp_nonce_field('update_fee_nonce'); ?>
                        <input type="hidden" name="fee_id[]" value="<?php echo esc_attr($fee->id); ?>">
                        <div class="form-group">
                            <label for="template_id">Template</label>
                            <select class="form-control" name="template_id[]">
                                <option value="">Select Template</option>
                                <?php foreach ($templates as $template) : ?>
                                    <option value="<?php echo esc_attr($template->id); ?>" <?php selected($fee->template_id, $template->id); ?>><?php auxin_print($template->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" step="0.01" class="form-control" name="amount[]" value="<?php echo esc_attr($fee->amount); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status[]">
                                <option value="pending" <?php selected($fee->status, 'pending'); ?>>Pending</option>
                                <option value="paid" <?php selected($fee->status, 'paid'); ?>>Paid</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="paid_date">Paid Date</label>
                            <input type="date" class="form-control" name="paid_date[]" value="<?php echo esc_attr($fee->paid_date); ?>">
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control" name="payment_method[]">
                                <option value="">Select</option>
                                <option value="cod" <?php selected($fee->payment_method, 'cod'); ?>>COD</option>
                                <option value="cheque" <?php selected($fee->payment_method, 'cheque'); ?>>Cheque</option>
                                <option value="cash" <?php selected($fee->payment_method, 'cash'); ?>>Cash</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cheque_number">Cheque Number</label>
                            <input type="text" class="form-control" name="cheque_number[]" value="<?php echo esc_attr($fee->cheque_number); ?>">
                        </div>
                        <button type="submit" name="update_fee" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    render_admin_styles_and_scripts();
}

// Fees Templates UI
function render_fees_templates_ui($wpdb) {
    $table_name = $wpdb->prefix . 'fee_templates';

    // Handle CRUD
    if (isset($_POST['add_template']) && check_admin_referer('add_template_nonce')) {
        $wpdb->insert($table_name, [
            'name' => sanitize_text_field($_POST['name']),
            'amount' => floatval($_POST['amount']),
        ]);
        echo '<div class="notice notice-success"><p>Template added successfully!</p></div>';
    }

    if (isset($_POST['update_template']) && check_admin_referer('update_template_nonce')) {
        foreach ($_POST['template_id'] as $index => $id) {
            $wpdb->update($table_name, [
                'name' => sanitize_text_field($_POST['name'][$index]),
                'amount' => floatval($_POST['amount'][$index]),
            ], ['id' => intval($id)]);
        }
        echo '<div class="notice notice-success"><p>Templates updated successfully!</p></div>';
    }

    if (isset($_POST['delete_templates']) && check_admin_referer('delete_templates_nonce')) {
        $ids = array_map('intval', $_POST['template_ids'] ?? []);
        if (!empty($ids)) {
            $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")", ...$ids));
            echo '<div class="notice notice-success"><p>Templates deleted successfully!</p></div>';
        }
    }

    // Fetch all templates
    $templates = $wpdb->get_results("SELECT * FROM $table_name ORDER BY name ASC");

    ?>
    <div class="wrap education-erp-dashboard">
        <h1 class="wp-heading-inline">Fees Templates Management</h1>
        <button class="btn btn-primary add-btn" data-modal="add-template-modal">Add Template</button>
        <table id="templates-table" class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th><input type="checkbox" id="select-all-templates"></th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($templates) : ?>
                    <?php foreach ($templates as $template) : ?>
                        <tr>
                            <td><input type="checkbox" name="template_ids[]" value="<?php echo esc_attr($template->id); ?>" class="template-checkbox"></td>
                            <td><?php auxin_print($template->name); ?></td>
                            <td><?php auxin_print($template->amount); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-modal="edit-template-modal-<?php echo esc_attr($template->id); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="4">No templates found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <form method="post" class="delete-form">
            <?php wp_nonce_field('delete_templates_nonce'); ?>
            <button type="submit" name="delete_templates" class="btn btn-danger">Delete Selected</button>
        </form>

        <!-- Add Template Modal -->
        <div id="add-template-modal" class="modal">
            <div class="modal-content">
                <span class="modal-close">×</span>
                <h3>Add Template</h3>
                <form method="post">
                    <?php wp_nonce_field('add_template_nonce'); ?>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <button type="submit" name="add_template" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>

        <!-- Edit Template Modals -->
        <?php foreach ($templates as $template) : ?>
            <div id="edit-template-modal-<?php echo esc_attr($template->id); ?>" class="modal">
                <div class="modal-content">
                    <span class he="modal-close">×</span>
                    <h3>Edit Template</h3>
                    <form method="post">
                        <?php wp_nonce_field('update_template_nonce'); ?>
                        <input type="hidden" name="template_id[]" value="<?php echo esc_attr($template->id); ?>">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" name="name[]" value="<?php echo esc_attr($template->name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" step="0.01" class="form-control" name="amount[]" value="<?php echo esc_attr($template->amount); ?>" required>
                        </div>
                        <button type="submit" name="update_template" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    render_admin_styles_and_scripts();
}

// Transport Enrollments UI
function render_transport_enrollments_ui($wpdb) {
    $table_name = $wpdb->prefix . 'transport_enrollments';

    // Handle CRUD
    if (isset($_POST['add_enrollment']) && check_admin_referer('add_enrollment_nonce')) {
        $wpdb->insert($table_name, [
            'student_id' => sanitize_text_field($_POST['student_id']),
            'education_center_id' => sanitize_text_field($_POST['education_center_id']),
            'enrollment_date' => sanitize_text_field($_POST['enrollment_date']),
            'active' => isset($_POST['active']) ? 1 : 0,
        ]);
        echo '<div class="notice notice-success"><p>Enrollment added successfully!</p></div>';
    }

    if (isset($_POST['update_enrollment']) && check_admin_referer('update_enrollment_nonce')) {
        foreach ($_POST['enrollment_id'] as $index => $id) {
            $wpdb->update($table_name, [
                'enrollment_date' => sanitize_text_field($_POST['enrollment_date'][$index]),
                'active' => isset($_POST['active'][$index]) ? 1 : 0,
            ], ['id' => intval($id)]);
        }
        echo '<div class="notice notice-success"><p>Enrollments updated successfully!</p></div>';
    }

    if (isset($_POST['delete_enrollments']) && check_admin_referer('delete_enrollments_nonce')) {
        $ids = array_map('intval', $_POST['enrollment_ids'] ?? []);
        if (!empty($ids)) {
            $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")", ...$ids));
            echo '<div class="notice notice-success"><p>Enrollments deleted successfully!</p></div>';
        }
    }

    // Fetch all enrollments
    $enrollments = $wpdb->get_results(
        "SELECT * FROM $table_name 
         ORDER BY enrollment_date DESC"
    );

    ?>
    <div class="wrap education-erp-dashboard">
        <h1 class="wp-heading-inline">Transport Enrollments Management</h1>
        <div class="search-bar">
            <input type="text" class="form-control" placeholder="Search by Student ID" data-table="enrollments-table">
        </div>
        <button class="btn btn-primary add-btn" data-modal="add-enrollment-modal">Add Enrollment</button>
        <table id="enrollments-table" class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th><input type="checkbox" id="select-all-enrollments"></th>
                    <th>Student ID</th>
                    <th>Center ID</th>
                    <th>Enrollment Date</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($enrollments) : ?>
                    <?php foreach ($enrollments as $enrollment) : ?>
                        <tr>
                            <td><input type="checkbox" name="enrollment_ids[]" value="<?php echo esc_attr($enrollment->id); ?>" class="enrollment-checkbox"></td>
                            <td><?php auxin_print($enrollment->student_id); ?></td>
                            <td><?php auxin_print($enrollment->education_center_id); ?></td>
                            <td><?php auxin_print($enrollment->enrollment_date); ?></td>
                            <td><?php echo $enrollment->active ? 'Yes' : 'No'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-modal="edit-enrollment-modal-<?php echo esc_attr($enrollment->id); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="6">No transport enrollments found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <form method="post" class="delete-form">
            <?php wp_nonce_field('delete_enrollments_nonce'); ?>
            <button type="submit" name="delete_enrollments" class="btn btn-danger">Delete Selected</button>
        </form>

        <!-- Add Enrollment Modal -->
        <div id="add-enrollment-modal" class="modal">
            <div class="modal-content">
                <span class="modal-close">×</span>
                <h3>Add Enrollment</h3>
                <form method="post">
                    <?php wp_nonce_field('add_enrollment_nonce'); ?>
                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" class="form-control" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label for="education_center_id">Education Center ID</label>
                        <input type="text" class="form-control" name="education_center_id" required>
                    </div>
                    <div class="form-group">
                        <label for="enrollment_date">Enrollment Date</label>
                        <input type="date" class="form-control" name="enrollment_date" required>
                    </div>
                    <div class="form-group">
                        <label><input type="checkbox" name="active" checked> Active</label>
                    </div>
                    <button type="submit" name="add_enrollment" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>

        <!-- Edit Enrollment Modals -->
        <?php foreach ($enrollments as $enrollment) : ?>
            <div id="edit-enrollment-modal-<?php echo esc_attr($enrollment->id); ?>" class="modal">
                <div class="modal-content">
                    <span class="modal-close">×</span>
                    <h3>Edit Enrollment</h3>
                    <form method="post">
                        <?php wp_nonce_field('update_enrollment_nonce'); ?>
                        <input type="hidden" name="enrollment_id[]" value="<?php echo esc_attr($enrollment->id); ?>">
                        <div class="form-group">
                            <label for="enrollment_date">Enrollment Date</label>
                            <input type="date" class="form-control" name="enrollment_date[]" value="<?php echo esc_attr($enrollment->enrollment_date); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="active[]" <?php checked($enrollment->active, 1); ?>> Active</label>
                        </div>
                        <button type="submit" name="update_enrollment" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    render_admin_styles_and_scripts();
}

// Transport Fees UI
function render_transport_fees_ui($wpdb) {
    $table_name = $wpdb->prefix . 'transport_fees';
    $templates_table = $wpdb->prefix . 'fee_templates';

    // Handle CRUD
    if (isset($_POST['add_transport_fee']) && check_admin_referer('add_transport_fee_nonce')) {
        $wpdb->insert($table_name, [
            'student_id' => sanitize_text_field($_POST['student_id']),
            'education_center_id' => sanitize_text_field($_POST['education_center_id']),
            'template_id' => sanitize_text_field($_POST['template_id']),
            'amount' => floatval($_POST['amount']),
            'month_year' => sanitize_text_field($_POST['month_year']),
            'status' => sanitize_text_field($_POST['status']),
            'paid_date' => !empty($_POST['paid_date']) ? sanitize_text_field($_POST['paid_date']) : null,
            'payment_method' => sanitize_text_field($_POST['payment_method']),
            'cheque_number' => !empty($_POST['cheque_number']) ? sanitize_text_field($_POST['cheque_number']) : null,
        ]);
        echo '<div class="notice notice-success"><p>Transport Fee added successfully!</p></div>';
    }

    if (isset($_POST['update_transport_fee']) && check_admin_referer('update_transport_fee_nonce')) {
        foreach ($_POST['fee_id'] as $index => $id) {
            $wpdb->update($table_name, [
                'template_id' => sanitize_text_field($_POST['template_id'][$index]),
                'amount' => floatval($_POST['amount'][$index]),
                'status' => sanitize_text_field($_POST['status'][$index]),
                'paid_date' => !empty($_POST['paid_date'][$index]) ? sanitize_text_field($_POST['paid_date'][$index]) : null,
                'payment_method' => sanitize_text_field($_POST['payment_method'][$index]),
                'cheque_number' => !empty($_POST['cheque_number'][$index]) ? sanitize_text_field($_POST['cheque_number'][$index]) : null,
            ], ['id' => intval($id)]);
        }
        echo '<div class="notice notice-success"><p>Transport Fees updated successfully!</p></div>';
    }

    if (isset($_POST['delete_transport_fees']) && check_admin_referer('delete_transport_fees_nonce')) {
        $ids = array_map('intval', $_POST['fee_ids'] ?? []);
        if (!empty($ids)) {
            $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN (" . implode(',', array_fill(0, count($ids), '%d')) . ")", ...$ids));
            echo '<div class="notice notice-success"><p>Transport Fees deleted successfully!</p></div>';
        }
    }

    // Fetch all transport fees
    $fees = $wpdb->get_results(
        "SELECT tf.*, ft.name as template_name 
         FROM $table_name tf 
         LEFT JOIN $templates_table ft ON tf.template_id = ft.id 
         ORDER BY tf.month_year DESC"
    );
    $templates = $wpdb->get_results("SELECT id, name, amount FROM $templates_table WHERE name LIKE 'transport_%'");

    ?>
    <div class="wrap education-erp-dashboard">
        <h1 class="wp-heading-inline">Transport Fees Management</h1>
        <div class="search-bar">
            <input type="text" class="form-control" placeholder="Search by Student ID" data-table="transport-fees-table">
        </div>
        <button class="btn btn-primary add-btn" data-modal="add-transport-fee-modal">Add Transport Fee</button>
        <table id="transport-fees-table" class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th><input type="checkbox" id="select-all-transport-fees"></th>
                    <th>Student ID</th>
                    <th>Center ID</th>
                    <th>Template</th>
                    <th>Amount</th>
                    <th>Month/Year</th>
                    <th>Status</th>
                    <th>Paid Date</th>
                    <th>Payment Method</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($fees) : ?>
                    <?php foreach ($fees as $fee) : ?>
                        <tr>
                            <td><input type="checkbox" name="fee_ids[]" value="<?php echo esc_attr($fee->id); ?>" class="transport-fee-checkbox"></td>
                            <td><?php auxin_print($fee->student_id); ?></td>
                            <td><?php auxin_print($fee->education_center_id); ?></td>
                            <td><?php auxin_print($fee->template_name ?: 'N/A'); ?></td>
                            <td><?php auxin_print($fee->amount); ?></td>
                            <td><?php auxin_print($fee->month_year); ?></td>
                            <td><?php auxin_print($fee->status); ?></td>
                            <td><?php auxin_print($fee->paid_date ?: '-'); ?></td>
                            <td><?php auxin_print($fee->payment_method ?: '-'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning edit-btn" data-modal="edit-transport-fee-modal-<?php echo esc_attr($fee->id); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="10">No transport fees found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <form method="post" class="delete-form">
            <?php wp_nonce_field('delete_transport_fees_nonce'); ?>
            <button type="submit" name="delete_transport_fees" class="btn btn-danger">Delete Selected</button>
        </form>

        <!-- Add Transport Fee Modal -->
        <div id="add-transport-fee-modal" class="modal">
            <div class="modal-content">
                <span class="modal-close">×</span>
                <h3>Add Transport Fee</h3>
                <form method="post">
                    <?php wp_nonce_field('add_transport_fee_nonce'); ?>
                    <div class="form-group">
                        <label for="student_id">Student ID</label>
                        <input type="text" class="form-control" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label for="education_center_id">Education Center ID</label>
                        <input type="text" class="form-control" name="education_center_id" required>
                    </div>
                    <div class="form-group">
                        <label for="template_id">Template</label>
                        <select class="form-control" name="template_id" required>
                            <option value="">Select Template</option>
                            <?php foreach ($templates as $template) : ?>
                                <option value="<?php echo esc_attr($template->id); ?>"><?php auxin_print($template->name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount</label>
                        <input type="number" step="0.01" class="form-control" name="amount" required>
                    </div>
                    <div class="form-group">
                        <label for="month_year">Month/Year</label>
                        <input type="text" class="form-control" name="month_year" placeholder="YYYY-MM" required>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" name="status" required>
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="paid_date">Paid Date</label>
                        <input type="date" class="form-control" name="paid_date">
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="form-control" name="payment_method">
                            <option value="">Select</option>
                            <option value="cod">COD</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cheque_number">Cheque Number</label>
                        <input type="text" class="form-control" name="cheque_number">
                    </div>
                    <button type="submit" name="add_transport_fee" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>

        <!-- Edit Transport Fee Modals -->
        <?php foreach ($fees as $fee) : ?>
            <div id="edit-transport-fee-modal-<?php echo esc_attr($fee->id); ?>" class="modal">
                <div class="modal-content">
                    <span class="modal-close">×</span>
                    <h3>Edit Transport Fee</h3>
                    <form method="post">
                        <?php wp_nonce_field('update_transport_fee_nonce'); ?>
                        <input type="hidden" name="fee_id[]" value="<?php echo esc_attr($fee->id); ?>">
                        <div class="form-group">
                            <label for="template_id">Template</label>
                            <select class="form-control" name="template_id[]">
                                <option value="">Select Template</option>
                                <?php foreach ($templates as $template) : ?>
                                    <option value="<?php echo esc_attr($template->id); ?>" <?php selected($fee->template_id, $template->id); ?>><?php auxin_print($template->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" step="0.01" class="form-control" name="amount[]" value="<?php echo esc_attr($fee->amount); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" name="status[]">
                                <option value="pending" <?php selected($fee->status, 'pending'); ?>>Pending</option>
                                <option value="paid" <?php selected($fee->status, 'paid'); ?>>Paid</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="paid_date">Paid Date</label>
                            <input type="date" class="form-control" name="paid_date[]" value="<?php echo esc_attr($fee->paid_date); ?>">
                        </div>
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select class="form-control" name="payment_method[]">
                                <option value="">Select</option>
                                <option value="cod" <?php selected($fee->payment_method, 'cod'); ?>>COD</option>
                                <option value="cheque" <?php selected($fee->payment_method, 'cheque'); ?>>Cheque</option>
                                <option value="cash" <?php selected($fee->payment_method, 'cash'); ?>>Cash</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="cheque_number">Cheque Number</label>
                            <input type="text" class="form-control" name="cheque_number[]" value="<?php echo esc_attr($fee->cheque_number); ?>">
                        </div>
                        <button type="submit" name="update_transport_fee" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    render_admin_styles_and_scripts();
}

// Auxin Print Helper Function
function auxin_print($data) {
    echo esc_html($data);
}