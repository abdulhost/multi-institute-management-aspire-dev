<?php
if (!defined('ABSPATH')) {
    exit;
}

function edit_transport_enrollments_shortcode() {
    global $wpdb;
    $enrollments_table = $wpdb->prefix . 'transport_enrollments';
    $student_id = isset($_POST['student_id']) ? sanitize_text_field($_POST['student_id']) : '';
    $message = '';
    $enrollments = [];

    if (isset($_POST['update_enrollments']) && check_admin_referer('update_enrollments_nonce')) {
        $enrollment_ids = $_POST['enrollment_id'] ?? [];
        foreach ($enrollment_ids as $index => $id) {
            $data = [
                'enrollment_date' => sanitize_text_field($_POST['enrollment_date'][$index]),
                'active' => isset($_POST['active'][$index]) ? 1 : 0,
            ];
            $wpdb->update($enrollments_table, $data, ['id' => intval($id)]);
        }
        $message = '<div class="alert alert-success">Enrollments updated successfully!</div>';
    }

    if ($student_id) {
        $enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $enrollments_table WHERE student_id = %s ORDER BY enrollment_date DESC",
            $student_id
        ));
    }

    ob_start();
    ?>
    <div class="container">
        <h2 class="mb-4">Edit Transport Enrollments</h2>
        <?php echo $message; ?>
        <form method="post" class="mb-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text" name="student_id" class="form-control" placeholder="Enter Student ID" value="<?php echo esc_attr($student_id); ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Search</button>
                </div>
            </div>
        </form>

        <?php if ($student_id && !empty($enrollments)): ?>
            <form method="post">
                <?php wp_nonce_field('update_enrollments_nonce'); ?>
                <input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Enrollment Date</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $index => $enrollment): ?>
                            <tr>
                                <td>
                                    <input type="date" name="enrollment_date[<?php echo $index; ?>]" class="form-control" value="<?php echo esc_attr($enrollment->enrollment_date); ?>" required>
                                    <input type="hidden" name="enrollment_id[<?php echo $index; ?>]" value="<?php echo esc_attr($enrollment->id); ?>">
                                </td>
                                <td>
                                    <div class="form-check">
                                        <input type="checkbox" name="active[<?php echo $index; ?>]" class="form-check-input" <?php checked($enrollment->active, 1); ?>>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="update_enrollments" class="btn btn-primary">Update Enrollments</button>
            </form>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}