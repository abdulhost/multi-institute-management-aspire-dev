<?php
// Start the session
if (!session_id()) {
    session_start();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $class_name = sanitize_text_field($_POST['class_name']);
    $sections = sanitize_text_field($_POST['sections']);

     // Fetch the current user and their admin_id
     $current_user = wp_get_current_user();
     $admin_id = $current_user->user_login;
 
     // Query the Educational Center based on the admin_id
     $educational_center = get_posts(array(
         'post_type' => 'educational-center',
         'meta_key' => 'admin_id',
         'meta_value' => $admin_id,
         'posts_per_page' => 1, // Limit to 1 post
     ));
 
     // Check if there is an Educational Center for this admin
     if (empty($educational_center)) {
            wp_redirect(home_url('/login'));     
   
     }
 
     $educational_center_id = get_post_meta($educational_center[0]->ID, 'educational_center_id', true);
     if (empty($educational_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    
}

    $insert_result = $wpdb->insert(
        $table_name,
        array(
            'education_center_id' => $educational_center_id,
            'class_name' => $class_name,
            'sections' => $sections
        )
    );
   
    if ($insert_result) {
        $_SESSION['message'] = "<div class='updated'><p>Class and sections added successfully!</p></div>";
    } else {
        $_SESSION['message'] = "<div class='error'><p>There was an error adding the class and sections.</p></div>";
    }

    // Redirect to prevent form resubmission
    wp_redirect($_SERVER['REQUEST_URI']);
    exit;
}

// Retrieve the message from the session
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
// Clear the message from the session
unset($_SESSION['message']);
?>

<div class="wrap">
    <h2>Add New Class and Sections</h2>
    <?php if (!empty($message)) echo $message; ?>
    <form method="post" action="" id="class-form">
        <table class="form-table">
       
            <tr>
                <th scope="row"><label for="class_name">Class Name</label></th>
                <td><input name="class_name" type="text" id="class_name" value="" class="regular-text" required></td>
            </tr>
            <tr>
                <th scope="row"><label for="sections">Sections</label></th>
                <td><input name="sections" type="text" id="sections" value="" class="regular-text" required></td>
            </tr>
        </table>
        <p class="submit"><input type="submit" class="button-primary" value="Add Class"></p>
    </form>
</div>
<?php 
require_once 'search-class-table.php';
?>
<div>
    <?php render_class_table('add_classes'); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reset form fields after submission
    document.getElementById('class-form').reset();

    // Hide the message after 4 seconds
    var messageElement = document.querySelector('.updated, .error');
    if (messageElement) {
        setTimeout(function() {
            messageElement.style.display = 'none';
        }, 4000);
    }
});
</script>