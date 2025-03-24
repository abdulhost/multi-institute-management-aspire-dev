<?php
// student-sidebar.php
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
<li class="<?php echo $active_section == 'inventory' ? 'active' : ''; ?>">
            <a href="/student-dashboard?section=inventory">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
                <span>Inventory</span>
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
<?php
// Usage in template
echo '<a href="' . get_secure_logout_url_by_role() . '">Log Out</a>';
?>
    </ul>
    
</nav>
