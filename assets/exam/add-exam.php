<?php
if (!defined('ABSPATH')) {
    exit;
}

function add_exams_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    if (isset($_POST['add_exam']) && wp_verify_nonce($_POST['nonce'], 'add_exam_nonce')) {
        $name = sanitize_text_field($_POST['exam_name']);
        $exam_date = sanitize_text_field($_POST['exam_date']);
        $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;

        $wpdb->insert($wpdb->prefix . 'exams', [
            'name' => $name,
            'exam_date' => $exam_date,
            'class_id' => $class_id,
            'education_center_id' => $education_center_id,
        ]);

        echo '<div class="alert alert-success">Exam added successfully!</div>';
    }

    ob_start();
    ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Exam</h3>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="exam_name" class="form-label">Exam Name</label>
                    <input type="text" class="form-control" id="exam_name" name="exam_name" required>
                    <div class="invalid-feedback">Please enter an exam name.</div>
                </div>
                <div class="mb-3">
                    <label for="exam_date" class="form-label">Exam Date</label>
                    <input type="date" class="form-control" id="exam_date" name="exam_date" required>
                    <div class="invalid-feedback">Please select a date.</div>
                </div>
                <div class="mb-3">
                    <label for="class_id" class="form-label">Class ID (Optional)</label>
                    <input type="number" class="form-control" id="class_id" name="class_id">
                </div>
                <?php wp_nonce_field('add_exam_nonce', 'nonce'); ?>
                <button type="submit" name="add_exam" class="btn btn-primary">Add Exam</button>
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
    <?php
    return ob_get_clean();
}
add_shortcode('add_exams_institute_dashboard', 'add_exams_institute_dashboard_shortcode');