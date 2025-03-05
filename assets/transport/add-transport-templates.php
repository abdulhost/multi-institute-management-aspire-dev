<?php
if (!defined('ABSPATH')) {
    exit;
}

function add_transport_enrollments_shortcode() {
    global $wpdb;
    $enrollments_table = $wpdb->prefix . 'transport_enrollments';
    $education_center_id = get_educational_center_data();
    $message = '';

    if (isset($_POST['add_enrollment']) && check_admin_referer('add_enrollment_nonce')) {
        $data = [
            'student_id' => sanitize_text_field($_POST['student_id']),
            'education_center_id' => $education_center_id,
            'enrollment_date' => sanitize_text_field($_POST['enrollment_date']),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];

        $inserted = $wpdb->insert($enrollments_table, $data);
        $message = $inserted ? '<div class="alert alert-success">Enrollment added successfully!</div>' : '<div class="alert alert-danger">Error adding enrollment.</div>';
    }

    ob_start();
    ?>
    <div class="container">
        <h2 class="mb-4">Add Transport Enrollment</h2>
        <?php echo $message; ?>
        <form method="post" class="row g-3">
            <?php wp_nonce_field('add_enrollment_nonce'); ?>
            <div class="col-md-6">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" name="student_id" id="student_id" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="enrollment_date" class="form-label">Enrollment Date</label>
                <input type="date" name="enrollment_date" id="enrollment_date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <div class="form-check">
                    <input type="checkbox" name="active" id="active" class="form-check-input" checked>
                    <label for="active" class="form-check-label">Active</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" name="add_enrollment" class="btn btn-primary">Add Enrollment</button>
            </div>
        </form>
    </div>
    <?php
    return ob_get_clean();
}