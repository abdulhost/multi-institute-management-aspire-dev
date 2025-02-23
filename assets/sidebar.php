<?php
// Start the sidebar
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
    $logo = '';
    $title = 'No Institute Found';
}

// Get the active section from the passed parameter (default to 'dashboard' if not set)
$active_section = isset($active_section) ? $active_section : 'dashboard';
?>

<!-- Sidebar -->
<div class="sidebar">
    <div class="logo-title-section">
        <!-- Institute Logo -->
        <?php if ($logo): ?>
            <div class="institute-logo">
                <img src="<?php echo esc_url($logo['url']); ?>" alt="Institute Logo" style="border-radius: 50%; width: 60px; height: 60px; object-fit: cover;">
            </div>
        <?php endif; ?>
        <!-- Institute Title -->
        <h4 class="institute-title"><?php echo esc_html($title); ?></h4>
    </div>

    <ul>
        <!-- Dashboard -->
        <li class="<?php echo $active_section == 'dashboard' ? 'active' : ''; ?>" data-section="dashboard">
            <span class="icon">🏠</span>
            <a href="#dashboard">Dashboard</a>
        </li>

        <!-- Students -->
        <li class="has-submenu <?php echo $active_section == 'students' ? 'active' : ''; ?>" data-section="students">
            <span class="icon">👨‍🎓</span>
            <a href="#students">Students</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-students' ? 'active' : ''; ?>" data-section="add-students"><a href="#add-students">Add Students</a></li>
                <li class="<?php echo $active_section == 'edit-students' ? 'active' : ''; ?>" data-section="edit-students"><a href="#edit-students">Edit Students</a></li>
            </ul>
        </li>

        <!-- Classes -->
        <li class="has-submenu <?php echo $active_section == 'classes' ? 'active' : ''; ?>" data-section="classes">
            <span class="icon">📚</span>
            <a href="#classes">Classes</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-class' ? 'active' : ''; ?>" data-section="add-class"><a href="#add-class">Add Class</a></li>
                <li class="<?php echo $active_section == 'student-count-class' ? 'active' : ''; ?>" data-section="student-count-class"><a href="#student-count-class">Student Count</a></li>
                <li class="<?php echo $active_section == 'edit-class' ? 'active' : ''; ?>" data-section="edit-class"><a href="<?php echo esc_url(home_url('/edit-class-section')); ?>">Edit Class/Section</a></li>
                <li class="<?php echo $active_section == 'delete-class' ? 'active' : ''; ?>" data-section="delete-class"><a href="#delete-class">Delete Class/Section</a></li>
            </ul>
        </li>

        <!-- Reports -->
        <li class="has-submenu <?php echo $active_section == 'reports' ? 'active' : ''; ?>" data-section="reports">
            <span class="icon">📊</span>
            <a href="#reports">Reports</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'student-reports' ? 'active' : ''; ?>" data-section="student-reports"><a href="#student-reports">Student Reports</a></li>
                <li class="<?php echo $active_section == 'exam-reports' ? 'active' : ''; ?>" data-section="exam-reports"><a href="#exam-reports">Exam Reports</a></li>
                <li class="<?php echo $active_section == 'fees-reports' ? 'active' : ''; ?>" data-section="fees-reports"><a href="#fees-reports">Fees Reports</a></li>
            </ul>
        </li>

        <!-- Library -->
        <li class="has-submenu <?php echo $active_section == 'library' ? 'active' : ''; ?>" data-section="library">
            <span class="icon">📚</span>
            <a href="#library">Library</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-library' ? 'active' : ''; ?>" data-section="add-library"><a href="#add-library">Add Library</a></li>
                <li class="<?php echo $active_section == 'edit-library' ? 'active' : ''; ?>" data-section="edit-library"><a href="#edit-library">Edit Library</a></li>
            </ul>
        </li>

        <!-- Transport -->
        <li class="has-submenu <?php echo $active_section == 'transport' ? 'active' : ''; ?>" data-section="transport">
            <span class="icon">🚌</span>
            <a href="#transport">Transport</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-transport' ? 'active' : ''; ?>" data-section="add-transport"><a href="#add-transport">Add Transport</a></li>
                <li class="<?php echo $active_section == 'edit-transport' ? 'active' : ''; ?>" data-section="edit-transport"><a href="#edit-transport">Edit Transport</a></li>
            </ul>
        </li>

        <!-- Admin Section -->
        <li class="has-submenu <?php echo $active_section == 'admin' ? 'active' : ''; ?>" data-section="admin">
            <span class="icon">👤</span>
            <a href="#admin">Admin Section</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-admin' ? 'active' : ''; ?>" data-section="add-admin"><a href="#add-admin">Add Admin</a></li>
                <li class="<?php echo $active_section == 'edit-admin' ? 'active' : ''; ?>" data-section="edit-admin"><a href="#edit-admin">Edit Admin</a></li>
            </ul>
        </li>
    </ul>
</div>