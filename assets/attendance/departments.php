<?php
if (!defined('ABSPATH')) {
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options; // Add this line to import the Options class
function aspire_multiple_departments_dashboard_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $table_name = $wpdb->prefix . 'departments';

    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'departments';

    // Handle Bulk Delete
    if (isset($_POST['bulk_delete']) && wp_verify_nonce($_POST['nonce'], 'bulk_department_nonce') && !empty($_POST['department_ids'])) {
        $department_ids = array_map('intval', $_POST['department_ids']);
        $wpdb->query("DELETE FROM $table_name WHERE department_id IN (" . implode(',', $department_ids) . ") AND education_center_id = '$education_center_id'");
        echo '<div class="alert alert-success">Selected departments deleted successfully!</div>';
    }

    // Handle Single Delete
    if ($section === 'delete-department' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['department_id'])) {
        $department_id = intval($_GET['department_id']);
        $wpdb->delete($table_name, ['department_id' => $department_id, 'education_center_id' => $education_center_id], ['%d', '%s']);
    }

    // Generate PDF
    if ($section === 'departments' && isset($_GET['action']) && $_GET['action'] === 'generate_pdf') {
        $dompdf_path = realpath(dirname(__FILE__) . '/../exam/dompdf/autoload.inc.php');
        if (!file_exists($dompdf_path)) {
            wp_die('Dompdf autoload file not found at: ' . $dompdf_path);
        }
        require_once $dompdf_path;

        $educational_center_id = get_educational_center_data();
        $output = '';
        $departments = get_departments_with_teacher_count($educational_center_id); // Use the helper function to get teacher data

        if (empty($departments)) {
            $output .= '<div class="alert alert-danger" data-auto-hide="3000">No departments found to generate PDF.</div>';
        } else {
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
                    @page { 
                        margin: 15mm; 
                        border: 2px solid #007bff;
                        padding: 10mm;
                    }
                    body { 
                        font-family: Helvetica, sans-serif; 
                        font-size: 12pt; 
                        color: #333; 
                        line-height: 1.4;
                    }
                    .container {
                        width: 100%;
                        padding: 15px;
                        background-color: #fff;
                    }
                    .header {
                        text-align: center;
                        padding-bottom: 15px;
                        border-bottom: 2px solid #007bff;
                        margin-bottom: 20px;
                    }
                    .header h1 {
                        font-size: 24pt;
                        color: #007bff;
                        margin: 0;
                        text-transform: uppercase;
                    }
                    .header .subtitle {
                        font-size: 14pt;
                        color: #666;
                        margin: 5px 0;
                    }
                    .summary-section, .details-section {
                        margin: 20px 0;
                    }
                    .summary-section h2, .details-section h2 {
                        font-size: 18pt;
                        color: #007bff;
                        margin-bottom: 15px;
                        border-bottom: 1px solid #ddd;
                        padding-bottom: 5px;
                    }
                    .details-section h3 {
                        font-size: 16pt;
                        color: #007bff;
                        margin: 15px 0 10px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f8f9fa;
                        color: #007bff;
                        font-weight: bold;
                    }
                    tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .teacher-list {
                        margin-left: 20px;
                    }
                    .teacher-list ul {
                        list-style-type: disc;
                        padding-left: 20px;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 30px;
                        font-size: 10pt;
                        color: #666;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>Department & Teacher Report</h1>
                        <div class="subtitle">Educational Center Comprehensive Report</div>
                    </div>

                    <!-- Summary Section -->
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

                    <!-- Detailed Teacher Section -->
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
                                $teacher_id = esc_html($teacher->ID); // Assuming teacher ID is stored in $teacher->ID
                                $html .= '<li style="list-style: none;">' . $teacher_id . ' | ' . $teacher_name . '</li>';
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

            try {
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $pdf_content = $dompdf->output();

                while (ob_get_level()) {
                    ob_end_clean();
                }

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="departments_teachers_report_' . date('Ymd_His') . '.pdf"');
                header('Content-Length: ' . strlen($pdf_content));
                header('Cache-Control: no-cache');

                echo $pdf_content;
                flush();
                exit;
            } catch (Exception $e) {
                error_log('Dompdf Error: ' . $e->getMessage());
                $output .= '<div class="alert alert-danger" data-auto-hide="3000">Error generating PDF: ' . esc_html($e->getMessage()) . '</div>';
            }
        }
    }

    ob_start();
    ?>
    <div class="container-fluid">
        <div class="row">
            <?php 
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
                'relation' => 'AND', // Ensure both conditions must match
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

// List Departments
function departments_list_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $departments = get_departments_with_teacher_count($education_center_id);

    ob_start();
    ?>
    <div class="card mb-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title">Department Overview</h3>
            <a href="?section=departments&action=generate_pdf" class="btn btn-light">Export as PDF</a>
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
            <form method="POST">
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
                                    echo '<td>' . $dept->teacher_count . ' <a href="#" data-bs-toggle="modal" data-bs-target="#teacherModal' . $index . '" class="text-muted"><small>(View)</small></a></td>';
                                    echo '<td>
                                        <a href="?section=edit-department&department_id=' . $dept->department_id . '" class="btn btn-sm btn-info">Edit</a>
                                        <a href="?section=delete-department&action=delete&department_id=' . $dept->department_id . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\');">Delete</a>
                                    </td>';
                                    echo '</tr>';
                                    // Modal for Teacher Details
                                    echo '<div class="modal fade" id="teacherModal' . $index . '" tabindex="-1" aria-labelledby="teacherModalLabel' . $index . '" aria-hidden="true">';
                                    echo '<div class="modal-dialog">';
                                    echo '<div class="modal-content">';
                                    echo '<div class="modal-header">';
                                    echo '<h5 class="modal-title" id="teacherModalLabel' . $index . '">Teachers in ' . esc_html($dept->department_name) . '</h5>';
                                    echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                                    echo '</div>';
                                    echo '<div class="modal-body">';
                                    if ($dept->teacher_count > 0) {
                                        echo '<ul class="list-group">';
                                        foreach ($dept->teachers as $teacher) {
                                            echo '<li class="list-group-item">' . esc_html($teacher->post_title) .' | '.esc_html($teacher->teacher_name)  . '</li>';
                                        }
                                        echo '</ul>';
                                    } else {
                                        echo '<p>No teachers assigned.</p>';
                                    }
                                    echo '</div>';
                                    echo '<div class="modal-footer">';
                                    echo '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <?php wp_nonce_field('bulk_department_nonce', 'nonce'); ?>
                <button type="submit" name="bulk_delete" class="btn btn-danger mt-3" onclick="return confirm('Are you sure you want to delete selected departments?');">Delete Selected</button>
                <a href="?section=add-department" class="btn btn-primary mt-3">Add New Department</a>
            </form>
        </div>
    </div>
    <script>
        function filterDepartments() {
            let input = document.getElementById('departmentSearch').value.toLowerCase();
            let table = document.getElementById('departmentTable');
            let tr = table.getElementsByTagName('tr');
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName('td')[2]; // Department Name column
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toLowerCase().indexOf(input) > -1 ? '' : 'none';
                }
            }
        }
        function toggleSelectAll() {
            let checkboxes = document.getElementsByName('department_ids[]');
            let selectAll = document.getElementById('selectAll');
            for (let checkbox of checkboxes) {
                checkbox.checked = selectAll.checked;
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

// Add Department
function add_department_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
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
            <form method="POST">
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
    <?php
    return ob_get_clean();
}

// Edit Department
function edit_department_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $table_name = $wpdb->prefix . 'departments';
    $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

    if ($department_id > 0) {
        $department = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE department_id = %d AND education_center_id = %s",
            $department_id, $education_center_id
        ));

        if (!$department) {
            return '<div class="alert alert-danger">Department not found.</div>';
        }

        if (isset($_POST['submit_department']) && wp_verify_nonce($_POST['nonce'], 'department_nonce')) {
            $department_name = sanitize_text_field($_POST['department_name']);
            if (!empty($department_name)) {
                $wpdb->update($table_name, ['department_name' => $department_name], ['department_id' => $department_id, 'education_center_id' => $education_center_id], ['%s'], ['%d', '%s']);
                wp_redirect(home_url('/institute-dashboard/departments/?section=edit-departments'));
                exit;
            } else {
                echo '<div class="alert alert-danger">Department name is required.</div>';
            }
        }

        ob_start();
        ?>
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Update Department Details</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="department_name" class="form-label">Department Name</label>
                        <input type="text" name="department_name" id="department_name" class="form-control" value="<?php echo esc_attr($department->department_name); ?>" required>
                    </div>
                    <?php wp_nonce_field('department_nonce', 'nonce'); ?>
                    <button type="submit" name="submit_department" class="btn btn-primary">Update Department</button>
                    <a href="?section=departments" class="btn btn-secondary">Back to Directory</a>
            </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    } else {
        $departments = get_departments_with_teacher_count($education_center_id);
        ob_start();
        ?>
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Select Department to Edit</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" style="background-color: #f0f8ff;">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Department ID</th>
                                <th>Department Name</th>
                                <th style="width: 15%;">Total Teachers</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($departments)) {
                                echo '<tr><td colspan="4">No departments available to edit.</td></tr>';
                            } else {
                                foreach ($departments as $dept) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($dept->department_id) . '</td>';
                                    echo '<td>' . esc_html($dept->department_name) . '</td>';
                                    echo '<td>' . $dept->teacher_count . '</td>';
                                    echo '<td><a href="?section=edit-department&department_id=' . $dept->department_id . '" class="btn btn-sm btn-outline-primary">Edit</a></td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <a href="?section=departments" class="btn btn-secondary mt-3">Back to Directory</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Delete Department
function delete_department_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $table_name = $wpdb->prefix . 'departments';
    $department_id = isset($_GET['department_id']) ? intval($_GET['department_id']) : 0;

    if ($department_id > 0 && isset($_GET['action']) && $_GET['action'] === 'delete') {
        ob_start();
        ?>
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">Department Deletion</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-success">Department deleted successfully!</div>
                <a href="?section=departments" class="btn btn-primary">Back to Department Directory</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    } else {
        $departments = get_departments_with_teacher_count($education_center_id);
        ob_start();
        ?>
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">Select Department to Delete</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" style="background-color: #fff0f0;">
                        <thead>
                            <tr>
                                <th style="width: 20%;">Department ID</th>
                                <th>Department Name</th>
                                <th style="width: 15%;">Total Teachers</th>
                                <th style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($departments)) {
                                echo '<tr><td colspan="4">No departments available to delete.</td></tr>';
                            } else {
                                foreach ($departments as $dept) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($dept->department_id) . '</td>';
                                    echo '<td>' . esc_html($dept->department_name) . '</td>';
                                    echo '<td>' . $dept->teacher_count . '</td>';
                                    echo '<td><a href="?section=delete-department&action=delete&department_id=' . $dept->department_id . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Are you sure you want to delete ' . esc_attr($dept->department_name) . '?\');">Delete</a></td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <a href="?section=departments" class="btn btn-secondary mt-3">Back to Directory</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}