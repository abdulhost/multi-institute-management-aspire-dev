<?php
if (!defined('ABSPATH')) {
    exit;
}

function add_exam_subjects_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();

    if (isset($_POST['add_subjects']) && wp_verify_nonce($_POST['nonce'], 'add_subjects_nonce')) {
        $exam_id = intval($_POST['exam_id']);
        $subjects = array_map('sanitize_text_field', $_POST['subjects']);
        $max_marks = array_map('floatval', $_POST['max_marks']);
        foreach ($subjects as $index => $subject) {
            if (!empty($subject)) {
                $wpdb->insert($wpdb->prefix . 'exam_subjects', [
                    'exam_id' => $exam_id,
                    'subject_name' => $subject,
                    'max_marks' => $max_marks[$index],
                    'education_center_id' => $education_center_id,
                ]);
            }
        }
        echo '<div class="alert alert-success">Subjects added successfully!</div>';
    }

    $exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %d",
        $exam_id, $education_center_id
    ));

    ob_start();
    ?>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add Subjects to Exam</h3>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <label for="exam_id" class="input-group-text">Select Exam</label>
                    <select name="exam_id" id="exam_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Exam --</option>
                        <?php
                        $exams = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %d",
                            $education_center_id
                        ));
                        foreach ($exams as $e) {
                            $selected = ($exam_id == $e->id) ? 'selected' : '';
                            echo "<option value='{$e->id}' $selected>{$e->name}</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="section" value="add-exam-subjects">
                </div>
            </form>

            <?php if ($exam): ?>
                <h4>Subjects for <?php echo esc_html($exam->name); ?></h4>
                
                <?php
                $subjects = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
                    $exam_id
                ));
                if ($subjects): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Subject Name</th>
                                <th>Max Marks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subject): ?>
                                <tr>
                                    <td><?php echo esc_html($subject->subject_name); ?></td>
                                    <td><?php echo esc_html($subject->max_marks); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                
                <form method="POST" id="subjects-form">
                    <table class="table table-bordered" id="subjects-table">
                        <thead>
                            <tr>
                                <th>Subject Name</th>
                                <th>Max Marks</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="subjects[]" class="form-control" required></td>
                                <td><input type="number" name="max_marks[]" class="form-control" value="100" step="0.01" min="0" required></td>
                                <td><button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">Remove</button></td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-secondary mb-3" onclick="addSubjectRow()">Add Subject</button>
                    <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                    <?php wp_nonce_field('add_subjects_nonce', 'nonce'); ?>
                    <button type="submit" name="add_subjects" class="btn btn-primary">Save Subjects</button>
                </form>

            <?php else: ?>
                <div class="alert alert-warning">Please select an exam.</div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function addSubjectRow() {
        const table = document.getElementById('subjects-table').getElementsByTagName('tbody')[0];
        const row = table.insertRow();
        row.innerHTML = `
            <td><input type="text" name="subjects[]" class="form-control" required></td>
            <td><input type="number" name="max_marks[]" class="form-control" value="100" step="0.01" min="0" required></td>
            <td><button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">Remove</button></td>
        `;
    }
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('add_exam_subjects_institute_dashboard', 'add_exam_subjects_institute_dashboard_shortcode');