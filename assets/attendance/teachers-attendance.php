<?php
if (!defined('ABSPATH')) {
    exit;
}

// Create teacher_attendance table
global $wpdb;
$table_name = $wpdb->prefix . 'teacher_attendance';
$departments_table = $wpdb->prefix . 'departments';
$charset_collate = $wpdb->get_charset_collate();


// Reusable Helper Functions
function get_teachers_by_center($center_id) {
    $args = array(
        'post_type' => 'teacher',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'educational_center_id',
                'value' => $center_id,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $teachers_query = new WP_Query($args);
    $teachers = array();

    if ($teachers_query->have_posts()) {
        while ($teachers_query->have_posts()) {
            $teachers_query->the_post();
            $teacher_id = get_field('teacher_id') ?: 'ID-' . get_the_ID(); // Fallback if teacher_id is missing
            $teacher_name = get_field('teacher_name') ?: get_the_title(); // Fallback to post title
            $teacher_department = get_field('teacher_department') ?: 'No Department'; // Fallback if missing
            $teachers[] = (object) array(
                'teacher_id' => $teacher_id,
                'teacher_name' => $teacher_name,
                'teacher_department' => $teacher_department
            );
        }
        wp_reset_postdata();
    } else {
        error_log("No teachers found for center_id: $center_id");
    }

    // Debugging: Log the number of teachers fetched
    error_log("Teachers fetched for center_id $center_id: " . count($teachers));
    return $teachers;
}

function get_or_create_department_id($department_name, $center_id) {
    global $wpdb;
    $departments_table = $wpdb->prefix . 'departments';
    $department_id = $wpdb->get_var($wpdb->prepare(
        "SELECT department_id FROM $departments_table WHERE department_name = %s AND education_center_id = %s",
        $department_name,
        $center_id
    ));
    if (!$department_id && $department_name !== 'No Department') {
        $wpdb->insert($departments_table, [
            'department_name' => $department_name,
            'education_center_id' => $center_id
        ]);
        $department_id = $wpdb->insert_id;
    }
    return $department_id ?: 0; // Return 0 if no department or fallback
}

// Register REST endpoint for fetching attendance data
add_action('rest_api_init', function () {
    register_rest_route('teacher-attendance/v1', '/fetch', array(
        'methods' => 'POST',
        'callback' => 'fetch_teacher_attendance_data',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});

// Fetch attendance data (unchanged)
function fetch_teacher_attendance_data($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $departments_table = $wpdb->prefix . 'departments';
    $params = $request->get_params();

    $educational_center_id = get_educational_center_data();
    if (!$educational_center_id) {
        return array('success' => false, 'data' => array('html' => '<p class="text-danger">Educational center ID not found</p>'));
    }

    $department_id = sanitize_text_field($params['department_id'] ?? '');
    $month = !empty($params['month']) ? intval($params['month']) : date('n');
    $year = !empty($params['year']) ? intval($params['year']) : date('Y');
    $page = max(1, intval($params['page'] ?? 1));
    $per_page = 10;

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    $query = "SELECT ta.teacher_id, ta.teacher_name, d.department_name, ta.date, ta.status 
              FROM $table_name ta
              LEFT JOIN $departments_table d ON ta.department_id = d.department_id
              WHERE ta.education_center_id = %s";
    $query_args = [$educational_center_id];

    if (!empty($department_id)) {
        $query .= " AND ta.department_id = %d";
        $query_args[] = $department_id;
    }
    $query .= " AND YEAR(ta.date) = %d AND MONTH(ta.date) = %d";
    $query_args[] = $year;
    $query_args[] = $month;

    $total_query = "SELECT COUNT(DISTINCT ta.teacher_id) FROM $table_name ta WHERE ta.education_center_id = %s";
    $total_args = [$educational_center_id];
    if (!empty($department_id)) {
        $total_query .= " AND ta.department_id = %d";
        $total_args[] = $department_id;
    }
    $total_query .= " AND YEAR(ta.date) = %d AND MONTH(ta.date) = %d";
    $total_args[] = $year;
    $total_args[] = $month;

    $total_teachers = $wpdb->get_var($wpdb->prepare($total_query, $total_args));
    $total_pages = ceil($total_teachers / $per_page);

    $offset = ($page - 1) * $per_page;
    $query .= " ORDER BY ta.teacher_id LIMIT %d OFFSET %d";
    $query_args[] = $per_page;
    $query_args[] = $offset;

    $results = $wpdb->get_results($wpdb->prepare($query, $query_args));

    $teacher_data = [];
    foreach ($results as $row) {
        $teacher_key = $row->teacher_id;
        if (!isset($teacher_data[$teacher_key])) {
            $teacher_data[$teacher_key] = [
                'name' => $row->teacher_name,
                'department' => $row->department_name,
                'attendance' => array_fill(1, $days_in_month, ''),
                'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'O' => 0]
            ];
        }
        $day = (int) date('j', strtotime($row->date));
        if ($day >= 1 && $day <= $days_in_month) {
            $status_short = '';
            switch ($row->status) {
                case 'Present': $status_short = 'P'; $teacher_data[$teacher_key]['counts']['P']++; break;
                case 'Late': $status_short = 'L'; $teacher_data[$teacher_key]['counts']['L']++; break;
                case 'Absent': $status_short = 'A'; $teacher_data[$teacher_key]['counts']['A']++; break;
                case 'On Leave': $status_short = 'O'; $teacher_data[$teacher_key]['counts']['O']++; break;
            }
            $teacher_data[$teacher_key]['attendance'][$day] = $status_short;
        }
    }

    ob_start();
    ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="teacher-attendance-table">
            <thead class="table-light">
                <tr>
                    <th scope="col">Name</th>
                    <th scope="col">Teacher ID</th>
                    <th scope="col">Department</th>
                    <th scope="col">P</th>
                    <th scope="col">L</th>
                    <th scope="col">A</th>
                    <th scope="col">O</th>
                    <th scope="col">%</th>
                    <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                        <?php $date = sprintf('%04d-%02d-%02d', $year, $month, $day); $day_name = date('D', strtotime($date)); ?>
                        <th scope="col"><?php echo "$day<br>$day_name"; ?></th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($teacher_data)) : ?>
                    <?php foreach ($teacher_data as $teacher_id => $data) : ?>
                        <?php $total_days = $days_in_month - $data['counts']['O']; $percent = $total_days > 0 ? round(($data['counts']['P'] / $total_days) * 100) : 0; ?>
                        <tr>
                            <td><?php echo esc_html($data['name']); ?></td>
                            <td><?php echo esc_html($teacher_id); ?></td>
                            <td><?php echo esc_html($data['department']); ?></td>
                            <td><?php echo esc_html($data['counts']['P']); ?></td>
                            <td><?php echo esc_html($data['counts']['L']); ?></td>
                            <td><?php echo esc_html($data['counts']['A']); ?></td>
                            <td><?php echo esc_html($data['counts']['O']); ?></td>
                            <td><?php echo esc_html($percent . '%'); ?></td>
                            <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                                <td><?php echo esc_html($data['attendance'][$day] ?? ''); ?></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="<?php echo 8 + $days_in_month; ?>" class="text-center">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center gap-2 mt-3">
        <?php if ($total_pages > 1) : ?>
            <button class="btn btn-primary pagination-btn" data-page="<?php echo ($page > 1) ? $page - 1 : 1; ?>" <?php echo ($page <= 1) ? 'disabled' : ''; ?>>Previous</button>
            <span class="align-self-center">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <button class="btn btn-primary pagination-btn" data-page="<?php echo ($page < $total_pages) ? $page + 1 : $total_pages; ?>" <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>>Next</button>
        <?php endif; ?>
    </div>
    <?php
    return array('success' => true, 'data' => array('html' => ob_get_clean()));
}

// Dashboard Shortcode
function aspire_teacher_attendance_dashboard_shortcode() {
    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();    }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'teachers-attendance';

    ob_start();
    ?>
    <div class="container-fluid p-0">
        <div class="row">
            <div class="col-md-3 col-12 mb-3">
                <?php 
                 echo render_admin_header(wp_get_current_user());
                 if (!is_center_subscribed($education_center_id)) {
                     return render_subscription_expired_message($education_center_id);
                 }
                $active_section = str_replace('-', '-', $section);
                include plugin_dir_path(__FILE__) . '../sidebar.php';
                ?>
            </div>
            <div class="col-md-9 col-12">
                <div id="teacher-attendance-content">
                    <?php
                    switch ($section) {
                        case 'teachers-attendance':
                            echo display_teacher_attendance_section();
                            break;
                        case 'add-teacher-attendance':
                            echo display_add_teacher_attendance_section();
                            break;
                        case 'edit-teacher-attendance':
                            echo display_edit_teacher_attendance_section();
                            break;
                        case 'bulk-import-teacher-attendance':
                            echo display_bulk_import_teacher_attendance_section();
                            break;
                        case 'bulk-export-teacher-attendance':
                            echo export_teacher_attendance_shortcode([]);
                            break;
                        default:
                            echo '<div class="alert alert-warning">Section not found. Please select a valid option.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_teacher_attendance_dashboard', 'aspire_teacher_attendance_dashboard_shortcode');

// Section: View Attendance
function display_teacher_attendance_section() {
    global $wpdb;
    $departments_table = $wpdb->prefix . 'departments';
    $departments = $wpdb->get_results($wpdb->prepare("SELECT department_id, department_name FROM $departments_table WHERE education_center_id = %s", get_educational_center_data()));
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $dates = $wpdb->get_results("SELECT DISTINCT YEAR(date) AS year, MONTH(date) AS month FROM $table_name ORDER BY year DESC, month DESC");

    ob_start();
    ?>
    <h2 class="mb-3">Teacher Attendance</h2>
    <div class="mb-3">
        <form id="teacher-attendance-filter-form" class="row g-3">
            <div class="col-md-3 col-sm-6">
                <input type="text" id="search-teacher-id" class="form-control" placeholder="Teacher ID" title="Filter by Teacher ID">
            </div>
            <div class="col-md-3 col-sm-6">
                <input type="text" id="search-teacher-name" class="form-control" placeholder="Teacher Name" title="Filter by Teacher Name">
            </div>
            <div class="col-md-3 col-sm-6">
                <select id="search-department" class="form-select" title="Filter by Department">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept) : ?>
                        <option value="<?php echo esc_attr($dept->department_id); ?>"><?php echo esc_html($dept->department_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select id="search-month" class="form-select" title="Filter by Month and Year">
                    <option value="">Select Month</option>
                    <?php
                    $month_names = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                    $current_month = date('n');
                    $current_year = date('Y');
                    foreach ($dates as $date) {
                        $selected = ($date->month == $current_month && $date->year == $current_year) ? 'selected' : '';
                        echo '<option value="' . esc_attr($date->month) . '" data-year="' . esc_attr($date->year) . '" ' . $selected . '>' . esc_html($month_names[$date->month] . ' ' . $date->year) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-1 col-sm-12">
                <button id="search-button" type="button" class="btn btn-primary w-100">Search</button>
            </div>
        </form>
    </div>
    <div id="teacher-attendance-table-container" class="table-responsive">
        <p class="loading-message text-muted">Loading teacher attendance data...</p>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var restUrl = '<?php echo esc_url(rest_url('teacher-attendance/v1/fetch')); ?>';
            var nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';

            function fetchAttendanceData(page = 1) {
                var department_id = $('#search-department').val();
                var month = $('#search-month').val();
                var year = $('#search-month option:selected').data('year') || '<?php echo date('Y'); ?>';

                $.ajax({
                    url: restUrl,
                    method: 'POST',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', nonce);
                    },
                    data: {
                        department_id: department_id,
                        month: month,
                        year: year,
                        page: page
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#teacher-attendance-table-container').html(response.data.html);
                        } else {
                            $('#teacher-attendance-table-container').html('<p class="text-danger">Error loading data</p>');
                        }
                    },
                    error: function() {
                        $('#teacher-attendance-table-container').html('<p class="text-danger">Failed to fetch data</p>');
                    }
                });
            }

            fetchAttendanceData();

            $('#search-button').on('click', function() {
                fetchAttendanceData();
            });

            $(document).on('click', '.pagination-btn', function() {
                var page = $(this).data('page');
                fetchAttendanceData(page);
            });

            $('#search-teacher-id, #search-teacher-name').on('keypress', function(e) {
                if (e.which === 13) {
                    fetchAttendanceData();
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// Section: Add Teacher Attendance
function display_add_teacher_attendance_section() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $center_id = get_educational_center_data();
    if (empty($center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }
    $teachers = get_teachers_by_center($center_id);

    if (isset($_POST['add_teacher_attendance']) && check_admin_referer('add_teacher_attendance_action')) {
        $teacher_id = sanitize_text_field($_POST['teacher_id']);
        $date = sanitize_text_field($_POST['attendance_date']);
        $status = sanitize_text_field($_POST['status']);

        $teacher = array_filter($teachers, function($t) use ($teacher_id) {
            return $t->teacher_id === $teacher_id;
        });
        $teacher = reset($teacher);

        if ($teacher) {
            $department_id = get_or_create_department_id($teacher->teacher_department, $center_id);
            $data = array(
                'education_center_id' => $center_id,
                'teacher_id' => $teacher_id,
                'teacher_name' => $teacher->teacher_name,
                'department_id' => $department_id,
                'date' => $date,
                'status' => $status
            );
            $result = $wpdb->insert($table_name, $data);
            if ($result !== false) {
                echo '<div class="alert alert-success">Attendance added successfully!</div>';
            } else {
                echo '<div class="alert alert-danger">Failed to add attendance: ' . $wpdb->last_error . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Teacher not found.</div>';
        }
    }

    ob_start();
    ?>
    <h2 class="mb-3">Add Teacher Attendance</h2>
    <form method="post" class="mb-3">
        <?php wp_nonce_field('add_teacher_attendance_action'); ?>
        <div class="mb-3">
            <label for="teacher_id" class="form-label">Teacher</label>
            <select name="teacher_id" id="teacher_id" class="form-select" required>
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $teacher) : ?>
                    <option value="<?php echo esc_attr($teacher->teacher_id); ?>" data-department="<?php echo esc_attr($teacher->teacher_department); ?>">
                        <?php echo esc_html($teacher->teacher_name . ' (' . $teacher->teacher_id . ') - ' . $teacher->teacher_department); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="attendance_date" class="form-label">Date</label>
            <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Present">Present</option>
                <option value="Late">Late</option>
                <option value="Absent">Absent</option>
                <option value="On Leave">On Leave</option>
            </select>
        </div>
        <button type="submit" name="add_teacher_attendance" class="btn btn-primary">Add Attendance</button>
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#teacher_id').select2({
                placeholder: 'Search for a teacher...',
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <?php
    $output = ob_get_clean();
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), null, true);
    return $output;
}

// Section: Edit Teacher Attendance
function display_edit_teacher_attendance_section() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $center_id = get_educational_center_data();
    if (!$center_id) {
        wp_redirect(home_url('/login'));
        exit(); 
    }

    $teachers = get_teachers_by_center($center_id);

    $selected_teacher = isset($_POST['select_teacher_id']) ? sanitize_text_field($_POST['select_teacher_id']) : '';
    $selected_month = isset($_POST['select_month']) ? sanitize_text_field($_POST['select_month']) : '';
    $selected_date = isset($_POST['select_date']) ? sanitize_text_field($_POST['select_date']) : '';

    $edit_record = null;
    if (isset($_POST['select_attendance_record']) && $selected_teacher && $selected_date) {
        $edit_record = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE teacher_id = %s AND date = %s AND education_center_id = %s",
            $selected_teacher, $selected_date, $center_id
        ));
        if (!$edit_record) {
            echo '<div class="alert alert-danger">No record found for teacher ' . esc_html($selected_teacher) . ' on ' . esc_html($selected_date) . '</div>';
        }
    }

    if (isset($_POST['edit_teacher_attendance']) && check_admin_referer('edit_teacher_attendance_action')) {
        $ta_id = intval($_POST['attendance_id']);
        $status = sanitize_text_field($_POST['status']);
        $date = sanitize_text_field($_POST['attendance_date']);
        $result = $wpdb->update(
            $table_name,
            array('status' => $status, 'date' => $date),
            array('ta_id' => $ta_id),
            array('%s', '%s'),
            array('%d')
        );
        if ($result !== false) {
            echo '<div class="alert alert-success">Attendance updated successfully!</div>';
            $edit_record = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE ta_id = %d", $ta_id));
        } else {
            echo '<div class="alert alert-danger">Update failed: ' . $wpdb->last_error . '</div>';
        }
    }

    ob_start();
    ?>
    <h2 class="mb-3">Edit Teacher Attendance</h2>
    <form method="post" class="attendance-select-form mb-3" id="edit-attendance-form">
        <h3>Select Teacher and Date</h3>
        <div class="mb-3">
            <label for="select_teacher_id" class="form-label">Select Teacher:</label>
            <select name="select_teacher_id" id="select_teacher_id" class="form-select" required>
                <option value="">-- Select Teacher --</option>
                <?php foreach ($teachers as $teacher) : ?>
                    <option value="<?php echo esc_attr($teacher->teacher_id); ?>" <?php selected($selected_teacher, $teacher->teacher_id); ?>>
                        <?php echo esc_html("{$teacher->teacher_name} ({$teacher->teacher_id}) - {$teacher->teacher_department}"); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3" id="month_select_container" style="<?php echo $selected_teacher ? '' : 'display: none;'; ?>">
            <label for="select_month" class="form-label">Filter by Month (Optional):</label>
            <select name="select_month" id="select_month" class="form-select">
                <option value="">-- All Months --</option>
                <?php if ($selected_teacher) : ?>
                    <?php
                    $months = $wpdb->get_results($wpdb->prepare(
                        "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as month 
                         FROM $table_name WHERE teacher_id = %s AND education_center_id = %s ORDER BY month DESC",
                        $selected_teacher, $center_id
                    ));
                    foreach ($months as $month) : ?>
                        <option value="<?php echo esc_attr($month->month); ?>" <?php selected($selected_month, $month->month); ?>>
                            <?php echo esc_html(date('F Y', strtotime($month->month . '-01'))); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="mb-3" id="date_select_container" style="<?php echo $selected_teacher ? '' : 'display: none;'; ?>">
            <label for="select_date" class="form-label">Select Date:</label>
            <select name="select_date" id="select_date" class="form-select" required>
                <option value="">-- Select Date --</option>
                <?php if ($selected_teacher) : ?>
                    <?php
                    $query = "SELECT date, status FROM $table_name WHERE teacher_id = %s AND education_center_id = %s";
                    $params = [$selected_teacher, $center_id];
                    if ($selected_month) {
                        $query .= " AND DATE_FORMAT(date, '%Y-%m') = %s";
                        $params[] = $selected_month;
                    }
                    $query .= " ORDER BY date DESC";
                    $dates = $wpdb->get_results($wpdb->prepare($query, $params));
                    foreach ($dates as $date) : ?>
                        <option value="<?php echo esc_attr($date->date); ?>" <?php selected($selected_date, $date->date); ?>>
                            <?php echo esc_html("{$date->date} ({$date->status})"); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <button type="submit" name="select_attendance_record" class="btn btn-primary">Load Record</button>
    </form>

    <?php if ($edit_record) : ?>
    <form method="post" class="attendance-update-form mb-3" id="update-attendance-form">
        <?php wp_nonce_field('edit_teacher_attendance_action'); ?>
        <h3>Edit Attendance</h3>
        <input type="hidden" name="attendance_id" value="<?php echo esc_attr($edit_record->ta_id); ?>">
        <div class="mb-3">
            <label class="form-label">Teacher</label>
            <input type="text" class="form-control" value="<?php echo esc_attr($edit_record->teacher_name . ' (' . $edit_record->teacher_id . ')'); ?>" disabled>
        </div>
        <div class="mb-3">
            <label for="attendance_date" class="form-label">Date</label>
            <input type="date" name="attendance_date" id="attendance_date" class="form-control" value="<?php echo esc_attr($edit_record->date); ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Present" <?php selected($edit_record->status, 'Present'); ?>>Present</option>
                <option value="Late" <?php selected($edit_record->status, 'Late'); ?>>Late</option>
                <option value="Absent" <?php selected($edit_record->status, 'Absent'); ?>>Absent</option>
                <option value="On Leave" <?php selected($edit_record->status, 'On Leave'); ?>>On Leave</option>
            </select>
        </div>
        <button type="submit" name="edit_teacher_attendance" class="btn btn-primary">Update Attendance</button>
    </form>
    <?php endif; ?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#select_teacher_id').select2({
                placeholder: 'Search for a teacher...',
                allowClear: true,
                width: '100%'
            });

            $('#select_teacher_id').on('change', function() {
                var teacherId = $(this).val();
                if (teacherId) {
                    $('#month_select_container, #date_select_container').show();
                    $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'update_edit_form',
                        teacher_id: teacherId,
                        nonce: '<?php echo wp_create_nonce('edit_teacher_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            $('#select_month').html(response.data.months);
                            $('#select_date').html(response.data.dates);
                        }
                    });
                } else {
                    $('#month_select_container, #date_select_container').hide();
                }
            });

            $('#select_month').on('change', function() {
                var teacherId = $('#select_teacher_id').val();
                var month = $(this).val();
                if (teacherId) {
                    $.post('<?php echo admin_url('admin-ajax.php'); ?>', {
                        action: 'update_edit_form',
                        teacher_id: teacherId,
                        month: month,
                        nonce: '<?php echo wp_create_nonce('edit_teacher_nonce'); ?>'
                    }, function(response) {
                        if (response.success) {
                            $('#select_date').html(response.data.dates);
                        }
                    });
                }
            });
        });
    </script>
    <?php
    $output = ob_get_clean();
     return $output;
}

// AJAX Handler for Edit Form Updates
add_action('wp_ajax_update_edit_form', 'update_edit_form_callback');
function update_edit_form_callback() {
    check_ajax_referer('edit_teacher_nonce', 'nonce');
    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $teacher_id = sanitize_text_field($_POST['teacher_id']);
    $month = isset($_POST['month']) ? sanitize_text_field($_POST['month']) : '';
    $center_id = get_educational_center_data();
    if (empty($center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }
    $months_html = '<option value="">-- All Months --</option>';
    $months = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as month 
         FROM $table_name WHERE teacher_id = %s AND education_center_id = %s ORDER BY month DESC",
        $teacher_id, $center_id
    ));
    foreach ($months as $m) {
        $months_html .= '<option value="' . esc_attr($m->month) . '">' . esc_html(date('F Y', strtotime($m->month . '-01'))) . '</option>';
    }

    $query = "SELECT date, status FROM $table_name WHERE teacher_id = %s AND education_center_id = %s";
    $params = [$teacher_id, $center_id];
    if ($month) {
        $query .= " AND DATE_FORMAT(date, '%Y-%m') = %s";
        $params[] = $month;
    }
    $query .= " ORDER BY date DESC";
    $dates = $wpdb->get_results($wpdb->prepare($query, $params));
    $dates_html = '<option value="">-- Select Date --</option>';
    foreach ($dates as $d) {
        $dates_html .= '<option value="' . esc_attr($d->date) . '">' . esc_html("{$d->date} ({$d->status})") . '</option>';
    }

    wp_send_json_success(array('months' => $months_html, 'dates' => $dates_html));
}

// Section: Bulk Import Teacher Attendance
// Section: Bulk Import Teacher Attendance (Updated)
function display_bulk_import_teacher_attendance_section() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access this feature.</p>';
    }

    $current_teacher_id = function_exists('get_current_teacher_id') ? get_current_teacher_id() : get_current_user_id();
    if (!$current_teacher_id) {
        return '<p>Unable to identify teacher. Please log in again.</p>';
    }

    $center_id = get_educational_center_data();
    if (!$center_id) {
        wp_redirect(home_url('/login'));
        exit(); 
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $departments_table = $wpdb->prefix . 'departments';
    $output = '';

    // Default message and type
    $message = '';
    $type = 'success';

    // Display stored messages from submission
    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) && $_GET['type'] === 'error' ? 'error' : 'success';
        $output .= "<div class='attendance-message $type' data-auto-hide='3000'>$message</div>";
    }

    // Handle bulk upload
    if (isset($_POST['bulk_teacher_attendance_action']) && wp_verify_nonce($_POST['bulk_teacher_attendance_nonce'], 'bulk_teacher_attendance_submit')) {
        if (!empty($_FILES['attendance_file']['name']) && !empty($_POST['bulk_department'])) {
            $file = $_FILES['attendance_file']['tmp_name'];
            $bulk_department = sanitize_text_field($_POST['bulk_department']);

            if (($handle = fopen($file, 'r')) !== false) {
                $row = 0;
                $success_count = 0;
                $update_count = 0;
                $error_count = 0;
                $error_details = [];

                // Read header row to get date columns
                $header = fgetcsv($handle, 1000, ',');
                if (count($header) < 3) {
                    $output .= '<div class="attendance-message error" data-auto-hide="3000">Invalid CSV format: Missing headers.</div>';
                    fclose($handle);
                    return $output;
                }

                $date_columns = array_slice($header, 2); // Skip teacher_id and teacher_name
                $teachers = get_teachers_by_center($center_id);
                $teacher_map = array_column($teachers, null, 'teacher_id');

                while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                    $row++;
                    if (count($data) < 3) {
                        $error_count++;
                        $error_details[] = "Row $row: Invalid format (insufficient columns).";
                        continue;
                    }

                    $teacher_id = sanitize_text_field(trim($data[0]));
                    $teacher_name = sanitize_text_field(trim($data[1]));

                    if (empty($teacher_id) || empty($teacher_name)) {
                        $error_count++;
                        $error_details[] = "Row $row: Missing teacher_id or teacher_name.";
                        continue;
                    }

                    $teacher = isset($teacher_map[$teacher_id]) ? $teacher_map[$teacher_id] : null;
                    if (!$teacher) {
                        $error_count++;
                        $error_details[] = "Row $row: Teacher ID $teacher_id not found.";
                        continue;
                    }

                    // Use department from form instead of CSV for consistency
                    $department_id = get_or_create_department_id($bulk_department, $center_id);

                    // Process each date and status pair
                    for ($i = 0; $i < count($date_columns); $i++) {
                        if (isset($data[$i + 2])) {
                            $date_str = trim($date_columns[$i]); // Date from header
                            $status = sanitize_text_field(trim($data[$i + 2]));

                            if (empty($date_str) || empty($status)) {
                                $error_count++;
                                $error_details[] = "Row $row, Date $date_str: Missing date or status.";
                                continue;
                            }

                            // Convert date to Y-m-d (e.g., 2/1/2025 -> 2025-02-01)
                            $date = DateTime::createFromFormat('n/j/Y', $date_str);
                            if ($date === false) {
                                $error_count++;
                                $error_details[] = "Row $row, Date $date_str: Invalid date format (use MM/DD/YYYY).";
                                continue;
                            }
                            $date = $date->format('Y-m-d');

                            // Validate status
                            $valid_statuses = ['Present', 'Late', 'Absent', 'On Leave'];
                            if (!in_array($status, $valid_statuses)) {
                                $status_short = strtoupper(substr($status, 0, 1));
                                $status_map = ['P' => 'Present', 'L' => 'Late', 'A' => 'Absent', 'O' => 'On Leave'];
                                $status = $status_map[$status_short] ?? 'Absent'; // Default to Absent if invalid
                            }

                            // Check if record exists
                            $existing_record = $wpdb->get_row($wpdb->prepare(
                                "SELECT ta_id FROM $table_name 
                                 WHERE education_center_id = %s 
                                 AND teacher_id = %s 
                                 AND date = %s",
                                $center_id,
                                $teacher_id,
                                $date
                            ));

                            $record_data = [
                                'education_center_id' => $center_id,
                                'teacher_id' => $teacher_id,
                                'teacher_name' => $teacher_name,
                                'department_id' => $department_id,
                                'date' => $date,
                                'status' => $status
                            ];

                            if ($existing_record) {
                                $result = $wpdb->update(
                                    $table_name,
                                    ['status' => $status],
                                    ['ta_id' => $existing_record->ta_id],
                                    ['%s'],
                                    ['%d']
                                );
                                if ($result !== false) {
                                    $update_count++;
                                } else {
                                    $error_count++;
                                    $error_details[] = "Row $row, Date $date: Failed to update - " . $wpdb->last_error;
                                }
                            } else {
                                $result = $wpdb->insert($table_name, $record_data);
                                if ($result !== false) {
                                    $success_count++;
                                } else {
                                    $error_count++;
                                    $error_details[] = "Row $row, Date $date: Failed to insert - " . $wpdb->last_error;
                                }
                            }
                        }
                    }
                }
                fclose($handle);

                $message = "Bulk upload complete: $success_count inserted, $update_count updated, $error_count errors.";
                $type = $error_count > 0 ? 'error' : 'success';

                if ($error_count > 0) {
                    $output .= '<h3>Import Errors:</h3>';
                    $output .= '<ul class="error-details">';
                    foreach ($error_details as $error) {
                        $output .= '<li>' . esc_html($error) . '</li>';
                    }
                    $output .= '</ul>';
                }

                // Redirect to avoid form resubmission
                $redirect_url = add_query_arg(['message' => urlencode($message), 'type' => $type], $_SERVER['REQUEST_URI']);
                wp_redirect($redirect_url);
                exit;
            } else {
                $output .= '<div class="attendance-message error" data-auto-hide="3000">Failed to open the uploaded file.</div>';
            }
        } else {
            $output .= '<div class="attendance-message error" data-auto-hide="3000">Please upload a file and select a department.</div>';
        }
    }

    ob_start();
    ?>
    <div class="attendance-frontend">
        <?php echo $output; ?>

        <!-- Bulk Upload Form -->
        <h2>Bulk Import Teacher Attendance</h2>
        <form method="post" class="attendance-bulk-form" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('bulk_teacher_attendance_submit', 'bulk_teacher_attendance_nonce'); ?>
            <div class="form-group">
                <label for="bulk_department">Department:</label>
                <select name="bulk_department" id="bulk_department" required>
                    <option value="">-- Select Department --</option>
                    <?php
                    $departments = $wpdb->get_results($wpdb->prepare(
                        "SELECT department_id, department_name 
                         FROM $departments_table 
                         WHERE education_center_id = %s 
                         ORDER BY department_name ASC",
                        $center_id
                    ));
                    foreach ($departments as $dept) : ?>
                        <option value="<?php echo esc_attr($dept->department_name); ?>">
                            <?php echo esc_html($dept->department_name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="attendance_file">Upload CSV File:</label>
                <input type="file" name="attendance_file" id="attendance_file" accept=".csv" required>
                <p><small>Format: teacher_id, teacher_name, followed by dates (e.g., 2/1/2025, 3/2/2025) and status (P/L/A/O or full names like Present/Absent)</small></p>
                <div class="upload-progress" style="display: none;">Uploading... <span class="progress-text"></span></div>
            </div>
            <input type="submit" name="bulk_teacher_attendance_action" value="Import Attendance" class="button button-primary">
        </form>

        <!-- Detailed Results -->
        <?php if (isset($_GET['type']) && isset($_GET['message'])): ?>
            <div class="import-results">
                <h3>Import Summary:</h3>
                <p><?php echo esc_html(sanitize_text_field($_GET['message'])); ?></p>
                <?php if ($_GET['type'] === 'error' && !empty($error_details)): ?>
                    <h4>Error Details:</h4>
                    <ul class="error-details">
                        <?php foreach ($error_details as $error): ?>
                            <li><?php echo esc_html($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
    $content = ob_get_clean();

    // Enqueue styles and scripts
    $message_data = array(
        'message' => $message,
        'type' => $type,
        'ajax_url' => admin_url('admin-ajax.php')
    );
    wp_enqueue_style('bulk-teacher-attendance-style', plugin_dir_url(__FILE__) . 'bulk-teacher-attendance.css', array(), '1.0');
    wp_add_inline_style('bulk-teacher-attendance-style', "
        .attendance-frontend {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #0073aa;
            outline: none;
            box-shadow: 0 0 5px rgba(0,115,170,0.3);
        }
        .form-group input::placeholder {
            color: #999;
        }
        .button {
            padding: 12px 24px;
            background: #0073aa;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #005177;
        }
        .attendance-message, .import-results {
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
            font-size: 14px;
        }
        .attendance-message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .attendance-message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .import-results h3, .import-results h4 {
            margin-top: 0;
            color: #333;
        }
        .error-details {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .error-details li {
            padding: 8px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            margin-bottom: 5px;
            color: #721c24;
        }
        .upload-progress {
            margin-top: 10px;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            display: none;
        }
        .upload-progress .progress-text {
            font-weight: bold;
        }
    ");

    wp_enqueue_script('bulk-teacher-attendance-script', plugin_dir_url(__FILE__) . 'bulk-teacher-attendance.js', array('jquery'), '1.0', true);
    wp_localize_script('bulk-teacher-attendance-script', 'bulkTeacherAttendanceData', $message_data);
    wp_add_inline_script('bulk-teacher-attendance-script', "
        jQuery(document).ready(function($) {
            $('.attendance-message[data-auto-hide]').each(function() {
                var $message = $(this);
                setTimeout(function() {
                    $message.fadeOut(500, function() {
                        $(this).remove();
                    });
                }, $(this).data('auto-hide'));
            });

            $('.attendance-bulk-form').on('submit', function(e) {
                var form = $(this);
                var progress = $('.upload-progress');
                progress.show();
                progress.find('.progress-text').text('Processing... 0%');

                var progressInterval = setInterval(function() {
                    var currentProgress = parseInt(progress.find('.progress-text').text().replace('Processing... ', '').replace('%', ''));
                    if (currentProgress < 100) {
                        progress.find('.progress-text').text('Processing... ' + (currentProgress + 10) + '%');
                    } else {
                        clearInterval(progressInterval);
                    }
                }, 500);
            });
        });
    ");

    return $content;
}


// Register shortcode
add_shortcode('export_teacher_attendance', 'export_teacher_attendance_shortcode');
function export_teacher_attendance_shortcode($atts) {
    if (!is_user_logged_in()) {
        return '<p>Please log in to access this feature.</p>';
    }

    $current_teacher_id = function_exists('get_current_teacher_id') ? get_current_teacher_id() : get_current_user_id();
    if (!$current_teacher_id) {
        return '<p>Unable to identify teacher. Please log in again.</p>';
    }

    $educational_center_id = get_educational_center_data();
    if (!$educational_center_id) {
        wp_redirect(home_url('/login'));
        exit(); 
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $departments_table = $wpdb->prefix . 'departments';
    $output = '';
    $error = '';

    $message = '';
    $type = 'success';

    if (isset($_GET['message'])) {
        $message = sanitize_text_field($_GET['message']);
        $type = isset($_GET['type']) && $_GET['type'] === 'error' ? 'error' : 'success';
        $output .= "<div class='alert alert-$type' data-auto-hide='3000'>$message</div>";
    }

    if (isset($_POST['export_teacher_attendance_action']) && wp_verify_nonce($_POST['export_teacher_attendance_nonce'], 'export_teacher_attendance_submit')) {
        $start_month = sanitize_text_field($_POST['start_month']);
        $end_month = sanitize_text_field($_POST['end_month']);
        $teacher_id = sanitize_text_field($_POST['teacher_id']);
        $department = sanitize_text_field($_POST['department']);
        $format = sanitize_text_field($_POST['export_format']);

        $start_date = DateTime::createFromFormat('Y-m', $start_month);
        $end_date = DateTime::createFromFormat('Y-m', $end_month);
        if ($start_date === false || $end_date === false) {
            $error = '<div class="alert alert-danger" data-auto-hide="3000">Invalid date format for month range.</div>';
        } elseif ($start_date > $end_date) {
            $error = '<div class="alert alert-danger" data-auto-hide="3000">Start month cannot be after end month.</div>';
        } else {
            $query = "SELECT teacher_id, teacher_name, date, status 
                      FROM $table_name 
                      WHERE education_center_id = %s";
            $params = [$educational_center_id];

            if (!empty($teacher_id)) {
                $query .= " AND teacher_id = %s";
                $params[] = $teacher_id;
            }
            if (!empty($department)) {
                $query .= " AND department_id = (SELECT department_id FROM $departments_table WHERE department_name = %s AND education_center_id = %s LIMIT 1)";
                $params[] = $department;
                $params[] = $educational_center_id;
            }

            if ($start_month === $end_month) {
                $query .= " AND DATE_FORMAT(date, '%Y-%m') = %s";
                $params[] = $start_month;
            } else {
                $query .= " AND DATE_FORMAT(date, '%Y-%m') BETWEEN %s AND %s";
                $params[] = $start_month;
                $params[] = $end_month;
            }

            $query .= " ORDER BY teacher_id, date ASC";
            $results = $wpdb->get_results($wpdb->prepare($query, $params));

            if (empty($results)) {
                $error = '<div class="alert alert-danger" data-auto-hide="3000">No attendance records found for the selected criteria.</div>';
            } else {
                $start = clone $start_date;
                $start->setDate($start_date->format('Y'), $start_date->format('m'), 1);
                $end = clone $end_date;
                $end->setDate($end_date->format('Y'), $end_date->format('m'), $end_date->format('t'));

                $interval = new DateInterval('P1D');
                $date_range = new DatePeriod($start, $interval, $end->modify('+1 day'));

                $date_headers = [];
                foreach ($date_range as $date) {
                    $date_headers[] = $date->format('m/d/yyyy');
                }

                $teacher_data = [];
                foreach ($results as $record) {
                    if (!isset($teacher_data[$record->teacher_id])) {
                        $teacher_data[$record->teacher_id] = [
                            'teacher_id' => $record->teacher_id,
                            'teacher_name' => $record->teacher_name,
                            'statuses' => array_fill(0, count($date_headers), '')
                        ];
                    }
                    $date_obj = DateTime::createFromFormat('Y-m-d', $record->date);
                    if ($date_obj !== false) {
                        $date_key = array_search($date_obj->format('m/d/yyyy'), $date_headers);
                        if ($date_key !== false) {
                            $teacher_data[$record->teacher_id]['statuses'][$date_key] = $record->status;
                        }
                    }
                }

                if ($format === 'csv') {
                    // Clear all output buffers to ensure clean CSV output
                    while (ob_get_level() > 0) {
                        ob_end_clean();
                    }

                    header('Content-Type: text/csv; charset=UTF-8');
                    header('Content-Disposition: attachment; filename="teacher_attendance_export_' . date('Ymd') . '.csv"');
                    header('Cache-Control: no-cache, no-store, must-revalidate');
                    header('Pragma: no-cache');
                    header('Expires: 0');

                    $output = fopen('php://output', 'w');
                    if ($output === false) {
                        die('Failed to open output stream.');
                    }

                    $header_row = ['Teacher ID', 'Teacher Name'];
                    $header_row = array_merge($header_row, array_map('trim', $date_headers));
                    fputcsv($output, $header_row, ',', '"', "\\");

                    foreach ($teacher_data as $teacher) {
                        $row = [
                            trim($teacher['teacher_id']),
                            trim($teacher['teacher_name'])
                        ];
                        $row = array_merge($row, array_map('trim', $teacher['statuses']));
                        fputcsv($output, $row, ',', '"', "\\");
                    }

                    fclose($output);
                    exit;
                }
            }
        }
    }

    $months = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT DATE_FORMAT(date, '%Y-%m') as month 
         FROM $table_name 
         WHERE education_center_id = %s 
         ORDER BY month ASC",
        $educational_center_id
    ));

    $departments = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT department_name 
         FROM $departments_table 
         WHERE education_center_id = %s",
        $educational_center_id
    ));

    ob_start();
    ?>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <?php echo $error; ?>
                <h2 class="mb-4">Export Teacher Attendance Records</h2>
                <form method="post" class="attendance-export-form" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>">
                    <?php wp_nonce_field('export_teacher_attendance_submit', 'export_teacher_attendance_nonce'); ?>
                    <div class="mb-3">
                        <label for="start_month" class="form-label fw-bold">Start Month:</label>
                        <select name="start_month" id="start_month" class="form-select" required>
                            <option value="">-- Select Start Month --</option>
                            <?php foreach ($months as $month) : ?>
                                <option value="<?php echo esc_attr($month); ?>">
                                    <?php echo esc_html(date('F Y', strtotime($month . '-01'))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="end_month" class="form-label fw-bold">End Month:</label>
                        <select name="end_month" id="end_month" class="form-select" required>
                            <option value="">-- Select End Month --</option>
                            <?php foreach ($months as $month) : ?>
                                <option value="<?php echo esc_attr($month); ?>">
                                    <?php echo esc_html(date('F Y', strtotime($month . '-01'))); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="teacher_id" class="form-label fw-bold">Teacher ID (Optional):</label>
                        <input type="text" name="teacher_id" id="teacher_id" class="form-control" placeholder="e.g., TEA-123">
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label fw-bold">Department (Optional):</label>
                        <select name="department" id="department" class="form-select">
                            <option value="">-- All Departments --</option>
                            <?php foreach ($departments as $dept) : ?>
                                <option value="<?php echo esc_attr($dept); ?>">
                                    <?php echo esc_html($dept); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="export_format" class="form-label fw-bold">Export Format:</label>
                        <select name="export_format" id="export_format" class="form-select" required>
                            <option value="csv">CSV</option>
                        </select>
                    </div>
                    <button type="submit" name="export_teacher_attendance_action" class="btn btn-primary">Export Attendance</button>
                </form>
            </div>
        </div>
    </div>
    <?php
    $content = ob_get_clean();

    // Inline JavaScript for auto-hiding messages
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            $('.alert[data-auto-hide]').each(function() {
                var \$alert = $(this);
                setTimeout(function() {
                    \$alert.fadeOut(500, function() {
                        $(this).remove();
                    });
                }, \$alert.data('auto-hide'));
            });
        });
    ");

    return $output . $content;
}