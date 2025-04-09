<?php
// sidebar.php
if (!defined('ABSPATH')) {
    exit; // Prevent direct access
}

if (!function_exists('wp_get_current_user')) {
    return; // Exit if WordPress isn’t fully loaded
}

$current_user = wp_get_current_user();

$active_section = isset($active_section) ? $active_section : 'overview';
?>

<nav id="sidebar">
    <ul>
        <li>
            <div class="logo-title-section">
    <div class="institute-logo">
        <img src="<?php echo plugin_dir_url(__FILE__) . '../logo instituto.jpg'; ?>" alt="Avatar" style="border-radius: 50%; width: 60px; height: 60px; object-fit: cover;" class="profile-avatar mb-3">
    </div>
    <h4 class="institute-title" style="margin-bottom:0; margin-left:4px; color: var(--text-clr);">Super Admin</h4>
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
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'subscription' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Subscriptions</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'subscription' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'subscription' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subscription'); ?>">Subscription</a></li>
      <li class="<?php echo $active_action == 'add-subscription' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subscription&action=add-subscription'); ?>">Add Subscription</a></li>
      <li class="<?php echo $active_action == 'edit-subscription' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subscription&action=edit-subscription'); ?>">Edit Subscription</a></li>
      <li class="<?php echo $active_action == 'delete-subscription' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subscription&action=delete-subscription'); ?>">Delete Subscription</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'payment_methods' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Payment Methods</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'payment_methods' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'payment_methods' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=payment_methods'); ?>">Payment Methods</a></li>
      <li class="<?php echo $active_action == 'add-payment_methods' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=payment_methods&action=add-payment_methods'); ?>">Add Payment Methods</a></li>
      <li class="<?php echo $active_action == 'edit-payment_methods' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=payment_methods&action=edit-payment_methods'); ?>">Edit Payment Methods</a></li>
      <li class="<?php echo $active_action == 'delete-payment_methods' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=payment_methods&action=delete-payment_methods'); ?>">Delete Payment Methods</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'subscription_plans' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Subscription Plans</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'subscription_plans' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'subscription_plans' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subscription_plans'); ?>">Subscription Plans</a></li>
      <li class="<?php echo $active_action == 'add-subscription_plans' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subscription_plans&action=add-subscription_plans'); ?>">Add Subscription Plans</a></li>
      <li class="<?php echo $active_action == 'edit-subscription_plans' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subscription_plans&action=edit-subscription_plans'); ?>">Edit Subscription Plans</a></li>
      <li class="<?php echo $active_action == 'delete-subscription_plans' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subscription_plans&action=delete-subscription_plans'); ?>">Delete Subscription Plans</a></li>
    </div>
  </ul>
</li>



        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'teacher' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Teacher</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'teacher' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'teacher' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=teacher'); ?>">teacher</a></li>
      <li class="<?php echo $active_action == 'add-teacher' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=teacher&action=add-teacher'); ?>">Add teacher</a></li>
      <li class="<?php echo $active_action == 'edit-teacher' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=teacher&action=edit-teacher'); ?>">Edit teacher</a></li>
      <li class="<?php echo $active_action == 'delete-teacher' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=teacher&action=delete-teacher'); ?>">Delete teacher</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'students' ? 'rotate' : ''; ?>">
  <i class="fas fa-user-friends" style="color: #e8eaed;"></i>
  <span>Students</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'students' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'students' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=students'); ?>">Students</a></li>
      <li class="<?php echo $active_action == 'add-students' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=students&action=add-students'); ?>">Add Students</a></li>
      <li class="<?php echo $active_action == 'edit-students' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=students&action=edit-students'); ?>">Edit Students</a></li>
      <li class="<?php echo $active_action == 'delete-students' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=students&action=delete-students'); ?>">Delete Students</a></li>
    </div>
  </ul>
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
      <li class="<?php echo $active_action == 'add-library' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=library&action=add-library'); ?>">Add Library</a></li>
      <li class="<?php echo $active_action == 'edit-library' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=library&action=edit-library'); ?>">Edit Library</a></li>
      <li class="<?php echo $active_action == 'delete-library' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=library&action=delete-library'); ?>">Delete Library</a></li>
      <li class="<?php echo $active_action == 'transaction-library' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=library&action=transaction-library'); ?>">Transaction Library</a></li>
      <li class="<?php echo $active_action == 'overdue-library' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=library&action=overdue-library'); ?>">Overdue Library</a></li>
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
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'timetable' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Timetable</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'timetable' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'timetable' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=timetable'); ?>">Timetable</a></li>
      <li class="<?php echo $active_action == 'add-timetable' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=timetable&action=add-timetable'); ?>">Add Timetable</a></li>
      <li class="<?php echo $active_action == 'edit-timetable' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=timetable&action=edit-timetable'); ?>">Edit Timetable</a></li>
      <li class="<?php echo $active_action == 'delete-timetable' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=timetable&action=delete-timetable'); ?>">Delete Timetable</a></li>
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'subjects' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Subjects</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'subjects' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'subjects' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subjects'); ?>">Subjects</a></li>
      <li class="<?php echo $active_action == 'add-subjects' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subjects&action=add-subjects'); ?>">Add Subjects</a></li>
      <li class="<?php echo $active_action == 'edit-subjects' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subjects&action=edit-subjects'); ?>">Edit Subjects</a></li>
      <li class="<?php echo $active_action == 'delete-subjects' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=subjects&action=delete-subjects'); ?>">Delete Subjects</a></li>
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'homework' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Homework</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'homework' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'homework' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=homework'); ?>">Homework</a></li>
      <li class="<?php echo $active_action == 'add-homework' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=homework&action=add-homework'); ?>">Add Homework</a></li>
      <li class="<?php echo $active_action == 'edit-homework' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=homework&action=edit-homework'); ?>">Edit Homework</a></li>
      <li class="<?php echo $active_action == 'delete-homework' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=homework&action=delete-homework'); ?>">Delete Homework</a></li>
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'department' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Department</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'department' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'department' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=department'); ?>">Department</a></li>
      <li class="<?php echo $active_action == 'add-department' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=department&action=add-department'); ?>">Add Department</a></li>
      <li class="<?php echo $active_action == 'edit-department' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=department&action=edit-department'); ?>">Edit Department</a></li>
      <li class="<?php echo $active_action == 'delete-department' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=department&action=delete-department'); ?>">Delete Department</a></li>
    </div>
  </ul>
</li>
<li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'parents' ? 'rotate' : ''; ?>">
  <i class="fas fa-calendar-alt" style="color: #e8eaed;"></i>    <span>Parents</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'parents' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'parents' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=parents'); ?>">Parents</a></li>
      <li class="<?php echo $active_action == 'add-parents' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=parents&action=add-parents'); ?>">Add Parents</a></li>
      <li class="<?php echo $active_action == 'edit-parents' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=parents&action=edit-parents'); ?>">Edit Parents</a></li>
      <li class="<?php echo $active_action == 'delete-parents' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=parents&action=delete-parents'); ?>">Delete Parents</a></li>
    </div>
  </ul>
</li>

      
        <li class="<?php echo $active_section === 'reports' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=reports')); ?>">
                <i class="fas fa-file-alt" style="color: #e8eaed;"></i>
                <span>Reports</span>
            </a>
        </li>

        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'inventory' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Inventory</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'inventory' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'inventory' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory'); ?>">Inventory</a></li>
      <li class="<?php echo $active_action == 'add-inventory' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory&action=add-inventory'); ?>">Add Inventory</a></li>
      <li class="<?php echo $active_action == 'edit-inventory' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory&action=edit-inventory'); ?>">Edit Inventory</a></li>
      <li class="<?php echo $active_action == 'delete-inventory' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory&action=delete-inventory'); ?>">Delete Inventory</a></li>
      <li class="<?php echo $active_action == 'view-transaction-inventory' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory&action=view-transaction-inventory'); ?>">View Transaction Inventory</a></li>

      <li class="<?php echo $active_action == 'transaction-inventory' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory&action=transaction-inventory'); ?>"> Manage Transaction Inventory</a></li>
      <li class="<?php echo $active_action == 'add-transaction-inventory' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory&action=add-transaction-inventory'); ?>">Add Transaction Inventory</a></li>
      <li class="<?php echo $active_action == 'edit-transaction-inventory' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory&action=edit-transaction-inventory'); ?>">Edit Transaction Inventory</a></li>
      <li class="<?php echo $active_action == 'delete-transaction-inventory' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=inventory&action=delete-transaction-inventory'); ?>">Delete Transaction Inventory</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'fees' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Fees</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'fees' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'fees' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=fees'); ?>">Fees</a></li>
      <li class="<?php echo $active_action == 'add-fees' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=fees&action=add-fees'); ?>">Add Fees</a></li>
      <li class="<?php echo $active_action == 'edit-fees' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=fees&action=edit-fees'); ?>">Edit Fees</a></li>
      <li class="<?php echo $active_action == 'delete-fees' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=fees&action=delete-fees'); ?>">Delete Fees</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'fee_templates' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Fees Templates</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'fee_templates' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'fee_templates' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=fee_templates'); ?>">Fees Templates</a></li>
      <li class="<?php echo $active_action == 'add-fee_templates' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=fee_templates&action=add-fee_templates'); ?>">Add Fees Templates</a></li>
      <li class="<?php echo $active_action == 'edit-fee_templates' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=fee_templates&action=edit-fee_templates'); ?>">Edit Fees Templates</a></li>
      <li class="<?php echo $active_action == 'delete-fee_templates' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=fee_templates&action=delete-fee_templates'); ?>">Delete Fees Templates</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'transport_fees' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Transport Fees</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'transport_fees' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'transport_fees' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=transport_fees'); ?>">Transport Fees</a></li>
      <li class="<?php echo $active_action == 'add-transport_fees' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=transport_fees&action=add-transport_fees'); ?>">Add Transport Fees</a></li>
      <li class="<?php echo $active_action == 'edit-transport_fees' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=transport_fees&action=edit-transport_fees'); ?>">Edit Transport Fees</a></li>
      <li class="<?php echo $active_action == 'delete-transport_fees' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=transport_fees&action=delete-transport_fees'); ?>">Delete Transport Fees</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'transport_enrollments' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Transport Enrollments</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'transport_enrollments' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'transport_enrollments' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=transport_enrollments'); ?>">Transport Enrollments</a></li>
      <li class="<?php echo $active_action == 'add-transport_enrollments' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=transport_enrollments&action=add-transport_enrollments'); ?>">Add Transport Enrollments</a></li>
      <li class="<?php echo $active_action == 'edit-transport_enrollments' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=transport_enrollments&action=edit-transport_enrollments'); ?>">Edit Transport Enrollments</a></li>
      <li class="<?php echo $active_action == 'delete-transport_enrollments' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=transport_enrollments&action=delete-transport_enrollments'); ?>">Delete Transport Enrollments</a></li>
    </div>
  </ul>
</li>
        <li>
  <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section == 'message' ? 'rotate' : ''; ?>">
  <i class="fas fa-chalkboard-teacher" style="color: #e8eaed;"></i>    
    <span>Message</span>
    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
  </button>
  <ul class="sub-menu <?php echo $active_section == 'message' ? 'show' : ''; ?>">
    <div>
      <li class="<?php echo $active_section == 'message' && empty($active_action) ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=message'); ?>">Message</a></li>
      <li class="<?php echo $active_action == 'add-message' ? 'active' : ''; ?>"><a href="<?php echo home_url('su_p-dashboard?section=message&action=add-message'); ?>">Add Message</a></li>
    </div>
  </ul>
</li>

        <li class="<?php echo $active_section === 'notice_board' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(home_url('su_p-dashboard?section=notice_board')); ?>">
                <i class="fas fa-bell" style="color: #e8eaed;"></i>
                <span>Notice Board</span>
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