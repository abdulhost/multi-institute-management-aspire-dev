<?php

function render_teacher_exams($user_id, $teacher) {
    global $wpdb;
    $education_center_id = educational_center_teacher_id();
    $exams = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %d",
        $education_center_id
    ));

    ob_start();
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Exams List</h3>
            <a href="?section=add-exam" class="btn btn-primary">Add Exam</a>
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
                        <th>Class</th>
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
                                <a href="?section=exams&action=edit-exam&exam_id=<?php echo $exam->id; ?>" class="btn btn-sm btn-warning">Edit</a>
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

function render_teacher_add_exam($user_id, $teacher) {
    global $wpdb;
    $education_center_id = educational_center_teacher_id();
    $classes = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %d",
        $education_center_id
    ), ARRAY_A);

    if (isset($_POST['add_exam']) && wp_verify_nonce($_POST['nonce'], 'add_exam_nonce')) {
        $name = sanitize_text_field($_POST['exam_name']);
        $exam_date = sanitize_text_field($_POST['exam_date']);
        $class_name = sanitize_text_field($_POST['class_name']);

        $wpdb->insert($wpdb->prefix . 'exams', [
            'name' => $name,
            'exam_date' => $exam_date,
            'class_id' => $class_name,
            'education_center_id' => $education_center_id,
            'teacher_id' => $user_id
        ]);

        ob_start();
        echo '<div class="alert alert-success">Exam added successfully! <a href="?section=exams">Back to Exams</a></div>';
        return ob_get_clean();
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
                <div class="d-flex gap-2">
                    <button type="submit" name="add_exam" class="btn btn-primary">Add Exam</button>
                    <a href="?section=exams" class="btn btn-secondary">Cancel</a>
                </div>
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

function render_teacher_edit_exam($user_id, $teacher) {
    global $wpdb;
    $education_center_id = educational_center_teacher_id();
    $exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

    // Fetch the exam
    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %d",
        $exam_id, $education_center_id
    ));

    // Fetch classes
    $classes = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}class_sections WHERE education_center_id = %d",
        $education_center_id
    ), ARRAY_A);

    // Handle form submission
    if (isset($_POST['edit_exam']) && wp_verify_nonce($_POST['nonce'], 'edit_exam_nonce')) {
        $name = sanitize_text_field($_POST['exam_name']);
        $exam_date = sanitize_text_field($_POST['exam_date']);
        $class_name = sanitize_text_field($_POST['class_name']);

        $wpdb->update(
            $wpdb->prefix . 'exams',
            ['name' => $name, 'exam_date' => $exam_date, 'class_id' => $class_name],
            ['id' => $exam_id, 'education_center_id' => $education_center_id]
        );

        ob_start();
        echo '<div class="alert alert-success">Exam updated successfully! <a href="?section=exams">Back to Exams</a></div>';
        return ob_get_clean();
    }

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
                    <label for="class_id" class="form-label">Class</label>
                    <select name="class_name" id="class_id" class="form-select" required>
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $class): ?>
                            <option value="<?php echo esc_attr($class['class_name']); ?>" 
                                <?php echo ($exam->class_id === $class['class_name']) ? 'selected' : ''; ?>>
                                <?php echo esc_html($class['class_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Please select a class.</div>
                </div>
                <?php wp_nonce_field('edit_exam_nonce', 'nonce'); ?>
                <div class="d-flex gap-2">
                    <button type="submit" name="edit_exam" class="btn btn-primary">Update Exam</button>
                    <a href="?section=exams" class="btn btn-secondary">Cancel</a>
                </div>
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
        <?php
        global $wpdb;
        $education_center_id = educational_center_teacher_id();    
           $exams = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %d",
            $education_center_id
        ));
    
        ?> 
        <!-- <div class="alert alert-danger">Exam not found or you don’t have permission to edit it. <a href="?section=exams">Back to Exams</a></div> -->
        <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Exams List</h3>
            <a href="?section=add-exam" class="btn btn-primary">Add Exam</a>
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
                        <th>Class</th>
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
                                <a href="?section=exams&action=edit-exam&exam_id=<?php echo $exam->id; ?>" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
   <?php endif;
    return ob_get_clean();
}

function render_teacher_delete_exam($user_id, $teacher) {
    global $wpdb;
    $education_center_id = educational_center_teacher_id();

    // Fetch exams for display
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
            <div id="delete-message" class="mb-3"></div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="exams-table">
                    <?php if (empty($exams)): ?>
                        <tr>
                            <td colspan="4">No exams found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($exams as $exam): ?>
                            <tr id="exam-row-<?php echo $exam->id; ?>">
                                <td><?php echo esc_html($exam->name); ?></td>
                                <td><?php echo esc_html($exam->exam_date); ?></td>
                                <td><?php echo esc_html($exam->class_id ?: 'N/A'); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-exam-btn" 
                                            data-exam-id="<?php echo $exam->id; ?>" 
                                            onclick="deleteExam(<?php echo $exam->id; ?>)">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
    function deleteExam(examId) {
        if (!confirm('Are you sure? This will Delete Exam')) {
            return;
        }

        const data = {
            action: 'delete_teacher_exam',
            exam_id: examId,
            nonce: '<?php echo wp_create_nonce('delete_exam_nonce'); ?>'
        };

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(data)
        })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById('delete-message');
            if (data.success) {
                messageDiv.innerHTML = '<div class="alert alert-success">Exam deleted successfully!</div>';
                document.getElementById('exam-row-' + examId).remove();
            } else {
                messageDiv.innerHTML = '<div class="alert alert-danger">' + (data.data || 'Failed to delete exam.') + '</div>';
            }
            setTimeout(() => messageDiv.innerHTML = '', 5000); // Clear message after 5 seconds
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('delete-message').innerHTML = '<div class="alert alert-danger">An error occurred.</div>';
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

// AJAX handler for deleting exams
add_action('wp_ajax_delete_teacher_exam', 'delete_teacher_exam_callback');
function delete_teacher_exam_callback() {
    global $wpdb;

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'delete_exam_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $exam_id = isset($_POST['exam_id']) ? intval($_POST['exam_id']) : 0;
    $education_center_id = educational_center_teacher_id();
    $user_id = get_current_user_id(); // Assuming this is the teacher ID

    if ($exam_id <= 0) {
        wp_send_json_error('Invalid exam ID');
    }

    // Delete the exam
    $deleted = $wpdb->delete($wpdb->prefix . 'exams', [
        'id' => $exam_id,
        'education_center_id' => $education_center_id
        // Uncomment and add teacher_id if you want to restrict deletion to teacher's own exams
        // 'teacher_id' => $user_id
    ]);

    if ($deleted) {
        $wpdb->delete($wpdb->prefix . 'exam_subjects', ['exam_id' => $exam_id]);
        // Uncomment the line below if you want to delete exam results as well
        // $wpdb->delete($wpdb->prefix . 'exam_results', ['exam_id' => $exam_id]);
        wp_send_json_success('Exam deleted successfully');
    } else {
        wp_send_json_error('Failed to delete exam or exam not found');
    }
}

function render_teacher_add_exam_subjects($user_id, $teacher) {
    global $wpdb;
    $education_center_id = educational_center_teacher_id();

    ob_start();
    ?>
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title mb-0">Manage Exam Subjects</h3>
        </div>
        <div class="card-body">
            <div class="input-group mb-4">
                <label for="teacher_exam_id" class="input-group-text">Select Exam</label>
                <select name="exam_id" id="teacher_exam_id" class="form-select">
                    <option value="">-- Select Exam --</option>
                    <?php
                    $exams = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %d",
                        $education_center_id
                    ));
                    foreach ($exams as $e) {
                        echo "<option value='{$e->id}'>" . esc_html($e->name) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div id="teacher_exam_content" class="mt-3 position-relative"></div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const examSelect = document.getElementById('teacher_exam_id');
        const contentDiv = document.getElementById('teacher_exam_content');

        examSelect.addEventListener('change', function() {
            const examId = this.value;
            contentDiv.innerHTML = examId ? '<div class="loading-overlay"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>' : '<div class="alert alert-warning">Please select an exam.</div>';

            if (!examId) return;

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'load_teacher_exam_subjects',
                    exam_id: examId,
                    nonce: '<?php echo wp_create_nonce('load_teacher_exam_subjects_nonce'); ?>'
                })
            })
            .then(response => response.text())
            .then(data => { contentDiv.innerHTML = data; })
            .catch(error => {
                console.error('Error:', error);
                contentDiv.innerHTML = '<div class="alert alert-danger">Failed to load exam data.</div>';
            });
        });

        const urlParams = new URLSearchParams(window.location.search);
        const initialExamId = urlParams.get('exam_id');
        if (initialExamId) {
            examSelect.value = initialExamId;
            examSelect.dispatchEvent(new Event('change'));
        }
    });

    function addTeacherSubjectRow() {
        const tbody = document.getElementById('teacher_subjects_table').getElementsByTagName('tbody')[0];
        const row = tbody.insertRow();
        row.innerHTML = `
            <td><input type="text" name="subjects[]" class="form-control" required></td>
            <td><input type="number" name="max_marks[]" class="form-control" value="100" step="0.01" min="0" required></td>
            <td><button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">Remove</button></td>
        `;
    }

    function submitTeacherSubjects(event) {
        event.preventDefault();
        const form = document.getElementById('teacher_subjects_form');
        const formData = new FormData(form);
        formData.append('action', 'save_teacher_exam_subjects');
        formData.append('nonce', '<?php echo wp_create_nonce('save_teacher_exam_subjects_nonce'); ?>');

        const messageDiv = document.getElementById('teacher_subject_message');
        messageDiv.innerHTML = '<div class="alert alert-info">Saving...</div>';

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            messageDiv.innerHTML = data.success 
                ? `<div class="alert alert-success">${data.data} <a href="?section=exams" class="alert-link">Back to Exams</a></div>`
                : `<div class="alert alert-danger">${data.data || 'Failed to save subjects.'}</div>`;
            if (data.success) {
                document.getElementById('teacher_exam_id').dispatchEvent(new Event('change')); // Refresh content
            }
        })
        .catch(error => {
            console.error('Error:', error);
            messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred.</div>';
        });
    }

    function startEditing(element, subjectId, field, initialValue) {
        if (element.classList.contains('editing')) return;

        element.classList.add('editing');
        const isMarks = field === 'max_marks';
        const input = document.createElement('input');
        input.type = isMarks ? 'number' : 'text';
        input.className = 'inline-input';
        input.value = initialValue;
        if (isMarks) {
            input.step = '0.01';
            input.min = '0';
        }

        element.innerHTML = '';
        element.appendChild(input);
        input.focus();

        function saveEdit() {
            const newValue = isMarks ? parseFloat(input.value) : input.value.trim();
            if (newValue === initialValue || (isMarks && isNaN(newValue))) {
                element.innerHTML = initialValue;
                element.classList.remove('editing');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update_teacher_exam_subject');
            formData.append('subject_id', subjectId);
            formData.append('field', field);
            formData.append('value', newValue);
            formData.append('nonce', '<?php echo wp_create_nonce('update_teacher_exam_subject_nonce'); ?>');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    element.innerHTML = newValue;
                    document.getElementById('teacher_exam_id').dispatchEvent(new Event('change')); // Refresh if needed
                } else {
                    element.innerHTML = initialValue;
                    alert(data.data || 'Failed to update.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                element.innerHTML = initialValue;
                alert('An error occurred.');
            })
            .finally(() => element.classList.remove('editing'));
        }

        input.addEventListener('blur', saveEdit);
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                input.blur(); // Trigger save on Enter
            }
        });
    }

    function deleteSubject(subjectId) {
        if (!confirm('Are you sure you want to delete this subject?')) return;

        const formData = new FormData();
        formData.append('action', 'delete_teacher_exam_subject');
        formData.append('subject_id', subjectId);
        formData.append('nonce', '<?php echo wp_create_nonce('delete_teacher_exam_subject_nonce'); ?>');

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) document.getElementById('teacher_exam_id').dispatchEvent(new Event('change'));
            else alert(data.data || 'Failed to delete.');
        })
        .catch(error => console.error('Error:', error));
    }
    </script>
    <?php
    return ob_get_clean();
}

// AJAX handler to load exam subjects
add_action('wp_ajax_load_teacher_exam_subjects', 'load_teacher_exam_subjects_callback');
function load_teacher_exam_subjects_callback() {
    global $wpdb;
    if (!wp_verify_nonce($_POST['nonce'], 'load_teacher_exam_subjects_nonce')) {
        wp_die('Invalid nonce');
    }

    $exam_id = intval($_POST['exam_id']);
    $education_center_id = educational_center_teacher_id();

    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %d",
        $exam_id, $education_center_id
    ));

    if (!$exam) {
        echo '<div class="alert alert-danger">Exam not found or you don’t have permission.</div>';
        wp_die();
    }

    ob_start();
    ?>
    <h4 class="mb-3"><?php echo esc_html($exam->name); ?> - Subjects</h4>
    <?php
    $subjects = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d AND education_center_id = %d",
        $exam_id, $education_center_id
    ));
    if ($subjects): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Subject Name</th>
                        <th>Max Marks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subjects as $subject): ?>
                        <tr>
                            <td class="editable" onclick="startEditing(this, <?php echo $subject->id; ?>, 'subject_name', '<?php echo esc_js($subject->subject_name); ?>')"><?php echo esc_html($subject->subject_name); ?></td>
                            <td class="editable" onclick="startEditing(this, <?php echo $subject->id; ?>, 'max_marks', '<?php echo esc_js($subject->max_marks); ?>')"><?php echo esc_html($subject->max_marks); ?></td>
                            <td><button class="btn btn-danger btn-sm" onclick="deleteSubject(<?php echo $subject->id; ?>)">Delete</button></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <div id="teacher_subject_message" class="mt-3"></div>
    <form method="POST" id="teacher_subjects_form" onsubmit="submitTeacherSubjects(event)" class="mt-4">
        <div class="table-responsive">
            <table class="table table-bordered" id="teacher_subjects_table">
                <thead class="table-light">
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
                        <td><button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.parentElement.remove()">Remove</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between mt-3">
            <button type="button" class="btn btn-outline-secondary" onclick="addTeacherSubjectRow()">Add Subject</button>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="?section=exams" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
    </form>
    <?php
    echo ob_get_clean();
    wp_die();
}

// AJAX handler to save/update exam subjects
add_action('wp_ajax_save_teacher_exam_subjects', 'save_teacher_exam_subjects_callback');
function save_teacher_exam_subjects_callback() {
    global $wpdb;

    if (!wp_verify_nonce($_POST['nonce'], 'save_teacher_exam_subjects_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $exam_id = intval($_POST['exam_id']);
    $subjects = array_map('sanitize_text_field', $_POST['subjects']);
    $max_marks = array_map('floatval', $_POST['max_marks']);
    $education_center_id = educational_center_teacher_id();

    $exam = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %d",
        $exam_id, $education_center_id
    ));
    if (!$exam) {
        wp_send_json_error('Exam not found or you don’t have permission');
    }

    $success = false;
    foreach ($subjects as $index => $subject) {
        if (!empty($subject)) {
            $inserted = $wpdb->insert($wpdb->prefix . 'exam_subjects', [
                'exam_id' => $exam_id,
                'subject_name' => $subject,
                'max_marks' => $max_marks[$index],
                'education_center_id' => $education_center_id,
            ]);
            if ($inserted) $success = true;
        }
    }

    wp_send_json($success ? ['success' => true, 'data' => 'Subjects added successfully!'] : ['success' => false, 'data' => 'Failed to add subjects.']);
}

// AJAX handler to update exam subject
add_action('wp_ajax_update_teacher_exam_subject', 'update_teacher_exam_subject_callback');
function update_teacher_exam_subject_callback() {
    global $wpdb;

    if (!wp_verify_nonce($_POST['nonce'], 'update_teacher_exam_subject_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $subject_id = intval($_POST['subject_id']);
    $field = sanitize_text_field($_POST['field']);
    $value = $field === 'max_marks' ? floatval($_POST['value']) : sanitize_text_field($_POST['value']);
    $education_center_id = educational_center_teacher_id();

    $subject = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE id = %d AND education_center_id = %d",
        $subject_id, $education_center_id
    ));
    if (!$subject) {
        wp_send_json_error('Subject not found or you don’t have permission');
    }

    $updated = $wpdb->update(
        $wpdb->prefix . 'exam_subjects',
        [$field => $value],
        ['id' => $subject_id],
        [$field === 'max_marks' ? '%f' : '%s'],
        ['%d']
    );

    wp_send_json($updated !== false ? ['success' => true, 'data' => 'Subject updated successfully!'] : ['success' => false, 'data' => 'Failed to update subject.']);
}

// AJAX handler to delete exam subject
add_action('wp_ajax_delete_teacher_exam_subject', 'delete_teacher_exam_subject_callback');
function delete_teacher_exam_subject_callback() {
    global $wpdb;

    if (!wp_verify_nonce($_POST['nonce'], 'delete_teacher_exam_subject_nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $subject_id = intval($_POST['subject_id']);
    $education_center_id = educational_center_teacher_id();

    $subject = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE id = %d AND education_center_id = %d",
        $subject_id, $education_center_id
    ));
    if (!$subject) {
        wp_send_json_error('Subject not found or you don’t have permission');
    }

    $deleted = $wpdb->delete($wpdb->prefix . 'exam_subjects', ['id' => $subject_id], ['%d']);
    wp_send_json($deleted ? ['success' => true, 'data' => 'Subject deleted successfully!'] : ['success' => false, 'data' => 'Failed to delete subject.']);
}


use Dompdf\Dompdf;
use Dompdf\Options;

function render_teacher_results($user_id, $teacher) {
    global $wpdb;

    $dompdf_path = plugin_dir_path(__FILE__) . 'dompdf/autoload.inc.php';
    if (file_exists($dompdf_path)) {
        require_once $dompdf_path;
    } else {
        wp_die('Dompdf autoload file not found at: ' . $dompdf_path);
    }

    $education_center_id = educational_center_teacher_id();
    $center = get_post($education_center_id);
    $institute_name = $center->post_title;
    $logo = get_post_meta($education_center_id, 'institute_logo', true);
    $logo_url = $logo ? wp_get_attachment_url($logo) : '';

    // Handle grade submission
    if (isset($_POST['submit_grades']) && wp_verify_nonce($_POST['nonce'], 'submit_grades_nonce')) {
        $exam_id = intval($_POST['exam_id']);
        foreach ($_POST['marks'] as $student_id => $subjects) {
            foreach ($subjects as $subject_id => $marks) {
                $marks = floatval($marks);
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}exam_results WHERE exam_id = %d AND subject_id = %d AND student_id = %s",
                    $exam_id, $subject_id, $student_id
                ));
                if ($existing) {
                    $wpdb->update(
                        $wpdb->prefix . 'exam_results',
                        ['marks' => $marks],
                        ['id' => $existing]
                    );
                } else {
                    $wpdb->insert($wpdb->prefix . 'exam_results', [
                        'exam_id' => $exam_id,
                        'subject_id' => $subject_id,
                        'student_id' => $student_id,
                        'marks' => $marks,
                        'education_center_id' => $education_center_id,
                    ]);
                }
            }
        }
        ob_start();
        echo '<div class="alert alert-success">Grades submitted successfully!</div>';
        return ob_get_clean();
    }

    // Generate PDF
    if (isset($_GET['action']) && in_array($_GET['action'], ['generate_pdf', 'generate_student_pdf']) && isset($_GET['exam_id'])) {
        while (ob_get_level()) {
            ob_end_clean();
        }

        $exam_id = intval($_GET['exam_id']);
        $exam = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %d AND teacher_id = %d",
            $exam_id, $education_center_id, $user_id
        ));
        if (!$exam) {
            wp_die('Exam not found or you don’t have permission.');
        }

        $subjects = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
            $exam_id
        ));
        if (empty($subjects)) {
            wp_die('No subjects found.');
        }

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT er.*, es.subject_name 
             FROM {$wpdb->prefix}exam_results er 
             JOIN {$wpdb->prefix}exam_subjects es ON er.subject_id = es.id 
             WHERE er.exam_id = %d AND er.education_center_id = %d",
            $exam_id, $education_center_id
        ), ARRAY_A);
        if (empty($results)) {
            wp_die('No results found.');
        }

        $student_marks = [];
        foreach ($results as $result) {
            $student_marks[$result['student_id']]['name'] = $result['student_id'];
            $student_marks[$result['student_id']]['marks'][$result['subject_id']] = $result['marks'];
        }

        if ($_GET['action'] === 'generate_student_pdf' && isset($_GET['student_id'])) {
            $student_id = sanitize_text_field($_GET['student_id']);
            if (!isset($student_marks[$student_id])) {
                wp_die('Student not found.');
            }
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', ABSPATH);
        $options->set('tempDir', sys_get_temp_dir());
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);

        $logo_path = $logo_url ? str_replace(home_url(), ABSPATH, $logo_url) : '';
        $logo_path = file_exists($logo_path) ? 'file://' . str_replace('\\', '/', realpath($logo_path)) : $logo_url;

        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page { margin: 10mm; border: 4px solid #1a2b5f; padding: 4mm; }
                body { font-family: Helvetica, sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
                .container { width: 100%; padding: 15px; border: 1px solid #ccc; background-color: #fff; box-sizing: border-box; }
                .header { text-align: center; padding-bottom: 10px; border-bottom: 2px solid #1a2b5f; margin-bottom: 10px; }
                .header h1 { font-size: 18pt; color: #1a2b5f; margin: 0; text-transform: uppercase; }
                .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                .header img { width: 60px; height: 60px; border-radius: 50%; margin-bottom: 4px; object-fit: cover; }
                .details-table, .marks-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 11pt; table-layout: fixed; }
                .details-table td, .marks-table td, .marks-table th { padding: 6px; word-wrap: break-word; overflow-wrap: break-word; }
                .details-table td.label { width: 25%; font-weight: bold; background-color: #f5f5f5; color: #1a2b5f; }
                .marks-table th, .marks-table td { text-align: center; }
                .marks-table th { background-color: #1a2b5f; color: white; font-weight: bold; }
                .marks-table tr:nth-child(even) { background-color: #f9f9f9; }
                .marks-table .total-row td { font-weight: bold; background-color: #e6f0fa; }
                .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 20px; }
                .page-break { page-break-before: always; }
            </style>
        </head>
        <body>';

        if ($_GET['action'] === 'generate_student_pdf' && isset($_GET['student_id'])) {
            $student_id = sanitize_text_field($_GET['student_id']);
            $html .= '<div class="container">';
            $html .= generate_teacher_student_marksheet($exam, $subjects, $student_marks[$student_id], $logo_path, $institute_name);
            $html .= '</div>';
            $filename = "marksheet_{$exam_id}_{$student_id}.pdf";
        } else {
            $first = true;
            foreach ($student_marks as $student_id => $data) {
                if (!$first) {
                    $html .= '<div class="page-break"></div>';
                }
                $html .= '<div class="container">';
                $html .= generate_teacher_student_marksheet($exam, $subjects, $data, $logo_path, $institute_name);
                $html .= '</div>';
                $first = false;
            }
            $filename = "marksheet_{$exam_id}_all.pdf";
        }

        $html .= '</body></html>';

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $pdf_content = $dompdf->output();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Length: ' . strlen($pdf_content));
        echo $pdf_content;
        flush();
        exit;
    }

    ob_start();
    ?>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Manage Exam Results</h3>
            <?php
            $exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;
            $exam = $exam_id ? $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}exams WHERE id = %d AND education_center_id = %d AND teacher_id = %d",
                $exam_id, $education_center_id, $user_id
            )) : null;
            if ($exam): ?>
                <a href="?section=results&exam_id=<?php echo $exam_id; ?>&action=generate_pdf" class="btn btn-success">Generate All Mark Sheets</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <label for="exam_id" class="input-group-text">Select Exam</label>
                    <select name="exam_id" id="exam_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Select Exam --</option>
                        <?php
                        $exams = $wpdb->get_results($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}exams WHERE education_center_id = %d AND teacher_id = %d",
                            $education_center_id, $user_id
                        ));
                        foreach ($exams as $e) {
                            $selected = ($exam_id == $e->id) ? 'selected' : '';
                            echo "<option value='{$e->id}' $selected>{$e->name}</option>";
                        }
                        ?>
                    </select>
                    <input type="hidden" name="section" value="results">
                </div>
            </form>

            <?php if ($exam): ?>
                <h4>Grades for <?php echo esc_html($exam->name); ?></h4>
                <?php
                $subjects = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}exam_subjects WHERE exam_id = %d",
                    $exam_id
                ));
                if (empty($subjects)): ?>
                    <div class="alert alert-warning">No subjects defined. <a href="?section=add-exam-subjects&exam_id=<?php echo $exam_id; ?>">Add subjects</a></div>
                <?php else:
                    $students = $exam->class_id ? $wpdb->get_results($wpdb->prepare(
                        "SELECT p.ID, p.post_title 
                         FROM {$wpdb->prefix}posts p 
                         JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
                         WHERE p.post_type = 'student' 
                         AND p.post_status = 'publish' 
                         AND pm.meta_key = 'class_name' 
                         AND pm.meta_value = %s",
                        $exam->class_id
                    )) : [];
                ?>
                    <form method="POST">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <?php foreach ($subjects as $subject): ?>
                                            <th><?php echo esc_html($subject->subject_name); ?> (<?php echo $subject->max_marks; ?>)</th>
                                        <?php endforeach; ?>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if (empty($students)) {
                                        echo '<tr><td colspan="' . (count($subjects) + 2) . '">No students found for this class.</td></tr>';
                                    } else {
                                        $marks_results = $wpdb->get_results($wpdb->prepare(
                                            "SELECT student_id, subject_id, marks 
                                             FROM {$wpdb->prefix}exam_results 
                                             WHERE exam_id = %d AND education_center_id = %d",
                                            $exam_id, $education_center_id
                                        ), ARRAY_A);
                                        $marks_index = [];
                                        foreach ($marks_results as $result) {
                                            $marks_index[$result['student_id']][$result['subject_id']] = $result['marks'];
                                        }

                                        foreach ($students as $student) {
                                            $student_id = "STU-" . md5($student->ID);
                                            echo '<tr>';
                                            echo '<td>' . esc_html($student->post_title) . ' (' . esc_html($student_id) . ')</td>';
                                            foreach ($subjects as $subject) {
                                                $marks = isset($marks_index[$student_id][$subject->id]) ? $marks_index[$student_id][$subject->id] : '';
                                                echo '<td><input type="number" name="marks[' . esc_attr($student_id) . '][' . $subject->id . ']" class="form-control" value="' . esc_attr($marks) . '" step="0.01" min="0" max="' . $subject->max_marks . '"></td>';
                                            }
                                            echo '<td><a href="?section=results&exam_id=' . $exam_id . '&action=generate_student_pdf&student_id=' . urlencode($student_id) . '" class="btn btn-sm btn-info">Download Mark Sheet</a></td>';
                                            echo '</tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                        <?php wp_nonce_field('submit_grades_nonce', 'nonce'); ?>
                        <button type="submit" name="submit_grades" class="btn btn-primary">Submit Grades</button>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="alert alert-info">Please select an exam to manage results.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function generate_teacher_student_marksheet($exam, $subjects, $student_data, $logo_path, $institute_name) {
    global $wpdb;

    $student_id = $student_data['name'];
    $student = $wpdb->get_row($wpdb->prepare(
        "SELECT p.* 
         FROM {$wpdb->prefix}posts p 
         JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id 
         WHERE p.post_type = 'student' 
         AND p.post_status = 'publish' 
         AND pm.meta_key = 'student_id' 
         AND pm.meta_value = %s",
        $student_id
    ));

    $student_name = $student ? $student->post_title : "Unknown ($student_id)";
    $student_roll = get_post_meta($student->ID, 'roll_number', true) ?: 'N/A';
    $student_dob = get_post_meta($student->ID, 'date_of_birth', true) ?: 'N/A';
    $student_gender = get_post_meta($student->ID, 'gender', true) ?: 'N/A';
    $exam_year = $exam->exam_date ? date('Y', strtotime($exam->exam_date)) : 'N/A';

    $html = '
        <div class="header">
            ' . ($logo_path ? '<img src="' . esc_attr($logo_path) . '" alt="Institute Logo">' : '<p>No logo available</p>') . '
            <h1>' . esc_html($institute_name) . '</h1>
            <div class="subtitle">' . esc_html($exam->name) . '</div>
        </div>
        <table class="details-table">
            <tr><td class="label">Student Name</td><td>' . esc_html($student_name) . '</td></tr>
            <tr><td class="label">Student ID</td><td>' . esc_html($student_id) . '</td></tr>
            <tr><td class="label">Roll Number</td><td>' . esc_html($student_roll) . '</td></tr>
            <tr><td class="label">Date of Birth</td><td>' . esc_html($student_dob) . '</td></tr>
            <tr><td class="label">Gender</td><td>' . esc_html($student_gender) . '</td></tr>
            <tr><td class="label">Examination</td><td>' . esc_html($exam->name) . '</td></tr>
            <tr><td class="label">Class</td><td>' . esc_html($exam->class_id ?: 'N/A') . '</td></tr>
            <tr><td class="label">Academic Year</td><td>' . $exam_year . '</td></tr>
            <tr><td class="label">Exam Date</td><td>' . esc_html($exam->exam_date ?: 'N/A') . '</td></tr>
        </table>
        <table class="marks-table">
            <tr><th>Subject</th><th>Marks Obtained</th><th>Maximum Marks</th></tr>';

    $total = 0;
    $max_total = 0;
    foreach ($subjects as $subject) {
        $mark = isset($student_data['marks'][$subject->id]) ? $student_data['marks'][$subject->id] : '-';
        $total += is_numeric($mark) ? floatval($mark) : 0;
        $max_total += floatval($subject->max_marks);
        $html .= '
            <tr>
                <td>' . esc_html($subject->subject_name) . '</td>
                <td>' . $mark . '</td>
                <td>' . $subject->max_marks . '</td>
            </tr>';
    }

    $percentage = $max_total > 0 ? round(($total / $max_total) * 100, 2) : 0;

    $html .= '
            <tr class="total-row"><td>Total</td><td>' . $total . '</td><td>' . $max_total . '</td></tr>
            <tr class="total-row"><td colspan="2">Percentage</td><td>' . $percentage . '%</td></tr>
        </table>
        <div class="footer">
            <p>Generated on ' . date('Y-m-d') . '</p>
            <p>Teacher: ' . esc_html($teacher->post_title) . '</p>
        </div>';

    return $html;
}

?>