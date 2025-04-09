<?php
if (!defined('ABSPATH')) {
    exit;
}

function aspire_multiple_exams_dashboard_shortcode() {
    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'exams';

    if ($section === 'delete-exam' && isset($_GET['action']) && $_GET['action'] === 'delete') {
        my_education_erp_delete_exam();
    }

    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">

            <?php 
            echo render_admin_header(wp_get_current_user());
            if (!is_center_subscribed($education_center_id)) {
                return render_subscription_expired_message($education_center_id);
            }
                    $active_section = str_replace('-', '-', $section); // e.g., "add-fee" -> "add_fee"

            include plugin_dir_path(__FILE__) . '../sidebar.php'; ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'exams':
                        echo exams_institute_dashboard_shortcode();
                        break;
                    case 'add-exam':
                        echo add_exams_institute_dashboard_shortcode();
                        break;
                    case 'edit-exam':
                        echo edit_exams_institute_dashboard_shortcode();
                        break;
                    case 'delete-exam':
                        echo delete_exams_institute_dashboard_shortcode();
                        break;
                    case 'add-exam-subjects':
                        echo add_exam_subjects_institute_dashboard_shortcode();
                        break;
                    case 'results':
                        echo results_institute_dashboard_shortcode();
                        break;
                    default:
                        echo '<div class="alert alert-warning">Section not found. Please select a valid option.</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_multiple_exams_dashboard', 'aspire_multiple_exams_dashboard_shortcode');


function exams_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    
    }
    $exams = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %d",
        $education_center_id
    ));

    ob_start();
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Exams List</h3>
            <a href="/institute-dashboard/exam/?section=add-exam" class="btn btn-primary">Add Exam</a>
        </div>
        <div class="card-body">
            <div class="input-group mb-3">
                <input type="text" id="exam-search" class="form-control" placeholder="Search exams...">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Class ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="exams-table">
                    <?php foreach ($exams as $exam): ?>
                        <tr>
                            <td><?php echo esc_html($exam->name); ?></td>
                            <td><?php echo esc_html($exam->exam_date); ?></td>
                            <td><?php echo esc_html($exam->class_id ?: 'N/A'); ?></td>
                            <td>
                                <a href="?section=edit-exam&exam_id=<?php echo $exam->id; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="?section=delete-exam&action=delete&exam_id=<?php echo $exam->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                <a href="?section=add-exam-subjects&exam_id=<?php echo $exam->id; ?>" class="btn btn-sm btn-info">Subjects</a>
                                <a href="?section=results&exam_id=<?php echo $exam->id; ?>" class="btn btn-sm btn-success">Results</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('exams_institute_dashboard', 'exams_institute_dashboard_shortcode');