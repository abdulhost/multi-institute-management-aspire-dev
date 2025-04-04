<?php
// student-sidebar.php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

if (!function_exists('wp_get_current_user')) {
    return; // Exit if WordPress isn’t fully loaded
}

$current_user = wp_get_current_user();
$parent_id = $current_user->user_login;

$args = [
    'post_type' => 'parent',
    'meta_query' => [['key' => 'parent_id', 'value' => $parent_id, 'compare' => '=']]
];
$parents = new WP_Query($args);

if ($parents->have_posts()) {
    $parents->the_post();
    $post_id = get_the_ID();
    $name = get_field('parent_name', $post_id);
    $title = get_the_title($post_id);
} else {
    $post_id = '';
    $title = 'Parent';
}
$avatar = wp_get_attachment_url(get_post_meta($post_id, 'parent_profile_photo', true)) ?: 'https://via.placeholder.com/150';
$active_section = isset($active_section) ? $active_section : 'overview';
?>

<nav id="sidebar">
    <ul>
        <li>
            <div class="logo-title-section">
                <?php if ($avatar): ?>
                    <div class="institute-logo">
                        <img src="<?php echo esc_url($avatar); ?>" alt="Avatar" style="border-radius: 50%; width: 60px; height: 60px; object-fit: cover;" class="profile-avatar mb-3">
                    </div>
                <?php endif; ?>
                <h4 class="institute-title" style="margin-bottom:0; margin-left:4px; color: var(--text-clr);"><?php echo esc_html($name); ?></h4>
            </div>
            <button onclick="toggleSidebar()" id="toggle-btn">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
            </button>
        </li>
        <!-- Only the <li> items from the second sidebar with their URLs, active sections, and span texts -->
        <li class="<?php echo $active_section === 'overview' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard')); ?>">
                <i class="fas fa-tachometer-alt" style="color: #e8eaed;"></i>
                <span>Overview</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'centers' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=centers')); ?>">
                <i class="fas fa-school" style="color: #e8eaed;"></i>
                <span>Educational Centers</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'teacher' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=teacher')); ?>">
                <i class="fas fa-user-friends" style="color: #e8eaed;"></i>
                <span>Teacher</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'students' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=students')); ?>">
                <i class="fas fa-user-friends" style="color: #e8eaed;"></i>
                <span>Students</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'staff' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=staff')); ?>">
                <i class="fas fa-user-friends" style="color: #e8eaed;"></i>
                <span>Staff</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'roles' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=roles')); ?>">
                <i class="fas fa-user-shield" style="color: #e8eaed;"></i>
                <span>Roles & Permissions</span>
            </a>
        </li>
       
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'classes' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Classes & Sections</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'classes' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'classes' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=classes'); ?>">Classes & Sections</a></li>
      <li class="<?php echo $active_action == 'add-class' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=classes&action=add-class'); ?>">Add Class</a></li>
      <li class="<?php echo $active_action == 'edit-class' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=classes&action=edit-class'); ?>">Edit Class</a></li>
      <li class="<?php echo $active_action == 'delete-class' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=classes&action=delete-class'); ?>">Delete Class</a></li>
      <li class="<?php echo $active_action == 'student-count' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=classes&action=student-count'); ?>">Student Count</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'exams' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Exams</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'exams' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'exams' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams'); ?>">Classes & Sections</a></li>
      <li class="<?php echo $active_action == 'add-exams' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=add-exams'); ?>">Add Class</a></li>
      <li class="<?php echo $active_action == 'edit-exams' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=edit-exams'); ?>">Edit Class</a></li>
      <li class="<?php echo $active_action == 'delete-exams' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=delete-exams'); ?>">Delete Class</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'results' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Results</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'results' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'results' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=results'); ?>">Results</a></li>
      <li class="<?php echo $active_action == 'add-exams' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=add-exams'); ?>">Add Class</a></li>
      <li class="<?php echo $active_action == 'edit-exams' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=edit-exams'); ?>">Edit Class</a></li>
      <li class="<?php echo $active_action == 'delete-exams' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=delete-exams'); ?>">Delete Class</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'library' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Library</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'library' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'library' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=library'); ?>">Library</a></li>
      <li class="<?php echo $active_action == 'add-library' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=add-exams'); ?>">Add Class</a></li>
      <li class="<?php echo $active_action == 'edit-exams' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=edit-exams'); ?>">Edit Class</a></li>
      <li class="<?php echo $active_action == 'delete-exams' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=exams&action=delete-exams'); ?>">Delete Class</a></li>
    </div>
  </ul>
</li>

      
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'attendance' ? 'rotate' : ''; ?>">
  <i class="fas fa-check-square" style="color: #e8eaed;"></i>
      <span>Attendance</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'attendance' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'attendance' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=attendance'); ?>">Students Attendance</a></li>
      <li class="<?php echo $active_action == 'teachers-attendance' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=attendance&action=teachers-attendance'); ?>">Teachers Attendance</a></li>
      <li class="<?php echo $active_action == 'staff-attendance' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=attendance&action=staff-attendance'); ?>">Staff Attendance</a></li>
    </div>
  </ul>
</li>


        <li class="<?php echo $active_section === 'timetable' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=timetable')); ?>">
                <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>
                <span>Timetable</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'reports' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=reports')); ?>">
                <i class="fas fa-file-alt" style="color: #e8eaed;"></i>
                <span>Reports</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'notifications' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=notifications')); ?>">
                <i class="fas fa-bell" style="color: #e8eaed;"></i>
                <span>Notifications</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'settings' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=settings')); ?>">
                <i class="fas fa-cog" style="color: #e8eaed;"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'audit' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=audit')); ?>">
                <i class="fas fa-history" style="color: #e8eaed;"></i>
                <span>Audit Logs</span>
            </a>
        </li>
        <li class="<?php echo $active_section === 'support' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=support')); ?>">
                <i class="fas fa-life-ring" style="color: #e8eaed;"></i>
                <span>Support</span>
            </a>
        </li>
    </ul>
</nav>

<?php wp_reset_postdata(); ?>