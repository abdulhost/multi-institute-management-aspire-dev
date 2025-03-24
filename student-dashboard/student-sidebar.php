<?php
// teacher-sidebar.php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

if (!function_exists('wp_get_current_user')) {
    return; // Exit if WordPress isn’t fully loaded
}

$current_user = wp_get_current_user();
$student_id = $current_user->user_login;

$args = [
    'post_type' => 'students',
    'meta_query' => [['key' => 'student_id', 'value' => $student_id, 'compare' => '=']]
];
$students = new WP_Query($args);

if ($students->have_posts()) {
    $students->the_post();
    $post_id = get_the_ID();
    // $logo = get_field('student_profile_photo', $post_id);
    $name = get_field('student_name', $post_id);
    $title = get_the_title($post_id);
} else {
    $logo = '';
    $title = 'student';
}
$avatar = wp_get_attachment_url(get_post_meta($post_id, 'student_profile_photo', true)) ?: 'https://via.placeholder.com/150';
if (!empty($_FILES['student_profile_photo']['name'])) {
  $upload = wp_handle_upload($_FILES['student_profile_photo'], ['test_form' => false]);
  if (isset($upload['file'])) {
      $attachment_id = wp_insert_attachment([
          'guid' => $upload['url'],
          'post_mime_type' => $upload['type'],
          'post_title' => basename($upload['file']),
          'post_content' => '',
          'post_status' => 'inherit'
      ], $upload['file']);
      wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $upload['file']));
      update_post_meta($student->ID, 'student_profile_photo', $attachment_id);
      $avatar = $upload['url'];
  }
}
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
        <li class="<?php echo $active_section == 'overview' ? 'active' : ''; ?>">
            <a href="/student-dashboard">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="<?php echo $active_section == 'profile' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=profile">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Profile</span>
            </a>
        </li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'classes' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-240q66 0 113-47t47-113q0-66-47-113t-113-47q-66 0-113 47t-47 113q0 66 47 113t113 47Zm0-160q-33 0-56.5-23.5T400-400q0-33 23.5-56.5T480-480q33 0 56.5 23.5T560-400q0 33-23.5 56.5T480-320q-33 0-56.5-23.5T400-400q0-33 23.5-56.5T480-480Z"/></svg>
    <span>Classes</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'classes' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'classes' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('/student-dashboard/?section=classes'); ?>">Manage Classes</a></li>
      <li class="<?php echo $active_action == 'add-class' ? 'active' : ''; ?>"><a href="<?php echo home_url('/teacher-dashboard/?section=classes&action=add-class'); ?>">Add Class</a></li>
      <li class="<?php echo $active_action == 'edit-class' ? 'active' : ''; ?>"><a href="<?php echo home_url('/teacher-dashboard/?section=classes&action=edit-class'); ?>">Edit Class</a></li>
      <li class="<?php echo $active_action == 'delete-class' ? 'active' : ''; ?>"><a href="<?php echo home_url('/teacher-dashboard/?section=classes&action=delete-class'); ?>">Delete Class</a></li>
    </div>
  </ul>
</li>
<li class="<?php echo $active_section == 'timetable' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=timetable">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Timetable</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'attendance' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=attendance">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Attendance</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'exams' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=exams">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Exam</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'result' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=result">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Result</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'report' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=report">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Report</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'homework' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=homework">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Homework</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'communication' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=communication">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Communication</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'noticeboard' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=noticeboard">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Noticeboard</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'library' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=library">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Library</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'fees' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=fees">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Fees</span>
            </a>
        </li>
        <li>
<li>
<li class="<?php echo $active_section == 'transport' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=transport">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Transport</span>
            </a>
        </li>
        <li>
<li>


  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'students' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-240q66 0 113-47t47-113q0-66-47-113t-113-47q-66 0-113 47t-47 113q0 66 47 113t113 47Zm0-160q-33 0-56.5-23.5T400-400q0-33 23.5-56.5T480-480q33 0 56.5 23.5T560-400q0 33-23.5 56.5T480-320Z"/></svg>
    <span>Students</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'students' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'students' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('/teacher-dashboard/?section=students'); ?>">Manage Students</a></li>
      <li class="<?php echo $active_action == 'add-student' ? 'active' : ''; ?>"><a href="<?php echo home_url('/teacher-dashboard/?section=students&action=add-student'); ?>">Add Student</a></li>
      <li class="<?php echo $active_action == 'edit-student' ? 'active' : ''; ?>"><a href="<?php echo home_url('/teacher-dashboard/?section=students&action=edit-student'); ?>">Edit Student</a></li>
      <li class="<?php echo $active_action == 'delete-student' ? 'active' : ''; ?>"><a href="<?php echo home_url('/teacher-dashboard/?section=students&action=delete-student'); ?>">Delete Student</a></li>
    </div>
  </ul>
</li>
<!-- Homework Menu Item -->
<!-- Homework Menu Item -->
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'homework' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-240q66 0 113-47t47-113q0-66-47-113t-113-47q-66 0-113 47t-47 113q0 66 47 113t113 47Zm0-160q-33 0-56.5-23.5T400-400q0-33 23.5-56.5T480-480q33 0 56.5 23.5T560-400q0 33-23.5 56.5T480-320q-33 0-56.5-23.5T400-400q0-33 23.5-56.5T480-480Z"/></svg>
    <span>Homework</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'homework' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'homework' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=homework'); ?>">Homework Assignments</a>
      </li>
      <li class="<?php echo $active_action == 'add-homework' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=homework&action=add-homework'); ?>">Add Homework</a>
      </li>
      <li class="<?php echo $active_action == 'edit-homework' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=homework&action=edit-homework&id=' . (isset($_GET['id']) ? intval($_GET['id']) : '')); ?>">Edit Homework</a>
      </li>
      <li class="<?php echo $active_action == 'delete-homework' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=homework&action=delete-homework&id=' . (isset($_GET['id']) ? intval($_GET['id']) : '')); ?>">Delete Homework</a>
      </li>
    </div>
  </ul>
</li>
<!-- Exams Menu Item -->
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'exams' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-240q66 0 113-47t47-113q0-66-47-113t-113-47q-66 0-113 47t-47 113q0 66 47 113t113 47Zm0-160q-33 0-56.5-23.5T400-400q0-33 23.5-56.5T480-480q33 0 56.5 23.5T560-400q0 33-23.5 56.5T480-320q-33 0-56.5-23.5T400-400q0-33 23.5-56.5T480-480ZM240-120q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47Zm0-160q-33 0-56.5 23.5T160-280q0 33 23.5 56.5T240-200q33 0 56.5-23.5T320-280q0-33-23.5-56.5T240-360Z"/></svg>
    <span>Exams</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'exams' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'exams' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=exams'); ?>">Manage Exams</a>
      </li>
      <li class="<?php echo $active_action == 'add-exam' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=exams&action=add-exam'); ?>">Add Exam</a>
      </li>
      <li class="<?php echo $active_action == 'edit-exam' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=exams&action=edit-exam&action=edit-exam'); ?>">Edit Exam</a>
      </li>
      <li class="<?php echo $active_action == 'delete-exam' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=exams&action=delete-exam'); ?>">Delete Exam</a>
      </li>
      <li class="<?php echo $active_action == 'add-exam-subjects' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=exams&action=add-exam-subjects'); ?>">Add Exam Subjects</a>
      </li>
      <li class="<?php echo $active_action == 'results' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=exams&action=results'); ?>">Exam Results</a>
      </li>
    </div>
  </ul>
</li>

<!-- Communication Menu Item -->
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'communication' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M720-320q-28 0-47-19t-19-47v-320q0-28 19-47t47-19h320q28 0 47 19t19 47v320q0 28-19 47t-47 19H720Zm0-40h320V-400H720v320Zm0 0q28 0 47-19t19-47v-320q0-28-19-47t-47-19H720q-28 0-47 19t-19 47v320q0 28 19 47t47 19Z"/></svg>
    <span>Communication</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'communication' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'communication' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=communication'); ?>">Messages</a>
      </li>
      <li class="<?php echo $active_action == 'view-chat' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=communication' . (isset($_GET['id']) ? intval($_GET['id']) : '')); ?>">Chat</a>
      </li>
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'noticeboard' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M720-320q-28 0-47-19t-19-47v-320q0-28 19-47t47-19h320q28 0 47 19t19 47v320q0 28-19 47t-47 19H720Zm0-40h320V-400H720v320Zm0 0q28 0 47-19t19-47v-320q0-28-19-47t-47-19H720q-28 0-47 19t-19 47v320q0 28 19 47t47 19Z"/></svg>
    <span>Notice Board</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'noticeboard' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'noticeboard' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=noticeboard'); ?>">View Notices</a>
      </li>
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'attendance' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Attendance</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'attendance' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'attendance' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=attendance'); ?>">Manage Attendance</a>
      </li>
      <li class="<?php echo $active_action == 'add-attendance' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=attendance&action=add-attendance'); ?>">Add Attendance</a>
      </li>
      <li class="<?php echo $active_action == 'edit-attendance' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=attendance&action=edit-attendance'); ?>">Edit Attendance</a>
      </li>
      <li class="<?php echo $active_action == 'bulk-import' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=attendance&action=bulk-import'); ?>">Bulk Import Attendance</a>
      </li>
      <li class="<?php echo $active_action == 'export-attendance' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=attendance&action=export-attendance'); ?>">Export Attendance</a>
      </li>
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'subjects' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Subjects</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'subjects' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'subjects' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=subjects'); ?>">Manage Subjects</a>
      </li>
      <li class="<?php echo $active_action == 'add-subjects' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=subjects&action=add-subjects'); ?>">Add Subjects</a>
      </li>
      <li class="<?php echo $active_action == 'edit-subjects' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=subjects&action=edit-subjects'); ?>">Edit Subjects</a>
      </li>
      <li class="<?php echo $active_action == 'delete-subjects' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=subjects&action=delete-subjects'); ?>">Delete Subjects</a>
      </li>
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'parents' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Parents</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'parents' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'parents' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=parents'); ?>">Manage parents</a>
      </li>
      <li class="<?php echo $active_action == 'add-parents' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=parents&action=add-parents'); ?>">Add Parents</a>
      </li>
      <li class="<?php echo $active_action == 'edit-parents' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=parents&action=edit-parents'); ?>">Edit Parents</a>
      </li>
      <li class="<?php echo $active_action == 'delete-parents' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=parents&action=delete-parents'); ?>">Delete Parents</a>
      </li>
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'fees' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Fees</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'fees' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'fees' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=fees'); ?>">Fee Details</a>
      </li>
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'transport-fees' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Transport Fees</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'transport-fees' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'transport-fees' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=transport-fees'); ?>">Fee Details</a>
      </li>
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'transport-enrollments' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Transport Enrollments</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'transport-enrollments' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'transport-enrollments' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=transport-enrollments'); ?>">Transport Enrollments</a>
      </li>
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'view-reports' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Reports</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'view-reports' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'view-reports' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=view-reports'); ?>">Reports</a>
      </li>
      <li class="<?php echo $active_section == 'generate-reports' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=generate-reports'); ?>">Generate Reports</a>
      </li>
    
    </div>
  </ul>
</li>

<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'timetable' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Timetable</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'timetable' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'timetable' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=timetable'); ?>">Manage Timetable</a>
      </li>
      <li class="<?php echo $active_action == 'add-timetable' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=timetable&action=add-timetable'); ?>">Add Timetable</a>
      </li>
      <li class="<?php echo $active_action == 'edit-timetable' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=timetable&action=edit-timetable'); ?>">Edit Timetable</a>
      </li>
      <li class="<?php echo $active_action == 'delete-timetable' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=timetable&action=delete-timetable'); ?>">Delete Timetable</a>
      </li>
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'department' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Department</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'department' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'timetable' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=department'); ?>">Manage Department</a>
      </li>
      <li class="<?php echo $active_action == 'add-department' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=department&action=add-department'); ?>">Add Department</a>
      </li>
      <li class="<?php echo $active_action == 'edit-timetable' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=department&action=edit-department'); ?>">Edit Department</a>
      </li>
      <li class="<?php echo $active_action == 'delete-timetable' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=department&action=delete-department'); ?>">Delete Department</a>
      </li>
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'inventory' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Inventory</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'inventory' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'timetable' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=inventory'); ?>">Manage Inventory</a>
      </li> 
       <li class="<?php echo $active_action == 'inventory-issued' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=inventory&action=inventory-issued'); ?>">Inventory Issued</a>
      </li>
       <li class="<?php echo $active_action == 'inventory-transaction' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=inventory&action=inventory-transaction'); ?>">Inventory Transaction</a>
      </li>
      <li class="<?php echo $active_action == 'add-inventory' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=inventory&action=add-inventory'); ?>">Add Inventory</a>
      </li>
      <li class="<?php echo $active_action == 'edit-inventory' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=inventory&action=edit-inventory'); ?>">Edit Inventory</a>
      </li>
    
      
    
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'library' ? 'rotate' : ''; ?>">
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M736-320q0 28-19 47t-47 19H280q-28 0-47-19t-19-47V280q0-28 19-47t47-19h320q28 0 47 19t19 47v400Z"/>
    </svg>
    <span>Library</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
      <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
    </svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'library' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'timetable' && empty($active_action) ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=library'); ?>">Manage Library</a>
      </li> 
      
       <li class="<?php echo $active_action == 'library-transaction' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=library&action=library-transaction'); ?>">Library Transaction</a>
      </li>
       <li class="<?php echo $active_action == 'library-issued' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=library&action=library-issued'); ?>">Library Overdue</a>
      </li>
      <li class="<?php echo $active_action == 'add-library' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=library&action=add-library'); ?>">Add Library</a>
      </li>
      <li class="<?php echo $active_action == 'edit-library' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=library&action=edit-library'); ?>">Edit Library</a>
      </li>
      <li class="<?php echo $active_action == 'delete-library' ? 'active' : ''; ?>">
        <a href="<?php echo home_url('/teacher-dashboard/?section=library&action=delete-library'); ?>">Delete Library</a>
      </li>
    
    </div>
  </ul>
</li>

<?php
// Usage in template
echo '<a href="' . get_secure_logout_url_by_role() . '">Log Out</a>';
?>
    </ul>
    
</nav>
