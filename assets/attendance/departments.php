<?php
if (!defined('ABSPATH')) {
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

function aspire_multiple_departments_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $table_name = $wpdb->prefix . 'departments';

    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();     }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'departments';

    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php   echo render_admin_header(wp_get_current_user());
         if (!is_center_subscribed($educational_center_id)) {
             return render_subscription_expired_message($educational_center_id);
         }
            $active_section = str_replace('-', '-', $section);
            include plugin_dir_path(__FILE__) . '../sidebar.php';
            ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'departments':
                        echo departments_list_shortcode();
                        break;
                    case 'add-department':
                        echo add_department_shortcode();
                        break;
                    case 'edit-department':
                        echo edit_department_shortcode();
                        break;
                    case 'delete-department':
                        echo delete_department_shortcode();
                        break;
                    default:
                        echo '<div class="alert alert-warning">Section not found. Please select a valid option.</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_multiple_departments_dashboard', 'aspire_multiple_departments_dashboard_shortcode');

// Helper: Get Departments with Teacher Count
function get_departments_with_teacher_count($education_center_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'departments';
    $departments = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE education_center_id = %s ORDER BY department_name",
        $education_center_id
    ));

    foreach ($departments as $dept) {
        $teachers = get_posts([
            'post_type' => 'teacher',
            'numberposts' => -1,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'teacher_department',
                    'value' => $dept->department_name,
                    'compare' => '='
                ],
                [
                    'key' => 'educational_center_id',
                    'value' => $education_center_id,
                    'compare' => '='
                ]
            ]
        ]);
        $dept->teacher_count = count($teachers);
        $dept->teachers = $teachers;
    }
    return $departments;
}

// List Departments with PDF Generation
// List Departments with PDF Generation
function departments_list_shortcode() {
    global $wpdb;
    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $departments = get_departments_with_teacher_count($education_center_id);

    ob_start();
    ?>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title">Department Overview</h3>
            <button class="btn btn-light generate-pdf-btn" data-nonce="<?php echo wp_create_nonce('generate_department_pdf'); ?>">Export as PDF</button>
        </div>
        <div class="card-body">
            <!-- Stats -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="alert alert-info">
                        <strong>Total Departments:</strong> <?php echo count($departments); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info">
                        <strong>Total Teachers:</strong> <?php echo array_sum(array_column($departments, 'teacher_count')); ?>
                    </div>
                </div>
            </div>
            <!-- Search -->
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="departmentSearch" placeholder="Search departments..." onkeyup="filterDepartments()">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <!-- Table -->
            <form method="POST" id="bulk-delete-form">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="departmentTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                                <th>Department ID</th>
                                <th>Department Name</th>
                                <th>Total Teachers</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($departments)) {
                                echo '<tr><td colspan="5">No departments found.</td></tr>';
                            } else {
                                foreach ($departments as $index => $dept) {
                                    echo '<tr>';
                                    echo '<td><input type="checkbox" name="department_ids[]" value="' . $dept->department_id . '"></td>';
                                    echo '<td>' . esc_html($dept->department_id) . '</td>';
                                    echo '<td>' . esc_html($dept->department_name) . '</td>';
                                    echo '<td>' . $dept->teacher_count . ' <a href="#" class="text-muted view-teachers-btn" data-dept-index="' . $index . '"><small>(View)</small></a></td>';
                                    echo '<td>
                                        <button class="btn btn-sm btn-info edit-department-btn" data-department-id="' . esc_attr($dept->department_id) . '" data-nonce="' . wp_create_nonce('edit_department_' . $dept->department_id) . '">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-department-btn" data-department-id="' . esc_attr($dept->department_id) . '" data-nonce="' . wp_create_nonce('delete_department_' . $dept->department_id) . '">Delete</button>
                                    </td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php wp_nonce_field('bulk_department_nonce', 'nonce');
                if(!is_teacher($current_user->ID)){
                ?>
                <button type="submit" name="bulk_delete" class="btn btn-danger mt-3 bulk-delete-btn" onclick="return confirm('Are you sure you want to delete selected departments?');">Delete Selected</button>
                <a href="?section=add-department" class="btn btn-primary mt-3">Add New Department</a>
           <?php } ?>
            </form>
        </div>
    </div>

    <!-- Custom Teacher Details Modal -->
    <div class="modal" id="teacherDetailsModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-primary text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Teacher Details</h5>
            <div id="teacher-details-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn">Close</button>
            </div>
        </div>
    </div>

    <!-- Existing Edit and Delete Modals Remain Unchanged -->
    <div class="modal" id="editDepartmentModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-info text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Edit Department</h5>
            <div id="edit-department-form-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Close</button>
                <button type="button" class="btn btn-primary update-department-btn">Update Department</button>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteDepartmentModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-danger text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Deletion</h5>
            <div id="delete-department-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Cancel</button>
                <button type="button" class="btn btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            // Filter Departments
            function filterDepartments() {
                let input = document.getElementById('departmentSearch').value.toLowerCase();
                let table = document.getElementById('departmentTable');
                let tr = table.getElementsByTagName('tr');
                for (let i = 1; i < tr.length; i++) {
                    let td = tr[i].getElementsByTagName('td')[2];
                    if (td) {
                        let txtValue = td.textContent || td.innerText;
                        tr[i].style.display = txtValue.toLowerCase().indexOf(input) > -1 ? '' : 'none';
                    }
                }
            }

            // Toggle Select All
            function toggleSelectAll() {
                let checkboxes = document.getElementsByName('department_ids[]');
                let selectAll = document.getElementById('selectAll');
                for (let checkbox of checkboxes) {
                    checkbox.checked = selectAll.checked;
                }
            }

            // Teacher Details Modal
            const teacherModal = document.getElementById('teacherDetailsModal');
            const teacherContainer = document.getElementById('teacher-details-container');
            const viewTeacherButtons = document.querySelectorAll('.view-teachers-btn');

            function showTeacherModal() { teacherModal.style.display = 'block'; }
            function hideTeacherModal() { teacherModal.style.display = 'none'; teacherContainer.innerHTML = ''; }

            viewTeacherButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const deptIndex = this.getAttribute('data-dept-index');
                    const departments = <?php echo json_encode($departments); ?>; // Pass departments data to JS
                    const dept = departments[deptIndex];

                    let html = '<h6>' + dept.department_name + '</h6>';
                    if (dept.teacher_count > 0) {
                        html += '<ul class="list-group">';
                        dept.teachers.forEach(teacher => {
                            const teacherName = teacher.teacher_name || teacher.post_title;
                            html += '<li class="list-group-item">' + teacherName + '</li>';
                        });
                        html += '</ul>';
                    } else {
                        html += '<p>No teachers assigned.</p>';
                    }

                    teacherContainer.innerHTML = html;
                    showTeacherModal();
                });
            });

            document.querySelectorAll('#teacherDetailsModal .modal-close, #teacherDetailsModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideTeacherModal);
            });
            window.addEventListener('click', function(e) { if (e.target === teacherModal) hideTeacherModal(); });
            // PDF Generation
            document.querySelector('.generate-pdf-btn').addEventListener('click', function() {
                const button = this;
                const nonce = button.getAttribute('data-nonce');
                button.disabled = true;
                button.textContent = 'Generating...';

                const data = new FormData();
                data.append('action', 'generate_department_pdf');
                data.append('nonce', nonce);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: data
                })
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'departments_teachers_report_' + new Date().toISOString().replace(/[-:T]/g, '').split('.')[0] + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                    button.disabled = false;
                    button.textContent = 'Export as PDF';
                })
                .catch(error => {
                    console.error('PDF Error:', error);
                    alert('Error generating PDF');
                    button.disabled = false;
                    button.textContent = 'Export as PDF';
                });
            });

            // Edit Modal
            const editModal = document.getElementById('editDepartmentModal');
            const editContainer = document.getElementById('edit-department-form-container');
            const editButtons = document.querySelectorAll('.edit-department-btn');
            const editUpdateButton = document.querySelector('.update-department-btn');

            function showEditModal() { editModal.style.display = 'block'; }
            function hideEditModal() { editModal.style.display = 'none'; editContainer.innerHTML = ''; }

            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const departmentId = this.getAttribute('data-department-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_department_edit_form');
                    data.append('department_id', departmentId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            editContainer.innerHTML = data.data.form_html;
                            showEditModal();
                            attachEditFormHandler(departmentId);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load edit form'));
                        }
                        this.disabled = false;
                        this.textContent = 'Edit';
                    })
                    .catch(error => {
                        console.error('Edit Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Edit';
                    });
                });
            });

            document.querySelectorAll('#editDepartmentModal .modal-close, #editDepartmentModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideEditModal);
            });
            window.addEventListener('click', function(e) { if (e.target === editModal) hideEditModal(); });

            function attachEditFormHandler(departmentId) {
                const form = editContainer.querySelector('#edit-department-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        editUpdateButton.disabled = true;
                        editUpdateButton.textContent = 'Updating...';

                        const formData = new FormData(this);
                        formData.append('action', 'update_department');
                        formData.append('department_id', departmentId);

                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                editContainer.innerHTML = '<div class="alert alert-success">Department updated successfully!</div>';
                                // setTimeout(() => {
                                //     hideEditModal();
                                //     location.reload();
                                // }, 1500);
// Immediately redirect to the same page
window.location.href = window.location.href;
                            } else {
                                editContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error updating department') + '</div>';
                                editUpdateButton.disabled = false;
                                editUpdateButton.textContent = 'Update Department';
                            }
                        })
                        .catch(error => {
                            console.error('Update Error:', error);
                            editContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                            editUpdateButton.disabled = false;
                            editUpdateButton.textContent = 'Update Department';
                        });
                    });
                    editUpdateButton.onclick = () => form.dispatchEvent(new Event('submit'));
                }
            }

            // Delete Modal
            const deleteModal = document.getElementById('deleteDepartmentModal');
            const deleteContainer = document.getElementById('delete-department-container');
            const deleteButtons = document.querySelectorAll('.delete-department-btn');
            const confirmDeleteButton = document.querySelector('.confirm-delete-btn');

            function showDeleteModal() { deleteModal.style.display = 'block'; }
            function hideDeleteModal() { deleteModal.style.display = 'none'; deleteContainer.innerHTML = ''; }

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const departmentId = this.getAttribute('data-department-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_department_delete_confirm');
                    data.append('department_id', departmentId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteContainer.innerHTML = data.data.confirm_html;
                            showDeleteModal();
                            attachDeleteHandler(departmentId, nonce);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load delete confirmation'));
                        }
                        this.disabled = false;
                        this.textContent = 'Delete';
                    })
                    .catch(error => {
                        console.error('Delete Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Delete';
                    });
                });
            });

            document.querySelectorAll('#deleteDepartmentModal .modal-close, #deleteDepartmentModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideDeleteModal);
            });
            window.addEventListener('click', function(e) { if (e.target === deleteModal) hideDeleteModal(); });

            function attachDeleteHandler(departmentId, nonce) {
                confirmDeleteButton.onclick = function() {
                    confirmDeleteButton.disabled = true;
                    confirmDeleteButton.textContent = 'Deleting...';

                    const data = new FormData();
                    data.append('action', 'delete_department');
                    data.append('department_id', departmentId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteContainer.innerHTML = '<div class="alert alert-success">Department deleted successfully!</div>';
                            // setTimeout(() => {
                            //     hideDeleteModal();
                            //     location.reload();
                            // }, 1500);
// Immediately redirect to the same page
window.location.href = window.location.href;
                        } else {
                            deleteContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error deleting department') + '</div>';
                            confirmDeleteButton.disabled = false;
                            confirmDeleteButton.textContent = 'Delete';
                        }
                    })
                    .catch(error => {
                        console.error('Delete Error:', error);
                        deleteContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                        confirmDeleteButton.disabled = false;
                        confirmDeleteButton.textContent = 'Delete';
                    });
                };
            }

            // Bulk Delete
            document.getElementById('bulk-delete-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'bulk_delete_departments');

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Selected departments deleted successfully!');
                        // location.reload();
// Immediately redirect to the same page
window.location.href = window.location.href;
                    } else {
                        alert('Error: ' + (data.data.message || 'Unable to delete departments'));
                    }
                })
                .catch(error => {
                    console.error('Bulk Delete Error:', error);
                    alert('An error occurred: ' + error.message);
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// Add Department
function add_department_shortcode() {
    global $wpdb;
    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $table_name = $wpdb->prefix . 'departments';

    if (isset($_POST['submit_department']) && wp_verify_nonce($_POST['nonce'], 'department_nonce')) {
        $department_name = sanitize_text_field($_POST['department_name']);
        if (!empty($department_name)) {
            $wpdb->insert($table_name, ['department_name' => $department_name, 'education_center_id' => $education_center_id], ['%s', '%s']);
            echo '<div class="alert alert-success">Department added successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Department name is required.</div>';
        }
    }

    ob_start();
    ?>
    <div class="card">
        <div class="card-header bg-success text-white">
            <h3 class="card-title">Add New Department</h3>
        </div>
        <div class="card-body">
            <form method="POST" id="add-department-form">
                <div class="mb-3">
                    <label for="department_name" class="form-label">Department Name</label>
                    <input type="text" name="department_name" id="department_name" class="form-control" required>
                </div>
                <?php wp_nonce_field('department_nonce', 'nonce'); ?>
                <button type="submit" name="submit_department" class="btn btn-primary">Add Department</button>
                <a href="?section=departments" class="btn btn-secondary">Back to Directory</a>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('add-department-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_department');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.insertAdjacentHTML('beforebegin', '<div class="alert alert-success">Department added successfully!</div>');
                    // setTimeout(() => location.href = '?section=departments', 1500);
                    // Redirect to the same page
// Immediately redirect to the same page
window.location.href = window.location.href;

                } else {
                    this.insertAdjacentHTML('beforebegin', '<div class="alert alert-danger">' + (data.data.message || 'Department name is required') + '</div>');
                }
            })
            .catch(error => {
                console.error('Add Error:', error);
                this.insertAdjacentHTML('beforebegin', '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>');
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// Edit Department Shortcode (now just a placeholder for AJAX)
function edit_department_shortcode() {
    // return '<div class="alert alert-info">Please select a department to edit from the list.</div>';
    global $wpdb;
    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $departments = get_departments_with_teacher_count($education_center_id);

    ob_start();
    ?>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title">Edit Department</h3>
            <button class="btn btn-light generate-pdf-btn" data-nonce="<?php echo wp_create_nonce('generate_department_pdf'); ?>">Export as PDF</button>
        </div>
        <div class="card-body">
            <!-- Stats -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="alert alert-info">
                        <strong>Total Departments:</strong> <?php echo count($departments); ?>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="alert alert-info">
                        <strong>Total Teachers:</strong> <?php echo array_sum(array_column($departments, 'teacher_count')); ?>
                    </div>
                </div>
            </div>
            <!-- Search -->
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="departmentSearch" placeholder="Search departments..." onkeyup="filterDepartments()">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <!-- Table -->
            <form method="POST" id="bulk-delete-form">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="departmentTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                                <th>Department ID</th>
                                <th>Department Name</th>
                            
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($departments)) {
                                echo '<tr><td colspan="5">No departments found.</td></tr>';
                            } else {
                                foreach ($departments as $index => $dept) {
                                    echo '<tr>';
                                    echo '<td><input type="checkbox" name="department_ids[]" value="' . $dept->department_id . '"></td>';
                                    echo '<td>' . esc_html($dept->department_id) . '</td>';
                                    echo '<td>' . esc_html($dept->department_name) . '</td>';
                                    echo '<td>
                                        <button class="btn btn-sm btn-info edit-department-btn" data-department-id="' . esc_attr($dept->department_id) . '" data-nonce="' . wp_create_nonce('edit_department_' . $dept->department_id) . '">Edit</button>
                                    </td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php wp_nonce_field('bulk_department_nonce', 'nonce');if(!is_teacher($current_user->ID)){
                ?>
                <button type="submit" name="bulk_delete" class="btn btn-danger mt-3 bulk-delete-btn" onclick="return confirm('Are you sure you want to delete selected departments?');">Delete Selected</button>
                <a href="?section=add-department" class="btn btn-primary mt-3">Add New Department</a>
                <?php } ?>
                </form>
        </div>
    </div>

    <!-- Custom Teacher Details Modal -->
    <div class="modal" id="teacherDetailsModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-primary text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Teacher Details</h5>
            <div id="teacher-details-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn">Close</button>
            </div>
        </div>
    </div>

    <!-- Existing Edit and Delete Modals Remain Unchanged -->
    <div class="modal" id="editDepartmentModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-info text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Edit Department</h5>
            <div id="edit-department-form-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Close</button>
                <button type="button" class="btn btn-primary update-department-btn">Update Department</button>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteDepartmentModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-danger text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Deletion</h5>
            <div id="delete-department-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Cancel</button>
                <button type="button" class="btn btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            // Filter Departments
            function filterDepartments() {
                let input = document.getElementById('departmentSearch').value.toLowerCase();
                let table = document.getElementById('departmentTable');
                let tr = table.getElementsByTagName('tr');
                for (let i = 1; i < tr.length; i++) {
                    let td = tr[i].getElementsByTagName('td')[2];
                    if (td) {
                        let txtValue = td.textContent || td.innerText;
                        tr[i].style.display = txtValue.toLowerCase().indexOf(input) > -1 ? '' : 'none';
                    }
                }
            }

            // Toggle Select All
            function toggleSelectAll() {
                let checkboxes = document.getElementsByName('department_ids[]');
                let selectAll = document.getElementById('selectAll');
                for (let checkbox of checkboxes) {
                    checkbox.checked = selectAll.checked;
                }
            }

            // Edit Modal
            const editModal = document.getElementById('editDepartmentModal');
            const editContainer = document.getElementById('edit-department-form-container');
            const editButtons = document.querySelectorAll('.edit-department-btn');
            const editUpdateButton = document.querySelector('.update-department-btn');

            function showEditModal() { editModal.style.display = 'block'; }
            function hideEditModal() { editModal.style.display = 'none'; editContainer.innerHTML = ''; }

            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const departmentId = this.getAttribute('data-department-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_department_edit_form');
                    data.append('department_id', departmentId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            editContainer.innerHTML = data.data.form_html;
                            showEditModal();
                            attachEditFormHandler(departmentId);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load edit form'));
                        }
                        this.disabled = false;
                        this.textContent = 'Edit';
                    })
                    .catch(error => {
                        console.error('Edit Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Edit';
                    });
                });
            });

            document.querySelectorAll('#editDepartmentModal .modal-close, #editDepartmentModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideEditModal);
            });
            window.addEventListener('click', function(e) { if (e.target === editModal) hideEditModal(); });

            function attachEditFormHandler(departmentId) {
                const form = editContainer.querySelector('#edit-department-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        editUpdateButton.disabled = true;
                        editUpdateButton.textContent = 'Updating...';

                        const formData = new FormData(this);
                        formData.append('action', 'update_department');
                        formData.append('department_id', departmentId);

                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                editContainer.innerHTML = '<div class="alert alert-success">Department updated successfully!</div>';
                                // setTimeout(() => {
                                //     hideEditModal();
                                //     location.reload();
                                // }, 1500);
                                // Immediately redirect to the same page
    window.location.href = window.location.href;
                            } else {
                                editContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error updating department') + '</div>';
                                editUpdateButton.disabled = false;
                                editUpdateButton.textContent = 'Update Department';
                            }
                        })
                        .catch(error => {
                            console.error('Update Error:', error);
                            editContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                            editUpdateButton.disabled = false;
                            editUpdateButton.textContent = 'Update Department';
                        });
                    });
                    editUpdateButton.onclick = () => form.dispatchEvent(new Event('submit'));
                }
            }

});
</script>
    <?php
    return ob_get_clean();
}

// Delete Department Shortcode (now just a placeholder for AJAX)
function delete_department_shortcode() {
    // return '<div class="alert alert-info">Please select a department to delete from the list.</div>';
    global $wpdb;
    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $departments = get_departments_with_teacher_count($education_center_id);

    ob_start();
    ?>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title">Delete Department</h3>
            <button class="btn btn-light generate-pdf-btn" data-nonce="<?php echo wp_create_nonce('generate_department_pdf'); ?>">Export as PDF</button>
        </div>
        <div class="card-body">
        
            <!-- Search -->
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="departmentSearch" placeholder="Search departments..." onkeyup="filterDepartments()">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <!-- Table -->
            <form method="POST" id="bulk-delete-form">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="departmentTable">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll()"></th>
                                <th>Department ID</th>
                                <th>Department Name</th>
                          
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($departments)) {
                                echo '<tr><td colspan="5">No departments found.</td></tr>';
                            } else {
                                foreach ($departments as $index => $dept) {
                                    echo '<tr>';
                                    echo '<td><input type="checkbox" name="department_ids[]" value="' . $dept->department_id . '"></td>';
                                    echo '<td>' . esc_html($dept->department_id) . '</td>';
                                    echo '<td>' . esc_html($dept->department_name) . '</td>';
                                    echo '<td>
                                        <button class="btn btn-sm btn-danger delete-department-btn" data-department-id="' . esc_attr($dept->department_id) . '" data-nonce="' . wp_create_nonce('delete_department_' . $dept->department_id) . '">Delete</button>
                                    </td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php wp_nonce_field('bulk_department_nonce', 'nonce'); if(!is_teacher($current_user->ID)){
                ?>
                <button type="submit" name="bulk_delete" class="btn btn-danger mt-3 bulk-delete-btn" onclick="return confirm('Are you sure you want to delete selected departments?');">Delete Selected</button>
                <a href="?section=add-department" class="btn btn-primary mt-3">Add New Department</a>
                <?php } ?>
                </form>
        </div>
    </div>

    <div class="modal" id="deleteDepartmentModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-danger text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Deletion</h5>
            <div id="delete-department-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Cancel</button>
                <button type="button" class="btn btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            // Filter Departments
            function filterDepartments() {
                let input = document.getElementById('departmentSearch').value.toLowerCase();
                let table = document.getElementById('departmentTable');
                let tr = table.getElementsByTagName('tr');
                for (let i = 1; i < tr.length; i++) {
                    let td = tr[i].getElementsByTagName('td')[2];
                    if (td) {
                        let txtValue = td.textContent || td.innerText;
                        tr[i].style.display = txtValue.toLowerCase().indexOf(input) > -1 ? '' : 'none';
                    }
                }
            }

            // Toggle Select All
            function toggleSelectAll() {
                let checkboxes = document.getElementsByName('department_ids[]');
                let selectAll = document.getElementById('selectAll');
                for (let checkbox of checkboxes) {
                    checkbox.checked = selectAll.checked;
                }
            }
  // Delete Modal
  const deleteModal = document.getElementById('deleteDepartmentModal');
            const deleteContainer = document.getElementById('delete-department-container');
            const deleteButtons = document.querySelectorAll('.delete-department-btn');
            const confirmDeleteButton = document.querySelector('.confirm-delete-btn');

            function showDeleteModal() { deleteModal.style.display = 'block'; }
            function hideDeleteModal() { deleteModal.style.display = 'none'; deleteContainer.innerHTML = ''; }

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const departmentId = this.getAttribute('data-department-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_department_delete_confirm');
                    data.append('department_id', departmentId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteContainer.innerHTML = data.data.confirm_html;
                            showDeleteModal();
                            attachDeleteHandler(departmentId, nonce);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load delete confirmation'));
                        }
                        this.disabled = false;
                        this.textContent = 'Delete';
                    })
                    .catch(error => {
                        console.error('Delete Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Delete';
                    });
                });
            });

            document.querySelectorAll('#deleteDepartmentModal .modal-close, #deleteDepartmentModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideDeleteModal);
            });
            window.addEventListener('click', function(e) { if (e.target === deleteModal) hideDeleteModal(); });

            function attachDeleteHandler(departmentId, nonce) {
                confirmDeleteButton.onclick = function() {
                    confirmDeleteButton.disabled = true;
                    confirmDeleteButton.textContent = 'Deleting...';

                    const data = new FormData();
                    data.append('action', 'delete_department');
                    data.append('department_id', departmentId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteContainer.innerHTML = '<div class="alert alert-success">Department deleted successfully!</div>';
                            // setTimeout(() => {
                            //     hideDeleteModal();
                            //     location.reload();
                            // }, 1500);
// Immediately redirect to the same page
window.location.href = window.location.href;
                        } else {
                            deleteContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error deleting department') + '</div>';
                            confirmDeleteButton.disabled = false;
                            confirmDeleteButton.textContent = 'Delete';
                        }
                    })
                    .catch(error => {
                        console.error('Delete Error:', error);
                        deleteContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                        confirmDeleteButton.disabled = false;
                        confirmDeleteButton.textContent = 'Delete';
                    });
                };
            }

            // Bulk Delete
            document.getElementById('bulk-delete-form').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('action', 'bulk_delete_departments');

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Selected departments deleted successfully!');
                        // location.reload();
// Immediately redirect to the same page
window.location.href = window.location.href;
                    } else {
                        alert('Error: ' + (data.data.message || 'Unable to delete departments'));
                    }
                })
                .catch(error => {
                    console.error('Bulk Delete Error:', error);
                    alert('An error occurred: ' + error.message);
                });
            });
        });

 
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handlers

// Generate PDF
add_action('wp_ajax_generate_department_pdf', 'generate_department_pdf_callback');
function generate_department_pdf_callback() {
    check_ajax_referer('generate_department_pdf', 'nonce');

    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $departments = get_departments_with_teacher_count($education_center_id);

    if (empty($departments)) {
        wp_send_json_error(['message' => 'No departments found to generate PDF']);
        wp_die();
    }

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $options->set('isHtml5ParserEnabled', true);
    $options->set('chroot', ABSPATH);
    $options->set('tempDir', sys_get_temp_dir());
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf($options);

    $html = '
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @page { margin: 15mm; border: 2px solid #007bff; padding: 10mm; }
            body { font-family: Helvetica, sans-serif; font-size: 12pt; color: #333; line-height: 1.4; }
            .container { width: 100%; padding: 15px; background-color: #fff; }
            .header { text-align: center; padding-bottom: 15px; border-bottom: 2px solid #007bff; margin-bottom: 20px; }
            .header h1 { font-size: 24pt; color: #007bff; margin: 0; text-transform: uppercase; }
            .header .subtitle { font-size: 14pt; color: #666; margin: 5px 0; }
            .summary-section, .details-section { margin: 20px 0; }
            .summary-section h2, .details-section h2 { font-size: 18pt; color: #007bff; margin-bottom: 15px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
            .details-section h3 { font-size: 16pt; color: #007bff; margin: 15px 0 10px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f8f9fa; color: #007bff; font-weight: bold; }
            tr:nth-child(even) { background-color: #f9f9f9; }
            .teacher-list { margin-left: 20px; }
            .teacher-list ul { list-style-type: disc; padding-left: 20px; }
            .footer { text-align: center; margin-top: 30px; font-size: 10pt; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Department & Teacher Report</h1>
                <div class="subtitle">Educational Center Comprehensive Report</div>
            </div>
            <div class="summary-section">
                <h2>Department Summary</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Department Name</th>
                            <th>Total Teachers</th>
                        </tr>
                    </thead>
                    <tbody>';

    $total_teachers = 0;
    foreach ($departments as $dept) {
        $total_teachers += $dept->teacher_count;
        $html .= '
                        <tr>
                            <td>' . esc_html($dept->department_id) . '</td>
                            <td>' . esc_html($dept->department_name) . '</td>
                            <td>' . esc_html($dept->teacher_count) . '</td>
                        </tr>';
    }

    $html .= '
                    </tbody>
                </table>
                <p><strong>Total Departments:</strong> ' . count($departments) . ' | <strong>Total Teachers:</strong> ' . $total_teachers . '</p>
            </div>
            <div class="details-section">
                <h2>Department-wise Teacher Details</h2>';

    foreach ($departments as $dept) {
        $html .= '
                <h3>' . esc_html($dept->department_name) . ' (ID: ' . $dept->department_id . ')</h3>
                <p><strong>Total Teachers:</strong> ' . esc_html($dept->teacher_count) . '</p>';
        if ($dept->teacher_count > 0) {
            $html .= '
                <div class="teacher-list">
                    <ul>';
                    foreach ($dept->teachers as $teacher) {
                        $teacher_name = !empty($teacher->teacher_name) ? esc_html($teacher->teacher_name) : esc_html($teacher->post_title);
                        $html .= '<li style="list-style: none;">' . esc_html($teacher->ID) . ' | ' . $teacher_name . '</li>';
                    }
            $html .= '
                    </ul>
                </div>';
        } else {
            $html .= '<p>No teachers assigned to this department.</p>';
        }
    }

    $html .= '
            </div>
            <div class="footer">
                <p>Generated on ' . date('Y-m-d H:i:s') . '</p>
            </div>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $pdf_content = $dompdf->output();

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="departments_teachers_report_' . date('Ymd_His') . '.pdf"');
    header('Content-Length: ' . strlen($pdf_content));
    echo $pdf_content;
    wp_die();
}

// Add Department
add_action('wp_ajax_add_department', 'add_department_callback');
function add_department_callback() {
    global $wpdb;
    check_ajax_referer('department_nonce', 'nonce');

    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    
    $table_name = $wpdb->prefix . 'departments';
    $department_name = sanitize_text_field($_POST['department_name']);

    if (empty($department_name)) {
        wp_send_json_error(['message' => 'Department name is required']);
    } else {
        $wpdb->insert($table_name, ['department_name' => $department_name, 'education_center_id' => $education_center_id], ['%s', '%s']);
        wp_send_json_success(['message' => 'Department added successfully']);
    }
    wp_die();
}

// Load Edit Form
add_action('wp_ajax_load_department_edit_form', 'load_department_edit_form_callback');
function load_department_edit_form_callback() {
    global $wpdb;
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'edit_department_' . $department_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        wp_die();
    }

    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $table_name = $wpdb->prefix . 'departments';
    $department = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE department_id = %d AND education_center_id = %s",
        $department_id, $education_center_id
    ));

    if (!$department) {
        wp_send_json_error(['message' => 'Department not found']);
        wp_die();
    }

    ob_start();
    ?>
    <form id="edit-department-form">
        <div class="mb-3">
            <label for="department_name" class="form-label">Department Name</label>
            <input type="text" name="department_name" id="department_name" class="form-control" value="<?php echo esc_attr($department->department_name); ?>" required>
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('department_nonce'); ?>">
    </form>
    <?php
    $form_html = ob_get_clean();
    wp_send_json_success(['form_html' => $form_html]);
    wp_die();
}

// Update Department
add_action('wp_ajax_update_department', 'update_department_callback');
function update_department_callback() {
    global $wpdb;
    check_ajax_referer('department_nonce', 'nonce');

    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $table_name = $wpdb->prefix . 'departments';
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    $department_name = sanitize_text_field($_POST['department_name']);

    if (empty($department_name)) {
        wp_send_json_error(['message' => 'Department name is required']);
    } else {
        $updated = $wpdb->update(
            $table_name,
            ['department_name' => $department_name],
            ['department_id' => $department_id, 'education_center_id' => $education_center_id],
            ['%s'],
            ['%d', '%s']
        );
        if ($updated === false) {
            wp_send_json_error(['message' => 'Database update failed']);
        } else {
            wp_send_json_success(['message' => 'Department updated successfully']);
        }
    }
    wp_die();
}

// Load Delete Confirmation
add_action('wp_ajax_load_department_delete_confirm', 'load_department_delete_confirm_callback');
function load_department_delete_confirm_callback() {
    global $wpdb;
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'delete_department_' . $department_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        wp_die();
    }

    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $department = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}departments WHERE department_id = %d AND education_center_id = %s",
        $department_id, $education_center_id
    ));

    if (!$department) {
        wp_send_json_error(['message' => 'Department not found']);
        wp_die();
    }

    $teachers = get_posts([
        'post_type' => 'teacher',
        'numberposts' => -1,
        'meta_query' => [
            'relation' => 'AND',
            ['key' => 'teacher_department', 'value' => $department->department_name, 'compare' => '='],
            ['key' => 'educational_center_id', 'value' => $education_center_id, 'compare' => '=']
        ]
    ]);

    ob_start();
    ?>
    <p>Are you sure you want to delete the following department?</p>
    <ul>
        <li><strong>ID:</strong> <?php echo esc_html($department->department_id); ?></li>
        <li><strong>Name:</strong> <?php echo esc_html($department->department_name); ?></li>
        <li><strong>Total Teachers:</strong> <?php echo count($teachers); ?></li>
    </ul>
    <p class="text-danger">Note: Deleting this department will not remove associated teachers, but they will lose their department assignment.</p>
    <?php
    $confirm_html = ob_get_clean();
    wp_send_json_success(['confirm_html' => $confirm_html]);
    wp_die();
}

// Delete Department
add_action('wp_ajax_delete_department', 'delete_department_callback');
function delete_department_callback() {
    global $wpdb;
    $department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'delete_department_' . $department_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        wp_die();
    }

    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $deleted = $wpdb->delete(
        $wpdb->prefix . 'departments',
        ['department_id' => $department_id, 'education_center_id' => $education_center_id],
        ['%d', '%s']
    );

    if ($deleted === false || $deleted === 0) {
        wp_send_json_error(['message' => 'Failed to delete department']);
    } else {
        wp_send_json_success(['message' => 'Department deleted successfully']);
    }
    wp_die();
}

// Bulk Delete Departments
add_action('wp_ajax_bulk_delete_departments', 'bulk_delete_departments_callback');
function bulk_delete_departments_callback() {
    global $wpdb;
    check_ajax_referer('bulk_department_nonce', 'nonce');

    // $education_center_id = get_educational_center_data();
    $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
        if (empty($education_center_id)) {
            wp_redirect(home_url('/login'));
            exit();
        }
    $department_ids = isset($_POST['department_ids']) ? array_map('intval', $_POST['department_ids']) : [];

    if (empty($department_ids)) {
        wp_send_json_error(['message' => 'No departments selected']);
    } else {
        $wpdb->query("DELETE FROM {$wpdb->prefix}departments WHERE department_id IN (" . implode(',', $department_ids) . ") AND education_center_id = '$education_center_id'");
        wp_send_json_success(['message' => 'Selected departments deleted successfully']);
    }
    wp_die();
}