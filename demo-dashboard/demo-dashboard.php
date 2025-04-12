<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Scripts and Styles
 */
function aspire_demo_enqueue_scripts() {
    wp_enqueue_style('aspire-demo-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_script('aspire-demo-script', plugin_dir_url(__FILE__) . 'demo.js', [], '1.0.11', true);
}
add_action('wp_enqueue_scripts', 'aspire_demo_enqueue_scripts');
$demoData = [
    'student_attendance' => [
        ['student_id' => 'ST1001', 'student_name' => 'John Doe', 'class' => '10A', 'section' => 'A', 'date' => '2025-04-01', 'status' => 'Present'],
        ['student_id' => 'ST1001', 'student_name' => 'John Doe', 'class' => '10A', 'section' => 'A', 'date' => '2025-04-02', 'status' => 'Late'],
        ['student_id' => 'ST1002', 'student_name' => 'Jane Smith', 'class' => '10A', 'section' => 'A', 'date' => '2025-04-01', 'status' => 'Absent'],
    ],
    'teacher_attendance' => [
        ['teacher_id' => 'TR1001', 'teacher_name' => 'Alice Brown', 'department' => 'Math', 'date' => '2025-04-01', 'status' => 'Present'],
        ['teacher_id' => 'TR1001', 'teacher_name' => 'Alice Brown', 'department' => 'Math', 'date' => '2025-04-02', 'status' => 'Late'],
        ['teacher_id' => 'TR1002', 'teacher_name' => 'Bob Wilson', 'department' => 'Science', 'date' => '2025-04-01', 'status' => 'Absent'],
    ]
];
/**
 * Dashboard Shortcode
 */
/**
 * Dashboard Shortcode
 */
function aspire_demo_dashboard_shortcode() {
    $role = isset($_GET['demo-role']) ? sanitize_text_field($_GET['demo-role']) : '';
    $section = isset($_GET['demo-section']) ? sanitize_text_field($_GET['demo-section']) : 'overview';
    $action = isset($_GET['demo-action']) ? sanitize_text_field($_GET['demo-action']) : '';

    ob_start();
    ?>
    <div id="demo-wrapper">
        <header class="demo-header">
            <div class="header-container">
                <div class="header-left">
                    <a href="<?php echo esc_url(home_url('/?page_id=' . get_the_ID())); ?>" class="header-logo">
                        <img decoding="async" class="logo-image" src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../logo instituto.jpg'); ?>" alt="Demo Pro Logo">
                        <span class="logo-text">Demo Pro</span>
                    </a>
                    <div class="header-search">
                        <input type="text" placeholder="Search" class="search-input" id="header-search-input" aria-label="Search">
                        <div class="search-dropdown" id="search-results">
                            <ul class="results-list" id="search-results-list"></ul>
                        </div>
                    </div>
                </div>
                <div class="header-right">
                    <nav class="header-nav">
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'overview'])); ?>" class="nav-item <?php echo $section === 'overview' ? 'active' : ''; ?>">Dashboard</a>
                        <?php if ($role === 'teacher'): ?>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams'])); ?>" class="nav-item <?php echo $section === 'exams' ? 'active' : ''; ?>">Exams</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance-reports'])); ?>" class="nav-item <?php echo $section === 'attendance-reports' ? 'active' : ''; ?>">Attendance</a>
                        <?php elseif ($role === 'student'): ?>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'exams'])); ?>" class="nav-item <?php echo $section === 'exams' ? 'active' : ''; ?>">Exams</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'attendance'])); ?>" class="nav-item <?php echo $section === 'attendance' ? 'active' : ''; ?>">Attendance</a>
                        <?php elseif ($role === 'parent'): ?>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'exams'])); ?>" class="nav-item <?php echo $section === 'exams' ? 'active' : ''; ?>">Child Exams</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'attendance'])); ?>" class="nav-item <?php echo $section === 'attendance' ? 'active' : ''; ?>">Child Attendance</a>
                        <?php elseif ($role === 'superadmin'): ?>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers'])); ?>" class="nav-item <?php echo $section === 'centers' ? 'active' : ''; ?>">Centers</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription'])); ?>" class="nav-item <?php echo $section === 'subscription' ? 'active' : ''; ?>">Subscriptions</a>
                        <?php endif; ?>
                    </nav>
                    <div class="header-actions">
                        <!-- Quick Links Dropdown -->
                        <div class="header-quick-links">
                            <a href="#" class="action-btn" id="quick-links-toggle">
                                <i class="fas fa-link fa-lg"></i>
                            </a>
                            <div class="dropdown quick-links-dropdown" id="quick-links-dropdown">
                                <div class="dropdown-header">
                                    <span>Quick Links</span>
                                </div>
                                <ul class="dropdown-list">
                                    <?php if ($role === 'teacher'): ?>
                                        <li><a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'add-exam'])); ?>" class="dropdown-link">Add Exam</a></li>
                                    <?php elseif ($role === 'superadmin'): ?>
                                        <li><a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'add-exam'])); ?>" class="dropdown-link">Add Exam</a></li>
                                        <li><a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'add-subscription'])); ?>" class="dropdown-link">Add Subscription</a></li>
                                    <?php endif; ?>
                                    <li><a href="https://support.demo-pro.edu" target="_blank" class="dropdown-link">Support</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- Messages Dropdown -->
                        <div class="header-messages">
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'messages'])); ?>" class="action-btn" id="messages-toggle">
                                <i class="fas fa-envelope fa-lg"></i>
                                <span class="action-badge <?php echo $role ? '' : 'd-none'; ?>" id="messages-count">3</span> <!-- Hardcoded -->
                            </a>
                            <div class="dropdown messages-dropdown" id="messages-dropdown">
                                <div class="dropdown-header">
                                    <span>Messages (Last 7 Days)</span>
                                </div>
                                <ul class="dropdown-list">
                                    <li>
                                        <span class="msg-content">
                                            <span class="msg-sender">System</span>:
                                            <span class="msg-preview">Exam scheduled...</span>
                                        </span>
                                        <span class="msg-time">Apr 10, 2025</span>
                                    </li>
                                    <li>
                                        <span class="msg-content">
                                            <span class="msg-sender">Admin</span>:
                                            <span class="msg-preview">Update available...</span>
                                        </span>
                                        <span class="msg-time">Apr 9, 2025</span>
                                    </li>
                                    <li>
                                        <span class="msg-content">
                                            <span class="msg-sender">Teacher</span>:
                                            <span class="msg-preview">Class canceled...</span>
                                        </span>
                                        <span class="msg-time">Apr 8, 2025</span>
                                    </li>
                                </ul>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'messages'])); ?>" class="dropdown-footer">View All Messages</a>
                            </div>
                        </div>

                        <!-- Notifications Dropdown -->
                        <div class="header-notifications">
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'notifications'])); ?>" class="action-btn" id="notifications-toggle">
                                <i class="fas fa-bell fa-lg"></i>
                                <span class="action-badge <?php echo $role ? '' : 'd-none'; ?>" id="notifications-count">2</span> <!-- Hardcoded -->
                            </a>
                            <div class="dropdown notifications-dropdown" id="notifications-dropdown">
                                <div class="dropdown-header">
                                    <span>Notifications (Last 7 Days)</span>
                                </div>
                                <ul class="dropdown-list">
                                    <li>
                                        <span class="msg-content">
                                            <span class="msg-sender">System</span>:
                                            <span class="notif-text">New exam added...</span>
                                        </span>
                                        <span class="notif-time">Apr 11, 2025</span>
                                    </li>
                                    <li>
                                        <span class="msg-content">
                                            <span class="msg-sender">Admin</span>:
                                            <span class="notif-text">Attendance updated...</span>
                                        </span>
                                        <span class="notif-time">Apr 10, 2025</span>
                                    </li>
                                </ul>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'notifications'])); ?>" class="dropdown-footer">View All</a>
                            </div>
                        </div>

                        <!-- Settings -->
                        <div class="header-settings">
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'settings'])); ?>" class="action-btn" id="settings-toggle">
                                <i class="fas fa-cog fa-lg"></i>
                            </a>
                        </div>

                        <!-- Help/Support -->
                        <div class="header-help">
                            <a href="https://support.demo-pro.edu" target="_blank" class="action-btn" id="help-toggle">
                                <i class="fas fa-question-circle fa-lg"></i>
                            </a>
                        </div>

                        <!-- Dark Mode Toggle -->
                        <div class="header-dark-mode">
                            <button class="action-btn" id="dark-mode-toggle">
                                <i class="fas fa-moon fa-lg"></i>
                            </button>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="header-profile">
                            <div class="profile-toggle" id="profile-toggle">
                                <img decoding="async" src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../logo instituto.jpg'); ?>" alt="Profile" class="profile-img">
                                <i class="fas fa-caret-down profile-arrow"></i>
                            </div>
                            <div class="action-dropdown profile-dropdown" id="profile-dropdown">
                                <div class="profile-info">
                                    <img decoding="async" src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../logo instituto.jpg'); ?>" alt="Profile" class="profile-img-large">
                                    <div>
                                        <span class="profile-name"><?php echo esc_html($role ? ucfirst($role) : 'Demo User'); ?></span><br>
                                        <span class="profile-email"><?php echo esc_html($role ? "$role@demo-pro.edu" : 'demo@demo-pro.edu'); ?></span>
                                    </div>
                                </div>
                                <ul class="dropdown-list">
                                    <li><a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'settings'])); ?>" class="profile-link">Settings</a></li>
                                    <li><a href="<?php echo esc_url(home_url('/?page_id=' . get_the_ID())); ?>" class="profile-link logout">Logout</a></li>
                                </ul>
                            </div>
                        </div>

                        <button class="nav-toggle" id="nav-toggle" aria-label="Toggle Navigation">
                            <i class="fas fa-bars"></i>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Rest of the shortcode remains unchanged -->
        <div class="institute-dashboard-wrapper">
            <div id="sidebar-container">
                <?php if ($role): ?>
                    <?php echo demoRenderSidebar($role, $section); ?>
                <?php endif; ?>
            </div>
            <div class="main-content" id="dashboard-content">
                <?php if (!$role): ?>
                    <div class="container py-4" id="role-selection">
                        <h1 class="text-center mb-4">Welcome to Demo Pro</h1>
                        <p class="text-center mb-4">Manage your educational institute with ease. Choose your role to begin.</p>
                        <div class="row justify-content-center">
                            <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'teacher')); ?>" class="btn btn-primary w-100">Teacher</a></div>
                            <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'student')); ?>" class="btn btn-success w-100">Student</a></div>
                            <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'parent')); ?>" class="btn btn-info w-100">Parent</a></div>
                            <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'superadmin')); ?>" class="btn btn-warning w-100">Super Admin</a></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-9 p-4" id="main-content-inner">
                            <?php
                            switch ($role) {
                                case 'teacher':
                                    $data = demoGetTeacherData();
                                    echo demoRenderTeacherContent($section, $action, $data);
                                    break;
                                case 'student':
                                    $data = demoGetStudentData();
                                    echo demoRenderStudentContent($section, $action, $data);
                                    break;
                                case 'parent':
                                    $data = demoGetParentData();
                                    echo demoRenderParentContent($section, $action, $data);
                                    break;
                                case 'superadmin':
                                    $data = demoGetSuperadminData();
                                    echo demoRenderSuperadminContent($section, $action, $data);
                                    break;
                                default:
                                    echo '<h2>Invalid Role</h2><p>Please select a valid role.</p>';
                            }
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- JavaScript for Header Interactivity -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', () => {
        const fontAwesomeLink = document.createElement('link');
        fontAwesomeLink.rel = 'stylesheet';
        fontAwesomeLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css';
        fontAwesomeLink.crossOrigin = 'anonymous';
        document.head.appendChild(fontAwesomeLink);

        const searchInput = document.getElementById('header-search-input');
        const searchResults = document.getElementById('search-results');
        const navToggle = document.getElementById('nav-toggle');
        const headerNav = document.querySelector('.header-nav');
        const messagesToggle = document.getElementById('messages-toggle');
        const messagesDropdown = document.getElementById('messages-dropdown');
        const notificationsToggle = document.getElementById('notifications-toggle');
        const notificationsDropdown = document.getElementById('notifications-dropdown');
        const quickLinksToggle = document.getElementById('quick-links-toggle');
        const quickLinksDropdown = document.getElementById('quick-links-dropdown');
        const darkModeToggle = document.getElementById('dark-mode-toggle');
        const profileToggle = document.getElementById('profile-toggle');
        const profileDropdown = document.getElementById('profile-dropdown');

        let activeDropdown = null;

        function toggleDropdown(toggle, dropdown, isLink = false) {
            toggle.addEventListener('mouseenter', () => {
                closeAllDropdowns();
                dropdown.classList.add('visible');
                activeDropdown = dropdown;
            });

            dropdown.addEventListener('mouseenter', () => {
                dropdown.classList.add('visible');
                activeDropdown = dropdown;
            });

            toggle.addEventListener('mouseleave', () => {
                dropdown.addEventListener('mouseleave', () => {
                    dropdown.classList.remove('visible');
                    activeDropdown = null;
                });
            });

            if (!isLink) {
                toggle.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    if (dropdown.classList.contains('visible')) {
                        dropdown.classList.remove('visible');
                        activeDropdown = null;
                    } else {
                        closeAllDropdowns();
                        dropdown.classList.add('visible');
                        activeDropdown = dropdown;
                    }
                });
            }
        }

        function closeAllDropdowns() {
            [messagesDropdown, notificationsDropdown, quickLinksDropdown, profileDropdown, searchResults].forEach(dropdown => {
                if (dropdown) dropdown.classList.remove('visible');
            });
            activeDropdown = null;
        }

        if (searchInput && searchResults) {
            let debounceTimer;
            searchInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const query = this.value.trim();
                    if (query.length < 2) {
                        searchResults.classList.remove('visible');
                        return;
                    }

                    searchResults.querySelector('.results-list').innerHTML = '<li>Loading...</li>';
                    searchResults.classList.add('visible');

                    // Hardcoded search results
                    const sections = [
                        { title: 'Dashboard', url: '<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'overview'])); ?>' },
                        { title: 'Exams', url: '<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'exams'])); ?>' },
                        { title: 'Attendance', url: '<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'attendance'])); ?>' },
                        { title: 'Centers', url: '<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'centers'])); ?>' },
                        { title: 'Subscriptions', url: '<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'subscription'])); ?>' }
                    ];
                    const resultsList = searchResults.querySelector('.results-list');
                    resultsList.innerHTML = '';
                    const filtered = sections.filter(item => item.title.toLowerCase().includes(query.toLowerCase()));
                    if (filtered.length > 0) {
                        filtered.forEach(item => {
                            resultsList.innerHTML += `<li><a href="${item.url}">${item.title}</a></li>`;
                        });
                    } else {
                        resultsList.innerHTML = '<li>No results found</li>';
                    }
                }, 150);
            });
        }

        if (navToggle && headerNav) {
            navToggle.addEventListener('click', () => {
                headerNav.classList.toggle('visible');
            });
        }

        if (messagesToggle && messagesDropdown) {
            toggleDropdown(messagesToggle, messagesDropdown, true);
        }

        if (notificationsToggle && notificationsDropdown) {
            toggleDropdown(notificationsToggle, notificationsDropdown, true);
        }

        if (quickLinksToggle && quickLinksDropdown) {
            toggleDropdown(quickLinksToggle, quickLinksDropdown);
        }

        if (darkModeToggle) {
            darkModeToggle.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                const isDark = document.body.classList.contains('dark-mode');
                darkModeToggle.querySelector('i').classList.toggle('fa-moon', !isDark);
                darkModeToggle.querySelector('i').classList.toggle('fa-sun', isDark);
            });
        }

        if (profileToggle && profileDropdown) {
            toggleDropdown(profileToggle, profileDropdown);
        }

        document.addEventListener('click', (e) => {
            if (searchInput && searchResults && !searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.remove('visible');
            }
            if (activeDropdown && !activeDropdown.contains(e.target) && !e.target.closest('.action-btn, .profile-toggle')) {
                activeDropdown.classList.remove('visible');
                activeDropdown = null;
            }
        });
    });

    jQuery(document).ready(function($) {
        $('a[href*="section=messages"]').on('click', function(e) {
            e.preventDefault();
            $('#messages-count').text('0').addClass('d-none');
            $('#messages-dropdown .dropdown-list').html('<li><span class="msg-preview">No new messages</span></li>');
            $('#messages-dropdown .dropdown-footer').remove();
            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'messages'])); ?>';
        });

        $('a[href*="section=notifications"]').on('click', function(e) {
            e.preventDefault();
            $('#notifications-count').text('0').addClass('d-none');
            $('#notifications-dropdown .dropdown-list').html('<li><span class="notif-text">No new notifications</span></li>');
            $('#notifications-dropdown .dropdown-footer').remove();
            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'notifications'])); ?>';
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('demo-dashboard', 'aspire_demo_dashboard_shortcode');

/**
 * Sidebar Function
 */
function demoRenderSidebar($role, $active_section) {
    $active_action = isset($_GET['demo-action']) ? sanitize_text_field($_GET['demo-action']) : '';
    $links = [
        'teacher' => [
            ['section' => 'overview', 'label' => 'Overview', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'overview']), 'icon' => 'tachometer-alt'],
            ['section' => 'exams', 'label' => 'Exams', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams']), 'icon' => 'list'],
            ['section' => 'attendance-reports', 'label' => 'Attendance Reports', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance-reports']), 'icon' => 'calendar'],
            ['section' => 'students', 'label' => 'Students', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students']), 'icon' => 'users', 'submenu' => [
                ['action' => 'manage-students', 'label' => 'Manage Students', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'manage-students'])],
                ['action' => 'add-student', 'label' => 'Add Student', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'add-student'])],
                ['action' => 'edit-student', 'label' => 'Edit Student', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'edit-student'])],
                ['action' => 'delete-student', 'label' => 'Delete Student', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'delete-student'])]
            ]],
            ['section' => 'profile', 'label' => 'View Profile', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'profile']), 'icon' => 'user'],
            ['section' => 'exam-management', 'label' => 'Exam Management', 'icon' => 'book', 'submenu' => [
                ['action' => 'view-exams', 'label' => 'View Exams', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'view-exams'])],
                ['action' => 'add-exam', 'label' => 'Add Exam', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'add-exam'])],
                ['action' => 'edit-exam', 'label' => 'Edit Exam', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'edit-exam'])],
                ['action' => 'delete-exam', 'label' => 'Delete Exam', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'delete-exam'])]
            ]],
            ['section' => 'student-attendance', 'label' => 'Student Attendance', 'icon' => 'calendar-check', 'submenu' => [
                ['action' => 'manage-student-attendance', 'label' => 'Manage Attendance', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'manage-student-attendance'])],
                ['action' => 'add-student-attendance', 'label' => 'Add Attendance', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'add-student-attendance'])],
                ['action' => 'edit-student-attendance', 'label' => 'Edit Attendance', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'edit-student-attendance'])],
                ['action' => 'delete-student-attendance', 'label' => 'Delete Attendance', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'student-attendance', 'demo-action' => 'delete-student-attendance'])]
            ]]
        ],
        'student' => [
            ['section' => 'overview', 'label' => 'Overview', 'url' => add_query_arg(['demo-role' => 'student', 'demo-section' => 'overview']), 'icon' => 'tachometer-alt'],
            ['section' => 'exams', 'label' => 'Exams', 'url' => add_query_arg(['demo-role' => 'student', 'demo-section' => 'exams']), 'icon' => 'list'],
            ['section' => 'attendance', 'label' => 'Attendance', 'url' => add_query_arg(['demo-role' => 'student', 'demo-section' => 'attendance']), 'icon' => 'calendar'],
            ['section' => 'profile', 'label' => 'Profile', 'url' => add_query_arg(['demo-role' => 'student', 'demo-section' => 'profile']), 'icon' => 'user']
        ],
        'parent' => [
            ['section' => 'overview', 'label' => 'Overview', 'url' => add_query_arg(['demo-role' => 'parent', 'demo-section' => 'overview']), 'icon' => 'tachometer-alt'],
            ['section' => 'exams', 'label' => 'Child Exams', 'url' => add_query_arg(['demo-role' => 'parent', 'demo-section' => 'exams']), 'icon' => 'list'],
            ['section' => 'attendance', 'label' => 'Child Attendance', 'url' => add_query_arg(['demo-role' => 'parent', 'demo-section' => 'attendance']), 'icon' => 'calendar']
        ],
        'superadmin' => [
            ['section' => 'overview', 'label' => 'Overview', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'overview']), 'icon' => 'tachometer-alt'],
            ['section' => 'centers', 'label' => 'Centers', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers']), 'icon' => 'school'],
            ['section' => 'students', 'label' => 'Students Management', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students']), 'icon' => 'users', 'submenu' => [
                ['action' => 'manage-students', 'label' => 'Manage Students', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'manage-students'])],
                ['action' => 'add-student', 'label' => 'Add Student', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'add-student'])],
                ['action' => 'edit-student', 'label' => 'Edit Student', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'edit-student'])],
                ['action' => 'delete-student', 'label' => 'Delete Student', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'delete-student'])]
            ]],
            ['section' => 'teachers', 'label' => 'Teachers Management', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers']), 'icon' => 'chalkboard-teacher', 'submenu' => [
                ['action' => 'manage-teachers', 'label' => 'Manage Teachers', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'manage-teachers'])],
                ['action' => 'add-teacher', 'label' => 'Add Teacher', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'add-teacher'])],
                ['action' => 'edit-teacher', 'label' => 'Edit Teacher', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'edit-teacher'])],
                ['action' => 'delete-teacher', 'label' => 'Delete Teacher', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'delete-teacher'])]
            ]],
            ['section' => 'staff', 'label' => 'Staff Management', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff']), 'icon' => 'user-tie', 'submenu' => [
                ['action' => 'manage-staff', 'label' => 'Manage Staff', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'manage-staff'])],
                ['action' => 'add-staff', 'label' => 'Add Staff', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'add-staff'])],
                ['action' => 'edit-staff', 'label' => 'Edit Staff', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'edit-staff'])],
                ['action' => 'delete-staff', 'label' => 'Delete Staff', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'delete-staff'])]
            ]],
            ['section' => 'attendance', 'label' => 'Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance']), 'icon' => 'calendar-check', 'submenu' => [
                ['action' => 'student-attendance', 'label' => 'Student Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'student-attendance']), 'submenu' => [
                    ['action' => 'manage-student-attendance', 'label' => 'Manage Student Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])],
                    ['action' => 'add-student-attendance', 'label' => 'Add Student Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'add-student-attendance'])],
                    ['action' => 'edit-student-attendance', 'label' => 'Edit Student Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'edit-student-attendance'])],
                    ['action' => 'delete-student-attendance', 'label' => 'Delete Student Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'delete-student-attendance'])]
                ]],
                ['action' => 'teacher-attendance', 'label' => 'Teacher Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'teacher-attendance']), 'submenu' => [
                    ['action' => 'manage-teacher-attendance', 'label' => 'Manage Teacher Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])],
                    ['action' => 'add-teacher-attendance', 'label' => 'Add Teacher Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'add-teacher-attendance'])],
                    ['action' => 'edit-teacher-attendance', 'label' => 'Edit Teacher Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'edit-teacher-attendance'])],
                    ['action' => 'delete-teacher-attendance', 'label' => 'Delete Teacher Attendance', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'delete-teacher-attendance'])]
                ]]
            ]],
            ['section' => 'subscription', 'label' => 'Subscription', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription']), 'icon' => 'credit-card'],
            ['section' => 'payment_methods', 'label' => 'Payment Methods', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods']), 'icon' => 'money-bill']
        ]
    ];

    $title = ucfirst($role) === 'Superadmin' ? 'Super Admin' : ucfirst($role);
    ob_start();
    ?>
    <nav id="sidebar">
        <ul>
            <li>
                <div class="logo-title-section">
                    <div class="institute-logo">
                        <img src="<?php echo plugin_dir_url(__FILE__) . '../logo instituto.jpg'; ?>" alt="Avatar" 
                             style="border-radius: 50%; width: 60px; height: 60px; object-fit: cover;" class="profile-avatar mb-3">
                    </div>
                    <h4 class="institute-title" style="margin-bottom:0; margin-left:4px; color: var(--text-clr);"><?php echo esc_html($title); ?></h4>
                </div>
                <button onclick="toggleSidebar()" id="toggle-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
                        <path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/>
                    </svg>
                </button>
            </li>
            <?php foreach ($links[$role] as $link): ?>
                <?php if (isset($link['submenu'])): ?>
                    <li>
                        <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section === $link['section'] ? 'rotate' : ''; ?>">
                            <?php if ($link['icon'] === 'tachometer-alt'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.05-7.44 7-7.93V6h2V4c3.94.49 7 3.85 7 7.93s-3.05 7.44-7 7.93v-2h-2v2zM12 14l3-3h-2V7h-2v4H9l3 3z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'school'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'users'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'chalkboard-teacher'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'user-tie'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M12 2C9.24 2 7 4.24 7 7c0 1.69.88 3.19 2.22 4.03C6.47 11.81 4 14.43 4 17.5V20h16v-2.5c0-3.07-2.47-5.69-5.22-6.47C16.12 10.19 17 8.69 17 7c0-2.76-2.24-5-5-5zm-2 5c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm2 10.5l-2-2 2-3 2 3-2 2z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'calendar-check'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V8h16v13zm-5-6l-4-4-2 2 6 6 9-9-2-2-7 7z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'credit-card'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4V8h16v10zm-4-4h-2v2h-4v-2H8v-2h2v-2h4v2h2v2z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'money-bill'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M21 4H3c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15H3V6h18v13zm-6-6c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'list'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'calendar'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V8h16v13z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'user'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'book'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
                                </svg>
                            <?php endif; ?>
                            <span><?php echo esc_html($link['label']); ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
                                <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
                            </svg>
                        </button>
                        <ul class="sub-menu <?php echo $active_section === $link['section'] ? 'show' : ''; ?>">
                            <div>
                                <?php foreach ($link['submenu'] as $sub_link): ?>
                                    <?php if (isset($sub_link['submenu'])): ?>
                                        <li>
                                            <button onclick="toggleNestedSubMenu(this)" class="nested-dropdown-btn <?php echo $active_action === $sub_link['action'] ? 'rotate' : ''; ?>">
                                                <?php echo esc_html($sub_link['label']); ?>
                                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed">
                                                    <path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/>
                                                </svg>
                                            </button>
                                            <ul class="nested-sub-menu <?php echo $active_action === $sub_link['action'] ? 'show' : ''; ?>">
                                                <?php foreach ($sub_link['submenu'] as $nested_link): ?>
                                                    <li class="<?php echo $active_action === $nested_link['action'] ? 'active' : ''; ?>">
                                                        <a href="<?php echo esc_url($nested_link['url']); ?>"><?php echo esc_html($nested_link['label']); ?></a>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php else: ?>
                                        <li class="<?php echo $active_section === $link['section'] && $active_action === $sub_link['action'] ? 'active' : ''; ?>">
                                            <a href="<?php echo esc_url($sub_link['url']); ?>"><?php echo esc_html($sub_link['label']); ?></a>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="<?php echo $active_section === $link['section'] ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url($link['url']); ?>">
                            <?php if ($link['icon'] === 'tachometer-alt'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.05-7.44 7-7.93V6h2V4c3.94.49 7 3.85 7 7.93s-3.05 7.44-7 7.93v-2h-2v2zM12 14l3-3h-2V7h-2v4H9l3 3z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'school'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'users'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'chalkboard-teacher'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'user-tie'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M12 2C9.24 2 7 4.24 7 7c0 1.69.88 3.19 2.22 4.03C6.47 11.81 4 14.43 4 17.5V20h16v-2.5c0-3.07-2.47-5.69-5.22-6.47C16.12 10.19 17 8.69 17 7c0-2.76-2.24-5-5-5zm-2 5c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm2 10.5l-2-2 2-3 2 3-2 2z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'calendar-check'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V8h16v13zm-5-6l-4-4-2 2 6 6 9-9-2-2-7 7z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'credit-card'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.1 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4V8h16v10zm-4-4h-2v2h-4v-2H8v-2h2v-2h4v2h2v2z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'money-bill'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M21 4H3c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15H3V6h18v13zm-6-6c0-1.1-.9-2-2-2s-2 .9-2 2 .9 2 2 2 2-.9 2-2z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'list'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'calendar'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M20 3h-1V1h-2v2H7V1H5v2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 18H4V8h16v13z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'user'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                                </svg>
                            <?php elseif ($link['icon'] === 'book'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                    <path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/>
                                </svg>
                            <?php endif; ?>
                            <span><?php echo esc_html($link['label']); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
    <script>
        function toggleNestedSubMenu(button) {
            const subMenu = button.nextElementSibling;
            button.classList.toggle('rotate');
            subMenu.classList.toggle('show');
        }
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Teacher Content
 */
function demoRenderTeacherContent($section, $action, $data = []) {
    ob_start();
    if ($section === 'exam-management') {
        switch ($action) {
            case 'view-exams':
                echo demoRenderTeacherExams($data);
                break;
            case 'add-exam':
                echo demoRenderTeacherAddExam();
                break;
            case 'edit-exam':
                echo demoRenderTeacherEditExam($data);
                break;
            case 'delete-exam':
                echo demoRenderTeacherDeleteExam($data);
                break;
            default:
                echo demoRenderTeacherExams($data);
        }
    } elseif ($section === 'students') {
        switch ($action) {
            case 'add-student':
                echo demoRenderTeacherAddStudent();
                break;
            case 'edit-student':
                echo demoRenderTeacherEditStudent($data);
                break;
            case 'delete-student':
                echo demoRenderTeacherDeleteStudent($data);
                break;
            case 'manage-students':
            default:
                echo demoRenderTeacherStudents($data);
        }
    } elseif ($section === 'profile') {
        echo demoRenderTeacherProfile($data);
    } elseif ($section === 'student-attendance') {
        switch ($action) {
            case 'manage-student-attendance':
                echo demoRenderTeacherManageStudentAttendance($data);
                break;
            case 'add-student-attendance':
                echo demoRenderTeacherAddStudentAttendance();
                break;
            case 'edit-student-attendance':
                echo demoRenderTeacherEditStudentAttendance($data);
                break;
            case 'delete-student-attendance':
                echo demoRenderTeacherDeleteStudentAttendance($data);
                break;
            default:
                echo demoRenderTeacherManageStudentAttendance($data);
        }
    } else {
        switch ($section) {
            case 'exams':
                echo demoRenderTeacherExams($data);
                break;
            case 'attendance-reports':
                echo demoRenderTeacherAttendance($data);
                break;
            default:
                echo demoRenderTeacherOverview();
        }
    }
    return ob_get_clean();
}

function demoRenderTeacherManageStudentAttendance($data = []) {
    ob_start();
    ?>
    <div class="edu-attendance-container" style="margin-top: 80px;">
        <h2 class="edu-attendance-title">Manage Student Attendance</h2>
        <div class="edu-attendance-actions">
            <input type="text" id="student-attendance-search" class="edu-search-input" placeholder="Search Students..." style="margin-right: 20px; padding: 8px; width: 300px;">
            <button class="edu-button edu-button-primary" id="add-student-attendance-btn">Add Attendance</button>
            <button class="edu-button edu-button-secondary" id="export-student-attendance">Export CSV</button>
            <input type="file" id="import-student-attendance" accept=".csv" style="display: none;">
            <button class="edu-button edu-button-secondary" id="import-student-attendance-btn">Import CSV</button>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="attendance-per-page">Show:</label>
            <select id="attendance-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="student-attendance-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="student-attendance-table-body">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>

        <!-- Add Student Attendance Modal -->
        <div id="add-student-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="add-student-attendance-close">&times;</span>
                <h2>Add Student Attendance</h2>
                <form id="add-student-attendance-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="add-student-id" name="student_id" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="add-student-name" name="student_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-class">Class</label>
                        <input type="text" class="edu-form-input" id="add-class" name="class" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-section">Section</label>
                        <input type="text" class="edu-form-input" id="add-section" name="section" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-attendance-date">Date</label>
                        <input type="date" class="edu-form-input" id="add-attendance-date" name="date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-attendance-status">Status</label>
                        <select class="edu-form-input" id="add-attendance-status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="save-student-attendance">Add Attendance</button>
                </form>
                <div class="edu-form-message" id="add-student-attendance-message"></div>
            </div>
        </div>

        <!-- Edit Student Attendance Modal -->
        <div id="edit-student-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="edit-student-attendance-close">&times;</span>
                <h2>Edit Student Attendance</h2>
                <form id="edit-student-attendance-form" class="edu-form">
                    <input type="hidden" id="edit-attendance-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="edit-student-id" name="student_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="edit-student-name" name="student_name" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-class">Class</label>
                        <input type="text" class="edu-form-input" id="edit-class" name="class" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-section">Section</label>
                        <input type="text" class="edu-form-input" id="edit-section" name="section" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-attendance-date">Date</label>
                        <input type="date" class="edu-form-input" id="edit-attendance-date" name="date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-attendance-status">Status</label>
                        <select class="edu-form-input" id="edit-attendance-status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-student-attendance">Update Attendance</button>
                </form>
                <div class="edu-form-message" id="edit-student-attendance-message"></div>
            </div>
        </div>

        <!-- Delete Student Attendance Modal -->
        <div id="delete-student-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="delete-student-attendance-close">&times;</span>
                <h2>Delete Student Attendance</h2>
                <p>Are you sure you want to delete attendance for <span id="delete-student-name"></span> on <span id="delete-attendance-date"></span>?</p>
                <input type="hidden" id="delete-attendance-id">
                <button type="button" class="edu-button edu-button-delete" id="confirm-delete-student-attendance">Delete</button>
                <button type="button" class="edu-button edu-button-secondary" id="cancel-delete-student-attendance">Cancel</button>
                <div class="edu-form-message" id="delete-student-attendance-message"></div>
            </div>
        </div>

        <script>
    jQuery(document).ready(function($) {
        let currentPage = 1;
        let perPage = 10;
        let searchQuery = '';
        let studentAttendanceData = <?php echo json_encode($data['student_attendance'] ?? []); ?>;

        function loadStudentAttendance(page, limit, query) {
            const filtered = studentAttendanceData.filter(a => 
                !query || a.student_name.toLowerCase().includes(query.toLowerCase())
            );
            const total = filtered.length;
            const start = (page - 1) * limit;
            const end = start + limit;
            const paginated = filtered.slice(start, end);
            let html = '';
            paginated.forEach((record, index) => {
                html += `
                    <tr data-attendance-id="${start + index}">
                        <td>${record.student_id}</td>
                        <td>${record.student_name}</td>
                        <td>${record.class}</td>
                        <td>${record.section}</td>
                        <td>${record.date}</td>
                        <td>${record.status}</td>
                        <td>
                            <button class="edu-button edu-button-edit edit-student-attendance" data-attendance-id="${start + index}">Edit</button>
                            <button class="edu-button edu-button-delete delete-student-attendance" data-attendance-id="${start + index}">Delete</button>
                        </td>
                    </tr>
                `;
            });
            $('#student-attendance-table-body').html(html || '<tr><td colspan="7">No attendance records found.</td></tr>');
            const totalPages = Math.ceil(total / limit);
            $('#page-info').text(`Page ${page} of ${totalPages}`);
            $('#prev-page').prop('disabled', page === 1);
            $('#next-page').prop('disabled', page === totalPages || total === 0);
        }

            loadStudentAttendance(currentPage, perPage, searchQuery);

            $('#student-attendance-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadStudentAttendance(currentPage, perPage, searchQuery);
            });

            $('#attendance-per-page').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadStudentAttendance(currentPage, perPage, searchQuery);
            });

            $('#next-page').on('click', function() { currentPage++; loadStudentAttendance(currentPage, perPage, searchQuery); });
            $('#prev-page').on('click', function() { currentPage--; loadStudentAttendance(currentPage, perPage, searchQuery); });

            $('#add-student-attendance-btn').on('click', function() { $('#add-student-attendance-modal').show(); });
            $('#add-student-attendance-close').on('click', function() { $('#add-student-attendance-modal').hide(); });
            $('#save-student-attendance').on('click', function() {
                const record = {
                    student_id: $('#add-student-id').val(),
                    student_name: $('#add-student-name').val(),
                    class: $('#add-class').val(),
                    section: $('#add-section').val(),
                    date: $('#add-attendance-date').val(),
                    status: $('#add-attendance-status').val()
                };
                if (record.student_id && record.student_name && record.class && record.section && record.date && record.status) {
                    studentAttendanceData.push(record);
                    $('#add-student-attendance-message').addClass('edu-success').text('Attendance added successfully!');
                    setTimeout(() => {
                        $('#add-student-attendance-modal').hide();
                        $('#add-student-attendance-message').removeClass('edu-success').text('');
                        $('#add-student-attendance-form')[0].reset();
                        loadStudentAttendance(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#add-student-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.edit-student-attendance', function() {
                const attendanceId = $(this).data('attendance-id');
                const record = studentAttendanceData[attendanceId];
                $('#edit-attendance-id').val(attendanceId);
                $('#edit-student-id').val(record.student_id);
                $('#edit-student-name').val(record.student_name);
                $('#edit-class').val(record.class);
                $('#edit-section').val(record.section);
                $('#edit-attendance-date').val(record.date);
                $('#edit-attendance-status').val(record.status);
                $('#edit-student-attendance-modal').show();
            });
            $('#edit-student-attendance-close').on('click', function() { $('#edit-student-attendance-modal').hide(); });
            $('#update-student-attendance').on('click', function() {
                const attendanceId = $('#edit-attendance-id').val();
                const record = studentAttendanceData[attendanceId];
                record.date = $('#edit-attendance-date').val();
                record.status = $('#edit-attendance-status').val();
                if (record.date && record.status) {
                    $('#edit-student-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                    setTimeout(() => {
                        $('#edit-student-attendance-modal').hide();
                        $('#edit-student-attendance-message').removeClass('edu-success').text('');
                        loadStudentAttendance(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#edit-student-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-student-attendance', function() {
                const attendanceId = $(this).data('attendance-id');
                const record = studentAttendanceData[attendanceId];
                $('#delete-attendance-id').val(attendanceId);
                $('#delete-student-name').text(record.student_name);
                $('#delete-attendance-date').text(record.date);
                $('#delete-student-attendance-modal').show();
            });
            $('#delete-student-attendance-close, #cancel-delete-student-attendance').on('click', function() { $('#delete-student-attendance-modal').hide(); });
            $('#confirm-delete-student-attendance').on('click', function() {
                const attendanceId = $('#delete-attendance-id').val();
                studentAttendanceData.splice(attendanceId, 1);
                $('#delete-student-attendance-message').addClass('edu-success').text('Attendance deleted successfully!');
                setTimeout(() => {
                    $('#delete-student-attendance-modal').hide();
                    $('#delete-student-attendance-message').removeClass('edu-success').text('');
                    loadStudentAttendance(currentPage, perPage, searchQuery);
                }, 1000);
            });

            $('#export-student-attendance').on('click', function() {
                const csv = studentAttendanceData.map(row => `${row.student_id},${row.student_name},${row.class},${row.section},${row.date},${row.status}`).join('\n');
                const headers = 'Student ID,Student Name,Class,Section,Date,Status\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'student_attendance.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('#import-student-attendance-btn').on('click', function() {
                $('#import-student-attendance').click();
            });

            $('#import-student-attendance').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [student_id, student_name, class_name, section, date, status] = row.split(',');
                            return { student_id, student_name, class: class_name, section, date, status };
                        });
                        studentAttendanceData.push(...newRecords);
                        $('#add-student-attendance-message').addClass('edu-success').text('Attendance imported successfully!');
                        setTimeout(() => {
                            $('#add-student-attendance-message').removeClass('edu-success').text('');
                            loadStudentAttendance(currentPage, perPage, searchQuery);
                        }, 1000);
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

// Add new profile function
function demoRenderTeacherProfile($data) {
    ob_start();
    $profile = $data['profile'];
    ?>
    <div class="dashboard-section">
        <h2>My Profile</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'profile'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="teacher">
                <input type="hidden" name="demo-section" value="profile">
                <div class="mb-3">
                    <label for="teacher-id">Teacher ID</label>
                    <input type="text" id="teacher-id" name="teacher-id" value="<?php echo esc_html($profile['teacher_id']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="teacher-name">Name</label>
                    <input type="text" id="teacher-name" name="teacher-name" value="<?php echo esc_html($profile['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="teacher-email">Email</label>
                    <input type="email" id="teacher-email" name="teacher-email" value="<?php echo esc_html($profile['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="teacher-subject">Subject</label>
                    <input type="text" id="teacher-subject" name="teacher-subject" value="<?php echo esc_html($profile['subject']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="teacher-center">Center</label>
                    <input type="text" id="teacher-center" name="teacher-center" value="<?php echo esc_html($profile['center']); ?>" readonly>
                </div>
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// New CRUD functions for Teacher Dashboard
function demoRenderTeacherAddStudent() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Student</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'manage-students'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="teacher">
                <input type="hidden" name="demo-section" value="students">
                <input type="hidden" name="demo-action" value="manage-students">
                <div class="mb-3"><label for="student-id">Student ID</label><input type="text" id="student-id" name="student-id" value="ST<?php echo rand(1000, 9999); ?>" readonly></div>
                <div class="mb-3"><label for="student-name">Name</label><input type="text" id="student-name" name="student-name" required></div>
                <div class="mb-3"><label for="student-email">Email</label><input type="email" id="student-email" name="student-email"></div>
                <div class="mb-3"><label for="student-phone">Phone</label><input type="text" id="student-phone" name="student-phone"></div>
                <div class="mb-3"><label for="student-class">Class</label><input type="text" id="student-class" name="student-class" required></div>
                <button type="submit" class="btn btn-primary">Add Student</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'manage-students'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditStudent($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Student</h2>
        <div class="alert alert-info">Select a student to edit from the list below.</div>
        <table class="table" id="teacher-students">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Class</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['students'] ?? [] as $student): ?>
                    <tr>
                        <td><?php echo esc_html($student['id']); ?></td>
                        <td><?php echo esc_html($student['name']); ?></td>
                        <td><?php echo esc_html($student['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($student['class']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'edit-student'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'delete-student'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteStudent($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Student</h2>
        <div class="alert alert-warning">Click "Delete" to remove a student.</div>
        <table class="table" id="teacher-students">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Class</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['students'] ?? [] as $student): ?>
                    <tr>
                        <td><?php echo esc_html($student['id']); ?></td>
                        <td><?php echo esc_html($student['name']); ?></td>
                        <td><?php echo esc_html($student['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($student['class']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'manage-students'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherExams($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Exams</h2>
        <table class="table" id="teacher-exams">
            <thead><tr><th>Name</th><th>Date</th><th>Class</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['exams'] as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['name']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['class']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'edit-exam'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'delete-exam'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherOverview() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Welcome, Teacher</h2>
        <div class="row">
            <div class="col-md-4"><div class="card"><h5>Total Classes</h5><p class="display-4">3</p></div></div>
            <div class="col-md-4"><div class="card"><h5>Upcoming Exams</h5><p class="display-4">4</p></div></div>
            <div class="col-md-4"><div class="card"><h5>Students</h5><p class="display-4">45</p></div></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddExam() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Exam</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'view-exams'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="teacher">
                <input type="hidden" name="demo-section" value="exam-management">
                <input type="hidden" name="demo-action" value="view-exams">
                <div class="mb-3"><label for="exam-name">Exam Name</label><input type="text" id="exam-name" name="exam-name" required placeholder="e.g., Midterm Exam"></div>
                <div class="mb-3"><label for="exam-date">Date</label><input type="date" id="exam-date" name="exam-date" required></div>
                <div class="mb-3"><label for="exam-class">Class</label><input type="text" id="exam-class" name="exam-class" required placeholder="e.g., Class 2B"></div>
                <button type="submit" class="btn btn-primary">Add Exam</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'view-exams'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditExam($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Exam</h2>
        <div class="alert alert-info">Select an exam to edit from the Exams section.</div>
        <table class="table" id="teacher-exams">
            <thead><tr><th>Name</th><th>Date</th><th>Class</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['exams'] as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['name']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['class']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'edit-exam'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'delete-exam'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteExam($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Exam</h2>
        <div class="alert alert-warning">Click "Delete" to remove an exam.</div>
        <table class="table" id="teacher-exams">
            <thead><tr><th>Name</th><th>Date</th><th>Class</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['exams'] as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['name']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['class']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'view-exams'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Attendance Reports</h2>
        <table class="table" id="teacher-attendance">
            <thead><tr><th>Date</th><th>Student</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($data['attendance'] as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record['date']); ?></td>
                        <td><?php echo esc_html($record['student']); ?></td>
                        <td><?php echo esc_html($record['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Student Content
 */
function demoRenderStudentContent($section, $action, $data = []) {
    ob_start();
    switch ($section) {
        case 'exams':
            echo demoRenderStudentExams($data);
            break;
        case 'attendance':
            echo demoRenderStudentAttendance($data);
            break;
        case 'profile':
            echo demoRenderStudentProfile($data);
            break;
        default:
            echo demoRenderStudentOverview();
    }
    return ob_get_clean();
}

function demoRenderStudentProfile($data) {
    $student = [
        'student_id' => 'ST1001',
        'name' => 'John Doe',
        'email' => 'john@demo-pro.edu',
        'phone' => '123-456-7890',
        'class' => '1A',
        'roll_number' => '001',
        'admission_number' => 'AD1001'
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>My Profile</h2>
        <div class="card p-4">
            <p><strong>Student ID:</strong> <?php echo esc_html($student['student_id']); ?></p>
            <p><strong>Name:</strong> <?php echo esc_html($student['name']); ?></p>
            <p><strong>Email:</strong> <?php echo esc_html($student['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo esc_html($student['phone']); ?></p>
            <p><strong>Class:</strong> <?php echo esc_html($student['class']); ?></p>
            <p><strong>Roll Number:</strong> <?php echo esc_html($student['roll_number']); ?></p>
            <p><strong>Admission Number:</strong> <?php echo esc_html($student['admission_number']); ?></p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderStudentOverview() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Welcome, Student</h2>
        <div class="row">
            <div class="col-md-4"><div class="card"><h5>Class</h5><p class="display-4">1A</p></div></div>
            <div class="col-md-4"><div class="card"><h5>Upcoming Exams</h5><p class="display-4">3</p></div></div>
            <div class="col-md-4"><div class="card"><h5>Attendance Rate</h5><p class="display-4">92%</p></div></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderStudentExams($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Exams</h2>
        <table class="table" id="student-exams">
            <thead><tr><th>Name</th><th>Date</th><th>Class</th></tr></thead>
            <tbody>
                <?php foreach ($data['exams'] as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['name']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['class']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Attendance</h2>
        <table class="table" id="student-attendance">
            <thead><tr><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($data['attendance'] as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record['date']); ?></td>
                        <td><?php echo esc_html($record['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Parent Content
 */
function demoRenderParentContent($section, $action, $data = []) {
    ob_start();
    switch ($section) {
        case 'exams':
            echo demoRenderParentExams($data);
            break;
        case 'attendance':
            echo demoRenderParentAttendance($data);
            break;
        default:
            echo demoRenderParentOverview();
    }
    return ob_get_clean();
}

function demoRenderParentOverview() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Welcome, Parent</h2>
        <div class="row">
            <div class="col-md-4"><div class="card"><h5>Child</h5><p class="display-4">Demo Student</p></div></div>
            <div class="col-md-4"><div class="card"><h5>Class</h5><p class="display-4">1A</p></div></div>
            <div class="col-md-4"><div class="card"><h5>Attendance Rate</h5><p class="display-4">92%</p></div></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderParentExams($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Child Exams</h2>
        <table class="table" id="parent-exams">
            <thead><tr><th>Name</th><th>Date</th><th>Class</th></tr></thead>
            <tbody>
                <?php foreach ($data['exams'] as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['name']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['class']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderParentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Child Attendance</h2>
        <table class="table" id="parent-attendance">
            <thead><tr><th>Date</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($data['attendance'] as $record): ?>
                    <tr>
                        <td><?php echo esc_html($record['date']); ?></td>
                        <td><?php echo esc_html($record['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Superadmin Content
 */
function demoRenderSuperadminContent($section, $action, $data = []) {
    ob_start();
    if ($section === 'centers') {
        switch ($action) {
            case 'manage-centers':
                echo demoRenderSuperadminCenters($data);
                break;
            case 'add-center':
                echo demoRenderSuperadminAddCenter();
                break;
            case 'edit-center':
                echo demoRenderSuperadminEditCenter($data);
                break;
            case 'delete-center':
                echo demoRenderнерSuperadminDeleteCenter($data);
                break;
            case 'reset-password':
                echo demoRenderSuperadminResetPassword($data);
                break;
            case 'add-admin':
                echo demoRenderSuperadminAddAdmin($data);
                break;
            default:
                echo demoRenderSuperadminCenters($data);
        }
    } elseif ($section === 'students') {
        switch ($action) {
            case 'manage-students':
                echo demoRenderSuperadminStudents($data);
                break;
            case 'add-student':
                echo demoRenderSuperadminAddStudent();
                break;
            case 'edit-student':
                echo demoRenderSuperadminEditStudent($data);
                break;
            case 'delete-student':
                echo demoRenderSuperadminDeleteStudent($data);
                break;
            default:
                echo demoRenderSuperadminStudents($data);
        }
    } elseif ($section === 'teachers') {
        switch ($action) {
            case 'manage-teachers':
                echo demoRenderSuperadminTeachers($data);
                break;
            case 'add-teacher':
                echo demoRenderSuperadminAddTeacher();
                break;
            case 'edit-teacher':
                echo demoRenderSuperadminEditTeacher($data);
                break;
            case 'delete-teacher':
                echo demoRenderSuperadminDeleteTeacher($data);
                break;
            default:
                echo demoRenderSuperadminTeachers($data);
        }
    } elseif ($section === 'staff') {
        switch ($action) {
            case 'manage-staff':
                echo demoRenderSuperadminStaff($data);
                break;
            case 'add-staff':
                echo demoRenderSuperadminAddStaff();
                break;
            case 'edit-staff':
                echo demoRenderSuperadminEditStaff($data);
                break;
            case 'delete-staff':
                echo demoRenderSuperadminDeleteStaff($data);
                break;
            default:
                echo demoRenderSuperadminStaff($data);
        }
    } elseif ($section === 'attendance') {
        switch ($action) {
            case 'student-attendance':
                echo demoRenderSuperadminStudentAttendance($data);
                break;
            case 'manage-student-attendance':
                echo demoRenderSuperadminManageStudentAttendance($data);
                break;
            case 'add-student-attendance':
                echo demoRenderSuperadminAddStudentAttendance();
                break;
            case 'edit-student-attendance':
                echo demoRenderSuperadminEditStudentAttendance($data);
                break;
            case 'delete-student-attendance':
                echo demoRenderSuperadminDeleteStudentAttendance($data);
                break;
            case 'teacher-attendance':
                echo demoRenderSuperadminTeacherAttendance($data);
                break;
            case 'manage-teacher-attendance':
                echo demoRenderSuperadminManageTeacherAttendance($data);
                break;
            case 'add-teacher-attendance':
                echo demoRenderSuperadminAddTeacherAttendance();
                break;
            case 'edit-teacher-attendance':
                echo demoRenderSuperadminEditTeacherAttendance($data);
                break;
            case 'delete-teacher-attendance':
                echo demoRenderSuperadminDeleteTeacherAttendance($data);
                break;
            default:
                echo demoRenderSuperadminStudentAttendance($data);
        }
    } elseif ($section === 'subscription') {
        switch ($action) {
            case 'add-subscription':
                echo demoRenderSuperadminAddSubscription();
                break;
            case 'edit-subscription':
                echo demoRenderSuperadminEditSubscription($data);
                break;
            case 'delete-subscription':
                echo demoRenderSuperadminDeleteSubscription($data);
                break;
            default:
                echo demoRenderSuperadminSubscription($data);
        }
    } elseif ($section === 'payment_methods') {
        switch ($action) {
            case 'add-payment-method':
                echo demoRenderSuperadminAddPaymentMethods();
                break;
            case 'edit-payment-method':
                echo demoRenderSuperadminEditPaymentMethods($data);
                break;
            case 'delete-payment-method':
                echo demoRenderSuperadminDeletePaymentMethods($data);
                break;
            default:
                echo demoRenderSuperadminPaymentMethods($data);
        }
    } else {
        switch ($section) {
            case 'exams':
                echo demoRenderSuperadminExams($data);
                break;
            case 'add-exam':
                echo demoRenderSuperadminAddExam();
                break;
            case 'edit-exam':
                echo demoRenderSuperadminEditExam($data);
                break;
            case 'delete-exam':
                echo demoRenderSuperadminDeleteExam($data);
                break;
            default:
                echo demoRenderSuperadminOverview();
        }
    }
    return ob_get_clean();
}
function demoRenderSuperadminManageTeacherAttendance($data = []) {
    ob_start();
    ?>
    <div class="edu-attendance-container" style="margin-top: 80px;">
        <h2 class="edu-attendance-title">Manage Teacher Attendance</h2>
        <div class="edu-attendance-actions">
            <input type="text" id="teacher-attendance-search" class="edu-search-input" placeholder="Search Teachers..." style="margin-right: 20px; padding: 8px; width: 300px;">
            <button class="edu-button edu-button-primary" id="add-teacher-attendance-btn">Add Attendance</button>
            <button class="edu-button edu-button-secondary" id="export-teacher-attendance">Export CSV</button>
            <input type="file" id="import-teacher-attendance" accept=".csv" style="display: none;">
            <button class="edu-button edu-button-secondary" id="import-teacher-attendance-btn">Import CSV</button>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="attendance-per-page">Show:</label>
            <select id="attendance-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="teacher-attendance-table">
                <thead>
                    <tr>
                        <th>Teacher ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="teacher-attendance-table-body">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>

        <!-- Add Teacher Attendance Modal -->
        <div id="add-teacher-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="add-teacher-attendance-close">&times;</span>
                <h2>Add Teacher Attendance</h2>
                <form id="add-teacher-attendance-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-teacher-id">Teacher ID</label>
                        <input type="text" class="edu-form-input" id="add-teacher-id" name="teacher_id" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-teacher-name">Teacher Name</label>
                        <input type="text" class="edu-form-input" id="add-teacher-name" name="teacher_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-department">Department</label>
                        <input type="text" class="edu-form-input" id="add-department" name="department" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-attendance-date">Date</label>
                        <input type="date" class="edu-form-input" id="add-attendance-date" name="date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-attendance-status">Status</label>
                        <select class="edu-form-input" id="add-attendance-status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="save-teacher-attendance">Add Attendance</button>
                </form>
                <div class="edu-form-message" id="add-teacher-attendance-message"></div>
            </div>
        </div>

        <!-- Edit Teacher Attendance Modal -->
        <div id="edit-teacher-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="edit-teacher-attendance-close">&times;</span>
                <h2>Edit Teacher Attendance</h2>
                <form id="edit-teacher-attendance-form" class="edu-form">
                    <input type="hidden" id="edit-attendance-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-teacher-id">Teacher ID</label>
                        <input type="text" class="edu-form-input" id="edit-teacher-id" name="teacher_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-teacher-name">Teacher Name</label>
                        <input type="text" class="edu-form-input" id="edit-teacher-name" name="teacher_name" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-department">Department</label>
                        <input type="text" class="edu-form-input" id="edit-department" name="department" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-attendance-date">Date</label>
                        <input type="date" class="edu-form-input" id="edit-attendance-date" name="date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-attendance-status">Status</label>
                        <select class="edu-form-input" id="edit-attendance-status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-teacher-attendance">Update Attendance</button>
                </form>
                <div class="edu-form-message" id="edit-teacher-attendance-message"></div>
            </div>
        </div>

        <!-- Delete Teacher Attendance Modal -->
        <div id="delete-teacher-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="delete-teacher-attendance-close">&times;</span>
                <h2>Delete Teacher Attendance</h2>
                <p>Are you sure you want to delete attendance for <span id="delete-teacher-name"></span> on <span id="delete-attendance-date"></span>?</p>
                <input type="hidden" id="delete-attendance-id">
                <button type="button" class="edu-button edu-button-delete" id="confirm-delete-teacher-attendance">Delete</button>
                <button type="button" class="edu-button edu-button-secondary" id="cancel-delete-teacher-attendance">Cancel</button>
                <div class="edu-form-message" id="delete-teacher-attendance-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let teacherAttendanceData = <?php echo json_encode($data['teacher_attendance']); ?>;

            function loadTeacherAttendance(page, limit, query) {
                const filtered = teacherAttendanceData.filter(a => 
                    !query || a.teacher_name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach((record, index) => {
                    html += `
                        <tr data-attendance-id="${start + index}">
                            <td>${record.teacher_id}</td>
                            <td>${record.teacher_name}</td>
                            <td>${record.department}</td>
                            <td>${record.date}</td>
                            <td>${record.status}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-teacher-attendance" data-attendance-id="${start + index}">Edit</button>
                                <button class="edu-button edu-button-delete delete-teacher-attendance" data-attendance-id="${start + index}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#teacher-attendance-table-body').html(html || '<tr><td colspan="6">No attendance records found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadTeacherAttendance(currentPage, perPage, searchQuery);

            $('#teacher-attendance-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadTeacherAttendance(currentPage, perPage, searchQuery);
            });

            $('#attendance-per-page').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadTeacherAttendance(currentPage, perPage, searchQuery);
            });

            $('#next-page').on('click', function() { currentPage++; loadTeacherAttendance(currentPage, perPage, searchQuery); });
            $('#prev-page').on('click', function() { currentPage--; loadTeacherAttendance(currentPage, perPage, searchQuery); });

            $('#add-teacher-attendance-btn').on('click', function() { $('#add-teacher-attendance-modal').show(); });
            $('#add-teacher-attendance-close').on('click', function() { $('#add-teacher-attendance-modal').hide(); });
            $('#save-teacher-attendance').on('click', function() {
                const record = {
                    teacher_id: $('#add-teacher-id').val(),
                    teacher_name: $('#add-teacher-name').val(),
                    department: $('#add-department').val(),
                    date: $('#add-attendance-date').val(),
                    status: $('#add-attendance-status').val()
                };
                if (record.teacher_id && record.teacher_name && record.department && record.date && record.status) {
                    teacherAttendanceData.push(record);
                    $('#add-teacher-attendance-message').addClass('edu-success').text('Attendance added successfully!');
                    setTimeout(() => {
                        $('#add-teacher-attendance-modal').hide();
                        $('#add-teacher-attendance-message').removeClass('edu-success').text('');
                        $('#add-teacher-attendance-form')[0].reset();
                        loadTeacherAttendance(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#add-teacher-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.edit-teacher-attendance', function() {
                const attendanceId = $(this).data('attendance-id');
                const record = teacherAttendanceData[attendanceId];
                $('#edit-attendance-id').val(attendanceId);
                $('#edit-teacher-id').val(record.teacher_id);
                $('#edit-teacher-name').val(record.teacher_name);
                $('#edit-department').val(record.department);
                $('#edit-attendance-date').val(record.date);
                $('#edit-attendance-status').val(record.status);
                $('#edit-teacher-attendance-modal').show();
            });
            $('#edit-teacher-attendance-close').on('click', function() { $('#edit-teacher-attendance-modal').hide(); });
            $('#update-teacher-attendance').on('click', function() {
                const attendanceId = $('#edit-attendance-id').val();
                const record = teacherAttendanceData[attendanceId];
                record.date = $('#edit-attendance-date').val();
                record.status = $('#edit-attendance-status').val();
                if (record.date && record.status) {
                    $('#edit-teacher-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                    setTimeout(() => {
                        $('#edit-teacher-attendance-modal').hide();
                        $('#edit-teacher-attendance-message').removeClass('edu-success').text('');
                        loadTeacherAttendance(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#edit-teacher-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-teacher-attendance', function() {
                const attendanceId = $(this).data('attendance-id');
                const record = teacherAttendanceData[attendanceId];
                $('#delete-attendance-id').val(attendanceId);
                $('#delete-teacher-name').text(record.teacher_name);
                $('#delete-attendance-date').text(record.date);
                $('#delete-teacher-attendance-modal').show();
            });
            $('#delete-teacher-attendance-close, #cancel-delete-teacher-attendance').on('click', function() { $('#delete-teacher-attendance-modal').hide(); });
            $('#confirm-delete-teacher-attendance').on('click', function() {
                const attendanceId = $('#delete-attendance-id').val();
                teacherAttendanceData.splice(attendanceId, 1);
                $('#delete-teacher-attendance-message').addClass('edu-success').text('Attendance deleted successfully!');
                setTimeout(() => {
                    $('#delete-teacher-attendance-modal').hide();
                    $('#delete-teacher-attendance-message').removeClass('edu-success').text('');
                    loadTeacherAttendance(currentPage, perPage, searchQuery);
                }, 1000);
            });

            $('#export-teacher-attendance').on('click', function() {
                const csv = teacherAttendanceData.map(row => `${row.teacher_id},${row.teacher_name},${row.department},${row.date},${row.status}`).join('\n');
                const headers = 'Teacher ID,Teacher Name,Department,Date,Status\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'teacher_attendance.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('#import-teacher-attendance-btn').on('click', function() {
                $('#import-teacher-attendance').click();
            });

            $('#import-teacher-attendance').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [teacher_id, teacher_name, department, date, status] = row.split(',');
                            return { teacher_id, teacher_name, department, date, status };
                        });
                        teacherAttendanceData.push(...newRecords);
                        $('#add-teacher-attendance-message').addClass('edu-success').text('Attendance imported successfully!');
                        setTimeout(() => {
                            $('#add-teacher-attendance-message').removeClass('edu-success').text('');
                            loadTeacherAttendance(currentPage, perPage, searchQuery);
                        }, 1000);
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminManageStudentAttendance($data = []) {
    ob_start();
    ?>
    <div class="edu-attendance-container" style="margin-top: 80px;">
        <h2 class="edu-attendance-title">Manage Student Attendance</h2>
        <div class="edu-attendance-actions">
            <input type="text" id="student-attendance-search" class="edu-search-input" placeholder="Search Students..." style="margin-right: 20px; padding: 8px; width: 300px;">
            <button class="edu-button edu-button-primary" id="add-student-attendance-btn">Add Attendance</button>
            <button class="edu-button edu-button-secondary" id="export-student-attendance">Export CSV</button>
            <input type="file" id="import-student-attendance" accept=".csv" style="display: none;">
            <button class="edu-button edu-button-secondary" id="import-student-attendance-btn">Import CSV</button>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="attendance-per-page">Show:</label>
            <select id="attendance-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="student-attendance-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Center</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="student-attendance-table-body">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>

        <!-- Add Student Attendance Modal -->
        <div id="add-student-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="add-student-attendance-close">×</span>
                <h2>Add Student Attendance</h2>
                <form id="add-student-attendance-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="add-student-id" name="student_id" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="add-student-name" name="student_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-class">Class</label>
                        <input type="text" class="edu-form-input" id="add-class" name="class" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-section">Section</label>
                        <input type="text" class="edu-form-input" id="add-section" name="section" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-center">Center</label>
                        <input type="text" class="edu-form-input" id="add-center" name="center" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-attendance-date">Date</label>
                        <input type="date" class="edu-form-input" id="add-attendance-date" name="date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-attendance-status">Status</label>
                        <select class="edu-form-input" id="add-attendance-status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="save-student-attendance">Add Attendance</button>
                </form>
                <div class="edu-form-message" id="add-student-attendance-message"></div>
            </div>
        </div>

        <!-- Edit Student Attendance Modal -->
        <div id="edit-student-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="edit-student-attendance-close">×</span>
                <h2>Edit Student Attendance</h2>
                <form id="edit-student-attendance-form" class="edu-form">
                    <input type="hidden" id="edit-attendance-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="edit-student-id" name="student_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="edit-student-name" name="student_name" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-class">Class</label>
                        <input type="text" class="edu-form-input" id="edit-class" name="class" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-section">Section</label>
                        <input type="text" class="edu-form-input" id="edit-section" name="section" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-center" name="center" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-attendance-date">Date</label>
                        <input type="date" class="edu-form-input" id="edit-attendance-date" name="date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-attendance-status">Status</label>
                        <select class="edu-form-input" id="edit-attendance-status" name="status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-student-attendance">Update Attendance</button>
                </form>
                <div class="edu-form-message" id="edit-student-attendance-message"></div>
            </div>
        </div>

        <!-- Delete Student Attendance Modal -->
        <div id="delete-student-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="delete-student-attendance-close">×</span>
                <h2>Delete Student Attendance</h2>
                <p>Are you sure you want to delete attendance for <span id="delete-student-name"></span> on <span id="delete-attendance-date"></span>?</p>
                <input type="hidden" id="delete-attendance-id">
                <button type="button" class="edu-button edu-button-delete" id="confirm-delete-student-attendance">Delete</button>
                <button type="button" class="edu-button edu-button-secondary" id="cancel-delete-student-attendance">Cancel</button>
                <div class="edu-form-message" id="delete-student-attendance-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let studentAttendanceData = <?php echo json_encode($data['student_attendance'] ?? []); ?>;

            function loadStudentAttendance(page, limit, query) {
                const filtered = studentAttendanceData.filter(a => 
                    !query || a.student_name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach((record, index) => {
                    html += `
                        <tr data-attendance-id="${start + index}">
                            <td>${record.student_id}</td>
                            <td>${record.student_name}</td>
                            <td>${record.class}</td>
                            <td>${record.section}</td>
                            <td>${record.center}</td>
                            <td>${record.date}</td>
                            <td>${record.status}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-student-attendance" data-attendance-id="${start + index}">Edit</button>
                                <button class="edu-button edu-button-delete delete-student-attendance" data-attendance-id="${start + index}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#student-attendance-table-body').html(html || '<tr><td colspan="8">No attendance records found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadStudentAttendance(currentPage, perPage, searchQuery);

            $('#student-attendance-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadStudentAttendance(currentPage, perPage, searchQuery);
            });

            $('#attendance-per-page').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadStudentAttendance(currentPage, perPage, searchQuery);
            });

            $('#next-page').on('click', function() { currentPage++; loadStudentAttendance(currentPage, perPage, searchQuery); });
            $('#prev-page').on('click', function() { currentPage--; loadStudentAttendance(currentPage, perPage, searchQuery); });

            $('#add-student-attendance-btn').on('click', function() { $('#add-student-attendance-modal').show(); });
            $('#add-student-attendance-close').on('click', function() { $('#add-student-attendance-modal').hide(); });
            $('#save-student-attendance').on('click', function() {
                const record = {
                    student_id: $('#add-student-id').val(),
                    student_name: $('#add-student-name').val(),
                    class: $('#add-class').val(),
                    section: $('#add-section').val(),
                    center: $('#add-center').val(),
                    date: $('#add-attendance-date').val(),
                    status: $('#add-attendance-status').val()
                };
                if (record.student_id && record.student_name && record.class && record.section && record.center && record.date && record.status) {
                    studentAttendanceData.push(record);
                    $('#add-student-attendance-message').addClass('edu-success').text('Attendance added successfully!');
                    setTimeout(() => {
                        $('#add-student-attendance-modal').hide();
                        $('#add-student-attendance-message').removeClass('edu-success').text('');
                        $('#add-student-attendance-form')[0].reset();
                        loadStudentAttendance(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#add-student-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.edit-student-attendance', function() {
                const attendanceId = $(this).data('attendance-id');
                const record = studentAttendanceData[attendanceId];
                $('#edit-attendance-id').val(attendanceId);
                $('#edit-student-id').val(record.student_id);
                $('#edit-student-name').val(record.student_name);
                $('#edit-class').val(record.class);
                $('#edit-section').val(record.section);
                $('#edit-center').val(record.center);
                $('#edit-attendance-date').val(record.date);
                $('#edit-attendance-status').val(record.status);
                $('#edit-student-attendance-modal').show();
            });
            $('#edit-student-attendance-close').on('click', function() { $('#edit-student-attendance-modal').hide(); });
            $('#update-student-attendance').on('click', function() {
                const attendanceId = $('#edit-attendance-id').val();
                const record = studentAttendanceData[attendanceId];
                record.date = $('#edit-attendance-date').val();
                record.status = $('#edit-attendance-status').val();
                if (record.date && record.status) {
                    $('#edit-student-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                    setTimeout(() => {
                        $('#edit-student-attendance-modal').hide();
                        $('#edit-student-attendance-message').removeClass('edu-success').text('');
                        loadStudentAttendance(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#edit-student-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-student-attendance', function() {
                const attendanceId = $(this).data('attendance-id');
                const record = studentAttendanceData[attendanceId];
                $('#delete-attendance-id').val(attendanceId);
                $('#delete-student-name').text(record.student_name);
                $('#delete-attendance-date').text(record.date);
                $('#delete-student-attendance-modal').show();
            });
            $('#delete-student-attendance-close, #cancel-delete-student-attendance').on('click', function() { $('#delete-student-attendance-modal').hide(); });
            $('#confirm-delete-student-attendance').on('click', function() {
                const attendanceId = $('#delete-attendance-id').val();
                studentAttendanceData.splice(attendanceId, 1);
                $('#delete-student-attendance-message').addClass('edu-success').text('Attendance deleted successfully!');
                setTimeout(() => {
                    $('#delete-student-attendance-modal').hide();
                    $('#delete-student-attendance-message').removeClass('edu-success').text('');
                    loadStudentAttendance(currentPage, perPage, searchQuery);
                }, 1000);
            });

            $('#export-student-attendance').on('click', function() {
                const csv = studentAttendanceData.map(row => `${row.student_id},${row.student_name},${row.class},${row.section},${row.center},${row.date},${row.status}`).join('\n');
                const headers = 'Student ID,Student Name,Class,Section,Center,Date,Status\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'student_attendance.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('#import-student-attendance-btn').on('click', function() {
                $('#import-student-attendance').click();
            });

            $('#import-student-attendance').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [student_id, student_name, class_name, section, center, date, status] = row.split(',');
                            return { student_id, student_name, class: class_name, section, center, date, status };
                        });
                        studentAttendanceData.push(...newRecords);
                        $('#add-student-attendance-message').addClass('edu-success').text('Attendance imported successfully!');
                        setTimeout(() => {
                            $('#add-student-attendance-message').removeClass('edu-success').text('');
                            loadStudentAttendance(currentPage, perPage, searchQuery);
                        }, 1000);
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
// Student Attendance CRUD Functions
function demoRenderSuperadminStudentAttendance($data) {
    $action = isset($_GET['demo-action']) ? sanitize_text_field($_GET['demo-action']) : 'manage-student-attendance';
    $subaction = isset($_GET['demo-subaction']) ? sanitize_text_field($_GET['demo-subaction']) : 'view';
    $center_id = 'demo_center';
    $month = isset($_GET['month']) ? intval($_GET['month']) : 4;
    $year = isset($_GET['year']) ? intval($_GET['year']) : 2025;
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    ob_start();
    ?>
    <div class="container-fluid p-0">
        <div class="row">
            <?php echo demoRenderSidebar('superadmin', 'attendance', $action, $subaction); ?>
            <div class="col-md-9 col-12">
                <div id="student-attendance-content">
                    <?php
                    if ($action === 'manage-student-attendance') {
                        switch ($subaction) {
                            case 'view':
                                echo demoDisplayStudentAttendanceView($data, $center_id, $month, $year, $days_in_month);
                                break;
                            case 'add':
                                echo demoDisplayStudentAttendanceAdd($data, $center_id);
                                break;
                            case 'edit':
                                echo demoDisplayStudentAttendanceEdit($data, $center_id);
                                break;
                            case 'bulk-import':
                                echo demoDisplayStudentAttendanceBulkImport($data, $center_id);
                                break;
                            default:
                                echo '<div class="alert alert-warning">Section not found.</div>';
                        }
                    } else {
                        echo '<div class="alert alert-warning">Action not recognized.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// View Section
function demoDisplayStudentAttendanceView($data, $center_id, $month, $year, $days_in_month) {
    $class = isset($_GET['class']) ? sanitize_text_field($_GET['class']) : '';
    $section_filter = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : '';
    $student_id = isset($_GET['student_id']) ? sanitize_text_field($_GET['student_id']) : '';
    $student_name = isset($_GET['student_name']) ? sanitize_text_field($_GET['student_name']) : '';
    $page = max(1, isset($_GET['page']) ? intval($_GET['page']) : 1);
    $per_page = 10;

    // Filter and process data
    $student_data = [];
    foreach ($data['student_attendance'] as $record) {
        if (($class && $record['class'] !== $class) || 
            ($section_filter && $record['section'] !== $section_filter) || 
            ($student_id && $record['student_id'] !== $student_id) || 
            ($student_name && stripos($record['student_name'], $student_name) === false) || 
            (date('n', strtotime($record['date'])) != $month || date('Y', strtotime($record['date'])) != $year)) {
            continue;
        }
        $key = $record['student_id'];
        if (!isset($student_data[$key])) {
            $student_data[$key] = [
                'name' => $record['student_name'],
                'class' => $record['class'],
                'section' => $record['section'],
                'attendance' => array_fill(1, $days_in_month, ''),
                'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'F' => 0, 'H' => 0]
            ];
        }
        $day = (int)date('j', strtotime($record['date']));
        if ($day >= 1 && $day <= $days_in_month) {
            switch ($record['status']) {
                case 'Present': $student_data[$key]['attendance'][$day] = 'P'; $student_data[$key]['counts']['P']++; break;
                case 'Late': $student_data[$key]['attendance'][$day] = 'L'; $student_data[$key]['counts']['L']++; break;
                case 'Absent': $student_data[$key]['attendance'][$day] = 'A'; $student_data[$key]['counts']['A']++; break;
                case 'Full Day': $student_data[$key]['attendance'][$day] = 'F'; $student_data[$key]['counts']['F']++; break;
                case 'Holiday': $student_data[$key]['attendance'][$day] = 'H'; $student_data[$key]['counts']['H']++; break;
            }
        }
    }

    $total_students = count($student_data);
    $total_pages = ceil($total_students / $per_page);
    $student_data = array_slice($student_data, ($page - 1) * $per_page, $per_page);

    $classes = array_unique(array_column($data['student_attendance'], 'class'));
    $sections = array_unique(array_column($data['student_attendance'], 'section'));
    $month_names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    ob_start();
    ?>
    <h2 class="mb-3">Student Attendance</h2>
    <div class="mb-3">
        <form id="student-attendance-filter-form" class="row g-3">
            <div class="col-md-3 col-sm-6">
                <input type="text" id="search-student-id" class="form-control" placeholder="Student ID" value="<?php echo esc_attr($student_id); ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <input type="text" id="search-student-name" class="form-control" placeholder="Student Name" value="<?php echo esc_attr($student_name); ?>">
            </div>
            <div class="col-md-2 col-sm-6">
                <select id="search-class" class="form-select">
                    <option value="">All Classes</option>
                    <?php foreach ($classes as $cls): ?>
                        <option value="<?php echo esc_attr($cls); ?>" <?php selected($class, $cls); ?>><?php echo esc_html($cls); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select id="search-section" class="form-select">
                    <option value="">All Sections</option>
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?php echo esc_attr($sec); ?>" <?php selected($section_filter, $sec); ?>><?php echo esc_html($sec); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select id="search-month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" data-year="2025" <?php selected($month, $m); ?>><?php echo esc_html($month_names[$m-1] . ' 2025'); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-1 col-sm-12">
                <button id="search-button" type="button" class="btn btn-primary w-100" onclick="updateFilters()">Search</button>
            </div>
        </form>
    </div>
    <div class="mb-3">
        <a href="<?php echo esc_url(add_query_arg(['demo-subaction' => 'add'])); ?>" class="btn btn-primary">Add Attendance</a>
        <a href="<?php echo esc_url(add_query_arg(['demo-subaction' => 'edit'])); ?>" class="btn btn-primary">Edit Attendance</a>
        <a href="<?php echo esc_url(add_query_arg(['demo-subaction' => 'bulk-import'])); ?>" class="btn btn-primary">Bulk Import</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="student-attendance-table">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Student ID</th>
                    <th>Class</th>
                    <th>P</th>
                    <th>L</th>
                    <th>A</th>
                    <th>F</th>
                    <th>H</th>
                    <th>%</th>
                    <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                        <?php $date = sprintf('%04d-%02d-%02d', $year, $month, $day); $day_name = date('D', strtotime($date)); ?>
                        <th><?php echo "$day<br>$day_name"; ?></th>
                    <?php endfor; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($student_data)): ?>
                    <?php foreach ($student_data as $student_id => $data): ?>
                        <?php $total_days = $days_in_month - $data['counts']['H']; $percent = $total_days > 0 ? round(($data['counts']['P'] / $total_days) * 100) : 0; ?>
                        <tr>
                            <td><?php echo esc_html($data['name']); ?></td>
                            <td><?php echo esc_html($student_id); ?></td>
                            <td><?php echo esc_html($data['class'] . ' - ' . $data['section']); ?></td>
                            <td><?php echo esc_html($data['counts']['P']); ?></td>
                            <td><?php echo esc_html($data['counts']['L']); ?></td>
                            <td><?php echo esc_html($data['counts']['A']); ?></td>
                            <td><?php echo esc_html($data['counts']['F']); ?></td>
                            <td><?php echo esc_html($data['counts']['H']); ?></td>
                            <td><?php echo esc_html($percent . '%'); ?></td>
                            <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                                <td><?php echo esc_html($data['attendance'][$day] ?? ''); ?></td>
                            <?php endfor; ?>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(['demo-subaction' => 'edit', 'student_id' => $student_id])); ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm" onclick="demoDeleteStudentAttendance('<?php echo esc_js($student_id); ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="<?php echo 10 + $days_in_month; ?>" class="text-center">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center gap-2 mt-3">
        <?php if ($total_pages > 1): ?>
            <button class="btn btn-primary pagination-btn" onclick="updatePage(<?php echo ($page > 1) ? $page - 1 : 1; ?>)" <?php echo ($page <= 1) ? 'disabled' : ''; ?>>Previous</button>
            <span class="align-self-center">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <button class="btn btn-primary pagination-btn" onclick="updatePage(<?php echo ($page < $total_pages) ? $page + 1 : $total_pages; ?>)" <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>>Next</button>
        <?php endif; ?>
    </div>
    <script>
        function updateFilters() {
            const url = new URL(window.location.href);
            url.searchParams.set('demo-subaction', 'view');
            url.searchParams.set('student_id', document.getElementById('search-student-id').value);
            url.searchParams.set('student_name', document.getElementById('search-student-name').value);
            url.searchParams.set('class', document.getElementById('search-class').value);
            url.searchParams.set('section', document.getElementById('search-section').value);
            url.searchParams.set('month', document.getElementById('search-month').value);
            url.searchParams.set('year', document.getElementById('search-month').selectedOptions[0].getAttribute('data-year'));
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }
        function updatePage(page) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }
        function demoDeleteStudentAttendance(studentId) {
            if (confirm('Are you sure you want to delete all attendance records for ' + studentId + '?')) {
                alert('Attendance for ' + studentId + ' would be deleted in a real system.');
                // Simulate deletion by redirecting to view
                const url = new URL(window.location.href);
                url.searchParams.set('demo-subaction', 'view');
                window.location.href = url.toString();
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

// Add Section
function demoDisplayStudentAttendanceAdd($data, $center_id) {
    $students = [];
    foreach ($data['student_attendance'] as $record) {
        $students[$record['student_id']] = ['name' => $record['student_name'], 'class' => $record['class'], 'section' => $record['section']];
    }
    $students = array_unique($students, SORT_REGULAR);

    if (isset($_GET['add']) && $_GET['student_id']) {
        $student_id = sanitize_text_field($_GET['student_id']);
        $date = sanitize_text_field($_GET['date'] ?? date('Y-m-d'));
        $status = sanitize_text_field($_GET['status'] ?? 'Present');
        echo '<div class="alert alert-success">Attendance for ' . esc_html($student_id) . ' on ' . esc_html($date) . ' added as ' . esc_html($status) . ' (simulated).</div>';
    }

    ob_start();
    ?>
    <h2 class="mb-3">Add Student Attendance</h2>
    <form method="get" class="mb-3">
        <input type="hidden" name="demo-role" value="superadmin">
        <input type="hidden" name="demo-section" value="attendance">
        <input type="hidden" name="demo-action" value="manage-student-attendance">
        <input type="hidden" name="demo-subaction" value="add">
        <div class="mb-3">
            <label for="student_id" class="form-label">Student</label>
            <select name="student_id" id="student_id" class="form-select" required>
                <option value="">Select Student</option>
                <?php foreach ($students as $id => $student): ?>
                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($student['name'] . ' (' . $id . ') - ' . $student['class'] . ' ' . $student['section']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="attendance_date" class="form-label">Date</label>
            <input type="date" name="date" id="attendance_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Present">Present</option>
                <option value="Late">Late</option>
                <option value="Absent">Absent</option>
                <option value="Full Day">Full Day</option>
                <option value="Holiday">Holiday</option>
            </select>
        </div>
        <button type="submit" name="add" value="1" class="btn btn-primary">Add Attendance</button>
    </form>
    <?php
    return ob_get_clean();
}

// Edit Section
function demoDisplayStudentAttendanceEdit($data, $center_id) {
    $students = [];
    foreach ($data['student_attendance'] as $record) {
        $students[$record['student_id']] = ['name' => $record['student_name'], 'class' => $record['class'], 'section' => $record['section']];
    }
    $students = array_unique($students, SORT_REGULAR);

    $selected_student = isset($_GET['student_id']) ? sanitize_text_field($_GET['student_id']) : '';
    $selected_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';

    $edit_record = null;
    if ($selected_student && $selected_date) {
        foreach ($data['student_attendance'] as $record) {
            if ($record['student_id'] === $selected_student && $record['date'] === $selected_date) {
                $edit_record = $record;
                break;
            }
        }
    }

    if (isset($_GET['edit']) && $edit_record) {
        $status = sanitize_text_field($_GET['status'] ?? $edit_record['status']);
        echo '<div class="alert alert-success">Attendance for ' . esc_html($selected_student) . ' on ' . esc_html($selected_date) . ' updated to ' . esc_html($status) . ' (simulated).</div>';
    }

    ob_start();
    ?>
    <h2 class="mb-3">Edit Student Attendance</h2>
    <form method="get" class="mb-3">
        <input type="hidden" name="demo-role" value="superadmin">
        <input type="hidden" name="demo-section" value="attendance">
        <input type="hidden" name="demo-action" value="manage-student-attendance">
        <input type="hidden" name="demo-subaction" value="edit">
        <h3>Select Student and Date</h3>
        <div class="mb-3">
            <label for="student_id" class="form-label">Select Student:</label>
            <select name="student_id" id="student_id" class="form-select" required onchange="this.form.submit()">
                <option value="">-- Select Student --</option>
                <?php foreach ($students as $id => $student): ?>
                    <option value="<?php echo esc_attr($id); ?>" <?php selected($selected_student, $id); ?>><?php echo esc_html($student['name'] . ' (' . $id . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($selected_student): ?>
        <div class="mb-3">
            <label for="date" class="form-label">Select Date:</label>
            <select name="date" id="date" class="form-select" required>
                <option value="">-- Select Date --</option>
                <?php foreach ($data['student_attendance'] as $record): ?>
                    <?php if ($record['student_id'] === $selected_student): ?>
                        <option value="<?php echo esc_attr($record['date']); ?>" <?php selected($selected_date, $record['date']); ?>><?php echo esc_html($record['date'] . ' (' . $record['status'] . ')'); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($edit_record): ?>
        <h3>Edit Attendance</h3>
        <div class="mb-3">
            <label class="form-label">Student</label>
            <input type="text" class="form-control" value="<?php echo esc_attr($edit_record['student_name'] . ' (' . $edit_record['student_id'] . ')'); ?>" disabled>
        </div>
        <div class="mb-3">
            <label for="attendance_date" class="form-label">Date</label>
            <input type="date" name="date" id="attendance_date" class="form-control" value="<?php echo esc_attr($edit_record['date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Present" <?php selected($edit_record['status'], 'Present'); ?>>Present</option>
                <option value="Late" <?php selected($edit_record['status'], 'Late'); ?>>Late</option>
                <option value="Absent" <?php selected($edit_record['status'], 'Absent'); ?>>Absent</option>
                <option value="Full Day" <?php selected($edit_record['status'], 'Full Day'); ?>>Full Day</option>
                <option value="Holiday" <?php selected($edit_record['status'], 'Holiday'); ?>>Holiday</option>
            </select>
        </div>
        <button type="submit" name="edit" value="1" class="btn btn-primary">Update Attendance</button>
        <?php endif; ?>
        <?php endif; ?>
    </form>
    <?php
    return ob_get_clean();
}

// Bulk Import Section
function demoDisplayStudentAttendanceBulkImport($data, $center_id) {
    $classes = array_unique(array_column($data['student_attendance'], 'class'));

    if (isset($_GET['bulk']) && isset($_GET['class']) && isset($_GET['file'])) {
        echo '<div class="alert alert-success">Bulk import for class ' . esc_html($_GET['class']) . ' simulated with file ' . esc_html($_GET['file']) . '.</div>';
    }

    ob_start();
    ?>
    <h2 class="mb-3">Bulk Import Student Attendance</h2>
    <form method="get" class="mb-3">
        <input type="hidden" name="demo-role" value="superadmin">
        <input type="hidden" name="demo-section" value="attendance">
        <input type="hidden" name="demo-action" value="manage-student-attendance">
        <input type="hidden" name="demo-subaction" value="bulk-import">
        <div class="mb-3">
            <label for="class" class="form-label">Class:</label>
            <select name="class" id="class" class="form-select" required>
                <option value="">-- Select Class --</option>
                <?php foreach ($classes as $cls): ?>
                    <option value="<?php echo esc_attr($cls); ?>"><?php echo esc_html($cls); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">Upload CSV File:</label>
            <input type="text" name="file" id="file" class="form-control" placeholder="Simulate file upload" required>
            <p><small>Format: student_id, student_name, date1 (e.g., 4/1/2025), date2, etc.</small></p>
        </div>
        <button type="submit" name="bulk" value="1" class="btn btn-primary">Import Attendance</button>
    </form>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddStudentAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Student Attendance</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="superadmin">
                <input type="hidden" name="demo-section" value="attendance">
                <input type="hidden" name="demo-action" value="manage-student-attendance">
                <div class="mb-3">
                    <label for="student-id">Student ID</label>
                    <input type="text" id="student-id" name="student-id" value="ST<?php echo rand(1000, 9999); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="student-name">Student Name</label>
                    <input type="text" id="student-name" name="student-name" required>
                </div>
                <div class="mb-3">
                    <label for="center-id">Education Center ID</label>
                    <input type="text" id="center-id" name="center-id" required>
                </div>
                <div class="mb-3">
                    <label for="class">Class</label>
                    <input type="text" id="class" name="class" required>
                </div>
                <div class="mb-3">
                    <label for="section">Section</label>
                    <input type="text" id="section" name="section" required>
                </div>
                <div class="mb-3">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Late">Late</option>
                        <option value="Absent">Absent</option>
                        <option value="Full Day">Full Day</option>
                        <option value="Holiday">Holiday</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                <div class="mb-3">
                    <label for="teacher-id">Teacher ID</label>
                    <input type="text" id="teacher-id" name="teacher-id" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Attendance</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Student Attendance</h2>
        <div class="alert alert-info">Select an attendance record to edit from the list below.</div>
        <div class="attendance-table-wrapper">
            <table class="table" id="superadmin-student-attendance">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Edu Center ID</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Subject</th>
                        <th>Teacher ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['student_attendance'] ?? [] as $attendance): ?>
                        <tr>
                            <td><?php echo esc_html($attendance['student_name']); ?></td>
                            <td><?php echo esc_html($attendance['student_id']); ?></td>
                            <td><?php echo esc_html($attendance['education_center_id']); ?></td>
                            <td><?php echo esc_html($attendance['class']); ?></td>
                            <td><?php echo esc_html($attendance['section']); ?></td>
                            <td><?php echo esc_html($attendance['date']); ?></td>
                            <td><?php echo esc_html($attendance['status']); ?></td>
                            <td><?php echo esc_html($attendance['subject']); ?></td>
                            <td><?php echo esc_html($attendance['teacher_id']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'edit-student-attendance'])); ?>" class="btn btn-warning">Edit</a>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'delete-student-attendance'])); ?>" class="btn btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Student Attendance</h2>
        <div class="alert alert-warning">Click "Delete" to remove an attendance record.</div>
        <div class="attendance-table-wrapper">
            <table class="table" id="superadmin-student-attendance">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Edu Center ID</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Subject</th>
                        <th>Teacher ID</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['student_attendance'] ?? [] as $attendance): ?>
                        <tr>
                            <td><?php echo esc_html($attendance['student_name']); ?></td>
                            <td><?php echo esc_html($attendance['student_id']); ?></td>
                            <td><?php echo esc_html($attendance['education_center_id']); ?></td>
                            <td><?php echo esc_html($attendance['class']); ?></td>
                            <td><?php echo esc_html($attendance['section']); ?></td>
                            <td><?php echo esc_html($attendance['date']); ?></td>
                            <td><?php echo esc_html($attendance['status']); ?></td>
                            <td><?php echo esc_html($attendance['subject']); ?></td>
                            <td><?php echo esc_html($attendance['teacher_id']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherSidebar($role, $section, $active_action = 'manage-student-attendance', $active_subaction = 'view') {
    ob_start();
    ?>
    <div class="col-md-3 col-12 mb-3">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Teacher Dashboard</h5>
            </div>
            <div class="card-body p-0">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($section === 'attendance' ? 'active bg-light' : ''); ?>" href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'attendance'])); ?>">Attendance</a>
                        <?php if ($section === 'attendance'): ?>
                        <ul class="nav flex-column ps-3">
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($active_action === 'manage-student-attendance' ? 'active' : ''); ?>" href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance', 'demo-subaction' => 'view'])); ?>">Student Attendance</a>
                                <?php if ($active_action === 'manage-student-attendance'): ?>
                                <ul class="nav flex-column ps-3">
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($active_subaction === 'view' ? 'active' : ''); ?>" href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance', 'demo-subaction' => 'view'])); ?>">View</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($active_subaction === 'add' ? 'active' : ''); ?>" href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance', 'demo-subaction' => 'add'])); ?>">Add</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($active_subaction === 'edit' ? 'active' : ''); ?>" href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance', 'demo-subaction' => 'edit'])); ?>">Edit</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link <?php echo ($active_subaction === 'bulk-import' ? 'active' : ''); ?>" href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance', 'demo-subaction' => 'bulk-import'])); ?>">Bulk Import</a>
                                    </li>
                                </ul>
                                <?php endif; ?>
                            </li>
                        </ul>
                        <?php endif; ?>
                    </li>
                    <!-- Add more sections like Grades, Schedule, etc., as needed -->
                </ul>
            </div>
        </div>
    </div>
    <style>
        .nav-link { padding: 10px 15px; color: #333; }
        .nav-link:hover { background-color: #f8f9fa; }
        .nav-link.active { background-color: #007bff; color: white; }
        .nav.flex-column .nav-item .nav-link { padding-left: 30px; }
        .nav.flex-column .nav-item .nav.flex-column .nav-link { padding-left: 45px; }
        .card { height: 100%; }
        .card-body { padding: 0; }
    </style>
    <?php
    return ob_get_clean();
}
// Teacher Attendance CRUD Functions
function demoRenderSuperadminTeacherAttendance($data) {
    $action = isset($_GET['demo-action']) ? sanitize_text_field($_GET['demo-action']) : 'manage-teacher-attendance';
    $subaction = isset($_GET['demo-subaction']) ? sanitize_text_field($_GET['demo-subaction']) : 'view';
    $center_id = 'demo_center';
    $month = isset($_GET['month']) ? intval($_GET['month']) : 4;
    $year = isset($_GET['year']) ? intval($_GET['year']) : 2025;
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);

    ob_start();
    ?>
    <div class="container-fluid p-0">
        <div class="row">
            <?php echo demoRenderSidebar('superadmin', 'attendance', $action, $subaction); ?>
            <div class="col-md-9 col-12">
                <div id="teacher-attendance-content">
                    <?php
                    if ($action === 'manage teacher-attendance') {
                        switch ($subaction) {
                            case 'view':
                                echo demoDisplayTeacherAttendanceView($data, $center_id, $month, $year, $days_in_month);
                                break;
                            case 'add':
                                echo demoDisplayTeacherAttendanceAdd($data, $center_id);
                                break;
                            case 'edit':
                                echo demoDisplayTeacherAttendanceEdit($data, $center_id);
                                break;
                            case 'bulk-import':
                                echo demoDisplayTeacherAttendanceBulkImport($data, $center_id);
                                break;
                            default:
                                echo '<div class="alert alert-warning">Section not found.</div>';
                        }
                    } else {
                        echo '<div class="alert alert-warning">Action not recognized.</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// View, Add, Edit, Bulk Import functions remain the same as previously provided

// View Section
function demoDisplayTeacherAttendanceView($data, $center_id, $month, $year, $days_in_month) {
    $department = isset($_GET['department']) ? sanitize_text_field($_GET['department']) : '';
    $teacher_id = isset($_GET['teacher_id']) ? sanitize_text_field($_GET['teacher_id']) : '';
    $teacher_name = isset($_GET['teacher_name']) ? sanitize_text_field($_GET['teacher_name']) : '';
    $page = max(1, isset($_GET['page']) ? intval($_GET['page']) : 1);
    $per_page = 10;

    $teacher_data = [];
    foreach ($data['teacher_attendance'] as $record) {
        if (($department && $record['department'] !== $department) || 
            ($teacher_id && $record['teacher_id'] !== $teacher_id) || 
            ($teacher_name && stripos($record['teacher_name'], $teacher_name) === false) || 
            (date('n', strtotime($record['date'])) != $month || date('Y', strtotime($record['date'])) != $year)) {
            continue;
        }
        $key = $record['teacher_id'];
        if (!isset($teacher_data[$key])) {
            $teacher_data[$key] = [
                'name' => $record['teacher_name'],
                'department' => $record['department'],
                'attendance' => array_fill(1, $days_in_month, ''),
                'counts' => ['P' => 0, 'L' => 0, 'A' => 0, 'O' => 0]
            ];
        }
        $day = (int)date('j', strtotime($record['date']));
        if ($day >= 1 && $day <= $days_in_month) {
            switch ($record['status']) {
                case 'Present': $teacher_data[$key]['attendance'][$day] = 'P'; $teacher_data[$key]['counts']['P']++; break;
                case 'Late': $teacher_data[$key]['attendance'][$day] = 'L'; $teacher_data[$key]['counts']['L']++; break;
                case 'Absent': $teacher_data[$key]['attendance'][$day] = 'A'; $teacher_data[$key]['counts']['A']++; break;
                case 'On Leave': $teacher_data[$key]['attendance'][$day] = 'O'; $teacher_data[$key]['counts']['O']++; break;
            }
        }
    }

    $total_teachers = count($teacher_data);
    $total_pages = ceil($total_teachers / $per_page);
    $teacher_data = array_slice($teacher_data, ($page - 1) * $per_page, $per_page);

    $departments = array_unique(array_column($data['teacher_attendance'], 'department'));
    $month_names = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    ob_start();
    ?>
    <h2 class="mb-3">Teacher Attendance</h2>
    <div class="mb-3">
        <form id="teacher-attendance-filter-form" class="row g-3">
            <div class="col-md-3 col-sm-6">
                <input type="text" id="search-teacher-id" class="form-control" placeholder="Teacher ID" value="<?php echo esc_attr($teacher_id); ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <input type="text" id="search-teacher-name" class="form-control" placeholder="Teacher Name" value="<?php echo esc_attr($teacher_name); ?>">
            </div>
            <div class="col-md-3 col-sm-6">
                <select id="search-department" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo esc_attr($dept); ?>" <?php selected($department, $dept); ?>><?php echo esc_html($dept); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select id="search-month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>" data-year="2025" <?php selected($month, $m); ?>><?php echo esc_html($month_names[$m-1] . ' 2025'); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-1 col-sm-12">
                <button id="search-button" type="button" class="btn btn-primary w-100" onclick="updateFilters()">Search</button>
            </div>
        </form>
    </div>
    <div class="mb-3">
        <a href="<?php echo esc_url(add_query_arg(['demo-subaction' => 'add'])); ?>" class="btn btn-primary">Add Attendance</a>
        <a href="<?php echo esc_url(add_query_arg(['demo-subaction' => 'edit'])); ?>" class="btn btn-primary">Edit Attendance</a>
        <a href="<?php echo esc_url(add_query_arg(['demo-subaction' => 'bulk-import'])); ?>" class="btn btn-primary">Bulk Import</a>
    </div>
    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="teacher-attendance-table">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Teacher ID</th>
                    <th>Department</th>
                    <th>P</th>
                    <th>L</th>
                    <th>A</th>
                    <th>O</th>
                    <th>%</th>
                    <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                        <?php $date = sprintf('%04d-%02d-%02d', $year, $month, $day); $day_name = date('D', strtotime($date)); ?>
                        <th><?php echo "$day<br>$day_name"; ?></th>
                    <?php endfor; ?>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($teacher_data)): ?>
                    <?php foreach ($teacher_data as $teacher_id => $data): ?>
                        <?php $total_days = $days_in_month - $data['counts']['O']; $percent = $total_days > 0 ? round(($data['counts']['P'] / $total_days) * 100) : 0; ?>
                        <tr>
                            <td><?php echo esc_html($data['name']); ?></td>
                            <td><?php echo esc_html($teacher_id); ?></td>
                            <td><?php echo esc_html($data['department']); ?></td>
                            <td><?php echo esc_html($data['counts']['P']); ?></td>
                            <td><?php echo esc_html($data['counts']['L']); ?></td>
                            <td><?php echo esc_html($data['counts']['A']); ?></td>
                            <td><?php echo esc_html($data['counts']['O']); ?></td>
                            <td><?php echo esc_html($percent . '%'); ?></td>
                            <?php for ($day = 1; $day <= $days_in_month; $day++): ?>
                                <td><?php echo esc_html($data['attendance'][$day] ?? ''); ?></td>
                            <?php endfor; ?>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(['demo-subaction' => 'edit', 'teacher_id' => $teacher_id])); ?>" class="btn btn-warning btn-sm">Edit</a>
                                <button class="btn btn-danger btn-sm" onclick="demoDeleteTeacherAttendance('<?php echo esc_js($teacher_id); ?>')">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="<?php echo 9 + $days_in_month; ?>" class="text-center">No records found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center gap-2 mt-3">
        <?php if ($total_pages > 1): ?>
            <button class="btn btn-primary pagination-btn" onclick="updatePage(<?php echo ($page > 1) ? $page - 1 : 1; ?>)" <?php echo ($page <= 1) ? 'disabled' : ''; ?>>Previous</button>
            <span class="align-self-center">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
            <button class="btn btn-primary pagination-btn" onclick="updatePage(<?php echo ($page < $total_pages) ? $page + 1 : $total_pages; ?>)" <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>>Next</button>
        <?php endif; ?>
    </div>
    <script>
        function updateFilters() {
            const url = new URL(window.location.href);
            url.searchParams.set('demo-subaction', 'view');
            url.searchParams.set('teacher_id', document.getElementById('search-teacher-id').value);
            url.searchParams.set('teacher_name', document.getElementById('search-teacher-name').value);
            url.searchParams.set('department', document.getElementById('search-department').value);
            url.searchParams.set('month', document.getElementById('search-month').value);
            url.searchParams.set('year', document.getElementById('search-month').selectedOptions[0].getAttribute('data-year'));
            url.searchParams.set('page', '1');
            window.location.href = url.toString();
        }
        function updatePage(page) {
            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }
        function demoDeleteTeacherAttendance(teacherId) {
            if (confirm('Are you sure you want to delete all attendance records for ' + teacherId + '?')) {
                alert('Attendance for ' + teacherId + ' would be deleted in a real system.');
                const url = new URL(window.location.href);
                url.searchParams.set('demo-subaction', 'view');
                window.location.href = url.toString();
            }
        }
    </script>
    <?php
    return ob_get_clean();
}

// Add Section
function demoDisplayTeacherAttendanceAdd($data, $center_id) {
    $teachers = [];
    foreach ($data['teacher_attendance'] as $record) {
        $teachers[$record['teacher_id']] = ['name' => $record['teacher_name'], 'department' => $record['department']];
    }
    $teachers = array_unique($teachers, SORT_REGULAR);

    if (isset($_GET['add']) && $_GET['teacher_id']) {
        $teacher_id = sanitize_text_field($_GET['teacher_id']);
        $date = sanitize_text_field($_GET['date'] ?? date('Y-m-d'));
        $status = sanitize_text_field($_GET['status'] ?? 'Present');
        echo '<div class="alert alert-success">Attendance for ' . esc_html($teacher_id) . ' on ' . esc_html($date) . ' added as ' . esc_html($status) . ' (simulated).</div>';
    }

    ob_start();
    ?>
    <h2 class="mb-3">Add Teacher Attendance</h2>
    <form method="get" class="mb-3">
        <input type="hidden" name="demo-role" value="superadmin">
        <input type="hidden" name="demo-section" value="attendance">
        <input type="hidden" name="demo-action" value="manage-teacher-attendance">
        <input type="hidden" name="demo-subaction" value="add">
        <div class="mb-3">
            <label for="teacher_id" class="form-label">Teacher</label>
            <select name="teacher_id" id="teacher_id" class="form-select" required>
                <option value="">Select Teacher</option>
                <?php foreach ($teachers as $id => $teacher): ?>
                    <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($teacher['name'] . ' (' . $id . ') - ' . $teacher['department']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="attendance_date" class="form-label">Date</label>
            <input type="date" name="date" id="attendance_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Present">Present</option>
                <option value="Late">Late</option>
                <option value="Absent">Absent</option>
                <option value="On Leave">On Leave</option>
            </select>
        </div>
        <button type="submit" name="add" value="1" class="btn btn-primary">Add Attendance</button>
    </form>
    <?php
    return ob_get_clean();
}

// Edit Section
function demoDisplayTeacherAttendanceEdit($data, $center_id) {
    $teachers = [];
    foreach ($data['teacher_attendance'] as $record) {
        $teachers[$record['teacher_id']] = ['name' => $record['teacher_name'], 'department' => $record['department']];
    }
    $teachers = array_unique($teachers, SORT_REGULAR);

    $selected_teacher = isset($_GET['teacher_id']) ? sanitize_text_field($_GET['teacher_id']) : '';
    $selected_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';

    $edit_record = null;
    if ($selected_teacher && $selected_date) {
        foreach ($data['teacher_attendance'] as $record) {
            if ($record['teacher_id'] === $selected_teacher && $record['date'] === $selected_date) {
                $edit_record = $record;
                break;
            }
        }
    }

    if (isset($_GET['edit']) && $edit_record) {
        $status = sanitize_text_field($_GET['status'] ?? $edit_record['status']);
        echo '<div class="alert alert-success">Attendance for ' . esc_html($selected_teacher) . ' on ' . esc_html($selected_date) . ' updated to ' . esc_html($status) . ' (simulated).</div>';
    }

    ob_start();
    ?>
    <h2 class="mb-3">Edit Teacher Attendance</h2>
    <form method="get" class="mb-3">
        <input type="hidden" name="demo-role" value="superadmin">
        <input type="hidden" name="demo-section" value="attendance">
        <input type="hidden" name="demo-action" value="manage-teacher-attendance">
        <input type="hidden" name="demo-subaction" value="edit">
        <h3>Select Teacher and Date</h3>
        <div class="mb-3">
            <label for="teacher_id" class="form-label">Select Teacher:</label>
            <select name="teacher_id" id="teacher_id" class="form-select" required onchange="this.form.submit()">
                <option value="">-- Select Teacher --</option>
                <?php foreach ($teachers as $id => $teacher): ?>
                    <option value="<?php echo esc_attr($id); ?>" <?php selected($selected_teacher, $id); ?>><?php echo esc_html($teacher['name'] . ' (' . $id . ')'); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($selected_teacher): ?>
        <div class="mb-3">
            <label for="date" class="form-label">Select Date:</label>
            <select name="date" id="date" class="form-select" required>
                <option value="">-- Select Date --</option>
                <?php foreach ($data['teacher_attendance'] as $record): ?>
                    <?php if ($record['teacher_id'] === $selected_teacher): ?>
                        <option value="<?php echo esc_attr($record['date']); ?>" <?php selected($selected_date, $record['date']); ?>><?php echo esc_html($record['date'] . ' (' . $record['status'] . ')'); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($edit_record): ?>
        <h3>Edit Attendance</h3>
        <div class="mb-3">
            <label class="form-label">Teacher</label>
            <input type="text" class="form-control" value="<?php echo esc_attr($edit_record['teacher_name'] . ' (' . $edit_record['teacher_id'] . ')'); ?>" disabled>
        </div>
        <div class="mb-3">
            <label for="attendance_date" class="form-label">Date</label>
            <input type="date" name="date" id="attendance_date" class="form-control" value="<?php echo esc_attr($edit_record['date']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Present" <?php selected($edit_record['status'], 'Present'); ?>>Present</option>
                <option value="Late" <?php selected($edit_record['status'], 'Late'); ?>>Late</option>
                <option value="Absent" <?php selected($edit_record['status'], 'Absent'); ?>>Absent</option>
                <option value="On Leave" <?php selected($edit_record['status'], 'On Leave'); ?>>On Leave</option>
            </select>
        </div>
        <button type="submit" name="edit" value="1" class="btn btn-primary">Update Attendance</button>
        <?php endif; ?>
        <?php endif; ?>
    </form>
    <?php
    return ob_get_clean();
}

// Bulk Import Section
function demoDisplayTeacherAttendanceBulkImport($data, $center_id) {
    $departments = array_unique(array_column($data['teacher_attendance'], 'department'));

    if (isset($_GET['bulk']) && isset($_GET['department']) && isset($_GET['file'])) {
        echo '<div class="alert alert-success">Bulk import for department ' . esc_html($_GET['department']) . ' simulated with file ' . esc_html($_GET['file']) . '.</div>';
    }

    ob_start();
    ?>
    <h2 class="mb-3">Bulk Import Teacher Attendance</h2>
    <form method="get" class="mb-3">
        <input type="hidden" name="demo-role" value="superadmin">
        <input type="hidden" name="demo-section" value="attendance">
        <input type="hidden" name="demo-action" value="manage-teacher-attendance">
        <input type="hidden" name="demo-subaction" value="bulk-import">
        <div class="mb-3">
            <label for="department" class="form-label">Department:</label>
            <select name="department" id="department" class="form-select" required>
                <option value="">-- Select Department --</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?php echo esc_attr($dept); ?>"><?php echo esc_html($dept); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">Upload CSV File:</label>
            <input type="text" name="file" id="file" class="form-control" placeholder="Simulate file upload" required>
            <p><small>Format: teacher_id, teacher_name, date1 (e.g., 4/1/2025), date2, etc.</small></p>
        </div>
        <button type="submit" name="bulk" value="1" class="btn btn-primary">Import Attendance</button>
    </form>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddTeacherAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Teacher Attendance</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="superadmin">
                <input type="hidden" name="demo-section" value="attendance">
                <input type="hidden" name="demo-action" value="manage-teacher-attendance">
                <div class="mb-3">
                    <label for="teacher-id">Teacher ID</label>
                    <input type="text" id="teacher-id" name="teacher-id" value="TR<?php echo rand(1000, 9999); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="teacher-name">Teacher Name</label>
                    <input type="text" id="teacher-name" name="teacher-name" required>
                </div>
                <div class="mb-3">
                    <label for="center-id">Education Center ID</label>
                    <input type="text" id="center-id" name="center-id" required>
                </div>
                <div class="mb-3">
                    <label for="department">Department</label>
                    <input type="text" id="department" name="department" required>
                </div>
                <div class="mb-3">
                    <label for="date">Date</label>
                    <input type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="status">Status</label>
                    <select id="status" name="status" required>
                        <option value="Present">Present</option>
                        <option value="Late">Late</option>
                        <option value="Absent">Absent</option>
                        <option value="On Leave">On Leave</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add Attendance</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditTeacherAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Teacher Attendance</h2>
        <div class="alert alert-info">Select an attendance record to edit from the list below.</div>
        <div class="attendance-table-wrapper">
            <table class="table" id="superadmin-teacher-attendance">
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Teacher ID</th>
                        <th>Edu Center ID</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['teacher_attendance'] ?? [] as $attendance): ?>
                        <tr>
                            <td><?php echo esc_html($attendance['teacher_name']); ?></td>
                            <td><?php echo esc_html($attendance['teacher_id']); ?></td>
                            <td><?php echo esc_html($attendance['education_center_id']); ?></td>
                            <td><?php echo esc_html($attendance['department']); ?></td>
                            <td><?php echo esc_html($attendance['date']); ?></td>
                            <td><?php echo esc_html($attendance['status']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'edit-teacher-attendance'])); ?>" class="btn btn-warning">Edit</a>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'delete-teacher-attendance'])); ?>" class="btn btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteTeacherAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Teacher Attendance</h2>
        <div class="alert alert-warning">Click "Delete" to remove an attendance record.</div>
        <div class="attendance-table-wrapper">
            <table class="table" id="superadmin-teacher-attendance">
                <thead>
                    <tr>
                        <th>Teacher Name</th>
                        <th>Teacher ID</th>
                        <th>Edu Center ID</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['teacher_attendance'] ?? [] as $attendance): ?>
                        <tr>
                            <td><?php echo esc_html($attendance['teacher_name']); ?></td>
                            <td><?php echo esc_html($attendance['teacher_id']); ?></td>
                            <td><?php echo esc_html($attendance['education_center_id']); ?></td>
                            <td><?php echo esc_html($attendance['department']); ?></td>
                            <td><?php echo esc_html($attendance['date']); ?></td>
                            <td><?php echo esc_html($attendance['status']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="btn btn-danger">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// New Staff CRUD Functions
function demoRenderSuperadminStaff($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Manage Staff</h2>
        <table class="table" id="superadmin-staff">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Center</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['staff'] ?? [] as $staff): ?>
                    <tr>
                        <td><?php echo esc_html($staff['staff_id']); ?></td>
                        <td><?php echo esc_html($staff['name']); ?></td>
                        <td><?php echo esc_html($staff['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($staff['role']); ?></td>
                        <td><?php echo esc_html($staff['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'edit-staff'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'delete-staff'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddStaff() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Staff</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'manage-staff'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="superadmin">
                <input type="hidden" name="demo-section" value="staff">
                <input type="hidden" name="demo-action" value="manage-staff">
                <div class="mb-3"><label for="staff-id">Staff ID</label><input type="text" id="staff-id" name="staff-id" value="SF<?php echo rand(1000, 9999); ?>" readonly></div>
                <div class="mb-3"><label for="staff-name">Name</label><input type="text" id="staff-name" name="staff-name" required></div>
                <div class="mb-3"><label for="staff-email">Email</label><input type="email" id="staff-email" name="staff-email"></div>
                <div class="mb-3"><label for="staff-role">Role</label><input type="text" id="staff-role" name="staff-role" required placeholder="e.g., Receptionist, Accountant"></div>
                <div class="mb-3"><label for="staff-center">Center</label><input type="text" id="staff-center" name="staff-center" required></div>
                <button type="submit" class="btn btn-primary">Add Staff</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'manage-staff'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditStaff($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Staff</h2>
        <div class="alert alert-info">Select a staff member to edit from the list below.</div>
        <table class="table" id="superadmin-staff">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Center</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['staff'] ?? [] as $staff): ?>
                    <tr>
                        <td><?php echo esc_html($staff['staff_id']); ?></td>
                        <td><?php echo esc_html($staff['name']); ?></td>
                        <td><?php echo esc_html($staff['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($staff['role']); ?></td>
                        <td><?php echo esc_html($staff['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'edit-staff'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'delete-staff'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteStaff($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Staff</h2>
        <div class="alert alert-warning">Click "Delete" to remove a staff member.</div>
        <table class="table" id="superadmin-staff">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Center</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['staff'] ?? [] as $staff): ?>
                    <tr>
                        <td><?php echo esc_html($staff['staff_id']); ?></td>
                        <td><?php echo esc_html($staff['name']); ?></td>
                        <td><?php echo esc_html($staff['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($staff['role']); ?></td>
                        <td><?php echo esc_html($staff['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'manage-staff'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// New CRUD functions for Superadmin Students
function demoRenderSuperadminStudents($data = []) {
    ob_start();
    ?>
    <div class="edu-student-container" style="margin-top: 80px;">
        <h2 class="edu-student-title">Manage Students</h2>
        <div class="edu-student-actions">
            <input type="text" id="student-search" class="edu-search-input" placeholder="Search Students..." style="margin-right: 20px; padding: 8px; width: 300px;">
            <button class="edu-button edu-button-primary" id="add-student-btn">Add Student</button>
            <button class="edu-button edu-button-secondary" id="export-students">Export CSV</button>
            <input type="file" id="import-students" accept=".csv" style="display: none;">
            <button class="edu-button edu-button-secondary" id="import-students-btn">Import CSV</button>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="students-per-page">Show:</label>
            <select id="students-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="student-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Email</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="student-table-body">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>

        <!-- Add Student Modal -->
        <div id="add-student-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="add-student-close">×</span>
                <h2>Add Student</h2>
                <form id="add-student-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="add-student-id" name="student_id" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="add-student-name" name="student_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-class">Class</label>
                        <input type="text" class="edu-form-input" id="add-class" name="class" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-section">Section</label>
                        <input type="text" class="edu-form-input" id="add-section" name="section" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-email">Email</label>
                        <input type="email" class="edu-form-input" id="add-email" name="email" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-center">Center</label>
                        <input type="text" class="edu-form-input" id="add-center" name="center" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="save-student">Add Student</button>
                </form>
                <div class="edu-form-message" id="add-student-message"></div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div id="edit-student-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="edit-student-close">×</span>
                <h2>Edit Student</h2>
                <form id="edit-student-form" class="edu-form">
                    <input type="hidden" id="edit-student-index">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="edit-student-id" name="student_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="edit-student-name" name="student_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-class">Class</label>
                        <input type="text" class="edu-form-input" id="edit-class" name="class" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-section">Section</label>
                        <input type="text" class="edu-form-input" id="edit-section" name="section" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-email">Email</label>
                        <input type="email" class="edu-form-input" id="edit-email" name="email" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-center" name="center" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-student">Update Student</button>
                </form>
                <div class="edu-form-message" id="edit-student-message"></div>
            </div>
        </div>

        <!-- Delete Student Modal -->
        <div id="delete-student-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="delete-student-close">×</span>
                <h2>Delete Student</h2>
                <p>Are you sure you want to delete <span id="delete-student-name"></span>?</p>
                <input type="hidden" id="delete-student-index">
                <button type="button" class="edu-button edu-button-delete" id="confirm-delete-student">Delete</button>
                <button type="button" class="edu-button edu-button-secondary" id="cancel-delete-student">Cancel</button>
                <div class="edu-form-message" id="delete-student-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let studentData = <?php echo json_encode($data['students'] ?? []); ?>;

            function loadStudents(page, limit, query) {
                const filtered = studentData.filter(s => 
                    !query || s.student_name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach((student, index) => {
                    html += `
                        <tr data-student-index="${start + index}">
                            <td>${student.student_id}</td>
                            <td>${student.student_name}</td>
                            <td>${student.class}</td>
                            <td>${student.section}</td>
                            <td>${student.email}</td>
                            <td>${student.center}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-student" data-student-index="${start + index}">Edit</button>
                                <button class="edu-button edu-button-delete delete-student" data-student-index="${start + index}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#student-table-body').html(html || '<tr><td colspan="7">No students found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadStudents(currentPage, perPage, searchQuery);

            $('#student-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadStudents(currentPage, perPage, searchQuery);
            });

            $('#students-per-page').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadStudents(currentPage, perPage, searchQuery);
            });

            $('#next-page').on('click', function() { currentPage++; loadStudents(currentPage, perPage, searchQuery); });
            $('#prev-page').on('click', function() { currentPage--; loadStudents(currentPage, perPage, searchQuery); });

            $('#add-student-btn').on('click', function() { $('#add-student-modal').show(); });
            $('#add-student-close').on('click', function() { $('#add-student-modal').hide(); });
            $('#save-student').on('click', function() {
                const student = {
                    student_id: $('#add-student-id').val(),
                    student_name: $('#add-student-name').val(),
                    class: $('#add-class').val(),
                    section: $('#add-section').val(),
                    email: $('#add-email').val(),
                    center: $('#add-center').val()
                };
                if (student.student_id && student.student_name && student.class && student.section && student.email && student.center) {
                    studentData.push(student);
                    $('#add-student-message').addClass('edu-success').text('Student added successfully!');
                    setTimeout(() => {
                        $('#add-student-modal').hide();
                        $('#add-student-message').removeClass('edu-success').text('');
                        $('#add-student-form')[0].reset();
                        loadStudents(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#add-student-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.edit-student', function() {
                const studentIndex = $(this).data('student-index');
                const student = studentData[studentIndex];
                $('#edit-student-index').val(studentIndex);
                $('#edit-student-id').val(student.student_id);
                $('#edit-student-name').val(student.student_name);
                $('#edit-class').val(student.class);
                $('#edit-section').val(student.section);
                $('#edit-email').val(student.email);
                $('#edit-center').val(student.center);
                $('#edit-student-modal').show();
            });
            $('#edit-student-close').on('click', function() { $('#edit-student-modal').hide(); });
            $('#update-student').on('click', function() {
                const studentIndex = $('#edit-student-index').val();
                const student = studentData[studentIndex];
                student.student_name = $('#edit-student-name').val();
                student.class = $('#edit-class').val();
                student.section = $('#edit-section').val();
                student.email = $('#edit-email').val();
                student.center = $('#edit-center').val();
                if (student.student_name && student.class && student.section && student.email && student.center) {
                    $('#edit-student-message').addClass('edu-success').text('Student updated successfully!');
                    setTimeout(() => {
                        $('#edit-student-modal').hide();
                        $('#edit-student-message').removeClass('edu-success').text('');
                        loadStudents(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#edit-student-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-student', function() {
                const studentIndex = $(this).data('student-index');
                const student = studentData[studentIndex];
                $('#delete-student-index').val(studentIndex);
                $('#delete-student-name').text(student.student_name);
                $('#delete-student-modal').show();
            });
            $('#delete-student-close, #cancel-delete-student').on('click', function() { $('#delete-student-modal').hide(); });
            $('#confirm-delete-student').on('click', function() {
                const studentIndex = $('#delete-student-index').val();
                studentData.splice(studentIndex, 1);
                $('#delete-student-message').addClass('edu-success').text('Student deleted successfully!');
                setTimeout(() => {
                    $('#delete-student-modal').hide();
                    $('#delete-student-message').removeClass('edu-success').text('');
                    loadStudents(currentPage, perPage, searchQuery);
                }, 1000);
            });

            $('#export-students').on('click', function() {
                const csv = studentData.map(row => `${row.student_id},${row.student_name},${row.class},${row.section},${row.email},${row.center}`).join('\n');
                const headers = 'Student ID,Student Name,Class,Section,Email,Center\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'students.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('#import-students-btn').on('click', function() {
                $('#import-students').click();
            });

            $('#import-students').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [student_id, student_name, class_name, section, email, center] = row.split(',');
                            return { student_id, student_name, class: class_name, section, email, center };
                        });
                        studentData.push(...newRecords);
                        $('#add-student-message').addClass('edu-success').text('Students imported successfully!');
                        setTimeout(() => {
                            $('#add-student-message').removeClass('edu-success').text('');
                            loadStudents(currentPage, perPage, searchQuery);
                        }, 1000);
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddStudent() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Student</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'manage-students'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="superadmin">
                <input type="hidden" name="demo-section" value="students">
                <input type="hidden" name="demo-action" value="manage-students">
                <div class="mb-3"><label for="student-id">Student ID</label><input type="text" id="student-id" name="student-id" value="ST<?php echo rand(1000, 9999); ?>" readonly></div>
                <div class="mb-3"><label for="student-name">Name</label><input type="text" id="student-name" name="student-name" required></div>
                <div class="mb-3"><label for="student-email">Email</label><input type="email" id="student-email" name="student-email"></div>
                <div class="mb-3"><label for="student-center">Center</label><input type="text" id="student-center" name="student-center" required></div>
                <button type="submit" class="btn btn-primary">Add Student</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'manage-students'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditStudent($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Student</h2>
        <div class="alert alert-info">Select a student to edit from the list below.</div>
        <table class="table" id="superadmin-students">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Center</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['students'] ?? [] as $student): ?>
                    <tr>
                        <td><?php echo esc_html($student['id']); ?></td>
                        <td><?php echo esc_html($student['name']); ?></td>
                        <td><?php echo esc_html($student['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($student['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'edit-student'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'delete-student'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteStudent($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Student</h2>
        <div class="alert alert-warning">Click "Delete" to remove a student.</div>
        <table class="table" id="superadmin-students">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Center</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['students'] ?? [] as $student): ?>
                    <tr>
                        <td><?php echo esc_html($student['id']); ?></td>
                        <td><?php echo esc_html($student['name']); ?></td>
                        <td><?php echo esc_html($student['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($student['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'manage-students'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

// New CRUD functions for Superadmin Teachers
function demoRenderSuperadminTeachers($data = []) {
    ob_start();
    ?>
    <div class="edu-teacher-container" style="margin-top: 80px;">
        <h2 class="edu-teacher-title">Manage Teachers</h2>
        <div class="edu-teacher-actions">
            <input type="text" id="teacher-search" class="edu-search-input" placeholder="Search Teachers..." style="margin-right: 20px; padding: 8px; width: 300px;">
            <button class="edu-button edu-button-primary" id="add-teacher-btn">Add Teacher</button>
            <button class="edu-button edu-button-secondary" id="export-teachers">Export CSV</button>
            <input type="file" id="import-teachers" accept=".csv" style="display: none;">
            <button class="edu-button edu-button-secondary" id="import-teachers-btn">Import CSV</button>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="teachers-per-page">Show:</label>
            <select id="teachers-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="teacher-table">
                <thead>
                    <tr>
                        <th>Teacher ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Email</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="teacher-table-body">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>

        <!-- Add Teacher Modal -->
        <div id="add-teacher-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="add-teacher-close">×</span>
                <h2>Add Teacher</h2>
                <form id="add-teacher-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-teacher-id">Teacher ID</label>
                        <input type="text" class="edu-form-input" id="add-teacher-id" name="teacher_id" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-teacher-name">Teacher Name</label>
                        <input type="text" class="edu-form-input" id="add-teacher-name" name="teacher_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-department">Department</label>
                        <input type="text" class="edu-form-input" id="add-department" name="department" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-email">Email</label>
                        <input type="email" class="edu-form-input" id="add-email" name="email" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-center">Center</label>
                        <input type="text" class="edu-form-input" id="add-center" name="center" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="save-teacher">Add Teacher</button>
                </form>
                <div class="edu-form-message" id="add-teacher-message"></div>
            </div>
        </div>

        <!-- Edit Teacher Modal -->
        <div id="edit-teacher-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="edit-teacher-close">×</span>
                <h2>Edit Teacher</h2>
                <form id="edit-teacher-form" class="edu-form">
                    <input type="hidden" id="edit-teacher-index">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-teacher-id">Teacher ID</label>
                        <input type="text" class="edu-form-input" id="edit-teacher-id" name="teacher_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-teacher-name">Teacher Name</label>
                        <input type="text" class="edu-form-input" id="edit-teacher-name" name="teacher_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-department">Department</label>
                        <input type="text" class="edu-form-input" id="edit-department" name="department" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-email">Email</label>
                        <input type="email" class="edu-form-input" id="edit-email" name="email" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-center" name="center" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-teacher">Update Teacher</button>
                </form>
                <div class="edu-form-message" id="edit-teacher-message"></div>
            </div>
        </div>

        <!-- Delete Teacher Modal -->
        <div id="delete-teacher-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="delete-teacher-close">×</span>
                <h2>Delete Teacher</h2>
                <p>Are you sure you want to delete <span id="delete-teacher-name"></span>?</p>
                <input type="hidden" id="delete-teacher-index">
                <button type="button" class="edu-button edu-button-delete" id="confirm-delete-teacher">Delete</button>
                <button type="button" class="edu-button edu-button-secondary" id="cancel-delete-teacher">Cancel</button>
                <div class="edu-form-message" id="delete-teacher-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let teacherData = <?php echo json_encode($data['teachers'] ?? []); ?>;

            function loadTeachers(page, limit, query) {
                const filtered = teacherData.filter(t => 
                    !query || t.teacher_name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach((teacher, index) => {
                    html += `
                        <tr data-teacher-index="${start + index}">
                            <td>${teacher.teacher_id}</td>
                            <td>${teacher.teacher_name}</td>
                            <td>${teacher.department}</td>
                            <td>${teacher.email}</td>
                            <td>${teacher.center}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-teacher" data-teacher-index="${start + index}">Edit</button>
                                <button class="edu-button edu-button-delete delete-teacher" data-teacher-index="${start + index}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#teacher-table-body').html(html || '<tr><td colspan="6">No teachers found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadTeachers(currentPage, perPage, searchQuery);

            $('#teacher-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadTeachers(currentPage, perPage, searchQuery);
            });

            $('#teachers-per-page').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadTeachers(currentPage, perPage, searchQuery);
            });

            $('#next-page').on('click', function() { currentPage++; loadTeachers(currentPage, perPage, searchQuery); });
            $('#prev-page').on('click', function() { currentPage--; loadTeachers(currentPage, perPage, searchQuery); });

            $('#add-teacher-btn').on('click', function() { $('#add-teacher-modal').show(); });
            $('#add-teacher-close').on('click', function() { $('#add-teacher-modal').hide(); });
            $('#save-teacher').on('click', function() {
                const teacher = {
                    teacher_id: $('#add-teacher-id').val(),
                    teacher_name: $('#add-teacher-name').val(),
                    department: $('#add-department').val(),
                    email: $('#add-email').val(),
                    center: $('#add-center').val()
                };
                if (teacher.teacher_id && teacher.teacher_name && teacher.department && teacher.email && teacher.center) {
                    teacherData.push(teacher);
                    $('#add-teacher-message').addClass('edu-success').text('Teacher added successfully!');
                    setTimeout(() => {
                        $('#add-teacher-modal').hide();
                        $('#add-teacher-message').removeClass('edu-success').text('');
                        $('#add-teacher-form')[0].reset();
                        loadTeachers(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#add-teacher-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.edit-teacher', function() {
                const teacherIndex = $(this).data('teacher-index');
                const teacher = teacherData[teacherIndex];
                $('#edit-teacher-index').val(teacherIndex);
                $('#edit-teacher-id').val(teacher.teacher_id);
                $('#edit-teacher-name').val(teacher.teacher_name);
                $('#edit-department').val(teacher.department);
                $('#edit-email').val(teacher.email);
                $('#edit-center').val(teacher.center);
                $('#edit-teacher-modal').show();
            });
            $('#edit-teacher-close').on('click', function() { $('#edit-teacher-modal').hide(); });
            $('#update-teacher').on('click', function() {
                const teacherIndex = $('#edit-teacher-index').val();
                const teacher = teacherData[teacherIndex];
                teacher.teacher_name = $('#edit-teacher-name').val();
                teacher.department = $('#edit-department').val();
                teacher.email = $('#edit-email').val();
                teacher.center = $('#edit-center').val();
                if (teacher.teacher_name && teacher.department && teacher.email && teacher.center) {
                    $('#edit-teacher-message').addClass('edu-success').text('Teacher updated successfully!');
                    setTimeout(() => {
                        $('#edit-teacher-modal').hide();
                        $('#edit-teacher-message').removeClass('edu-success').text('');
                        loadTeachers(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#edit-teacher-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-teacher', function() {
                const teacherIndex = $(this).data('teacher-index');
                const teacher = teacherData[teacherIndex];
                $('#delete-teacher-index').val(teacherIndex);
                $('#delete-teacher-name').text(teacher.teacher_name);
                $('#delete-teacher-modal').show();
            });
            $('#delete-teacher-close, #cancel-delete-teacher').on('click', function() { $('#delete-teacher-modal').hide(); });
            $('#confirm-delete-teacher').on('click', function() {
                const teacherIndex = $('#delete-teacher-index').val();
                teacherData.splice(teacherIndex, 1);
                $('#delete-teacher-message').addClass('edu-success').text('Teacher deleted successfully!');
                setTimeout(() => {
                    $('#delete-teacher-modal').hide();
                    $('#delete-teacher-message').removeClass('edu-success').text('');
                    loadTeachers(currentPage, perPage, searchQuery);
                }, 1000);
            });

            $('#export-teachers').on('click', function() {
                const csv = teacherData.map(row => `${row.teacher_id},${row.teacher_name},${row.department},${row.email},${row.center}`).join('\n');
                const headers = 'Teacher ID,Teacher Name,Department,Email,Center\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'teachers.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('#import-teachers-btn').on('click', function() {
                $('#import-teachers').click();
            });

            $('#import-teachers').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [teacher_id, teacher_name, department, email, center] = row.split(',');
                            return { teacher_id, teacher_name, department, email, center };
                        });
                        teacherData.push(...newRecords);
                        $('#add-teacher-message').addClass('edu-success').text('Teachers imported successfully!');
                        setTimeout(() => {
                            $('#add-teacher-message').removeClass('edu-success').text('');
                            loadTeachers(currentPage, perPage, searchQuery);
                        }, 1000);
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddTeacher() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Teacher</h2>
        <div class="card p-4">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'manage-teachers'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="superadmin">
                <input type="hidden" name="demo-section" value="teachers">
                <input type="hidden" name="demo-action" value="manage-teachers">
                <div class="mb-3"><label for="teacher-id">Teacher ID</label><input type="text" id="teacher-id" name="teacher-id" value="TR<?php echo rand(1000, 9999); ?>" readonly></div>
                <div class="mb-3"><label for="teacher-name">Name</label><input type="text" id="teacher-name" name="teacher-name" required></div>
                <div class="mb-3"><label for="teacher-email">Email</label><input type="email" id="teacher-email" name="teacher-email"></div>
                <div class="mb-3"><label for="teacher-center">Center</label><input type="text" id="teacher-center" name="teacher-center" required></div>
                <div class="mb-3"><label for="teacher-subject">Subject</label><input type="text" id="teacher-subject" name="teacher-subject" required></div>
                <button type="submit" class="btn btn-primary">Add Teacher</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'manage-teachers'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditTeacher($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Teacher</h2>
        <div class="alert alert-info">Select a teacher to edit from the list below.</div>
        <table class="table" id="superadmin-teachers">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Center</th><th>Subject</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['teachers'] ?? [] as $teacher): ?>
                    <tr>
                        <td><?php echo esc_html($teacher['teacher_id']); ?></td>
                        <td><?php echo esc_html($teacher['name']); ?></td>
                        <td><?php echo esc_html($teacher['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($teacher['center']); ?></td>
                        <td><?php echo esc_html($teacher['subject']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'edit-teacher'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'delete-teacher'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteTeacher($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Teacher</h2>
        <div class="alert alert-warning">Click "Delete" to remove a teacher.</div>
        <table class="table" id="superadmin-teachers">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Center</th><th>Subject</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['teachers'] ?? [] as $teacher): ?>
                    <tr>
                        <td><?php echo esc_html($teacher['teacher_id']); ?></td>
                        <td><?php echo esc_html($teacher['name']); ?></td>
                        <td><?php echo esc_html($teacher['email'] ?? 'N/A'); ?></td>
                        <td><?php echo esc_html($teacher['center']); ?></td>
                        <td><?php echo esc_html($teacher['subject']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'manage-teachers'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}


function demoRenderSuperadminCenters() {
    $action = isset($_GET['demo-action']) ? sanitize_text_field($_GET['demo-action']) : 'manage-centers';
    ob_start();
    ?>
    <div class="edu-centers-container" style="margin-top: 80px;">
        <!-- Loader -->
        <div id="edu-loader" class="edu-loader" style="display: none;">
            <div class="edu-loader-container">
                <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
            </div>
        </div>

        <?php if ($action === 'manage-centers'): ?>
            <h2 class="edu-centers-title">Educational Centers Management</h2>
            <div class="edu-centers-actions">
                <button class="edu-button edu-button-primary" id="add-center-btn">Add New Center</button>
                <input type="text" id="center-search" class="edu-search-input" placeholder="Search Centers..." style="margin-left: 20px; padding: 8px; width: 300px;">
            </div>
            <div class="edu-pagination" style="margin: 20px 0;">
                <label for="centers-per-page">Show:</label>
                <select id="centers-per-page" class="edu-select" style="margin-right: 20px;">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
                <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
                <span id="page-info" style="margin: 0 10px;"></span>
                <button class="edu-button edu-button-nav" id="next-page">Next</button>
            </div>
            <div class="edu-table-wrapper">
                <div class="export-tools" id="export-tools" style="margin-bottom: 10px;"></div>
                <table class="edu-table" id="centers-table">
                    <thead>
                        <tr>
                            <th>Educational Center ID</th>
                            <th>Name</th>
                            <th>Admin ID</th>
                            <th>Logo</th>
                            <th>Location</th>
                            <th>Mobile</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="centers-table-body">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>

        <?php elseif ($action === 'add-center'): ?>
            <h2 class="edu-centers-title">Add New Center</h2>
            <div class="edu-form-container">
                <form id="add-center-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="center-name">Center Name</label>
                        <input type="text" class="edu-form-input" id="center-name" name="center_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="center-admin-id">Admin ID</label>
                        <input type="text" class="edu-form-input" id="center-admin-id" name="admin_id" readonly placeholder="Assigned when adding new admin">
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="center-edu-id">Educational Center ID</label>
                        <input type="text" class="edu-form-input" id="center-edu-id" name="educational_center_id" readonly placeholder="Generated on save">
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="center-logo">Institute Logo</label>
                        <input type="file" class="edu-form-input" id="center-logo" name="institute_logo" accept="image/*">
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="center-location">Location</label>
                        <input type="text" class="edu-form-input" id="center-location" name="location">
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="center-mobile">Mobile Number</label>
                        <input type="number" class="edu-form-input" id="center-mobile" name="mobile_number" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="center-email">Email ID</label>
                        <input type="email" class="edu-form-input" id="center-email" name="email_id" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="save-center">Save Center</button>
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>" class="edu-button edu-button-secondary">Back to Manage Centers</a>
                </form>
                <div class="edu-form-message" id="add-center-message"></div>
            </div>

        <?php elseif ($action === 'edit-center'): ?>
            <h2 class="edu-centers-title">Edit Center</h2>
            <div class="edu-form-container">
                <form id="edit-center-select-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-select">Select Center to Edit</label>
                        <select id="edit-center-select" class="edu-form-input" required>
                            <option value="">-- Select a Center --</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                </form>
                <form id="edit-center-form" class="edu-form" style="display: none;">
                    <input type="hidden" id="edit-center-id" name="center_id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-name">Center Name</label>
                        <input type="text" class="edu-form-input" id="edit-center-name" name="center_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-admin-id">Admin ID</label>
                        <input type="text" class="edu-form-input" id="edit-center-admin-id" name="admin_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-edu-id">Educational Center ID</label>
                        <input type="text" class="edu-form-input" id="edit-center-edu-id" name="educational_center_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-logo">Institute Logo</label>
                        <input type="file" class="edu-form-input" id="edit-center-logo" name="institute_logo" accept="image/*">
                        <img id="edit-center-logo-preview" src="" alt="Current Logo" class="edu-logo-preview" style="display: none;">
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-location">Location</label>
                        <input type="text" class="edu-form-input" id="edit-center-location" name="location">
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-mobile">Mobile Number</label>
                        <input type="number" class="edu-form-input" id="edit-center-mobile" name="mobile_number" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-email">Email ID</label>
                        <input type="email" class="edu-form-input" id="edit-center-email" name="email_id" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-center">Update Center</button>
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>" class="edu-button edu-button-secondary">Back to Manage Centers</a>
                </form>
                <div class="edu-form-message" id="edit-center-message"></div>
            </div>

        <?php elseif ($action === 'delete-center'): ?>
            <h2 class="edu-centers-title">Delete Center</h2>
            <div class="edu-form-container">
                <form id="delete-center-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="delete-center-select">Select Center to Delete</label>
                        <select id="delete-center-select" class="edu-form-input" required>
                            <option value="">-- Select a Center --</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-delete" id="delete-center-btn">Delete Center</button>
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>" class="edu-button edu-button-secondary">Back to Manage Centers</a>
                </form>
                <div class="edu-form-message" id="delete-center-message"></div>
            </div>

        <?php elseif ($action === 'reset-password'): ?>
            <h2 class="edu-centers-title">Reset Admin Password</h2>
            <div class="edu-form-container">
                <form id="change-password-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="reset-center-select">Select Center</label>
                        <select id="reset-center-select" class="edu-form-input" required>
                            <option value="">-- Select a Center --</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                    <p>Send a password reset email to the institute admin.</p>
                    <button type="button" class="edu-button edu-button-primary" id="send-reset-link">Send Reset Link</button>
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>" class="edu-button edu-button-secondary">Back to Manage Centers</a>
                </form>
                <div class="edu-form-message" id="change-password-message"></div>
            </div>

        <?php elseif ($action === 'add-admin'): ?>
            <h2 class="edu-centers-title">Add New Admin</h2>
            <div class="edu-form-container">
                <form id="add-new-admin-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="new-admin-center-select">Select Center</label>
                        <select id="new-admin-center-select" class="edu-form-input" required>
                            <option value="">-- Select a Center --</option>
                            <!-- Populated via JS -->
                        </select>
                    </div>
                    <input type="hidden" id="new-admin-center-id" name="center_id">
                    <input type="hidden" id="new-admin-educational-center-id" name="educational_center_id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="new-admin-id">Admin ID</label>
                        <input type="text" class="edu-form-input" id="new-admin-id" name="admin_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="new-admin-name">Admin Name</label>
                        <input type="text" class="edu-form-input" id="new-admin-name" name="admin_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="new-admin-email">Email ID</label>
                        <input type="email" class="edu-form-input" id="new-admin-email" name="email_id" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="new-admin-password">Password</label>
                        <input type="password" class="edu-form-input" id="new-admin-password" name="password" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="new-admin-mobile">Mobile Number</label>
                        <input type="number" class="edu-form-input" id="new-admin-mobile" name="mobile_number" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="save-new-admin">Save New Admin</button>
                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>" class="edu-button edu-button-secondary">Back to Manage Centers</a>
                </form>
                <div class="edu-form-message" id="add-new-admin-message"></div>
            </div>
        <?php endif; ?>

        <!-- Modals for Manage Centers Actions -->
        <?php if ($action === 'manage-centers'): ?>
            <!-- Add Center Modal -->
            <div class="edu-modal" id="add-center-modal" style="display: none;">
                <div class="edu-modal-content">
                    <span class="edu-modal-close" data-modal="add-center-modal">×</span>
                    <h3>Add New Center</h3>
                    <form id="add-center-modal-form" class="edu-form">
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="modal-center-name">Center Name</label>
                            <input type="text" class="edu-form-input" id="modal-center-name" name="center_name" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="modal-center-admin-id">Admin ID</label>
                            <input type="text" class="edu-form-input" id="modal-center-admin-id" name="admin_id" readonly placeholder="Assigned when adding new admin">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="modal-center-edu-id">Educational Center ID</label>
                            <input type="text" class="edu-form-input" id="modal-center-edu-id" name="educational_center_id" readonly placeholder="Generated on save">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="modal-center-logo">Institute Logo</label>
                            <input type="file" class="edu-form-input" id="modal-center-logo" name="institute_logo" accept="image/*">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="modal-center-location">Location</label>
                            <input type="text" class="edu-form-input" id="modal-center-location" name="location">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="modal-center-mobile">Mobile Number</label>
                            <input type="number" class="edu-form-input" id="modal-center-mobile" name="mobile_number" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="modal-center-email">Email ID</label>
                            <input type="email" class="edu-form-input" id="modal-center-email" name="email_id" required>
                        </div>
                        <button type="button" class="edu-button edu-button-primary" id="save-modal-center">Save Center</button>
                    </form>
                    <div class="edu-form-message" id="add-center-modal-message"></div>
                </div>
            </div>

            <!-- Edit Center Modal -->
            <div class="edu-modal" id="edit-center-modal" style="display: none;">
                <div class="edu-modal-content">
                    <span class="edu-modal-close" data-modal="edit-center-modal">×</span>
                    <h3>Edit Center</h3>
                    <form id="edit-center-modal-form" class="edu-form">
                        <input type="hidden" id="edit-modal-center-id" name="center_id">
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-modal-center-name">Center Name</label>
                            <input type="text" class="edu-form-input" id="edit-modal-center-name" name="center_name" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-modal-center-admin-id">Admin ID</label>
                            <input type="text" class="edu-form-input" id="edit-modal-center-admin-id" name="admin_id" readonly>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-modal-center-edu-id">Educational Center ID</label>
                            <input type="text" class="edu-form-input" id="edit-modal-center-edu-id" name="educational_center_id" readonly>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-modal-center-logo">Institute Logo</label>
                            <input type="file" class="edu-form-input" id="edit-modal-center-logo" name="institute_logo" accept="image/*">
                            <img id="edit-modal-center-logo-preview" src="" alt="Current Logo" class="edu-logo-preview" style="display: none;">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-modal-center-location">Location</label>
                            <input type="text" class="edu-form-input" id="edit-modal-center-location" name="location">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-modal-center-mobile">Mobile Number</label>
                            <input type="number" class="edu-form-input" id="edit-modal-center-mobile" name="mobile_number" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-modal-center-email">Email ID</label>
                            <input type="email" class="edu-form-input" id="edit-modal-center-email" name="email_id" required>
                        </div>
                        <button type="button" class="edu-button edu-button-primary" id="update-modal-center">Update Center</button>
                    </form>
                    <div class="edu-form-message" id="edit-center-modal-message"></div>
                </div>
            </div>

            <!-- Change Password Modal -->
            <div class="edu-modal" id="change-password-modal" style="display: none;">
                <div class="edu-modal-content">
                    <span class="edu-modal-close" data-modal="change-password-modal">×</span>
                    <h3>Change Institute Admin Password</h3>
                    <form id="change-password-modal-form" class="edu-form">
                        <input type="hidden" id="change-modal-center-id" name="center_id">
                        <p>Send a password reset email to the institute admin.</p>
                        <button type="button" class="edu-button edu-button-primary" id="send-modal-reset-link">Send Reset Link</button>
                    </form>
                    <div class="edu-form-message" id="change-password-modal-message"></div>
                </div>
            </div>

            <!-- Add New Admin Modal -->
            <div class="edu-modal" id="add-new-admin-modal" style="display: none;">
                <div class="edu-modal-content">
                    <span class="edu-modal-close" data-modal="add-new-admin-modal">×</span>
                    <h3>Add New Institute Admin</h3>
                    <form id="add-new-admin-modal-form" class="edu-form">
                        <input type="hidden" id="new-admin-modal-center-id" name="center_id">
                        <input type="hidden" id="new-admin-modal-educational-center-id" name="educational_center_id">
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="new-admin-modal-id">Admin ID</label>
                            <input type="text" class="edu-form-input" id="new-admin-modal-id" name="admin_id" readonly>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="new-admin-modal-name">Admin Name</label>
                            <input type="text" class="edu-form-input" id="new-admin-modal-name" name="admin_name" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="new-admin-modal-email">Email ID</label>
                            <input type="email" class="edu-form-input" id="new-admin-modal-email" name="email_id" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="new-admin-modal-password">Password</label>
                            <input type="password" class="edu-form-input" id="new-admin-modal-password" name="password" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="new-admin-modal-mobile">Mobile Number</label>
                            <input type="number" class="edu-form-input" id="new-admin-modal-mobile" name="mobile_number" required>
                        </div>
                        <button type="button" class="edu-button edu-button-primary" id="save-new-admin-modal">Save New Admin</button>
                    </form>
                    <div class="edu-form-message" id="add-new-admin-modal-message"></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let centersData = [
                { ID: 1, educational_center_id: 'EC001', educational_center_name: 'Central Academy', admin_id: 'ADM001', institute_logo: '<?php echo esc_js(plugin_dir_url(__FILE__) . "../logo instituto.jpg"); ?>', location: 'New York', mobile_number: '123-456-7890', email_id: 'central@demo-pro.edu' },
                { ID: 2, educational_center_id: 'EC002', educational_center_name: 'Southern Institute', admin_id: 'ADM002', institute_logo: '<?php echo esc_js(plugin_dir_url(__FILE__) . "../logo instituto.jpg"); ?>', location: 'Texas', mobile_number: '234-567-8901', email_id: 'southern@demo-pro.edu' },
                { ID: 3, educational_center_id: 'EC003', educational_center_name: 'Northern School', admin_id: 'Unassigned', institute_logo: 'https://via.placeholder.com/50', location: 'Canada', mobile_number: '345-678-9012', email_id: 'northern@demo-pro.edu' }
            ];

            function showLoader() { $('#edu-loader').css('display', 'flex'); }
            function hideLoader() { $('#edu-loader').css('display', 'none'); }
            function openModal(modalId) { 
                showLoader(); 
                $(modalId).css({'display': 'block', 'z-index': '1000'}); 
                setTimeout(hideLoader, 100); 
            }
            function closeModal(modalId) { 
                $(modalId).css('display', 'none'); 
                $(modalId + ' .edu-form-message').removeClass('edu-success edu-error').text(''); 
                hideLoader(); 
            }

            function populateSelectOptions(selector) {
                let options = '<option value="">-- Select a Center --</option>';
                centersData.forEach(center => {
                    options += `<option value="${center.ID}">${center.educational_center_name} (${center.educational_center_id})</option>`;
                });
                $(selector).html(options);
            }

            function getTableData() {
                const table = $('#centers-table')[0];
                const headers = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim()).slice(0, -1);
                const rows = Array.from(table.querySelectorAll('tbody tr')).map(row => {
                    const cells = Array.from(row.querySelectorAll('td')).slice(0, -1);
                    return cells.map(td => td.textContent.trim());
                });
                return [headers, ...rows];
            }

            function exportToCSV() {
                const data = getTableData();
                const csv = data.map(row => row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(',')).join('\n');
                const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `centers_${new Date().toISOString().slice(0,10)}.csv`;
                link.click();
            }

            function exportToExcel() {
                const data = getTableData();
                const ws = XLSX.utils.aoa_to_sheet(data);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, 'Centers');
                XLSX.writeFile(wb, `centers_${new Date().toISOString().slice(0,10)}.xlsx`);
            }

            function generatePDF() {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF({ unit: 'mm', format: 'a4' });
                const data = getTableData();
                const instituteName = 'Demo Pro';
                const instituteLogo = '<?php echo esc_js(plugin_dir_url(__FILE__) . '../logo instituto.jpg'); ?>';
                const pageWidth = doc.internal.pageSize.width;
                const pageHeight = doc.internal.pageSize.height;
                const margin = 10;
                const borderColor = [70, 131, 180];

                doc.setDrawColor(...borderColor);
                doc.setLineWidth(1);
                doc.rect(margin, margin, pageWidth - 2 * margin, pageHeight - 2 * margin);
                doc.addImage(instituteLogo, 'JPEG', (pageWidth - 24) / 2, 15, 24, 24);
                doc.setFontSize(18);
                doc.setTextColor(...borderColor);
                doc.text(instituteName.toUpperCase(), pageWidth / 2, 45, { align: 'center' });
                doc.setFontSize(12);
                doc.setTextColor(102);
                doc.text('Educational Centers List', pageWidth / 2, 55, { align: 'center' });
                doc.setDrawColor(...borderColor);
                doc.line(margin + 5, 60, pageWidth - margin - 5, 60);

                const details = [
                    ['Date', new Date().toLocaleDateString()],
                    ['Total Centers', String(data.length - 1)]
                ];
                let y = 70;
                details.forEach(([label, value]) => {
                    doc.setFillColor(245, 245, 245);
                    doc.rect(margin + 5, y, 50, 6, 'F');
                    doc.setTextColor(...borderColor);
                    doc.setFont('helvetica', 'bold');
                    doc.text(label, margin + 7, y + 4);
                    doc.setTextColor(51);
                    doc.setFont('helvetica', 'normal');
                    doc.text(String(value), margin + 60, y + 4);
                    y += 6;
                });

                doc.autoTable({
                    startY: y + 10,
                    head: [data[0]],
                    body: data.slice(1),
                    theme: 'striped',
                    styles: { fontSize: 11, cellPadding: 2, overflow: 'linebreak', halign: 'center', textColor: [51, 51, 51] },
                    headStyles: { fillColor: borderColor, textColor: [255, 255, 255], fontStyle: 'bold' },
                    alternateRowStyles: { fillColor: [249, 249, 249] }
                });

                const finalY = doc.lastAutoTable.finalY || y + 10;
                doc.setFontSize(9);
                doc.setTextColor(102);
                doc.text(`This is an Online Generated Centers List issued by ${instituteName}`, pageWidth / 2, finalY + 20, { align: 'center' });
                doc.text(`Generated on ${new Date().toISOString().slice(0, 10)}`, pageWidth / 2, finalY + 25, { align: 'center' });
                doc.text('___________________________', pageWidth / 2, finalY + 35, { align: 'center' });
                doc.text('Registrar / Authorized Signatory', pageWidth / 2, finalY + 40, { align: 'center' });
                doc.text('Managed by Demo Pro Educational Center Management System', pageWidth / 2, finalY + 45, { align: 'center' });

                doc.save(`centers_${new Date().toISOString().slice(0,10)}.pdf`);
            }

            function printCenters() {
                const printWindow = window.open('', '_blank');
                const data = getTableData();
                const instituteName = 'Demo Pro';
                const instituteLogo = '<?php echo esc_js(plugin_dir_url(__FILE__) . '../logo instituto.jpg'); ?>';

                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Educational Centers List</title>
                        <style>
                            @media print {
                                body { font-family: Helvetica, sans-serif; margin: 10mm; width: 190mm; }
                                .page { border: 4px solid #4683b4; padding: 5mm; box-sizing: border-box; width: 100%; max-width: 190mm; }
                                .header { text-align: center; border-bottom: 2px solid #4683b4; margin-bottom: 10mm; }
                                .header img { width: 60px; height: 60px; margin-bottom: 5mm; }
                                .header h1 { font-size: 18pt; color: #4683b4; margin: 0; text-transform: uppercase; }
                                .header .subtitle { font-size: 12pt; color: #666; margin: 0; }
                                table { width: 100%; max-width: 100%; border-collapse: collapse; margin: 10mm 0; table-layout: fixed; }
                                th, td { border: 1px solid #e5e5e5; padding: 8px; text-align: center; word-wrap: break-word; font-size: 10pt; }
                                th { background: #4683b4; color: white; font-weight: bold; }
                                tr:nth-child(even) { background: #f9f9f9; }
                                .footer { text-align: center; font-size: 9pt; color: #666; margin-top: 10mm; }
                                @page { size: A4; margin: 10mm; }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="page">
                            <div class="header">
                                <img src="${instituteLogo}" alt="Logo">
                                <h1>${instituteName.toUpperCase()}</h1>
                                <p class="subtitle">Educational Centers List</p>
                            </div>
                            <p><strong>Date:</strong> ${new Date().toLocaleDateString()}</p>
                            <p><strong>Total Centers:</strong> ${data.length - 1}</p>
                            <table>
                                <thead><tr>${data[0].map(header => `<th>${header}</th>`).join('')}</tr></thead>
                                <tbody>${data.slice(1).map(row => `<tr>${row.map(cell => `<td>${cell}</td>`).join('')}</tr>`).join('')}</tbody>
                            </table>
                            <div class="footer">
                                <p>This is an Online Generated Centers List issued by ${instituteName}</p>
                                <p>Generated on ${new Date().toISOString().slice(0, 10)}</p>
                                <p>___________________________</p>
                                <p>Registrar / Authorized Signatory</p>
                                <p>Managed by Demo Pro Educational Center Management System</p>
                            </div>
                        </div>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
            }

            function copyToClipboard() {
                const data = getTableData();
                const text = data.map(row => row.join('\t')).join('\n');
                navigator.clipboard.writeText(text).then(() => alert('Centers copied to clipboard!'));
            }

            function setupExportButtons() {
                const tools = $('#export-tools');
                tools.html(`
                    <button class="export-btn export-csv" aria-label="Export to CSV"><i class="fas fa-file-csv"></i><span class="tooltip">Export to CSV</span></button>
                    <button class="export-btn export-pdf" aria-label="Export to PDF"><i class="fas fa-file-pdf"></i><span class="tooltip">Export to PDF</span></button>
                    <button class="export-btn export-excel" aria-label="Export to Excel"><i class="fas fa-file-excel"></i><span class="tooltip">Export to Excel</span></button>
                    <button class="export-btn export-copy" aria-label="Copy to Clipboard"><i class="fas fa-copy"></i><span class="tooltip">Copy to Clipboard</span></button>
                    <button class="export-btn export-print" aria-label="Print"><i class="fas fa-print"></i><span class="tooltip">Print</span></button>
                `);
                tools.find('.export-csv').on('click', exportToCSV);
                tools.find('.export-pdf').on('click', generatePDF);
                tools.find('.export-excel').on('click', exportToExcel);
                tools.find('.export-copy').on('click', copyToClipboard);
                tools.find('.export-print').on('click', printCenters);
            }

            function loadCenters(page, limit, query) {
                showLoader();
                setTimeout(() => {
                    const filtered = centersData.filter(c => !query || c.educational_center_name.toLowerCase().includes(query.toLowerCase()));
                    const total = filtered.length;
                    const start = (page - 1) * limit;
                    const end = start + limit;
                    const paginated = filtered.slice(start, end);
                    let html = '';
                    paginated.forEach(center => {
                        html += `
                            <tr data-center-id="${center.ID}">
                                <td>${center.educational_center_id}</td>
                                <td>${center.educational_center_name}</td>
                                <td>${center.admin_id}</td>
                                <td><img src="${center.institute_logo}" alt="Logo" class="edu-logo"></td>
                                <td>${center.location || 'N/A'}</td>
                                <td>${center.mobile_number}</td>
                                <td>${center.email_id}</td>
                                <td>
                                    <button class="edu-button edu-button-edit edit-center" data-center-id="${center.ID}">Edit</button>
                                    <button class="edu-button edu-button-delete delete-center" data-center-id="${center.ID}">Delete</button>
                                    <button class="edu-button edu-button-password change-password" data-center-id="${center.ID}" data-admin-id="${center.admin_id}">Change Password</button>
                                    <button class="edu-button edu-button-add-admin add-new-admin" data-center-id="${center.ID}">Add New Admin</button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#centers-table-body').html(html || '<tr><td colspan="8">No centers found.</td></tr>');
                    setupExportButtons();
                    const totalPages = Math.ceil(total / limit);
                    $('#page-info').text(`Page ${page} of ${totalPages}`);
                    $('#prev-page').prop('disabled', page === 1);
                    $('#next-page').prop('disabled', page === totalPages || total === 0);
                    hideLoader();
                }, 500);
            }

            // Load data based on action
            if ('<?php echo $action; ?>' === 'manage-centers') {
                loadCenters(currentPage, perPage, searchQuery);
            } else if ('<?php echo $action; ?>' === 'edit-center' || '<?php echo $action; ?>' === 'delete-center' || '<?php echo $action; ?>' === 'reset-password' || '<?php echo $action; ?>' === 'add-admin') {
                populateSelectOptions('#edit-center-select, #delete-center-select, #reset-center-select, #new-admin-center-select');
            }

            // Search
            $('#center-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadCenters(currentPage, perPage, searchQuery);
            });

            // Pagination
            $('#centers-per-page').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadCenters(currentPage, perPage, searchQuery);
            });

            $('#next-page').on('click', function() { currentPage++; loadCenters(currentPage, perPage, searchQuery); });
            $('#prev-page').on('click', function() { currentPage--; loadCenters(currentPage, perPage, searchQuery); });

            // Add Center (Sidebar)
            $('#save-center').on('click', function() {
                const name = $('#center-name').val();
                const location = $('#center-location').val();
                const mobile = $('#center-mobile').val();
                const email = $('#center-email').val();
                if (name && mobile && email) {
                    showLoader();
                    setTimeout(() => {
                        const newId = centersData.length + 1;
                        const newCenter = {
                            ID: newId,
                            educational_center_id: `EC00${newId}`,
                            educational_center_name: name,
                            admin_id: 'Unassigned',
                            institute_logo: 'https://via.placeholder.com/50',
                            location: location,
                            mobile_number: mobile,
                            email_id: email
                        };
                        centersData.push(newCenter);
                        $('#add-center-message').addClass('edu-success').text('Center added successfully!');
                        $('#center-edu-id').val(newCenter.educational_center_id);
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>';
                        }, 1000);
                        hideLoader();
                    }, 500);
                } else {
                    $('#add-center-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            // Add Center (Modal)
            $('#add-center-btn').on('click', function() { openModal('#add-center-modal'); });
            $('#save-modal-center').on('click', function() {
                const name = $('#modal-center-name').val();
                const location = $('#modal-center-location').val();
                const mobile = $('#modal-center-mobile').val();
                const email = $('#modal-center-email').val();
                if (name && mobile && email) {
                    showLoader();
                    setTimeout(() => {
                        const newId = centersData.length + 1;
                        const newCenter = {
                            ID: newId,
                            educational_center_id: `EC00${newId}`,
                            educational_center_name: name,
                            admin_id: 'Unassigned',
                            institute_logo: 'https://via.placeholder.com/50',
                            location: location,
                            mobile_number: mobile,
                            email_id: email
                        };
                        centersData.push(newCenter);
                        $('#add-center-modal-message').addClass('edu-success').text('Center added successfully!');
                        $('#modal-center-edu-id').val(newCenter.educational_center_id);
                        setTimeout(() => {
                            closeModal('#add-center-modal');
                            loadCenters(currentPage, perPage, searchQuery);
                        }, 1000);
                        hideLoader();
                    }, 500);
                } else {
                    $('#add-center-modal-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            // Edit Center (Sidebar)
            $('#edit-center-select').on('change', function() {
                const centerId = $(this).val();
                if (centerId) {
                    const center = centersData.find(c => c.ID == centerId);
                    $('#edit-center-id').val(center.ID);
                    $('#edit-center-name').val(center.educational_center_name);
                    $('#edit-center-admin-id').val(center.admin_id);
                    $('#edit-center-edu-id').val(center.educational_center_id);
                    $('#edit-center-location').val(center.location);
                    $('#edit-center-mobile').val(center.mobile_number);
                    $('#edit-center-email').val(center.email_id);
                    if (center.institute_logo) $('#edit-center-logo-preview').attr('src', center.institute_logo).show();
                    else $('#edit-center-logo-preview').hide();
                    $('#edit-center-form').show();
                } else {
                    $('#edit-center-form').hide();
                }
            });

            $('#update-center').on('click', function() {
                const id = $('#edit-center-id').val();
                const name = $('#edit-center-name').val();
                const location = $('#edit-center-location').val();
                const mobile = $('#edit-center-mobile').val();
                const email = $('#edit-center-email').val();
                if (name && mobile && email) {
                    showLoader();
                    setTimeout(() => {
                        const center = centersData.find(c => c.ID == id);
                        center.educational_center_name = name;
                        center.location = location;
                        center.mobile_number = mobile;
                        center.email_id = email;
                        $('#edit-center-message').addClass('edu-success').text('Center updated successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>';
                        }, 1000);
                        hideLoader();
                    }, 500);
                } else {
                    $('#edit-center-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            // Edit Center (Modal)
            $(document).on('click', '.edit-center', function() {
                const centerId = $(this).data('center-id');
                const center = centersData.find(c => c.ID == centerId);
                $('#edit-modal-center-id').val(center.ID);
                $('#edit-modal-center-name').val(center.educational_center_name);
                $('#edit-modal-center-admin-id').val(center.admin_id);
                $('#edit-modal-center-edu-id').val(center.educational_center_id);
                $('#edit-modal-center-location').val(center.location);
                $('#edit-modal-center-mobile').val(center.mobile_number);
                $('#edit-modal-center-email').val(center.email_id);
                if (center.institute_logo) $('#edit-modal-center-logo-preview').attr('src', center.institute_logo).show();
                else $('#edit-modal-center-logo-preview').hide();
                openModal('#edit-center-modal');
            });

            $('#update-modal-center').on('click', function() {
                const id = $('#edit-modal-center-id').val();
                const name = $('#edit-modal-center-name').val();
                const location = $('#edit-modal-center-location').val();
                const mobile = $('#edit-modal-center-mobile').val();
                const email = $('#edit-modal-center-email').val();
                if (name && mobile && email) {
                    showLoader();
                    setTimeout(() => {
                        const center = centersData.find(c => c.ID == id);
                        center.educational_center_name = name;
                        center.location = location;
                        center.mobile_number = mobile;
                        center.email_id = email;
                        $('#edit-center-modal-message').addClass('edu-success').text('Center updated successfully!');
                        setTimeout(() => {
                            closeModal('#edit-center-modal');
                            loadCenters(currentPage, perPage, searchQuery);
                        }, 1000);
                        hideLoader();
                    }, 500);
                } else {
                    $('#edit-center-modal-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            // Delete Center (Sidebar)
            $('#delete-center-btn').on('click', function() {
                const centerId = $('#delete-center-select').val();
                if (centerId && confirm('Are you sure you want to delete this center?')) {
                    showLoader();
                    setTimeout(() => {
                        centersData = centersData.filter(c => c.ID != centerId);
                        $('#delete-center-message').addClass('edu-success').text('Center deleted successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>';
                        }, 1000);
                        hideLoader();
                    }, 500);
                } else if (!centerId) {
                    $('#delete-center-message').addClass('edu-error').text('Please select a center.');
                }
            });

            // Delete Center (Table)
            $(document).on('click', '.delete-center', function() {
                if (!confirm('Are you sure you want to delete this center?')) return;
                const centerId = $(this).data('center-id');
                showLoader();
                setTimeout(() => {
                    centersData = centersData.filter(c => c.ID != centerId);
                    loadCenters(currentPage, perPage, searchQuery);
                    hideLoader();
                }, 500);
            });

            // Reset Password (Sidebar)
            $('#send-reset-link').on('click', function() {
                const centerId = $('#reset-center-select').val();
                if (centerId) {
                    showLoader();
                    setTimeout(() => {
                        $('#change-password-message').addClass('edu-success').text('Reset link sent successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>';
                        }, 1000);
                        hideLoader();
                    }, 500);
                } else {
                    $('#change-password-message').addClass('edu-error').text('Please select a center.');
                }
            });

            // Reset Password (Modal)
            $(document).on('click', '.change-password', function() {
                $('#change-modal-center-id').val($(this).data('center-id'));
                openModal('#change-password-modal');
            });

            $('#send-modal-reset-link').on('click', function() {
                showLoader();
                setTimeout(() => {
                    $('#change-password-modal-message').addClass('edu-success').text('Reset link sent successfully!');
                    setTimeout(() => closeModal('#change-password-modal'), 1000);
                    hideLoader();
                }, 500);
            });

            // Add New Admin (Sidebar)
            $('#new-admin-center-select').on('change', function() {
                const centerId = $(this).val();
                if (centerId) {
                    const center = centersData.find(c => c.ID == centerId);
                    $('#new-admin-center-id').val(centerId);
                    $('#new-admin-educational-center-id').val(center.educational_center_id);
                    $('#new-admin-id').val(`ADM00${centersData.length + 1}`);
                }
            });

            $('#save-new-admin').on('click', function() {
                const centerId = $('#new-admin-center-id').val();
                const adminId = $('#new-admin-id').val();
                const name = $('#new-admin-name').val();
                const email = $('#new-admin-email').val();
                const password = $('#new-admin-password').val();
                const mobile = $('#new-admin-mobile').val();
                if (centerId && name && email && password && mobile) {
                    showLoader();
                    setTimeout(() => {
                        const center = centersData.find(c => c.ID == centerId);
                        center.admin_id = adminId;
                        $('#add-new-admin-message').addClass('edu-success').text('New admin added successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])); ?>';
                        }, 1000);
                        hideLoader();
                    }, 500);
                } else {
                    $('#add-new-admin-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            // Add New Admin (Modal)
            $(document).on('click', '.add-new-admin', function() {
                const centerId = $(this).data('center-id');
                const center = centersData.find(c => c.ID == centerId);
                $('#new-admin-modal-center-id').val(centerId);
                $('#new-admin-modal-educational-center-id').val(center.educational_center_id);
                $('#new-admin-modal-id').val(`ADM00${centersData.length + 1}`);
                openModal('#add-new-admin-modal');
            });

            $('#save-new-admin-modal').on('click', function() {
                const centerId = $('#new-admin-modal-center-id').val();
                const adminId = $('#new-admin-modal-id').val();
                const name = $('#new-admin-modal-name').val();
                const email = $('#new-admin-modal-email').val();
                const password = $('#new-admin-modal-password').val();
                const mobile = $('#new-admin-modal-mobile').val();
                if (name && email && password && mobile) {
                    showLoader();
                    setTimeout(() => {
                        const center = centersData.find(c => c.ID == centerId);
                        center.admin_id = adminId;
                        $('#add-new-admin-modal-message').addClass('edu-success').text('New admin added successfully!');
                        setTimeout(() => {
                            closeModal('#add-new-admin-modal');
                            loadCenters(currentPage, perPage, searchQuery);
                        }, 1000);
                        hideLoader();
                    }, 500);
                } else {
                    $('#add-new-admin-modal-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $('.edu-modal-close').on('click', function() { closeModal(`#${$(this).data('modal')}`); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminOverview() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Welcome, Super Admin</h2>
        <div class="row">
            <div class="col-md-4"><div class="card"><h5>Centers</h5><p class="display-4">5</p></div></div>
            <div class="col-md-4"><div class="card"><h5>Users</h5><p class="display-4">200</p></div></div>
            <div class="col-md-4"><div class="card"><h5>Active Exams</h5><p class="display-4">8</p></div></div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminExams($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Exams</h2>
        <table class="table" id="superadmin-exams">
            <thead><tr><th>Name</th><th>Date</th><th>Center</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['exams'] as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['name']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'edit-exam'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'delete-exam'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddExam() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Exam</h2>
        <div class="card p-4 bg-light">
            <form action="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'exams'])); ?>" method="get">
                <input type="hidden" name="demo-role" value="superadmin">
                <input type="hidden" name="demo-section" value="exams">
                <div class="mb-3"><label for="exam-name">Exam Name</label><input type="text" id="exam-name" name="exam-name" required placeholder="e.g., Final Exam"></div>
                <div class="mb-3"><label for="exam-date">Date</label><input type="date" id="exam-date" name="exam-date" required></div>
                <div class="mb-3"><label for="exam-center">Center</label><input type="text" id="exam-center" name="exam-center" required placeholder="e.g., Center C"></div>
                <button type="submit" class="btn btn-success">Add Exam</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'exams'])); ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditExam($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Exam</h2>
        <div class="alert alert-info">Select an exam to edit from the Exams section.</div>
        <table class="table" id="superadmin-exams">
            <thead><tr><th>Name</th><th>Date</th><th>Center</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['exams'] as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['name']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'edit-exam'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'delete-exam'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteExam($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Exam</h2>
        <div class="alert alert-warning">Click "Delete" to remove an exam.</div>
        <table class="table" id="superadmin-exams">
            <thead><tr><th>Name</th><th>Date</th><th>Center</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data['exams'] as $exam): ?>
                    <tr>
                        <td><?php echo esc_html($exam['name']); ?></td>
                        <td><?php echo esc_html($exam['date']); ?></td>
                        <td><?php echo esc_html($exam['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'exams'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}


function demoRenderSuperadminSubscription($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Subscriptions</h2>
        <p>Subscription management placeholder.</p>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddSubscription() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Subscription</h2>
        <p>Add subscription form placeholder.</p>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditSubscription($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Subscription</h2>
        <p>Edit subscription form placeholder.</p>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteSubscription($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Subscription</h2>
        <p>Delete subscription confirmation placeholder.</p>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminPaymentMethods($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Payment Methods</h2>
        <p>Payment methods management placeholder.</p>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddPaymentMethods() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Payment Method</h2>
        <p>Add payment method form placeholder.</p>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditPaymentMethods($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Payment Method</h2>
        <p>Edit payment method form placeholder.</p>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeletePaymentMethods($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Payment Method</h2>
        <p>Delete payment method confirmation placeholder.</p>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherStudents($data = []) {
    ob_start();
    ?>
    <div class="edu-student-container" style="margin-top: 80px;">
        <h2 class="edu-student-title">Manage Students</h2>
        <div class="edu-student-actions">
            <input type="text" id="student-search" class="edu-search-input" placeholder="Search Students..." style="margin-right: 20px; padding: 8px; width: 300px;">
            <button class="edu-button edu-button-primary" id="add-student-btn">Add Student</button>
            <button class="edu-button edu-button-secondary" id="export-students">Export CSV</button>
            <input type="file" id="import-students" accept=".csv" style="display: none;">
            <button class="edu-button edu-button-secondary" id="import-students-btn">Import CSV</button>
        </div>
        <div class="edu-pagination" style="margin: 20px 0;">
            <label for="students-per-page">Show:</label>
            <select id="students-per-page" class="edu-select" style="margin-right: 20px;">
                <option value="10">10</option>
                <option value="20">20</option>
                <option value="50">50</option>
            </select>
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info" style="margin: 0 10px;"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="student-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Section</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="student-table-body">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>

        <!-- Add Student Modal -->
        <div id="add-student-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="add-student-close">×</span>
                <h2>Add Student</h2>
                <form id="add-student-form" class="edu-form">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="add-student-id" name="student_id" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="add-student-name" name="student_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-class">Class</label>
                        <input type="text" class="edu-form-input" id="add-class" name="class" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-section">Section</label>
                        <input type="text" class="edu-form-input" id="add-section" name="section" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="add-email">Email</label>
                        <input type="email" class="edu-form-input" id="add-email" name="email" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="save-student">Add Student</button>
                </form>
                <div class="edu-form-message" id="add-student-message"></div>
            </div>
        </div>

        <!-- Edit Student Modal -->
        <div id="edit-student-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="edit-student-close">×</span>
                <h2>Edit Student</h2>
                <form id="edit-student-form" class="edu-form">
                    <input type="hidden" id="edit-student-index">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="edit-student-id" name="student_id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="edit-student-name" name="student_name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-class">Class</label>
                        <input type="text" class="edu-form-input" id="edit-class" name="class" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-section">Section</label>
                        <input type="text" class="edu-form-input" id="edit-section" name="section" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-email">Email</label>
                        <input type="email" class="edu-form-input" id="edit-email" name="email" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-student">Update Student</button>
                </form>
                <div class="edu-form-message" id="edit-student-message"></div>
            </div>
        </div>

        <!-- Delete Student Modal -->
        <div id="delete-student-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" id="delete-student-close">×</span>
                <h2>Delete Student</h2>
                <p>Are you sure you want to delete <span id="delete-student-name"></span>?</p>
                <input type="hidden" id="delete-student-index">
                <button type="button" class="edu-button edu-button-delete" id="confirm-delete-student">Delete</button>
                <button type="button" class="edu-button edu-button-secondary" id="cancel-delete-student">Cancel</button>
                <div class="edu-form-message" id="delete-student-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let studentData = <?php echo json_encode($data['students'] ?? []); ?>;

            function loadStudents(page, limit, query) {
                const filtered = studentData.filter(s => 
                    !query || s.student_name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach((student, index) => {
                    html += `
                        <tr data-student-index="${start + index}">
                            <td>${student.student_id}</td>
                            <td>${student.student_name}</td>
                            <td>${student.class}</td>
                            <td>${student.section}</td>
                            <td>${student.email}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-student" data-student-index="${start + index}">Edit</button>
                                <button class="edu-button edu-button-delete delete-student" data-student-index="${start + index}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#student-table-body').html(html || '<tr><td colspan="6">No students found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadStudents(currentPage, perPage, searchQuery);

            $('#student-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadStudents(currentPage, perPage, searchQuery);
            });

            $('#students-per-page').on('change', function() {
                perPage = parseInt($(this).val());
                currentPage = 1;
                loadStudents(currentPage, perPage, searchQuery);
            });

            $('#next-page').on('click', function() { currentPage++; loadStudents(currentPage, perPage, searchQuery); });
            $('#prev-page').on('click', function() { currentPage--; loadStudents(currentPage, perPage, searchQuery); });

            $('#add-student-btn').on('click', function() { $('#add-student-modal').show(); });
            $('#add-student-close').on('click', function() { $('#add-student-modal').hide(); });
            $('#save-student').on('click', function() {
                const student = {
                    student_id: $('#add-student-id').val(),
                    student_name: $('#add-student-name').val(),
                    class: $('#add-class').val(),
                    section: $('#add-section').val(),
                    email: $('#add-email').val()
                };
                if (student.student_id && student.student_name && student.class && student.section && student.email) {
                    studentData.push(student);
                    $('#add-student-message').addClass('edu-success').text('Student added successfully!');
                    setTimeout(() => {
                        $('#add-student-modal').hide();
                        $('#add-student-message').removeClass('edu-success').text('');
                        $('#add-student-form')[0].reset();
                        loadStudents(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#add-student-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.edit-student', function() {
                const studentIndex = $(this).data('student-index');
                const student = studentData[studentIndex];
                $('#edit-student-index').val(studentIndex);
                $('#edit-student-id').val(student.student_id);
                $('#edit-student-name').val(student.student_name);
                $('#edit-class').val(student.class);
                $('#edit-section').val(student.section);
                $('#edit-email').val(student.email);
                $('#edit-student-modal').show();
            });
            $('#edit-student-close').on('click', function() { $('#edit-student-modal').hide(); });
            $('#update-student').on('click', function() {
                const studentIndex = $('#edit-student-index').val();
                const student = studentData[studentIndex];
                student.student_name = $('#edit-student-name').val();
                student.class = $('#edit-class').val();
                student.section = $('#edit-section').val();
                student.email = $('#edit-email').val();
                if (student.student_name && student.class && student.section && student.email) {
                    $('#edit-student-message').addClass('edu-success').text('Student updated successfully!');
                    setTimeout(() => {
                        $('#edit-student-modal').hide();
                        $('#edit-student-message').removeClass('edu-success').text('');
                        loadStudents(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#edit-student-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-student', function() {
                const studentIndex = $(this).data('student-index');
                const student = studentData[studentIndex];
                $('#delete-student-index').val(studentIndex);
                $('#delete-student-name').text(student.student_name);
                $('#delete-student-modal').show();
            });
            $('#delete-student-close, #cancel-delete-student').on('click', function() { $('#delete-student-modal').hide(); });
            $('#confirm-delete-student').on('click', function() {
                const studentIndex = $('#delete-student-index').val();
                studentData.splice(studentIndex, 1);
                $('#delete-student-message').addClass('edu-success').text('Student deleted successfully!');
                setTimeout(() => {
                    $('#delete-student-modal').hide();
                    $('#delete-student-message').removeClass('edu-success').text('');
                    loadStudents(currentPage, perPage, searchQuery);
                }, 1000);
            });

            $('#export-students').on('click', function() {
                const csv = studentData.map(row => `${row.student_id},${row.student_name},${row.class},${row.section},${row.email}`).join('\n');
                const headers = 'Student ID,Student Name,Class,Section,Email\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'students.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('#import-students-btn').on('click', function() {
                $('#import-students').click();
            });

            $('#import-students').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [student_id, student_name, class_name, section, email] = row.split(',');
                            return { student_id, student_name, class: class_name, section, email };
                        });
                        studentData.push(...newRecords);
                        $('#add-student-message').addClass('edu-success').text('Students imported successfully!');
                        setTimeout(() => {
                            $('#add-student-message').removeClass('edu-success').text('');
                            loadStudents(currentPage, perPage, searchQuery);
                        }, 1000);
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Data Functions
 */
function demoGetTeacherData() {
    return [
        'role' => 'teacher',
        'exams' => [
            ['name' => 'Math Exam', 'date' => '2025-04-15', 'class' => 'Class 1A'],
            ['name' => 'Science Exam', 'date' => '2025-04-20', 'class' => 'Class 1A'],
            ['name' => 'History Quiz', 'date' => '2025-04-25', 'class' => 'Class 2B'],
            ['name' => 'English Test', 'date' => '2025-05-01', 'class' => 'Class 3C']
        ],
        'attendance' => [
            ['date' => '2025-04-01', 'student' => 'Student 1', 'status' => 'Present'],
            ['date' => '2025-04-02', 'student' => 'Student 2', 'status' => 'Absent'],
            ['date' => '2025-04-03', 'student' => 'Student 3', 'status' => 'Present'],
            ['date' => '2025-04-04', 'student' => 'Student 4', 'status' => 'Late']
        ],
        'students' => [
            ['id' => 'ST1001', 'name' => 'John Doe', 'email' => 'john@demo-pro.edu', 'class' => 'Class 1A', 'center' => 'Center A'],
            ['id' => 'ST1002', 'name' => 'Jane Smith', 'email' => 'jane@demo-pro.edu', 'class' => 'Class 1A', 'center' => 'Center A']
        ],
        'student_attendance' => [
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-10', 'status' => 'Present'],
            ['student_id' => 'S002', 'student_name' => 'Jane Smith', 'class' => '10', 'section' => 'B', 'date' => '2025-04-10', 'status' => 'Absent'],
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-11', 'status' => 'Late'],
        ],
        'profile' => [
            'teacher_id' => 'TR1001',
            'name' => 'Alice Brown',
            'email' => 'alice@demo-pro.edu',
            'subject' => 'Math',
            'center' => 'Center A'
        ]
    ];
}

function demoGetSuperadminData() {
    return [
        'centers' => [
            ['id' => 1, 'name' => 'Main Campus'],
            ['id' => 2, 'name' => 'West Campus'],
        ],
        'students' => [
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'email' => 'john.doe@example.com', 'center' => 'Main Campus'],
            ['student_id' => 'S002', 'student_name' => 'Jane Smith', 'class' => '10', 'section' => 'B', 'email' => 'jane.smith@example.com', 'center' => 'West Campus'],
        ],
        'teachers' => [
            ['teacher_id' => 'T001', 'teacher_name' => 'Alice Johnson', 'department' => 'Math', 'email' => 'alice.j@example.com', 'center' => 'Main Campus'],
            ['teacher_id' => 'T002', 'teacher_name' => 'Bob Wilson', 'department' => 'Science', 'email' => 'bob.w@example.com', 'center' => 'West Campus'],
        ],
        'student_attendance' => [
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'center' => 'Main Campus', 'date' => '2025-04-10', 'status' => 'Present'],
            ['student_id' => 'S002', 'student_name' => 'Jane Smith', 'class' => '10', 'section' => 'B', 'center' => 'West Campus', 'date' => '2025-04-10', 'status' => 'Absent'],
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'center' => 'Main Campus', 'date' => '2025-04-11', 'status' => 'Late'],
        ],
        'teacher_attendance' => [
            ['teacher_id' => 'T001', 'teacher_name' => 'Alice Johnson', 'department' => 'Math', 'center' => 'Main Campus', 'date' => '2025-04-10', 'status' => 'Present'],
            ['teacher_id' => 'T002', 'teacher_name' => 'Bob Wilson', 'department' => 'Science', 'center' => 'West Campus', 'date' => '2025-04-10', 'status' => 'Present'],
            ['teacher_id' => 'T001', 'teacher_name' => 'Alice Johnson', 'department' => 'Math', 'center' => 'Main Campus', 'date' => '2025-04-11', 'status' => 'Absent'],
        ],
    ];
}
function demoGetStudentData() {
    return [
        'role' => 'student',
        'exams' => [
            ['name' => 'Math Exam', 'date' => '2025-04-15', 'class' => 'Class 1A'],
            ['name' => 'Science Exam', 'date' => '2025-04-20', 'class' => 'Class 1A'],
            ['name' => 'History Quiz', 'date' => '2025-04-25', 'class' => 'Class 1A']
        ],
        'attendance' => [
            ['date' => '2025-04-01', 'status' => 'Present'],
            ['date' => '2025-04-02', 'status' => 'Absent'],
            ['date' => '2025-04-03', 'status' => 'Present'],
            ['date' => '2025-04-04', 'status' => 'Late']
        ]
    ];
}

function demoGetParentData() {
    return [
        'role' => 'parent',
        'exams' => [
            ['name' => 'Math Exam', 'date' => '2025-04-15', 'class' => 'Class 1A'],
            ['name' => 'Science Exam', 'date' => '2025-04-20', 'class' => 'Class 1A'],
            ['name' => 'History Quiz', 'date' => '2025-04-25', 'class' => 'Class 1A']
        ],
        'attendance' => [
            ['date' => '2025-04-01', 'status' => 'Present'],
            ['date' => '2025-04-02', 'status' => 'Absent'],
            ['date' => '2025-04-03', 'status' => 'Present'],
            ['date' => '2025-04-04', 'status' => 'Late']
        ]
    ];
}

