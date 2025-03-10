<?php
if (!defined('ABSPATH')) {
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

function aspire_timetable_management_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $timetable_table = $wpdb->prefix . 'timetables';
    $class_table = $wpdb->prefix . 'class_sections';
    $subject_table = $wpdb->prefix . 'subjects';

    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'timetable-list';
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $filter_section = isset($_GET['filter_section']) ? sanitize_text_field($_GET['filter_section']) : '';

    // Generate PDF
    if ($section === 'timetable-list' && isset($_GET['action']) && $_GET['action'] === 'generate_pdf') {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $dompdf_path = dirname(__FILE__) . '/exam/dompdf/autoload.inc.php';
        if (!file_exists($dompdf_path)) {
            wp_die('Dompdf autoload file not found.');
        }
        require_once $dompdf_path;

        $where_clause = '';
        $params = [$education_center_id];
        if ($class_id) {
            $where_clause .= " AND t.class_id = %d";
            $params[] = $class_id;
        }
        if ($filter_section) {
            $where_clause .= " AND t.section = %s";
            $params[] = $filter_section;
        }
        
        $timetable = $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, c.class_name, s.subject_name 
             FROM $timetable_table t 
             JOIN $class_table c ON t.class_id = c.id 
             LEFT JOIN $subject_table s ON t.subject_id = s.subject_id 
             WHERE t.education_center_id = %s" . $where_clause . " ORDER BY t.day, t.start_time",
            $params
        ));

        if (empty($timetable)) {
            wp_die('No timetable data found.');
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', ABSPATH);
        $options->set('tempDir', sys_get_temp_dir());
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $time_slots = [];
        foreach ($timetable as $slot) {
            $time_slots[$slot->start_time . '-' . $slot->end_time] = true;
        }
        ksort($time_slots);
        $time_slot_array = array_keys($time_slots);

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { 
                    margin: 10mm; 
                    border: 2px solid #1a2b5f;
                    padding: 4mm;
                }
                body { 
                    font-family: Helvetica, sans-serif; 
                    font-size: 10pt; 
                    color: #333; 
                    line-height: 1.4;
                }
                .container {
                    width: 100%;
                    padding: 15px;
                    border: 1px solid #ccc;
                    background-color: #fff;
                }
                .header {
                    text-align: center;
                    padding-bottom: 10px;
                    border-bottom: 2px solid #1a2b5f;
                    margin-bottom: 15px;
                }
                .header h1 {
                    font-size: 16pt;
                    color: #1a2b5f;
                    margin: 0;
                }
                .header p {
                    font-size: 12pt;
                    color: #666;
                    margin: 5px 0 0;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 9pt;
                }
                th, td {
                    border: 1px solid #333;
                    padding: 6px;
                    text-align: center;
                    min-width: 100px;
                }
                th {
                    background-color: #1a2b5f;
                    color: white;
                    font-weight: bold;
                }
                .day-column {
                    background-color: #f5f5f5;
                    font-weight: bold;
                    width: 80px;
                }
                .subject {
                    font-size: 8pt;
                    word-wrap: break-word;
                }
                .empty {
                    background-color: #f0f0f0;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Weekly Timetable</h1>
                    <p>Education Center: ' . esc_html($education_center_id) . 
                    ($class_id ? ' - Class: ' . esc_html($wpdb->get_var($wpdb->prepare("SELECT class_name FROM $class_table WHERE id = %d", $class_id))) : '') .
                    ($filter_section ? ' - Section: ' . esc_html($filter_section) : '') . '</p>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Day</th>';
        foreach ($time_slot_array as $time_range) {
            $html .= '<th>' . esc_html($time_range) . '</th>';
        }
        $html .= '</tr></thead><tbody>';

        foreach ($days as $day) {
            $html .= '<tr><td class="day-column">' . $day . '</td>';
            foreach ($time_slot_array as $time_range) {
                [$start, $end] = explode('-', $time_range);
                $found = false;
                foreach ($timetable as $slot) {
                    if ($slot->day === $day && $slot->start_time === $start && $slot->end_time === $end) {
                        $html .= '<td class="subject">' . esc_html($slot->class_name . ' (' . $slot->section . ') - ' . ($slot->subject_name ?: 'N/A')) . '</td>';
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $html .= '<td class="empty">-</td>';
                }
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></body></html>';

        try {
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $pdf_content = $dompdf->output();

            while (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="timetable_' . rawurlencode($education_center_id . ($class_id ? '_class_' . $class_id : '') . ($filter_section ? '_section_' . $filter_section : '')) . '.pdf"');
            header('Content-Length: ' . strlen($pdf_content));
            header('Cache-Control: no-cache');

            echo $pdf_content;
            flush();
            exit;
        } catch (Exception $e) {
            error_log('Dompdf Error: ' . $e->getMessage());
            wp_die('Error generating PDF: ' . $e->getMessage());
        }
    }

    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php 
            $active_section = str_replace('-', '-', $section);
            include plugin_dir_path(__FILE__) . 'sidebar.php';
            ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'timetable-list':
                        echo timetable_list_shortcode();
                        break;
                    case 'timetable-add':
                        echo timetable_add_shortcode();
                        break;
                    case 'timetable-edit':
                        echo timetable_edit_shortcode();
                        break;
                    case 'timetable-delete':
                        echo timetable_delete_shortcode();
                        break;
                    default:
                        echo '<div class="alert alert-warning">Section not found.</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_timetable_management', 'aspire_timetable_management_shortcode');

// List Timetable
function timetable_list_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $filter_section = isset($_GET['filter_section']) ? sanitize_text_field($_GET['filter_section']) : '';
    
    $classes = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %s",
        $education_center_id
    ));

    $where_clause = '';
    $params = [$education_center_id];
    if ($class_id) {
        $where_clause .= " AND t.class_id = %d";
        $params[] = $class_id;
    }
    if ($filter_section) {
        $where_clause .= " AND t.section = %s";
        $params[] = $filter_section;
    }
    
    $timetable = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, c.class_name, s.subject_name 
         FROM {$wpdb->prefix}timetables t 
         JOIN {$wpdb->prefix}class_sections c ON t.class_id = c.id 
         LEFT JOIN {$wpdb->prefix}subjects s ON t.subject_id = s.subject_id 
         WHERE t.education_center_id = %s" . $where_clause . " ORDER BY t.day, t.start_time",
        $params
    ));

    $sections_data = [];
    foreach ($classes as $class) {
        $sections_data[$class->id] = array_filter(explode(',', $class->sections));
    }

    ob_start();
    ?>
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title">Timetable Directory</h3>
            <div>
                <select id="classFilter" class="form-select d-inline-block w-auto" onchange="filterTimetable()">
                    <option value="0">All Classes</option>
                    <?php foreach ($classes as $class) : ?>
                        <option value="<?php echo $class->id; ?>" <?php selected($class_id, $class->id); ?>><?php echo esc_html($class->class_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="sectionFilter" class="form-select d-inline-block w-auto" onchange="filterTimetable()">
                    <option value="">All Sections</option>
                    <?php if ($class_id && !empty($sections_data[$class_id])) : 
                        foreach ($sections_data[$class_id] as $sec) : ?>
                            <option value="<?php echo esc_attr($sec); ?>" <?php selected($filter_section, $sec); ?>><?php echo esc_html($sec); ?></option>
                        <?php endforeach;
                    endif; ?>
                </select>
                <a href="?section=timetable-list<?php echo $class_id ? '&class_id=' . $class_id : ''; ?><?php echo $filter_section ? '&filter_section=' . urlencode($filter_section) : ''; ?>&action=generate_pdf" class="btn btn-light ml-2">Export as PDF</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" style="background-color: #fff;">
                    <thead class="table-dark" style="background-color: #007bff; color: white;">
                        <tr>
                            <th style="width: 100px;">Day</th>
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $time_slots = [];
                            foreach ($timetable as $slot) {
                                $time_slots[$slot->start_time . '-' . $slot->end_time] = true;
                            }
                            ksort($time_slots);
                            $time_slot_array = array_keys($time_slots);
                            foreach ($time_slot_array as $time_range) {
                                echo '<th>' . esc_html($time_range) . '</th>';
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($timetable)) {
                            echo '<tr><td colspan="' . (count($time_slot_array) + 1) . '" class="text-center">No timetable slots found.</td></tr>';
                        } else {
                            foreach ($days as $day) {
                                echo '<tr>';
                                echo '<td class="bg-light font-weight-bold">' . $day . '</td>';
                                foreach ($time_slot_array as $time_range) {
                                    [$start, $end] = explode('-', $time_range);
                                    $found = false;
                                    foreach ($timetable as $slot) {
                                        if ($slot->day === $day && $slot->start_time === $start && $slot->end_time === $end) {
                                            echo '<td style="background-color: #e9f7ef;">';
                                            echo esc_html($slot->class_name . ' (' . $slot->section . ') - ' . ($slot->subject_name ?: 'N/A'));
                                            echo '</td>';
                                            $found = true;
                                            break;
                                        }
                                    }
                                    if (!$found) {
                                        echo '<td class="text-muted">-</td>';
                                    }
                                }
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <a href="?section=timetable-add" class="btn btn-primary">Add New Slot</a>
                <a href="?section=timetable-edit<?php echo $class_id ? '&class_id=' . $class_id : ''; ?><?php echo $filter_section ? '&filter_section=' . urlencode($filter_section) : ''; ?>" class="btn btn-info ml-2">Edit Slots</a>
                <a href="?section=timetable-delete<?php echo $class_id ? '&class_id=' . $class_id : ''; ?><?php echo $filter_section ? '&filter_section=' . urlencode($filter_section) : ''; ?>" class="btn btn-danger ml-2">Delete Slots</a>
            </div>
        </div>
    </div>
    <script>
        const sectionsData = <?php echo json_encode($sections_data); ?>;

        function updateSections() {
            const classFilter = document.getElementById('classFilter');
            const sectionFilter = document.getElementById('sectionFilter');
            const selectedClassId = classFilter.value;

            sectionFilter.innerHTML = '<option value="">All Sections</option>';
            if (selectedClassId !== '0' && sectionsData[selectedClassId]) {
                sectionsData[selectedClassId].forEach(section => {
                    if (section.trim()) {
                        const option = document.createElement('option');
                        option.value = section.trim();
                        option.text = section.trim();
                        sectionFilter.appendChild(option);
                    }
                });
                sectionFilter.disabled = false;
            } else {
                sectionFilter.disabled = true;
            }
        }

        function filterTimetable() {
            const classId = document.getElementById('classFilter').value;
            const section = document.getElementById('sectionFilter').value;
            const url = new URL(window.location.href);
            
            url.searchParams.set('section', 'timetable-list');
            if (classId === '0') {
                url.searchParams.delete('class_id');
            } else {
                url.searchParams.set('class_id', classId);
            }
            if (section === '') {
                url.searchParams.delete('filter_section');
            } else {
                url.searchParams.set('filter_section', section);
            }
            window.location.href = url.toString();
        }

        document.getElementById('classFilter').addEventListener('change', updateSections);
        updateSections();
    </script>
    <?php
    return ob_get_clean();
}

// Add Timetable Slot
function timetable_add_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $timetable_table = $wpdb->prefix . 'timetables';

    if (isset($_POST['submit_timetable']) && wp_verify_nonce($_POST['nonce'], 'timetable_nonce')) {
        $class_id = intval($_POST['class_id']);
        $section = sanitize_text_field($_POST['section']);
        $subject_id = intval($_POST['subject_id']) ?: null;
        $day = sanitize_text_field($_POST['day']);
        $start_time = sanitize_text_field($_POST['start_time']);
        $end_time = sanitize_text_field($_POST['end_time']);

        if (empty($class_id) || empty($section) || empty($day) || empty($start_time) || empty($end_time)) {
            echo '<div class="alert alert-danger">All fields except subject are required.</div>';
        } elseif ($start_time >= $end_time) {
            echo '<div class="alert alert-danger">Start time must be before end time.</div>';
        } else {
            $conflict = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $timetable_table 
                 WHERE education_center_id = %s AND class_id = %d AND section = %s AND day = %s AND (
                    (start_time <= %s AND end_time > %s) OR 
                    (start_time < %s AND end_time >= %s)
                 )",
                $education_center_id, $class_id, $section, $day, $start_time, $start_time, $end_time, $end_time
            ));

            if ($conflict > 0) {
                echo '<div class="alert alert-danger">Time conflict detected for this class and section!</div>';
            } else {
                $wpdb->insert($timetable_table, [
                    'class_id' => $class_id,
                    'section' => $section,
                    'subject_id' => $subject_id,
                    'day' => $day,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'education_center_id' => $education_center_id
                ], ['%d', '%s', '%d', '%s', '%s', '%s', '%s']);
                wp_redirect(add_query_arg(['section' => 'timetable-list'], home_url('/institute-dashboard/time-table')));
                exit;
            }
        }
    }

    $classes = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %s", $education_center_id));
    $subjects = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}subjects WHERE education_center_id = '$education_center_id' OR education_center_id IS NULL");
    $sections_data = [];
    foreach ($classes as $class) {
        $sections_data[$class->id] = array_filter(explode(',', $class->sections));
    }

    ob_start();
    ?>
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title">Add Timetable Slot</h3>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="class_id" class="form-label">Class</label>
                        <select name="class_id" id="class_id" class="form-select" required onchange="updateSections()">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class) : ?>
                                <option value="<?php echo $class->id; ?>"><?php echo esc_html($class->class_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="section" class="form-label">Section</label>
                        <select name="section" id="section" class="form-select" required>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="subject_id" class="form-label">Subject</label>
                        <select name="subject_id" id="subject_id" class="form-select">
                            <option value="">None</option>
                            <?php foreach ($subjects as $subject) : ?>
                                <option value="<?php echo $subject->subject_id; ?>"><?php echo esc_html($subject->subject_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="day" class="form-label">Day</label>
                        <select name="day" id="day" class="form-select" required>
                            <option value="">Select Day</option>
                            <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) : ?>
                                <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" name="end_time" id="end_time" class="form-control" required>
                    </div>
                    <div class="col-md-3 mb-3 align-self-end">
                        <?php wp_nonce_field('timetable_nonce', 'nonce'); ?>
                        <button type="submit" name="submit_timetable" class="btn btn-primary">Add Slot</button>
                    </div>
                </div>
            </form>
            <a href="?section=timetable-list" class="btn btn-secondary mt-3">Back to List</a>
        </div>
    </div>
    <script>
        const sectionsData = <?php echo json_encode($sections_data); ?>;
        function updateSections() {
            const classSelect = document.getElementById('class_id');
            const sectionSelect = document.getElementById('section');
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            const selectedClassId = classSelect.value;
            if (selectedClassId && sectionsData[selectedClassId]) {
                sectionsData[selectedClassId].forEach(section => {
                    if (section.trim()) {
                        const option = document.createElement('option');
                        option.value = section.trim();
                        option.text = section.trim();
                        sectionSelect.appendChild(option);
                    }
                });
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

// Edit Timetable Slot
function timetable_edit_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $timetable_table = $wpdb->prefix . 'timetables';
    $timetable_id = isset($_GET['timetable_id']) ? intval($_GET['timetable_id']) : 0;
    $class_id_filter = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_filter = isset($_GET['filter_section']) ? sanitize_text_field($_GET['filter_section']) : '';

    if ($timetable_id > 0) {
        $slot = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $timetable_table WHERE timetable_id = %d AND education_center_id = %s",
            $timetable_id, $education_center_id
        ));

        if (!$slot) {
            return '<div class="alert alert-danger">Timetable slot not found.</div>';
        }

        if (isset($_POST['submit_timetable']) && wp_verify_nonce($_POST['nonce'], 'timetable_nonce')) {
            $class_id = intval($_POST['class_id']);
            $section = sanitize_text_field($_POST['section']);
            $subject_id = intval($_POST['subject_id']) ?: null;
            $day = sanitize_text_field($_POST['day']);
            $start_time = sanitize_text_field($_POST['start_time']);
            $end_time = sanitize_text_field($_POST['end_time']);

            if (empty($class_id) || empty($section) || empty($day) || empty($start_time) || empty($end_time)) {
                echo '<div class="alert alert-danger">All fields except subject are required.</div>';
            } elseif ($start_time >= $end_time) {
                echo '<div class="alert alert-danger">Start time must be before end time.</div>';
            } else {
                $conflict = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $timetable_table 
                     WHERE education_center_id = %s AND class_id = %d AND section = %s AND day = %s AND (
                        (start_time <= %s AND end_time > %s) OR 
                        (start_time < %s AND end_time >= %s)
                     ) AND timetable_id != %d",
                    $education_center_id, $class_id, $section, $day, $start_time, $start_time, $end_time, $end_time, $timetable_id
                ));

                if ($conflict > 0) {
                    echo '<div class="alert alert-danger">Time conflict detected!</div>';
                } else {
                    $wpdb->update($timetable_table, [
                        'class_id' => $class_id,
                        'section' => $section,
                        'subject_id' => $subject_id,
                        'day' => $day,
                        'start_time' => $start_time,
                        'end_time' => $end_time
                    ], ['timetable_id' => $timetable_id, 'education_center_id' => $education_center_id], ['%d', '%s', '%d', '%s', '%s', '%s'], ['%d', '%s']);
                    $redirect_args = ['section' => 'timetable-list'];
                    if ($class_id_filter) $redirect_args['class_id'] = $class_id_filter;
                    if ($section_filter) $redirect_args['filter_section'] = $section_filter;
                    wp_redirect(add_query_arg($redirect_args, home_url('/institute-dashboard/time-tables')));
                    exit;
                }
            }
        }

        $classes = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %s", $education_center_id));
        $subjects = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}subjects WHERE education_center_id = '$education_center_id' OR education_center_id IS NULL");
        $sections_data = [];
        foreach ($classes as $class) {
            $sections_data[$class->id] = array_filter(explode(',', $class->sections));
        }

        ob_start();
        ?>
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Edit Timetable Slot</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="class_id" class="form-label">Class</label>
                            <select name="class_id" id="class_id" class="form-select" required onchange="updateSections()">
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class) : ?>
                                    <option value="<?php echo $class->id; ?>" <?php selected($slot->class_id, $class->id); ?>><?php echo esc_html($class->class_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="section" class="form-label">Section</label>
                            <select name="section" id="section" class="form-select" required>
                                <option value="">Select Section</option>
                                <?php 
                                if ($slot->class_id && !empty($sections_data[$slot->class_id])) :
                                    foreach ($sections_data[$slot->class_id] as $sec) : ?>
                                        <option value="<?php echo esc_attr($sec); ?>" <?php selected($slot->section, $sec); ?>><?php echo esc_html($sec); ?></option>
                                    <?php endforeach;
                                endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="subject_id" class="form-label">Subject</label>
                            <select name="subject_id" id="subject_id" class="form-select">
                                <option value="">None</option>
                                <?php foreach ($subjects as $subject) : ?>
                                    <option value="<?php echo $subject->subject_id; ?>" <?php selected($slot->subject_id, $subject->subject_id); ?>><?php echo esc_html($subject->subject_name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="day" class="form-label">Day</label>
                            <select name="day" id="day" class="form-select" required>
                                <option value="">Select Day</option>
                                <?php foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) : ?>
                                    <option value="<?php echo $day; ?>" <?php selected($slot->day, $day); ?>><?php echo $day; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="start_time" class="form-label">Start Time</label>
                            <input type="time" name="start_time" id="start_time" class="form-control" value="<?php echo esc_attr($slot->start_time); ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="end_time" class="form-label">End Time</label>
                            <input type="time" name="end_time" id="end_time" class="form-control" value="<?php echo esc_attr($slot->end_time); ?>" required>
                        </div>
                        <div class="col-md-3 mb-3 align-self-end">
                            <?php wp_nonce_field('timetable_nonce', 'nonce'); ?>
                            <button type="submit" name="submit_timetable" class="btn btn-primary">Update Slot</button>
                        </div>
                    </div>
                </form>
                <a href="?section=timetable-list<?php echo $class_id_filter ? '&class_id=' . $class_id_filter : ''; ?><?php echo $section_filter ? '&filter_section=' . urlencode($section_filter) : ''; ?>" class="btn btn-secondary mt-3">Back to List</a>
            </div>
        </div>
        <script>
            const sectionsData = <?php echo json_encode($sections_data); ?>;
            function updateSections() {
                const classSelect = document.getElementById('class_id');
                const sectionSelect = document.getElementById('section');
                sectionSelect.innerHTML = '<option value="">Select Section</option>';
                const selectedClassId = classSelect.value;
                if (selectedClassId && sectionsData[selectedClassId]) {
                    sectionsData[selectedClassId].forEach(section => {
                        if (section.trim()) {
                            const option = document.createElement('option');
                            option.value = section.trim();
                            option.text = section.trim();
                            sectionSelect.appendChild(option);
                        }
                    });
                }
            }
            updateSections();
        </script>
        <?php
        return ob_get_clean();
    } else {
        $where_clause = '';
        $params = [$education_center_id];
        if ($class_id_filter) {
            $where_clause .= " AND t.class_id = %d";
            $params[] = $class_id_filter;
        }
        if ($section_filter) {
            $where_clause .= " AND t.section = %s";
            $params[] = $section_filter;
        }

        $timetable = $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, c.class_name, s.subject_name 
             FROM $timetable_table t 
             JOIN {$wpdb->prefix}class_sections c ON t.class_id = c.id 
             LEFT JOIN {$wpdb->prefix}subjects s ON t.subject_id = s.subject_id 
             WHERE t.education_center_id = %s" . $where_clause . " ORDER BY t.day, t.start_time",
            $params
        ));

        ob_start();
        ?>
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Select Timetable Slot to Edit</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" style="background-color: #f0f8ff;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($timetable)) {
                                echo '<tr><td colspan="8">No timetable slots available.</td></tr>';
                            } else {
                                foreach ($timetable as $slot) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($slot->timetable_id) . '</td>';
                                    echo '<td>' . esc_html($slot->day) . '</td>';
                                    echo '<td>' . esc_html($slot->start_time) . '</td>';
                                    echo '<td>' . esc_html($slot->end_time) . '</td>';
                                    echo '<td>' . esc_html($slot->class_name) . '</td>';
                                    echo '<td>' . esc_html($slot->section) . '</td>';
                                    echo '<td>' . esc_html($slot->subject_name ?: 'N/A') . '</td>';
                                    echo '<td><a href="?section=timetable-edit&timetable_id=' . $slot->timetable_id . ($class_id_filter ? '&class_id=' . $class_id_filter : '') . ($section_filter ? '&filter_section=' . urlencode($section_filter) : '') . '" class="btn btn-sm btn-outline-primary">Edit</a></td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <a href="?section=timetable-list<?php echo $class_id_filter ? '&class_id=' . $class_id_filter : ''; ?><?php echo $section_filter ? '&filter_section=' . urlencode($section_filter) : ''; ?>" class="btn btn-secondary mt-3">Back to List</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Delete Timetable Slot
function timetable_delete_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $timetable_id = isset($_GET['timetable_id']) ? intval($_GET['timetable_id']) : 0;
    $class_id_filter = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_filter = isset($_GET['filter_section']) ? sanitize_text_field($_GET['filter_section']) : '';

    if ($timetable_id > 0 && isset($_GET['action']) && $_GET['action'] === 'delete') {
        $wpdb->delete($wpdb->prefix . 'timetables', ['timetable_id' => $timetable_id, 'education_center_id' => $education_center_id], ['%d', '%s']);
        ob_start();
        ?>
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">Timetable Slot Deletion</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">Timetable slot deleted successfully!</div>
                <a href="?section=timetable-list<?php echo $class_id_filter ? '&class_id=' . $class_id_filter : ''; ?><?php echo $section_filter ? '&filter_section=' . urlencode($section_filter) : ''; ?>" class="btn btn-primary">Back to Timetable List</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    } else {
        $where_clause = '';
        $params = [$education_center_id];
        if ($class_id_filter) {
            $where_clause .= " AND t.class_id = %d";
            $params[] = $class_id_filter;
        }
        if ($section_filter) {
            $where_clause .= " AND t.section = %s";
            $params[] = $section_filter;
        }

        $timetable = $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, c.class_name, s.subject_name 
             FROM {$wpdb->prefix}timetables t 
             JOIN {$wpdb->prefix}class_sections c ON t.class_id = c.id 
             LEFT JOIN {$wpdb->prefix}subjects s ON t.subject_id = s.subject_id 
             WHERE t.education_center_id = %s" . $where_clause . " ORDER BY t.day, t.start_time",
            $params
        ));

        ob_start();
        ?>
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">Select Timetable Slot to Delete</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" style="background-color: #fff0f0;">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Day</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Class</th>
                                <th>Section</th>
                                <th>Subject</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($timetable)) {
                                echo '<tr><td colspan="8">No timetable slots available.</td></tr>';
                            } else {
                                foreach ($timetable as $slot) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($slot->timetable_id) . '</td>';
                                    echo '<td>' . esc_html($slot->day) . '</td>';
                                    echo '<td>' . esc_html($slot->start_time) . '</td>';
                                    echo '<td>' . esc_html($slot->end_time) . '</td>';
                                    echo '<td>' . esc_html($slot->class_name) . '</td>';
                                    echo '<td>' . esc_html($slot->section) . '</td>';
                                    echo '<td>' . esc_html($slot->subject_name ?: 'N/A') . '</td>';
                                    echo '<td><a href="?section=timetable-delete&action=delete&timetable_id=' . $slot->timetable_id . ($class_id_filter ? '&class_id=' . $class_id_filter : '') . ($section_filter ? '&filter_section=' . urlencode($section_filter) : '') . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Are you sure?\');">Delete</a></td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <a href="?section=timetable-list<?php echo $class_id_filter ? '&class_id=' . $class_id_filter : ''; ?><?php echo $section_filter ? '&filter_section=' . urlencode($section_filter) : ''; ?>" class="btn btn-secondary mt-3">Back to List</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}