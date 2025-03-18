<?php
if (!defined('ABSPATH')) {
    exit;
}

$dompdf_path = plugin_dir_path(__FILE__) . 'dompdf/autoload.inc.php';
if (file_exists($dompdf_path)) {
    require_once $dompdf_path;
} else {
    wp_die('Dompdf autoload file not found at: ' . $dompdf_path);
}

use Dompdf\Dompdf;
use Dompdf\Options;

function get_educational_center_data2() {
    $current_user = wp_get_current_user();
    if (is_teacher($current_user->ID)) { 
        $educational_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $educational_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
    $args = array(
        'post_type' => 'educational-center',
        'meta_query' => array(
            array(
                'key' => 'educational_center_id',
                'value' => $educational_center_id,
                'compare' => '='
            )
        ),
        'posts_per_page' => 1,
        'post_status' => 'publish'
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $education_center_id = get_field('educational_center_id', $post_id) ?: $post_id;
        $institute_name = get_the_title($post_id);
        $logo = get_field('institute_logo', $post_id);
        $logo_url = is_array($logo) && isset($logo['url']) ? esc_url($logo['url']) : ($logo ? wp_get_attachment_url($logo) : '');
        wp_reset_postdata();
        return array(
            'id' => $education_center_id,
            'name' => $institute_name,
            'logo_url' => $logo_url
        );
    }
    return false;
}

function aspire_reports_dashboard_shortcode($atts) {
    global $wpdb;
    $current_user = wp_get_current_user();

    if (!is_user_logged_in()) {
        return '<p>You must be logged in to view this dashboard.</p>';
    }

    $center_data = get_educational_center_data2();
    if (empty($center_data)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    $education_center_id = $center_data['id'];
    $institute_name = $center_data['name'];
    $logo_url = $center_data['logo_url'];
    $logo_path = $logo_url ? (file_exists(str_replace(home_url(), ABSPATH, $logo_url)) ? 'file://' . str_replace('\\', '/', realpath(str_replace(home_url(), ABSPATH, $logo_url))) : $logo_url) : '';

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'view-reports';

    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php 
            if (!is_teacher($current_user->ID)) {
                $active_section = $section;
                include plugin_dir_path(__FILE__) . '../sidebar.php';
            }
            ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'view-reports':
                        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
                        ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">View Reports</h3>
                                <form method="GET" class="d-flex">
                                    <input type="hidden" name="section" value="view-reports">
                                    <input type="text" name="search" class="form-control me-2" placeholder="Search exams..." value="<?php echo esc_attr($search_query); ?>">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </form>
                            </div>
                            <div class="card-body">
                                <h4>Available Reports</h4>
                                <?php
                                $exams_query = $search_query ? 
                                    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %s AND name LIKE %s", $education_center_id, '%' . $wpdb->esc_like($search_query) . '%') : 
                                    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %s", $education_center_id);
                                $exams = $wpdb->get_results($exams_query);

                                if (empty($exams)) {
                                    echo '<div class="alert alert-info">' . ($search_query ? 'No exams found matching your search.' : 'No exams found to generate reports for.') . '</div>';
                                } else {
                                    echo '<div class="table-responsive">';
                                    echo '<table class="table table-bordered table-striped">';
                                    echo '<thead><tr><th>Exam Name</th><th>Average Score</th><th>Students</th><th>Actions</th></tr></thead>';
                                    echo '<tbody>';
                                    foreach ($exams as $exam) {
                                        $results = $wpdb->get_results($wpdb->prepare(
                                            "SELECT AVG(marks) as avg_marks, COUNT(DISTINCT student_id) as student_count 
                                             FROM {$wpdb->prefix}exam_results 
                                             WHERE exam_id = %d AND education_center_id = %s",
                                            $exam->id, $education_center_id
                                        ));
                                        $avg_marks = $results[0]->avg_marks ? round($results[0]->avg_marks, 2) : 'N/A';
                                        $student_count = $results[0]->student_count ?: 0;
                                        echo '<tr>';
                                        echo '<td>' . esc_html($exam->name) . '</td>';
                                        echo '<td>' . $avg_marks . '</td>';
                                        echo '<td>' . $student_count . '</td>';
                                        echo '<td>';
                                        echo '<button class="btn btn-sm btn-primary me-2 generate-report-btn" data-exam-id="' . esc_attr($exam->id) . '" data-nonce="' . wp_create_nonce('generate_report_' . $exam->id) . '">Generate Report</button>';
                                        echo '<button class="btn btn-sm btn-info view-details-btn" data-exam-id="' . esc_attr($exam->id) . '" data-nonce="' . wp_create_nonce('view_details_' . $exam->id) . '">View Details</button>';
                                        echo '</td>';
                                        echo '</tr>';
                                    }
                                    echo '</tbody></table></div>';
                                }
                                ?>
                                <div id="report-details-container" class="mt-4" style="display: none;"></div>
                            </div>
                        </div>
                        <?php
                        break;

                    case 'generate-reports':
                        $exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
                        $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

                        if (!$exam_id) {
                            ?>
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Generate a Report</h3>
                                </div>
                                <div class="card-body text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#1a2b5f">
                                        <path d="M320-240v-480h320v480H320Zm40-40h240v-400H360v400Zm120-120Zm-160 160v-480H160q-33 0-56.5-23.5T80-800v-80h800v80q0 33-23.5 56.5T800-720H640v480H480Zm0-560Zm160 280Z"/>
                                    </svg>
                                    <h4 class="mt-3">Select an Exam to Generate a Report</h4>
                                    <p class="text-muted">Choose an exam from the list below to create a detailed performance report.</p>
                                    <form method="GET" class="mt-4">
                                        <div class="input-group w-50 mx-auto">
                                            <select name="exam_id" class="form-select" required>
                                                <option value="">-- Select Exam --</option>
                                                <?php
                                                $exams = $wpdb->get_results($wpdb->prepare(
                                                    "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %s",
                                                    $education_center_id
                                                ));
                                                foreach ($exams as $exam) {
                                                    echo '<option value="' . $exam->id . '">' . esc_html($exam->name) . '</option>';
                                                }
                                                ?>
                                            </select>
                                            <input type="hidden" name="section" value="generate-reports">
                                            <button type="submit" class="btn btn-primary">Proceed</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <?php
                            break;
                        }

                        $exam = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
                            $exam_id, $education_center_id
                        ));
                        if (!$exam) {
                            echo '<div class="alert alert-danger">Exam not found.</div>';
                            break;
                        }

                        $subjects = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
                            $exam_id
                        ));
                        $results = $wpdb->get_results($wpdb->prepare(
                            "SELECT er.*, es.subject_name 
                             FROM {$wpdb->prefix}exam_results er 
                             JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
                             WHERE er.exam_id = %d AND er.education_center_id = %s",
                            $exam_id, $education_center_id
                        ), ARRAY_A);

                        if (empty($results)) {
                            echo '<div class="alert alert-info">No results available to generate a report.</div>';
                            break;
                        }

                        $student_marks = [];
                        foreach ($results as $result) {
                            $student_marks[$result['student_id']]['marks'][$result['subject_id']] = $result['marks'];
                        }

                        $filtered_results = array_filter($student_marks, function($data) use ($wpdb, $search_query) {
                            if (!$search_query) return true;
                            $student = $wpdb->get_row($wpdb->prepare(
                                "SELECT post_title FROM {$wpdb->prefix}posts p 
                                 JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
                                 WHERE pm.meta_key = 'student_id' AND pm.meta_value = %s 
                                 AND p.post_status = 'publish'",
                                $data['student_id']
                            ));
                            $student_name = $student ? $student->post_title : 'Unknown (' . $data['student_id'] . ')';
                            return stripos($student_name, $search_query) !== false;
                        }, ARRAY_FILTER_USE_KEY);

                        if (isset($_POST['generate_pdf']) && wp_verify_nonce($_POST['nonce'], 'generate_report_nonce')) {
                            error_log('Filtered Results: ' . print_r($filtered_results, true)); // Debug log
                            generate_detailed_pdf($exam, $subjects, $student_marks, $logo_path, $institute_name, $filtered_results);
                        }

                        ?>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title">Generate Report for <?php echo esc_html($exam->name); ?></h3>
                                <div>
                                    <form method="GET" class="d-flex me-2" style="display:inline;">
                                        <input type="hidden" name="section" value="generate-reports">
                                        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                                        <input type="text" name="search" class="form-control me-2" placeholder="Search students..." value="<?php echo esc_attr($search_query); ?>">
                                        <button type="submit" class="btn btn-primary">Search</button>
                                    </form>
                                    <a href="?section=view-reports" class="btn btn-secondary">Back to Reports</a>
                                </div>
                            </div>
                            <div class="card-body">
                                <h4>Exam Overview</h4>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <p><strong>Exam Name:</strong> <?php echo esc_html($exam->name); ?></p>
                                                <p><strong>Exam Date:</strong> <?php echo esc_html($exam->exam_date ?: 'N/A'); ?></p>
                                                <p><strong>Class/Course:</strong> <?php echo esc_html($exam->class_id ?: 'N/A'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <p><strong>Total Subjects:</strong> <?php echo count($subjects); ?></p>
                                                <p><strong>Total Students:</strong> <?php echo count($student_marks); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h4>Performance Preview</h4>
                                <?php if (empty($filtered_results)) { ?>
                                    <div class="alert alert-info">No students found matching your search.</div>
                                <?php } else { ?>
                                    <div class="row">
                                        <?php
                                        foreach ($filtered_results as $student_id => $data) {
                                            $student = $wpdb->get_row($wpdb->prepare(
                                                "SELECT post_title FROM {$wpdb->prefix}posts p 
                                                 JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
                                                 WHERE pm.meta_key = 'student_id' AND pm.meta_value = %s 
                                                 AND p.post_status = 'publish'",
                                                $student_id
                                            ));
                                            $student_name = $student ? $student->post_title : 'Unknown (' . esc_html($student_id) . ')';
                                            $avg_marks = !empty($student_marks[$student_id]['marks']) ? array_sum($student_marks[$student_id]['marks']) / count($student_marks[$student_id]['marks']) : 0;
                                            ?>
                                            <div class="col-md-4 mb-3">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <h5 class="card-title"><?php echo esc_html($student_name); ?></h5>
                                                        <p class="card-text">Average Marks: <span class="text-primary"><?php echo round($avg_marks, 2); ?></span></p>
                                                        <a href="?section=generate-reports&exam_id=<?php echo $exam_id; ?>&student_id=<?php echo urlencode($student_id); ?>&view_details=true" class="btn btn-sm btn-info">View Details</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </div>
                                <?php } ?>

                                <form method="POST" class="mt-3 text-center">
                                    <?php wp_nonce_field('generate_report_nonce', 'nonce'); 
                                    // <!-- <button type="submit" name="generate_pdf" class="btn btn-success btn-lg">Download PDF Report</button> -->
                                    echo '<button class="btn btn-sm btn-primary me-2 generate-report-btn" data-exam-id="' . esc_attr($exam->id) . '" data-nonce="' . wp_create_nonce('generate_report_' . $exam->id) . '">Download PDF Report</button>';
                                   ?> </form>

                                <?php
                                if (isset($_GET['view_details']) && isset($_GET['student_id']) && isset($_GET['exam_id'])) {
                                    $detail_student_id = sanitize_text_field($_GET['student_id']);
                                    $detail_exam_id = intval($_GET['exam_id']);
                                    $detail_student = $wpdb->get_row($wpdb->prepare(
                                        "SELECT post_title FROM {$wpdb->prefix}posts p 
                                         JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
                                         WHERE pm.meta_key = 'student_id' AND pm.meta_value = %s 
                                         AND p.post_status = 'publish'",
                                        $detail_student_id
                                    ));
                                    $student_name = $detail_student ? $detail_student->post_title : 'Unknown (' . esc_html($detail_student_id) . ')';
                                    $detail_results = $wpdb->get_results($wpdb->prepare(
                                        "SELECT er.marks, es.subject_name 
                                         FROM {$wpdb->prefix}exam_results er 
                                         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
                                         WHERE er.exam_id = %d AND er.student_id = %s AND er.education_center_id = %s",
                                        $detail_exam_id, $detail_student_id, $education_center_id
                                    ), ARRAY_A);
                                    ?>
                                    <div class="card mt-4 border-primary">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="card-title mb-0">Detailed Report: <?php echo esc_html($student_name); ?> (<?php echo esc_html($exam->name); ?>)</h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group">
                                                <?php
                                                foreach ($detail_results as $result) {
                                                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                                    echo esc_html($result['subject_name']) . '<span class="badge bg-primary rounded-pill">' . $result['marks'] . '</span>';
                                                    echo '</li>';
                                                }
                                                ?>
                                            </ul>
                                            <a href="?section=generate-reports&exam_id=<?php echo $exam_id; ?>" class="btn btn-secondary mt-3">Close</a>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <?php
                        break;

                    default:
                        echo '<div class="alert alert-warning">Section not found.</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        $('.generate-report-btn').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var examId = $button.data('exam-id');
            var nonce = $button.data('nonce');

            $button.prop('disabled', true).text('Generating...');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'generate_report_preview',
                    exam_id: examId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=download_report_pdf&exam_id=' + examId + '&nonce=' + nonce;
                    } else {
                        alert('Error: ' + response.data);
                    }
                    $button.prop('disabled', false).text('Generate Report');
                },
                error: function() {
                    alert('An error occurred while generating the report.');
                    $button.prop('disabled', false).text('Generate Report');
                }
            });
        });

        $('.view-details-btn').on('click', function(e) {
            e.preventDefault();
            var $button = $(this);
            var examId = $button.data('exam-id');
            var nonce = $button.data('nonce');
            var $container = $('#report-details-container');

            $button.prop('disabled', true).text('Loading...');
            $container.hide().html('');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'view_report_details',
                    exam_id: examId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        $container.html(response.data).slideDown();
                    } else {
                        $container.html('<div class="alert alert-danger">' + response.data + '</div>').slideDown();
                    }
                    $button.prop('disabled', false).text('View Details');
                },
                error: function() {
                    $container.html('<div class="alert alert-danger">Error loading details.</div>').slideDown();
                    $button.prop('disabled', false).text('View Details');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

function generate_detailed_pdf($exam, $subjects, $student_marks, $logo_path, $institute_name, $filtered_results = null) {
    global $wpdb;

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('chroot', ABSPATH);
    $options->set('tempDir', sys_get_temp_dir());
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf($options);

    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @page { 
                margin: 10mm; 
                border: 4px solid #1a2b5f;
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
                max-width: 100%;
                padding: 15px;
                border: 1px solid #ccc;
                background-color: #fff;
                box-sizing: border-box;
            }
            .header {
                text-align: center;
                padding-bottom: 10px;
                border-bottom: 2px solid #1a2b5f;
                margin-bottom: 10px;
            }
            .header h1 {
                font-size: 18pt;
                color: #1a2b5f;
                margin: 0;
                text-transform: uppercase;
            }
            .header .subtitle {
                font-size: 12pt;
                color: #666;
                margin: 0;
            }
            .header img {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                margin-bottom: 4px;
                object-fit: cover;
            }
            .details-table, .marks-table {
                width: 100%;
                max-width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 11pt;
                table-layout: fixed;
            }
            .details-table td, .marks-table td, .marks-table th {
                padding: 6px;
                word-wrap: break-word;
                overflow-wrap: break-word;
            }
            .details-table td.label {
                width: 25%;
                font-weight: bold;
                background-color: #f5f5f5;
                color: #1a2b5f;
            }
            .marks-table th, .marks-table td {
                width: auto;
                text-align: center;
            }
            .marks-table th {
                background-color: #1a2b5f;
                color: white;
                font-weight: bold;
            }
            .marks-table tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .marks-table .total-row td {
                font-weight: bold;
                background-color: #e6f0fa;
            }
            .footer {
                text-align: center;
                font-size: 9pt;
                color: #666;
                margin-top: 20px;
            }
            .report-details {
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ddd;
                background-color: #f9f9f9;
            }
            .report-details .detail-item {
                margin-bottom: 10px;
                display: flex;
            }
            .report-details .label {
                font-weight: bold;
                color: #1a2b5f;
                width: 150px;
                flex-shrink: 0;
            }
            .page-break {
                page-break-before: always;
            }
            @media print {
                .container {
                    width: 100%;
                    max-width: 100%;
                }
                .details-table, .marks-table {
                    width: 100%;
                    max-width: 100%;
                }
            }
        </style>
    </head>
    <body>';

    $html .= '
    <div class="container">
        <div class="header">
            ' . ($logo_path && file_exists(str_replace('file://', '', $logo_path)) ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
            <h1>' . esc_html($institute_name) . '</h1>
            <div class="subtitle">Exam Performance Report</div>
        </div>
        <div class="report-details">
            <div class="detail-item">
                <span class="label">Exam Name:</span>
                <span>' . esc_html($exam->name) . '</span>
            </div>
            <div class="detail-item">
                <span class="label">Exam Date:</span>
                <span>' . esc_html($exam->exam_date ?: 'N/A') . '</span>
            </div>
            <div class="detail-item">
                <span class="label">Class/Course:</span>
                <span>' . esc_html($exam->class_id ?: 'N/A') . '</span>
            </div>
            <div class="detail-item">
                <span class="label">Total Subjects:</span>
                <span>' . count($subjects) . '</span>
            </div>
            <div class="detail-item">
                <span class="label">Total Students:</span>
                <span>' . count($student_marks) . '</span>
            </div>
        </div>
    </div>';

    $students_to_process = $filtered_results ?: array_keys($student_marks);
    if (empty($students_to_process)) {
        $html .= '<div class="container"><p>No students available to report.</p></div>';
    } else {
        foreach ($students_to_process as $student_id) {
            if (!is_scalar($student_id)) {
                error_log('Invalid student_id: ' . print_r($student_id, true));
                continue; // Skip invalid keys
            }

            $html .= '<div class="page-break"></div>';

            $student = get_posts([
                'post_type' => 'student',
                'meta_query' => array(
                    array(
                        'key' => 'student_id',
                        'value' => $student_id,
                        'compare' => '='
                    )
                ),
                'posts_per_page' => 1,
                'post_status' => 'publish'
            ]);

            if (empty($student)) {
                $student = get_posts([
                    'post_type' => 'student',
                    'name' => $student_id,
                    'posts_per_page' => 1,
                    'post_status' => 'publish'
                ]);
            }

            if (empty($student)) {
                $student = $wpdb->get_row($wpdb->prepare(
                    "SELECT p.* 
                     FROM {$wpdb->prefix}posts p 
                     JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
                     WHERE pm.meta_key = 'student_id' 
                     AND pm.meta_value = %s 
                     AND p.post_status = 'publish'",
                    $student_id
                ));
            }

            if (empty($student)) {
                $student = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}posts 
                     WHERE post_name = %s 
                     OR post_title = %s 
                     AND post_status = 'publish'",
                    $student_id, $student_id
                ));
            }

            $student_post = $student ? (is_array($student) ? $student[0] : $student) : null;
            $student_name = $student_post ? $student_post->post_title : 'Unknown (' . esc_html($student_id) . ')';
            $student_roll = $student_post ? (get_field('roll_number', $student_post->ID) ?: 'N/A') : 'N/A';
            $student_dob = $student_post ? (get_field('date_of_birth', $student_post->ID) ?: 'N/A') : 'N/A';
            $student_gender = $student_post ? (get_field('gender', $student_post->ID) ?: 'N/A') : 'N/A';
            $exam_year = $exam->exam_date ? date('Y', strtotime($exam->exam_date)) : 'N/A';

            $html .= '
            <div class="container">
                <div class="header">
                    ' . ($logo_path && file_exists(str_replace('file://', '', $logo_path)) ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
                    <h1>' . esc_html($institute_name) . '</h1>
                    <div class="subtitle">' . esc_html($exam->name) . '</div>
                </div>
                <table class="details-table">
                    <tr><td class="label">Student Name</td><td>' . esc_html($student_name) . '</td></tr>
                    <tr><td class="label">Student ID</td><td>' . esc_html($student_id) . '</td></tr>
                    <tr><td class="label">Roll Number</td><td>' . esc_html($student_roll) . '</td></tr>
                    <tr><td class="label">Date of Birth</td><td>' . esc_html($student_dob) . '</td></tr>
                    <tr><td class="label">Gender</td><td>' . esc_html($student_gender) . '</td></tr>
                    <tr><td class="label">Examination</td><td>' . esc_html($exam->name) . '</td></tr>
                    <tr><td class="label">Class/Course</td><td>' . esc_html($exam->class_id ?: 'N/A') . '</td></tr>
                    <tr><td class="label">Academic Year</td><td>' . $exam_year . '</td></tr>
                    <tr><td class="label">Exam Date</td><td>' . esc_html($exam->exam_date ?: 'N/A') . '</td></tr>
                </table>
                <table class="marks-table">
                    <tr><th>Subject</th><th>Marks Obtained</th><th>Maximum Marks</th></tr>';

            $total = 0;
            $max_total = 0;
            foreach ($subjects as $subject) {
                $mark = isset($student_marks[$student_id]['marks'][$subject->id]) ? $student_marks[$student_id]['marks'][$subject->id] : '-';
                $total += is_numeric($mark) ? floatval($mark) : 0;
                $max_total += floatval($subject->max_marks);
                $html .= '
                    <tr>
                        <td>' . esc_html($subject->subject_name) . '</td>
                        <td>' . $mark . '</td>
                        <td>' . $subject->max_marks . '</td>
                    </tr>';
            }

            $percentage = $max_total > 0 ? round(($total / $max_total) * 100, 2) : 0;

            $html .= '
                    <tr class="total-row">
                        <td>Total</td>
                        <td>' . $total . '</td>
                        <td>' . $max_total . '</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="2">Percentage</td>
                        <td>' . $percentage . '%</td>
                    </tr>
                </table>
                <div class="footer">
                    <p>This is an Online Generated Report issued by ' . esc_html($institute_name) . '</p>
                    <p>Generated on ' . date('Y-m-d') . '</p>
                    <div class="signature">
                        <p>___________________________</p>
                        <p>Registrar / Authorized Signatory</p>
                    </div>
                    <div class="generated-by">
                        Managed by Instituto Educational Center Management System
                    </div>
                </div>
            </div>';
        }
    }

    $html .= '</body></html>';

    try {
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf_content = $dompdf->output();

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="exam_report_' . $exam->id . '_detailed.pdf"');
        header('Content-Length: ' . strlen($pdf_content));
        header('Cache-Control: no-cache');

        echo $pdf_content;
        flush();
        exit;
    } catch (Exception $e) {
        error_log('Dompdf Error: ' . $e->getMessage());
        wp_die('Error generating PDF: ' . esc_html($e->getMessage()));
    }
}

add_action('wp_ajax_generate_report_preview', 'ajax_generate_report_preview');
function ajax_generate_report_preview() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'generate_report_' . $_POST['exam_id'])) {
        wp_send_json_error('Security check failed.');
    }

    $exam_id = intval($_POST['exam_id']);
    $center_data = get_educational_center_data2();
    if (!$center_data) {
        wp_send_json_error('No educational center found.');
    }

    global $wpdb;
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
        $exam_id, $center_data['id']
    ));
    if (!$exam) {
        wp_send_json_error('Exam not found.');
    }

    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.*, es.subject_name 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         WHERE er.exam_id = %d AND er.education_center_id = %s",
        $exam_id, $center_data['id']
    ), ARRAY_A);

    if (empty($results)) {
        wp_send_json_error('No results available for this exam.');
    }

    wp_send_json_success('Report ready for download.');
}

add_action('wp_ajax_download_report_pdf', 'ajax_download_report_pdf');
function ajax_download_report_pdf() {
    if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'generate_report_' . $_GET['exam_id'])) {
        wp_die('Security check failed.');
    }

    $exam_id = intval($_GET['exam_id']);
    $center_data = get_educational_center_data2();
    if (!$center_data) {
        wp_die('No educational center found.');
    }

    global $wpdb;
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
        $exam_id, $center_data['id']
    ));
    if (!$exam) {
        wp_die('Exam not found.');
    }

    $subjects = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
        $exam_id
    ));
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.*, es.subject_name 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         WHERE er.exam_id = %d AND er.education_center_id = %s",
        $exam_id, $center_data['id']
    ), ARRAY_A);

    if (empty($results)) {
        wp_die('No results available to generate a report.');
    }

    $student_marks = [];
    foreach ($results as $result) {
        $student_marks[$result['student_id']]['marks'][$result['subject_id']] = $result['marks'];
    }

    $logo_path = $center_data['logo_url'] ? (file_exists(str_replace(home_url(), ABSPATH, $center_data['logo_url'])) ? 'file://' . str_replace('\\', '/', realpath(str_replace(home_url(), ABSPATH, $center_data['logo_url']))) : $center_data['logo_url']) : '';

    generate_detailed_pdf($exam, $subjects, $student_marks, $logo_path, $center_data['name']);
}

add_action('wp_ajax_view_report_details', 'ajax_view_report_details');
function ajax_view_report_details() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'view_details_' . $_POST['exam_id'])) {
        wp_send_json_error('Security check failed.');
    }

    $exam_id = intval($_POST['exam_id']);
    $center_data = get_educational_center_data2();
    if (!$center_data) {
        wp_send_json_error('No educational center found.');
    }

    global $wpdb;
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %s",
        $exam_id, $center_data['id']
    ));
    if (!$exam) {
        wp_send_json_error('Exam not found.');
    }

    $detail_results = $wpdb->get_results($wpdb->prepare(
        "SELECT er.student_id, er.marks, es.subject_name 
         FROM {$wpdb->prefix}exam_results er 
         JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
         WHERE er.exam_id = %d AND er.education_center_id = %s",
        $exam_id, $center_data['id']
    ), ARRAY_A);

    if (empty($detail_results)) {
        wp_send_json_error('No detailed results found for this exam.');
    }

    $student_marks = [];
    foreach ($detail_results as $result) {
        $student = $wpdb->get_row($wpdb->prepare(
            "SELECT post_title FROM {$wpdb->prefix}posts p JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
             WHERE pm.meta_key = 'student_id' AND pm.meta_value = %s AND p.post_status = 'publish'",
            $result['student_id']
        ));
        $student_name = $student ? $student->post_title : 'Unknown (' . esc_html($result['student_id']) . ')';
        $student_marks[$student_name][$result['subject_name']] = $result['marks'];
    }

    $html = '<div class="card border-primary"><div class="card-header bg-primary text-white"><h5 class="card-title mb-0">Detailed Report: ' . esc_html($exam->name) . '</h5></div><div class="card-body">';
    foreach ($student_marks as $student_name => $marks) {
        $html .= '<h6>' . esc_html($student_name) . '</h6><ul class="list-group mb-3">';
        foreach ($marks as $subject => $mark) {
            $html .= '<li class="list-group-item d-flex justify-content-between align-items-center">' . esc_html($subject) . '<span class="badge bg-primary rounded-pill">' . $mark . '</span></li>';
        }
        $html .= '</ul>';
    }
    $html .= '<button class="btn btn-secondary mt-3 close-details">Close</button></div></div>';
    $html .= '<script>jQuery(".close-details").on("click", function() { jQuery("#report-details-container").slideUp(); });</script>';

    wp_send_json_success($html);
}

add_shortcode('aspire_reports_dashboard', 'aspire_reports_dashboard_shortcode');