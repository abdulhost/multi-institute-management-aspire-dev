<?php
if (!defined('ABSPATH')) {
    exit;
}

function view_transport_enrollments_shortcode() {
    global $wpdb;
    $enrollments_table = $wpdb->prefix . 'transport_enrollments';
    $student_id = isset($_GET['student_id']) ? sanitize_text_field($_GET['student_id']) : '';
    $enrollments = [];

    if ($student_id) {
        $enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $enrollments_table WHERE student_id = %s ORDER BY enrollment_date DESC",
            $student_id
        ));
    }

    ob_start();
    ?>
    <div class="container">
        <h2 class="mb-4">View Transport Enrollments</h2>
        <form method="get" class="mb-4">
            <input type="hidden" name="section" value="view-transport-enrollments">
            <div class="row">
                <div class="col-md-6">
                    <input type="text" name="student_id" class="form-control" placeholder="Enter Student ID" value="<?php echo esc_attr($student_id); ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>

        <?php if ($student_id && empty($enrollments)): ?>
            <div class="alert alert-info">No transport enrollments found for this student.</div>
        <?php elseif (!empty($enrollments)): ?>
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>Enrollment Date</th>
                        <th>Active</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($enrollments as $enrollment): ?>
                        <tr>
                            <td><?php echo esc_html($enrollment->enrollment_date); ?></td>
                            <td><?php echo $enrollment->active ? 'Yes' : 'No'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}