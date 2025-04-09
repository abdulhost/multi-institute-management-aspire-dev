<?php
if (!defined('ABSPATH')) {
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

// Main shortcode function
function aspire_timetable_management_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    
    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();

    if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'timetable-list';
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $filter_section = isset($_GET['filter_section']) ? sanitize_text_field($_GET['filter_section']) : '';

    // Main layout
    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php 
            if (!is_teacher($current_user->ID)) {


                echo render_admin_header(wp_get_current_user());
            if (!is_center_subscribed($education_center_id)) {
                return render_subscription_expired_message($education_center_id);
            }
                $active_section = str_replace('-', '-', $section);
                include plugin_dir_path(__FILE__) . 'sidebar.php';
            }
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
                        echo timetable_list_shortcode();
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_timetable_management', 'aspire_timetable_management_shortcode');

// AJAX Handler
add_action('wp_ajax_fetch_timetable_data', 'fetch_timetable_data_callback');
function fetch_timetable_data_callback() {
    global $wpdb;
    $current_user = wp_get_current_user();

    check_ajax_referer('timetable_nonce', 'nonce');

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $filter_section = isset($_POST['filter_section']) ? sanitize_text_field($_POST['filter_section']) : '';

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

    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'Database error: ' . $wpdb->last_error]);
    }

    $classes = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %s",
        $education_center_id
    ));

    $sections_data = [];
    foreach ($classes as $class) {
        if (!is_object($class) || !isset($class->id) || !isset($class->sections)) {
            continue;
        }
        $sections_data[$class->id] = array_filter(explode(',', $class->sections));
    }

    ob_start();
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $time_slots = [];
    foreach ($timetable as $slot) {
        if (!is_object($slot) || !isset($slot->start_time) || !isset($slot->end_time)) {
            continue;
        }
        $time_slots[$slot->start_time . '-' . $slot->end_time] = true;
    }
    ksort($time_slots);
    $time_slot_array = array_keys($time_slots);
    ?>
    <table class="table table-bordered" style="background-color: #fff;">
        <thead class="table-dark" style="background-color: #007bff; color: white;">
            <tr>
                <th style="width: 100px;">Day</th>
                <?php foreach ($time_slot_array as $time_range) : ?>
                    <th><?php echo esc_html($time_range); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($timetable)) : ?>
                <tr><td colspan="<?php echo count($time_slot_array) + 1; ?>" class="text-center">No timetable slots found.</td></tr>
            <?php else : ?>
                <?php foreach ($days as $day) : ?>
                    <tr>
                        <td class="bg-light font-weight-bold"><?php echo $day; ?></td>
                        <?php foreach ($time_slot_array as $time_range) : 
                            [$start, $end] = explode('-', $time_range);
                            $found = false;
                            foreach ($timetable as $slot) :
                                if (!is_object($slot)) continue;
                                if ($slot->day === $day && $slot->start_time === $start && $slot->end_time === $end) : ?>
                                    <td style="background-color: #e9f7ef;">
                                        <?php echo esc_html($slot->class_name . ' (' . $slot->section . ') - ' . ($slot->subject_name ?: 'N/A')); ?>
                                    </td>
                                    <?php $found = true;
                                    break;
                                endif;
                            endforeach;
                            if (!$found) : ?>
                                <td class="text-muted">-</td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php
    $table_html = ob_get_clean();

    wp_send_json_success([
        'table_html' => $table_html,
        'sections_data' => $sections_data
    ]);
}

// Timetable List Shortcode
function timetable_list_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $filter_section = isset($_GET['filter_section']) ? sanitize_text_field($_GET['filter_section']) : '';

    $classes = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %s",
        $education_center_id
    ));

    $sections_data = [];
    foreach ($classes as $class) {
        if (!is_object($class) || !isset($class->id) || !isset($class->sections)) {
            continue;
        }
        $sections_data[$class->id] = array_filter(explode(',', $class->sections));
    }

    ob_start();
    ?>
    <div class="card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title">Timetable Directory</h3>
            <div>
                <select id="classFilter" class="form-select d-inline-block w-auto">
                    <option value="0">All Classes</option>
                    <?php foreach ($classes as $class) : ?>
                        <option value="<?php echo $class->id; ?>" <?php selected($class_id, $class->id); ?>><?php echo esc_html($class->class_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="sectionFilter" class="form-select d-inline-block w-auto">
                    <option value="">All Sections</option>
                    <?php if ($class_id && !empty($sections_data[$class_id])) : 
                        foreach ($sections_data[$class_id] as $sec) : ?>
                            <option value="<?php echo esc_attr($sec); ?>" <?php selected($filter_section, $sec); ?>><?php echo esc_html($sec); ?></option>
                        <?php endforeach;
                    endif; ?>
                </select>
                <button class="btn btn-light ml-2 generate-pdf-btn" 
                        data-class-id="<?php echo esc_attr($class_id); ?>" 
                        data-filter-section="<?php echo esc_attr($filter_section); ?>" 
                        data-nonce="<?php echo wp_create_nonce('generate_timetable_pdf'); ?>">Export as PDF</button>            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive" id="timetable-container">
                <p>Loading timetable...</p>
            </div>
            <div class="mt-3">
                <a href="?section=timetable-add" class="btn btn-primary">Add New Slot</a>
                <a href="?section=timetable-edit<?php echo $class_id ? '&class_id=' . $class_id : ''; ?><?php echo $filter_section ? '&filter_section=' . urlencode($filter_section) : ''; ?>" class="btn btn-info ml-2">Edit Slots</a>
                <a href="?section=timetable-delete<?php echo $class_id ? '&class_id=' . $class_id : ''; ?><?php echo $filter_section ? '&filter_section=' . urlencode($filter_section) : ''; ?>" class="btn btn-danger ml-2">Delete Slots</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sectionsData = <?php echo json_encode($sections_data); ?>;
            const nonce = '<?php echo wp_create_nonce('timetable_nonce'); ?>';
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

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

            function fetchTimetable() {
                const classId = document.getElementById('classFilter').value;
                const section = document.getElementById('sectionFilter').value;
                const container = document.getElementById('timetable-container');

                container.innerHTML = '<p>Loading...</p>';

                const data = new FormData();
                data.append('action', 'fetch_timetable_data');
                data.append('nonce', nonce);
                data.append('class_id', classId);
                data.append('filter_section', section);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: data
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        container.innerHTML = data.data.table_html;
                        Object.assign(sectionsData, data.data.sections_data);
                        updateSections();
                    } else {
                        container.innerHTML = '<p class="text-danger">Error: ' + (data.data?.message || 'Unable to load timetable') + '</p>';
                    }
                })
                .catch(error => {
                    console.error('Fetch Error:', error);
                    container.innerHTML = '<p class="text-danger">Error loading timetable: ' + error.message + '</p>';
                });
            }
// AJAX for PDF Generation
document.querySelector('.generate-pdf-btn').addEventListener('click', function(e) {
                e.preventDefault();
                const button = this;
                const classId = button.getAttribute('data-class-id');
                const filterSection = button.getAttribute('data-filter-section');
                const pdfNonce = button.getAttribute('data-nonce');

                button.disabled = true;
                button.textContent = 'Generating...';

                const data = new FormData();
                data.append('action', 'generate_timetable_pdf');
                data.append('nonce', pdfNonce);
                data.append('class_id', classId);
                data.append('filter_section', filterSection);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: data
                })
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `timetable_${classId || 'all'}_${filterSection || 'all'}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                    button.disabled = false;
                    button.textContent = 'Export as PDF';
                })
                .catch(error => {
                    console.error('PDF Generation Error:', error);
                    alert('An error occurred while generating the PDF.');
                    button.disabled = false;
                    button.textContent = 'Export as PDF';
                });
            });

            document.getElementById('classFilter').addEventListener('change', () => {
                updateSections();
                fetchTimetable();
                document.querySelector('.generate-pdf-btn').setAttribute('data-class-id', document.getElementById('classFilter').value);
            });
            document.getElementById('sectionFilter').addEventListener('change', () => {
                fetchTimetable();
                document.querySelector('.generate-pdf-btn').setAttribute('data-filter-section', document.getElementById('sectionFilter').value);
            });
            // document.getElementById('classFilter').addEventListener('change', () => {
            //     updateSections();
            //     fetchTimetable();
            // });
            // document.getElementById('sectionFilter').addEventListener('change', fetchTimetable);

            // Initial load
            updateSections();
            fetchTimetable();
        });
    </script>
    <?php
    return ob_get_clean();
}
// Updated AJAX Handler for PDF Generation using generate_detailed_pdf
// New AJAX Handler for PDF Generation
add_action('wp_ajax_generate_timetable_pdf', 'generate_timetable_pdf_callback');
function generate_timetable_pdf_callback() {
    global $wpdb;
    $current_user = wp_get_current_user();

    check_ajax_referer('generate_timetable_pdf', 'nonce');

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $filter_section = isset($_POST['filter_section']) ? sanitize_text_field($_POST['filter_section']) : '';

    $timetable_table = $wpdb->prefix . 'timetables';
    $class_table = $wpdb->prefix . 'class_sections';
    $subject_table = $wpdb->prefix . 'subjects';

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
        wp_send_json_error('No timetable data found.');
        wp_die();
    }

    require_once dirname(__FILE__) . '/exam/dompdf/autoload.inc.php';

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
        if (!is_object($slot) || !isset($slot->start_time) || !isset($slot->end_time)) {
            continue;
        }
        $time_slots[$slot->start_time . '-' . $slot->end_time] = true;
    }
    ksort($time_slots);
    $time_slot_array = array_keys($time_slots);

    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @page { margin: 10mm; border: 2px solid #1a2b5f; padding: 4mm; }
            body { font-family: Helvetica, sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
            .container { width: 100%; padding: 15px; border: 1px solid #ccc; background-color: #fff; }
            .header { text-align: center; padding-bottom: 10px; border-bottom: 2px solid #1a2b5f; margin-bottom: 15px; }
            .header h1 { font-size: 16pt; color: #1a2b5f; margin: 0; }
            .header p { font-size: 12pt; color: #666; margin: 5px 0 0; }
            table { width: 100%; border-collapse: collapse; font-size: 9pt; }
            th, td { border: 1px solid #333; padding: 6px; text-align: center; min-width: 100px; }
            th { background-color: #1a2b5f; color: white; font-weight: bold; }
            .day-column { background-color: #f5f5f5; font-weight: bold; width: 80px; }
            .subject { font-size: 8pt; word-wrap: break-word; }
            .empty { background-color: #f0f0f0; }
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
                    <tr><th>Day</th>';
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

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="timetable_' . rawurlencode($education_center_id . ($class_id ? '_class_' . $class_id : '') . ($filter_section ? '_section_' . $filter_section : '')) . '.pdf"');
    header('Content-Length: ' . strlen($dompdf->output()));
    echo $dompdf->output();
    wp_die();
}

// Add Timetable Slot (unchanged)
function timetable_add_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
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
                // wp_redirect(add_query_arg(['section' => 'timetable-list'], remove_query_arg(['action'], home_url('/institute-dashboard/time-table'))));
                if (is_teacher($current_user->ID)) {
                   // Redirect to the same page
wp_redirect($_SERVER['REQUEST_URI']);


                } else {
// Redirect to the same page
wp_redirect($_SERVER['REQUEST_URI']);

                }
                exit;
            }
        }
    }

    $classes = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %s", $education_center_id));
    $subjects = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}subjects WHERE education_center_id = '$education_center_id' OR education_center_id IS NULL");
    $sections_data = [];
    foreach ($classes as $class) {
        if (!is_object($class) || !isset($class->id) || !isset($class->sections)) {
            continue;
        }
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


// Edit Timetable Slot with Custom Modal and AJAX
function timetable_edit_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $timetable_table = $wpdb->prefix . 'timetables';
    $class_id_filter = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_filter = isset($_GET['filter_section']) ? sanitize_text_field($_GET['filter_section']) : '';

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
                                if (!is_object($slot)) continue;
                                echo '<tr>';
                                echo '<td>' . esc_html($slot->timetable_id) . '</td>';
                                echo '<td>' . esc_html($slot->day) . '</td>';
                                echo '<td>' . esc_html($slot->start_time) . '</td>';
                                echo '<td>' . esc_html($slot->end_time) . '</td>';
                                echo '<td>' . esc_html($slot->class_name) . '</td>';
                                echo '<td>' . esc_html($slot->section) . '</td>';
                                echo '<td>' . esc_html($slot->subject_name ?: 'N/A') . '</td>';
                                echo '<td><button class="btn btn-sm btn-outline-primary edit-timetable-btn" data-timetable-id="' . esc_attr($slot->timetable_id) . '" data-nonce="' . wp_create_nonce('edit_timetable_' . $slot->timetable_id) . '">Edit</button></td>';
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

    <!-- Custom Modal -->
    <div class="modal" id="editTimetableModal">
        <div class="modal-content">
            <span class="modal-close">&times;</span>
            <h5 class="bg-info text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Edit Timetable Slot</h5>
            <div id="edit-timetable-form-container">
                <!-- Form will be loaded here via AJAX -->
            </div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Close</button>
                <button type="button" class="btn btn-primary update-timetable-btn">Update Slot</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const editButtons = document.querySelectorAll('.edit-timetable-btn');
            const modal = document.getElementById('editTimetableModal');
            const formContainer = document.getElementById('edit-timetable-form-container');
            const updateButton = document.querySelector('.update-timetable-btn');
            const closeButtons = document.querySelectorAll('.modal-close, .modal-close-btn');

            function showModal() {
                modal.style.display = 'block';
            }

            function hideModal() {
                modal.style.display = 'none';
                formContainer.innerHTML = ''; // Clear form on close
            }

            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const timetableId = this.getAttribute('data-timetable-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_timetable_edit_form');
                    data.append('timetable_id', timetableId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            formContainer.innerHTML = data.data.form_html;
                            showModal();
                            attachFormSubmitHandler(timetableId);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load edit form'));
                        }
                        this.disabled = false;
                        this.textContent = 'Edit';
                    })
                    .catch(error => {
                        console.error('Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Edit';
                    });
                });
            });

            closeButtons.forEach(button => {
                button.addEventListener('click', hideModal);
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hideModal();
                }
            });

            function attachFormSubmitHandler(timetableId) {
                const form = formContainer.querySelector('#edit-timetable-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        updateButton.disabled = true;
                        updateButton.textContent = 'Updating...';

                        const formData = new FormData(this);
                        formData.append('action', 'update_timetable_slot');
                        formData.append('timetable_id', timetableId);

                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                formContainer.innerHTML = '<div class="alert alert-success">Timetable slot updated successfully!</div>';
                                // setTimeout(() => {
                                //     hideModal();
                                //     location.reload(); // Refresh table to reflect changes
                                // }, 1500);
                                header("Refresh:0");

                            } else {
                                formContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error updating timetable slot') + '</div>';
                                updateButton.disabled = false;
                                updateButton.textContent = 'Update Slot';
                            }
                        })
                        .catch(error => {
                            console.error('Update Error:', error);
                            formContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                            updateButton.disabled = false;
                            updateButton.textContent = 'Update Slot';
                        });
                    });
                }
            }

            updateButton.addEventListener('click', function() {
                const form = formContainer.querySelector('#edit-timetable-form');
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handler for Loading Edit Form (unchanged from previous)
add_action('wp_ajax_load_timetable_edit_form', 'load_timetable_edit_form_callback');
function load_timetable_edit_form_callback() {
    global $wpdb;
    $current_user = wp_get_current_user();

    $timetable_id = isset($_POST['timetable_id']) ? intval($_POST['timetable_id']) : 0;
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'edit_timetable_' . $timetable_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        wp_die();
    }

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $timetable_table = $wpdb->prefix . 'timetables';
    $slot = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $timetable_table WHERE timetable_id = %d AND education_center_id = %s",
        $timetable_id, $education_center_id
    ));

    if (!$slot) {
        wp_send_json_error(['message' => 'Timetable slot not found']);
        wp_die();
    }

    $classes = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %s",
        $education_center_id
    ));
    $subjects = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}subjects WHERE education_center_id = '$education_center_id' OR education_center_id IS NULL"
    );
    $sections_data = [];
    foreach ($classes as $class) {
        if (!is_object($class) || !isset($class->id) || !isset($class->sections)) {
            continue;
        }
        $sections_data[$class->id] = array_filter(explode(',', $class->sections));
    }

    ob_start();
    ?>
    <form id="edit-timetable-form">
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
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('timetable_nonce'); ?>">
    </form>
    <script>
        function updateSections() {
            const classSelect = document.getElementById('class_id');
            const sectionSelect = document.getElementById('section');
            const sectionsData = <?php echo json_encode($sections_data); ?>;
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
    $form_html = ob_get_clean();
    wp_send_json_success(['form_html' => $form_html]);
    wp_die();
}

// AJAX Handler for Updating Timetable Slot (unchanged)
add_action('wp_ajax_update_timetable_slot', 'update_timetable_slot_callback');
function update_timetable_slot_callback() {
    global $wpdb;
    $current_user = wp_get_current_user();

    check_ajax_referer('timetable_nonce', 'nonce');

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $timetable_id = isset($_POST['timetable_id']) ? intval($_POST['timetable_id']) : 0;
    $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
    $section = isset($_POST['section']) ? sanitize_text_field($_POST['section']) : '';
    $subject_id = isset($_POST['subject_id']) ? intval($_POST['subject_id']) ?: null : null;
    $day = isset($_POST['day']) ? sanitize_text_field($_POST['day']) : '';
    $start_time = isset($_POST['start_time']) ? sanitize_text_field($_POST['start_time']) : '';
    $end_time = isset($_POST['end_time']) ? sanitize_text_field($_POST['end_time']) : '';

    if (empty($class_id) || empty($section) || empty($day) || empty($start_time) || empty($end_time)) {
        wp_send_json_error(['message' => 'All fields except subject are required']);
        wp_die();
    }

    if ($start_time >= $end_time) {
        wp_send_json_error(['message' => 'Start time must be before end time']);
        wp_die();
    }

    $timetable_table = $wpdb->prefix . 'timetables';
    $conflict = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $timetable_table 
         WHERE education_center_id = %s AND class_id = %d AND section = %s AND day = %s AND (
            (start_time <= %s AND end_time > %s) OR 
            (start_time < %s AND end_time >= %s)
         ) AND timetable_id != %d",
        $education_center_id, $class_id, $section, $day, $start_time, $start_time, $end_time, $end_time, $timetable_id
    ));

    if ($conflict > 0) {
        wp_send_json_error(['message' => 'Time conflict detected']);
        wp_die();
    }

    $updated = $wpdb->update(
        $timetable_table,
        [
            'class_id' => $class_id,
            'section' => $section,
            'subject_id' => $subject_id,
            'day' => $day,
            'start_time' => $start_time,
            'end_time' => $end_time
        ],
        ['timetable_id' => $timetable_id, 'education_center_id' => $education_center_id],
        ['%d', '%s', '%d', '%s', '%s', '%s'],
        ['%d', '%s']
    );

    if ($updated === false) {
        wp_send_json_error(['message' => 'Database update failed']);
    } else {
        wp_send_json_success(['message' => 'Timetable slot updated successfully']);
    }
    wp_die();
}

function timetable_delete_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $timetable_table = $wpdb->prefix . 'timetables';
    $class_id_filter = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
    $section_filter = isset($_GET['filter_section']) ? sanitize_text_field($_GET['filter_section']) : '';

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
                                if (!is_object($slot)) continue;
                                echo '<tr>';
                                echo '<td>' . esc_html($slot->timetable_id) . '</td>';
                                echo '<td>' . esc_html($slot->day) . '</td>';
                                echo '<td>' . esc_html($slot->start_time) . '</td>';
                                echo '<td>' . esc_html($slot->end_time) . '</td>';
                                echo '<td>' . esc_html($slot->class_name) . '</td>';
                                echo '<td>' . esc_html($slot->section) . '</td>';
                                echo '<td>' . esc_html($slot->subject_name ?: 'N/A') . '</td>';
                                echo '<td><button class="btn btn-sm btn-outline-danger delete-timetable-btn" data-timetable-id="' . esc_attr($slot->timetable_id) . '" data-nonce="' . wp_create_nonce('delete_timetable_' . $slot->timetable_id) . '">Delete</button></td>';
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

    <!-- Custom Modal -->
    <div class="modal" id="deleteTimetableModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-danger text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Deletion</h5>
            <div id="delete-timetable-container">
                <!-- Confirmation message will be loaded here via AJAX -->
            </div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Cancel</button>
                <button type="button" class="btn btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>



    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const deleteButtons = document.querySelectorAll('.delete-timetable-btn');
            const modal = document.getElementById('deleteTimetableModal');
            const container = document.getElementById('delete-timetable-container');
            const confirmButton = document.querySelector('.confirm-delete-btn');
            const closeButtons = document.querySelectorAll('.modal-close, .modal-close-btn');

            function showModal() {
                modal.style.display = 'block';
            }

            function hideModal() {
                modal.style.display = 'none';
                container.innerHTML = ''; // Clear content on close
            }

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const timetableId = this.getAttribute('data-timetable-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_timetable_delete_confirm');
                    data.append('timetable_id', timetableId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            container.innerHTML = data.data.confirm_html;
                            showModal();
                            attachConfirmHandler(timetableId, nonce);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load delete confirmation'));
                        }
                        this.disabled = false;
                        this.textContent = 'Delete';
                    })
                    .catch(error => {
                        console.error('Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Delete';
                    });
                });
            });

            closeButtons.forEach(button => {
                button.addEventListener('click', hideModal);
            });

            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hideModal();
                }
            });

            function attachConfirmHandler(timetableId, nonce) {
                confirmButton.onclick = function() {
                    confirmButton.disabled = true;
                    confirmButton.textContent = 'Deleting...';

                    const data = new FormData();
                    data.append('action', 'delete_timetable_slot');
                    data.append('timetable_id', timetableId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            container.innerHTML = '<div class="alert alert-success">Timetable slot deleted successfully!</div>';
                            // setTimeout(() => {
                            //     hideModal();
                            //     location.reload(); // Refresh table to reflect changes
                            // }, 1500);
                            header("Refresh:0");

                        } else {
                            container.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error deleting timetable slot') + '</div>';
                            confirmButton.disabled = false;
                            confirmButton.textContent = 'Delete';
                        }
                    })
                    .catch(error => {
                        console.error('Delete Error:', error);
                        container.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                        confirmButton.disabled = false;
                        confirmButton.textContent = 'Delete';
                    });
                };
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

// New AJAX Handler for Loading Delete Confirmation
add_action('wp_ajax_load_timetable_delete_confirm', 'load_timetable_delete_confirm_callback');
function load_timetable_delete_confirm_callback() {
    global $wpdb;
    $current_user = wp_get_current_user();

    $timetable_id = isset($_POST['timetable_id']) ? intval($_POST['timetable_id']) : 0;
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'delete_timetable_' . $timetable_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        wp_die();
    }

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $timetable_table = $wpdb->prefix . 'timetables';
    $slot = $wpdb->get_row($wpdb->prepare(
        "SELECT t.*, c.class_name, s.subject_name 
         FROM $timetable_table t 
         JOIN {$wpdb->prefix}class_sections c ON t.class_id = c.id 
         LEFT JOIN {$wpdb->prefix}subjects s ON t.subject_id = s.subject_id 
         WHERE t.timetable_id = %d AND t.education_center_id = %s",
        $timetable_id, $education_center_id
    ));

    if (!$slot) {
        wp_send_json_error(['message' => 'Timetable slot not found']);
        wp_die();
    }

    ob_start();
    ?>
    <p>Are you sure you want to delete the following timetable slot?</p>
    <ul>
        <li><strong>ID:</strong> <?php echo esc_html($slot->timetable_id); ?></li>
        <li><strong>Day:</strong> <?php echo esc_html($slot->day); ?></li>
        <li><strong>Time:</strong> <?php echo esc_html($slot->start_time . ' - ' . $slot->end_time); ?></li>
        <li><strong>Class:</strong> <?php echo esc_html($slot->class_name); ?></li>
        <li><strong>Section:</strong> <?php echo esc_html($slot->section); ?></li>
        <li><strong>Subject:</strong> <?php echo esc_html($slot->subject_name ?: 'N/A'); ?></li>
    </ul>
    <?php
    $confirm_html = ob_get_clean();
    wp_send_json_success(['confirm_html' => $confirm_html]);
    wp_die();
}

// New AJAX Handler for Deleting Timetable Slot
add_action('wp_ajax_delete_timetable_slot', 'delete_timetable_slot_callback');
function delete_timetable_slot_callback() {
    global $wpdb;
    $current_user = wp_get_current_user();

    $timetable_id = isset($_POST['timetable_id']) ? intval($_POST['timetable_id']) : 0;
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'delete_timetable_' . $timetable_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        wp_die();
    }

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        
    }
    $deleted = $wpdb->delete(
        $wpdb->prefix . 'timetables',
        ['timetable_id' => $timetable_id, 'education_center_id' => $education_center_id],
        ['%d', '%s']
    );

    if ($deleted === false || $deleted === 0) {
        wp_send_json_error(['message' => 'Failed to delete timetable slot']);
    } else {
        wp_send_json_success(['message' => 'Timetable slot deleted successfully']);
    }
    wp_die();
}

