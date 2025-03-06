<?php
if (!defined('ABSPATH')) {
    exit;
}

function edit_exams_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    if (isset($_POST['edit_exam']) && wp_verify_nonce($_POST['nonce'], 'edit_exam_nonce') && isset($_GET['exam_id'])) {
        $exam_id = intval($_GET['exam_id']);
        $name = sanitize_text_field($_POST['exam_name']);
        $exam_date = sanitize_text_field($_POST['exam_date']);
        $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;

        $wpdb->update($wpdb->prefix . 'exams', [
            'name' => $name,
            'exam_date' => $exam_date,
            'class_id' => $class_id,
        ], ['id' => $exam_id, 'education_center_id' => $education_center_id]);

        echo '<div class="alert alert-success">Exam updated successfully!</div>';
    }

    $exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %d",
        $exam_id, $education_center_id
    ));

    ob_start();
    if ($exam):
    ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Exam: <?php echo esc_html($exam->name); ?></h3>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="exam_name" class="form-label">Exam Name</label>
                    <input type="text" class="form-control" id="exam_name" name="exam_name" value="<?php echo esc_attr($exam->name); ?>" required>
                    <div class="invalid-feedback">Please enter an exam name.</div>
                </div>
                <div class="mb-3">
                    <label for="exam_date" class="form-label">Exam Date</label>
                    <input type="date" class="form-control" id="exam_date" name="exam_date" value="<?php echo esc_attr($exam->exam_date); ?>" required>
                    <div class="invalid-feedback">Please select a date.</div>
                </div>
                <div class="mb-3">
                    <label for="class_id" class="form-label">Class ID (Optional)</label>
                    <input type="number" class="form-control" id="class_id" name="class_id" value="<?php echo esc_attr($exam->class_id); ?>">
                </div>
                <?php wp_nonce_field('edit_exam_nonce', 'nonce'); ?>
                <button type="submit" name="edit_exam" class="btn btn-primary">Update Exam</button>
            </form>
        </div>
    </div>
    <script>
    (function () {
        'use strict';
        var forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
    </script>
    <?php else: ?>
    <!-- <div class="alert alert-danger">Exam not found.</div> -->
    <?php    global $wpdb;
    $education_center_id = get_educational_center_data();
    $exams = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %d",
        $education_center_id
    ));
    ?>
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
                
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif;
    return ob_get_clean();
}
add_shortcode('edit_exams_institute_dashboard', 'edit_exams_institute_dashboard_shortcode');