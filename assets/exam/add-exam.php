<?php
if (!defined('ABSPATH')) {
    exit;
}

function add_exams_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $classes = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %s", $education_center_id),
        ARRAY_A
    );
    if (isset($_POST['add_exam']) && wp_verify_nonce($_POST['nonce'], 'add_exam_nonce')) {
        $name = sanitize_text_field($_POST['exam_name']);
        $exam_date = sanitize_text_field($_POST['exam_date']);
        $class_name = isset($_POST['class_name']) ? sanitize_text_field($_POST['class_name']) : null;

        $wpdb->insert($wpdb->prefix . 'exams', [
            'name' => $name,
            'exam_date' => $exam_date,
            'class_id' => $class_name,
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
    <label for="class_id" class="form-label">Class</label>
    <select name="class_name" id="class_id" class="form-select" required>
        <option value="">-- Select Class --</option>
        <?php foreach ($classes as $class): ?>
            <option value="<?php echo esc_attr($class['class_name']); ?>">
                <?php echo esc_html($class['class_name']); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="invalid-feedback">Please select a class.</div>
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