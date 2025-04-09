<?php
if (!defined('ABSPATH')) {
    exit;
}

function add_exam_subjects_institute_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    
    }
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
add_shortcode('add_exam_subjects_institute_dashboard', 'add_exam_subjects_institute_dashboard_shortcode');

