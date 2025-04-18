<?php

/**
 * Enqueue Scripts and Styles
 */
// function aspire_demo_enqueue_scripts() {
//     wp_enqueue_style('aspire-demo-style', get_template_directory_uri() . '/style.css');
//     wp_enqueue_script('aspire-demo-script', plugin_dir_url(__FILE__) . 'demo.js', [], '1.0.11', true);
//     wp_enqueue_script('export-utils', plugin_dir_url(__FILE__) . 'export-utils.js', ['jquery'], '1.0.0', true);
//     wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], '2.5.1', true);
//     wp_enqueue_script('papaparse', 'https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js', [], '5.3.2', true);
//     wp_enqueue_script('xlsx', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js', [], '0.18.5', true);
// }
// add_action('wp_enqueue_scripts', 'aspire_demo_enqueue_scripts');


/**
 * Attendance Functions
 */
function demoRenderTeacherManageStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Manage Student Attendance</h2>
        <div class="alert alert-success">View and manage attendance records for your students.</div>
        <div class="summary-widget">
            <h4>Total Records: <?php echo count($data['student_attendance']); ?> | Present: <?php echo array_count_values(array_column($data['student_attendance'], 'status'))['Present'] ?? 0; ?></h4>
        </div>
        <div class="filter-dropdown">
            <label for="date-filter" class="me-2">Filter by Date:</label>
            <select id="date-filter" class="form-select d-inline-block w-auto">
                <option value="">All Dates</option>
                <?php
                $dates = array_unique(array_column($data['student_attendance'], 'date'));
                foreach ($dates as $date) {
                    echo '<option value="' . esc_attr($date) . '">' . esc_html($date) . '</option>';
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'add-student-attendance'])); ?>" class="btn btn-primary">Add Attendance</a>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'import-student-attendance'])); ?>" class="btn btn-info">Import Attendance</a>
            <button class="btn btn-secondary export-btn" data-type="pdf" data-section="student_attendance">Export to PDF</button>
            <button class="btn btn-secondary export-btn" data-type="csv" data-section="student_attendance">Export to CSV</button>
            <button class="btn btn-secondary export-btn" data-type="excel" data-section="student_attendance">Export to Excel</button>
        </div>
        <table class="table table-striped" id="teacher-attendance">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['student_attendance'] as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record['student_id']); ?></td>
                        <td><?php echo esc_html($record['student_name']); ?></td>
                        <td><?php echo esc_html($record['class']); ?></td>
                        <td><?php echo esc_html($record['section']); ?></td>
                        <td><?php echo esc_html($record['date']); ?></td>
                        <td>
                            <span class="status-badge <?php echo $record['status'] === 'Present' ? 'bg-success' : ($record['status'] === 'Absent' ? 'bg-danger' : 'bg-warning'); ?>" data-id="<?php echo esc_html($record['student_id']); ?>">
                                <?php echo esc_html($record['status']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'edit-student-attendance', 'id' => $record['student_id']])); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'delete-student-attendance', 'id' => $record['student_id']])); ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        window.student_attendanceData = <?php echo json_encode($data['student_attendance']); ?>;
        document.getElementById('date-filter')?.addEventListener('change', function() {
            const date = this.value;
            const rows = document.querySelectorAll('#teacher-attendance tbody tr');
            rows.forEach(row => {
                const rowDate = row.cells[4].textContent;
                row.style.display = date === '' || rowDate === date ? '' : 'none';
            });
        });
        document.querySelectorAll('.status-badge').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const statuses = ['Present', 'Absent', 'Late'];
                const current = toggle.textContent.trim();
                const next = statuses[(statuses.indexOf(current) + 1) % statuses.length];
                toggle.textContent = next;
                toggle.classList.remove('bg-success', 'bg-danger', 'bg-warning');
                toggle.classList.add(next === 'Present' ? 'bg-success' : (next === 'Absent' ? 'bg-danger' : 'bg-warning'));
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddStudentAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Attendance</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'manage-student-attendance'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="teacher">
                <input type="hidden" name="demo-section" value="student-attendance">
                <input type="hidden" name="demo-action" value="manage-student-attendance">
                <div class="mb-3">
                    <label for="student-id">Student ID</label>
                    <input type="text" id="student-id" name="student-id" required placeholder="e.g., S001">
                </div>
                <div class="mb-3">
                    <label for="student-name">Student Name</label>
                    <input type="text" id="student-name" name="student-name" required placeholder="e.g., John Doe">
                </div>
                <div class="mb-3">
                    <label for="class">Class</label>
                    <input type="text" id="class" name="class" required placeholder="e.g., 10">
                </div>
                <div class="mb-3">
                    <label for="section">Section</label>
                    <input type="text" id="section" name="section" required placeholder="e.g., A">
                </div>
                <div class="mb-3">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="mb-3">
                    <label for="status">Status</label>
                    <select id="status" name="status" class="form-select" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Attendance</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Attendance</h2>
        <div class="alert alert-info">Select an attendance record to edit.</div>
        <table class="table table-striped" id="teacher-attendance">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['student_attendance'] as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record['student_id']); ?></td>
                        <td><?php echo esc_html($record['student_name']); ?></td>
                        <td><?php echo esc_html($record['class']); ?></td>
                        <td><?php echo esc_html($record['section']); ?></td>
                        <td><?php echo esc_html($record['date']); ?></td>
                        <td><?php echo esc_html($record['status']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'edit-student-attendance', 'id' => $record['student_id']])); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'delete-student-attendance', 'id' => $record['student_id']])); ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (isset($_GET['id'])): ?>
            <div class="card p-4 mt-4">
                <h3>Editing Record for Student ID: <?php echo esc_html($_GET['id']); ?></h3>
                <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'manage-student-attendance'])); ?>" method="get">
                    <input type="hidden" name="demo-role" value="teacher">
                    <input type="hidden" name="demo-section" value="student-attendance">
                    <input type="hidden" name="demo-action" value="manage-student-attendance">
                    <div class="mb-3">
                        <label for="student-id">Student ID</label>
                        <input type="text" id="student-id" name="student-id" value="<?php echo esc_html($_GET['id']); ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="student-name">Student Name</label>
                        <input type="text" id="student-name" name="student-name" value="John Doe" required>
                    </div>
                    <div class="mb-3">
                        <label for="class">Class</label>
                        <input type="text" id="class" name="class" value="10" required>
                    </div>
                    <div class="mb-3">
                        <label for="section">Section</label>
                        <input type="text" id="section" name="section" value="A" required>
                    </div>
                    <div class="mb-3">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" value="2025-04-10" required>
                    </div>
                    <div class="mb-3">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-select" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Attendance</button>
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Attendance</h2>
        <div class="alert alert-warning">Click "Delete" to remove an attendance record.</div>
        <table class="table table-striped" id="teacher-attendance">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['student_attendance'] as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record['student_id']); ?></td>
                        <td><?php echo esc_html($record['student_name']); ?></td>
                        <td><?php echo esc_html($record['class']); ?></td>
                        <td><?php echo esc_html($record['section']); ?></td>
                        <td><?php echo esc_html($record['date']); ?></td>
                        <td><?php echo esc_html($record['status']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherImportStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Import Attendance</h2>
        <div class="alert alert-info">Upload a CSV file to import attendance records.</div>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'manage-student-attendance'])); ?>" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="attendance-file" class="form-label">Select CSV File</label>
                    <input type="file" id="attendance-file" name="attendance-file" accept=".csv" class="form-control" required>
                    <small class="form-text text-muted">CSV format: Student ID, Name, Class, Section, Date, Status</small>
                </div>
                <button type="submit" class="btn btn-primary">Import</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
            <div class="mt-4 import-preview">
                <h4>Preview</h4>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="import-preview-table"></tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('attendance-file')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                Papa.parse(file, {
                    header: true,
                    complete: function(results) {
                        const tbody = document.getElementById('import-preview-table');
                        tbody.innerHTML = '';
                        results.data.slice(0, 5).forEach(row => {
                            tbody.innerHTML += `
                                <tr>
                                    <td>${row['Student ID'] || ''}</td>
                                    <td>${row['Name'] || ''}</td>
                                    <td>${row['Class'] || ''}</td>
                                    <td>${row['Section'] || ''}</td>
                                    <td>${row['Date'] || ''}</td>
                                    <td>${row['Status'] || ''}</td>
                                </tr>
                            `;
                        });
                    }
                });
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherExportStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Export Attendance</h2>
        <div class="alert alert-success">Select a format to export attendance records.</div>
        <div class="mb-3">
            <button class="btn btn-secondary export-btn" data-type="pdf" data-section="student_attendance">Export to PDF</button>
            <button class="btn btn-secondary export-btn" data-type="csv" data-section="student_attendance">Export to CSV</button>
            <button class="btn btn-secondary export-btn" data-type="excel" data-section="student_attendance">Export to Excel</button>
        </div>
        <table class="table table-striped" id="teacher-attendance">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['student_attendance'] as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record['student_id']); ?></td>
                        <td><?php echo esc_html($record['student_name']); ?></td>
                        <td><?php echo esc_html($record['class']); ?></td>
                        <td><?php echo esc_html($record['section']); ?></td>
                        <td><?php echo esc_html($record['date']); ?></td>
                        <td><?php echo esc_html($record['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script>
        window.student_attendanceData = <?php echo json_encode($data['student_attendance']); ?>;
    </script>
    <?php
    return ob_get_clean();
}


// /**
//  * Data Function (Updated for Teacher)
//  */
// function demoGetTeacherData() {
//     return [
//         'role' => 'teacher',
//         'exams' => [
//             ['name' => 'Math Exam', 'date' => '2025-04-15', 'class' => 'Class 1A'],
//             ['name' => 'Science Exam', 'date' => '2025-04-20', 'class' => 'Class 1A'],
//         ],
//         'student_attendance' => [
//             ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-10', 'status' => 'Present'],
//             ['student_id' => 'S002', 'student_name' => 'Jane Smith', 'class' => '10', 'section' => 'B', 'date' => '2025-04-10', 'status' => 'Absent'],
//             ['student_id' => 'S003', 'student_name' => 'Alice Brown', 'class' => '10', 'section' => 'A', 'date' => '2025-04-11', 'status' => 'Late'],
//         ],
//         'students' => [
//             ['id' => 'ST1001', 'name' => 'John Doe', 'email' => 'john@demo-pro.edu', 'class' => 'Class 1A'],
//             ['id' => 'ST1002', 'name' => 'Jane Smith', 'email' => 'jane@demo-pro.edu', 'class' => 'Class 1A'],
//         ],
//         'profile' => [
//             'teacher_id' => 'TR1001',
//             'name' => 'Alice Brown',
//             'email' => 'alice@demo-pro.edu',
//             'subject' => 'Math',
//             'center' => 'Center A',
//         ],
//     ];
// }