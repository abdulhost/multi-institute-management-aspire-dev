

<!-- addstudents file -->
<!-- Add Student Button -->
<button id="add-student-btn" onclick="toggleForm()">+ Add Student</button>
<?php
global $wpdb;
    $table_name = $wpdb->prefix . 'class_sections';
    $charset_collate = $wpdb->get_charset_collate();

    // Check if the table exists before creating it
    // if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
    //     $sql = "CREATE TABLE $table_name (
    //         id mediumint(9) NOT NULL AUTO_INCREMENT,
    //         class_name varchar(255) NOT NULL,
    //         sections text NOT NULL,
    //         PRIMARY KEY (id)
    //     ) $charset_collate;";

    //     require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    //     dbDelta($sql);
    // }


// Fetch all classes and sections
$class_sections = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
?>

<!-- Add Student Form (Hidden by default) -->
<div id="add-student-form" style="display: none;">
    <h3>Add Student</h3>
    <form method="POST">

  <!-- Class Dropdown -->
  <label for="class_name">Class:</label>
        <select name="class_name" id="class_name" required>
            <option value="">Select Class</option>
            <?php foreach ($class_sections as $row) : ?>
                <option value="<?php echo esc_attr($row['class_name']); ?>"><?php echo esc_html($row['class_name']); ?></option>
            <?php endforeach; ?>
        </select>

        <!-- Section Dropdown -->
        <label for="section">Section:</label>
        <select name="section" id="section" required>
            <option value="">Select Section</option>
            <?php foreach ($class_sections as $row) : ?>
                <?php $sections = explode(',', $row['sections']); ?>
                <?php foreach ($sections as $section) : ?>
                    <option value="<?php echo esc_attr($section); ?>"><?php echo esc_html($section); ?></option>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </select>

    <label for="admission_number">Admission Number</label>
    <input type="text" name="admission_number" required>
        <label for="student_name">Student Full Name</label>
        <input type="text" name="student_name" required>

        <label for="student_email">Student Email</label>
        <input type="email" name="student_email">
        <label for="phone_number">Phone Number</label>
        <input type="text" name="phone_number">
        
        <?php
// Function to display Post Object dropdown
function display_post_object_dropdown($field_name) {
    // Get the Post ID from ACF field
    $post_id = get_field($field_name);

    // Get the "Post Object" field object and parameters dynamically
    $field = get_field_object($field_name);
    $post_type = $field['post_type']; // Get post type(s) from ACF field settings
    
    // Debugging: Check the post types associated with the Post Object field
    echo '<br> Class ';

    // Construct the query arguments dynamically based on the field settings for posts
    $args = array(
        'post_type' => $post_type, // Use the post type from ACF settings
        'posts_per_page' => -1,    // Get all posts
        'post_status' => 'publish',// Only published posts
    );

    // Query for posts and check if there are posts to display
    $posts_query = new WP_Query($args);
    if ($posts_query->have_posts()) {
        echo '<select name="' . $field_name . '" id="' . $field_name . '_dropdown">';
        echo '<option value="">Select a Class</option>'; // Default option

        // Loop through posts and create dropdown options
        while ($posts_query->have_posts()) {
            $posts_query->the_post();
            $selected = ($post_id == get_the_ID()) ? 'selected' : ''; // Check if the current post is selected
            echo '<option value="' . get_the_ID() . '" ' . $selected . '>' . get_the_title() . '</option>';
        }
        echo '</select>';
    } else {
        echo 'No posts found for the Post Object field.<br>';
    }

    // Reset the query after custom WP_Query
    wp_reset_postdata();
}

// Function to display Taxonomy dropdown
function display_taxonomy_dropdown($field_name) {
    // Get the Term ID from ACF field
    $term_id = get_field($field_name);

    // Get the "Taxonomy" field object and parameters dynamically
    $field = get_field_object($field_name);
    $taxonomy = $field['taxonomy']; // Get the taxonomy name from ACF field settings
    
    // Debugging: Check the taxonomy associated with the field
    echo 'Section  ';

    // Query for terms in the specified taxonomy
    $terms = get_terms(array(
        'taxonomy' => $taxonomy,
        'hide_empty' => false,  // Include empty terms
    ));

    if (!is_wp_error($terms) && !empty($terms)) {
        echo '<select name="' . $field_name . '" id="' . $field_name . '_dropdown">';
        echo '<option value="">Section</option>'; // Default option

        // Loop through terms and create dropdown options
        foreach ($terms as $term) {
            $selected = ($term_id == $term->term_id) ? 'selected' : ''; // Check if the current term is selected
            echo '<option value="' . $term->term_id . '" ' . $selected . '>' . $term->name . '</option>';
        }
        echo '</select>';
    } else {
        echo 'No terms found for the Taxonomy field.<br>';
    }
}

// Display both fields using reusable functions
display_post_object_dropdown('field_67acbf1021580');  // Post Object field
display_taxonomy_dropdown('field_67acbf7821581');     // Taxonomy field
?>


        <!-- Hidden field to pass the educational center ID -->
        <input type="hidden" name="educational_center_id" value="<?php echo esc_attr($educational_center_id); ?>">

        <!-- Student ID Field -->
        <label for="student_id">Student ID (Auto-generated)</label>
        <input type="text" name="student_id" value="<?php echo 'STU-' . uniqid(); ?>" readonly>

        <?php
// gender the field key
$field_key = 'field_67ab1ab5978fc'; // ACF field key

// Get the field object to get the options
$field = get_field_object($field_key);

// Make sure we have valid data
if ($field): ?>
<label for="<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field['label']); ?>:</label>
<select id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>">
    <option value="">Select</option>
    <?php
    // Loop through the choices and display them as options
    foreach ($field['choices'] as $value => $label):
        // Remove any unwanted HTML tags (including <br> tags)
        $label = wp_strip_all_tags($label); // Strips all HTML tags
        
        // Check if this option is the selected one
        $selected = ($value == $gender) ? ' selected' : '';
        echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
    endforeach;
    ?>
</select>
<?php else: ?>
<p>No field found.</p>
<?php endif; ?>

<?php
// religion the field key
$field_key = 'field_67ab1b6d978fe'; // ACF field key

// Get the field object to get the options
$field = get_field_object($field_key);

// Make sure we have valid data
if ($field): ?>
<label for="<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field['label']); ?>:</label>
<select id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>">
    <option value="">Select</option>
    <?php
    // Loop through the choices and display them as options
    foreach ($field['choices'] as $value => $label):
        // Remove any unwanted HTML tags (including <br> tags)
        $label = wp_strip_all_tags($label); // Strips all HTML tags
        
        // Check if this option is the selected one
        $selected = ($value == $gender) ? ' selected' : '';
        echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
    endforeach;
    ?>
</select>
<?php else: ?>
<p>No field found.</p>
<?php endif; ?>

<?php
// blood group the field key
$field_key = 'field_67ab1c0197900'; // ACF field key

// Get the field object to get the options
$field = get_field_object($field_key);

// Make sure we have valid data
if ($field): ?>
<label for="<?php echo esc_attr($field_key); ?>"><?php echo esc_html($field['label']); ?>:</label>
<select id="<?php echo esc_attr($field_key); ?>" name="<?php echo esc_attr($field_key); ?>">
    <option value="">Select</option>
    <?php
    // Loop through the choices and display them as options
    foreach ($field['choices'] as $value => $label):
        // Remove any unwanted HTML tags (including <br> tags)
        $label = wp_strip_all_tags($label); // Strips all HTML tags
        
        // Check if this option is the selected one
        $selected = ($value == $gender) ? ' selected' : '';
        echo '<option value="' . esc_attr($value) . '"' . $selected . '>' . esc_html($label) . '</option>';
    endforeach;
    ?>
</select>
<?php else: ?>
<p>No field found.</p>
<?php endif; ?>


<label for="date_of_birth">Date of Birth</label>
<input type="date" name="date_of_birth">



<label for="student_profile_photo">Student Profile Photo</label>
    <input type="file" name="student_profile_photo">


    

        <input type="submit" name="add_student" value="Add Student">
    </form>
</div>

<!-- JavaScript to dynamically populate sections based on selected class -->
<script>
jQuery(document).ready(function($) {
    // Preload sections for all classes
    var sectionsData = {};
    <?php
    foreach ($class_sections as $row) {
        echo 'sectionsData["' . esc_attr($row['class_name']) . '"] = ' . json_encode(explode(',', $row['sections'])) . ';';
    }
    ?>

    // Update sections dropdown when class is selected
    $('#class_name').change(function() {
        var selectedClass = $(this).val();
        var sectionSelect = $('#section');

        if (selectedClass && sectionsData[selectedClass]) {
            sectionSelect.html('<option value="">Select Section</option>');
            sectionsData[selectedClass].forEach(function(section) {
                sectionSelect.append('<option value="' + section + '">' + section + '</option>');
            });
            sectionSelect.prop('disabled', false); // Enable the dropdown
        } else {
            sectionSelect.html('<option value="">Select Class First</option>').prop('disabled', true); // Disable the dropdown
        }
    });

    // Initialize the sections dropdown on page load
    $('#class_name').trigger('change');
});
jQuery(document).ready(function($) {
    // Preload sections for all classes
    var sectionsData = {};
    <?php
    $classes = get_all_classes();
    foreach ($classes as $class) {
        $sections = get_sections_for_class($class->ID);
        echo 'sectionsData[' . $class->ID . '] = [';
        foreach ($sections as $section) {
            echo '{ value: "' . esc_attr($section->slug) . '", label: "' . esc_html($section->name) . '" },';
        }
        echo '];';
    }
    ?>
});
</script>