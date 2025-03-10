<?php
if (!defined('ABSPATH')) {
    exit;
}

use Dompdf\Dompdf;
use Dompdf\Options;

function generate_unique_staff_id($wpdb, $table_name, $education_center_id) {
    $max_attempts = 5;
    $prefix = 'STF-';
    $id_length = 12;

    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        $time_part = substr(str_replace('.', '', microtime(true)), -10);
        $random_part = strtoupper(substr(bin2hex(random_bytes(1)), 0, 2));
        $staff_id = $prefix . $time_part . $random_part;

        if (strlen($staff_id) > 20) {
            $staff_id = substr($staff_id, 0, 20);
        }

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE staff_id = %s AND education_center_id = %s",
            $staff_id, $education_center_id
        ));

        if ($exists == 0) {
            return $staff_id;
        }
        usleep(10000);
    }
    return new WP_Error('staff_id_generation_failed', 'Unable to generate a unique Staff ID after multiple attempts.');
}

// Handle the Add Staff form submission via admin-post.php
add_action('admin_post_add_new_staff', 'handle_add_new_staff_submission');
function handle_add_new_staff_submission() {
    global $wpdb;
    $staff_table = $wpdb->prefix . 'staff';
    $education_center_id = get_educational_center_data();

    // Log the submission attempt
    error_log("handle_add_new_staff_submission() called at " . current_time('mysql'));
    error_log("POST data: " . print_r($_POST, true));

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'add_new_staff_nonce')) {
        error_log("Nonce verification failed");
        wp_die('Security check failed. Please try again.', 'Error', ['back_link' => true]);
    }

    // Sanitize and validate input
    $staff_id = sanitize_text_field($_POST['staff_id']);
    $name = sanitize_text_field($_POST['name']);
    $role = sanitize_text_field($_POST['role']);
    $salary_base = floatval($_POST['salary_base']);

    error_log("Processed data: staff_id=$staff_id, name=$name, role=$role, salary_base=$salary_base");

    if (empty($staff_id) || empty($name) || empty($role) || $salary_base < 0) {
        error_log("Validation failed: Invalid or missing fields");
        wp_die('All fields must be valid (Staff ID, Name, Role, and a non-negative salary)!', 'Validation Error', ['back_link' => true]);
    }

    // Check for duplicates
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $staff_table WHERE staff_id = %s OR (name = %s AND role = %s AND education_center_id = %s)",
        $staff_id, $name, $role, $education_center_id
    ));

    if ($exists > 0) {
        error_log("Duplicate staff detected: staff_id=$staff_id or name/role combo exists");
        wp_die('Staff ID or Name/Role combination already exists!', 'Duplicate Error', ['back_link' => true]);
    }

    // Insert into database
    $result = $wpdb->insert(
        $staff_table,
        [
            'staff_id' => $staff_id,
            'name' => $name,
            'role' => $role,
            'education_center_id' => $education_center_id,
            'salary_base' => $salary_base
        ],
        ['%s', '%s', '%s', '%s', '%f']
    );

    if ($result === false) {
        error_log("Insert failed: " . $wpdb->last_error);
        error_log("Last query: " . $wpdb->last_query);
        wp_die('Failed to add staff: ' . esc_html($wpdb->last_error), 'Database Error', ['back_link' => true]);
    }

    error_log("Staff added successfully: staff_id=$staff_id");
    wp_redirect(home_url('/institute-dashboard/staff/?section=staff-list'));
    exit;
}

function aspire_staff_management_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $staff_table = $wpdb->prefix . 'staff';
    $attendance_table = $wpdb->prefix . 'staff_attendance';

    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }

    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'staff-list';

    if ($section === 'staff-list' && isset($_GET['action']) && $_GET['action'] === 'generate_payroll_pdf') {
        $month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : date('Y-m');
        $dompdf_path = dirname(__FILE__) . '/dompdf/autoload.inc.php';
        if (!file_exists($dompdf_path)) {
            wp_die('Dompdf autoload file not found.');
        }
        require_once $dompdf_path;

        $payroll = $wpdb->get_results($wpdb->prepare(
            "SELECT s.staff_id, s.name, s.role, s.salary_base, 
                    COUNT(a.attendance_id) AS days_present,
                    (s.salary_base * COUNT(a.attendance_id) / 30) AS monthly_salary
             FROM $staff_table s
             LEFT JOIN $attendance_table a ON s.staff_id = a.staff_id AND a.status = 'Present' AND DATE_FORMAT(a.date, '%%Y-%%m') = %s
             WHERE s.education_center_id = %s
             GROUP BY s.staff_id, s.name, s.role, s.salary_base",
            $month, $education_center_id
        ));

        $html = '
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; font-size: 12px; }
                th, td { border: 1px solid #333; padding: 8px; text-align: center; }
                th { background-color: #f2f2f2; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Staff Payroll - ' . esc_html($month) . '</h1>
                <p>Education Center: ' . esc_html($education_center_id) . '</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Base Salary</th>
                        <th>Days Present</th>
                        <th>Monthly Salary</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($payroll as $staff) {
            $html .= '<tr>
                <td>' . esc_html($staff->staff_id) . '</td>
                <td>' . esc_html($staff->name) . '</td>
                <td>' . esc_html($staff->role) . '</td>
                <td>' . esc_html($staff->salary_base) . '</td>
                <td>' . esc_html($staff->days_present) . '</td>
                <td>' . number_format($staff->monthly_salary, 2) . '</td>
            </tr>';
        }
        $html .= '</tbody></table></body></html>';

        $dompdf = new Dompdf();
        if (ob_get_length()) {
            ob_end_clean();
        }
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="payroll_' . $education_center_id . '_' . $month . '.pdf"');
        echo $dompdf->output();
        exit;
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
                    case 'staff-list':
                        echo staff_list_shortcode();
                        break;
                    case 'staff-add':
                        echo staff_add_shortcode();
                        break;
                    case 'staff-edit':
                        echo staff_edit_shortcode();
                        break;
                    case 'staff-delete':
                        echo staff_delete_shortcode();
                        break;
                    case 'staff-portal':
                        echo staff_portal_shortcode();
                        break;
                    default:
                        echo '<div class="alert alert-warning">Section not found.</div>';
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_staff_management', 'aspire_staff_management_shortcode');

function staff_list_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $month = date('Y-m');
    $staff_table = $wpdb->prefix . 'staff';
    $attendance_table = $wpdb->prefix . 'staff_attendance';

    $staff = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $staff_table WHERE education_center_id = %s",
        $education_center_id
    ));

    $payroll_summary = $wpdb->get_row($wpdb->prepare(
        "SELECT 
            COALESCE(SUM(s.salary_base * (
                SELECT COUNT(*) 
                FROM $attendance_table a 
                WHERE a.staff_id = s.staff_id 
                AND a.status = 'Present' 
                AND DATE_FORMAT(a.date, '%%Y-%%m') = %s
            ) / 30), 0) AS total_salary,
            COUNT(DISTINCT s.staff_id) AS staff_count 
         FROM $staff_table s 
         WHERE s.education_center_id = %s",
        $month, $education_center_id
    ));

    ob_start();
    ?>
    <div class="card" style="border: 2px solid #007bff;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="card-title">Staff Directory</h3>
            <a href="?section=staff-list&action=generate_payroll_pdf&month=<?php echo $month; ?>" class="btn btn-light">Export Payroll PDF</a>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">Payroll Summary (<?php echo $month; ?>)</h5>
                            <p>Total Staff: <?php echo esc_html($payroll_summary->staff_count ?? 0); ?></p>
                            <p>Total Salary: <?php echo number_format($payroll_summary->total_salary ?? 0, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" style="background-color: #f8f9fa;">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Base Salary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($staff)) {
                            echo '<tr><td colspan="5" class="text-center">No staff found.</td></tr>';
                        } else {
                            foreach ($staff as $member) {
                                echo '<tr>';
                                echo '<td>' . esc_html($member->staff_id) . '</td>';
                                echo '<td>' . esc_html($member->name) . '</td>';
                                echo '<td>' . esc_html($member->role) . '</td>';
                                echo '<td>' . esc_html($member->salary_base) . '</td>';
                                echo '<td>
                                    <a href="?section=staff-edit&staff_id=' . urlencode($member->staff_id) . '" class="btn btn-sm btn-info">Edit</a>
                                    <a href="?section=staff-delete&action=delete&staff_id=' . urlencode($member->staff_id) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\');">Delete</a>
                                </td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-between mt-3">
                <a href="?section=staff-add" class="btn btn-primary">Add New Staff</a>
                <a href="?section=staff-portal" class="btn btn-secondary">Staff Portal</a>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function staff_add_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $staff_table = $wpdb->prefix . 'staff';

    // Generate a unique staff ID
    $new_staff_id = generate_unique_staff_id($wpdb, $staff_table, $education_center_id);
    if (is_wp_error($new_staff_id)) {
        $message = '<div class="alert alert-danger">' . esc_html($new_staff_id->get_error_message()) . '</div>';
        $new_staff_id = '';
    } else {
        $message = '';
    }

    error_log("staff_add_shortcode() rendered at " . current_time('mysql'));

    ob_start();
    ?>
    <div class="card" style="border: 2px solid #28a745; background-color: #f0fff0;">
        <div class="card-header bg-success text-white">
            <h3 class="card-title">Add New Staff</h3>
        </div>
        <div class="card-body">
            <?php echo $message; ?>
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="row g-3">
                <input type="hidden" name="action" value="add_new_staff">
                <div class="col-md-6">
                    <label for="staff_id" class="form-label">Staff ID (Auto-generated):</label>
                    <input type="text" name="staff_id" id="staff_id" class="form-control" value="<?php echo esc_attr($new_staff_id); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo esc_attr($_POST['name'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="Accountant" <?php selected($_POST['role'] ?? '', 'Accountant'); ?>>Accountant</option>
                        <option value="Administrator" <?php selected($_POST['role'] ?? '', 'Administrator'); ?>>Administrator</option>
                        <option value="Librarian" <?php selected($_POST['role'] ?? '', 'Librarian'); ?>>Librarian</option>
                        <option value="Support Staff" <?php selected($_POST['role'] ?? '', 'Support Staff'); ?>>Support Staff</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="salary_base" class="form-label">Base Salary (Monthly)</label>
                    <input type="number" name="salary_base" id="salary_base" class="form-control" step="0.01" min="0" value="<?php echo esc_attr($_POST['salary_base'] ?? ''); ?>" required>
                </div>
                <?php wp_nonce_field('add_new_staff_nonce', 'nonce'); ?>
                <div class="col-12 d-flex justify-content-between mt-3">
                    <button type="submit" name="submit" class="btn btn-primary">Add Staff</button>
                    <a href="?section=staff-list" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


function staff_edit_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $staff_id = isset($_GET['staff_id']) ? sanitize_text_field($_GET['staff_id']) : '';

    if (empty($staff_id)) {
        // Display list of staff to edit if no staff_id is provided
        $staff = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}staff WHERE education_center_id = %s",
            $education_center_id
        ));

        ob_start();
        ?>
        <div class="card" style="border: 2px solid #17a2b8; background-color: #e6faff;">
            <div class="card-header bg-info text-white">
                <h3 class="card-title">Select Staff to Edit</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" style="background-color: #ffffff;">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Base Salary</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($staff)) {
                                echo '<tr><td colspan="5" class="text-center">No staff available.</td></tr>';
                            } else {
                                foreach ($staff as $member) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($member->staff_id) . '</td>';
                                    echo '<td>' . esc_html($member->name) . '</td>';
                                    echo '<td>' . esc_html($member->role) . '</td>';
                                    echo '<td>' . esc_html($member->salary_base) . '</td>';
                                    echo '<td><a href="?section=staff-edit&staff_id=' . urlencode($member->staff_id) . '" class="btn btn-sm btn-outline-info">Edit</a></td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <a href="?section=staff-list" class="btn btn-secondary mt-3">Back to List</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // Fetch the staff member to edit
    $staff = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}staff WHERE staff_id = %s AND education_center_id = %s",
        $staff_id, $education_center_id
    ));

    if (!$staff) {
        return '<div class="alert alert-danger">Staff member not found.</div>';
    }

    ob_start();
    ?>
    <div class="card" style="border: 2px solid #17a2b8; background-color: #e6faff;">
        <div class="card-header bg-info text-white">
            <h3 class="card-title">Edit Staff: <?php echo esc_html($staff->name); ?></h3>
        </div>
        <div class="card-body">
            <?php
            // Display any error or success message passed via URL parameter
            if (isset($_GET['error'])) {
                echo '<div class="alert alert-danger">' . esc_html($_GET['error']) . '</div>';
            } elseif (isset($_GET['success'])) {
                echo '<div class="alert alert-success">' . esc_html($_GET['success']) . '</div>';
            }
            ?>
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="row g-3">
                <input type="hidden" name="action" value="edit_staff">
                <input type="hidden" name="staff_id" value="<?php echo esc_attr($staff->staff_id); ?>">
                <div class="col-md-6">
                    <label for="staff_id_display" class="form-label">Staff ID</label>
                    <input type="text" name="staff_id_display" id="staff_id_display" class="form-control" value="<?php echo esc_attr($staff->staff_id); ?>" readonly>
                </div>
                <div class="col-md-6">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?php echo esc_attr($_POST['name'] ?? $staff->name); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="">Select Role</option>
                        <option value="Accountant" <?php selected($_POST['role'] ?? $staff->role, 'Accountant'); ?>>Accountant</option>
                        <option value="Administrator" <?php selected($_POST['role'] ?? $staff->role, 'Administrator'); ?>>Administrator</option>
                        <option value="Librarian" <?php selected($_POST['role'] ?? $staff->role, 'Librarian'); ?>>Librarian</option>
                        <option value="Support Staff" <?php selected($_POST['role'] ?? $staff->role, 'Support Staff'); ?>>Support Staff</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="salary_base" class="form-label">Base Salary (Monthly)</label>
                    <input type="number" name="salary_base" id="salary_base" class="form-control" step="0.01" min="0" value="<?php echo esc_attr($_POST['salary_base'] ?? $staff->salary_base); ?>" required>
                </div>
                <?php wp_nonce_field('edit_staff_nonce', 'nonce'); ?>
                <div class="col-12 d-flex justify-content-between mt-3">
                    <button type="submit" name="submit_staff" class="btn btn-primary">Update Staff</button>
                    <a href="?section=staff-list" class="btn btn-secondary">Back to List</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Handle the Edit Staff form submission via admin-post.php
add_action('admin_post_edit_staff', 'handle_edit_staff_submission');
function handle_edit_staff_submission() {
    global $wpdb;
    $staff_table = $wpdb->prefix . 'staff';
    $education_center_id = get_educational_center_data();

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'edit_staff_nonce')) {
        error_log("Nonce verification failed");
        wp_redirect(add_query_arg('error', 'Security check failed. Please try again.', wp_get_referer()));
        exit;
    }

    // Sanitize and validate input
    $staff_id = sanitize_text_field($_POST['staff_id']);
    $name = sanitize_text_field($_POST['name']);
    $role = sanitize_text_field($_POST['role']);
    $salary_base = floatval($_POST['salary_base']);

    if (empty($staff_id) || empty($name) || empty($role) || $salary_base < 0) {
        error_log("Validation failed: Invalid or missing fields");
        wp_redirect(add_query_arg('error', 'All fields must be valid (Staff ID, Name, Role, and a non-negative salary)!', wp_get_referer()));
        exit;
    }

    // Check for duplicates (excluding current staff_id)
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $staff_table WHERE name = %s AND role = %s AND education_center_id = %s AND staff_id != %s",
        $name, $role, $education_center_id, $staff_id
    ));

    if ($exists > 0) {
        error_log("Duplicate staff detected: name/role combo exists");
        wp_redirect(add_query_arg('error', 'Another staff member with this name and role already exists!', wp_get_referer()));
        exit;
    }

    // Update the database
    $result = $wpdb->update(
        $staff_table,
        [
            'name' => $name,
            'role' => $role,
            'salary_base' => $salary_base
        ],
        [
            'staff_id' => $staff_id,
            'education_center_id' => $education_center_id
        ],
        ['%s', '%s', '%f'],
        ['%s', '%s']
    );

    if ($result === false) {
        error_log("Update failed: " . $wpdb->last_error);
        error_log("Last query: " . $wpdb->last_query);
        wp_redirect(add_query_arg('error', 'Failed to update staff: ' . esc_html($wpdb->last_error), wp_get_referer()));
        exit;
    }

    error_log("Staff updated successfully: staff_id=$staff_id");
    wp_redirect(home_url('/institute-dashboard/staff/?section=staff-list'));
    exit;
}

function staff_delete_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $staff_id = isset($_GET['staff_id']) ? sanitize_text_field($_GET['staff_id']) : '';

    if (!empty($staff_id) && isset($_GET['action']) && $_GET['action'] === 'delete') {
        $result = $wpdb->delete($wpdb->prefix . 'staff', ['staff_id' => $staff_id, 'education_center_id' => $education_center_id], ['%s', '%s']);
        if ($result === false) {
            $error = '<div class="alert alert-danger">Failed to delete staff: ' . esc_html($wpdb->last_error) . '</div>';
        } else {
            $message = '<div class="alert alert-success">Staff member deleted successfully!</div>';
        }

        ob_start();
        ?>
        <div class="card" style="border: 2px solid #dc3545; background-color: #fff5f5;">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">Staff Deletion</h3>
            </div>
            <div class="card-body text-center">
                <?php echo $message ?? $error ?? ''; ?>
                <a href="?section=staff-list" class="btn btn-primary">Back to Staff List</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    } else {
        $staff = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}staff WHERE education_center_id = %s",
            $education_center_id
        ));

        ob_start();
        ?>
        <div class="card" style="border: 2px solid #dc3545; background-color: #fff5f5;">
            <div class="card-header bg-danger text-white">
                <h3 class="card-title">Select Staff to Delete</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" style="background-color: #ffffff;">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Base Salary</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($staff)) {
                                echo '<tr><td colspan="5" class="text-center">No staff available.</td></tr>';
                            } else {
                                foreach ($staff as $member) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($member->staff_id) . '</td>';
                                    echo '<td>' . esc_html($member->name) . '</td>';
                                    echo '<td>' . esc_html($member->role) . '</td>';
                                    echo '<td>' . esc_html($member->salary_base) . '</td>';
                                    echo '<td><a href="?section=staff-delete&action=delete&staff_id=' . urlencode($member->staff_id) . '" class="btn btn-sm btn-outline-danger" onclick="return confirm(\'Are you sure?\');">Delete</a></td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <a href="?section=staff-list" class="btn btn-secondary mt-3">Back to List</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

function staff_portal_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $staff_id = isset($_GET['staff_id']) ? sanitize_text_field($_GET['staff_id']) : '';

    if (empty($staff_id)) {
        $staff = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}staff WHERE education_center_id = %s",
            $education_center_id
        ));

        ob_start();
        ?>
        <div class="card" style="border: 2px solid #6c757d; background-color: #f8f9fa;">
            <div class="card-header bg-secondary text-white">
                <h3 class="card-title">Select Staff for Portal</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    if (empty($staff)) {
                        echo '<div class="col-12 text-center">No staff available.</div>';
                    } else {
                        foreach ($staff as $member) {
                            echo '<div class="col-md-4 mb-3">';
                            echo '<div class="card h-100">';
                            echo '<div class="card-body">';
                            echo '<h5 class="card-title">' . esc_html($member->name) . '</h5>';
                            echo '<p class="card-text"><strong>ID:</strong> ' . esc_html($member->staff_id) . '</p>';
                            echo '<p class="card-text"><strong>Role:</strong> ' . esc_html($member->role) . '</p>';
                            echo '<a href="?section=staff-portal&staff_id=' . urlencode($member->staff_id) . '" class="btn btn-sm btn-outline-secondary">View Portal</a>';
                            echo '</div>';
                            echo '</div>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                <a href="?section=staff-list" class="btn btn-secondary mt-3">Back to List</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    $staff = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}staff WHERE staff_id = %s AND education_center_id = %s",
        $staff_id, $education_center_id
    ));

    if (!$staff) {
        return '<div class="alert alert-danger">No staff profile found for this ID.</div>';
    }

    $month = date('Y-m');
    $attendance = $wpdb->get_results($wpdb->prepare(
        "SELECT date, status FROM {$wpdb->prefix}staff_attendance WHERE staff_id = %s AND DATE_FORMAT(date, '%%Y-%%m') = %s",
        $staff->staff_id, $month
    ));
    $days_present = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}staff_attendance WHERE staff_id = %s AND status = 'Present' AND DATE_FORMAT(date, '%%Y-%%m') = %s",
        $staff->staff_id, $month
    ));
    $monthly_salary = $staff->salary_base * $days_present / 30;

    ob_start();
    ?>
    <div class="card" style="border: 2px solid #6c757d; background-color: #f8f9fa;">
        <div class="card-header bg-secondary text-white">
            <h3 class="card-title">Staff Portal - <?php echo esc_html($staff->name); ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Profile</h5>
                            <p><strong>Staff ID:</strong> <?php echo esc_html($staff->staff_id); ?></p>
                            <p><strong>Role:</strong> <?php echo esc_html($staff->role); ?></p>
                            <p><strong>Base Salary:</strong> <?php echo esc_html($staff->salary_base); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5>Salary Summary (<?php echo $month; ?>)</h5>
                            <p><strong>Days Present:</strong> <?php echo esc_html($days_present); ?></p>
                            <p><strong>Monthly Salary:</strong> <?php echo number_format($monthly_salary, 2); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <h5 class="mt-4">Attendance (<?php echo $month; ?>)</h5>
            <div class="table-responsive">
                <table class="table table-striped table-bordered" style="background-color: #ffffff;">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($attendance)) {
                            echo '<tr><td colspan="2" class="text-center">No attendance records for this month.</td></tr>';
                        } else {
                            foreach ($attendance as $record) {
                                echo '<tr>';
                                echo '<td>' . esc_html($record->date) . '</td>';
                                echo '<td>' . esc_html($record->status) . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <a href="?section=staff-list" class="btn btn-secondary mt-3">Back to Staff List</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}