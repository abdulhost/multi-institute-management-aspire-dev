<?php
if (!defined('ABSPATH')) {
    exit;
}

function delete_transport_enrollments_shortcode() {
    global $wpdb;
    $enrollments_table = $wpdb->prefix . 'transport_enrollments';
    $student_id = isset($_POST['student_id']) ? sanitize_text_field($_POST['student_id']) : '';
    $message = '';
    $enrollments = [];

    if (isset($_POST['delete_enrollments']) && check_admin_referer('delete_enrollments_nonce')) {
        $enrollment_ids = $_POST['enrollment_ids'] ?? [];
        if (!empty($enrollment_ids)) {
            $enrollment_ids = array_map('intval', $enrollment_ids);
            $ids_placeholder = implode(',', array_fill(0, count($enrollment_ids), '%d'));
            $deleted = $wpdb->query($wpdb->prepare("DELETE FROM $enrollments_table WHERE id IN ($ids_placeholder)", ...$enrollment_ids));
            $message = $deleted !== false ? '<div class="alert alert-success">Enrollments deleted successfully!</div>' : '<div class="alert alert-danger">Error deleting enrollments.</div>';
        } else {
            $message = '<div class="alert alert-warning">No enrollments selected.</div>';
        }
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
        <h2 class="mb-4">Delete Transport Enrollments</h2>
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
            <form method="post" onsubmit="return confirm('Are you sure you want to delete the selected enrollments?');">
                <?php wp_nonce_field('delete_enrollments_nonce'); ?>
                <input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
                <table class="table table-striped table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th><input type="checkbox" id="select-all"></th>
                            <th>Enrollment Date</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <tr>
                                <td><input type="checkbox" name="enrollment_ids[]" value="<?php echo esc_attr($enrollment->id); ?>" class="enrollment-checkbox"></td>
                                <td><?php echo esc_html($enrollment->enrollment_date); ?></td>
                                <td><?php echo $enrollment->active ? 'Yes' : 'No'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="delete_enrollments" class="btn btn-danger">Delete Selected</button>
            </form>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const selectAll = document.getElementById('select-all');
                    const checkboxes = document.querySelectorAll('.enrollment-checkbox');
                    selectAll.addEventListener('change', function() {
                        checkboxes.forEach(cb => {
                            cb.checked = this.checked;
                            cb.closest('tr').classList.toggle('table-danger', this.checked);
                        });
                    });
                    checkboxes.forEach(cb => {
                        cb.addEventListener('change', function() {
                            this.closest('tr').classList.toggle('table-danger', this.checked);
                        });
                    });
                });
            </script>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}