<?php
if (!defined('ABSPATH')) {
    exit;
}

function delete_exams_institute_dashboard_shortcode() {
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
        <div class="card-header">
            <h3 class="card-title">Delete Exams</h3>
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
                                <a href="?section=delete-exam&action=delete&exam_id=<?php echo $exam->id; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure? This will delete all related subjects and results.')">Delete</a>
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
add_shortcode('delete_exams_institute_dashboard', 'delete_exams_institute_dashboard_shortcode');