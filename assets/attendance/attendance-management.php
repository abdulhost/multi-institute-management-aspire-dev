<?php


// Create table
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
    PRIMARY KEY (sa_id),
    INDEX (student_id, date)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Enqueue CSS and JS
function enqueue_attendance_management_scripts() {
    wp_enqueue_style('attendance-management-css', plugins_url('attendance-management.css', __FILE__));
    wp_enqueue_script('attendance-management-js', plugins_url('attendance-management.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('attendance-management-js', 'attendance_ajax', array(
        'ajax_url' => esc_url(rest_url('attendance/v1/fetch')),
        'nonce' => wp_create_nonce('wp_rest')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_attendance_management_scripts');

// Register custom REST endpoint
// Register custom REST endpoint
add_action('rest_api_init', function () {
    register_rest_route('attendance/v1', '/fetch', array(
        'methods' => 'POST',
        'callback' => 'fetch_attendance_data',
        'permission_callback' => function () {
            return current_user_can('read');
        },
    ));
});
// Fetch attendance data via REST
function fetch_attendance_data($request) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $params = $request->get_params();
    $educational_center_id = get_educational_center_data();
    if (!$educational_center_id) {
        return array(
            'success' => false,
            'data' => array('html' => '<p>Educational center ID not found</p>')
        );
    }

    $class = sanitize_text_field($params['class'] ?? '');
    $section = sanitize_text_field($params['section'] ?? '');
    $month = !empty($params['month']) ? intval($params['month']) : 0;
    $year = !empty($params['year']) ? intval($params['year']) : 0;
    $student_id = sanitize_text_field($params['student_id'] ?? ''); // New: student ID filter
    $student_name = sanitize_text_field($params['student_name'] ?? ''); // New: student name filter

    // Default to current month/year if no filters are applied
    $has_filters = !empty($class) || !empty($section) || ($month > 0 && $year > 0) || !empty($student_id) || !empty($student_name);
    if (!$has_filters) {
        $month = date('n');
        $year = date('Y');
    }

    // Determine days in month if month/year are provided
    $days_in_month = ($month > 0 && $year > 0) ? cal_days_in_month(CAL_GREGORIAN, $month, $year) : 31;

    // Build dynamic query
    $query = "SELECT student_id, student_name, class, section, date, status 
              FROM $table_name 
              WHERE education_center_id = %s";
    $query_args = [$educational_center_id];

    if (!empty($class)) {
        $query .= " AND class = %s";
        $query_args[] = $class;
    }
    if (!empty($section)) {
        $query .= " AND section = %s";
        $query_args[] = $section;
    }
    if ($month > 0 && $year > 0) {
        $query .= " AND YEAR(date) = %d AND MONTH(date) = %d";
        $query_args[] = $year;
        $query_args[] = $month;
    }
    if (!empty($student_id)) {
        $query .= " AND student_id = %s";
        $query_args[] = $student_id;
    }
    if (!empty($student_name)) {
        $query .= " AND student_name LIKE %s";
        $query_args[] = '%' . $wpdb->esc_like($student_name) . '%'; // Partial match for name
    }

    $results = $wpdb->get_results($wpdb->prepare($query, $query_args));
    $student_data = [];
    if ($month > 0 && $year > 0) {
        // Group by student with daily attendance for specific month
        foreach ($results as $row) {
            $student_key = $row->student_id;
            if (!isset($student_data[$student_key])) {
                $student_data[$student_key] = [
                    'name' => $row->student_name,
                    'class' => $row->class,
                    'section' => $row->section,
                    'attendance' => array_fill(1, $days_in_month, ''),
                    'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0]
                ];
            }
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
    } else {
        // Aggregate counts without daily breakdown if no month is selected
        foreach ($results as $row) {
            $student_key = $row->student_id;
            if (!isset($student_data[$student_key])) {
                $student_data[$student_key] = [
                    'name' => $row->student_name,
                    'class' => $row->class,
                    'section' => $row->section,
                    'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0]
                ];
            }
            switch ($row->status) {
                case 'Present': $student_data[$student_key]['counts']['P']++; break;
                case 'Late': $student_data[$student_key]['counts']['L']++; break;
                case 'Absent': $student_data[$student_key]['counts']['A']++; break;
                case 'Full Day': $student_data[$student_key]['counts']['F']++; break;
                case 'Holiday': $student_data[$student_key]['counts']['H']++; break;
            }
        }
    }

    ob_start();
    ?>
    <table id="attendance-table" class="wp-list-table widefat fixed striped">
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
                <?php if ($month > 0 && $year > 0) : ?>
                    <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                        <?php
                        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
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
                    $total_days = ($month > 0 && $year > 0) ? $days_in_month - $data['counts']['H'] : array_sum($data['counts']);
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
                        <?php if ($month > 0 && $year > 0) : ?>
                            <?php for ($day = 1; $day <= $days_in_month; $day++) : ?>
                                <td><?php echo esc_html($data['attendance'][$day] ?? ''); ?></td>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="<?php echo ($month > 0 && $year > 0) ? (9 + $days_in_month) : 9; ?>">No records found</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $table_html = ob_get_clean();

    return array(
        'success' => true,
        'data' => array('html' => $table_html)
    );
}

// Frontend Interface
function display_attendance_management() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'student_attendance';
    $educational_center_id = get_educational_center_data();

    if (is_string($educational_center_id) && strpos($educational_center_id, '<p>') === 0) {
        return $educational_center_id;
    }

    $classes = $wpdb->get_col("SELECT DISTINCT class FROM $table_name WHERE education_center_id = '$educational_center_id'");
    $sections = $wpdb->get_col("SELECT DISTINCT section FROM $table_name WHERE education_center_id = '$educational_center_id'");
    $dates = $wpdb->get_results("SELECT DISTINCT YEAR(date) AS year, MONTH(date) AS month FROM $table_name WHERE education_center_id = '$educational_center_id' ORDER BY year DESC, month DESC");

    ob_clean();
    ob_start();
    ?>
    <div class="attendance-main-wrapper" style="display: flex;">
        <div class="institute-dashboard-wrapper">
            <?php
            $active_section = 'students-attendance';
            $main_section = 'student';
            include(plugin_dir_path(__FILE__) . '../sidebar.php');
            ?>
        </div>
        <div class="attendance-content-wrapper">
            <div class="attendance-management">
                <h2>Attendance Management</h2>
                <div class="search-filters">
                    <input type="text" id="search-student-id" placeholder="Student ID">
                    <input type="text" id="search-student-name" placeholder="Student Name">
                    <select id="search-class">
                        <option value="">All Classes</option>
                        <?php foreach ($classes as $class) : ?>
                            <option value="<?php echo esc_attr($class); ?>"><?php echo esc_html($class); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="search-section">
                        <option value="">All Sections</option>
                        <?php foreach ($sections as $section) : ?>
                            <option value="<?php echo esc_attr($section); ?>"><?php echo esc_html($section); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="search-month">
                        <option value="">All Months</option>
                        <?php
                        $month_names = array(1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                        foreach ($dates as $date) {
                            echo '<option value="' . esc_attr($date->month) . '" data-year="' . esc_attr($date->year) . '">' . esc_html($month_names[$date->month] . ' ' . $date->year) . '</option>';
                        }
                        ?>
                    </select>
                    
                    <button id="search-button">Search</button>
                </div>
                <div class="actions">
                    <button id="bulk-import-button">Bulk Import</button>
                    <button id="add-attendance-button">Add Attendance</button>
                </div>
                <p style="font-size: 12px; color: #666; margin-top: 5px;">Select a month to view the full attendance report.</p>
                <div class="attendance-table-wrapper" id="attendance-table-container">
                    <p class="loading-message">Loading attendance data...</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('attendance_management', 'display_attendance_management');
?>