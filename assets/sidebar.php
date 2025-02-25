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
<!-- <div class="sidebar">
    <div class="logo-title-section">
        <?php if ($logo): ?>
            <div class="institute-logo">
                <img src="<?php echo esc_url($logo['url']); ?>" alt="Institute Logo" style="border-radius: 50%; width: 60px; height: 60px; object-fit: cover;">
            </div>
        <?php endif; ?>
        <h4 class="institute-title"><?php echo esc_html($title); ?></h4>
    </div>

    <ul>
        <li class="<?php echo $active_section == 'dashboard' ? 'active' : ''; ?>" data-section="dashboard">
            <span class="icon">🏠</span>
            <a href="/institute-dashboard/#dashboard">Dashboard</a>
        </li>

        <li class="has-submenu <?php echo $active_section == 'students' ? 'active' : ''; ?>" data-section="students">
            <span class="icon">👨‍🎓</span>
            <a href="/institute-dashboard/#students">Students</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-students' ? 'active' : ''; ?>" data-section="add-students"><a href="/institute-dashboard/#add-students">Add Students</a></li>
                <li class="<?php echo $active_section == 'edit-students' ? 'active' : ''; ?>" data-section="edit-students"><a href="/institute-dashboard/#edit-students">Edit Students</a></li>
            </ul>
        </li>

        <li class="has-submenu <?php echo $active_section == 'classes' ? 'active' : ''; ?>" data-section="classes">
            <span class="icon">📚</span>
            <a href="/institute-dashboard/#classes">Classes</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-class' ? 'active' : ''; ?>" data-section="add-class"><a href="/institute-dashboard/#add-class">Add Class</a></li>
                <li class="<?php echo $active_section == 'student-count-class' ? 'active' : ''; ?>" data-section="student-count-class"><a href="/institute-dashboard/#student-count-class">Student Count</a></li>
                <li class="<?php echo $active_section == 'edit-class' ? 'active' : ''; ?>" data-section="edit-class"><a href="<?php echo esc_url(home_url('/edit-class-section')); ?>">Edit Class/Section</a></li>
                <li class="<?php echo $active_section == 'delete-class' ? 'active' : ''; ?>" data-section="delete-class"><a href="<?php echo esc_url(home_url('/delete-class-section')); ?>">Delete Class/Section</a></li>
            </ul>
        </li>

        <li class="has-submenu <?php echo $active_section == 'attendance' ? 'active' : ''; ?>" data-section="attendance">
            <span class="icon">📊</span>
            <a href="#attendance">Attendance </a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'students-attendance' ? 'active' : ''; ?>" data-section="student-attendance"><a href="<?php echo esc_url(home_url('/attendance-management')); ?>">Students Attendance </a></li>
                <li class="<?php echo $active_section == 'record-attendance' ? 'active' : ''; ?>" data-section="record-attendance"><a href="<?php echo esc_url(home_url('/attendance-entry-form')); ?>">Record Attendance </a></li>
                <li class="<?php echo $active_section == 'teachers-attendance' ? 'active' : ''; ?>" data-section="teachers-attendance"><a href="#teachers-attendance">Teachers Attendance </a></li>
                <li class="<?php echo $active_section == 'fees-reports' ? 'active' : ''; ?>" data-section="fees-reports"><a href="#fees-reports">Attendance </a></li>
            </ul>
        </li>

        <li class="has-submenu <?php echo $active_section == 'reports' ? 'active' : ''; ?>" data-section="reports">
            <span class="icon">📊</span>
            <a href="#reports">Reports</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'student-reports' ? 'active' : ''; ?>" data-section="student-reports"><a href="#student-reports">Student Reports</a></li>
                <li class="<?php echo $active_section == 'exam-reports' ? 'active' : ''; ?>" data-section="exam-reports"><a href="#exam-reports">Exam Reports</a></li>
                <li class="<?php echo $active_section == 'fees-reports' ? 'active' : ''; ?>" data-section="fees-reports"><a href="#fees-reports">Fees Reports</a></li>
            </ul>
        </li>

        <li class="has-submenu <?php echo $active_section == 'library' ? 'active' : ''; ?>" data-section="library">
            <span class="icon">📚</span>
            <a href="#library">Library</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-library' ? 'active' : ''; ?>" data-section="add-library"><a href="#add-library">Add Library</a></li>
                <li class="<?php echo $active_section == 'edit-library' ? 'active' : ''; ?>" data-section="edit-library"><a href="#edit-library">Edit Library</a></li>
            </ul>
        </li>

        <li class="has-submenu <?php echo $active_section == 'transport' ? 'active' : ''; ?>" data-section="transport">
            <span class="icon">🚌</span>
            <a href="#transport">Transport</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-transport' ? 'active' : ''; ?>" data-section="add-transport"><a href="#add-transport">Add Transport</a></li>
                <li class="<?php echo $active_section == 'edit-transport' ? 'active' : ''; ?>" data-section="edit-transport"><a href="#edit-transport">Edit Transport</a></li>
            </ul>
        </li>

        <li class="has-submenu <?php echo $active_section == 'admin' ? 'active' : ''; ?>" data-section="admin">
            <span class="icon">👤</span>
            <a href="#admin">Admin Section</a>
            <ul class="submenu">
                <li class="<?php echo $active_section == 'add-admin' ? 'active' : ''; ?>" data-section="add-admin"><a href="#add-admin">Add Admin</a></li>
                <li class="<?php echo $active_section == 'edit-admin' ? 'active' : ''; ?>" data-section="edit-admin"><a href="#edit-admin">Edit Admin</a></li>
            </ul>
        </li>
    </ul>
</div> -->

<!-- <nav id="sidebar">
    <ul>
      <li>
        <span class="logo">codin</span>
        <button onclick=toggleSidebar() id="toggle-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
        </button>
      </li>
      <li >
        <a href="index.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
          <span>Home</span>
        </a>
      </li>
      <li class="<?php echo $active_section == 'students-attendance' ? 'active' : ''; ?>">
        <a href="<?php echo esc_url(home_url('/attendance-management')); ?>">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M520-640v-160q0-17 11.5-28.5T560-840h240q17 0 28.5 11.5T840-800v160q0 17-11.5 28.5T800-600H560q-17 0-28.5-11.5T520-640ZM120-480v-320q0-17 11.5-28.5T160-840h240q17 0 28.5 11.5T440-800v320q0 17-11.5 28.5T400-440H160q-17 0-28.5-11.5T120-480Zm400 320v-320q0-17 11.5-28.5T560-520h240q17 0 28.5 11.5T840-480v320q0 17-11.5 28.5T800-120H560q-17 0-28.5-11.5T520-160Zm-400 0v-160q0-17 11.5-28.5T160-360h240q17 0 28.5 11.5T440-320v160q0 17-11.5 28.5T400-120H160q-17 0-28.5-11.5T120-160Zm80-360h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/></svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <button onclick=toggleSubMenu(this) class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h207q16 0 30.5 6t25.5 17l57 57h320q33 0 56.5 23.5T880-640v400q0 33-23.5 56.5T800-160H160Zm0-80h640v-400H447l-80-80H160v480Zm0 0v-480 480Zm400-160v40q0 17 11.5 28.5T600-320q17 0 28.5-11.5T640-360v-40h40q17 0 28.5-11.5T720-440q0-17-11.5-28.5T680-480h-40v-40q0-17-11.5-28.5T600-560q-17 0-28.5 11.5T560-520v40h-40q-17 0-28.5 11.5T480-440q0 17 11.5 28.5T520-400h40Z"/></svg>
          <span>Create</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li><a href="#">Folder</a></li>
            <li><a href="#">Document</a></li>
            <li><a href="#">Project</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick=toggleSubMenu(this) class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m221-313 142-142q12-12 28-11.5t28 12.5q11 12 11 28t-11 28L250-228q-12 12-28 12t-28-12l-86-86q-11-11-11-28t11-28q11-11 28-11t28 11l57 57Zm0-320 142-142q12-12 28-11.5t28 12.5q11 12 11 28t-11 28L250-548q-12 12-28 12t-28-12l-86-86q-11-11-11-28t11-28q11-11 28-11t28 11l57 57Zm339 353q-17 0-28.5-11.5T520-320q0-17 11.5-28.5T560-360h280q17 0 28.5 11.5T880-320q0 17-11.5 28.5T840-280H560Zm0-320q-17 0-28.5-11.5T520-640q0-17 11.5-28.5T560-680h280q17 0 28.5 11.5T880-640q0 17-11.5 28.5T840-600H560Z"/></svg>
          <span>Todo-Lists</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li><a href="#">Work</a></li>
            <li><a href="#">Private</a></li>
            <li><a href="#">Coding</a></li>
            <li><a href="#">Gardening</a></li>
            <li><a href="#">School</a></li>
          </div>
        </ul>
      </li>
      <li>
        <a href="calendar.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M200-80q-33 0-56.5-23.5T120-160v-560q0-33 23.5-56.5T200-800h40v-40q0-17 11.5-28.5T280-880q17 0 28.5 11.5T320-840v40h320v-40q0-17 11.5-28.5T680-880q17 0 28.5 11.5T720-840v40h40q33 0 56.5 23.5T840-720v560q0 33-23.5 56.5T760-80H200Zm0-80h560v-400H200v400Zm0-480h560v-80H200v80Zm0 0v-80 80Zm280 240q-17 0-28.5-11.5T440-440q0-17 11.5-28.5T480-480q17 0 28.5 11.5T520-440q0 17-11.5 28.5T480-400Zm-160 0q-17 0-28.5-11.5T280-440q0-17 11.5-28.5T320-480q17 0 28.5 11.5T360-440q0 17-11.5 28.5T320-400Zm320 0q-17 0-28.5-11.5T600-440q0-17 11.5-28.5T640-480q17 0 28.5 11.5T680-440q0 17-11.5 28.5T640-400ZM480-240q-17 0-28.5-11.5T440-280q0-17 11.5-28.5T480-320q17 0 28.5 11.5T520-280q0 17-11.5 28.5T480-240Zm-160 0q-17 0-28.5-11.5T280-280q0-17 11.5-28.5T320-320q17 0 28.5 11.5T360-280q0 17-11.5 28.5T320-240Zm320 0q-17 0-28.5-11.5T600-280q0-17 11.5-28.5T640-320q17 0 28.5 11.5T680-280q0 17-11.5 28.5T640-240Z"/></svg>
          <span>Calendar</span>
        </a>
      </li>
      <li>
        <a href="profile.html">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-240v-32q0-34 17.5-62.5T224-378q62-31 126-46.5T480-440q66 0 130 15.5T736-378q29 15 46.5 43.5T800-272v32q0 33-23.5 56.5T720-160H240q-33 0-56.5-23.5T160-240Zm80 0h480v-32q0-11-5.5-20T700-306q-54-27-109-40.5T480-360q-56 0-111 13.5T260-306q-9 5-14.5 14t-5.5 20v32Zm240-320q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
          <span>Profile</span>
        </a>
      </li>
    </ul>
  </nav> -->

  <!-- <nav id="sidebar">
    <ul>
      <li>
        <span class="logo">codin</span>
        <button onclick="toggleSidebar()" id="toggle-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
        </button>
      </li>
      <li class="<?php echo $active_section == 'dashboard' ? 'active' : ''; ?>">
        <a href="/institute-dashboard/#dashboard">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
          <span>Dashboard</span>
        </a>
      </li>
      <li>
        <button onclick="toggleSubMenu(this)" class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
          <span>Students</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li class="<?php echo $active_section == 'add-students' ? 'active' : ''; ?>"><a href="/institute-dashboard/#add-students">Add Students</a></li>
            <li class="<?php echo $active_section == 'edit-students' ? 'active' : ''; ?>"><a href="/institute-dashboard/#edit-students">Edit Students</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick="toggleSubMenu(this)" class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm0-80h640v-480H160v480Zm0 0v-480 480Z"/></svg>
          <span>Classes</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li class="<?php echo $active_section == 'add-class' ? 'active' : ''; ?>"><a href="/institute-dashboard/#add-class">Add Class</a></li>
            <li class="<?php echo $active_section == 'student-count-class' ? 'active' : ''; ?>"><a href="/institute-dashboard/#student-count-class">Student Count</a></li>
            <li class="<?php echo $active_section == 'edit-class' ? 'active' : ''; ?>"><a href="<?php echo esc_url(home_url('/edit-class-section')); ?>">Edit Class/Section</a></li>
            <li class="<?php echo $active_section == 'delete-class' ? 'active' : ''; ?>"><a href="<?php echo esc_url(home_url('/delete-class-section')); ?>">Delete Class/Section</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'students-attendance' || $active_section == 'record-attendance' || $active_section == 'teachers-attendance' || $active_section == 'fees-reports' ? 'rotate' : ''; ?>">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M520-640v-160q0-17 11.5-28.5T560-840h240q17 0 28.5 11.5T840-800v160q0 17-11.5 28.5T800-600H560q-17 0-28.5-11.5T520-640ZM120-480v-320q0-17 11.5-28.5T160-840h240q17 0 28.5 11.5T440-800v320q0 17-11.5 28.5T400-440H160q-17 0-28.5-11.5T120-480Zm400 320v-320q0-17 11.5-28.5T560-520h240q17 0 28.5 11.5T840-480v320q0 17-11.5 28.5T800-120H560q-17 0-28.5-11.5T520-160Zm-400 0v-160q0-17 11.5-28.5T160-360h240q17 0 28.5 11.5T440-320v160q0 17-11.5 28.5T400-120H160q-17 0-28.5-11.5T120-160Zm80-360h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/></svg>
          <span>Attendance</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu <?php echo $active_section == 'students-attendance' || $active_section == 'record-attendance' || $active_section == 'teachers-attendance' || $active_section == 'fees-reports' ? 'show' : ''; ?>">
          <div>
            <li class="<?php echo $active_section == 'students-attendance' ? 'active' : ''; ?>"><a href="<?php echo esc_url(home_url('/attendance-management')); ?>">Students Attendance</a></li>
            <li class="<?php echo $active_section == 'record-attendance' ? 'active' : ''; ?>"><a href="<?php echo esc_url(home_url('/attendance-entry-form')); ?>">Record Attendance</a></li>
            <li class="<?php echo $active_section == 'teachers-attendance' ? 'active' : ''; ?>"><a href="#teachers-attendance">Teachers Attendance</a></li>
            <li class="<?php echo $active_section == 'fees-reports' ? 'active' : ''; ?>"><a href="#fees-reports">Attendance</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick="toggleSubMenu(this)" class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M320-240q-33 0-56.5-23.5T240-320v-480q0-33 23.5-56.5T320-880h480q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H320Zm0-80h480v-480H320v480ZM160-80q-33 0-56.5-23.5T80-160v-560h80v560h560v80H160Zm160-240Z"/></svg>
          <span>Reports</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li class="<?php echo $active_section == 'student-reports' ? 'active' : ''; ?>"><a href="#student-reports">Student Reports</a></li>
            <li class="<?php echo $active_section == 'exam-reports' ? 'active' : ''; ?>"><a href="#exam-reports">Exam Reports</a></li>
            <li class="<?php echo $active_section == 'fees-reports' ? 'active' : ''; ?>"><a href="#fees-reports">Fees Reports</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick="toggleSubMenu(this)" class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M320-160q-33 0-56.5-23.5T240-240v-480q0-33 23.5-56.5T320-800h480q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H320Zm0-80h480v-480H320v480ZM160-720v-80h80v80h-80Zm0 560v-80h80v80h-80Zm160-640v-80h80v80h-80Zm0 720v-80h80v80h-80Zm320-720v-80h80v80h-80Zm0 720v-80h80v80h-80Zm160-720v-80h80v80h-80Zm0 720v-80h80v80h-80Z"/></svg>
          <span>Library</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li class="<?php echo $active_section == 'add-library' ? 'active' : ''; ?>"><a href="#add-library">Add Library</a></li>
            <li class="<?php echo $active_section == 'edit-library' ? 'active' : ''; ?>"><a href="#edit-library">Edit Library</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick="toggleSubMenu(this)" class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M760-640v-80h80v80h-80Zm0 560v-80h80v80h-80ZM640-760v-80h80v80h-80Zm0 640v-80h80v80h-80ZM520-640v-80h80v80h-80Zm0 560v-80h80v80h-80ZM400-760v-80h80v80h-80Zm0 640v-80h80v80h-80ZM280-640v-80h80v80h-80Zm0 560v-80h80v80h-80ZM160-760v-80h80v80h-80Zm0 640v-80h80v80h-80ZM80-640v-80h80v80h-80Zm0 560v-80h80v80h-80Zm80-640q33 0 56.5-23.5T240-800h480q33 0 56.5 23.5T800-720v480q0 33-23.5 56.5T720-160H240q-33 0-56.5-23.5T160-240v-480Z"/></svg>
          <span>Transport</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li class="<?php echo $active_section == 'add-transport' ? 'active' : ''; ?>"><a href="#add-transport">Add Transport</a></li>
            <li class="<?php echo $active_section == 'edit-transport' ? 'active' : ''; ?>"><a href="#edit-transport">Edit Transport</a></li>
          </div>
        </ul>
      </li>
      <li>
        <button onclick="toggleSubMenu(this)" class="dropdown-btn">
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
          <span>Admin Section</span>
          <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
        </button>
        <ul class="sub-menu">
          <div>
            <li class="<?php echo $active_section == 'add-admin' ? 'active' : ''; ?>"><a href="#add-admin">Add Admin</a></li>
            <li class="<?php echo $active_section == 'edit-admin' ? 'active' : ''; ?>"><a href="#edit-admin">Edit Admin</a></li>
          </div>
        </ul>
      </li>
    </ul>
</nav> -->
<nav id="sidebar">
  <ul>
    <li>
      <div class="logo-title-section">
        <?php if ($logo): ?>
          <div class="institute-logo">
            <img src="<?php echo esc_url($logo['url']); ?>" alt="Institute Logo" style="border-radius: 50%; width: 60px; height: 60px; object-fit: cover;">
          </div>
        <?php endif; ?>
        <h4 class="institute-title" style="margin-bottom:0; margin-left:4px; color: var(--text-clr);"><?php echo esc_html($title); ?></h4>
      </div>
      <button onclick="toggleSidebar()" id="toggle-btn">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
      </button>
    </li>
    <li class="<?php echo $active_section == 'dashboard' ? 'active' : ''; ?>">
      <a href="/institute-dashboard/#dashboard">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M240-200h120v-200q0-17 11.5-28.5T400-440h160q17 0 28.5 11.5T600-400v200h120v-360L480-740 240-560v360Zm-80 0v-360q0-19 8.5-36t23.5-28l240-180q21-16 48-16t48 16l240 180q15 11 23.5 28t8.5 36v360q0 33-23.5 56.5T720-120H560q-17 0-28.5-11.5T520-160v-200h-80v200q0 17-11.5 28.5T400-120H240q-33 0-56.5-23.5T160-200Zm320-270Z"/></svg>
        <span>Dashboard</span>
      </a>
    </li>
    <li>
      <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'add-students' || $active_section == 'edit-students' ? 'rotate' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
        <span>Students</span>
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
      </button>
      <ul class="sub-menu <?php echo $active_section == 'add-students' || $active_section == 'edit-students' ? 'show' : ''; ?>">
        <div>
          <li class="<?php echo $active_section == 'add-students' ? 'active' : ''; ?>"><a href="/institute-dashboard/#add-students">Add Students</a></li>
          <li class="<?php echo $active_section == 'edit-students' ? 'active' : ''; ?>"><a href="/institute-dashboard/#edit-students">Edit Students</a></li>
        </div>
      </ul>
    </li>
    <li>
      <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'add-class' || $active_section == 'student-count-class' || $active_section == 'edit-class' || $active_section == 'delete-class' ? 'rotate' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M160-160q-33 0-56.5-23.5T80-240v-480q0-33 23.5-56.5T160-800h640q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H160Zm0-80h640v-480H160v480Zm0 0v-480 480Z"/></svg>
        <span>Classes</span>
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
      </button>
      <ul class="sub-menu <?php echo $active_section == 'add-class' || $active_section == 'student-count-class' || $active_section == 'edit-class' || $active_section == 'delete-class' ? 'show' : ''; ?>">
        <div>
          <li class="<?php echo $active_section == 'add-class' ? 'active' : ''; ?>"><a href="/institute-dashboard/#add-class">Add Class</a></li>
          <li class="<?php echo $active_section == 'student-count-class' ? 'active' : ''; ?>"><a href="/institute-dashboard/#student-count-class">Student Count</a></li>
          <li class="<?php echo $active_section == 'edit-class' ? 'active' : ''; ?>"><a href="<?php echo esc_url(home_url('/edit-class-section')); ?>">Edit Class/Section</a></li>
          <li class="<?php echo $active_section == 'delete-class' ? 'active' : ''; ?>"><a href="<?php echo esc_url(home_url('/delete-class-section')); ?>">Delete Class/Section</a></li>
        </div>
      </ul>
    </li>
    <li>
      <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'students-attendance' || $active_section == 'record-attendance' || $active_section == 'teachers-attendance' || $active_section == 'fees-reports' ? 'rotate' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M520-640v-160q0-17 11.5-28.5T560-840h240q17 0 28.5 11.5T840-800v160q0 17-11.5 28.5T800-600H560q-17 0-28.5-11.5T520-640ZM120-480v-320q0-17 11.5-28.5T160-840h240q17 0 28.5 11.5T440-800v320q0 17-11.5 28.5T400-440H160q-17 0-28.5-11.5T120-480Zm400 320v-320q0-17 11.5-28.5T560-520h240q17 0 28.5 11.5T840-480v320q0 17-11.5 28.5T800-120H560q-17 0-28.5-11.5T520-160Zm-400 0v-160q0-17 11.5-28.5T160-360h240q17 0 28.5 11.5T440-320v160q0 17-11.5 28.5T400-120H160q-17 0-28.5-11.5T120-160Zm80-360h160v-240H200v240Zm400 320h160v-240H600v240Zm0-480h160v-80H600v80ZM200-200h160v-80H200v80Zm160-320Zm240-160Zm0 240ZM360-280Z"/></svg>
        <span>Attendance</span>
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
      </button>
      <ul class="sub-menu <?php echo $active_section == 'students-attendance' || $active_section == 'record-attendance' || $active_section == 'teachers-attendance' || $active_section == 'fees-reports' ? 'show' : ''; ?>">
        <div>
          <li class="<?php echo $active_section == 'students-attendance' ? 'active' : ''; ?>"><a href="<?php echo esc_url(home_url('/attendance-management')); ?>">Students Attendance</a></li>
          <li class="<?php echo $active_section == 'record-attendance' ? 'active' : ''; ?>"><a href="<?php echo esc_url(home_url('/attendance-entry-form')); ?>">Record Attendance</a></li>
          <li class="<?php echo $active_section == 'teachers-attendance' ? 'active' : ''; ?>"><a href="#teachers-attendance">Teachers Attendance</a></li>
          <li class="<?php echo $active_section == 'fees-reports' ? 'active' : ''; ?>"><a href="#fees-reports">Attendance</a></li>
        </div>
      </ul>
    </li>
    <li>
      <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'student-reports' || $active_section == 'exam-reports' || $active_section == 'fees-reports' ? 'rotate' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M320-240q-33 0-56.5-23.5T240-320v-480q0-33 23.5-56.5T320-880h480q33 0 56.5 23.5T880-800v480q0 33-23.5 56.5T800-240H320Zm0-80h480v-480H320v480ZM160-80q-33 0-56.5-23.5T80-160v-560h80v560h560v80H160Zm160-240Z"/></svg>
        <span>Reports</span>
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
      </button>
      <ul class="sub-menu <?php echo $active_section == 'student-reports' || $active_section == 'exam-reports' || $active_section == 'fees-reports' ? 'show' : ''; ?>">
        <div>
          <li class="<?php echo $active_section == 'student-reports' ? 'active' : ''; ?>"><a href="#student-reports">Student Reports</a></li>
          <li class="<?php echo $active_section == 'exam-reports' ? 'active' : ''; ?>"><a href="#exam-reports">Exam Reports</a></li>
          <li class="<?php echo $active_section == 'fees-reports' ? 'active' : ''; ?>"><a href="#fees-reports">Fees Reports</a></li>
        </div>
      </ul>
    </li>
    <li>
      <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'add-library' || $active_section == 'edit-library' ? 'rotate' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M320-160q-33 0-56.5-23.5T240-240v-480q0-33 23.5-56.5T320-800h480q33 0 56.5 23.5T880-720v480q0 33-23.5 56.5T800-160H320Zm0-80h480v-480H320v480ZM160-720v-80h80v80h-80Zm0 560v-80h80v80h-80Zm160-640v-80h80v80h-80Zm0 720v-80h80v80h-80Zm320-720v-80h80v80h-80Zm0 720v-80h80v80h-80Zm160-720v-80h80v80h-80Zm0 720v-80h80v80h-80Z"/></svg>
        <span>Library</span>
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
      </button>
      <ul class="sub-menu <?php echo $active_section == 'add-library' || $active_section == 'edit-library' ? 'show' : ''; ?>">
        <div>
          <li class="<?php echo $active_section == 'add-library' ? 'active' : ''; ?>"><a href="#add-library">Add Library</a></li>
          <li class="<?php echo $active_section == 'edit-library' ? 'active' : ''; ?>"><a href="#edit-library">Edit Library</a></li>
        </div>
      </ul>
    </li>
    <li>
      <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'add-transport' || $active_section == 'edit-transport' ? 'rotate' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M80-640v-80h80v80h-80Zm0 560v-80h80v80h-80Zm160-640v-80h80v80h-80Zm0 560v-80h80v80h-80Zm320-720v-80h80v80h-80Zm0 560v-80h80v80h-80Zm160-720v-80h80v80h-80Zm0 560v-80h80v80h-80Zm80-640q33 0 56.5-23.5T880-800v480q0 33-23.5 56.5T800-240H320q-33 0-56.5-23.5T240-320v-480q0-33 23.5-56.5T320-880h480Z"/></svg>
        <span>Transport</span>
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
      </button>
      <ul class="sub-menu <?php echo $active_section == 'add-transport' || $active_section == 'edit-transport' ? 'show' : ''; ?>">
        <div>
          <li class="<?php echo $active_section == 'add-transport' ? 'active' : ''; ?>"><a href="#add-transport">Add Transport</a></li>
          <li class="<?php echo $active_section == 'edit-transport' ? 'active' : ''; ?>"><a href="#edit-transport">Edit Transport</a></li>
        </div>
      </ul>
    </li>
    <li>
      <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'add-admin' || $active_section == 'edit-admin' ? 'rotate' : ''; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-480q-66 0-113-47t-47-113q0-66 47-113t113-47q66 0 113 47t47 113q0 66-47 113t-113 47ZM160-160v-80q0-33 23.5-56.5T240-320h480q33 0 56.5 23.5T800-240v80H160Zm80-160h480q0-17-11.5-28.5T680-360H280q-17 0-28.5 11.5T240-320Zm240-240q33 0 56.5-23.5T560-640q0-33-23.5-56.5T480-720q-33 0-56.5 23.5T400-640q0 33 23.5 56.5T480-560Zm0-80Zm0 400Z"/></svg>
        <span>Admin Section</span>
        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
      </button>
      <ul class="sub-menu <?php echo $active_section == 'add-admin' || $active_section == 'edit-admin' ? 'show' : ''; ?>">
        <div>
          <li class="<?php echo $active_section == 'add-admin' ? 'active' : ''; ?>"><a href="#add-admin">Add Admin</a></li>
          <li class="<?php echo $active_section == 'edit-admin' ? 'active' : ''; ?>"><a href="#edit-admin">Edit Admin</a></li>
        </div>
      </ul>
    </li>
  </ul>
</nav>

<!-- Main Content -->
<div class="main-content">
  <!-- Dashboard Section -->
  <div id="dashboard" class="section <?php echo $active_section == 'dashboard' ? 'active' : ''; ?>">
    <?php echo render_dashboard_section($post_id, $logo, $title); ?>
  </div>

  <!-- Students Section -->
  <div id="students" class="section <?php echo $active_section == 'students' ? 'active' : ''; ?>">
    <?php echo render_students_section(); ?>
  </div>
  <!-- Add Students Section -->
  <div id="add-students" class="section <?php echo $active_section == 'add-students' ? 'active' : ''; ?>">
    <?php echo render_addstudents_section(); ?>
  </div>
  <!-- Edit Students Section -->
  <div id="edit-students" class="section <?php echo $active_section == 'edit-students' ? 'active' : ''; ?>">
    <?php echo render_editstudents_section(); ?>
  </div>
  <!-- Classes Section -->
  <div id="classes" class="section <?php echo $active_section == 'classes' ? 'active' : ''; ?>">
    <?php echo render_classes_section(); ?>
  </div>
  <!-- Add Class Section -->
  <div id="add-class" class="section <?php echo $active_section == 'add-class' ? 'active' : ''; ?>">
    <?php echo render_addclass_section(); ?>
  </div>
  <!-- Student Count Class Section -->
  <div id="student-count-class" class="section <?php echo $active_section == 'student-count-class' ? 'active' : ''; ?>">
    <?php echo render_student_count_class_section(); ?>
  </div>
</div>

<?php
// Updated Render Functions
function render_students_section() {
    ob_start();
    $file_path = plugin_dir_path(__FILE__) . 'students.php';
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        echo "<p>Error: students.php not found at $file_path</p>";
    }
    return ob_get_clean();
}

function render_addstudents_section() {
    ob_start();
    $file_path = plugin_dir_path(__FILE__) . 'add-students.php';
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        echo "<p>Error: add-students.php not found at $file_path</p>";
    }
    return ob_get_clean();
}

function render_editstudents_section() {
    ob_start();
    $file_path = plugin_dir_path(__FILE__) . 'edit-students.php';
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        echo "<p>Error: edit-students.php not found at $file_path</p>";
    }
    return ob_get_clean();
}

function render_classes_section() {
    ob_start();
    $file_path = plugin_dir_path(__FILE__) . 'classes.php';
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        echo "<p>Error: classes.php not found at $file_path</p>";
    }
    return ob_get_clean();
}

function render_addclass_section() {
    ob_start();
    $file_path = plugin_dir_path(__FILE__) . 'add-classes.php';
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        echo "<p>Error: add-classes.php not found at $file_path</p>";
    }
    return ob_get_clean();
}

function render_student_count_class_section() {
    ob_start();
    $file_path = plugin_dir_path(__FILE__) . 'student-count-class.php';
    if (file_exists($file_path)) {
        include $file_path;
    } else {
        echo "<p>Error: student-count-class.php not found at $file_path</p>";
    }
    return ob_get_clean();
}