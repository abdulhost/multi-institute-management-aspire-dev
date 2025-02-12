<?php

// Function to render the Students section
function wp_dashboard_setup() {
    // Fetch the Educational Center ID for the logged-in user
    $current_user = wp_get_current_user();
    $admin_id = $current_user->user_login;

    // Query the Educational Center based on the admin_id
    $args = array(
        'post_type' => 'educational-center',
        'meta_query' => array(
            array(
                'key' => 'admin_id',
                'value' => $admin_id,
                'compare' => '='
            )
        )
    );
    $educational_center = new WP_Query($args);

    // Check if there is an Educational Center for this admin
    if ($educational_center->have_posts()) {
        $educational_center->the_post();
        $educational_center_id = get_post_meta(get_the_ID(), 'educational_center_id', true); // Custom field value
    } else {
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    // Query to get students associated with the current Educational Center
    $students_args = array(
        'post_type' => 'students',
        'meta_query' => array(
            array(
                'key' => 'educational_center_id',
                'value' => $educational_center_id,
                'compare' => '='
            )
        )
    );
    $students_query = new WP_Query($students_args);

    // Start output buffering
    ob_start();
    ?>

    <!-- Add Student Button -->
    <button id="add-student-btn">+ Add Student</button>

    <!-- Add Student Form (Hidden by default) -->
    <div id="add-student-form" style="display: none;">
        <h3>Add Student</h3>
        <form method="POST">
            <label for="student_name">Student Name</label>
            <input type="text" name="student_name" required>

            <label for="student_email">Student Email</label>
            <input type="email" name="student_email" required>

            <label for="student_class">Class</label>
            <input type="text" name="student_class" required>

            <!-- Hidden field to pass the educational center ID -->
            <input type="hidden" name="educational_center_id" value="<?php echo esc_attr($educational_center_id); ?>">

            <!-- Student ID Field -->
            <label for="student_id">Student ID (Auto-generated)</label>
            <input type="text" name="student_id" value="<?php echo 'STU-' . uniqid(); ?>" readonly>

            <input type="submit" name="add_student" value="Add Student">
        </form>
    </div>

    <?php
    // Handle form submission to add student
    if (isset($_POST['add_student'])) {
        // Get form data
        $student_name = sanitize_text_field($_POST['student_name']);
        $student_email = sanitize_email($_POST['student_email']);
        $student_class = sanitize_text_field($_POST['student_class']);
        $educational_center_id = sanitize_text_field($_POST['educational_center_id']);
        $student_id = sanitize_text_field($_POST['student_id']); // Unique Student ID

        // Insert the student into the 'students' CPT
        $student_post = array(
            'post_title'   => $student_name,
            'post_type'    => 'students',
            'post_status'  => 'publish',
            'meta_input'   => array(
                'student_email'           => $student_email,
                'student_class'           => $student_class,
                'educational_center_id'   => $educational_center_id, // Associate with the Educational Center
                'student_id'              => $student_id, // Store unique student ID
            ),
        );
        $student_id = wp_insert_post($student_post);

        // Provide feedback and redirect
        if ($student_id) {
            // Redirect to the same page after submitting the form
            wp_redirect(home_url('/institute-dashboard/')); // No query parameters
            // exit; Stop further code execution after redirect
        } else {
            echo '<p class="error-message">Error adding student.</p>';
        }
    }

    // Display the list of students associated with this Educational Center
    if ($students_query->have_posts()) {
        echo '<h3>Student List</h3>';
        echo '<ul>';
        while ($students_query->have_posts()) {
            $students_query->the_post();
            $student_name = get_the_title();
            $student_email = get_post_meta(get_the_ID(), 'student_email', true);
            $student_class = get_post_meta(get_the_ID(), 'student_class', true);
            $student_id = get_post_meta(get_the_ID(), 'student_id', true);
            echo '<li><strong>' . esc_html($student_name) . ' (ID: ' . esc_html($student_id) . ')</strong> (' . esc_html($student_email) . ') - ' . esc_html($student_class) . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p>No students found for this Educational Center.</p>';
    }

}
