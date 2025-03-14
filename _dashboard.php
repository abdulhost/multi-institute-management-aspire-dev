<?php
// Shortcode to display educational center dashboard with sidebar
function institute_dashboard_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to view your dashboard.</p>';
    }

    // Start buffering the output
    ob_start();

    // Fetch the logo and title for the sidebar and dashboard
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

    if ($educational_center->have_posts()) {
        $educational_center->the_post();
        $post_id = get_the_ID();
        $logo = get_field('institute_logo', $post_id);
        $title = get_the_title($post_id);
    } else {
        return '<p>No Educational Center found for this Admin ID.</p>';
    }

    ?>

    <div class="institute-dashboard-wrapper">
        <!-- Sidebar -->
        <?php
            $active_section = 'record-attendance';
            include(plugin_dir_path(__FILE__) . 'assets/sidebar.php');
            ?>
        <!-- <div class="sidebar"id="sidebar">
            <div class="logo-title-section">
                <?php if ($logo): ?>
                    <div class="institute-logo">
                        <img src="<?php echo esc_url($logo['url']); ?>" alt="Institute Logo" style="border-radius: 50%; width: 60px; height: 60px; object-fit: cover;">
                    </div>
                <?php endif; ?>
                <h4 class="institute-title"><?php echo esc_html($title); ?></h4>
            </div>
            <ul>
            <li class="active" data-section="dashboard">
         
                            <a href="/institute-dashboard/#dashboard">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
                            <span>Dashboard</span>                        </a>
            </li>
            <li class="has-submenu" data-section="students">
                <span class="icon">👨‍🎓</span>
                <a href="/institute-dashboard/#students">Students</a>
                <ul class="submenu">
                    <li data-section="add-students"><a href="/institute-dashboard/#add-students">Add Students</a></li>
                    <li data-section="edit-students"><a href="/institute-dashboard/#edit-students">Edit Students</a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="classes">
                <span class="icon">📚</span>
                <a href="#classes">Classes</a>
                <ul class="submenu">
                    <li data-section="add-class"><a href="#add-class">Add Class</a></li>
                    <li data-section="student-count-class"><a href="#student-count-class">Student Count</a></li>
                    <li data-section="edit-class"><a href="<?php echo esc_url(home_url('/edit-class-section')); ?>">Edit Class/Section</a></li>
                    <li data-section="delete-class"><a href="<?php echo esc_url(home_url('/delete-class-section')); ?>">Delete Class/Section</a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="reports">
                <span class="icon">📚</span>
                <a href="#studentreports">Student Reports</a>
                <ul class="submenu">
                    <li data-section="add-studentreports"><a href="#add-studentreports">Add Student Reports</a></li>
                    <li data-section="edit-studentreports"><a href="#edit-studentreports">Edit Student Reports</a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="examreports">
                <span class="icon">📚</span>
                <a href="#examreports">Exam Reports</a>
                <ul class="submenu">
                    <li data-section="add-examreports"><a href="#add-examreports">Add Exam Reports</a></li>
                    <li data-section="edit-examreports"><a href="#edit-examreports">Edit Exam Reports</a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="feesreports">
                <span class="icon">📚</span>
                <a href="#feesreports">Fees Reports</a>
                <ul class="submenu">
                    <li data-section="add-feesreports"><a href="#add-feesreports">Add Fees Reports</a></li>
                    <li data-section="edit-feesreports"><a href="#edit-feesreports">Edit Fees Reports</a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="libraryreports">
                <span class="icon">📚</span>
                <a href="#libraryreports">Library</a>
                <ul class="submenu">
                    <li data-section="add-libraryreports"><a href="#add-libraryreports">Add Library Reports</a></li>
                    <li data-section="edit-libraryreports"><a href="#edit-libraryreports">Edit Library Reports</a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="transportreports">
                <span class="icon">📚</span>
                <a href="#transportreports">Transport</a>
                <ul class="submenu">
                    <li data-section="add-transportreports"><a href="#add-transportreports">Add Transport Reports</a></li>
                    <li data-section="edit-transportreports"><a href="#edit-transportreports">Edit Transport Reports</a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="leave">
                <span class="icon">📚</span>
                <a href="#leave">Leave</a>
                <ul class="submenu">
                    <li data-section="add-leave"><a href="#add-leave">Add leave </a></li>
                    <li data-section="edit-leave"><a href="#edit-leave">Edit leave </a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="communicate">
                <span class="icon">📚</span>
                <a href="#communicate">Communicate</a>
                <ul class="submenu">
                    <li data-section="noticeboard-communicate"><a href="#noticeboard-communicate">Notice Board </a></li>
                    <li data-section="sendemails-communicate"><a href="#sendemails-communicate">Send Emails</a></li>
                    <li data-section="event-communicate"><a href="#event-communicate">Event</a></li>
                    <li data-section="calender-communicate"><a href="#calender-communicate">Calender</a></li>
                    <li data-section=""><a href="#">Email Template</a></li>
                    <li data-section=""><a href="#">Sms Template</a></li>
                </ul>
            </li>
            <li class="has-submenu" data-section="adminsection">
                <span class="icon">📚</span>
                <a href="#adminsection">Admin Section</a>
                <ul class="submenu">
                    <li data-section=""><a href="#">Addmission Query</a></li>
                    <li data-section=""><a href="#">Visitors Book</a></li>
                    <li data-section=""><a href="#">Complaints</a></li>
                    <li data-section=""><a href="#">Phone Call Logs</a></li>
                    <li data-section=""><a href="#">Certificates</a></li>
                    <li data-section=""><a href="#">Add Certificates</a></li>
                    <li data-section=""><a href="#">Add ID Card</a></li>
                    <li data-section=""><a href="#">ID cards </a></li>
                </ul>
            </li>
        </ul>
        </div> -->

        <!-- Main Content -->
        <div class="main-content">
            <!-- Dashboard Section (Default Active) -->
            <div id="dashboard" class="section active">
                <?php echo render_dashboard_section($post_id, $logo, $title); ?>
            </div>

        <div id="students" class="section">
            <?php echo render_students_section(); ?>
        </div>
        <div id="add-students" class="section">
            <?php echo render_addstudents_section(); ?>
        </div>
        <div id="edit-students" class="section">
            <?php echo render_editstudents_section(); ?>
        </div>
        <div id="classes" class="section">
            <?php echo render_classes_section(); ?>
        </div>
        <div id="add-class" class="section">
            <?php echo render_addclass_section(); ?>
        </div>
        
        <div id="student-count-class" class="section">
            <?php echo render_student_count_class_section(); ?>
        </div>
     
        </div>
    </div>

    <?php
    return ob_get_clean(); // Return the buffered content
}
add_shortcode('institute_dashboard', 'institute_dashboard_shortcode');

// Function to render the Dashboard section
function render_dashboard_section($post_id, $logo, $title) {
    ob_start();
    ?>
    <div class="institute-dashboard-container">
        <!-- Institute Logo -->
        <div class="institute-logo-container">
            <?php if ($logo): ?>
                <div class="institute-logo" style="position: relative;">
                    <img id="institute-logo-image" src="<?php echo esc_url($logo['url']); ?>" alt="Institute Logo" style="border-radius: 50%; width: 100px; height: 100px; object-fit: cover;">
                    <span class="edit-logo-icon" onclick="document.getElementById('logo-file-input').click();">&#128247;</span>
                </div>
            <?php else: ?>
                <div class="upload-logo-placeholder" onclick="document.getElementById('logo-file-input').click();">
                    <span class="upload-logo-icon">&#128247;</span>
                    <span class="upload-logo-text">Upload Logo</span>
                </div>
            <?php endif; ?>
        </div>

        <h2 style="text-transform:capitalize"><?php echo esc_html($title); ?></h2>

        <!-- Editable Fields -->
        <form method="POST" enctype="multipart/form-data">
            <label for="institute_name">Institute Name</label>
            <input type="text" name="institute_name" value="<?php echo esc_html($title); ?>" required />
<br>
            <label for="mobile_number">Mobile Number</label>
            <input type="text" name="mobile_number" value="<?php echo get_field('mobile_number', $post_id); ?>" required />
            <br>

            <label for="email_id">Email ID</label>
            <input type="email" name="email_id" value="<?php echo get_field('email_id', $post_id); ?>" required />
            <br>

            <!-- Editable Logo File Upload (hidden by default) -->
            <input type="file" name="institute_logo" accept="image/*" id="logo-file-input" style="display: none;" onchange="previewLogo(event)" />

            <input type="submit" name="update_center" value="Update Center" />
        </form>

        <?php
        // Handle form submission
        if (isset($_POST['update_center'])) {
            // Update the data
            $new_institute_name = sanitize_text_field($_POST['institute_name']);
            $post_data = array(
                'ID' => $post_id,
                'post_title' => $new_institute_name,
            );
            wp_update_post($post_data);

            // Update other fields
            update_field('mobile_number', sanitize_text_field($_POST['mobile_number']), $post_id);
            update_field('email_id', sanitize_email($_POST['email_id']), $post_id);

            // Handle logo upload
            if (isset($_FILES['institute_logo']) && $_FILES['institute_logo']['error'] === UPLOAD_ERR_OK) {
                if ($_FILES['institute_logo']['size'] > 1048576) {
                    echo '<p class="error-message">Logo size must be less than 1 MB.</p>';
                } else {
                    $file = $_FILES['institute_logo'];
                    $upload_dir = wp_upload_dir();
                    $target_file = $upload_dir['path'] . '/' . basename($file['name']);

                    if (move_uploaded_file($file['tmp_name'], $target_file)) {
                        $file_type = wp_check_filetype($target_file);
                        $attachment = array(
                            'guid' => $upload_dir['url'] . '/' . basename($file['name']),
                            'post_mime_type' => $file_type['type'],
                            'post_title' => sanitize_file_name($file['name']),
                            'post_content' => '',
                            'post_status' => 'inherit'
                        );

                        $attach_id = wp_insert_attachment($attachment, $target_file, $post_id);
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata($attach_id, $target_file);
                        wp_update_attachment_metadata($attach_id, $attach_data);

                        update_field('institute_logo', $attach_id, $post_id);

                        echo "<script>
                            document.getElementById('institute-logo-image').src = '" . wp_get_attachment_url($attach_id) . "'; 
                        </script>";
                    }
                }
            }
            header("Refresh:0");
            echo '<p class="success-message">Educational Center updated successfully.</p>';
        }
        ?>
    </div>

    <?php
    return ob_get_clean();
}

// Function to render the Students section
function render_students_section() {
    include plugin_dir_path(__FILE__) . 'assets/students.php'; // Ensure the path is correct
    return render_students_section_content(); // Make sure you return the function's output instead of echoing
}
function render_addstudents_section() {
    // return '<div class="classes-section"><h2>Classes</h2><p>Manage your classes here.</p></div>';

    ob_start();

    // Include the addstudents.php file from the plugin's assets folder
    include plugin_dir_path(__FILE__) . 'assets/add-students.php'; // Ensure the path is correct

    // Get the captured HTML content and return it
    return ob_get_clean(); // Make sure you return the function's output instead of echoing
}
function render_editstudents_section() {
    ob_start();
    include plugin_dir_path(__FILE__) . 'assets/edit-students.php'; 
    return ob_get_clean(); 
}

// Function to render the Classes section
function render_classes_section() {
    ob_start();

    // Include the addstudents.php file from the plugin's assets folder
    include plugin_dir_path(__FILE__) . 'assets/classes.php'; // Ensure the path is correct

    // Get the captured HTML content and return it
    return ob_get_clean();}
// Function to render the Classes section

// Function to render the Classes section
function render_addclass_section() {
    ob_start();

    // Include the addstudents.php file from the plugin's assets folder
    include plugin_dir_path(__FILE__) . 'assets/add-classes.php'; // Ensure the path is correct

    // Get the captured HTML content and return it
    return ob_get_clean();}
    function render_student_count_class_section() {
        ob_start();
        include plugin_dir_path(__FILE__) . 'assets/student-count-class.php'; 
        return ob_get_clean(); 
    }
   

    function institute_dashboard_scripts() {
    wp_enqueue_style('institute-dashboard-style', plugin_dir_url(__FILE__) . '/css/style.css');
    wp_enqueue_script('institute-dashboard-script', plugin_dir_url(__FILE__) . '/js/js.js', array('jquery'), null, true);
    wp_add_inline_script('institute-dashboard-script', '
       jQuery(document).ready(function() {
    function showSection(section) {
        jQuery(".section").removeClass("active");
        jQuery("#" + section).addClass("active");
        jQuery(".sidebar li").removeClass("active");
        jQuery("[data-section=" + section + "]").addClass("active");
    }

    var hash = window.location.hash.substring(1);
    if (hash) {
        showSection(hash);
    }

    // Trigger section change when clicking on any part of the <li> element
    jQuery(".sidebar li").click(function() {
        var section = jQuery(this).attr("data-section"); // Get the section from data-section attribute
        window.location.hash = section; // Update the URL hash
        showSection(section); // Show the corresponding section
    });
});

    ');
}
add_action('wp_enqueue_scripts', 'institute_dashboard_scripts');

?>
