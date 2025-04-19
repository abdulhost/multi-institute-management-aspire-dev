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


function demoGetTeacherData() {
    return [
        'role' => 'teacher',
        'exams' => [
            ['name' => 'Math Exam', 'date' => '2025-04-15', 'class' => 'Class 1A'],
            ['name' => 'Science Exam', 'date' => '2025-04-20', 'class' => 'Class 1A'],
            ['name' => 'History Quiz', 'date' => '2025-04-25', 'class' => 'Class 2B'],
            ['name' => 'English Test', 'date' => '2025-05-01', 'class' => 'Class 3C']
        ],
        'attendance' => [
            ['date' => '2025-04-01', 'student' => 'Student 1', 'status' => 'Present'],
            ['date' => '2025-04-02', 'student' => 'Student 2', 'status' => 'Absent'],
            ['date' => '2025-04-03', 'student' => 'Student 3', 'status' => 'Present'],
            ['date' => '2025-04-04', 'student' => 'Student 4', 'status' => 'Late']
        ],
        'students' => [
            ['id' => 'ST1001', 'name' => 'John Doe', 'email' => 'john@demo-pro.edu', 'class' => 'Class 1A', 'center' => 'Center A'],
            ['id' => 'ST1002', 'name' => 'Jane Smith', 'email' => 'jane@demo-pro.edu', 'class' => 'Class 1A', 'center' => 'Center A']
        ],
        'student_attendance' => [
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-10', 'status' => 'Present'],
            ['student_id' => 'S002', 'student_name' => 'Jane Smith', 'class' => '10', 'section' => 'B', 'date' => '2025-04-10', 'status' => 'Absent'],
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-11', 'status' => 'Late'],
        ],
        'profile' => [
            'teacher_id' => 'TR1001',
            'name' => 'Alice Brown',
            'email' => 'alice@demo-pro.edu',
            'subject' => 'Math',
            'center' => 'Center A'
        ],
        'fees' => [
            ['fee_id' => 'F001', 'student_id' => 'ST1001', 'student_name' => 'John Doe', 'amount' => 150.00, 'due_date' => '2025-05-01', 'status' => 'Pending'],
            ['fee_id' => 'F002', 'student_id' => 'ST1002', 'student_name' => 'Jane Smith', 'amount' => 150.00, 'due_date' => '2025-05-01', 'status' => 'Paid']
        ],
        'notices' => [
            ['id' => 'N001', 'title' => 'Parent-Teacher Meeting', 'date' => '2025-04-20', 'content' => 'Meeting scheduled for Class 1A parents.', 'class' => 'Class 1A'],
            ['id' => 'N002', 'title' => 'Holiday Notice', 'date' => '2025-04-25', 'content' => 'School closed for national holiday.', 'class' => 'All Classes']
        ],
        'library' => [
            ['book_id' => 'B001', 'title' => 'Algebra Basics', 'author' => 'Dr. Smith', 'status' => 'Available', 'due_date' => ''],
            ['book_id' => 'B002', 'title' => 'Physics Fundamentals', 'author' => 'Prof. Jones', 'status' => 'Checked Out', 'due_date' => '2025-04-30']
        ],
        'inventory' => [
            ['item_id' => 'I001', 'name' => 'Projector', 'quantity' => 5, 'status' => 'Available'],
            ['item_id' => 'I002', 'name' => 'Lab Equipment', 'quantity' => 10, 'status' => 'In Use']
        ],
        'chats' => [
            ['chat_id' => 'C001', 'recipient' => 'John Doe', 'message' => 'Please submit your homework.', 'timestamp' => '2025-04-10 10:00'],
            ['chat_id' => 'C002', 'recipient' => 'Jane Smith', 'message' => 'Great job in class!', 'timestamp' => '2025-04-11 12:00']
        ],
        'reports' => [
            ['report_id' => 'R001', 'title' => 'Class 1A Progress', 'date' => '2025-04-15', 'summary' => 'Overall improvement in math scores.'],
            ['report_id' => 'R002', 'title' => 'Attendance Summary', 'date' => '2025-04-20', 'summary' => '80% attendance rate for Class 1A.']
        ],
        'results' => [
            ['result_id' => 'RS001', 'student_id' => 'ST1001', 'student_name' => 'John Doe', 'exam' => 'Math Exam', 'score' => 85, 'grade' => 'B+'],
            ['result_id' => 'RS002', 'student_id' => 'ST1002', 'student_name' => 'Jane Smith', 'exam' => 'Math Exam', 'score' => 92, 'grade' => 'A']
        ],
        'timetable' => [
            ['id' => 'T001', 'class' => 'Class 1A', 'subject' => 'Mathematics', 'teacher' => 'Alice Brown', 'day' => 'Monday', 'time_slot' => '08:00-09:00'],
            ['id' => 'T002', 'class' => 'Class 1A', 'subject' => 'Science', 'teacher' => 'Bob Wilson', 'day' => 'Tuesday', 'time_slot' => '09:00-10:00']
        ],
        'homework' => [
            ['id' => 'H001', 'subject' => 'Mathematics', 'class' => 'Class 1A', 'title' => 'Algebra Practice', 'due_date' => '2025-04-20', 'description' => 'Solve exercises 1-10 from Chapter 3.'],
            ['id' => 'H002', 'subject' => 'Science', 'class' => 'Class 1A', 'title' => 'Lab Report', 'due_date' => '2025-04-22', 'description' => 'Write a report on the recent experiment.']
        ],
        'class_sessions' => [
            ['session_id' => 'SES001', 'class' => 'Class 1A', 'subject' => 'Mathematics', 'date' => '2025-04-20', 'time' => '08:00-09:00', 'status' => 'Scheduled'],
            ['session_id' => 'SES002', 'class' => 'Class 1B', 'subject' => 'Science', 'date' => '2025-04-21', 'time' => '09:00-10:00', 'status' => 'Completed'],
        ],
        'subjects' => [
            ['subject_id' => 'SUB001', 'name' => 'Mathematics', 'class' => 'Class 1A', 'teacher' => 'Alice Brown', 'credits' => 4],
            ['subject_id' => 'SUB002', 'name' => 'Science', 'class' => 'Class 1B', 'teacher' => 'Bob White', 'credits' => 3],
        ],
        'parents' => [
            ['parent_id' => 'PAR001', 'name' => 'Michael Doe', 'student_name' => 'John Doe', 'email' => 'michael.doe@example.com', 'phone' => '123-456-7890'],
            ['parent_id' => 'PAR002', 'name' => 'Sarah Smith', 'student_name' => 'Jane Smith', 'email' => 'sarah.smith@example.com', 'phone' => '098-765-4321'],
        ],
    ];
}


function demoRenderTeacherAddAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Attendance</h2>
        <div class="alert alert-info">Fill in the details to add a new attendance record.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance-management'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditAttendance() {
    ob_start();
    $attendance_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Attendance</h2>
        <div class="alert alert-info">Update the attendance record for ID: <?php echo esc_html($attendance_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="S001" readonly>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="10" required>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section" value="A" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="2025-04-10" readonly>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
              
                <select class="form-control" id="status" name="status" required>
                    <option value="Present" <?php echo $record['status'] === 'Present' ? 'selected' : ''; ?>>Present</option>
                    <option value="Absent" <?php echo $record['status'] === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                    <option value="Late" <?php echo $record['status'] === 'Late' ? 'selected' : ''; ?>>Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance-management'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteAttendance() {
    ob_start();
    $attendance_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Attendance</h2>
        <div class="alert alert-success">Attendance record with ID: <?php echo esc_html($attendance_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance-management'])); ?>" class="btn btn-primary">Back to Attendance</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherFees($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Fee Tracker</h2>
        <div class="alert alert-info">Track and manage student fees.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'fees-management', 'demo-action' => 'add-fee'])); ?>" class="btn btn-primary">Add New Fee</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Fee ID</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['fees'])): ?>
                        <?php foreach ($data['fees'] as $fee): ?>
                            <tr>
                                <td><?php echo esc_html($fee['fee_id']); ?></td>
                                <td><?php echo esc_html($fee['student_id']); ?></td>
                                <td><?php echo esc_html($fee['student_name']); ?></td>
                                <td><?php echo esc_html($fee['amount']); ?></td>
                                <td><?php echo esc_html($fee['due_date']); ?></td>
                                <td><?php echo esc_html($fee['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'fees-management', 'demo-action' => 'edit-fee', 'id' => $fee['fee_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'fees-management', 'demo-action' => 'delete-fee', 'id' => $fee['fee_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this fee?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No fees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddFee() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Fee</h2>
        <div class="alert alert-info">Fill in the details to add a new fee.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Pending">Pending</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Fee</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'fees-management'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditFee() {
    ob_start();
    $fee_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Fee</h2>
        <div class="alert alert-info">Update the fee record for ID: <?php echo esc_html($fee_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="ST1001" readonly>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" step="0.01" value="150.00" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="2025-05-01" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Pending" selected>Pending</option>
                    <option value="Paid">Paid</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Fee</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'fees-management'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteFee() {
    ob_start();
    $fee_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Fee</h2>
        <div class="alert alert-success">Fee record with ID: <?php echo esc_html($fee_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'fees-management'])); ?>" class="btn btn-primary">Back to Fees</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherNotices($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Notice Board</h2>
        <div class="alert alert-info">Manage notices for your classes.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'notices', 'demo-action' => 'add-notice'])); ?>" class="btn btn-primary">Add New Notice</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['notices'])): ?>
                        <?php foreach ($data['notices'] as $notice): ?>
                            <tr>
                                <td><?php echo esc_html($notice['id']); ?></td>
                                <td><?php echo esc_html($notice['title']); ?></td>
                                <td><?php echo esc_html($notice['date']); ?></td>
                                <td><?php echo esc_html($notice['class']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'notices', 'demo-action' => 'edit-notice', 'id' => $notice['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'notices', 'demo-action' => 'delete-notice', 'id' => $notice['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this notice?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No notices found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddNotice() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Notice</h2>
        <div class="alert alert-info">Fill in the details to add a new notice.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Notice</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'notices'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditNotice() {
    ob_start();
    $notice_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Notice</h2>
        <div class="alert alert-info">Update the notice with ID: <?php echo esc_html($notice_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="Parent-Teacher Meeting" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="2025-04-20" required>
            </div>
            <div class="mb-3">
                <label for="content" class="form-label">Content</label>
                <textarea class="form-control" id="content" name="content" rows="5" required>Meeting scheduled for Class 1A parents.</textarea>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Class 1A" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Notice</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'notices'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteNotice() {
    ob_start();
    $notice_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Notice</h2>
        <div class="alert alert-success">Notice with ID: <?php echo esc_html($notice_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'notices'])); ?>" class="btn btn-primary">Back to Notices</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherLibrary($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Library Hub</h2>
        <div class="alert alert-info">Manage library books.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'library-management', 'demo-action' => 'add-book'])); ?>" class="btn btn-primary">Add New Book</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Book ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['library'])): ?>
                        <?php foreach ($data['library'] as $book): ?>
                            <tr>
                                <td><?php echo esc_html($book['book_id']); ?></td>
                                <td><?php echo esc_html($book['title']); ?></td>
                                <td><?php echo esc_html($book['author']); ?></td>
                                <td><?php echo esc_html($book['status']); ?></td>
                                <td><?php echo esc_html($book['due_date']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'library-management', 'demo-action' => 'edit-book', 'id' => $book['book_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'library-management', 'demo-action' => 'delete-book', 'id' => $book['book_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No books found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddBook() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Book</h2>
        <div class="alert alert-info">Fill in the details to add a new book.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="book_id" class="form-label">Book ID</label>
                <input type="text" class="form-control" id="book_id" name="book_id" required>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" class="form-control" id="author" name="author" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Available">Available</option>
                    <option value="Checked Out">Checked Out</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date">
            </div>
            <button type="submit" class="btn btn-primary">Add Book</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'library-management'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditBook() {
    ob_start();
    $book_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Book</h2>
        <div class="alert alert-info">Update the book with ID: <?php echo esc_html($book_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="book_id" class="form-label">Book ID</label>
                <input type="text" class="form-control" id="book_id" name="book_id" value="B001" readonly>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="Algebra Basics" required>
            </div>
            <div class="mb-3">
                <label for="author" class="form-label">Author</label>
                <input type="text" class="form-control" id="author" name="author" value="Dr. Smith" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Available" selected>Available</option>
                    <option value="Checked Out">Checked Out</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="">
            </div>
            <button type="submit" class="btn btn-primary">Update Book</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'library-management'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteBook() {
    ob_start();
    $book_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Book</h2>
        <div class="alert alert-success">Book with ID: <?php echo esc_html($book_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'library-management'])); ?>" class="btn btn-primary">Back to Library</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherInventory($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Inventory Control</h2>
        <div class="alert alert-info">Manage classroom inventory items.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'inventory-management', 'demo-action' => 'add-item'])); ?>" class="btn btn-primary">Add New Item</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Item ID</th>
                        <th>Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['inventory'])): ?>
                        <?php foreach ($data['inventory'] as $item): ?>
                            <tr>
                                <td><?php echo esc_html($item['item_id']); ?></td>
                                <td><?php echo esc_html($item['name']); ?></td>
                                <td><?php echo esc_html($item['quantity']); ?></td>
                                <td><?php echo esc_html($item['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'inventory-management', 'demo-action' => 'edit-item', 'id' => $item['item_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'inventory-management', 'demo-action' => 'delete-item', 'id' => $item['item_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No inventory items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddItem() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Item</h2>
        <div class="alert alert-info">Fill in the details to add a new inventory item.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="item_id" class="form-label">Item ID</label>
                <input type="text" class="form-control" id="item_id" name="item_id" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Available">Available</option>
                    <option value="In Use">In Use</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Item</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'inventory-management'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditItem() {
    ob_start();
    $item_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Item</h2>
        <div class="alert alert-info">Update the inventory item with ID: <?php echo esc_html($item_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="item_id" class="form-label">Item ID</label>
                <input type="text" class="form-control" id="item_id" name="item_id" value="I001" readonly>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="Projector" required>
            </div>
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="5" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Available" selected>Available</option>
                    <option value="In Use">In Use</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Item</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'inventory-management'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteItem() {
    ob_start();
    $item_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Item</h2>
        <div class="alert alert-success">Inventory item with ID: <?php echo esc_html($item_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'inventory-management'])); ?>" class="btn btn-primary">Back to Inventory</a>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render Teacher Chats
 * Updated to match superadmin chat structure with conversation sidebar and message area
 */
function demoRenderTeacherChats($data) {
    // Hardcoded conversation data for teacher
    $conversations = [
        ['id' => 'CONV001', 'recipient_id' => 'S001', 'recipient_name' => 'John Doe', 'last_message' => 'Can you clarify the homework?', 'last_message_time' => '2025-04-19 09:15', 'unread' => 1],
        ['id' => 'CONV002', 'recipient_id' => 'P001', 'recipient_name' => 'Mary Brown', 'last_message' => 'Can we discuss my child’s progress?', 'last_message_time' => '2025-04-18 14:30', 'unread' => 0],
    ];

    // Hardcoded chats for the first conversation (CONV001)
    $chats = [
        ['sender' => 'S001', 'sender_name' => 'John Doe', 'content' => 'Can you clarify the homework?', 'time' => '2025-04-19 09:15', 'status' => 'received'],
        ['sender' => 'T001', 'sender_name' => 'Ms. Alice Johnson', 'content' => 'Sure, it’s about chapter 5.', 'time' => '2025-04-19 09:20', 'status' => 'sent'],
        ['sender' => 'S001', 'sender_name' => 'John Doe', 'content' => 'Thanks! I’ll review it.', 'time' => '2025-04-19 09:25', 'status' => 'received'],
    ];

    ob_start();
    ?>
    <div class="dashboard-section chat-container" style="padding: 20px; display: flex; flex-direction: column; height: 100%;">
        <h2 style="color: #4a90e2;">Chat Room</h2>
        <div class="chat-wrapper" style="display: flex; flex: 1; overflow: hidden;">
            <!-- Sidebar -->
            <div class="chat-sidebar" style="width: 300px; border-right: 1px solid #ddd; overflow-y: auto;">
                <div class="sidebar-header" style="padding: 10px; border-bottom: 1px solid #ddd;">
                    <h4>Conversations</h4>
                    <input type="text" id="conversation-search" class="form-control" placeholder="Search conversations..." style="width: 100%; padding: 8px; border: 1px solid #4a90e2; border-radius: 5px;">
                </div>
                <ul class="conversation-list" style="list-style: none; padding: 0;">
                    <?php foreach ($conversations as $index => $conv): ?>
                        <li class="conversation-item <?php echo $index === 0 ? 'active' : ''; ?>" data-conv-id="<?php echo esc_attr($conv['id']); ?>" style="padding: 10px; cursor: pointer; <?php echo $index === 0 ? 'background: #f4f6f9;' : ''; ?>">
                            <strong><?php echo esc_html($conv['recipient_name']); ?> (<?php echo esc_html($conv['recipient_id']); ?>)</strong>
                            <p style="margin: 5px 0; color: #555;"><?php echo esc_html($conv['last_message']); ?></p>
                            <span class="meta" style="font-size: 0.8em; color: #999;"><?php echo esc_html($conv['last_message_time']); ?></span>
                            <?php if ($conv['unread'] > 0): ?>
                                <span class="badge bg-primary" style="background: #4a90e2; color: #fff; padding: 2px 6px; border-radius: 10px;"><?php echo esc_html($conv['unread']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- Main Chat Area -->
            <div class="chat-main" style="flex: 1; display: flex; flex-direction: column; padding: 10px;">
                <div class="chat-header" style="padding: 10px; border-bottom: 1px solid #ddd;">
                    <h4>Conversation with <?php echo esc_html($conversations[0]['recipient_name']); ?> (<?php echo esc_html($conversations[0]['recipient_id']); ?>)</h4>
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'chats', 'demo-action' => 'send-chat'])); ?>" class="btn btn-primary" style="background: #4a90e2; color: #fff; padding: 8px 15px; border-radius: 5px;">New Message</a>
                </div>
                <div class="chat-messages" id="chat-messages" style="flex: 1; overflow-y: auto; padding: 10px;">
                    <?php foreach ($chats as $msg): ?>
                        <div class="chat-message <?php echo $msg['status']; ?>" style="margin: 5px; padding: 10px; max-width: 70%; <?php echo $msg['status'] === 'sent' ? 'background: #4a90e2; color: #fff; align-self: flex-end; border-radius: 10px 10px 0 10px;' : 'background: #e8eaed; color: #000; align-self: flex-start; border-radius: 10px 10px 10px 0;'; ?>">
                            <div class="bubble"><?php echo esc_html($msg['content']); ?></div>
                            <div class="meta" style="font-size: 0.8em; color: <?php echo $msg['status'] === 'sent' ? '#e8eaed' : '#555'; ?>;"><?php echo esc_html($msg['sender_name']); ?> • <?php echo esc_html($msg['time']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form class="chat-form" id="chat-form" style="padding: 10px; border-top: 1px solid #ddd;">
                    <div class="d-flex align-items-center">
                        <textarea class="form-control" id="chat-input" rows="2" placeholder="Type a message..." style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px; margin-right: 10px;"></textarea>
                        <button type="submit" class="btn btn-primary" style="background: #4a90e2; color: #fff; padding: 10px 15px; border-radius: 5px;"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
                <div class="chat-loading" id="chat-loading" style="display: none; text-align: center; padding: 10px;">Loading...</div>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            // Conversation search
            $('#conversation-search').on('input', function() {
                const query = $(this).val().toLowerCase();
                $('.conversation-item').each(function() {
                    const name = $(this).find('strong').text().toLowerCase();
                    const message = $(this).find('p').text().toLowerCase();
                    $(this).toggle(name.includes(query) || message.includes(query));
                });
            });

            // Conversation selection
            $('.conversation-item').on('click', function() {
                $('.conversation-item').removeClass('active');
                $(this).addClass('active');
                const convId = $(this).data('conv-id');
                $('#chat-messages').html('<div class="chat-loading active" style="text-align: center; padding: 10px;">Loading...</div>');
                // Simulate loading chats (hardcoded)
                setTimeout(() => {
                    $('#chat-messages').html(`
                        <div class="chat-message received" style="margin: 5px; padding: 10px; max-width: 70%; background: #e8eaed; color: #000; align-self: flex-start; border-radius: 10px 10px 10px 0;">
                            <div class="bubble">Can you clarify the homework?</div>
                            <div class="meta" style="font-size: 0.8em; color: #555;">John Doe • 2025-04-19 09:15</div>
                        </div>
                        <div class="chat-message sent" style="margin: 5px; padding: 10px; max-width: 70%; background: #4a90e2; color: #fff; align-self: flex-end; border-radius: 10px 10px 0 10px;">
                            <div class="bubble">Sure, it’s about chapter 5.</div>
                            <div class="meta" style="font-size: 0.8em; color: #e8eaed;">Ms. Alice Johnson • 2025-04-19 09:20</div>
                        </div>
                        <div class="chat-message received" style="margin: 5px; padding: 10px; max-width: 70%; background: #e8eaed; color: #000; align-self: flex-start; border-radius: 10px 10px 10px 0;">
                            <div class="bubble">Thanks! I’ll review it.</div>
                            <div class="meta" style="font-size: 0.8em; color: #555;">John Doe • 2025-04-19 09:25</div>
                        </div>
                    `);
                    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                }, 500);
            });

            // Send chat
            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                const message = $('#chat-input').val().trim();
                if (message) {
                    $('#chat-messages').append(`
                        <div class="chat-message sent" style="margin: 5px; padding: 10px; max-width: 70%; background: #4a90e2; color: #fff; align-self: flex-end; border-radius: 10px 10px 0 10px;">
                            <div class="bubble">${message}</div>
                            <div class="meta" style="font-size: 0.8em; color: #e8eaed;">Ms. Alice Johnson • ${new Date().toLocaleString()}</div>
                        </div>
                    `);
                    $('#chat-input').val('');
                    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render Teacher Send Chat
 * Updated to match superadmin new conversation structure
 */
function demoRenderTeacherSendChat() {
    // Hardcoded recipient data for teacher (students and parents)
    $recipients = [
        'students' => [
            ['id' => 'S001', 'name' => 'John Doe'],
            ['id' => 'S002', 'name' => 'Jane Smith'],
        ],
        'parents' => [
            ['id' => 'P001', 'name' => 'Mary Brown'],
            ['id' => 'P002', 'name' => 'James Wilson'],
        ],
    ];

    ob_start();
    ?>
    <div class="dashboard-section chat-container" style="padding: 20px;">
        <h2 style="color: #4a90e2;">Send New Message</h2>
        <form id="new-chat-form" class="edu-form" style="max-width: 600px;">
            <div class="edu-form-group" style="margin-bottom: 15px;">
                <label class="edu-form-label" for="recipient" style="display: block; margin-bottom: 5px;">Recipient</label>
                <select id="recipient" class="edu-form-input" style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px;" required>
                    <option value="">Select a recipient</option>
                    <optgroup label="Students">
                        <?php foreach ($recipients['students'] as $student): ?>
                            <option value="<?php echo esc_attr($student['id']); ?>">
                                <?php echo esc_html($student['name'] . ' (' . $student['id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Parents">
                        <?php foreach ($recipients['parents'] as $parent): ?>
                            <option value="<?php echo esc_attr($parent['id']); ?>">
                                <?php echo esc_html($parent['name'] . ' (' . $parent['id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            <div class="edu-form-group" style="margin-bottom: 15px;">
                <label class="edu-form-label" for="subject" style="display: block; margin-bottom: 5px;">Subject</label>
                <input type="text" id="subject" class="edu-form-input" placeholder="e.g., Homework Feedback" style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px;" required>
            </div>
            <div class="edu-form-group" style="margin-bottom: 15px;">
                <label class="edu-form-label" for="chat" style="display: block; margin-bottom: 5px;">Message</label>
                <textarea id="chat" class="edu-form-input" rows="5" placeholder="Type your message..." style="width: 100%; padding: 10px; border: 1px solid #4a90e2; border-radius: 5px;" required></textarea>
            </div>
            <div class="edu-form-actions" style="display: flex; gap: 10px;">
                <button type="submit" class="edu-button edu-button-primary" style="background: #4a90e2; color: #fff; padding: 10px 20px; border-radius: 5px; border: none;">Send Message</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'chats'])); ?>" class="edu-button edu-button-secondary" style="background: #6c757d; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none;">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message" style="margin-top: 10px;"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#new-chat-form').on('submit', function(e) {
                e.preventDefault();
                const recipient = $('#recipient').val();
                const subject = $('#subject').val().trim();
                const chat = $('#chat').val().trim();
                if (recipient && subject && chat) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Message sent successfully!').css('color', '#4a90e2');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'chats'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.').css('color', '#dc3545');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}


function demoRenderTeacherReports($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Insight Reports</h2>
        <div class="alert alert-info">Manage class and student reports.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'reports', 'demo-action' => 'add-report'])); ?>" class="btn btn-primary">Add New Report</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Report ID</th>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Summary</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['reports'])): ?>
                        <?php foreach ($data['reports'] as $report): ?>
                            <tr>
                                <td><?php echo esc_html($report['report_id']); ?></td>
                                <td><?php echo esc_html($report['title']); ?></td>
                                <td><?php echo esc_html($report['date']); ?></td>
                                <td><?php echo esc_html($report['summary']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'reports', 'demo-action' => 'edit-report', 'id' => $report['report_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'reports', 'demo-action' => 'delete-report', 'id' => $report['report_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this report?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No reports found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddReport() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Report</h2>
        <div class="alert alert-info">Fill in the details to add a new report.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="summary" class="form-label">Summary</label>
                <textarea class="form-control" id="summary" name="summary" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Report</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'reports'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditReport() {
    ob_start();
    $report_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Report</h2>
        <div class="alert alert-info">Update the report with ID: <?php echo esc_html($report_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="Class 1A Progress" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="2025-04-15" required>
            </div>
            <div class="mb-3">
                <label for="summary" class="form-label">Summary</label>
                <textarea class="form-control" id="summary" name="summary" rows="5" required>Overall improvement in math scores.</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Report</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'reports'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteReport() {
    ob_start();
    $report_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Report</h2>
        <div class="alert alert-success">Report with ID: <?php echo esc_html($report_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'reports'])); ?>" class="btn btn-primary">Back to Reports</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherResults($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Exam Results</h2>
        <div class="alert alert-info">Manage student exam results.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'results', 'demo-action' => 'add-result'])); ?>" class="btn btn-primary">Add New Result</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Result ID</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Exam</th>
                        <th>Score</th>
                        <th>Grade</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['results'])): ?>
                        <?php foreach ($data['results'] as $result): ?>
                            <tr>
                                <td><?php echo esc_html($result['result_id']); ?></td>
                                <td><?php echo esc_html($result['student_id']); ?></td>
                                <td><?php echo esc_html($result['student_name']); ?></td>
                                <td><?php echo esc_html($result['exam']); ?></td>
                                <td><?php echo esc_html($result['score']); ?></td>
                                <td><?php echo esc_html($result['grade']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'results', 'demo-action' => 'edit-result', 'id' => $result['result_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'results', 'demo-action' => 'delete-result', 'id' => $result['result_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this result?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No results found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddResult() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Result</h2>
        <div class="alert alert-info">Fill in the details to add a new exam result.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="exam" class="form-label">Exam</label>
                <input type="text" class="form-control" id="exam" name="exam" required>
            </div>
            <div class="mb-3">
                <label for="score" class="form-label">Score</label>
                <input type="number" class="form-control" id="score" name="score" required>
            </div>
            <div class="mb-3">
                <label for="grade" class="form-label">Grade</label>
                <input type="text" class="form-control" id="grade" name="grade" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Result</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'results'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditResult() {
    ob_start();
    $result_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Result</h2>
        <div class="alert alert-info">Update the result with ID: <?php echo esc_html($result_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="ST1001" readonly>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="exam" class="form-label">Exam</label>
                <input type="text" class="form-control" id="exam" name="exam" value="Math Exam" required>
            </div>
            <div class="mb-3">
                <label for="score" class="form-label">Score</label>
                <input type="number" class="form-control" id="score" name="score" value="85" required>
            </div>
            <div class="mb-3">
                <label for="grade" class="form-label">Grade</label>
                <input type="text" class="form-control" id="grade" name="grade" value="B+" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Result</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'results'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteResult() {
    ob_start();
    $result_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Result</h2>
        <div class="alert alert-success">Result with ID: <?php echo esc_html($result_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'results'])); ?>" class="btn btn-primary">Back to Results</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherTimetable($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Class Schedule</h2>
        <div class="alert alert-info">Manage your class timetable.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'timetable', 'demo-action' => 'add-timetable'])); ?>" class="btn btn-primary">Add New Timetable Entry</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Teacher</th>
                        <th>Day</th>
                        <th>Time Slot</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['timetable'])): ?>
                        <?php foreach ($data['timetable'] as $entry): ?>
                            <tr>
                                <td><?php echo esc_html($entry['id']); ?></td>
                                <td><?php echo esc_html($entry['class']); ?></td>
                                <td><?php echo esc_html($entry['subject']); ?></td>
                                <td><?php echo esc_html($entry['teacher']); ?></td>
                                <td><?php echo esc_html($entry['day']); ?></td>
                                <td><?php echo esc_html($entry['time_slot']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'timetable', 'demo-action' => 'edit-timetable', 'id' => $entry['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'timetable', 'demo-action' => 'delete-timetable', 'id' => $entry['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this timetable entry?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No timetable entries found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddTimetable() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Timetable Entry</h2>
        <div class="alert alert-info">Fill in the details to add a new timetable entry.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="teacher" class="form-label">Teacher</label>
                <input type="text" class="form-control" id="teacher" name="teacher" required>
            </div>
            <div class="mb-3">
                <label for="day" class="form-label">Day</label>
                <select class="form-control" id="day" name="day" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="time_slot" class="form-label">Time Slot</label>
                <input type="text" class="form-control" id="time_slot" name="time_slot" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Timetable Entry</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'timetable'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditTimetable() {
    ob_start();
    $timetable_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Timetable Entry</h2>
        <div class="alert alert-info">Update the timetable entry with ID: <?php echo esc_html($timetable_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Class 1A" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="Mathematics" required>
            </div>
            <div class="mb-3">
                <label for="teacher" class="form-label">Teacher</label>
                <input type="text" class="form-control" id="teacher" name="teacher" value="Alice Brown" required>
            </div>
            <div class="mb-3">
                <label for="day" class="form-label">Day</label>
                <select class="form-control" id="day" name="day" required>
                    <option value="Monday" selected>Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="time_slot" class="form-label">Time Slot</label>
                <input type="text" class="form-control" id="time_slot" name="time_slot" value="08:00-09:00" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Timetable Entry</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'timetable'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteTimetable() {
    ob_start();
    $timetable_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Timetable Entry</h2>
        <div class="alert alert-success">Timetable entry with ID: <?php echo esc_html($timetable_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'timetable'])); ?>" class="btn btn-primary">Back to Timetable</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherHomework($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Assignments</h2>
        <div class="alert alert-info">Manage homework assignments for your classes.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'add-homework'])); ?>" class="btn btn-primary">Add New Assignment</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Title</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['homework'])): ?>
                        <?php foreach ($data['homework'] as $homework): ?>
                            <tr>
                                <td><?php echo esc_html($homework['id']); ?></td>
                                <td><?php echo esc_html($homework['subject']); ?></td>
                                <td><?php echo esc_html($homework['class']); ?></td>
                                <td><?php echo esc_html($homework['title']); ?></td>
                                <td><?php echo esc_html($homework['due_date']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'edit-homework', 'id' => $homework['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'delete-homework', 'id' => $homework['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this homework?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No homework assignments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddHomework() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Assignment</h2>
        <div class="alert alert-info">Fill in the details to add a new homework assignment.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Assignment</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditHomework() {
    ob_start();
    $homework_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Assignment</h2>
        <div class="alert alert-info">Update the homework assignment with ID: <?php echo esc_html($homework_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="Mathematics" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Class 1A" required>
            </div>
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="Algebra Practice" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="2025-04-20" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="5" required>Solve exercises 1-10 from Chapter 3.</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Assignment</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteHomework() {
    ob_start();
    $homework_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Assignment</h2>
        <div class="alert alert-success">Homework assignment with ID: <?php echo esc_html($homework_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework'])); ?>" class="btn btn-primary">Back to Assignments</a>
    </div>
    <?php
    return ob_get_clean();
}



function demoRenderTeacherClassSessions($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Class Planner</h2>
        <div class="alert alert-info">Schedule and manage your class sessions.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'class-sessions', 'demo-action' => 'add-session'])); ?>" class="btn btn-primary">Schedule New Session</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Session ID</th>
                        <th>Class</th>
                        <th>Subject</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['class_sessions'])): ?>
                        <?php foreach ($data['class_sessions'] as $session): ?>
                            <tr>
                                <td><?php echo esc_html($session['session_id']); ?></td>
                                <td><?php echo esc_html($session['class']); ?></td>
                                <td><?php echo esc_html($session['subject']); ?></td>
                                <td><?php echo esc_html($session['date']); ?></td>
                                <td><?php echo esc_html($session['time']); ?></td>
                                <td><?php echo esc_html($session['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'class-sessions', 'demo-action' => 'edit-session', 'id' => $session['session_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'class-sessions', 'demo-action' => 'delete-session', 'id' => $session['session_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this session?');">Cancel</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No class sessions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddSession() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Schedule New Session</h2>
        <div class="alert alert-info">Fill in the details to schedule a new class session.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="session_id" class="form-label">Session ID</label>
                <input type="text" class="form-control" id="session_id" name="session_id" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="time" class="form-label">Time</label>
                <input type="text" class="form-control" id="time" name="time" placeholder="e.g., 08:00-09:00" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Schedule Session</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'class-sessions'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditSession() {
    ob_start();
    $session_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Session</h2>
        <div class="alert alert-info">Update the class session with ID: <?php echo esc_html($session_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="session_id" class="form-label">Session ID</label>
                <input type="text" class="form-control" id="session_id" name="session_id" value="SES001" readonly>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Class 1A" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="Mathematics" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="2025-04-20" required>
            </div>
            <div class="mb-3">
                <label for="time" class="form-label">Time</label>
                <input type="text" class="form-control" id="time" name="time" value="08:00-09:00" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Scheduled" selected>Scheduled</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Session</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'class-sessions'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteSession() {
    ob_start();
    $session_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Cancel Session</h2>
        <div class="alert alert-success">Class session with ID: <?php echo esc_html($session_id); ?> has been cancelled.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'class-sessions'])); ?>" class="btn btn-primary">Back to Class Planner</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherSubjects($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Subject Manager</h2>
        <div class="alert alert-info">Manage subjects for your classes.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'subjects', 'demo-action' => 'add-subject'])); ?>" class="btn btn-primary">Add New Subject</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Subject ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Teacher</th>
                        <th>Credits</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['subjects'])): ?>
                        <?php foreach ($data['subjects'] as $subject): ?>
                            <tr>
                                <td><?php echo esc_html($subject['subject_id']); ?></td>
                                <td><?php echo esc_html($subject['name']); ?></td>
                                <td><?php echo esc_html($subject['class']); ?></td>
                                <td><?php echo esc_html($subject['teacher']); ?></td>
                                <td><?php echo esc_html($subject['credits']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'subjects', 'demo-action' => 'edit-subject', 'id' => $subject['subject_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'subjects', 'demo-action' => 'delete-subject', 'id' => $subject['subject_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No subjects found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddSubject() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Subject</h2>
        <div class="alert alert-info">Fill in the details to add a new subject.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject_id" class="form-label">Subject ID</label>
                <input type="text" class="form-control" id="subject_id" name="subject_id" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Subject Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="teacher" class="form-label">Teacher</label>
                <input type="text" class="form-control" id="teacher" name="teacher" required>
            </div>
            <div class="mb-3">
                <label for="credits" class="form-label">Credits</label>
                <input type="number" class="form-control" id="credits" name="credits" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Subject</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'subjects'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditSubject() {
    ob_start();
    $subject_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Subject</h2>
        <div class="alert alert-info">Update the subject with ID: <?php echo esc_html($subject_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject_id" class="form-label">Subject ID</label>
                <input type="text" class="form-control" id="subject_id" name="subject_id" value="SUB001" readonly>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Subject Name</label>
                <input type="text" class="form-control" id="name" name="name" value="Mathematics" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Class 1A" required>
            </div>
            <div class="mb-3">
                <label for="teacher" class="form-label">Teacher</label>
                <input type="text" class="form-control" id="teacher" name="teacher" value="Alice Brown" required>
            </div>
            <div class="mb-3">
                <label for="credits" class="form-label">Credits</label>
                <input type="number" class="form-control" id="credits" name="credits" value="4" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Subject</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'subjects'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteSubject() {
    ob_start();
    $subject_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Subject</h2>
        <div class="alert alert-success">Subject with ID: <?php echo esc_html($subject_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'subjects'])); ?>" class="btn btn-primary">Back to Subject Manager</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherParents($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Parent Connect</h2>
        <div class="alert alert-info">Manage parent contact information.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'parents', 'demo-action' => 'add-parent'])); ?>" class="btn btn-primary">Add New Parent</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Parent ID</th>
                        <th>Name</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['parents'])): ?>
                        <?php foreach ($data['parents'] as $parent): ?>
                            <tr>
                                <td><?php echo esc_html($parent['parent_id']); ?></td>
                                <td><?php echo esc_html($parent['name']); ?></td>
                                <td><?php echo esc_html($parent['student_name']); ?></td>
                                <td><?php echo esc_html($parent['email']); ?></td>
                                <td><?php echo esc_html($parent['phone']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'parents', 'demo-action' => 'edit-parent', 'id' => $parent['parent_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'parents', 'demo-action' => 'delete-parent', 'id' => $parent['parent_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this parent contact?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No parent contacts found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddParent() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Parent</h2>
        <div class="alert alert-info">Fill in the details to add a new parent contact.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="parent_id" class="form-label">Parent ID</label>
                <input type="text" class="form-control" id="parent_id" name="parent_id" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Parent Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Parent</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'parents'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditParent() {
    ob_start();
    $parent_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Parent</h2>
        <div class="alert alert-info">Update the parent contact with ID: <?php echo esc_html($parent_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="parent_id" class="form-label">Parent ID</label>
                <input type="text" class="form-control" id="parent_id" name="parent_id" value="PAR001" readonly>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Parent Name</label>
                <input type="text" class="form-control" id="name" name="name" value="Michael Doe" required>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="michael.doe@example.com" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="123-456-7890" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Parent</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'parents'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteParent() {
    ob_start();
    $parent_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Parent</h2>
        <div class="alert alert-success">Parent contact with ID: <?php echo esc_html($parent_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'parents'])); ?>" class="btn btn-primary">Back to Parent Connect</a>
    </div>
    <?php
    return ob_get_clean();
}
