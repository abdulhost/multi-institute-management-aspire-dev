<?php
if (!defined('ABSPATH')) {
    exit;
}

function my_education_erp_create_db() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Define table names
    $fee_templates_table = $wpdb->prefix . 'fee_templates';
    $student_fees_table = $wpdb->prefix . 'student_fees';
    $transport_enrollments_table = $wpdb->prefix . 'transport_enrollments';
    $transport_fees_table = $wpdb->prefix . 'transport_fees';
    $exams_table = $wpdb->prefix . 'exams';
    $exam_subjects_table = $wpdb->prefix . 'exam_subjects';
    $exam_results_table = $wpdb->prefix . 'exam_results';
    $table_name = $wpdb->prefix . 'teacher_attendance';
    $departments_table = $wpdb->prefix . 'departments';
    // Fee Templates Table
    $sql = "CREATE TABLE $fee_templates_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        frequency ENUM('monthly', 'quarterly', 'annual') DEFAULT 'monthly',
        class_id VARCHAR(255) DEFAULT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Student Fees Table (Tuition/Combo)
    $sql = "CREATE TABLE $student_fees_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id VARCHAR(255) NOT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        template_id VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        month_year VARCHAR(7) NOT NULL,
        status ENUM('pending', 'paid') DEFAULT 'paid',
        paid_date DATE DEFAULT NULL,
        payment_method ENUM('cod', 'cheque', 'cash') DEFAULT NULL,
        cheque_number VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Transport Enrollments Table
    $sql = "CREATE TABLE $transport_enrollments_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id VARCHAR(255) NOT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        enrollment_date DATE NOT NULL,
        active TINYINT(1) DEFAULT 1,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Transport Fees Table
    $sql = "CREATE TABLE $transport_fees_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        student_id VARCHAR(255) NOT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        template_id VARCHAR(255) NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        month_year VARCHAR(7) NOT NULL,
        status ENUM('pending', 'paid') DEFAULT 'paid',
        paid_date DATE DEFAULT NULL,
        payment_method ENUM('cod', 'cheque', 'cash') DEFAULT NULL,
        cheque_number VARCHAR(50) DEFAULT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Exams Table
    $sql = "CREATE TABLE $exams_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        exam_date DATE NOT NULL,
        class_id VARCHAR(255) DEFAULT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Exam Subjects Table (No foreign key for now)
    $sql = "CREATE TABLE $exam_subjects_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        exam_id VARCHAR(255) NOT NULL,
        subject_name VARCHAR(100) NOT NULL,
        max_marks DECIMAL(10,2) NOT NULL DEFAULT 100.00,
        education_center_id VARCHAR(255) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    // Exam Results Table 
    $sql = "CREATE TABLE $exam_results_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        exam_id VARCHAR(255) NOT NULL,
        subject_id VARCHAR(255) NOT NULL,
        student_id VARCHAR(255) NOT NULL,
        marks DECIMAL(10,2) NOT NULL,
        education_center_id VARCHAR(255) NOT NULL,
        recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    dbDelta($sql);

    
$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    ta_id INT(11) NOT NULL AUTO_INCREMENT,
    education_center_id VARCHAR(255) NOT NULL,
    teacher_id VARCHAR(50) NOT NULL,
    teacher_name VARCHAR(255) NOT NULL,
    department_id INT(11) NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    PRIMARY KEY (ta_id),
    INDEX (teacher_id, date)
) $charset_collate;";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// Create departments table
$sql = "CREATE TABLE IF NOT EXISTS $departments_table (
    department_id INT(11) NOT NULL AUTO_INCREMENT,
    department_name VARCHAR(255) NOT NULL,
    education_center_id VARCHAR(255) NOT NULL,
    PRIMARY KEY (department_id),
    INDEX (education_center_id)
) $charset_collate;";
dbDelta($sql);

$table_class_name = $wpdb->prefix . 'class_sections';

    // Uncomment and adjust table creation if needed
        $sql = "CREATE TABLE $table_class_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            class_name varchar(255) NOT NULL,
            sections text NOT NULL,
            education_center_id varchar(255), 
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
        $timetable_table = $wpdb->prefix . 'timetables';
        $sql = "CREATE TABLE IF NOT EXISTS $timetable_table (
            timetable_id INT AUTO_INCREMENT PRIMARY KEY,
            class_id INT NOT NULL,
            section VARCHAR(50) NOT NULL,
            subject_id BIGINT DEFAULT NULL,
            day VARCHAR(20) NOT NULL,
            start_time TIME NOT NULL,
            end_time TIME NOT NULL,
            education_center_id VARCHAR(255) NOT NULL,
            INDEX (education_center_id, class_id, section)
        ) $charset_collate;";
        

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
// Define charset and collation
$charset_collate = $wpdb->get_charset_collate();
// Staff Table (updated: staff_id as VARCHAR, removed user_id)
$staff_table = $wpdb->prefix . 'staff';
$sql_staff = "CREATE TABLE IF NOT EXISTS $staff_table (
    staff_id VARCHAR(20) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(100) NOT NULL,
    department_id INT DEFAULT NULL,
    education_center_id VARCHAR(255) NOT NULL,
    salary_base DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (department_id) REFERENCES {$wpdb->prefix}departments(department_id) ON DELETE SET NULL,
    INDEX (education_center_id)
) $charset_collate;";
dbDelta($sql_staff);

// Staff Attendance Table (updated: staff_id as VARCHAR)
$staff_attendance_table = $wpdb->prefix . 'staff_attendance';
$sql_attendance = "CREATE TABLE IF NOT EXISTS $staff_attendance_table (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    staff_id VARCHAR(20) NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Leave') DEFAULT 'Absent',
    education_center_id VARCHAR(255) NOT NULL,
    FOREIGN KEY (staff_id) REFERENCES $staff_table(staff_id) ON DELETE CASCADE,
    INDEX (staff_id, date),
    INDEX (education_center_id)
) $charset_collate;";
dbDelta($sql_attendance);
    // wp_inventory
    $table_name = $wpdb->prefix . 'inventory';
    $sql = "CREATE TABLE $table_name (
        item_id VARCHAR(20) NOT NULL,
        name VARCHAR(100) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        status ENUM('Available', 'Issued', 'Damaged') DEFAULT 'Available',
        category VARCHAR(50) DEFAULT 'Other',
        education_center_id VARCHAR(50) NOT NULL,
        low_stock_threshold INT DEFAULT 5,
        PRIMARY KEY (item_id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // wp_inventory_transactions
    $table_name = $wpdb->prefix . 'inventory_transactions';
    $sql = "CREATE TABLE $table_name (
        transaction_id BIGINT NOT NULL AUTO_INCREMENT,
        item_id VARCHAR(20) NOT NULL,
        user_id VARCHAR(20) NOT NULL,
        user_type ENUM('Staff', 'Student') NOT NULL,
        action ENUM('Issue', 'Return') NOT NULL,
        date DATETIME NOT NULL,
        status ENUM('Pending', 'Completed') DEFAULT 'Pending',
        PRIMARY KEY (transaction_id)
    ) $charset_collate;";
    dbDelta($sql);

    // wp_library
    $table_name = $wpdb->prefix . 'library';
    $sql = "CREATE TABLE $table_name (
        book_id VARCHAR(20) NOT NULL,
        isbn VARCHAR(13),
        title VARCHAR(100) NOT NULL,
        author VARCHAR(100) NOT NULL,
        quantity INT NOT NULL DEFAULT 0,
        available INT NOT NULL DEFAULT 0,
        education_center_id VARCHAR(50) NOT NULL,
        PRIMARY KEY (book_id)
    ) $charset_collate;";
    dbDelta($sql);

    // wp_library_transactions
    $table_name = $wpdb->prefix . 'library_transactions';
    $sql = "CREATE TABLE $table_name (
        transaction_id BIGINT NOT NULL AUTO_INCREMENT,
        book_id VARCHAR(20) NOT NULL,
        user_id VARCHAR(20) NOT NULL,
        user_type ENUM('Staff', 'Student') NOT NULL,
        issue_date DATETIME NOT NULL,
        due_date DATETIME NOT NULL,
        return_date DATETIME NULL,
        fine DECIMAL(10,2) DEFAULT 0.00,
        PRIMARY KEY (transaction_id)
    ) $charset_collate;";
    dbDelta($sql);

    // Homework Table
    $homework_table = $wpdb->prefix . 'homework';
    $sql = "CREATE TABLE IF NOT EXISTS $homework_table (
        homework_id BIGINT(20) NOT NULL AUTO_INCREMENT,
        education_center_id VARCHAR(255) NOT NULL,
        teacher_id VARCHAR(255) NOT NULL,
        subject_id BIGINT(20),
        class_name VARCHAR(255) NOT NULL,
        section VARCHAR(50) NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        due_date DATE NOT NULL,
        status ENUM('active', 'completed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (homework_id),
        INDEX (education_center_id(191), teacher_id(191), class_name(191), section(50))  -- Limiting index length
    ) $charset_collate;";
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);

    $table = $wpdb->prefix . 'aspire_announcements';
    $sql = "CREATE TABLE $table (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    sender_id VARCHAR(255) NOT NULL,
    receiver_id VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    education_center_id VARCHAR(255) NOT NULL,
    timestamp DATETIME NOT NULL,
    PRIMARY KEY (id),
    INDEX idx_education_center (education_center_id),
    INDEX idx_timestamp (timestamp)
) $charset_collate;";
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);



$charset_collate = $wpdb->get_charset_collate();
    $messages_table = $wpdb->prefix . 'aspire_messages';

   $sql = " CREATE TABLE IF NOT EXISTS $messages_table (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        sender_id VARCHAR(50) NOT NULL, /* Username of sender */
        receiver_id VARCHAR(50) NOT NULL, /* Username of receiver or group name */
        message TEXT NOT NULL,
        status ENUM('sent', 'read') DEFAULT 'sent',
        education_center_id VARCHAR(50) NOT NULL,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX sender_idx (sender_id),
        INDEX receiver_idx (receiver_id),
        INDEX education_center_idx (education_center_id)
    ) $charset_collate;";
   dbDelta($sql);


$institute_admins = $wpdb->prefix . 'institute_admins';
$sql = "CREATE TABLE IF NOT EXISTS $institute_admins (
    id BIGINT(20) NOT NULL AUTO_INCREMENT,
    institute_admin_id VARCHAR(50) NOT NULL, /* Username, e.g., adminC24162EFC0AE */
    education_center_id VARCHAR(50) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    PRIMARY KEY (id),
    UNIQUE KEY (institute_admin_id),
    INDEX edu_center_idx (education_center_id)
) $charset_collate;";
 dbDelta($sql);


$table_name = $wpdb->prefix . 'student_attendance';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    sa_id mediumint(9) NOT NULL AUTO_INCREMENT,
    education_center_id varchar(255) NOT NULL,
    student_id VARCHAR(50) NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    class varchar(255) NOT NULL,
    section text NOT NULL,
    date DATE NOT NULL,
    status VARCHAR(50) NOT NULL,
    teacher_id VARCHAR(50) NOT NULL,
    subject VARCHAR(50) ,
    PRIMARY KEY (sa_id),
    INDEX (student_id, date)
) $charset_collate;";

$table_name = $wpdb->prefix . 'subjects';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS $table_name (
    subject_id mediumint(9) NOT NULL AUTO_INCREMENT,
    education_center_id varchar(255) NOT NULL,
    subject_name VARCHAR(50) NOT NULL,
    teacher_id VARCHAR(50) NOT NULL,
    PRIMARY KEY (subject_id)
) $charset_collate;"; // Removed the extra comma here

//
// Create educational_center table
$educational_center_table = $wpdb->prefix . 'educational_center';
$sql = "CREATE TABLE IF NOT EXISTS $educational_center_table (
    educational_center_id VARCHAR(255) NOT NULL,
    institute_logo TEXT DEFAULT NULL,
    edu_center_name VARCHAR(255) NOT NULL,
    mobile_number VARCHAR(50) DEFAULT NULL,
    email_id VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    PRIMARY KEY (educational_center_id),
    INDEX (edu_center_name)
) $charset_collate;";
require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

$students_table = $wpdb->prefix . 'aspire_students';
// Create students table
$sql = "CREATE TABLE IF NOT EXISTS $students_table (
    student_id VARCHAR(255) NOT NULL,
    student_name VARCHAR(255) NOT NULL,
    educational_center_id VARCHAR(255) NOT NULL,
    student_email VARCHAR(100) DEFAULT NULL,
    admission_number VARCHAR(50) DEFAULT NULL,
    phone_number VARCHAR(50) DEFAULT NULL,
    gender VARCHAR(20) DEFAULT NULL,
    date_of_birth DATE DEFAULT NULL,
    religion VARCHAR(50) DEFAULT NULL,
    student_profile_photo TEXT DEFAULT NULL,
    blood_group VARCHAR(10) DEFAULT NULL,
    height FLOAT DEFAULT NULL,
    weight FLOAT DEFAULT NULL,
    current_address TEXT DEFAULT NULL,
    permanent_address TEXT DEFAULT NULL,
    roll_number VARCHAR(50) DEFAULT NULL,
    admission_date DATE DEFAULT NULL,
    class MEDIUMINT(9) DEFAULT NULL,
    section VARCHAR(50) DEFAULT NULL,
    PRIMARY KEY (student_id),
    UNIQUE KEY (admission_number),
    INDEX (educational_center_id),
    INDEX (student_email),
    INDEX (class)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
}

function my_education_erp_seed_templates() {
    global $wpdb;

    $fee_templates_table = $wpdb->prefix . 'fee_templates';
    $exams_table = $wpdb->prefix . 'exams';
    $exam_subjects_table = $wpdb->prefix . 'exam_subjects';
    $exam_results_table = $wpdb->prefix . 'exam_results';

    // Seed Fee Templates
    if ($wpdb->get_var("SELECT COUNT(*) FROM $fee_templates_table") == 0) {
        $wpdb->insert($fee_templates_table, ['name' => 'Tuition Class 11', 'amount' => 500.00, 'frequency' => 'monthly', 'education_center_id' => 1]);
        $wpdb->insert($fee_templates_table, ['name' => 'Transport', 'amount' => 200.00, 'frequency' => 'monthly', 'education_center_id' => 1]);
        $wpdb->insert($fee_templates_table, ['name' => 'Tuition + Transport', 'amount' => 700.00, 'frequency' => 'monthly', 'education_center_id' => 1]);
    }

    // Seed Exams
    if ($wpdb->get_var("SELECT COUNT(*) FROM $exams_table") == 0) {
        $wpdb->insert($exams_table, [
            'name' => 'Final Exam Class 12 2025',
            'exam_date' => '2025-12-15',
            'class_id' => 12,
            'education_center_id' => 1,
        ]);
    }

    // Seed Exam Subjects
    if ($wpdb->get_var("SELECT COUNT(*) FROM $exam_subjects_table") == 0) {
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'Mathematics', 'max_marks' => 100, 'education_center_id' => 1]);
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'Science', 'max_marks' => 100, 'education_center_id' => 1]);
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'English', 'max_marks' => 100, 'education_center_id' => 1]);
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'History', 'max_marks' => 100, 'education_center_id' => 1]);
        $wpdb->insert($exam_subjects_table, ['exam_id' => 1, 'subject_name' => 'Geography', 'max_marks' => 100, 'education_center_id' => 1]);
    }

    // Seed Exam Results
    if ($wpdb->get_var("SELECT COUNT(*) FROM $exam_results_table") == 0) {
        $wpdb->insert($exam_results_table, ['exam_id' => 1, 'subject_id' => 1, 'student_id' => 1, 'marks' => 85.50, 'education_center_id' => 1]);
        $wpdb->insert($exam_results_table, ['exam_id' => 1, 'subject_id' => 2, 'student_id' => 1, 'marks' => 78.00, 'education_center_id' => 1]);
    }
    $educational_center_table = $wpdb->prefix . 'educational_center';

    // Seed Educational Centers
    if ($wpdb->get_var("SELECT COUNT(*) FROM $educational_center_table") == 0) {
        $wpdb->insert($educational_center_table, [
            'educational_center_id' => 'EC001',
            'institute_logo' => '/wp-content/uploads/logos/sunrise.png',
            'edu_center_name' => 'Sunrise Learning Academy',
            'mobile_number' => '+1-555-123-4567',
            'email_id' => 'contact@sunriseacademy.org',
            'address' => '123 Main Street, Springfield, IL 62701, USA'
        ]);
        $wpdb->insert($educational_center_table, [
            'educational_center_id' => 'EC002',
            'institute_logo' => '/wp-content/uploads/logos/horizon.png',
            'edu_center_name' => 'Horizon Education Center',
            'mobile_number' => '+1-555-987-6543',
            'email_id' => 'info@horizonedu.org',
            'address' => '456 Oak Avenue, Boulder, CO 80301, USA'
        ]);
        $wpdb->insert($educational_center_table, [
            'educational_center_id' => 'EC003',
            'institute_logo' => null,
            'edu_center_name' => 'Bright Future Institute',
            'mobile_number' => '+1-555-456-7890',
            'email_id' => 'admin@brightfuture.org',
            'address' => '789 Pine Road, Austin, TX 73301, USA'
        ]);
    }
    $institute_admins_table = $wpdb->prefix . 'institute_admins';

    if ($wpdb->get_var("SELECT COUNT(*) FROM $institute_admins_table") == 0) {
        $default_admins = [
            [
                'institute_admin_id' => 'adminC24162EFC0AE',
                'education_center_id' => 'EC001',
                'name' => 'John Doe',
                'email' => 'john.doe@sunriseacademy.org',
                'password' => 'admin123',
                'role' => 'institute_admin'
            ],
            [
                'institute_admin_id' => 'adminD39281AFB5CD',
                'education_center_id' => 'EC002',
                'name' => 'Jane Smith',
                'email' => 'jane.smith@horizonedu.org',
                'password' => 'securepass456',
                'role' => 'institute_admin'
            ],
            [
                'institute_admin_id' => 'adminE57192CDE6BF',
                'education_center_id' => 'EC003',
                'name' => 'Alice Brown',
                'email' => 'alice.brown@brightfuture.org',
                'password' => 'password789',
                'role' => 'institute_admin'
            ]
        ];

        foreach ($default_admins as $admin) {
             $user_id = wp_create_user($admin['institute_admin_id'], $admin['password'], $admin['email']);

             if (!is_wp_error($user_id)) {
                 $user = new WP_User($user_id);
                 $user->set_role($admin['role']);
                 wp_update_user([
                     'ID' => $user_id,
                     'display_name' => $admin['name']
                 ]);
                 $wpdb->insert($institute_admins_table, [
                     'institute_admin_id' => $admin['institute_admin_id'],
                     'education_center_id' => $admin['education_center_id'],
                     'name' => $admin['name'],
                     'email' => $admin['email']
                   
                 ]);

                 if ($wpdb->last_error) {
                     error_log('Failed to insert institute admin ' . $admin['institute_admin_id'] . ': ' . $wpdb->last_error);
                 }
             } else {
                 error_log('Failed to create user for ' . $admin['institute_admin_id'] . ': ' . $user_id->get_error_message());
             }
        }
    }
      // Seed Students if table is empty
      $students_table = $wpdb->prefix . 'aspire_students';
      if ($wpdb->get_var("SELECT COUNT(*) FROM $students_table") == 0) {
        $default_students = [
            [
                'student_id' => 'STU-001',
                'student_name' => 'Amit Sharma',
                'educational_center_id' => 'EC001',
                'student_email' => 'amit.sharma@example.com',
                'admission_number' => 'ADM001',
                'phone_number' => '+91-9876543210',
                'gender' => 'Male',
                'date_of_birth' => '2008-05-15',
                'religion' => 'Hindu',
                'student_profile_photo' => '/wp-content/uploads/students/amit.jpg',
                'blood_group' => 'A+',
                'height' => 165.5,
                'weight' => 55.0,
                'current_address' => '123 Elm Street, Springfield, IL 62701',
                'permanent_address' => '456 Pine Road, Springfield, IL 62701',
                'roll_number' => '101',
                'admission_date' => '2023-04-01',
                'class' => 1, // Class 1 (id from wp_class_sections)
                'section' => 'a'
            ],
            [
                'student_id' => 'STU-002',
                'student_name' => 'Priya Patel',
                'educational_center_id' => 'EC002',
                'student_email' => 'priya.patel@example.com',
                'admission_number' => 'ADM002',
                'phone_number' => '+91-8765432109',
                'gender' => 'Female',
                'date_of_birth' => '2009-03-22',
                'religion' => 'Hindu',
                'student_profile_photo' => null,
                'blood_group' => 'B+',
                'height' => 158.0,
                'weight' => 50.0,
                'current_address' => '789 Oak Avenue, Boulder, CO 80301',
                'permanent_address' => '789 Oak Avenue, Boulder, CO 80301',
                'roll_number' => '102',
                'admission_date' => '2023-04-02',
                'class' => 2, // Class 2
                'section' => 'x'
            ],
            [
                'student_id' => 'STU-003',
                'student_name' => 'Rahul Khan',
                'educational_center_id' => 'EC003',
                'student_email' => 'rahul.khan@example.com',
                'admission_number' => 'ADM003',
                'phone_number' => '+91-7654321098',
                'gender' => 'Male',
                'date_of_birth' => '2007-11-10',
                'religion' => 'Muslim',
                'student_profile_photo' => '/wp-content/uploads/students/rahul.jpg',
                'blood_group' => 'O-',
                'height' => 170.0,
                'weight' => 60.0,
                'current_address' => '321 Maple Drive, Austin, TX 73301',
                'permanent_address' => '321 Maple Drive, Austin, TX 73301',
                'roll_number' => '103',
                'admission_date' => '2023-04-03',
                'class' => 1, // Class 1
                'section' => 'b'
            ]
        ];

        foreach ($default_students as $student) {
            
            // Insert student record
            $wpdb->insert($students_table, $student);
              // Call the reusable registration function
            $role='student';
            register_user_with_role($student['student_id'], $student['student_email'], $student['student_name'], $student['phone_number'], $role, $student['educational_center_id']);

            if ($wpdb->last_error) {
                error_log('Failed to insert student ' . $student['student_id'] . ': ' . $wpdb->last_error);
            }
        }
        }
}
my_education_erp_create_db();
// my_education_erp_seed_templates();
add_action('init', 'my_education_erp_seed_templates');