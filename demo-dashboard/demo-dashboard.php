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
                        <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'superadmin')); ?>" class="btn btn-warning w-100">Super Admin</a></div>
                        <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'institute-admin')); ?>" class="btn btn-warning w-100">Institute Admin</a></div>
                            <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'teacher')); ?>" class="btn btn-primary w-100">Teacher</a></div>
                            <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'student')); ?>" class="btn btn-success w-100">Student</a></div>
                            <div class="col-md-3"><a href="<?php echo esc_url(add_query_arg('demo-role', 'parent')); ?>" class="btn btn-info w-100">Parent</a></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <div class="col-md-9 p-4" id="main-content-inner">
                            <?php
                            switch ($role) {case 'superadmin':
                                    $data = demoGetSuperadminData();
                                    echo demoRenderSuperadminContent($section, $action, $data);
                                    break;
                                    case 'institute-admin':
                                        $data = demoGetInstituteAdminData();
                                        echo demoRenderInstituteAdminContent($section, $action, $data);
                                        break;
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
            ['section' => 'overview', 'label' => 'Overview', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'overview'])), 'icon' => 'tachometer-alt'],
            ['section' => 'exams', 'label' => 'Exams', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams'])), 'icon' => 'list'],
            ['section' => 'attendance-reports', 'label' => 'Attendance Reports', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance-reports'])), 'icon' => 'calendar'],
            ['section' => 'students', 'label' => 'Students', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students'])), 'icon' => 'users', 'submenu' => [
                ['action' => 'manage-students', 'label' => 'Manage Students', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'manage-students']))],
                ['action' => 'add-student', 'label' => 'Add Student', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'add-student']))],
                ['action' => 'edit-student', 'label' => 'Edit Student', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'edit-student']))],
                ['action' => 'delete-student', 'label' => 'Delete Student', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'delete-student']))],
            ]],
            ['section' => 'profile', 'label' => 'View Profile', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'profile'])), 'icon' => 'user'],
            ['section' => 'exam-management', 'label' => 'Exam Management', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management'])), 'icon' => 'book', 'submenu' => [
                ['action' => 'view-exams', 'label' => 'View Exams', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'view-exams']))],
                ['action' => 'add-exam', 'label' => 'Add Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'add-exam']))],
                ['action' => 'edit-exam', 'label' => 'Edit Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'edit-exam']))],
                ['action' => 'delete-exam', 'label' => 'Delete Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'delete-exam']))],
            ]],
            ['section' => 'attendance', 'label' => 'Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance'])), 'icon' => 'calendar-check', 'submenu' => [
                ['action' => 'student-attendance', 'label' => 'Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'student-attendance'])), 'submenu' => [
                    ['action' => 'manage-student-attendance', 'label' => 'Manage Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance']))],
                    ['action' => 'add-student-attendance', 'label' => 'Add Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'add-student-attendance']))],
                    ['action' => 'edit-student-attendance', 'label' => 'Edit Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'edit-student-attendance']))],
                    ['action' => 'delete-student-attendance', 'label' => 'Delete Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'delete-student-attendance']))],
                    ['action' => 'import-student-attendance', 'label' => 'Import Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'import-student-attendance']))],
                    ['action' => 'export-student-attendance', 'label' => 'Export Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'export-student-attendance']))],
                ]],
            ]],
        ],
        'student' => [
            ['section' => 'overview', 'label' => 'Overview', 'url' => esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'overview'])), 'icon' => 'tachometer-alt'],
            ['section' => 'exams', 'label' => 'Exams', 'url' => esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'exams'])), 'icon' => 'list'],
            ['section' => 'attendance', 'label' => 'Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'attendance'])), 'icon' => 'calendar'],
            ['section' => 'profile', 'label' => 'Profile', 'url' => esc_url(add_query_arg(['demo-role' => 'student', 'demo-section' => 'profile'])), 'icon' => 'user'],
        ],
        'parent' => [
            ['section' => 'overview', 'label' => 'Overview', 'url' => esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'overview'])), 'icon' => 'tachometer-alt'],
            ['section' => 'exams', 'label' => 'Child Exams', 'url' => esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'exams'])), 'icon' => 'list'],
            ['section' => 'attendance', 'label' => 'Child Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'parent', 'demo-section' => 'attendance'])), 'icon' => 'calendar'],
        ],
        'superadmin' => [
            ['section' => 'overview', 'label' => 'Overview', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'overview'])), 'icon' => 'tachometer-alt'],
            ['section' => 'centers', 'label' => 'Centers', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers'])), 'icon' => 'school'],
            ['section' => 'students', 'label' => 'Students Management', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students'])), 'icon' => 'users', 'submenu' => [
                ['action' => 'manage-students', 'label' => 'Manage Students', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'manage-students']))],
                ['action' => 'add-student', 'label' => 'Add Student', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'add-student']))],
                ['action' => 'edit-student', 'label' => 'Edit Student', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'edit-student']))],
                ['action' => 'delete-student', 'label' => 'Delete Student', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'delete-student']))],
            ]],
            ['section' => 'teachers', 'label' => 'Teachers Management', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers'])), 'icon' => 'chalkboard-teacher', 'submenu' => [
                ['action' => 'manage-teachers', 'label' => 'Manage Teachers', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'manage-teachers']))],
                ['action' => 'add-teacher', 'label' => 'Add Teacher', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'add-teacher']))],
                ['action' => 'edit-teacher', 'label' => 'Edit Teacher', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'edit-teacher']))],
                ['action' => 'delete-teacher', 'label' => 'Delete Teacher', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'teachers', 'demo-action' => 'delete-teacher']))],
            ]],
            ['section' => 'staff', 'label' => 'Staff Management', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff'])), 'icon' => 'user-tie', 'submenu' => [
                ['action' => 'manage-staff', 'label' => 'Manage Staff', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'manage-staff']))],
                ['action' => 'add-staff', 'label' => 'Add Staff', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'add-staff']))],
                ['action' => 'edit-staff', 'label' => 'Edit Staff', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'edit-staff']))],
                ['action' => 'delete-staff', 'label' => 'Delete Staff', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'staff', 'demo-action' => 'delete-staff']))],
            ]],
            ['section' => 'attendance', 'label' => 'Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance'])), 'icon' => 'calendar-check', 'submenu' => [
                ['action' => 'student-attendance', 'label' => 'Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'student-attendance'])), 'submenu' => [
                    ['action' => 'manage-student-attendance', 'label' => 'Manage Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance']))],
                    ['action' => 'add-student-attendance', 'label' => 'Add Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'add-student-attendance']))],
                    ['action' => 'edit-student-attendance', 'label' => 'Edit Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'edit-student-attendance']))],
                    ['action' => 'delete-student-attendance', 'label' => 'Delete Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'delete-student-attendance']))],
                    ['action' => 'import-student-attendance', 'label' => 'Import Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'import-student-attendance']))],
                    ['action' => 'export-student-attendance', 'label' => 'Export Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'export-student-attendance']))],
                ]],
                ['action' => 'teacher-attendance', 'label' => 'Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'teacher-attendance'])), 'submenu' => [
                    ['action' => 'manage-teacher-attendance', 'label' => 'Manage Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance']))],
                    ['action' => 'add-teacher-attendance', 'label' => 'Add Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'add-teacher-attendance']))],
                    ['action' => 'edit-teacher-attendance', 'label' => 'Edit Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'edit-teacher-attendance']))],
                    ['action' => 'delete-teacher-attendance', 'label' => 'Delete Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'delete-teacher-attendance']))],
                    ['action' => 'import-teacher-attendance', 'label' => 'Import Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'import-teacher-attendance']))],
                    ['action' => 'export-teacher-attendance', 'label' => 'Export Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'export-teacher-attendance']))],
                ]],
                ['action' => 'staff-attendance', 'label' => 'Staff Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'staff-attendance'])), 'submenu' => [
                    ['action' => 'manage-staff-attendance', 'label' => 'Manage Staff Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-staff-attendance']))],
                    ['action' => 'add-staff-attendance', 'label' => 'Add Staff Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'add-staff-attendance']))],
                    ['action' => 'edit-staff-attendance', 'label' => 'Edit Staff Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'edit-staff-attendance']))],
                    ['action' => 'delete-staff-attendance', 'label' => 'Delete Staff Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'delete-staff-attendance']))],
                    ['action' => 'import-staff-attendance', 'label' => 'Import Staff Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'import-staff-attendance']))],
                    ['action' => 'export-staff-attendance', 'label' => 'Export Staff Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'export-staff-attendance']))],
                ]],
            ]],
            ['section' => 'classes', 'label' => 'Classes and Sections', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes'])), 'icon' => 'school', 'submenu' => [
                ['action' => 'manage-classes', 'label' => 'Manage Classes', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'manage-classes']))],
                ['action' => 'add-class', 'label' => 'Add Class', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'add-class']))],
                ['action' => 'edit-class', 'label' => 'Edit Class', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'edit-class']))],
                ['action' => 'delete-class', 'label' => 'Delete Class', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'delete-class']))],
            ]],
            ['section' => 'subjects', 'label' => 'Subjects Management', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects'])), 'icon' => 'book', 'submenu' => [
                ['action' => 'manage-subjects', 'label' => 'Manage Subjects', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'manage-subjects']))],
                ['action' => 'add-subject', 'label' => 'Add Subject', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'add-subject']))],
                ['action' => 'edit-subject', 'label' => 'Edit Subject', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'edit-subject']))],
                ['action' => 'delete-subject', 'label' => 'Delete Subject', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'delete-subject']))],
            ]],
            ['section' => 'homeworks', 'label' => 'Homeworks Management', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks'])), 'icon' => 'tasks', 'submenu' => [
                ['action' => 'manage-homeworks', 'label' => 'Manage Homeworks', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'manage-homeworks']))],
                ['action' => 'add-homework', 'label' => 'Add Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'add-homework']))],
                ['action' => 'edit-homework', 'label' => 'Edit Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'edit-homework']))],
                ['action' => 'delete-homework', 'label' => 'Delete Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'delete-homework']))],
            ]],
            ['section' => 'subscription', 'label' => 'Subscriptions', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription'])), 'icon' => 'money-check-alt', 'submenu' => [
                ['action' => 'manage-subscription', 'label' => 'Manage Subscriptions', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'manage-subscription']))],
                ['action' => 'add-subscription', 'label' => 'Add Subscription', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'add-subscription']))],
                ['action' => 'edit-subscription', 'label' => 'Edit Subscription', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'edit-subscription']))],
                ['action' => 'delete-subscription', 'label' => 'Delete Subscription', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'delete-subscription']))],
            ]],
            ['section' => 'payment_methods', 'label' => 'Payment Methods', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods'])), 'icon' => 'credit-card', 'submenu' => [
                ['action' => 'manage-payment-methods', 'label' => 'Manage Payment Methods', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'manage-payment-methods']))],
                ['action' => 'add-payment-method', 'label' => 'Add Payment Method', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'add-payment-method']))],
                ['action' => 'edit-payment-method', 'label' => 'Edit Payment Method', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'edit-payment-method']))],
                ['action' => 'delete-payment-method', 'label' => 'Delete Payment Method', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'delete-payment-method']))],
            ]],
            ['section' => 'timetable', 'label' => 'Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable'])), 'icon' => 'calendar-alt', 'submenu' => [
                ['action' => 'manage-timetable', 'label' => 'Manage Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'manage-timetable']))],
                ['action' => 'add-timetable', 'label' => 'Add Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'add-timetable']))],
                ['action' => 'edit-timetable', 'label' => 'Edit Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'edit-timetable']))],
                ['action' => 'delete-timetable', 'label' => 'Delete Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'delete-timetable']))],
            ]],
            ['section' => 'departments', 'label' => 'Departments', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments'])), 'icon' => 'building', 'submenu' => [
                ['action' => 'manage-departments', 'label' => 'Manage Departments', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'manage-departments']))],
                ['action' => 'add-department', 'label' => 'Add Department', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'add-department']))],
                ['action' => 'edit-department', 'label' => 'Edit Department', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'edit-department']))],
                ['action' => 'delete-department', 'label' => 'Delete Department', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'delete-department']))],
            ]],
            ['section' => 'library', 'label' => 'Library', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library'])), 'icon' => 'book', 'submenu' => [
                ['action' => 'manage-library', 'label' => 'Manage Library', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'manage-library']))],
                ['action' => 'add-book', 'label' => 'Add Book', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'add-book']))],
                ['action' => 'edit-book', 'label' => 'Edit Book', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'edit-book']))],
                ['action' => 'delete-book', 'label' => 'Delete Book', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'delete-book']))],
            ]],
            ['section' => 'inventory', 'label' => 'Inventory', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory'])), 'icon' => 'boxes', 'submenu' => [
                ['action' => 'manage-inventory', 'label' => 'Manage Inventory', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'manage-inventory']))],
                ['action' => 'add-item', 'label' => 'Add Item', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'add-item']))],
                ['action' => 'edit-item', 'label' => 'Edit Item', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'edit-item']))],
                ['action' => 'delete-item', 'label' => 'Delete Item', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'delete-item']))],
            ]],
            ['section' => 'fees', 'label' => 'Fees Management', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees'])), 'icon' => 'money-bill', 'submenu' => [
                ['action' => 'fees', 'label' => 'Fees', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fees'])), 'submenu' => [
                    ['action' => 'manage-fees', 'label' => 'Manage Fees', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'manage-fees']))],
                    ['action' => 'add-fee', 'label' => 'Add Fee', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'add-fee']))],
                    ['action' => 'edit-fee', 'label' => 'Edit Fee', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'edit-fee']))],
                    ['action' => 'delete-fee', 'label' => 'Delete Fee', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'delete-fee']))],
                    ['action' => 'import-fees', 'label' => 'Import Fees', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'import-fees']))],
                    ['action' => 'export-fees', 'label' => 'Export Fees', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'export-fees']))],
                ]],
                ['action' => 'fee-templates', 'label' => 'Fee Templates', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])), 'submenu' => [
                    ['action' => 'manage-fee-templates', 'label' => 'Manage Fee Templates', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'manage-fee-templates']))],
                    ['action' => 'add-fee-template', 'label' => 'Add Fee Template', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'add-fee-template']))],
                    ['action' => 'edit-fee-template', 'label' => 'Edit Fee Template', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'edit-fee-template']))],
                    ['action' => 'delete-fee-template', 'label' => 'Delete Fee Template', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'delete-fee-template']))],
                    ['action' => 'import-fee-templates', 'label' => 'Import Fee Templates', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'import-fee-templates']))],
                    ['action' => 'export-fee-templates', 'label' => 'Export Fee Templates', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'export-fee-templates']))],
                ]],
            ]],
            ['section' => 'library-transactions', 'label' => 'Library Transactions', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions'])), 'icon' => 'book-reader', 'submenu' => [
                ['action' => 'manage-library-transactions', 'label' => 'Manage Library Transactions', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'manage-library-transactions']))],
                ['action' => 'add-transaction', 'label' => 'Add Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'add-transaction']))],
                ['action' => 'edit-transaction', 'label' => 'Edit Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'edit-transaction']))],
                ['action' => 'delete-transaction', 'label' => 'Delete Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'delete-transaction']))],
            ]],
            ['section' => 'inventory-transactions', 'label' => 'Inventory Transactions', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions'])), 'icon' => 'cogs', 'submenu' => [
                ['action' => 'manage-inventory-transactions', 'label' => 'Manage Inventory Transactions', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'manage-inventory-transactions']))],
                ['action' => 'add-transaction', 'label' => 'Add Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'add-transaction']))],
                ['action' => 'edit-transaction', 'label' => 'Edit Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'edit-transaction']))],
                ['action' => 'delete-transaction', 'label' => 'Delete Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'delete-transaction']))],
            ]],
            ['section' => 'noticeboard', 'label' => 'Noticeboard', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard'])), 'icon' => 'bullhorn', 'submenu' => [
                ['action' => 'manage-noticeboard', 'label' => 'Manage Noticeboard', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'manage-noticeboard']))],
                ['action' => 'add-notice', 'label' => 'Add Notice', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'add-notice']))],
                ['action' => 'edit-notice', 'label' => 'Edit Notice', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'edit-notice']))],
                ['action' => 'delete-notice', 'label' => 'Delete Notice', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'delete-notice']))],
            ]],
            ['section' => 'announcements', 'label' => 'Announcements', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements'])), 'icon' => 'megaphone', 'submenu' => [
                ['action' => 'manage-announcements', 'label' => 'Manage Announcements', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'manage-announcements']))],
                ['action' => 'add-announcement', 'label' => 'Add Announcement', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'add-announcement']))],
                ['action' => 'edit-announcement', 'label' => 'Edit Announcement', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'edit-announcement']))],
                ['action' => 'delete-announcement', 'label' => 'Delete Announcement', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'delete-announcement']))],
            ]],
            ['section' => 'chats', 'label' => 'Chats', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'chats'])), 'icon' => 'envelope', 'submenu' => [
                ['action' => '', 'label' => 'Inbox', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'chats', 'demo-action' => 'inbox']))],
                ['action' => 'new-conversation', 'label' => 'New Conversation', 'url' => esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'chats', 'demo-action' => 'new-conversation']))],
            ]],
        ],
       'institute-admin' => [
             ['section' => 'overview', 'label' => 'Overview', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'overview'])), 'icon' => 'tachometer-alt'],
        ['section' => 'students', 'label' => 'Students Management', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students'])), 'icon' => 'users', 'submenu' => [
        ['action' => 'manage-students', 'label' => 'Manage Students', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students', 'demo-action' => 'manage-students']))],
        ['action' => 'add-student', 'label' => 'Add Student', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students', 'demo-action' => 'add-student']))],
        ['action' => 'edit-student', 'label' => 'Edit Student', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students', 'demo-action' => 'edit-student']))],
        ['action' => 'delete-student', 'label' => 'Delete Student', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students', 'demo-action' => 'delete-student']))],
    ]],
    ['section' => 'teachers', 'label' => 'Teachers Management', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers'])), 'icon' => 'chalkboard-teacher', 'submenu' => [
        ['action' => 'manage-teachers', 'label' => 'Manage Teachers', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers', 'demo-action' => 'manage-teachers']))],
        ['action' => 'add-teacher', 'label' => 'Add Teacher', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers', 'demo-action' => 'add-teacher']))],
        ['action' => 'edit-teacher', 'label' => 'Edit Teacher', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers', 'demo-action' => 'edit-teacher']))],
        ['action' => 'delete-teacher', 'label' => 'Delete Teacher', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers', 'demo-action' => 'delete-teacher']))],
    ]],
    ['section' => 'exams', 'label' => 'Exams', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams'])), 'icon' => 'book', 'submenu' => [
        ['action' => 'manage-exams', 'label' => 'Manage Exams', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams', 'demo-action' => 'manage-exams']))],
        ['action' => 'add-exam', 'label' => 'Add Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams', 'demo-action' => 'add-exam']))],
        ['action' => 'edit-exam', 'label' => 'Edit Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams', 'demo-action' => 'edit-exam']))],
        ['action' => 'delete-exam', 'label' => 'Delete Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams', 'demo-action' => 'delete-exam']))],
    ]],
    ['section' => 'attendance', 'label' => 'Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance'])), 'icon' => 'calendar-check', 'submenu' => [
        ['action' => 'student-attendance', 'label' => 'Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'student-attendance'])), 'submenu' => [
            ['action' => 'manage-student-attendance', 'label' => 'Manage Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance']))],
            ['action' => 'add-student-attendance', 'label' => 'Add Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'add-student-attendance']))],
            ['action' => 'edit-student-attendance', 'label' => 'Edit Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'edit-student-attendance']))],
            ['action' => 'delete-student-attendance', 'label' => 'Delete Student Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'delete-student-attendance']))],
        ]],
        ['action' => 'teacher-attendance', 'label' => 'Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'teacher-attendance'])), 'submenu' => [
            ['action' => 'manage-teacher-attendance', 'label' => 'Manage Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance']))],
            ['action' => 'add-teacher-attendance', 'label' => 'Add Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'add-teacher-attendance']))],
            ['action' => 'edit-teacher-attendance', 'label' => 'Edit Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'edit-teacher-attendance']))],
            ['action' => 'delete-teacher-attendance', 'label' => 'Delete Teacher Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'delete-teacher-attendance']))],
        ]],
    ]],
    ['section' => 'fees', 'label' => 'Fees', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees'])), 'icon' => 'money-bill', 'submenu' => [
        ['action' => 'manage-fees', 'label' => 'Manage Fees', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees', 'demo-action' => 'manage-fees']))],
        ['action' => 'add-fee', 'label' => 'Add Fee', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees', 'demo-action' => 'add-fee']))],
        ['action' => 'edit-fee', 'label' => 'Edit Fee', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees', 'demo-action' => 'edit-fee']))],
        ['action' => 'delete-fee', 'label' => 'Delete Fee', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees', 'demo-action' => 'delete-fee']))],
    ]],
    ['section' => 'notices', 'label' => 'Notices', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices'])), 'icon' => 'bullhorn', 'submenu' => [
        ['action' => 'manage-notices', 'label' => 'Manage Notices', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices', 'demo-action' => 'manage-notices']))],
        ['action' => 'add-notice', 'label' => 'Add Notice', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices', 'demo-action' => 'add-notice']))],
        ['action' => 'edit-notice', 'label' => 'Edit Notice', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices', 'demo-action' => 'edit-notice']))],
        ['action' => 'delete-notice', 'label' => 'Delete Notice', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices', 'demo-action' => 'delete-notice']))],
    ]],
    ['section' => 'announcements', 'label' => 'Announcements', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements'])), 'icon' => 'megaphone', 'submenu' => [
        ['action' => 'manage-announcements', 'label' => 'Manage Announcements', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements', 'demo-action' => 'manage-announcements']))],
        ['action' => 'add-announcement', 'label' => 'Add Announcement', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements', 'demo-action' => 'add-announcement']))],
        ['action' => 'edit-announcement', 'label' => 'Edit Announcement', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements', 'demo-action' => 'edit-announcement']))],
        ['action' => 'delete-announcement', 'label' => 'Delete Announcement', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements', 'demo-action' => 'delete-announcement']))],
    ]],
    ['section' => 'library', 'label' => 'Library', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library'])), 'icon' => 'book', 'submenu' => [
        ['action' => 'manage-books', 'label' => 'Manage Books', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library', 'demo-action' => 'manage-books']))],
        ['action' => 'add-book', 'label' => 'Add Book', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library', 'demo-action' => 'add-book']))],
        ['action' => 'edit-book', 'label' => 'Edit Book', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library', 'demo-action' => 'edit-book']))],
        ['action' => 'delete-book', 'label' => 'Delete Book', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library', 'demo-action' => 'delete-book']))],
    ]],
    ['section' => 'library_transactions', 'label' => 'Library Transactions', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions'])), 'icon' => 'book-reader', 'submenu' => [
        ['action' => 'manage-transactions', 'label' => 'Manage Transactions', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions', 'demo-action' => 'manage-transactions']))],
        ['action' => 'add-transaction', 'label' => 'Add Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions', 'demo-action' => 'add-transaction']))],
        ['action' => 'edit-transaction', 'label' => 'Edit Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions', 'demo-action' => 'edit-transaction']))],
        ['action' => 'delete-transaction', 'label' => 'Delete Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions', 'demo-action' => 'delete-transaction']))],
    ]],
    ['section' => 'inventory', 'label' => 'Inventory', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory'])), 'icon' => 'boxes', 'submenu' => [
        ['action' => 'manage-items', 'label' => 'Manage Items', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory', 'demo-action' => 'manage-items']))],
        ['action' => 'add-item', 'label' => 'Add Item', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory', 'demo-action' => 'add-item']))],
        ['action' => 'edit-item', 'label' => 'Edit Item', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory', 'demo-action' => 'edit-item']))],
        ['action' => 'delete-item', 'label' => 'Delete Item', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory', 'demo-action' => 'delete-item']))],
    ]],
    ['section' => 'inventory_transactions', 'label' => 'Inventory Transactions', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions'])), 'icon' => 'cogs', 'submenu' => [
        ['action' => 'manage-transactions', 'label' => 'Manage Transactions', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions', 'demo-action' => 'manage-transactions']))],
        ['action' => 'add-transaction', 'label' => 'Add Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions', 'demo-action' => 'add-transaction']))],
        ['action' => 'edit-transaction', 'label' => 'Edit Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions', 'demo-action' => 'edit-transaction']))],
        ['action' => 'delete-transaction', 'label' => 'Delete Transaction', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions', 'demo-action' => 'delete-transaction']))],
    ]],
    ['section' => 'chats', 'label' => 'Chats', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats'])), 'icon' => 'envelope', 'submenu' => [
        ['action' => 'view-chats', 'label' => 'View Chats', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats', 'demo-action' => 'view-chats']))],
        ['action' => 'view-chat', 'label' => 'View Chat', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats', 'demo-action' => 'view-chat']))],
    ]],
    ['section' => 'reports', 'label' => 'Reports', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports'])), 'icon' => 'chart-bar', 'submenu' => [
        ['action' => 'view-reports', 'label' => 'View Reports', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports', 'demo-action' => 'view-reports']))],
        ['action' => 'generate-report', 'label' => 'Generate Report', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports', 'demo-action' => 'generate-report']))],
    ]],
    ['section' => 'results', 'label' => 'Results', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results'])), 'icon' => 'graduation-cap', 'submenu' => [
        ['action' => 'manage-results', 'label' => 'Manage Results', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results', 'demo-action' => 'manage-results']))],
        ['action' => 'add-result', 'label' => 'Add Result', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results', 'demo-action' => 'add-result']))],
        ['action' => 'edit-result', 'label' => 'Edit Result', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results', 'demo-action' => 'edit-result']))],
        ['action' => 'delete-result', 'label' => 'Delete Result', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results', 'demo-action' => 'delete-result']))],
    ]],
    ['section' => 'fee_templates', 'label' => 'Fee Templates', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates'])), 'icon' => 'file-invoice-dollar', 'submenu' => [
        ['action' => 'manage-templates', 'label' => 'Manage Templates', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates', 'demo-action' => 'manage-templates']))],
        ['action' => 'add-template', 'label' => 'Add Template', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates', 'demo-action' => 'add-template']))],
        ['action' => 'edit-template', 'label' => 'Edit Template', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates', 'demo-action' => 'edit-template']))],
        ['action' => 'delete-template', 'label' => 'Delete Template', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates', 'demo-action' => 'delete-template']))],
    ]],
    ['section' => 'transport', 'label' => 'Transport', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport'])), 'icon' => 'bus', 'submenu' => [
        ['action' => 'manage-routes', 'label' => 'Manage Routes', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport', 'demo-action' => 'manage-routes']))],
        ['action' => 'add-route', 'label' => 'Add Route', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport', 'demo-action' => 'add-route']))],
        ['action' => 'edit-route', 'label' => 'Edit Route', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport', 'demo-action' => 'edit-route']))],
        ['action' => 'delete-route', 'label' => 'Delete Route', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport', 'demo-action' => 'delete-route']))],
    ]],
    ['section' => 'transport_enrollments', 'label' => 'Transport Enrollments', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments'])), 'icon' => 'bus-alt', 'submenu' => [
        ['action' => 'manage-enrollments', 'label' => 'Manage Enrollments', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments', 'demo-action' => 'manage-enrollments']))],
        ['action' => 'add-enrollment', 'label' => 'Add Enrollment', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments', 'demo-action' => 'add-enrollment']))],
        ['action' => 'edit-enrollment', 'label' => 'Edit Enrollment', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments', 'demo-action' => 'edit-enrollment']))],
        ['action' => 'delete-enrollment', 'label' => 'Delete Enrollment', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments', 'demo-action' => 'delete-enrollment']))],
    ]],
    ['section' => 'departments', 'label' => 'Departments', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments'])), 'icon' => 'building', 'submenu' => [
        ['action' => 'manage-departments', 'label' => 'Manage Departments', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments', 'demo-action' => 'manage-departments']))],
        ['action' => 'add-department', 'label' => 'Add Department', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments', 'demo-action' => 'add-department']))],
        ['action' => 'edit-department', 'label' => 'Edit Department', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments', 'demo-action' => 'edit-department']))],
        ['action' => 'delete-department', 'label' => 'Delete Department', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments', 'demo-action' => 'delete-department']))],
    ]],
    ['section' => 'subjects', 'label' => 'Subjects', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects'])), 'icon' => 'book', 'submenu' => [
        ['action' => 'manage-subjects', 'label' => 'Manage Subjects', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects', 'demo-action' => 'manage-subjects']))],
        ['action' => 'add-subject', 'label' => 'Add Subject', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects', 'demo-action' => 'add-subject']))],
        ['action' => 'edit-subject', 'label' => 'Edit Subject', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects', 'demo-action' => 'edit-subject']))],
        ['action' => 'delete-subject', 'label' => 'Delete Subject', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects', 'demo-action' => 'delete-subject']))],
    ]],
    ['section' => 'timetable', 'label' => 'Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable'])), 'icon' => 'calendar-alt', 'submenu' => [
        ['action' => 'view-timetable', 'label' => 'View Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable', 'demo-action' => 'view-timetable']))],
        ['action' => 'add-timetable', 'label' => 'Add Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable', 'demo-action' => 'add-timetable']))],
        ['action' => 'edit-timetable', 'label' => 'Edit Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable', 'demo-action' => 'edit-timetable']))],
        ['action' => 'delete-timetable', 'label' => 'Delete Timetable', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable', 'demo-action' => 'delete-timetable']))],
    ]],
    ['section' => 'homework', 'label' => 'Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework'])), 'icon' => 'tasks', 'submenu' => [
        ['action' => 'manage-homework', 'label' => 'Manage Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework', 'demo-action' => 'manage-homework']))],
        ['action' => 'add-homework', 'label' => 'Add Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework', 'demo-action' => 'add-homework']))],
        ['action' => 'edit-homework', 'label' => 'Edit Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework', 'demo-action' => 'edit-homework']))],
        ['action' => 'delete-homework', 'label' => 'Delete Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework', 'demo-action' => 'delete-homework']))],
    ]],
    ['section' => 'classes_sections', 'label' => 'Classes & Sections', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections'])), 'icon' => 'school', 'submenu' => [
        ['action' => 'manage-classes-sections', 'label' => 'Manage Classes & Sections', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections', 'demo-action' => 'manage-classes-sections']))],
        ['action' => 'add-class-section', 'label' => 'Add Class/Section', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections', 'demo-action' => 'add-class-section']))],
        ['action' => 'edit-class-section', 'label' => 'Edit Class/Section', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections', 'demo-action' => 'edit-class-section']))],
        ['action' => 'delete-class-section', 'label' => 'Delete Class/Section', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections', 'demo-action' => 'delete-class-section']))],
    ]],
    ['section' => 'subscriptions', 'label' => 'Subscriptions', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subscriptions'])), 'icon' => 'money-check-alt', 'submenu' => [
        ['action' => 'view-subscriptions', 'label' => 'View Subscriptions', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subscriptions', 'demo-action' => 'view-subscriptions']))],
        ['action' => 'extend-subscription', 'label' => 'Extend Subscription', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subscriptions', 'demo-action' => 'extend-subscription']))],
    ]],
    ['section' => 'parents', 'label' => 'Parents', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents'])), 'icon' => 'user-friends', 'submenu' => [
        ['action' => 'manage-parents', 'label' => 'Manage Parents', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents', 'demo-action' => 'manage-parents']))],
        ['action' => 'add-parent', 'label' => 'Add Parent', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents', 'demo-action' => 'add-parent']))],
        ['action' => 'edit-parent', 'label' => 'Edit Parent', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents', 'demo-action' => 'edit-parent']))],
        ['action' => 'delete-parent', 'label' => 'Delete Parent', 'url' => esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents', 'demo-action' => 'delete-parent']))],
    ]],
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
                    <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../logo instituto.jpg'); ?>" alt="Avatar" 
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
                        <?php elseif ($link['icon'] === 'tasks'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'money-check-alt'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M8 6h8v2H8V6zm8 4H8v2h8v-2zm-8 4h5v2H8v-2zm12-8H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 13H4V8h16v11z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'calendar-alt'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'building'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'boxes'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8 16H7v-4h4v4zm0-6H7V9h4v4zm0-6H7V5h4v2zm6 12h-4v-4h4v4zm0-6h-4V9h4v4zm0-6h-4V5h4v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'book-reader'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M21 4H3c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15H3V6h18v13zM9 8h2v2H9V8zm0 4h2v2H9v-2zm4-4h2v2h-2V8zm0 4h2v2h-2v-2zm4-4h2v2h-2V8zm0 4h2v2h-2v-2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'cogs'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.44.17-.47-.41l-.36 2.54c-.59.24-1.13.56-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.3-.06.64-.06.94s.02.64.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.04.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.03-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'bullhorn'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 18H4V4h16v16zM18 6h-5l-1 2h6V6zm-1 4H8l-1 2h10v-2zm-4 4H6l-1 2h8v-2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'megaphone'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M18 3v2h-2V3H8v2H6V3c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h2.01L9 18h6l1 3h2c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8 12H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm8 8h-2v-2h2v2zm0-4h-2V9h2v2zm0-4h-2V5h2v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'envelope'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'chart-bar'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M22 3H2c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 14h-4v-4h4v4zm-6 0h-4V7h4v10zm-6 0H4V9h4v8z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'graduation-cap'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'file-invoice-dollar'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 11h-3v2h1.5c.83 0 1.5-.67 1.5-1.5 0-.83-.67-1.5-1.5-1.5zm-3-3h3c0-1.66-1.34-3-3-3v2zm-2-3c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1h3v2h-3c-1.66 0-3-1.34-3-3V8h3zm-3 6H6v-2h4v2zm0-4H6V8h4v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'bus'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M4 16c0 .88.39 1.67 1 2.22V20c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h8v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1.78c.61-.55 1-1.34 1-2.22V6c0-3.5-3.58-4-8-4s-8 .5-8 4v10zm3.5 1c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V6h12v5z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'bus-alt'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M17 3H7c-1.1 0-2 .9-2 2v11c0 .88.39 1.67 1 2.22V20c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h6v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1.78c.61-.55 1-1.34 1-2.22V5c0-1.1-.9-2-2-2zm-2 13.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm-6 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM17 11H7V6h10v5z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'user-friends'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05C16.19 13.88 17 15.03 17 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
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
                        <?php elseif ($link['icon'] === 'tasks'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'money-check-alt'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M8 6h8v2H8V6zm8 4H8v2h8v-2zm-8 4h5v2H8v-2zm12-8H4c-1.1 0-2 .9-2 2v11c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 13H4V8h16v11z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'calendar-alt'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'building'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'boxes'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8 16H7v-4h4v4zm0-6H7V9h4v4zm0-6H7V5h4v2zm6 12h-4v-4h4v4zm0-6h-4V9h4v4zm0-6h-4V5h4v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'book-reader'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M21 4H3c-1.1 0-2 .9-2 2v13c0 1.1.9 2 2 2h18c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 15H3V6h18v13zM9 8h2v2H9V8zm0 4h2v2H9v-2zm4-4h2v2h-2V8zm0 4h2v2h-2v-2zm4-4h2v2h-2V8zm0 4h2v2h-2v-2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'cogs'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19.14 12.94c.04-.3.06-.61.06-.94s-.02-.64-.06-.94l2.03-1.58c.18-.14.23-.41.12-.61l-1.92-3.32c-.12-.22-.37-.29-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54c-.04-.24-.24-.41-.48-.41h-3.84c-.24 0-.44.17-.47-.41l-.36 2.54c-.59.24-1.13.56-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.04.3-.06.64-.06.94s.02.64.06.94l-2.03 1.58c-.18.14-.23.41-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.04.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.03-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'bullhorn'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 18H4V4h16v16zM18 6h-5l-1 2h6V6zm-1 4H8l-1 2h10v-2zm-4 4H6l-1 2h8v-2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'megaphone'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M18 3v2h-2V3H8v2H6V3c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h2.01L9 18h6l1 3h2c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8 12H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm8 8h-2v-2h2v2zm0-4h-2V9h2v2zm0-4h-2V5h2v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'envelope'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'chart-bar'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M22 3H2c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-2 14h-4v-4h4v4zm-6 0h-4V7h4v10zm-6 0H4V9h4v8z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'graduation-cap'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'file-invoice-dollar'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-1 11h-3v2h1.5c.83 0 1.5-.67 1.5-1.5 0-.83-.67-1.5-1.5-1.5zm-3-3h3c0-1.66-1.34-3-3-3v2zm-2-3c-.55 0-1 .45-1 1v2c0 .55.45 1 1 1h3v2h-3c-1.66 0-3-1.34-3-3V8h3zm-3 6H6v-2h4v2zm0-4H6V8h4v2z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'bus'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M4 16c0 .88.39 1.67 1 2.22V20c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h8v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1.78c.61-.55 1-1.34 1- main-content2.22V6c0-3.5-3.58-4-8-4s-8 .5-8 4v10zm3.5 1c-.83 0-1.5-.67-1.5-1.5S6.67 14 7.5 14s1.5.67 1.5 1.5S8.33 17 7.5 17zm9 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm1.5-6H6V6h12v5z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'bus-alt'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M17 3H7c-1.1 0-2 .9-2 2v11c0 .88.39 1.67 1 2.22V20c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h6v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1.78c.61-.55 1-1.34 1-2.22V5c0-1.1-.9-2-2-2zm-2 13.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm-6 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM17 11H7V6h10v5z"/>
                            </svg>
                        <?php elseif ($link['icon'] === 'user-friends'): ?>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed">
                                <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05C16.19 13.88 17 15.03 17 16.5V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
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

        function toggleSubMenu(button) {
            const subMenu = button.nextElementSibling;
            button.classList.toggle('rotate');
            subMenu.classList.toggle('show');
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
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
                case 'import-student-attendance':
                    echo demoRenderTeacherImportStudentAttendance($data);
                    break;
                case 'export-student-attendance':
                    echo demoRenderTeacherExportStudentAttendance($data);
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
            case 'import-student-attendance':
                echo demoRenderSuperadminImportStudentAttendance($data);
                break;
            case 'export-student-attendance':
                echo demoRenderSuperadminExportStudentAttendance($data);
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
            case 'import-teacher-attendance':
                echo demoRenderSuperadminImportTeacherAttendance($data);
                break;
            case 'export-teacher-attendance':
                echo demoRenderSuperadminExportTeacherAttendance($data);
                break;
           
            case 'staff-attendance':
            case 'manage-staff-attendance':
                echo demoRenderSuperadminStaffAttendance($data);
                break;
            case 'add-staff-attendance':
                echo demoRenderSuperadminAddStaffAttendance($data);
                break;
            case 'edit-staff-attendance':
                echo demoRenderSuperadminEditStaffAttendance($data);
                break;
            case 'delete-staff-attendance':
                echo demoRenderSuperadminDeleteStaffAttendance($data);
                break;
            case 'import-staff-attendance':
                echo demoRenderSuperadminImportStaffAttendance($data);
                break;
            case 'export-staff-attendance':
                echo demoRenderSuperadminExportStaffAttendance($data);
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
    }
     elseif ($section === 'payment_methods') {
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
    } 
     elseif ($section === 'classes') {
            switch ($action) {
                case 'manage-classes':
                    return demoRenderSuperadminClasses($data);
                case 'add-class':
                    return demoRenderSuperadminAddClass($data);
                case 'edit-class':
                    return demoRenderSuperadminEditClass($data);
                case 'delete-class':
                    return demoRenderSuperadminDeleteClass($data);
                default:
                    return demoRenderSuperadminClasses($data);
            }} elseif ($section === 'subjects') {
     
            switch ($action) {
                case 'manage-subjects':
                    return demoRenderSuperadminSubjects($data);
                case 'add-subject':
                    return demoRenderSuperadminAddSubject($data);
                case 'edit-subject':
                    return demoRenderSuperadminEditSubject($data);
                case 'delete-subject':
                    return demoRenderSuperadminDeleteSubject($data);
                default:
                    return demoRenderSuperadminSubjects($data);
            }}
            elseif ($section === 'homeworks') {
            switch ($action) {
                case 'manage-homeworks':
                    return demoRenderSuperadminHomeworks($data);
                case 'add-homework':
                    return demoRenderSuperadminAddHomework($data);
                case 'edit-homework':
                    return demoRenderSuperadminEditHomework($data);
                case 'delete-homework':
                    return demoRenderSuperadminDeleteHomework($data);
                default:
                    return demoRenderSuperadminHomeworks($data);
            }
    } 
            elseif ($section === 'timetable') {
           
                    switch ($action) {
                        case 'manage-timetable':
                            return demoRenderSuperadminTimetable();
                        case 'add-timetable':
                            return demoRenderSuperadminAddTimetable();
                        case 'edit-timetable':
                            return demoRenderSuperadminEditTimetable();
                        case 'delete-timetable':
                            return demoRenderSuperadminDeleteTimetable();
                        default:
                            return demoRenderSuperadminTimetable();
                    }}elseif ($section === 'departments') {
              
                    switch ($action) {
                        case 'manage-departments':
                            return demoRenderSuperadminDepartments();
                        case 'add-department':
                            return demoRenderSuperadminAddDepartment();
                        case 'edit-department':
                            return demoRenderSuperadminEditDepartment();
                        case 'delete-department':
                            return demoRenderSuperadminDeleteDepartment();
                        default:
                            return demoRenderSuperadminDepartments();
                    }}elseif ($section === 'library') {
                    switch ($action) {
                        case 'manage-library':
                            return demoRenderSuperadminLibrary();
                        case 'add-book':
                            return demoRenderSuperadminAddBook();
                        case 'edit-book':
                            return demoRenderSuperadminEditBook();
                        case 'delete-book':
                            return demoRenderSuperadminDeleteBook();
                        default:
                            return demoRenderSuperadminLibrary();
                    }}
                    elseif ($section === 'inventory') {
                
                    switch ($action) {
                        case 'manage-inventory':
                            return demoRenderSuperadminInventory();
                        case 'add-item':
                            return demoRenderSuperadminAddItem();
                        case 'edit-item':
                            return demoRenderSuperadminEditItem();
                        case 'delete-item':
                            return demoRenderSuperadminDeleteItem();
                        default:
                            return demoRenderSuperadminInventory();
                    }  } 
                    elseif ($section === 'library-transactions') {
                
                            switch ($action) {
                                case 'manage-library-transactions':
                                    return demoRenderSuperadminLibraryTransactions();
                                case 'add-transaction':
                                    return demoRenderSuperadminAddLibraryTransaction();
                                case 'edit-transaction':
                                    return demoRenderSuperadminEditLibraryTransaction();
                                case 'delete-transaction':
                                    return demoRenderSuperadminDeleteLibraryTransaction();
                                default:
                                    return demoRenderSuperadminLibraryTransactions();
                                }}elseif ($section === 'inventory-transactions') {
          
                            switch ($action) {
                                case 'manage-inventory-transactions':
                                    return demoRenderSuperadminInventoryTransactions();
                                case 'add-transaction':
                                    return demoRenderSuperadminAddInventoryTransaction();
                                case 'edit-transaction':
                                    return demoRenderSuperadminEditInventoryTransaction();
                                case 'delete-transaction':
                                    return demoRenderSuperadminDeleteInventoryTransaction();
                                default:
                                    return demoRenderSuperadminInventoryTransactions();
                                }}elseif ($section === 'noticeboard') {
                     
                            switch ($action) {
                                case 'manage-noticeboard':
                                    return demoRenderSuperadminNoticeboard();
                                case 'add-notice':
                                    return demoRenderSuperadminAddNotice();
                                case 'edit-notice':
                                    return demoRenderSuperadminEditNotice();
                                case 'delete-notice':
                                    return demoRenderSuperadminDeleteNotice();
                                default:
                                    return demoRenderSuperadminNoticeboard();
                            }}
                            
                            elseif ($section === 'announcements') {
                     
                            switch ($action) {
                                case 'manage-announcements':
                                    return demoRenderSuperadminAnnouncements();
                                case 'add-announcement':
                                    return demoRenderSuperadminAddAnnouncement();
                                case 'edit-announcement':
                                    return demoRenderSuperadminEditAnnouncement();
                                case 'delete-announcement':
                                    return demoRenderSuperadminDeleteAnnouncement();
                                default:
                                    return demoRenderSuperadminAnnouncements();
                            }  } 
    elseif ($section === 'fees') {
        switch ($action) {
           
            case 'manage-fees':
                echo demoRenderSuperadminFees($data);
                break;
            case 'add-fee':
                echo demoRenderSuperadminAddFee($data);
                break;
            case 'edit-fee':
                echo demoRenderSuperadminEditFee($data);
                break;
            case 'delete-fee':
                echo demoRenderSuperadminDeleteFee($data);
                break;
            case 'import-fees':
                echo demoRenderSuperadminImportFees($data);
                break;
            case 'export-fees':
                echo demoRenderSuperadminExportFees($data);
                break;
            case 'fee-templates':
            case 'manage-fee-templates':
                echo demoRenderSuperadminFeeTemplates($data);
                break;
            case 'add-fee-template':
                echo demoRenderSuperadminAddFeeTemplate($data);
                break;
            case 'edit-fee-template':
                echo demoRenderSuperadminEditFeeTemplate($data);
                break;
            case 'delete-fee-template':
                echo demoRenderSuperadminDeleteFeeTemplate($data);
                break;
            case 'import-fee-templates':
                echo demoRenderSuperadminImportFeeTemplates($data);
                break;
            case 'export-fee-templates':
                echo demoRenderSuperadminExportFeeTemplates($data);
                break;
            default:
                echo demoRenderSuperadminFees($data);
                break;
        }}
        elseif ($section === 'chats') {
            switch ($action) {
                        case 'inbox':
                            return demoRenderSuperadminChats($action);
                        case 'new-conversation':
                            return demoRenderSuperadminNewConversation();
                        default:
                            return demoRenderSuperadminChats($action);
                    }
            }
    else {
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
/**
 * Superadmin Teacher Attendance (continued)
 */
function demoRenderSuperadminManageTeacherAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Manage Teacher Attendance</h2>
        <div class="edu-actions">
            <input type="text" id="attendance-search" class="edu-search-input" placeholder="Search Attendance...">
            <button class="edu-button edu-button-primary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'add-teacher-attendance'])); ?>'">Add Attendance</button>
            <button class="edu-button edu-button-secondary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'import-teacher-attendance'])); ?>'">Import CSV</button>
            <button class="edu-button edu-button-secondary" id="export-attendance">Export CSV</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="attendance-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teacher ID</th>
                        <th>Name</th>
                        <th>Center</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="attendance-table-body">
    <?php foreach ($data['teacher_attendance'] as $att): ?>
        <tr data-attendance-id="<?php echo esc_attr($att['teacher_id']); ?>"> <!-- Using teacher_id -->
            <td><?php echo esc_html($att['teacher_id']); ?></td> <!-- teacher_id instead of id -->
 
                           <td><?php echo esc_html($att['teacher_name']); ?></td>
                            <td><?php echo esc_html($att['center']); ?></td>
                            <td><?php echo esc_html($att['date']); ?></td>
                            <td><?php echo esc_html($att['status']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-attendance" data-attendance-id="<?php echo esc_attr($att['id']); ?>">Edit</button>
                                <button class="edu-button edu-button-delete delete-attendance" data-attendance-id="<?php echo esc_attr($att['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="edu-pagination">
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>

        <!-- Edit Attendance Modal -->
        <div id="edit-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-attendance-modal">×</span>
                <h2>Edit Teacher Attendance</h2>
                <form id="edit-attendance-form" class="edu-form">
                    <input type="hidden" id="edit-attendance-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-teacher-id">Teacher ID</label>
                        <input type="text" class="edu-form-input" id="edit-teacher-id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-teacher-name">Teacher Name</label>
                        <input type="text" class="edu-form-input" id="edit-teacher-name" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-center" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-date">Date</label>
                        <input type="date" class="edu-form-input" id="edit-date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-status">Status</label>
                        <select class="edu-form-input" id="edit-status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-attendance">Update Attendance</button>
                </form>
                <div class="edu-form-message" id="edit-attendance-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let attendanceData = <?php echo json_encode($data['teacher_attendance']); ?>;
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function loadAttendance(page, limit, query) {
                const filtered = attendanceData.filter(a => 
                    !query || a.teacher_name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach(att => {
                    html += `
                        <tr data-attendance-id="${att.id}">
                            <td>${att.id}</td>
                            <td>${att.teacher_id}</td>
                            <td>${att.teacher_name}</td>
                            <td>${att.center}</td>
                            <td>${att.date}</td>
                            <td>${att.status}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-attendance" data-attendance-id="${att.id}">Edit</button>
                                <button class="edu-button edu-button-delete delete-attendance" data-attendance-id="${att.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#attendance-table-body').html(html || '<tr><td colspan="7">No attendance records found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadAttendance(currentPage, perPage, searchQuery);

            $('#attendance-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadAttendance(currentPage, perPage, searchQuery);
            });

            $('#prev-page').on('click', function() { currentPage--; loadAttendance(currentPage, perPage, searchQuery); });
            $('#next-page').on('click', function() { currentPage++; loadAttendance(currentPage, perPage, searchQuery); });

            $(document).on('click', '.edit-attendance', function() {
                const attId = $(this).data('attendance-id');
                const att = attendanceData.find(a => a.id == attId);
                if (att) {
                    $('#edit-attendance-id').val(att.id);
                    $('#edit-teacher-id').val(att.teacher_id);
                    $('#edit-teacher-name').val(att.teacher_name);
                    $('#edit-center').val(att.center);
                    $('#edit-date').val(att.date);
                    $('#edit-status').val(att.status);
                    $('#edit-attendance-modal').show();
                } else {
                    alert('Attendance record not found.');
                }
            });

            $('#update-attendance').on('click', function() {
                const id = $('#edit-attendance-id').val();
                const date = $('#edit-date').val();
                const status = $('#edit-status').val();
                if (date && status) {
                    const att = attendanceData.find(a => a.id == id);
                    if (att) {
                        att.date = date;
                        att.status = status;
                        $('#edit-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                        setTimeout(() => {
                            $('#edit-attendance-modal').hide();
                            $('#edit-attendance-message').removeClass('edu-success').text('');
                            loadAttendance(currentPage, perPage, searchQuery);
                        }, 1000);
                    }
                } else {
                    $('#edit-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-attendance', function() {
                if (!confirm('Are you sure you want to delete this attendance record?')) return;
                const attId = $(this).data('attendance-id');
                attendanceData = attendanceData.filter(a => a.id != attId);
                loadAttendance(currentPage, perPage, searchQuery);
            });

            $('#export-attendance').on('click', function() {
                const csv = attendanceData.map(row => `${row.id},${row.teacher_id},${row.teacher_name},${row.center},${row.date},${row.status}`).join('\n');
                const headers = 'ID,Teacher ID,Teacher Name,Center,Date,Status\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'teacher_attendance.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('.edu-modal-close').on('click', function() { $('#edit-attendance-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddTeacherAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Teacher Attendance</h2>
        <div class="card p-4 bg-light">
            <form id="add-attendance-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-id">Teacher ID</label>
                    <input type="text" class="edu-form-input" id="teacher-id" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="teacher-name">Teacher Name</label>
                    <input type="text" class="edu-form-input" id="teacher-name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center">Center</label>
                    <input type="text" class="edu-form-input" id="center" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="date">Date</label>
                    <input type="date" class="edu-form-input" id="date" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="status">Status</label>
                    <select class="edu-form-input" id="status" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="save-attendance">Add Attendance</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="add-attendance-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#save-attendance').on('click', function() {
                const teacher_id = $('#teacher-id').val();
                const teacher_name = $('#teacher-name').val();
                const center = $('#center').val();
                const date = $('#date').val();
                const status = $('#status').val();
                if (teacher_id && teacher_name && center && date && status) {
                    $('#add-attendance-message').addClass('edu-success').text('Attendance added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>';
                    }, 1000);
                } else {
                    $('#add-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditTeacherAttendance($data = []) {
    ob_start();
    $att_id = isset($_GET['att_id']) ? sanitize_text_field($_GET['att_id']) : '';
    $att = null;
    foreach ($data['teacher_attendance'] as $record) {
        if ($record['id'] === $att_id) {
            $att = $record;
            break;
        }
    }
    if (!$att) {
        ?>
        <div class="dashboard-section">
            <h2>Edit Teacher Attendance</h2>
            <div class="alert alert-warning">Attendance record not found.</div>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="edu-button edu-button-secondary">Back</a>
        </div>
        <?php
        return ob_get_clean();
    }
    ?>
    <div class="dashboard-section">
        <h2>Edit Teacher Attendance</h2>
        <div class="card p-4 bg-light">
            <form id="edit-attendance-form" class="edu-form">
                <input type="hidden" id="edit-attendance-id" value="<?php echo esc_attr($att['id']); ?>">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-teacher-id">Teacher ID</label>
                    <input type="text" class="edu-form-input" id="edit-teacher-id" value="<?php echo esc_attr($att['teacher_id']); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-teacher-name">Teacher Name</label>
                    <input type="text" class="edu-form-input" id="edit-teacher-name" value="<?php echo esc_attr($att['teacher_name']); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center">Center</label>
                    <input type="text" class="edu-form-input" id="edit-center" value="<?php echo esc_attr($att['center']); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-date">Date</label>
                    <input type="date" class="edu-form-input" id="edit-date" value="<?php echo esc_attr($att['date']); ?>" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-status">Status</label>
                    <select class="edu-form-input" id="edit-status" required>
                        <option value="Present" <?php echo $att['status'] === 'Present' ? 'selected' : ''; ?>>Present</option>
                        <option value="Absent" <?php echo $att['status'] === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                        <option value="Late" <?php echo $att['status'] === 'Late' ? 'selected' : ''; ?>>Late</option>
                    </select>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="update-attendance">Update Attendance</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="edit-attendance-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#update-attendance').on('click', function() {
                const id = $('#edit-attendance-id').val();
                const date = $('#edit-date').val();
                const status = $('#edit-status').val();
                if (date && status) {
                    $('#edit-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>';
                    }, 1000);
                } else {
                    $('#edit-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteTeacherAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Teacher Attendance</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="attendance-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teacher ID</th>
                        <th>Name</th>
                        <th>Center</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="attendance-table-body">
                    <?php foreach ($data['teacher_attendance'] as $att): ?>
                        <tr data-attendance-id="<?php echo esc_attr($att['id']); ?>">
                            <td><?php echo esc_html($att['id']); ?></td>
                            <td><?php echo esc_html($att['teacher_id']); ?></td>
                            <td><?php echo esc_html($att['teacher_name']); ?></td>
                            <td><?php echo esc_html($att['center']); ?></td>
                            <td><?php echo esc_html($att['date']); ?></td>
                            <td><?php echo esc_html($att['status']); ?></td>
                            <td>
                                <button class="edu-button edu-button-delete delete-attendance" data-attendance-id="<?php echo esc_attr($att['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let attendanceData = <?php echo json_encode($data['teacher_attendance']); ?>;
            $(document).on('click', '.delete-attendance', function() {
                if (!confirm('Are you sure you want to delete this attendance record?')) return;
                const attId = $(this).data('attendance-id');
                attendanceData = attendanceData.filter(a => a.id != attId);
                window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>';
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminImportTeacherAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Import Teacher Attendance</h2>
        <div class="edu-form-group">
            <label class="edu-form-label" for="import-teacher-attendance">Upload CSV File</label>
            <input type="file" class="edu-form-input" id="import-teacher-attendance" accept=".csv">
        </div>
        <button type="button" class="edu-button edu-button-primary" id="import-teacher-attendance-btn">Import</button>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
        <div class="edu-form-message" id="import-teacher-attendance-message"></div>
        <p style="margin-top: 20px;">
            <strong>CSV Format:</strong><br>
            ID,Teacher ID,Teacher Name,Center,Date,Status<br>
            Example: 1,T001,John Doe,Main Campus,2025-04-12,Present
        </p>
        <script>
        jQuery(document).ready(function($) {
            let teacherAttendanceData = <?php echo json_encode($data['teacher_attendance'] ?? []); ?>;
            $('#import-teacher-attendance-btn').on('click', function() {
                const fileInput = $('#import-teacher-attendance')[0];
                const file = fileInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [id, teacher_id, teacher_name, center, date, status] = row.split(',');
                            return { id, teacher_id, teacher_name, center, date, status };
                        });
                        teacherAttendanceData.push(...newRecords);
                        $('#import-teacher-attendance-message').addClass('edu-success').text('Teacher attendance imported successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>';
                        }, 1000);
                    };
                    reader.readAsText(file);
                    fileInput.value = '';
                } else {
                    $('#import-teacher-attendance-message').addClass('edu-error').text('Please select a CSV file.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminExportTeacherAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Export Teacher Attendance</h2>
        <p>Click the button below to download a CSV file of all teacher attendance records.</p>
        <button type="button" class="edu-button edu-button-primary" id="export-teacher-attendance">Export CSV</button>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
        <div class="edu-form-message" id="export-teacher-attendance-message"></div>
        <script>
        jQuery(document).ready(function($) {
            const teacherAttendanceData = <?php echo json_encode($data['teacher_attendance'] ?? []); ?>;
            $('#export-teacher-attendance').on('click', function() {
                const csv = teacherAttendanceData.map(row => `${row.id},${row.teacher_id},${row.teacher_name},${row.center},${row.date},${row.status}`).join('\n');
                const headers = 'ID,Teacher ID,Teacher Name,Center,Date,Status\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'teacher_attendance.csv';
                a.click();
                window.URL.revokeObjectURL(url);
                $('#export-teacher-attendance-message').addClass('edu-success').text('Teacher attendance exported successfully!');
                setTimeout(() => {
                    $('#export-teacher-attendance-message').removeClass('edu-success').text('');
                }, 1000);
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Superadmin Student Attendance
 */
function demoRenderSuperadminManageStudentAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Manage Student Attendance</h2>
        <div class="edu-actions">
            <input type="text" id="attendance-search" class="edu-search-input" placeholder="Search Attendance...">
            <button class="edu-button edu-button-primary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'add-student-attendance'])); ?>'">Add Attendance</button>
            <button class="edu-button edu-button-secondary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'import-student-attendance'])); ?>'">Import CSV</button>
            <button class="edu-button edu-button-secondary" id="export-attendance">Export CSV</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="attendance-table">
                <thead>
                    <tr>
                        <th>ID</th>
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
                <tbody id="attendance-table-body">
    <?php foreach ($data['student_attendance'] as $att): ?>
        <tr data-attendance-id="<?php echo esc_attr($att['student_id']); ?>"> <!-- Using student_id here -->
            <td><?php echo esc_html($att['student_id']); ?></td> <!-- student_id instead of id -->
            <td><?php echo esc_html($att['student_name']); ?></td>
           
                            <td><?php echo esc_html($att['class']); ?></td>
                            <td><?php echo esc_html($att['section']); ?></td>
                            <td><?php echo esc_html($att['center']); ?></td>
                            <td><?php echo esc_html($att['date']); ?></td>
                            <td><?php echo esc_html($att['status']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-attendance" data-attendance-id="<?php echo esc_attr($att['id']); ?>">Edit</button>
                                <button class="edu-button edu-button-delete delete-attendance" data-attendance-id="<?php echo esc_attr($att['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="edu-pagination">
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>

        <!-- Edit Attendance Modal -->
        <div id="edit-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-attendance-modal">×</span>
                <h2>Edit Student Attendance</h2>
                <form id="edit-attendance-form" class="edu-form">
                    <input type="hidden" id="edit-attendance-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="edit-student-id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="edit-student-name" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-class">Class</label>
                        <input type="text" class="edu-form-input" id="edit-class" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-section">Section</label>
                        <input type="text" class="edu-form-input" id="edit-section" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-center" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-date">Date</label>
                        <input type="date" class="edu-form-input" id="edit-date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-status">Status</label>
                        <select class="edu-form-input" id="edit-status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-attendance">Update Attendance</button>
                </form>
                <div class="edu-form-message" id="edit-attendance-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let attendanceData = <?php echo json_encode($data['student_attendance']); ?>;
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function loadAttendance(page, limit, query) {
                const filtered = attendanceData.filter(a => 
                    !query || a.student_name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach(att => {
                    html += `
                        <tr data-attendance-id="${att.id}">
                            <td>${att.id}</td>
                            <td>${att.student_id}</td>
                            <td>${att.student_name}</td>
                            <td>${att.class}</td>
                            <td>${att.section}</td>
                            <td>${att.center}</td>
                            <td>${att.date}</td>
                            <td>${att.status}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-attendance" data-attendance-id="${att.id}">Edit</button>
                                <button class="edu-button edu-button-delete delete-attendance" data-attendance-id="${att.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#attendance-table-body').html(html || '<tr><td colspan="9">No attendance records found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadAttendance(currentPage, perPage, searchQuery);

            $('#attendance-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadAttendance(currentPage, perPage, searchQuery);
            });

            $('#prev-page').on('click', function() { currentPage--; loadAttendance(currentPage, perPage, searchQuery); });
            $('#next-page').on('click', function() { currentPage++; loadAttendance(currentPage, perPage, searchQuery); });

            $(document).on('click', '.edit-attendance', function() {
                const attId = $(this).data('attendance-id');
                const att = attendanceData.find(a => a.id == attId);
                if (att) {
                    $('#edit-attendance-id').val(att.id);
                    $('#edit-student-id').val(att.student_id);
                    $('#edit-student-name').val(att.student_name);
                    $('#edit-class').val(att.class);
                    $('#edit-section').val(att.section);
                    $('#edit-center').val(att.center);
                    $('#edit-date').val(att.date);
                    $('#edit-status').val(att.status);
                    $('#edit-attendance-modal').show();
                } else {
                    alert('Attendance record not found.');
                }
            });

            $('#update-attendance').on('click', function() {
                const id = $('#edit-attendance-id').val();
                const date = $('#edit-date').val();
                const status = $('#edit-status').val();
                if (date && status) {
                    const att = attendanceData.find(a => a.id == id);
                    if (att) {
                        att.date = date;
                        att.status = status;
                        $('#edit-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                        setTimeout(() => {
                            $('#edit-attendance-modal').hide();
                            $('#edit-attendance-message').removeClass('edu-success').text('');
                            loadAttendance(currentPage, perPage, searchQuery);
                        }, 1000);
                    }
                } else {
                    $('#edit-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-attendance', function() {
                if (!confirm('Are you sure you want to delete this attendance record?')) return;
                const attId = $(this).data('attendance-id');
                attendanceData = attendanceData.filter(a => a.id != attId);
                loadAttendance(currentPage, perPage, searchQuery);
            });

            $('#export-attendance').on('click', function() {
                const csv = attendanceData.map(row => `${row.id},${row.student_id},${row.student_name},${row.class},${row.section},${row.center},${row.date},${row.status}`).join('\n');
                const headers = 'ID,Student ID,Student Name,Class,Section,Center,Date,Status\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'student_attendance.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('.edu-modal-close').on('click', function() { $('#edit-attendance-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddStudentAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Student Attendance</h2>
        <div class="card p-4 bg-light">
            <form id="add-attendance-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-id">Student ID</label>
                    <input type="text" class="edu-form-input" id="student-id" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-name">Student Name</label>
                    <input type="text" class="edu-form-input" id="student-name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="class">Class</label>
                    <input type="text" class="edu-form-input" id="class" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="section">Section</label>
                    <input type="text" class="edu-form-input" id="section" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center">Center</label>
                    <input type="text" class="edu-form-input" id="center" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="date">Date</label>
                    <input type="date" class="edu-form-input" id="date" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="status">Status</label>
                    <select class="edu-form-input" id="status" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="save-attendance">Add Attendance</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="add-attendance-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#save-attendance').on('click', function() {
                const student_id = $('#student-id').val();
                const student_name = $('#student-name').val();
                const class_name = $('#class').val();
                const section = $('#section').val();
                const center = $('#center').val();
                const date = $('#date').val();
                const status = $('#status').val();
                if (student_id && student_name && class_name && section && center && date && status) {
                    $('#add-attendance-message').addClass('edu-success').text('Attendance added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>';
                    }, 1000);
                } else {
                    $('#add-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditStudentAttendance($data = []) {
    ob_start();
    $att_id = isset($_GET['att_id']) ? sanitize_text_field($_GET['att_id']) : '';
    $att = null;
    foreach ($data['student_attendance'] as $record) {
        if ($record['id'] === $att_id) {
            $att = $record;
            break;
        }
    }
    if (!$att) {
        ?>
        <div class="dashboard-section">
            <h2>Edit Student Attendance</h2>
            <div class="alert alert-warning">Attendance record not found.</div>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="edu-button edu-button-secondary">Back</a>
        </div>
        <?php
        return ob_get_clean();
    }
    ?>
    <div class="dashboard-section">
        <h2>Edit Student Attendance</h2>
        <div class="card p-4 bg-light">
            <form id="edit-attendance-form" class="edu-form">
                <input type="hidden" id="edit-attendance-id" value="<?php echo esc_attr($att['id']); ?>">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-id">Student ID</label>
                    <input type="text" class="edu-form-input" id="edit-student-id" value="<?php echo esc_attr($att['student_id']); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-student-name">Student Name</label>
                    <input type="text" class="edu-form-input" id="edit-student-name" value="<?php echo esc_attr($att['student_name']); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-class">Class</label>
                    <input type="text" class="edu-form-input" id="edit-class" value="<?php echo esc_attr($att['class']); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-section">Section</label>
                    <input type="text" class="edu-form-input" id="edit-section" value="<?php echo esc_attr($att['section']); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-center">Center</label>
                    <input type="text" class="edu-form-input" id="edit-center" value="<?php echo esc_attr($att['center']); ?>" readonly>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-date">Date</label>
                    <input type="date" class="edu-form-input" id="edit-date" value="<?php echo esc_attr($att['date']); ?>" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="edit-status">Status</label>
                    <select class="edu-form-input" id="edit-status" required>
                        <option value="Present" <?php echo $att['status'] === 'Present' ? 'selected' : ''; ?>>Present</option>
                        <option value="Absent" <?php echo $att['status'] === 'Absent' ? 'selected' : ''; ?>>Absent</option>
                        <option value="Late" <?php echo $att['status'] === 'Late' ? 'selected' : ''; ?>>Late</option>
                    </select>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="update-attendance">Update Attendance</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="edit-attendance-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#update-attendance').on('click', function() {
                const id = $('#edit-attendance-id').val();
                const date = $('#edit-date').val();
                const status = $('#edit-status').val();
                if (date && status) {
                    $('#edit-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>';
                    }, 1000);
                } else {
                    $('#edit-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteStudentAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Student Attendance</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="attendance-table">
                <thead>
                    <tr>
                        <th>ID</th>
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
                <tbody id="attendance-table-body">
                    <?php foreach ($data['student_attendance'] as $att): ?>
                        <tr data-attendance-id="<?php echo esc_attr($att['id']); ?>">
                            <td><?php echo esc_html($att['id']); ?></td>
                            <td><?php echo esc_html($att['student_id']); ?></td>
                            <td><?php echo esc_html($att['student_name']); ?></td>
                            <td><?php echo esc_html($att['class']); ?></td>
                            <td><?php echo esc_html($att['section']); ?></td>
                            <td><?php echo esc_html($att['center']); ?></td>
                            <td><?php echo esc_html($att['date']); ?></td>
                            <td><?php echo esc_html($att['status']); ?></td>
                            <td>
                                <button class="edu-button edu-button-delete delete-attendance" data-attendance-id="<?php echo esc_attr($att['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let attendanceData = <?php echo json_encode($data['student_attendance']); ?>;
            $(document).on('click', '.delete-attendance', function() {
                if (!confirm('Are you sure you want to delete this attendance record?')) return;
                const attId = $(this).data('attendance-id');
                attendanceData = attendanceData.filter(a => a.id != attId);
                window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>';
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminImportStudentAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Import Student Attendance</h2>
        <div class="edu-form-group">
            <label class="edu-form-label" for="import-student-attendance">Upload CSV File</label>
            <input type="file" class="edu-form-input" id="import-student-attendance" accept=".csv">
        </div>
        <button type="button" class="edu-button edu-button-primary" id="import-student-attendance-btn">Import</button>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
        <div class="edu-form-message" id="import-student-attendance-message"></div>
        <p style="margin-top: 20px;">
            <strong>CSV Format:</strong><br>
            ID,Student ID,Student Name,Class,Section,Center,Date,Status<br>
            Example: 1,S003,Alice Brown,11,A,Main Campus,2025-04-12,Present
        </p>
        <script>
        jQuery(document).ready(function($) {
            let studentAttendanceData = <?php echo json_encode($data['student_attendance'] ?? []); ?>;
            $('#import-student-attendance-btn').on('click', function() {
                const fileInput = $('#import-student-attendance')[0];
                const file = fileInput.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [id, student_id, student_name, class_name, section, center, date, status] = row.split(',');
                            return { id, student_id, student_name, class: class_name, section, center, date, status };
                        });
                        studentAttendanceData.push(...newRecords);
                        $('#import-student-attendance-message').addClass('edu-success').text('Student attendance imported successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>';
                        }, 1000);
                    };
                    reader.readAsText(file);
                    fileInput.value = '';
                } else {
                    $('#import-student-attendance-message').addClass('edu-error').text('Please select a CSV file.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminExportStudentAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Export Student Attendance</h2>
        <p>Click the button below to download a CSV file of all student attendance records.</p>
        <button type="button" class="edu-button edu-button-primary" id="export-student-attendance">Export CSV</button>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
        <div class="edu-form-message" id="export-student-attendance-message"></div>
        <script>
        jQuery(document).ready(function($) {
            const studentAttendanceData = <?php echo json_encode($data['student_attendance'] ?? []); ?>;
            $('#export-student-attendance').on('click', function() {
                const csv = studentAttendanceData.map(row => `${row.id},${row.student_id},${row.student_name},${row.class},${row.section},${row.center},${row.date},${row.status}`).join('\n');
                const headers = 'ID,Student ID,Student Name,Class,Section,Center,Date,Status\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'student_attendance.csv';
                a.click();
                window.URL.revokeObjectURL(url);
                $('#export-student-attendance-message').addClass('edu-success').text('Student attendance exported successfully!');
                setTimeout(() => {
                    $('#export-student-attendance-message').removeClass('edu-success').text('');
                }, 1000);
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
//
function demoRenderSuperadminAddCenter() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Center</h2>
        <div class="card p-4 bg-light">
            <form id="add-center-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-name">Center Name</label>
                    <input type="text" class="edu-form-input" id="center-name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-location">Location</label>
                    <input type="text" class="edu-form-input" id="center-location">
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-mobile">Mobile</label>
                    <input type="text" class="edu-form-input" id="center-mobile" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center-email">Email</label>
                    <input type="email" class="edu-form-input" id="center-email" required>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="save-center">Add Center</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="add-center-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#save-center').on('click', function() {
                const name = $('#center-name').val();
                const location = $('#center-location').val();
                const mobile = $('#center-mobile').val();
                const email = $('#center-email').val();
                if (name && mobile && email) {
                    $('#add-center-message').addClass('edu-success').text('Center added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers'])); ?>';
                    }, 1000);
                } else {
                    $('#add-center-message').addClass('edu-error').text('Please fill all required fields.');
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


function demoRenderSuperadminCenters($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Centers</h2>
        <div class="edu-actions">
            <input type="text" id="center-search" class="edu-search-input" placeholder="Search Centers...">
            <button class="edu-button edu-button-primary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'add-center'])); ?>'">Add Center</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="centers-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Mobile</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="centers-table-body">
                    <?php foreach ($data['centers'] as $center): ?>
                        <tr data-center-id="<?php echo esc_attr($center['id']); ?>">
                            <td><?php echo esc_html($center['id']); ?></td>
                            <td><?php echo esc_html($center['name']); ?></td>
                            <td><?php echo esc_html($center['location'] ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($center['mobile'] ?? 'N/A'); ?></td>
                            <td><?php echo esc_html($center['email'] ?? 'N/A'); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-center" data-center-id="<?php echo esc_attr($center['id']); ?>">Edit</button>
                                <button class="edu-button edu-button-delete delete-center" data-center-id="<?php echo esc_attr($center['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="edu-pagination">
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>

        <!-- Edit Center Modal -->
        <div id="edit-center-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-center-modal">×</span>
                <h2>Edit Center</h2>
                <form id="edit-center-form" class="edu-form">
                    <input type="hidden" id="edit-center-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-name">Center Name</label>
                        <input type="text" class="edu-form-input" id="edit-center-name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-location">Location</label>
                        <input type="text" class="edu-form-input" id="edit-center-location">
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-mobile">Mobile</label>
                        <input type="text" class="edu-form-input" id="edit-center-mobile" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center-email">Email</label>
                        <input type="email" class="edu-form-input" id="edit-center-email" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-center">Update Center</button>
                </form>
                <div class="edu-form-message" id="edit-center-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let centersData = <?php echo json_encode($data['centers']); ?>;
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function loadCenters(page, limit, query) {
                const filtered = centersData.filter(c => 
                    !query || c.name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach(center => {
                    html += `
                        <tr data-center-id="${center.id}">
                            <td>${center.id}</td>
                            <td>${center.name}</td>
                            <td>${center.location || 'N/A'}</td>
                            <td>${center.mobile || 'N/A'}</td>
                            <td>${center.email || 'N/A'}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-center" data-center-id="${center.id}">Edit</button>
                                <button class="edu-button edu-button-delete delete-center" data-center-id="${center.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#centers-table-body').html(html || '<tr><td colspan="6">No centers found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadCenters(currentPage, perPage, searchQuery);

            $('#center-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadCenters(currentPage, perPage, searchQuery);
            });

            $('#prev-page').on('click', function() { currentPage--; loadCenters(currentPage, perPage, searchQuery); });
            $('#next-page').on('click', function() { currentPage++; loadCenters(currentPage, perPage, searchQuery); });

            $(document).on('click', '.edit-center', function() {
                const centerId = $(this).data('center-id');
                const center = centersData.find(c => c.id == centerId);
                if (center) {
                    $('#edit-center-id').val(center.id);
                    $('#edit-center-name').val(center.name);
                    $('#edit-center-location').val(center.location || '');
                    $('#edit-center-mobile').val(center.mobile || '');
                    $('#edit-center-email').val(center.email || '');
                    $('#edit-center-modal').show();
                }
            });

            $('#update-center').on('click', function() {
                const id = $('#edit-center-id').val();
                const name = $('#edit-center-name').val();
                const location = $('#edit-center-location').val();
                const mobile = $('#edit-center-mobile').val();
                const email = $('#edit-center-email').val();
                if (name && mobile && email) {
                    const center = centersData.find(c => c.id == id);
                    if (center) {
                        center.name = name;
                        center.location = location;
                        center.mobile = mobile;
                        center.email = email;
                        $('#edit-center-message').addClass('edu-success').text('Center updated successfully!');
                        setTimeout(() => {
                            $('#edit-center-modal').hide();
                            $('#edit-center-message').removeClass('edu-success').text('');
                            loadCenters(currentPage, perPage, searchQuery);
                        }, 1000);
                    }
                } else {
                    $('#edit-center-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-center', function() {
                if (!confirm('Are you sure you want to delete this center?')) return;
                const centerId = $(this).data('center-id');
                centersData = centersData.filter(c => c.id != centerId);
                loadCenters(currentPage, perPage, searchQuery);
            });

            $('.edu-modal-close').on('click', function() { $('#edit-center-modal').hide(); });
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

function demoRenderSuperadminSubscription($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Subscriptions</h2>
        <div class="edu-actions">
            <input type="text" id="subscription-search" class="edu-search-input" placeholder="Search Subscriptions...">
            <button class="edu-button edu-button-primary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'add-subscription'])); ?>'">Add Subscription</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="subscriptions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Center</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="subscriptions-table-body">
                    <?php foreach ($data['subscriptions'] as $sub): ?>
                        <tr data-subscription-id="<?php echo esc_attr($sub['id']); ?>">
                            <td><?php echo esc_html($sub['id']); ?></td>
                            <td><?php echo esc_html($sub['center']); ?></td>
                            <td><?php echo esc_html($sub['plan']); ?></td>
                            <td><?php echo esc_html($sub['start_date']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-subscription" data-subscription-id="<?php echo esc_attr($sub['id']); ?>">Edit</button>
                                <button class="edu-button edu-button-delete delete-subscription" data-subscription-id="<?php echo esc_attr($sub['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="edu-pagination">
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>

        <!-- Edit Subscription Modal -->
        <div id="edit-subscription-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-subscription-modal">×</span>
                <h2>Edit Subscription</h2>
                <form id="edit-subscription-form" class="edu-form">
                    <input type="hidden" id="edit-subscription-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-subscription-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-subscription-center" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-subscription-plan">Plan</label>
                        <select class="edu-form-input" id="edit-subscription-plan" required>
                            <option value="Basic">Basic</option>
                            <option value="Standard">Standard</option>
                            <option value="Premium">Premium</option>
                        </select>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-subscription-start-date">Start Date</label>
                        <input type="date" class="edu-form-input" id="edit-subscription-start-date" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-subscription">Update Subscription</button>
                </form>
                <div class="edu-form-message" id="edit-subscription-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let subscriptionsData = <?php echo json_encode($data['subscriptions']); ?>;
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function loadSubscriptions(page, limit, query) {
                const filtered = subscriptionsData.filter(s => 
                    !query || s.center.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach(sub => {
                    html += `
                        <tr data-subscription-id="${sub.id}">
                            <td>${sub.id}</td>
                            <td>${sub.center}</td>
                            <td>${sub.plan}</td>
                            <td>${sub.start_date}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-subscription" data-subscription-id="${sub.id}">Edit</button>
                                <button class="edu-button edu-button-delete delete-subscription" data-subscription-id="${sub.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#subscriptions-table-body').html(html || '<tr><td colspan="5">No subscriptions found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadSubscriptions(currentPage, perPage, searchQuery);

            $('#subscription-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadSubscriptions(currentPage, perPage, searchQuery);
            });

            $('#prev-page').on('click', function() { currentPage--; loadSubscriptions(currentPage, perPage, searchQuery); });
            $('#next-page').on('click', function() { currentPage++; loadSubscriptions(currentPage, perPage, searchQuery); });

            $(document).on('click', '.edit-subscription', function() {
                const subId = $(this).data('subscription-id');
                const sub = subscriptionsData.find(s => s.id == subId);
                if (sub) {
                    $('#edit-subscription-id').val(sub.id);
                    $('#edit-subscription-center').val(sub.center);
                    $('#edit-subscription-plan').val(sub.plan);
                    $('#edit-subscription-start-date').val(sub.start_date);
                    $('#edit-subscription-modal').show();
                }
            });

            $('#update-subscription').on('click', function() {
                const id = $('#edit-subscription-id').val();
                const center = $('#edit-subscription-center').val();
                const plan = $('#edit-subscription-plan').val();
                const start_date = $('#edit-subscription-start-date').val();
                if (center && plan && start_date) {
                    const sub = subscriptionsData.find(s => s.id == id);
                    if (sub) {
                        sub.center = center;
                        sub.plan = plan;
                        sub.start_date = start_date;
                        $('#edit-subscription-message').addClass('edu-success').text('Subscription updated successfully!');
                        setTimeout(() => {
                            $('#edit-subscription-modal').hide();
                            $('#edit-subscription-message').removeClass('edu-success').text('');
                            loadSubscriptions(currentPage, perPage, searchQuery);
                        }, 1000);
                    }
                } else {
                    $('#edit-subscription-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-subscription', function() {
                if (!confirm('Are you sure you want to delete this subscription?')) return;
                const subId = $(this).data('subscription-id');
                subscriptionsData = subscriptionsData.filter(s => s.id != subId);
                loadSubscriptions(currentPage, perPage, searchQuery);
            });

            $('.edu-modal-close').on('click', function() { $('#edit-subscription-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddSubscription() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Subscription</h2>
        <div class="card p-4 bg-light">
            <form id="add-subscription-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="subscription-center">Center</label>
                    <input type="text" class="edu-form-input" id="subscription-center" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="subscription-plan">Plan</label>
                    <select class="edu-form-input" id="subscription-plan" required>
                        <option value="Basic">Basic</option>
                        <option value="Standard">Standard</option>
                        <option value="Premium">Premium</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="subscription-start-date">Start Date</label>
                    <input type="date" class="edu-form-input" id="subscription-start-date" required>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="save-subscription">Add Subscription</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="add-subscription-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#save-subscription').on('click', function() {
                const center = $('#subscription-center').val();
                const plan = $('#subscription-plan').val();
                const start_date = $('#subscription-start-date').val();
                if (center && plan && start_date) {
                    $('#add-subscription-message').addClass('edu-success').text('Subscription added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription'])); ?>';
                    }, 1000);
                } else {
                    $('#add-subscription-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditSubscription($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Subscription</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="subscriptions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Center</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="subscriptions-table-body">
                    <?php foreach ($data['subscriptions'] as $sub): ?>
                        <tr data-subscription-id="<?php echo esc_attr($sub['id']); ?>">
                            <td><?php echo esc_html($sub['id']); ?></td>
                            <td><?php echo esc_html($sub['center']); ?></td>
                            <td><?php echo esc_html($sub['plan']); ?></td>
                            <td><?php echo esc_html($sub['start_date']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-subscription" data-subscription-id="<?php echo esc_attr($sub['id']); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Subscription Modal -->
        <div id="edit-subscription-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-subscription-modal">×</span>
                <h2>Edit Subscription</h2>
                <form id="edit-subscription-form" class="edu-form">
                    <input type="hidden" id="edit-subscription-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-subscription-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-subscription-center" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-subscription-plan">Plan</label>
                        <select class="edu-form-input" id="edit-subscription-plan" required>
                            <option value="Basic">Basic</option>
                            <option value="Standard">Standard</option>
                            <option value="Premium">Premium</option>
                        </select>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-subscription-start-date">Start Date</label>
                        <input type="date" class="edu-form-input" id="edit-subscription-start-date" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-subscription">Update Subscription</button>
                </form>
                <div class="edu-form-message" id="edit-subscription-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let subscriptionsData = <?php echo json_encode($data['subscriptions']); ?>;

            $(document).on('click', '.edit-subscription', function() {
                const subId = $(this).data('subscription-id');
                const sub = subscriptionsData.find(s => s.id == subId);
                if (sub) {
                    $('#edit-subscription-id').val(sub.id);
                    $('#edit-subscription-center').val(sub.center);
                    $('#edit-subscription-plan').val(sub.plan);
                    $('#edit-subscription-start-date').val(sub.start_date);
                    $('#edit-subscription-modal').show();
                } else {
                    alert('Subscription not found.');
                }
            });

            $('#update-subscription').on('click', function() {
                const id = $('#edit-subscription-id').val();
                const center = $('#edit-subscription-center').val();
                const plan = $('#edit-subscription-plan').val();
                const start_date = $('#edit-subscription-start-date').val();
                if (center && plan && start_date) {
                    const sub = subscriptionsData.find(s => s.id == id);
                    if (sub) {
                        sub.center = center;
                        sub.plan = plan;
                        sub.start_date = start_date;
                        $('#edit-subscription-message').addClass('edu-success').text('Subscription updated successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription'])); ?>';
                        }, 1000);
                    }
                } else {
                    $('#edit-subscription-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $('.edu-modal-close').on('click', function() { $('#edit-subscription-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeleteSubscription($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Subscription</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="subscriptions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Center</th>
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="subscriptions-table-body">
                    <?php foreach ($data['subscriptions'] as $sub): ?>
                        <tr data-subscription-id="<?php echo esc_attr($sub['id']); ?>">
                            <td><?php echo esc_html($sub['id']); ?></td>
                            <td><?php echo esc_html($sub['center']); ?></td>
                            <td><?php echo esc_html($sub['plan']); ?></td>
                            <td><?php echo esc_html($sub['start_date']); ?></td>
                            <td>
                                <button class="edu-button edu-button-delete delete-subscription" data-subscription-id="<?php echo esc_attr($sub['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let subscriptionsData = <?php echo json_encode($data['subscriptions']); ?>;
            $(document).on('click', '.delete-subscription', function() {
                if (!confirm('Are you sure you want to delete this subscription?')) return;
                const subId = $(this).data('subscription-id');
                subscriptionsData = subscriptionsData.filter(s => s.id != subId);
                window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription'])); ?>';
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Superadmin Payment Methods
 */
function demoRenderSuperadminPaymentMethods($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Payment Methods</h2>
        <div class="edu-actions">
            <input type="text" id="payment-search" class="edu-search-input" placeholder="Search Payment Methods...">
            <button class="edu-button edu-button-primary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'add-payment-method'])); ?>'">Add Payment Method</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="payment-methods-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Method</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="payment-methods-table-body">
                    <?php foreach ($data['payment_methods'] as $pm): ?>
                        <tr data-payment-id="<?php echo esc_attr($pm['id']); ?>">
                            <td><?php echo esc_html($pm['id']); ?></td>
                            <td><?php echo esc_html($pm['method']); ?></td>
                            <td><?php echo esc_html($pm['center']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-payment" data-payment-id="<?php echo esc_attr($pm['id']); ?>">Edit</button>
                                <button class="edu-button edu-button-delete delete-payment" data-payment-id="<?php echo esc_attr($pm['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="edu-pagination">
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>

        <!-- Edit Payment Method Modal -->
        <div id="edit-payment-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-payment-modal">×</span>
                <h2>Edit Payment Method</h2>
                <form id="edit-payment-form" class="edu-form">
                    <input type="hidden" id="edit-payment-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-payment-method">Method</label>
                        <input type="text" class="edu-form-input" id="edit-payment-method" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-payment-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-payment-center" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-payment">Update Payment Method</button>
                </form>
                <div class="edu-form-message" id="edit-payment-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let paymentMethodsData = <?php echo json_encode($data['payment_methods']); ?>;
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function loadPaymentMethods(page, limit, query) {
                const filtered = paymentMethodsData.filter(p => 
                    !query || p.method.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach(pm => {
                    html += `
                        <tr data-payment-id="${pm.id}">
                            <td>${pm.id}</td>
                            <td>${pm.method}</td>
                            <td>${pm.center}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-payment" data-payment-id="${pm.id}">Edit</button>
                                <button class="edu-button edu-button-delete delete-payment" data-payment-id="${pm.id}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#payment-methods-table-body').html(html || '<tr><td colspan="4">No payment methods found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadPaymentMethods(currentPage, perPage, searchQuery);

            $('#payment-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadPaymentMethods(currentPage, perPage, searchQuery);
            });

            $('#prev-page').on('click', function() { currentPage--; loadPaymentMethods(currentPage, perPage, searchQuery); });
            $('#next-page').on('click', function() { currentPage++; loadPaymentMethods(currentPage, perPage, searchQuery); });

            $(document).on('click', '.edit-payment', function() {
                const pmId = $(this).data('payment-id');
                const pm = paymentMethodsData.find(p => p.id == pmId);
                if (pm) {
                    $('#edit-payment-id').val(pm.id);
                    $('#edit-payment-method').val(pm.method);
                    $('#edit-payment-center').val(pm.center);
                    $('#edit-payment-modal').show();
                }
            });

            $('#update-payment').on('click', function() {
                const id = $('#edit-payment-id').val();
                const method = $('#edit-payment-method').val();
                const center = $('#edit-payment-center').val();
                if (method && center) {
                    const pm = paymentMethodsData.find(p => p.id == id);
                    if (pm) {
                        pm.method = method;
                        pm.center = center;
                        $('#edit-payment-message').addClass('edu-success').text('Payment method updated successfully!');
                        setTimeout(() => {
                            $('#edit-payment-modal').hide();
                            $('#edit-payment-message').removeClass('edu-success').text('');
                            loadPaymentMethods(currentPage, perPage, searchQuery);
                        }, 1000);
                    }
                } else {
                    $('#edit-payment-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-payment', function() {
                if (!confirm('Are you sure you want to delete this payment method?')) return;
                const pmId = $(this).data('payment-id');
                paymentMethodsData = paymentMethodsData.filter(p => p.id != pmId);
                loadPaymentMethods(currentPage, perPage, searchQuery);
            });

            $('.edu-modal-close').on('click', function() { $('#edit-payment-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminAddPaymentMethods() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Payment Method</h2>
        <div class="card p-4 bg-light">
            <form id="add-payment-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="payment-method">Method</label>
                    <input type="text" class="edu-form-input" id="payment-method" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="payment-center">Center</label>
                    <input type="text" class="edu-form-input" id="payment-center" required>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="save-payment">Add Payment Method</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="add-payment-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#save-payment').on('click', function() {
                const method = $('#payment-method').val();
                const center = $('#payment-center').val();
                if (method && center) {
                    $('#add-payment-message').addClass('edu-success').text('Payment method added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods'])); ?>';
                    }, 1000);
                } else {
                    $('#add-payment-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminEditPaymentMethods($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Payment Method</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="payment-methods-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Method</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="payment-methods-table-body">
                    <?php foreach ($data['payment_methods'] as $pm): ?>
                        <tr data-payment-id="<?php echo esc_attr($pm['id']); ?>">
                            <td><?php echo esc_html($pm['id']); ?></td>
                            <td><?php echo esc_html($pm['id']); ?></td>
                            <td><?php echo esc_html($pm['center']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-payment" data-payment-id="<?php echo esc_attr($pm['id']); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Payment Method Modal -->
        <div id="edit-payment-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-payment-modal">×</span>
                <h2>Edit Payment Method</h2>
                <form id="edit-payment-form" class="edu-form">
                    <input type="hidden" id="edit-payment-id">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-payment-method">Method</label>
                        <input type="text" class="edu-form-input" id="edit-payment-method" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-payment-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-payment-center" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-payment">Update Payment Method</button>
                </form>
                <div class="edu-form-message" id="edit-payment-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let paymentMethodsData = <?php echo json_encode($data['payment_methods']); ?>;

            $(document).on('click', '.edit-payment', function() {
                const pmId = $(this).data('payment-id');
                const pm = paymentMethodsData.find(p => p.id == pmId);
                if (pm) {
                    $('#edit-payment-id').val(pm.id);
                    $('#edit-payment-method').val(pm.method);
                    $('#edit-payment-center').val(pm.center);
                    $('#edit-payment-modal').show();
                } else {
                    alert('Payment method not found.');
                }
            });

            $('#update-payment').on('click', function() {
                const id = $('#edit-payment-id').val();
                const method = $('#edit-payment-method').val();
                const center = $('#edit-payment-center').val();
                if (method && center) {
                    const pm = paymentMethodsData.find(p => p.id == id);
                    if (pm) {
                        pm.method = method;
                        pm.center = center;
                        $('#edit-payment-message').addClass('edu-success').text('Payment method updated successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods'])); ?>';
                        }, 1000);
                    }
                } else {
                    $('#edit-payment-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $('.edu-modal-close').on('click', function() { $('#edit-payment-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminDeletePaymentMethods($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Payment Method</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="payment-methods-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Method</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="payment-methods-table-body">
                    <?php foreach ($data['payment_methods'] as $pm): ?>
                        <tr data-payment-id="<?php echo esc_attr($pm['id']); ?>">
                            <td><?php echo esc_html($pm['id']); ?></td>
                            <td><?php echo esc_html($pm['method']); ?></td>
                            <td><?php echo esc_html($pm['center']); ?></td>
                            <td>
                                <button class="edu-button edu-button-delete delete-payment" data-payment-id="<?php echo esc_attr($pm['id']); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let paymentMethodsData = <?php echo json_encode($data['payment_methods']); ?>;
            $(document).on('click', '.delete-payment', function() {
                if (!confirm('Are you sure you want to delete this payment method?')) return;
                const pmId = $(this).data('payment-id');
                paymentMethodsData = paymentMethodsData.filter(p => p.id != pmId);
                window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods'])); ?>';
            });
        });
        </script>
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
 * Render Staff Attendance Section
 */
function demoRenderSuperadminStaffAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Staff Attendance</h2>
        <div class="edu-actions">
            <input type="text" id="staff-attendance-search" class="edu-search-input" placeholder="Search Staff Attendance...">
            <button class="edu-button edu-button-primary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'add-staff-attendance'])); ?>'">Add Attendance</button>
            <button class="edu-button edu-button-secondary" id="export-staff-attendance">Export CSV</button>
            <input type="file" id="import-staff-attendance" accept=".csv" style="display: none;">
            <button class="edu-button edu-button-secondary" id="import-staff-attendance-btn">Import CSV</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="staff-attendance-table">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Center</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="staff-attendance-table-body">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>
        <div class="edu-pagination">
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>

        <!-- Edit Staff Attendance Modal -->
        <div id="edit-staff-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-staff-attendance-modal">×</span>
                <h2>Edit Staff Attendance</h2>
                <form id="edit-staff-attendance-form" class="edu-form">
                    <input type="hidden" id="edit-staff-attendance-index">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-id">Staff ID</label>
                        <input type="text" class="edu-form-input" id="edit-staff-id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-name">Staff Name</label>
                        <input type="text" class="edu-form-input" id="edit-staff-name" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-role">Role</label>
                        <input type="text" class="edu-form-input" id="edit-staff-role" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-staff-center" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-date">Date</label>
                        <input type="date" class="edu-form-input" id="edit-staff-date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-status">Status</label>
                        <select class="edu-form-input" id="edit-staff-status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-staff-attendance">Update Attendance</button>
                </form>
                <div class="edu-form-message" id="edit-staff-attendance-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let staffAttendanceData = <?php echo json_encode($data['staff_attendance']); ?>;
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function loadStaffAttendance(page, limit, query) {
                const filtered = staffAttendanceData.filter(a => 
                    !query || a.staff_name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach((record, index) => {
                    html += `
                        <tr data-attendance-index="${start + index}">
                            <td>${record.staff_id}</td>
                            <td>${record.staff_name}</td>
                            <td>${record.role}</td>
                            <td>${record.education_center_id}</td>
                            <td>${record.date}</td>
                            <td>${record.status}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-staff-attendance" data-attendance-index="${start + index}">Edit</button>
                                <button class="edu-button edu-button-delete delete-staff-attendance" data-attendance-index="${start + index}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#staff-attendance-table-body').html(html || '<tr><td colspan="7">No attendance records found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadStaffAttendance(currentPage, perPage, searchQuery);

            $('#staff-attendance-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadStaffAttendance(currentPage, perPage, searchQuery);
            });

            $('#prev-page').on('click', function() { currentPage--; loadStaffAttendance(currentPage, perPage, searchQuery); });
            $('#next-page').on('click', function() { currentPage++; loadStaffAttendance(currentPage, perPage, searchQuery); });

            $(document).on('click', '.edit-staff-attendance', function() {
                const index = $(this).data('attendance-index');
                const record = staffAttendanceData[index];
                $('#edit-staff-attendance-index').val(index);
                $('#edit-staff-id').val(record.staff_id);
                $('#edit-staff-name').val(record.staff_name);
                $('#edit-staff-role').val(record.role);
                $('#edit-staff-center').val(record.education_center_id);
                $('#edit-staff-date').val(record.date);
                $('#edit-staff-status').val(record.status);
                $('#edit-staff-attendance-modal').show();
            });

            $('#update-staff-attendance').on('click', function() {
                const index = $('#edit-staff-attendance-index').val();
                const date = $('#edit-staff-date').val();
                const status = $('#edit-staff-status').val();
                if (date && status) {
                    staffAttendanceData[index].date = date;
                    staffAttendanceData[index].status = status;
                    $('#edit-staff-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                    setTimeout(() => {
                        $('#edit-staff-attendance-modal').hide();
                        $('#edit-staff-attendance-message').removeClass('edu-success').text('');
                        loadStaffAttendance(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#edit-staff-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-staff-attendance', function() {
                if (!confirm('Are you sure you want to delete this attendance record?')) return;
                const index = $(this).data('attendance-index');
                staffAttendanceData.splice(index, 1);
                loadStaffAttendance(currentPage, perPage, searchQuery);
            });

            $('#export-staff-attendance').on('click', function() {
                const csv = staffAttendanceData.map(row => `${row.staff_id},${row.staff_name},${row.role},${row.education_center_id},${row.date},${row.status}`).join('\n');
                const headers = 'Staff ID,Staff Name,Role,Center,Date,Status\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'staff_attendance.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('#import-staff-attendance-btn').on('click', function() {
                $('#import-staff-attendance').click();
            });

            $('#import-staff-attendance').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [staff_id, staff_name, role, education_center_id, date, status] = row.split(',');
                            return { staff_id, staff_name, role, education_center_id, date, status };
                        });
                        staffAttendanceData.push(...newRecords);
                        loadStaffAttendance(currentPage, perPage, searchQuery);
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                }
            });

            $('.edu-modal-close').on('click', function() { $('#edit-staff-attendance-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Add Staff Attendance
 */
function demoRenderSuperadminAddStaffAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Staff Attendance</h2>
        <div class="card p-4 bg-light">
            <form id="add-staff-attendance-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-id">Staff ID</label>
                    <input type="text" class="edu-form-input" id="staff-id" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-name">Staff Name</label>
                    <input type="text" class="edu-form-input" id="staff-name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-role">Role</label>
                    <input type="text" class="edu-form-input" id="staff-role" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-center">Center</label>
                    <input type="text" class="edu-form-input" id="staff-center" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-date">Date</label>
                    <input type="date" class="edu-form-input" id="staff-date" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="staff-status">Status</label>
                    <select class="edu-form-input" id="staff-status" required>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
                    </select>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="save-staff-attendance">Add Attendance</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'staff-attendance'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="add-staff-attendance-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#save-staff-attendance').on('click', function() {
                const record = {
                    staff_id: $('#staff-id').val(),
                    staff_name: $('#staff-name').val(),
                    role: $('#staff-role').val(),
                    education_center_id: $('#staff-center').val(),
                    date: $('#staff-date').val(),
                    status: $('#staff-status').val()
                };
                if (record.staff_id && record.staff_name && record.role && record.education_center_id && record.date && record.status) {
                    $('#add-staff-attendance-message').addClass('edu-success').text('Attendance added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'staff-attendance'])); ?>';
                    }, 1000);
                } else {
                    $('#add-staff-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Edit Staff Attendance
 */
function demoRenderSuperadminEditStaffAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Staff Attendance</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="staff-attendance-table">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Center</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="staff-attendance-table-body">
                    <?php foreach ($data['staff_attendance'] as $index => $record): ?>
                        <tr data-attendance-index="<?php echo esc_attr($index); ?>">
                            <td><?php echo esc_html($record['staff_id']); ?></td>
                            <td><?php echo esc_html($record['staff_name']); ?></td>
                            <td><?php echo esc_html($record['role']); ?></td>
                            <td><?php echo esc_html($record['education_center_id']); ?></td>
                            <td><?php echo esc_html($record['date']); ?></td>
                            <td><?php echo esc_html($record['status']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-staff-attendance" data-attendance-index="<?php echo esc_attr($index); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Staff Attendance Modal -->
        <div id="edit-staff-attendance-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-staff-attendance-modal">×</span>
                <h2>Edit Staff Attendance</h2>
                <form id="edit-staff-attendance-form" class="edu-form">
                    <input type="hidden" id="edit-staff-attendance-index">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-id">Staff ID</label>
                        <input type="text" class="edu-form-input" id="edit-staff-id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-name">Staff Name</label>
                        <input type="text" class="edu-form-input" id="edit-staff-name" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-role">Role</label>
                        <input type="text" class="edu-form-input" id="edit-staff-role" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-staff-center" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-date">Date</label>
                        <input type="date" class="edu-form-input" id="edit-staff-date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-staff-status">Status</label>
                        <select class="edu-form-input" id="edit-staff-status" required>
                            <option value="Present">Present</option>
                            <option value="Absent">Absent</option>
                            <option value="Late">Late</option>
                        </select>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-staff-attendance">Update Attendance</button>
                </form>
                <div class="edu-form-message" id="edit-staff-attendance-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let staffAttendanceData = <?php echo json_encode($data['staff_attendance']); ?>;

            $(document).on('click', '.edit-staff-attendance', function() {
                const index = $(this).data('attendance-index');
                const record = staffAttendanceData[index];
                $('#edit-staff-attendance-index').val(index);
                $('#edit-staff-id').val(record.staff_id);
                $('#edit-staff-name').val(record.staff_name);
                $('#edit-staff-role').val(record.role);
                $('#edit-staff-center').val(record.education_center_id);
                $('#edit-staff-date').val(record.date);
                $('#edit-staff-status').val(record.status);
                $('#edit-staff-attendance-modal').show();
            });

            $('#update-staff-attendance').on('click', function() {
                const index = $('#edit-staff-attendance-index').val();
                const date = $('#edit-staff-date').val();
                const status = $('#edit-staff-status').val();
                if (date && status) {
                    staffAttendanceData[index].date = date;
                    staffAttendanceData[index].status = status;
                    $('#edit-staff-attendance-message').addClass('edu-success').text('Attendance updated successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'staff-attendance'])); ?>';
                    }, 1000);
                } else {
                    $('#edit-staff-attendance-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $('.edu-modal-close').on('click', function() { $('#edit-staff-attendance-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Delete Staff Attendance
 */
function demoRenderSuperadminDeleteStaffAttendance($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Staff Attendance</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="staff-attendance-table">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Center</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="staff-attendance-table-body">
                    <?php foreach ($data['staff_attendance'] as $index => $record): ?>
                        <tr data-attendance-index="<?php echo esc_attr($index); ?>">
                            <td><?php echo esc_html($record['staff_id']); ?></td>
                            <td><?php echo esc_html($record['staff_name']); ?></td>
                            <td><?php echo esc_html($record['role']); ?></td>
                            <td><?php echo esc_html($record['education_center_id']); ?></td>
                            <td><?php echo esc_html($record['date']); ?></td>
                            <td><?php echo esc_html($record['status']); ?></td>
                            <td>
                                <button class="edu-button edu-button-delete delete-staff-attendance" data-attendance-index="<?php echo esc_attr($index); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let staffAttendanceData = <?php echo json_encode($data['staff_attendance']); ?>;
            $(document).on('click', '.delete-staff-attendance', function() {
                if (!confirm('Are you sure you want to delete this attendance record?')) return;
                const index = $(this).data('attendance-index');
                staffAttendanceData.splice(index, 1);
                window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'attendance', 'demo-action' => 'staff-attendance'])); ?>';
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Render Fees Section
 */

function demoRenderSuperadminFees($data = []) {
    ob_start();
    $centers = isset($data['centers']) ? $data['centers'] : [];
    $students = isset($data['students']) ? $data['students'] : [];
    $fees = isset($data['fees']) ? $data['fees'] : [
        ['fee_id' => 'F001', 'student_id' => 'S001', 'student_name' => 'John Doe', 'amount' => 500.00, 'due_date' => '2025-05-01', 'status' => 'Pending', 'center' => 'Main Campus'],
        ['fee_id' => 'F002', 'student_id' => 'S002', 'student_name' => 'Jane Smith', 'amount' => 600.00, 'due_date' => '2025-05-01', 'status' => 'Paid', 'center' => 'West Campus'],
    ];
    $months = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'];
    ?>
    <div class="attendance-main-wrapper">
        <div class="form-container attendance-content-wrapper">
            <div class="fees-management">
                <h2>Fees Management</h2>
                <div class="filters-card">
                    <div class="search-filters">
                        <label for="search-year">Year:</label>
                        <select id="search-year" class="filter-select">
                            <option value="">All Years</option>
                            <option value="2025">2025</option>
                        </select>
                        <label for="search-status">Status:</label>
                        <select id="search-status" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="Pending">Pending</option>
                            <option value="Paid">Paid</option>
                            <option value="Overdue">Overdue</option>
                        </select>
                        <button id="search-button" class="search-btn">Search</button>
                    </div>
                </div>
                <div class="fees-table-wrapper" id="fees-table-container">
                    <table id="fees-table" class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>View</th>
                                <th>Name</th>
                                <th>Student ID</th>
                                <th>Class</th>
                                <th>Paid (P)</th>
                                <th>Pending (L)</th>
                                <th>Overdue (A)</th>
                                <?php foreach ($months as $month_num => $month_name) : ?>
                                    <th><?php echo esc_html($month_name); ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody id="fees-table-body">
                            <?php if (!empty($fees)) : ?>
                                <?php foreach ($fees as $index => $fee) : ?>
                                    <?php
                                    $counts = ['Paid' => 0, 'Pending' => 0, 'Overdue' => 0];
                                    $monthly = array_fill_keys(array_keys($months), '');
                                    $month_num = (int)date('m', strtotime($fee['due_date']));
                                    switch ($fee['status']) {
                                        case 'Paid':
                                            $counts['Paid'] = 1;
                                            $monthly[$month_num] = 'P';
                                            break;
                                        case 'Pending':
                                            $counts['Pending'] = 1;
                                            $monthly[$month_num] = 'L';
                                            break;
                                        case 'Overdue':
                                            $counts['Overdue'] = 1;
                                            $monthly[$month_num] = 'A';
                                            break;
                                    }
                                    ?>
                                    <tr class="fee-row" data-fee-index="<?php echo esc_attr($index); ?>">
                                        <td>
                                            <button class="expand-details" data-fee-index="<?php echo esc_attr($index); ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg>
                                            </button>
                                        </td>
                                        <td><?php echo esc_html($fee['student_name']); ?></td>
                                        <td><?php echo esc_html($fee['student_id']); ?></td>
                                        <td><?php echo esc_html('Class - V'); ?></td>
                                        <td><?php echo esc_html($counts['Paid']); ?></td>
                                        <td><?php echo esc_html($counts['Pending']); ?></td>
                                        <td><?php echo esc_html($counts['Overdue']); ?></td>
                                        <?php foreach ($months as $month_num => $month_name) : ?>
                                            <td><?php echo esc_html($monthly[$month_num]); ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                    <tr class="fee-details" id="fee-details-<?php echo esc_attr($index); ?>" style="display: none;">
                                        <td colspan="<?php echo 7 + count($months); ?>">
                                            <div class="fee-details-content">
                                                <div class="fee-detail-header">
                                                    <h3>Fee Summary for <?php echo esc_html($fee['student_name']); ?></h3>
                                                    <div class="summary-stats">
                                                        <div class="stat-box">
                                                            <span class="stat-label">Total Amount:</span>
                                                            <span class="stat-value"><?php echo esc_html(number_format($fee['amount'], 2)); ?></span>
                                                        </div>
                                                        <div class="stat-box">
                                                            <span class="stat-label">Total Paid:</span>
                                                            <span class="stat-value"><?php echo esc_html($fee['status'] === 'Paid' ? number_format($fee['amount'], 2) : '0.00'); ?></span>
                                                        </div>
                                                        <div class="stat-box pending-months-box">
                                                            <span class="stat-label">Pending Months:</span>
                                                            <span class="stat-value">
                                                                <?php echo esc_html($fee['status'] === 'Pending' ? 1 : 0); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="toggle-details-section">
                                                    <button class="toggle-details-btn" data-fee-index="<?php echo esc_attr($index); ?>">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg>
                                                    </button>View Details
                                                </div>
                                                <div class="fee-detail-entries" id="fee-entries-<?php echo esc_attr($index); ?>" style="display: none;">
                                                    <div class="fee-detail-entry">
                                                        <div class="entry-grid">
                                                            <div class="entry-item">
                                                                <span class="entry-label">Month:</span>
                                                                <span class="entry-value"><?php echo esc_html(date('F', strtotime($fee['due_date']))); ?></span>
                                                            </div>
                                                            <div class="entry-item">
                                                                <span class="entry-label">Fee Type:</span>
                                                                <span class="entry-value">Tuition</span>
                                                            </div>
                                                            <div class="entry-item">
                                                                <span class="entry-label">Amount:</span>
                                                                <span class="entry-value"><?php echo esc_html(number_format($fee['amount'], 2)); ?></span>
                                                            </div>
                                                            <div class="entry-item">
                                                                <span class="entry-label">Status:</span>
                                                                <span class="entry-value <?php echo esc_attr(strtolower($fee['status'])); ?>">
                                                                    <?php echo esc_html($fee['status']); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="<?php echo 7 + count($months); ?>">No fees found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="fee-details-modal" class="modal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <div id="modal-content-inner"></div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        let feesData = <?php echo json_encode($fees); ?>;

        function loadFeesData() {
            let year = $('#search-year').val();
            let status = $('#search-status').val();
            let filtered = feesData.filter(f => {
                let dueDate = new Date(f.due_date);
                return (!year || dueDate.getFullYear() == year) &&
                       (!status || f.status === status);
            });
            let html = '';
            if (filtered.length > 0) {
                filtered.forEach((fee, index) => {
                    let counts = {'Paid': 0, 'Pending': 0, 'Overdue': 0};
                    let monthly = {'1': '', '2': '', '3': '', '4': '', '5': '', '6': '', '7': '', '8': '', '9': '', '10': '', '11': '', '12': ''};
                    let month_num = (new Date(fee.due_date)).getMonth() + 1;
                    switch (fee.status) {
                        case 'Paid': counts['Paid'] = 1; monthly[month_num] = 'P'; break;
                        case 'Pending': counts['Pending'] = 1; monthly[month_num] = 'L'; break;
                        case 'Overdue': counts['Overdue'] = 1; monthly[month_num] = 'A'; break;
                    }
                    html += `
                        <tr class="fee-row" data-fee-index="${index}">
                            <td><button class="expand-details" data-fee-index="${index}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/></svg></button></td>
                            <td>${fee.student_name}</td>
                            <td>${fee.student_id}</td>
                            <td>Class - V</td>
                            <td>${counts['Paid']}</td>
                            <td>${counts['Pending']}</td>
                            <td>${counts['Overdue']}</td>
                            <td>${monthly['1']}</td><td>${monthly['2']}</td><td>${monthly['3']}</td><td>${monthly['4']}</td><td>${monthly['5']}</td>
                            <td>${monthly['6']}</td><td>${monthly['7']}</td><td>${monthly['8']}</td><td>${monthly['9']}</td><td>${monthly['10']}</td>
                            <td>${monthly['11']}</td><td>${monthly['12']}</td>
                        </tr>
                        <tr class="fee-details" id="fee-details-${index}" style="display: none;">
                            <td colspan="19">
                                <div class="fee-details-content">
                                    <div class="fee-detail-header">
                                        <h3>Fee Summary for ${fee.student_name}</h3>
                                        <div class="summary-stats">
                                            <div class="stat-box"><span class="stat-label">Total Amount:</span><span class="stat-value">${number_format(fee.amount, 2)}</span></div>
                                            <div class="stat-box"><span class="stat-label">Total Paid:</span><span class="stat-value">${fee.status === 'Paid' ? number_format(fee.amount, 2) : '0.00'}</span></div>
                                            <div class="stat-box pending-months-box"><span class="stat-label">Pending Months:</span><span class="stat-value">${fee.status === 'Pending' ? 1 : 0}</span></div>
                                        </div>
                                    </div>
                                    <div class="toggle-details-section"><button class="toggle-details-btn" data-fee-index="${index}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32V224H48c-17.7 0-32 14.3-32 32s14.3 32 32 32H192V432c0 17.7 14.3 32 32 32s32-14.3 32-32V288H400c17.7 0 32-14.3 32-32s-14.3-32-32-32H256V80z"/></svg></button>View Details</div>
                                    <div class="fee-detail-entries" id="fee-entries-${index}" style="display: none;">
                                        <div class="fee-detail-entry">
                                            <div class="entry-grid">
                                                <div class="entry-item"><span class="entry-label">Month:</span><span class="entry-value">${new Date(fee.due_date).toLocaleString('default', { month: 'long' })}</span></div>
                                                <div class="entry-item"><span class="entry-label">Fee Type:</span><span class="entry-value">Tuition</span></div>
                                                <div class="entry-item"><span class="entry-label">Amount:</span><span class="entry-value">${number_format(fee.amount, 2)}</span></div>
                                                <div class="entry-item"><span class="entry-label">Status:</span><span class="entry-value ${fee.status.toLowerCase()}">${fee.status}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                });
            } else {
                html = '<tr><td colspan="19">No fees found</td></tr>';
            }
            $('#fees-table-body').html(html);
            setupTableEvents();
        }

        function setupTableEvents() {
            $('.expand-details').off('click').on('click', function() {
                var index = $(this).data('fee-index');
                var $detailsRow = $('#fee-details-' + index);
                var $modal = $('#fee-details-modal');
                var $modalContent = $('#modal-content-inner');
                $modalContent.html($detailsRow.find('.fee-details-content').clone());
                $modal.css('display', 'block');
                $detailsRow.css('display', 'none');
            });

            $('.modal-close').on('click', function() {
                $('#fee-details-modal').css('display', 'none');
                $('#modal-content-inner').empty();
            });

            $(window).on('click', function(event) {
                if (event.target == $('#fee-details-modal')[0]) {
                    $('#fee-details-modal').css('display', 'none');
                    $('#modal-content-inner').empty();
                }
            });

            $('.toggle-details-btn').off('click').on('click', function() {
                var index = $(this).data('fee-index');
                var $feeEntries = $('#fee-entries-' + index);
                $(this).toggleClass('active');
                $feeEntries.slideToggle(300);
            });
        }

        $('#search-button').on('click', function(e) {
            e.preventDefault();
            loadFeesData();
        });

        $('#search-year, #search-status').on('change', function() {
            loadFeesData();
        });

        loadFeesData();
    });
    </script>
    <?php
    return ob_get_clean();
}
/**
 * Add Fee
 */
function demoRenderSuperadminAddFee($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Fee</h2>
        <div class="card p-4 bg-light">
            <form id="add-fee-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="fee-id">Fee ID</label>
                    <input type="text" class="edu-form-input" id="fee-id" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-id">Student ID</label>
                    <input type="text" class="edu-form-input" id="student-id" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="student-name">Student Name</label>
                    <input type="text" class="edu-form-input" id="student-name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="amount">Amount</label>
                    <input type="number" step="0.01" class="edu-form-input" id="amount" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="due-date">Due Date</label>
                    <input type="date" class="edu-form-input" id="due-date" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="status">Status</label>
                    <select class="edu-form-input" id="status" required>
                        <option value="Pending">Pending</option>
                        <option value="Paid">Paid</option>
                        <option value="Overdue">Overdue</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="center">Center</label>
                    <input type="text" class="edu-form-input" id="center" required>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="save-fee">Add Fee</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fees'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="add-fee-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#save-fee').on('click', function() {
                const record = {
                    fee_id: $('#fee-id').val(),
                    student_id: $('#student-id').val(),
                    student_name: $('#student-name').val(),
                    amount: parseFloat($('#amount').val()),
                    due_date: $('#due-date').val(),
                    status: $('#status').val(),
                    center: $('#center').val()
                };
                if (record.fee_id && record.student_id && record.student_name && record.amount && record.due_date && record.status && record.center) {
                    $('#add-fee-message').addClass('edu-success').text('Fee added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fees'])); ?>';
                    }, 1000);
                } else {
                    $('#add-fee-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Edit Fee
 */
function demoRenderSuperadminEditFee($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Fee</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="fees-table">
                <thead>
                    <tr>
                        <th>Fee ID</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fees-table-body">
                    <?php foreach ($data['fees'] as $index => $record): ?>
                        <tr data-fee-index="<?php echo esc_attr($index); ?>">
                            <td><?php echo esc_html($record['fee_id']); ?></td>
                            <td><?php echo esc_html($record['student_id']); ?></td>
                            <td><?php echo esc_html($record['student_name']); ?></td>
                            <td><?php echo esc_html(number_format($record['amount'], 2)); ?></td>
                            <td><?php echo esc_html($record['due_date']); ?></td>
                            <td><?php echo esc_html($record['status']); ?></td>
                            <td><?php echo esc_html($record['center']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-fee" data-fee-index="<?php echo esc_attr($index); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Fee Modal -->
        <div id="edit-fee-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-fee-modal">×</span>
                <h2>Edit Fee</h2>
                <form id="edit-fee-form" class="edu-form">
                    <input type="hidden" id="edit-fee-index">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-fee-id">Fee ID</label>
                        <input type="text" class="edu-form-input" id="edit-fee-id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-id">Student ID</label>
                        <input type="text" class="edu-form-input" id="edit-student-id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-student-name">Student Name</label>
                        <input type="text" class="edu-form-input" id="edit-student-name" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-amount">Amount</label>
                        <input type="number" step="0.01" class="edu-form-input" id="edit-amount" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-due-date">Due Date</label>
                        <input type="date" class="edu-form-input" id="edit-due-date" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-status">Status</label>
                        <select class="edu-form-input" id="edit-status" required>
                            <option value="Pending">Pending</option>
                            <option value="Paid">Paid</option>
                            <option value="Overdue">Overdue</option>
                        </select>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-center" readonly>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-fee">Update Fee</button>
                </form>
                <div class="edu-form-message" id="edit-fee-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let feesData = <?php echo json_encode($data['fees']); ?>;

            $(document).on('click', '.edit-fee', function() {
                const index = $(this).data('fee-index');
                const record = feesData[index];
                $('#edit-fee-index').val(index);
                $('#edit-fee-id').val(record.fee_id);
                $('#edit-student-id').val(record.student_id);
                $('#edit-student-name').val(record.student_name);
                $('#edit-amount').val(record.amount);
                $('#edit-due-date').val(record.due_date);
                $('#edit-status').val(record.status);
                $('#edit-center').val(record.center);
                $('#edit-fee-modal').show();
            });

            $('#update-fee').on('click', function() {
                const index = $('#edit-fee-index').val();
                const amount = parseFloat($('#edit-amount').val());
                const due_date = $('#edit-due-date').val();
                const status = $('#edit-status').val();
                if (amount && due_date && status) {
                    feesData[index].amount = amount;
                    feesData[index].due_date = due_date;
                    feesData[index].status = status;
                    $('#edit-fee-message').addClass('edu-success').text('Fee updated successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fees'])); ?>';
                    }, 1000);
                } else {
                    $('#edit-fee-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $('.edu-modal-close').on('click', function() { $('#edit-fee-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Delete Fee
 */
function demoRenderSuperadminDeleteFee($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Fee</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="fees-table">
                <thead>
                    <tr>
                        <th>Fee ID</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fees-table-body">
                    <?php foreach ($data['fees'] as $index => $record): ?>
                        <tr data-fee-index="<?php echo esc_attr($index); ?>">
                            <td><?php echo esc_html($record['fee_id']); ?></td>
                            <td><?php echo esc_html($record['student_id']); ?></td>
                            <td><?php echo esc_html($record['student_name']); ?></td>
                            <td><?php echo esc_html(number_format($record['amount'], 2)); ?></td>
                            <td><?php echo esc_html($record['due_date']); ?></td>
                            <td><?php echo esc_html($record['status']); ?></td>
                            <td><?php echo esc_html($record['center']); ?></td>
                            <td>
                                <button class="edu-button edu-button-delete delete-fee" data-fee-index="<?php echo esc_attr($index); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let feesData = <?php echo json_encode($data['fees']); ?>;
            $(document).on('click', '.delete-fee', function() {
                if (!confirm('Are you sure you want to delete this fee record?')) return;
                const index = $(this).data('fee-index');
                feesData.splice(index, 1);
                window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fees'])); ?>';
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Render Fee Templates Section
 */
function demoRenderSuperadminFeeTemplates($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Fee Templates</h2>
        <div class="edu-actions">
            <input type="text" id="fee-templates-search" class="edu-search-input" placeholder="Search Fee Templates...">
            <button class="edu-button edu-button-primary" onclick="window.location.href='<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'add-fee-template'])); ?>'">Add Template</button>
            <button class="edu-button edu-button-secondary" id="export-fee-templates">Export CSV</button>
            <input type="file" id="import-fee-templates" accept=".csv" style="display: none;">
            <button class="edu-button edu-button-secondary" id="import-fee-templates-btn">Import CSV</button>
        </div>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="fee-templates-table">
                <thead>
                    <tr>
                        <th>Template ID</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Frequency</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fee-templates-table-body">
                    <!-- Populated via JS -->
                </tbody>
            </table>
        </div>
        <div class="edu-pagination">
            <button class="edu-button edu-button-nav" id="prev-page" disabled>Previous</button>
            <span id="page-info"></span>
            <button class="edu-button edu-button-nav" id="next-page">Next</button>
        </div>

        <!-- Edit Fee Template Modal -->
        <div id="edit-fee-template-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-fee-template-modal">×</span>
                <h2>Edit Fee Template</h2>
                <form id="edit-fee-template-form" class="edu-form">
                    <input type="hidden" id="edit-fee-template-index">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-template-id">Template ID</label>
                        <input type="text" class="edu-form-input" id="edit-template-id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-template-name">Name</label>
                        <input type="text" class="edu-form-input" id="edit-template-name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-template-amount">Amount</label>
                        <input type="number" step="0.01" class="edu-form-input" id="edit-template-amount" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-frequency">Frequency</label>
                        <select class="edu-form-input" id="edit-frequency" required>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-template-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-template-center" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-fee-template">Update Template</button>
                </form>
                <div class="edu-form-message" id="edit-fee-template-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let feeTemplatesData = <?php echo json_encode($data['fee_templates']); ?>;
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';

            function loadFeeTemplates(page, limit, query) {
                const filtered = feeTemplatesData.filter(t => 
                    !query || t.name.toLowerCase().includes(query.toLowerCase()) || t.template_id.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach((record, index) => {
                    html += `
                        <tr data-template-index="${start + index}">
                            <td>${record.template_id}</td>
                            <td>${record.name}</td>
                            <td>${record.amount.toFixed(2)}</td>
                            <td>${record.frequency}</td>
                            <td>${record.center}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-fee-template" data-template-index="${start + index}">Edit</button>
                                <button class="edu-button edu-button-delete delete-fee-template" data-template-index="${start + index}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#fee-templates-table-body').html(html || '<tr><td colspan="6">No fee templates found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            loadFeeTemplates(currentPage, perPage, searchQuery);

            $('#fee-templates-search').on('input', function() {
                searchQuery = $(this).val();
                currentPage = 1;
                loadFeeTemplates(currentPage, perPage, searchQuery);
            });

            $('#prev-page').on('click', function() { currentPage--; loadFeeTemplates(currentPage, perPage, searchQuery); });
            $('#next-page').on('click', function() { currentPage++; loadFeeTemplates(currentPage, perPage, searchQuery); });

            $(document).on('click', '.edit-fee-template', function() {
                const index = $(this).data('template-index');
                const record = feeTemplatesData[index];
                $('#edit-fee-template-index').val(index);
                $('#edit-template-id').val(record.template_id);
                $('#edit-template-name').val(record.name);
                $('#edit-template-amount').val(record.amount);
                $('#edit-frequency').val(record.frequency);
                $('#edit-template-center').val(record.center);
                $('#edit-fee-template-modal').show();
            });

            $('#update-fee-template').on('click', function() {
                const index = $('#edit-fee-template-index').val();
                const name = $('#edit-template-name').val();
                const amount = parseFloat($('#edit-template-amount').val());
                const frequency = $('#edit-frequency').val();
                const center = $('#edit-template-center').val();
                if (name && amount && frequency && center) {
                    feeTemplatesData[index].name = name;
                    feeTemplatesData[index].amount = amount;
                    feeTemplatesData[index].frequency = frequency;
                    feeTemplatesData[index].center = center;
                    $('#edit-fee-template-message').addClass('edu-success').text('Fee template updated successfully!');
                    setTimeout(() => {
                        $('#edit-fee-template-modal').hide();
                        $('#edit-fee-template-message').removeClass('edu-success').text('');
                        loadFeeTemplates(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#edit-fee-template-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $(document).on('click', '.delete-fee-template', function() {
                if (!confirm('Are you sure you want to delete this fee template?')) return;
                const index = $(this).data('template-index');
                feeTemplatesData.splice(index, 1);
                loadFeeTemplates(currentPage, perPage, searchQuery);
            });

            $('#export-fee-templates').on('click', function() {
                const csv = feeTemplatesData.map(row => `${row.template_id},${row.name},${row.amount},${row.frequency},${row.center}`).join('\n');
                const headers = 'Template ID,Name,Amount,Frequency,Center\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'fee_templates.csv';
                a.click();
                window.URL.revokeObjectURL(url);
            });

            $('#import-fee-templates-btn').on('click', function() {
                $('#import-fee-templates').click();
            });

            $('#import-fee-templates').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [template_id, name, amount, frequency, center] = row.split(',');
                            return { template_id, name, amount: parseFloat(amount), frequency, center };
                        });
                        feeTemplatesData.push(...newRecords);
                        loadFeeTemplates(currentPage, perPage, searchQuery);
                    };
                    reader.readAsText(file);
                    e.target.value = '';
                }
            });

            $('.edu-modal-close').on('click', function() { $('#edit-fee-template-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Add Fee Template
 */
function demoRenderSuperadminAddFeeTemplate($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Fee Template</h2>
        <div class="card p-4 bg-light">
            <form id="add-fee-template-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="template-id">Template ID</label>
                    <input type="text" class="edu-form-input" id="template-id" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="template-name">Name</label>
                    <input type="text" class="edu-form-input" id="template-name" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="template-amount">Amount</label>
                    <input type="number" step="0.01" class="edu-form-input" id="template-amount" required>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="frequency">Frequency</label>
                    <select class="edu-form-input" id="frequency" required>
                        <option value="Monthly">Monthly</option>
                        <option value="Quarterly">Quarterly</option>
                        <option value="Yearly">Yearly</option>
                    </select>
                </div>
                <div class="edu-form-group">
                    <label class="edu-form-label" for="template-center">Center</label>
                    <input type="text" class="edu-form-input" id="template-center" required>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="save-fee-template">Add Template</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="add-fee-template-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            $('#save-fee-template').on('click', function() {
                const record = {
                    template_id: $('#template-id').val(),
                    name: $('#template-name').val(),
                    amount: parseFloat($('#template-amount').val()),
                    frequency: $('#frequency').val(),
                    center: $('#template-center').val()
                };
                if (record.template_id && record.name && record.amount && record.frequency && record.center) {
                    $('#add-fee-template-message').addClass('edu-success').text('Fee template added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])); ?>';
                    }, 1000);
                } else {
                    $('#add-fee-template-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Edit Fee Template
 */
function demoRenderSuperadminEditFeeTemplate($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Fee Template</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="fee-templates-table">
                <thead>
                    <tr>
                        <th>Template ID</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Frequency</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fee-templates-table-body">
                    <?php foreach ($data['fee_templates'] as $index => $record): ?>
                        <tr data-template-index="<?php echo esc_attr($index); ?>">
                            <td><?php echo esc_html($record['template_id']); ?></td>
                            <td><?php echo esc_html($record['name']); ?></td>
                            <td><?php echo esc_html(number_format($record['amount'], 2)); ?></td>
                            <td><?php echo esc_html($record['frequency']); ?></td>
                            <td><?php echo esc_html($record['center']); ?></td>
                            <td>
                                <button class="edu-button edu-button-edit edit-fee-template" data-template-index="<?php echo esc_attr($index); ?>">Edit</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Fee Template Modal -->
        <div id="edit-fee-template-modal" class="edu-modal" style="display: none;">
            <div class="edu-modal-content">
                <span class="edu-modal-close" data-modal="edit-fee-template-modal">×</span>
                <h2>Edit Fee Template</h2>
                <form id="edit-fee-template-form" class="edu-form">
                    <input type="hidden" id="edit-fee-template-index">
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-template-id">Template ID</label>
                        <input type="text" class="edu-form-input" id="edit-template-id" readonly>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-template-name">Name</label>
                        <input type="text" class="edu-form-input" id="edit-template-name" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-template-amount">Amount</label>
                        <input type="number" step="0.01" class="edu-form-input" id="edit-template-amount" required>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-frequency">Frequency</label>
                        <select class="edu-form-input" id="edit-frequency" required>
                            <option value="Monthly">Monthly</option>
                            <option value="Quarterly">Quarterly</option>
                            <option value="Yearly">Yearly</option>
                        </select>
                    </div>
                    <div class="edu-form-group">
                        <label class="edu-form-label" for="edit-template-center">Center</label>
                        <input type="text" class="edu-form-input" id="edit-template-center" required>
                    </div>
                    <button type="button" class="edu-button edu-button-primary" id="update-fee-template">Update Template</button>
                </form>
                <div class="edu-form-message" id="edit-fee-template-message"></div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            let feeTemplatesData = <?php echo json_encode($data['fee_templates']); ?>;

            $(document).on('click', '.edit-fee-template', function() {
                const index = $(this).data('template-index');
                const record = feeTemplatesData[index];
                $('#edit-fee-template-index').val(index);
                $('#edit-template-id').val(record.template_id);
                $('#edit-template-name').val(record.name);
                $('#edit-template-amount').val(record.amount);
                $('#edit-frequency').val(record.frequency);
                $('#edit-template-center').val(record.center);
                $('#edit-fee-template-modal').show();
            });

            $('#update-fee-template').on('click', function() {
                const index = $('#edit-fee-template-index').val();
                const name = $('#edit-template-name').val();
                const amount = parseFloat($('#edit-template-amount').val());
                const frequency = $('#edit-frequency').val();
                const center = $('#edit-template-center').val();
                if (name && amount && frequency && center) {
                    feeTemplatesData[index].name = name;
                    feeTemplatesData[index].amount = amount;
                    feeTemplatesData[index].frequency = frequency;
                    feeTemplatesData[index].center = center;
                    $('#edit-fee-template-message').addClass('edu-success').text('Fee template updated successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])); ?>';
                    }, 1000);
                } else {
                    $('#edit-fee-template-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            $('.edu-modal-close').on('click', function() { $('#edit-fee-template-modal').hide(); });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Delete Fee Template
 */
function demoRenderSuperadminDeleteFeeTemplate($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Fee Template</h2>
        <div class="edu-table-wrapper">
            <table class="edu-table" id="fee-templates-table">
                <thead>
                    <tr>
                        <th>Template ID</th>
                        <th>Name</th>
                        <th>Amount</th>
                        <th>Frequency</th>
                        <th>Center</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fee-templates-table-body">
                    <?php foreach ($data['fee_templates'] as $index => $record): ?>
                        <tr data-template-index="<?php echo esc_attr($index); ?>">
                            <td><?php echo esc_html($record['template_id']); ?></td>
                            <td><?php echo esc_html($record['name']); ?></td>
                            <td><?php echo esc_html(number_format($record['amount'], 2)); ?></td>
                            <td><?php echo esc_html($record['frequency']); ?></td>
                            <td><?php echo esc_html($record['center']); ?></td>
                            <td>
                                <button class="edu-button edu-button-delete delete-fee-template" data-template-index="<?php echo esc_attr($index); ?>">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let feeTemplatesData = <?php echo json_encode($data['fee_templates']); ?>;
            $(document).on('click', '.delete-fee-template', function() {
                if (!confirm('Are you sure you want to delete this fee template?')) return;
                const index = $(this).data('template-index');
                feeTemplatesData.splice(index, 1);
                window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])); ?>';
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
/**
 * Import Fee Templates
 */
function demoRenderSuperadminImportFeeTemplates($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Import Fee Templates</h2>
        <div class="card p-4 bg-light">
            <form id="import-fee-templates-form" class="edu-form">
                <div class="edu-form-group">
                    <label class="edu-form-label" for="import-fee-templates-file">Upload CSV File</label>
                    <input type="file" class="edu-form-input" id="import-fee-templates-file" accept=".csv" required>
                </div>
                <button type="button" class="edu-button edu-button-primary" id="import-fee-templates-submit">Import</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </form>
            <div class="edu-form-message" id="import-fee-templates-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let feeTemplatesData = <?php echo json_encode($data['fee_templates']); ?>;
            $('#import-fee-templates-submit').on('click', function() {
                const file = $('#import-fee-templates-file').prop('files')[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const text = e.target.result;
                        const rows = text.split('\n').slice(1).filter(row => row.trim());
                        const newRecords = rows.map(row => {
                            const [template_id, name, amount, frequency, center] = row.split(',');
                            return { template_id, name, amount: parseFloat(amount), frequency, center };
                        });
                        feeTemplatesData.push(...newRecords);
                        $('#import-fee-templates-message').addClass('edu-success').text('Fee templates imported successfully!');
                        setTimeout(() => {
                            window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])); ?>';
                        }, 1000);
                    };
                    reader.readAsText(file);
                } else {
                    $('#import-fee-templates-message').addClass('edu-error').text('Please upload a CSV file.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Export Fee Templates
 */
function demoRenderSuperadminExportFeeTemplates($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Export Fee Templates</h2>
        <div class="card p-4 bg-light">
            <p>Click the button below to export fee templates as a CSV file.</p>
            <button class="edu-button edu-button-primary" id="export-fee-templates-submit">Export Fee Templates</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            <div class="edu-form-message" id="export-fee-templates-message"></div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            let feeTemplatesData = <?php echo json_encode($data['fee_templates']); ?>;
            $('#export-fee-templates-submit').on('click', function() {
                const csv = feeTemplatesData.map(row => `${row.template_id},${row.name},${row.amount},${row.frequency},${row.center}`).join('\n');
                const headers = 'Template ID,Name,Amount,Frequency,Center\n';
                const blob = new Blob([headers + csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'fee_templates.csv';
                a.click();
                window.URL.revokeObjectURL(url);
                $('#export-fee-templates-message').addClass('edu-success').text('Fee templates exported successfully!');
                setTimeout(() => {
                    window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'fees', 'demo-action' => 'fee-templates'])); ?>';
                }, 1000);
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminClasses() {
    // Hardcoded data for classes
    $classes = [
        ['class_id' => 'CL001', 'name' => 'Class 10', 'section' => 'A', 'center' => 'Main Campus'],
        ['class_id' => 'CL002', 'name' => 'Class 10', 'section' => 'B', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Classes Management</h2>
        <div class="alert alert-info">Manage classes below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="classes-search" placeholder="Search Classes...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'add-class'])); ?>" class="btn btn-primary">Add Class</a>
            </div>
        </div>
        <table class="table" id="superadmin-classes">
            <thead>
                <tr>
                    <th>Class ID</th>
                    <th>Name</th>
                    <th>Section</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?php echo esc_html($class['class_id']); ?></td>
                        <td><?php echo esc_html($class['name']); ?></td>
                        <td><?php echo esc_html($class['section']); ?></td>
                        <td><?php echo esc_html($class['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'edit-class'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'delete-class'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddClass($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Class</h2>
        <form id="add-class-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="class-id">Class ID</label>
                <input type="text" id="class-id" class="edu-form-input" placeholder="e.g., CL001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="class-name">Class Name</label>
                <input type="text" id="class-name" class="edu-form-input" placeholder="e.g., Class 10" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="section">Section</label>
                <input type="text" id="section" class="edu-form-input" placeholder="e.g., A" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($data['centers'] as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Class</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'manage-classes'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-class-form').on('submit', function(e) {
                e.preventDefault();
                const classId = $('#class-id').val().trim();
                const className = $('#class-name').val().trim();
                const section = $('#section').val().trim();
                const center = $('#center').val();
                if (classId && className && section && center) {
                    // Simulate adding class
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Class added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'manage-classes'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminSubjects() {
    // Hardcoded data for subjects
    $subjects = [
        ['subject_id' => 'SUB001', 'name' => 'Mathematics', 'class' => 'Class 10', 'center' => 'Main Campus'],
        ['subject_id' => 'SUB002', 'name' => 'Science', 'class' => 'Class 10', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Subjects Management</h2>
        <div class="alert alert-info">Manage subjects below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="subjects-search" placeholder="Search Subjects...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'add-subject'])); ?>" class="btn btn-primary">Add Subject</a>
            </div>
        </div>
        <table class="table" id="superadmin-subjects">
            <thead>
                <tr>
                    <th>Subject ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?php echo esc_html($subject['subject_id']); ?></td>
                        <td><?php echo esc_html($subject['name']); ?></td>
                        <td><?php echo esc_html($subject['class']); ?></td>
                        <td><?php echo esc_html($subject['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'edit-subject'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'delete-subject'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddSubject($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Subject</h2>
        <form id="add-subject-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="subject-id">Subject ID</label>
                <input type="text" id="subject-id" class="edu-form-input" placeholder="e.g., SUB001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="subject-name">Subject Name</label>
                <input type="text" id="subject-name" class="edu-form-input" placeholder="e.g., Mathematics" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="class">Class</label>
                <select id="class" class="edu-form-input" required>
                    <?php foreach ($data['classes'] as $class): ?>
                        <option value="<?php echo esc_attr($class['name']); ?>"><?php echo esc_html($class['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($data['centers'] as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Subject</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'manage-subjects'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-subject-form').on('submit', function(e) {
                e.preventDefault();
                const subjectId = $('#subject-id').val().trim();
                const subjectName = $('#subject-name').val().trim();
                const className = $('#class').val();
                const center = $('#center').val();
                if (subjectId && subjectName && className && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Subject added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'manage-subjects'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminHomeworks() {
    // Hardcoded data for homeworks
    $homeworks = [
        ['homework_id' => 'HW001', 'title' => 'Algebra Practice', 'subject' => 'Mathematics', 'class' => 'Class 10', 'due_date' => '2025-04-20', 'center' => 'Main Campus'],
        ['homework_id' => 'HW002', 'title' => 'Physics Lab Report', 'subject' => 'Science', 'class' => 'Class 10', 'due_date' => '2025-04-22', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Homeworks Management</h2>
        <div class="alert alert-info">Manage homeworks below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="homeworks-search" placeholder="Search Homeworks...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'add-homework'])); ?>" class="btn btn-primary">Add Homework</a>
            </div>
        </div>
        <table class="table" id="superadmin-homeworks">
            <thead>
                <tr>
                    <th>Homework ID</th>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Due Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($homeworks as $homework): ?>
                    <tr>
                        <td><?php echo esc_html($homework['homework_id']); ?></td>
                        <td><?php echo esc_html($homework['title']); ?></td>
                        <td><?php echo esc_html($homework['subject']); ?></td>
                        <td><?php echo esc_html($homework['class']); ?></td>
                        <td><?php echo esc_html($homework['due_date']); ?></td>
                        <td><?php echo esc_html($homework['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'edit-homework'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'delete-homework'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddHomework($data = []) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Homework</h2>
        <form id="add-homework-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="homework-id">Homework ID</label>
                <input type="text" id="homework-id" class="edu-form-input" placeholder="e.g., HW001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="homework-title">Title</label>
                <input type="text" id="homework-title" class="edu-form-input" placeholder="e.g., Algebra Practice" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="subject">Subject</label>
                <select id="subject" class="edu-form-input" required>
                    <?php foreach ($data['subjects'] as $subject): ?>
                        <option value="<?php echo esc_attr($subject['name']); ?>"><?php echo esc_html($subject['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="class">Class</label>
                <select id="class" class="edu-form-input" required>
                    <?php foreach ($data['classes'] as $class): ?>
                        <option value="<?php echo esc_attr($class['name']); ?>"><?php echo esc_html($class['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="due-date">Due Date</label>
                <input type="date" id="due-date" class="edu-form-input" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($data['centers'] as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Homework</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'manage-homeworks'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-homework-form').on('submit', function(e) {
                e.preventDefault();
                const homeworkId = $('#homework-id').val().trim();
                const title = $('#homework-title').val().trim();
                const subject = $('#subject').val();
                const className = $('#class').val();
                const dueDate = $('#due-date').val();
                const center = $('#center').val();
                if (homeworkId && title && subject && className && dueDate && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Homework added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'manage-homeworks'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditClass() {
    // Hardcoded data for classes
    $classes = [
        ['class_id' => 'CL001', 'name' => 'Class 10', 'section' => 'A', 'center' => 'Main Campus'],
        ['class_id' => 'CL002', 'name' => 'Class 10', 'section' => 'B', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Class</h2>
        <div class="alert alert-info">Select a class to edit from the list below.</div>
        <table class="table" id="superadmin-classes">
            <thead>
                <tr>
                    <th>Class ID</th>
                    <th>Name</th>
                    <th>Section</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?php echo esc_html($class['class_id']); ?></td>
                        <td><?php echo esc_html($class['name']); ?></td>
                        <td><?php echo esc_html($class['section']); ?></td>
                        <td><?php echo esc_html($class['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'manage-classes'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'delete-class'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteClass() {
    // Hardcoded data for classes
    $classes = [
        ['class_id' => 'CL001', 'name' => 'Class 10', 'section' => 'A', 'center' => 'Main Campus'],
        ['class_id' => 'CL002', 'name' => 'Class 10', 'section' => 'B', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Class</h2>
        <div class="alert alert-warning">Click "Delete" to remove a class.</div>
        <table class="table" id="superadmin-classes">
            <thead>
                <tr>
                    <th>Class ID</th>
                    <th>Name</th>
                    <th>Section</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $class): ?>
                    <tr>
                        <td><?php echo esc_html($class['class_id']); ?></td>
                        <td><?php echo esc_html($class['name']); ?></td>
                        <td><?php echo esc_html($class['section']); ?></td>
                        <td><?php echo esc_html($class['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'classes', 'demo-action' => 'manage-classes'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditSubject() {
    // Hardcoded data for subjects
    $subjects = [
        ['subject_id' => 'SUB001', 'name' => 'Mathematics', 'class' => 'Class 10', 'center' => 'Main Campus'],
        ['subject_id' => 'SUB002', 'name' => 'Science', 'class' => 'Class 10', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Subject</h2>
        <div class="alert alert-info">Select a subject to edit from the list below.</div>
        <table class="table" id="superadmin-subjects">
            <thead>
                <tr>
                    <th>Subject ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?php echo esc_html($subject['subject_id']); ?></td>
                        <td><?php echo esc_html($subject['name']); ?></td>
                        <td><?php echo esc_html($subject['class']); ?></td>
                        <td><?php echo esc_html($subject['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'manage-subjects'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'delete-subject'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteSubject() {
    // Hardcoded data for subjects
    $subjects = [
        ['subject_id' => 'SUB001', 'name' => 'Mathematics', 'class' => 'Class 10', 'center' => 'Main Campus'],
        ['subject_id' => 'SUB002', 'name' => 'Science', 'class' => 'Class 10', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Subject</h2>
        <div class="alert alert-warning">Click "Delete" to remove a subject.</div>
        <table class="table" id="superadmin-subjects">
            <thead>
                <tr>
                    <th>Subject ID</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subjects as $subject): ?>
                    <tr>
                        <td><?php echo esc_html($subject['subject_id']); ?></td>
                        <td><?php echo esc_html($subject['name']); ?></td>
                        <td><?php echo esc_html($subject['class']); ?></td>
                        <td><?php echo esc_html($subject['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subjects', 'demo-action' => 'manage-subjects'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditHomework() {
    // Hardcoded data for homeworks
    $homeworks = [
        ['homework_id' => 'HW001', 'title' => 'Algebra Practice', 'subject' => 'Mathematics', 'class' => 'Class 10', 'due_date' => '2025-04-20', 'center' => 'Main Campus'],
        ['homework_id' => 'HW002', 'title' => 'Physics Lab Report', 'subject' => 'Science', 'class' => 'Class 10', 'due_date' => '2025-04-22', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Homework</h2>
        <div class="alert alert-info">Select a homework to edit from the list below.</div>
        <table class="table" id="superadmin-homeworks">
            <thead>
                <tr>
                    <th>Homework ID</th>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Due Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($homeworks as $homework): ?>
                    <tr>
                        <td><?php echo esc_html($homework['homework_id']); ?></td>
                        <td><?php echo esc_html($homework['title']); ?></td>
                        <td><?php echo esc_html($homework['subject']); ?></td>
                        <td><?php echo esc_html($homework['class']); ?></td>
                        <td><?php echo esc_html($homework['due_date']); ?></td>
                        <td><?php echo esc_html($homework['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'manage-homeworks'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'delete-homework'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteHomework() {
    // Hardcoded data for homeworks
    $homeworks = [
        ['homework_id' => 'HW001', 'title' => 'Algebra Practice', 'subject' => 'Mathematics', 'class' => 'Class 10', 'due_date' => '2025-04-20', 'center' => 'Main Campus'],
        ['homework_id' => 'HW002', 'title' => 'Physics Lab Report', 'subject' => 'Science', 'class' => 'Class 10', 'due_date' => '2025-04-22', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Homework</h2>
        <div class="alert alert-warning">Click "Delete" to remove a homework.</div>
        <table class="table" id="superadmin-homeworks">
            <thead>
                <tr>
                    <th>Homework ID</th>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Due Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($homeworks as $homework): ?>
                    <tr>
                        <td><?php echo esc_html($homework['homework_id']); ?></td>
                        <td><?php echo esc_html($homework['title']); ?></td>
                        <td><?php echo esc_html($homework['subject']); ?></td>
                        <td><?php echo esc_html($homework['class']); ?></td>
                        <td><?php echo esc_html($homework['due_date']); ?></td>
                        <td><?php echo esc_html($homework['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'homeworks', 'demo-action' => 'manage-homeworks'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminTimetable() {
    // Hardcoded data for timetable
    $timetable = [
        ['timetable_id' => 'TT001', 'class' => 'Class 10', 'subject' => 'Mathematics', 'day' => 'Monday', 'time' => '09:00-10:00', 'center' => 'Main Campus'],
        ['timetable_id' => 'TT002', 'class' => 'Class 10', 'subject' => 'Science', 'day' => 'Tuesday', 'time' => '10:00-11:00', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Timetable Management</h2>
        <div class="alert alert-info">Manage timetable below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="timetable-search" placeholder="Search Timetable...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'add-timetable'])); ?>" class="btn btn-primary">Add Timetable</a>
            </div>
        </div>
        <table class="table" id="superadmin-timetable">
            <thead>
                <tr>
                    <th>Timetable ID</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timetable as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['timetable_id']); ?></td>
                        <td><?php echo esc_html($entry['class']); ?></td>
                        <td><?php echo esc_html($entry['subject']); ?></td>
                        <td><?php echo esc_html($entry['day']); ?></td>
                        <td><?php echo esc_html($entry['time']); ?></td>
                        <td><?php echo esc_html($entry['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'edit-timetable'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'delete-timetable'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddTimetable() {
    // Hardcoded options for classes, subjects, centers
    $classes = [
        ['name' => 'Class 10'],
        ['name' => 'Class 11'],
    ];
    $subjects = [
        ['name' => 'Mathematics'],
        ['name' => 'Science'],
    ];
    $centers = [
        ['name' => 'Main Campus'],
        ['name' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Timetable</h2>
        <form id="add-timetable-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="timetable-id">Timetable ID</label>
                <input type="text" id="timetable-id" class="edu-form-input" placeholder="e.g., TT001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="class">Class</label>
                <select id="class" class="edu-form-input" required>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo esc_attr($class['name']); ?>"><?php echo esc_html($class['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="subject">Subject</label>
                <select id="subject" class="edu-form-input" required>
                    <?php foreach ($subjects as $subject): ?>
                        <option value="<?php echo esc_attr($subject['name']); ?>"><?php echo esc_html($subject['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="day">Day</label>
                <select id="day" class="edu-form-input" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="time">Time</label>
                <input type="text" id="time" class="edu-form-input" placeholder="e.g., 09:00-10:00" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Timetable</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'manage-timetable'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-timetable-form').on('submit', function(e) {
                e.preventDefault();
                const timetableId = $('#timetable-id').val().trim();
                const className = $('#class').val();
                const subject = $('#subject').val();
                const day = $('#day').val();
                const time = $('#time').val().trim();
                const center = $('#center').val();
                if (timetableId && className && subject && day && time && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Timetable added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'manage-timetable'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditTimetable() {
    // Hardcoded data for timetable
    $timetable = [
        ['timetable_id' => 'TT001', 'class' => 'Class 10', 'subject' => 'Mathematics', 'day' => 'Monday', 'time' => '09:00-10:00', 'center' => 'Main Campus'],
        ['timetable_id' => 'TT002', 'class' => 'Class 10', 'subject' => 'Science', 'day' => 'Tuesday', 'time' => '10:00-11:00', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Timetable</h2>
        <div class="alert alert-info">Select a timetable entry to edit from the list below.</div>
        <table class="table" id="superadmin-timetable">
            <thead>
                <tr>
                    <th>Timetable ID</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timetable as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['timetable_id']); ?></td>
                        <td><?php echo esc_html($entry['class']); ?></td>
                        <td><?php echo esc_html($entry['subject']); ?></td>
                        <td><?php echo esc_html($entry['day']); ?></td>
                        <td><?php echo esc_html($entry['time']); ?></td>
                        <td><?php echo esc_html($entry['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'manage-timetable'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'delete-timetable'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteTimetable() {
    // Hardcoded data for timetable
    $timetable = [
        ['timetable_id' => 'TT001', 'class' => 'Class 10', 'subject' => 'Mathematics', 'day' => 'Monday', 'time' => '09:00-10:00', 'center' => 'Main Campus'],
        ['timetable_id' => 'TT002', 'class' => 'Class 10', 'subject' => 'Science', 'day' => 'Tuesday', 'time' => '10:00-11:00', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Timetable</h2>
        <div class="alert alert-warning">Click "Delete" to remove a timetable entry.</div>
        <table class="table" id="superadmin-timetable">
            <thead>
                <tr>
                    <th>Timetable ID</th>
                    <th>Class</th>
                    <th>Subject</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($timetable as $entry): ?>
                    <tr>
                        <td><?php echo esc_html($entry['timetable_id']); ?></td>
                        <td><?php echo esc_html($entry['class']); ?></td>
                        <td><?php echo esc_html($entry['subject']); ?></td>
                        <td><?php echo esc_html($entry['day']); ?></td>
                        <td><?php echo esc_html($entry['time']); ?></td>
                        <td><?php echo esc_html($entry['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'timetable', 'demo-action' => 'manage-timetable'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDepartments() {
    // Hardcoded data for departments
    $departments = [
        ['department_id' => 'DEP001', 'name' => 'Mathematics', 'head' => 'Dr. John Smith', 'center' => 'Main Campus'],
        ['department_id' => 'DEP002', 'name' => 'Science', 'head' => 'Prof. Jane Doe', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Departments Management</h2>
        <div class="alert alert-info">Manage departments below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="departments-search" placeholder="Search Departments...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'add-department'])); ?>" class="btn btn-primary">Add Department</a>
            </div>
        </div>
        <table class="table" id="superadmin-departments">
            <thead>
                <tr>
                    <th>Department ID</th>
                    <th>Name</th>
                    <th>Head</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $department): ?>
                    <tr>
                        <td><?php echo esc_html($department['department_id']); ?></td>
                        <td><?php echo esc_html($department['name']); ?></td>
                        <td><?php echo esc_html($department['head']); ?></td>
                        <td><?php echo esc_html($department['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'edit-department'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'delete-department'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddDepartment() {
    // Hardcoded options for centers
    $centers = [
        ['name' => 'Main Campus'],
        ['name' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Department</h2>
        <form id="add-department-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="department-id">Department ID</label>
                <input type="text" id="department-id" class="edu-form-input" placeholder="e.g., DEP001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="department-name">Department Name</label>
                <input type="text" id="department-name" class="edu-form-input" placeholder="e.g., Mathematics" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="head">Head of Department</label>
                <input type="text" id="head" class="edu-form-input" placeholder="e.g., Dr. John Smith" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Department</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'manage-departments'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-department-form').on('submit', function(e) {
                e.preventDefault();
                const departmentId = $('#department-id').val().trim();
                const departmentName = $('#department-name').val().trim();
                const head = $('#head').val().trim();
                const center = $('#center').val();
                if (departmentId && departmentName && head && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Department added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'manage-departments'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditDepartment() {
    // Hardcoded data for departments
    $departments = [
        ['department_id' => 'DEP001', 'name' => 'Mathematics', 'head' => 'Dr. John Smith', 'center' => 'Main Campus'],
        ['department_id' => 'DEP002', 'name' => 'Science', 'head' => 'Prof. Jane Doe', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Department</h2>
        <div class="alert alert-info">Select a department to edit from the list below.</div>
        <table class="table" id="superadmin-departments">
            <thead>
                <tr>
                    <th>Department ID</th>
                    <th>Name</th>
                    <th>Head</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $department): ?>
                    <tr>
                        <td><?php echo esc_html($department['department_id']); ?></td>
                        <td><?php echo esc_html($department['name']); ?></td>
                        <td><?php echo esc_html($department['head']); ?></td>
                        <td><?php echo esc_html($department['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'manage-departments'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'delete-department'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteDepartment() {
    // Hardcoded data for departments
    $departments = [
        ['department_id' => 'DEP001', 'name' => 'Mathematics', 'head' => 'Dr. John Smith', 'center' => 'Main Campus'],
        ['department_id' => 'DEP002', 'name' => 'Science', 'head' => 'Prof. Jane Doe', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Department</h2>
        <div class="alert alert-warning">Click "Delete" to remove a department.</div>
        <table class="table" id="superadmin-departments">
            <thead>
                <tr>
                    <th>Department ID</th>
                    <th>Name</th>
                    <th>Head</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $department): ?>
                    <tr>
                        <td><?php echo esc_html($department['department_id']); ?></td>
                        <td><?php echo esc_html($department['name']); ?></td>
                        <td><?php echo esc_html($department['head']); ?></td>
                        <td><?php echo esc_html($department['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'departments', 'demo-action' => 'manage-departments'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminLibrary() {
    // Hardcoded data for library
    $library = [
        ['book_id' => 'BK001', 'title' => 'Calculus', 'author' => 'James Stewart', 'status' => 'Available', 'center' => 'Main Campus'],
        ['book_id' => 'BK002', 'title' => 'Physics', 'author' => 'David Halliday', 'status' => 'Borrowed', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Library Management</h2>
        <div class="alert alert-info">Manage library books below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="library-search" placeholder="Search Library...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'add-book'])); ?>" class="btn btn-primary">Add Book</a>
            </div>
        </div>
        <table class="table" id="superadmin-library">
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($library as $book): ?>
                    <tr>
                        <td><?php echo esc_html($book['book_id']); ?></td>
                        <td><?php echo esc_html($book['title']); ?></td>
                        <td><?php echo esc_html($book['author']); ?></td>
                        <td><?php echo esc_html($book['status']); ?></td>
                        <td><?php echo esc_html($book['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'edit-book'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'delete-book'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddBook() {
    // Hardcoded options for centers
    $centers = [
        ['name' => 'Main Campus'],
        ['name' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Book</h2>
        <form id="add-book-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="book-id">Book ID</label>
                <input type="text" id="book-id" class="edu-form-input" placeholder="e.g., BK001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="book-title">Title</label>
                <input type="text" id="book-title" class="edu-form-input" placeholder="e.g., Calculus" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="author">Author</label>
                <input type="text" id="author" class="edu-form-input" placeholder="e.g., James Stewart" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="status">Status</label>
                <select id="status" class="edu-form-input" required>
                    <option value="Available">Available</option>
                    <option value="Borrowed">Borrowed</option>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Book</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'manage-library'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-book-form').on('submit', function(e) {
                e.preventDefault();
                const bookId = $('#book-id').val().trim();
                const title = $('#book-title').val().trim();
                const author = $('#author').val().trim();
                const status = $('#status').val();
                const center = $('#center').val();
                if (bookId && title && author && status && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Book added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'manage-library'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
} 
function demoRenderSuperadminEditBook() {
    // Hardcoded data for library
    $library = [
        ['book_id' => 'BK001', 'title' => 'Calculus', 'author' => 'James Stewart', 'status' => 'Available', 'center' => 'Main Campus'],
        ['book_id' => 'BK002', 'title' => 'Physics', 'author' => 'David Halliday', 'status' => 'Borrowed', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Book</h2>
        <div class="alert alert-info">Select a book to edit from the list below.</div>
        <table class="table" id="superadmin-library">
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($library as $book): ?>
                    <tr>
                        <td><?php echo esc_html($book['book_id']); ?></td>
                        <td><?php echo esc_html($book['title']); ?></td>
                        <td><?php echo esc_html($book['author']); ?></td>
                        <td><?php echo esc_html($book['status']); ?></td>
                        <td><?php echo esc_html($book['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'manage-library'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'delete-book'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteBook() {
    // Hardcoded data for library
    $library = [
        ['book_id' => 'BK001', 'title' => 'Calculus', 'author' => 'James Stewart', 'status' => 'Available', 'center' => 'Main Campus'],
        ['book_id' => 'BK002', 'title' => 'Physics', 'author' => 'David Halliday', 'status' => 'Borrowed', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Book</h2>
        <div class="alert alert-warning">Click "Delete" to remove a book.</div>
        <table class="table" id="superadmin-library">
            <thead>
                <tr>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Status</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($library as $book): ?>
                    <tr>
                        <td><?php echo esc_html($book['book_id']); ?></td>
                        <td><?php echo esc_html($book['title']); ?></td>
                        <td><?php echo esc_html($book['author']); ?></td>
                        <td><?php echo esc_html($book['status']); ?></td>
                        <td><?php echo esc_html($book['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library', 'demo-action' => 'manage-library'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminInventory() {
    // Hardcoded data for inventory
    $inventory = [
        ['item_id' => 'INV001', 'name' => 'Projector', 'quantity' => 10, 'category' => 'Electronics', 'center' => 'Main Campus'],
        ['item_id' => 'INV002', 'name' => 'Lab Equipment', 'quantity' => 5, 'category' => 'Science', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Inventory Management</h2>
        <div class="alert alert-info">Manage inventory items below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="inventory-search" placeholder="Search Inventory...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'add-item'])); ?>" class="btn btn-primary">Add Item</a>
            </div>
        </div>
        <table class="table" id="superadmin-inventory">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item['item_id']); ?></td>
                        <td><?php echo esc_html($item['name']); ?></td>
                        <td><?php echo esc_html($item['quantity']); ?></td>
                        <td><?php echo esc_html($item['category']); ?></td>
                        <td><?php echo esc_html($item['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'edit-item'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'delete-item'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddItem() {
    // Hardcoded options for centers
    $centers = [
        ['name' => 'Main Campus'],
        ['name' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Inventory Item</h2>
        <form id="add-item-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="item-id">Item ID</label>
                <input type="text" id="item-id" class="edu-form-input" placeholder="e.g., INV001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="item-name">Item Name</label>
                <input type="text" id="item-name" class="edu-form-input" placeholder="e.g., Projector" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="quantity">Quantity</label>
                <input type="number" id="quantity" class="edu-form-input" placeholder="e.g., 10" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="category">Category</label>
                <input type="text" id="category" class="edu-form-input" placeholder="e.g., Electronics" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Item</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'manage-inventory'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-item-form').on('submit', function(e) {
                e.preventDefault();
                const itemId = $('#item-id').val().trim();
                const itemName = $('#item-name').val().trim();
                const quantity = $('#quantity').val();
                const category = $('#category').val().trim();
                const center = $('#center').val();
                if (itemId && itemName && quantity && category && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Item added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'manage-inventory'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditItem() {
    // Hardcoded data for inventory
    $inventory = [
        ['item_id' => 'INV001', 'name' => 'Projector', 'quantity' => 10, 'category' => 'Electronics', 'center' => 'Main Campus'],
        ['item_id' => 'INV002', 'name' => 'Lab Equipment', 'quantity' => 5, 'category' => 'Science', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Inventory Item</h2>
        <div class="alert alert-info">Select an item to edit from the list below.</div>
        <table class="table" id="superadmin-inventory">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item['item_id']); ?></td>
                        <td><?php echo esc_html($item['name']); ?></td>
                        <td><?php echo esc_html($item['quantity']); ?></td>
                        <td><?php echo esc_html($item['category']); ?></td>
                        <td><?php echo esc_html($item['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'manage-inventory'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'delete-item'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteItem() {
    // Hardcoded data for inventory
    $inventory = [
        ['item_id' => 'INV001', 'name' => 'Projector', 'quantity' => 10, 'category' => 'Electronics', 'center' => 'Main Campus'],
        ['item_id' => 'INV002', 'name' => 'Lab Equipment', 'quantity' => 5, 'category' => 'Science', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Inventory Item</h2>
        <div class="alert alert-warning">Click "Delete" to remove an item.</div>
        <table class="table" id="superadmin-inventory">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Name</th>
                    <th>Quantity</th>
                    <th>Category</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item['item_id']); ?></td>
                        <td><?php echo esc_html($item['name']); ?></td>
                        <td><?php echo esc_html($item['quantity']); ?></td>
                        <td><?php echo esc_html($item['category']); ?></td>
                        <td><?php echo esc_html($item['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory', 'demo-action' => 'manage-inventory'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminLibraryTransactions() {
    // Hardcoded data for library transactions
    $library_transactions = [
        ['transaction_id' => 'LT001', 'book_id' => 'BK001', 'student_id' => 'ST001', 'issue_date' => '2025-04-10', 'return_date' => '2025-04-17', 'status' => 'Issued', 'center' => 'Main Campus'],
        ['transaction_id' => 'LT002', 'book_id' => 'BK002', 'student_id' => 'ST002', 'issue_date' => '2025-04-11', 'return_date' => '', 'status' => 'Returned', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Library Transactions Management</h2>
        <div class="alert alert-info">Manage library transactions below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="library-transactions-search" placeholder="Search Library Transactions...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'add-transaction'])); ?>" class="btn btn-primary">Add Transaction</a>
            </div>
        </div>
        <table class="table" id="superadmin-library-transactions">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Book ID</th>
                    <th>Student ID</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($library_transactions as $transaction): ?>
                    <tr>
                        <td><?php echo esc_html($transaction['transaction_id']); ?></td>
                        <td><?php echo esc_html($transaction['book_id']); ?></td>
                        <td><?php echo esc_html($transaction['student_id']); ?></td>
                        <td><?php echo esc_html($transaction['issue_date']); ?></td>
                        <td><?php echo esc_html($transaction['return_date']); ?></td>
                        <td><?php echo esc_html($transaction['status']); ?></td>
                        <td><?php echo esc_html($transaction['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'edit-transaction'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'delete-transaction'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddLibraryTransaction() {
    // Hardcoded options for books, students, centers
    $books = [
        ['book_id' => 'BK001', 'title' => 'Calculus'],
        ['book_id' => 'BK002', 'title' => 'Physics'],
    ];
    $students = [
        ['student_id' => 'ST001', 'name' => 'John Doe'],
        ['student_id' => 'ST002', 'name' => 'Jane Smith'],
    ];
    $centers = [
        ['name' => 'Main Campus'],
        ['name' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Library Transaction</h2>
        <form id="add-library-transaction-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="transaction-id">Transaction ID</label>
                <input type="text" id="transaction-id" class="edu-form-input" placeholder="e.g., LT001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="book-id">Book</label>
                <select id="book-id" class="edu-form-input" required>
                    <?php foreach ($books as $book): ?>
                        <option value="<?php echo esc_attr($book['book_id']); ?>"><?php echo esc_html($book['book_id'] . ' - ' . $book['title']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="student-id">Student</label>
                <select id="student-id" class="edu-form-input" required>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo esc_attr($student['student_id']); ?>"><?php echo esc_html($student['student_id'] . ' - ' . $student['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="issue-date">Issue Date</label>
                <input type="date" id="issue-date" class="edu-form-input" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="return-date">Return Date</label>
                <input type="date" id="return-date" class="edu-form-input">
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="status">Status</label>
                <select id="status" class="edu-form-input" required>
                    <option value="Issued">Issued</option>
                    <option value="Returned">Returned</option>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Transaction</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'manage-library-transactions'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-library-transaction-form').on('submit', function(e) {
                e.preventDefault();
                const transactionId = $('#transaction-id').val().trim();
                const bookId = $('#book-id').val();
                const studentId = $('#student-id').val();
                const issueDate = $('#issue-date').val();
                const status = $('#status').val();
                const center = $('#center').val();
                if (transactionId && bookId && studentId && issueDate && status && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Transaction added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'manage-library-transactions'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditLibraryTransaction() {
    // Hardcoded data for library transactions
    $library_transactions = [
        ['transaction_id' => 'LT001', 'book_id' => 'BK001', 'student_id' => 'ST001', 'issue_date' => '2025-04-10', 'return_date' => '2025-04-17', 'status' => 'Issued', 'center' => 'Main Campus'],
        ['transaction_id' => 'LT002', 'book_id' => 'BK002', 'student_id' => 'ST002', 'issue_date' => '2025-04-11', 'return_date' => '', 'status' => 'Returned', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Library Transaction</h2>
        <div class="alert alert-info">Select a transaction to edit from the list below.</div>
        <table class="table" id="superadmin-library-transactions">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Book ID</th>
                    <th>Student ID</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($library_transactions as $transaction): ?>
                    <tr>
                        <td><?php echo esc_html($transaction['transaction_id']); ?></td>
                        <td><?php echo esc_html($transaction['book_id']); ?></td>
                        <td><?php echo esc_html($transaction['student_id']); ?></td>
                        <td><?php echo esc_html($transaction['issue_date']); ?></td>
                        <td><?php echo esc_html($transaction['return_date']); ?></td>
                        <td><?php echo esc_html($transaction['status']); ?></td>
                        <td><?php echo esc_html($transaction['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'manage-library-transactions'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'delete-transaction'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteLibraryTransaction() {
    // Hardcoded data for library transactions
    $library_transactions = [
        ['transaction_id' => 'LT001', 'book_id' => 'BK001', 'student_id' => 'ST001', 'issue_date' => '2025-04-10', 'return_date' => '2025-04-17', 'status' => 'Issued', 'center' => 'Main Campus'],
        ['transaction_id' => 'LT002', 'book_id' => 'BK002', 'student_id' => 'ST002', 'issue_date' => '2025-04-11', 'return_date' => '', 'status' => 'Returned', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Library Transaction</h2>
        <div class="alert alert-warning">Click "Delete" to remove a transaction.</div>
        <table class="table" id="superadmin-library-transactions">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Book ID</th>
                    <th>Student ID</th>
                    <th>Issue Date</th>
                    <th>Return Date</th>
                    <th>Status</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($library_transactions as $transaction): ?>
                    <tr>
                        <td><?php echo esc_html($transaction['transaction_id']); ?></td>
                        <td><?php echo esc_html($transaction['book_id']); ?></td>
                        <td><?php echo esc_html($transaction['student_id']); ?></td>
                        <td><?php echo esc_html($transaction['issue_date']); ?></td>
                        <td><?php echo esc_html($transaction['return_date']); ?></td>
                        <td><?php echo esc_html($transaction['status']); ?></td>
                        <td><?php echo esc_html($transaction['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'library-transactions', 'demo-action' => 'manage-library-transactions'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminInventoryTransactions() {
    // Hardcoded data for inventory transactions
    $inventory_transactions = [
        ['transaction_id' => 'IT001', 'item_id' => 'INV001', 'type' => 'Issue', 'quantity' => 2, 'date' => '2025-04-10', 'center' => 'Main Campus'],
        ['transaction_id' => 'IT002', 'item_id' => 'INV002', 'type' => 'Return', 'quantity' => 1, 'date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Inventory Transactions Management</h2>
        <div class="alert alert-info">Manage inventory transactions below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="inventory-transactions-search" placeholder="Search Inventory Transactions...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'add-transaction'])); ?>" class="btn btn-primary">Add Transaction</a>
            </div>
        </div>
        <table class="table" id="superadmin-inventory-transactions">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Item ID</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory_transactions as $transaction): ?>
                    <tr>
                        <td><?php echo esc_html($transaction['transaction_id']); ?></td>
                        <td><?php echo esc_html($transaction['item_id']); ?></td>
                        <td><?php echo esc_html($transaction['type']); ?></td>
                        <td><?php echo esc_html($transaction['quantity']); ?></td>
                        <td><?php echo esc_html($transaction['date']); ?></td>
                        <td><?php echo esc_html($transaction['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'edit-transaction'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'delete-transaction'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddInventoryTransaction() {
    // Hardcoded options for items, centers
    $items = [
        ['item_id' => 'INV001', 'name' => 'Projector'],
        ['item_id' => 'INV002', 'name' => 'Lab Equipment'],
    ];
    $centers = [
        ['name' => 'Main Campus'],
        ['name' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Inventory Transaction</h2>
        <form id="add-inventory-transaction-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="transaction-id">Transaction ID</label>
                <input type="text" id="transaction-id" class="edu-form-input" placeholder="e.g., IT001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="item-id">Item</label>
                <select id="item-id" class="edu-form-input" required>
                    <?php foreach ($items as $item): ?>
                        <option value="<?php echo esc_attr($item['item_id']); ?>"><?php echo esc_html($item['item_id'] . ' - ' . $item['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="type">Type</label>
                <select id="type" class="edu-form-input" required>
                    <option value="Issue">Issue</option>
                    <option value="Return">Return</option>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="quantity">Quantity</label>
                <input type="number" id="quantity" class="edu-form-input" placeholder="e.g., 2" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="date">Date</label>
                <input type="date" id="date" class="edu-form-input" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Transaction</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'manage-inventory-transactions'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-inventory-transaction-form').on('submit', function(e) {
                e.preventDefault();
                const transactionId = $('#transaction-id').val().trim();
                const itemId = $('#item-id').val();
                const type = $('#type').val();
                const quantity = $('#quantity').val();
                const date = $('#date').val();
                const center = $('#center').val();
                if (transactionId && itemId && type && quantity && date && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Transaction added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'manage-inventory-transactions'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditInventoryTransaction() {
    // Hardcoded data for inventory transactions
    $inventory_transactions = [
        ['transaction_id' => 'IT001', 'item_id' => 'INV001', 'type' => 'Issue', 'quantity' => 2, 'date' => '2025-04-10', 'center' => 'Main Campus'],
        ['transaction_id' => 'IT002', 'item_id' => 'INV002', 'type' => 'Return', 'quantity' => 1, 'date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Inventory Transaction</h2>
        <div class="alert alert-info">Select a transaction to edit from the list below.</div>
        <table class="table" id="superadmin-inventory-transactions">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Item ID</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory_transactions as $transaction): ?>
                    <tr>
                        <td><?php echo esc_html($transaction['transaction_id']); ?></td>
                        <td><?php echo esc_html($transaction['item_id']); ?></td>
                        <td><?php echo esc_html($transaction['type']); ?></td>
                        <td><?php echo esc_html($transaction['quantity']); ?></td>
                        <td><?php echo esc_html($transaction['date']); ?></td>
                        <td><?php echo esc_html($transaction['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'manage-inventory-transactions'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'inventory-transactions', 'demo-action' => 'delete-transaction'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteInventoryTransaction() {
    // Hardcoded data for inventory transactions
    $inventory_transactions = [
        ['transaction_id' => 'IT001', 'item_id' => 'INV001', 'type' => 'Issue', 'quantity' => 2, 'date' => '2025-04-10', 'center' => 'Main Campus'],
        ['transaction_id' => 'IT002', 'item_id' => 'INV002', 'type' => 'Return', 'quantity' => 1, 'date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Inventory Transaction</h2>
        <div class="alert alert-warning">Click "Delete" to remove a transaction.</div>
        <table class="table" id="superadmin-inventory-transactions">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Item ID</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory_transactions as $transaction): ?>
                    <tr>
                        <td><?php echo esc_html($transaction['transaction_id']); ?></td>
                        <td><?php echo esc_html($transaction['item_id']); ?></td>
                        <td><?php echo esc_html($transaction['type']); ?></td>
                        <td><?php echo esc_html($transaction['quantity']); ?></td>
                        <td><?php echo esc_html($transaction['date']); ?></td>
                        <td><?php echo esc_html($transaction['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg([
                                'demo-role' => 'superadmin',
                                'demo-section' => 'inventory-transactions',
                                'demo-action' => 'manage-inventory-transactions'
                            ])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderSuperadminNoticeboard() {
    // Hardcoded data for noticeboard
    $notices = [
        ['notice_id' => 'NT001', 'title' => 'School Closure', 'content' => 'School will be closed on April 15.', 'posted_date' => '2025-04-10', 'center' => 'Main Campus'],
        ['notice_id' => 'NT002', 'title' => 'Exam Schedule', 'content' => 'Exams start on April 20.', 'posted_date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Noticeboard Management</h2>
        <div class="alert alert-info">Manage notices below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="noticeboard-search" placeholder="Search Notices...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'add-notice'])); ?>" class="btn btn-primary">Add Notice</a>
            </div>
        </div>
        <table class="table" id="superadmin-noticeboard">
            <thead>
                <tr>
                    <th>Notice ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Posted Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notices as $notice): ?>
                    <tr>
                        <td><?php echo esc_html($notice['notice_id']); ?></td>
                        <td><?php echo esc_html($notice['title']); ?></td>
                        <td><?php echo esc_html($notice['content']); ?></td>
                        <td><?php echo esc_html($notice['posted_date']); ?></td>
                        <td><?php echo esc_html($notice['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'edit-notice'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'delete-notice'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddNotice() {
    // Hardcoded options for centers
    $centers = [
        ['name' => 'Main Campus'],
        ['name' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Notice</h2>
        <form id="add-notice-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="notice-id">Notice ID</label>
                <input type="text" id="notice-id" class="edu-form-input" placeholder="e.g., NT001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="notice-title">Title</label>
                <input type="text" id="notice-title" class="edu-form-input" placeholder="e.g., School Closure" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="content">Content</label>
                <textarea id="content" class="edu-form-input" placeholder="e.g., School will be closed on April 15." required></textarea>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="posted-date">Posted Date</label>
                <input type="date" id="posted-date" class="edu-form-input" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Notice</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'manage-noticeboard'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-notice-form').on('submit', function(e) {
                e.preventDefault();
                const noticeId = $('#notice-id').val().trim();
                const title = $('#notice-title').val().trim();
                const content = $('#content').val().trim();
                const postedDate = $('#posted-date').val();
                const center = $('#center').val();
                if (noticeId && title && content && postedDate && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Notice added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'manage-noticeboard'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditNotice() {
    // Hardcoded data for noticeboard
    $notices = [
        ['notice_id' => 'NT001', 'title' => 'School Closure', 'content' => 'School will be closed on April 15.', 'posted_date' => '2025-04-10', 'center' => 'Main Campus'],
        ['notice_id' => 'NT002', 'title' => 'Exam Schedule', 'content' => 'Exams start on April 20.', 'posted_date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Notice</h2>
        <div class="alert alert-info">Select a notice to edit from the list below.</div>
        <table class="table" id="superadmin-noticeboard">
            <thead>
                <tr>
                    <th>Notice ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Posted Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notices as $notice): ?>
                    <tr>
                        <td><?php echo esc_html($notice['notice_id']); ?></td>
                        <td><?php echo esc_html($notice['title']); ?></td>
                        <td><?php echo esc_html($notice['content']); ?></td>
                        <td><?php echo esc_html($notice['posted_date']); ?></td>
                        <td><?php echo esc_html($notice['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'manage-noticeboard'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'delete-notice'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteNotice() {
    // Hardcoded data for noticeboard
    $notices = [
        ['notice_id' => 'NT001', 'title' => 'School Closure', 'content' => 'School will be closed on April 15.', 'posted_date' => '2025-04-10', 'center' => 'Main Campus'],
        ['notice_id' => 'NT002', 'title' => 'Exam Schedule', 'content' => 'Exams start on April 20.', 'posted_date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Notice</h2>
        <div class="alert alert-warning">Click "Delete" to remove a notice.</div>
        <table class="table" id="superadmin-noticeboard">
            <thead>
                <tr>
                    <th>Notice ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Posted Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notices as $notice): ?>
                    <tr>
                        <td><?php echo esc_html($notice['notice_id']); ?></td>
                        <td><?php echo esc_html($notice['title']); ?></td>
                        <td><?php echo esc_html($notice['content']); ?></td>
                        <td><?php echo esc_html($notice['posted_date']); ?></td>
                        <td><?php echo esc_html($notice['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'noticeboard', 'demo-action' => 'manage-noticeboard'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAnnouncements() {
    // Hardcoded data for announcements
    $announcements = [
        ['announcement_id' => 'AN001', 'title' => 'Annual Day', 'content' => 'Annual Day on April 25.', 'posted_date' => '2025-04-10', 'center' => 'Main Campus'],
        ['announcement_id' => 'AN002', 'title' => 'Sports Meet', 'content' => 'Sports Meet on April 30.', 'posted_date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Announcements Management</h2>
        <div class="alert alert-info">Manage announcements below.</div>
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="announcements-search" placeholder="Search Announcements...">
            </div>
            <div class="col-md-6 text-end">
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'add-announcement'])); ?>" class="btn btn-primary">Add Announcement</a>
            </div>
        </div>
        <table class="table" id="superadmin-announcements">
            <thead>
                <tr>
                    <th>Announcement ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Posted Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td><?php echo esc_html($announcement['announcement_id']); ?></td>
                        <td><?php echo esc_html($announcement['title']); ?></td>
                        <td><?php echo esc_html($announcement['content']); ?></td>
                        <td><?php echo esc_html($announcement['posted_date']); ?></td>
                        <td><?php echo esc_html($announcement['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'edit-announcement'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'delete-announcement'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminAddAnnouncement() {
    // Hardcoded options for centers
    $centers = [
        ['name' => 'Main Campus'],
        ['name' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Announcement</h2>
        <form id="add-announcement-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="announcement-id">Announcement ID</label>
                <input type="text" id="announcement-id" class="edu-form-input" placeholder="e.g., AN001" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="announcement-title">Title</label>
                <input type="text" id="announcement-title" class="edu-form-input" placeholder="e.g., Annual Day" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="content">Content</label>
                <textarea id="content" class="edu-form-input" placeholder="e.g., Annual Day on April 25." required></textarea>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="posted-date">Posted Date</label>
                <input type="date" id="posted-date" class="edu-form-input" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="center">Center</label>
                <select id="center" class="edu-form-input" required>
                    <?php foreach ($centers as $center): ?>
                        <option value="<?php echo esc_attr($center['name']); ?>"><?php echo esc_html($center['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Add Announcement</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'manage-announcements'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#add-announcement-form').on('submit', function(e) {
                e.preventDefault();
                const announcementId = $('#announcement-id').val().trim();
                const title = $('#announcement-title').val().trim();
                const content = $('#content').val().trim();
                const postedDate = $('#posted-date').val();
                const center = $('#center').val();
                if (announcementId && title && content && postedDate && center) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Announcement added successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'manage-announcements'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminEditAnnouncement() {
    // Hardcoded data for announcements
    $announcements = [
        ['announcement_id' => 'AN001', 'title' => 'Annual Day', 'content' => 'Annual Day on April 25.', 'posted_date' => '2025-04-10', 'center' => 'Main Campus'],
        ['announcement_id' => 'AN002', 'title' => 'Sports Meet', 'content' => 'Sports Meet on April 30.', 'posted_date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Edit Announcement</h2>
        <div class="alert alert-info">Select an announcement to edit from the list below.</div>
        <table class="table" id="superadmin-announcements">
            <thead>
                <tr>
                    <th>Announcement ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Posted Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td><?php echo esc_html($announcement['announcement_id']); ?></td>
                        <td><?php echo esc_html($announcement['title']); ?></td>
                        <td><?php echo esc_html($announcement['content']); ?></td>
                        <td><?php echo esc_html($announcement['posted_date']); ?></td>
                        <td><?php echo esc_html($announcement['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'manage-announcements'])); ?>" class="btn btn-warning">Edit</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'delete-announcement'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderSuperadminDeleteAnnouncement() {
    // Hardcoded data for announcements
    $announcements = [
        ['announcement_id' => 'AN001', 'title' => 'Annual Day', 'content' => 'Annual Day on April 25.', 'posted_date' => '2025-04-10', 'center' => 'Main Campus'],
        ['announcement_id' => 'AN002', 'title' => 'Sports Meet', 'content' => 'Sports Meet on April 30.', 'posted_date' => '2025-04-11', 'center' => 'West Campus'],
    ];
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Delete Announcement</h2>
        <div class="alert alert-warning">Click "Delete" to remove an announcement.</div>
        <table class="table" id="superadmin-announcements">
            <thead>
                <tr>
                    <th>Announcement ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Posted Date</th>
                    <th>Center</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($announcements as $announcement): ?>
                    <tr>
                        <td><?php echo esc_html($announcement['announcement_id']); ?></td>
                        <td><?php echo esc_html($announcement['title']); ?></td>
                        <td><?php echo esc_html($announcement['content']); ?></td>
                        <td><?php echo esc_html($announcement['posted_date']); ?></td>
                        <td><?php echo esc_html($announcement['center']); ?></td>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'announcements', 'demo-action' => 'manage-announcements'])); ?>" class="btn btn-danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Superadmin Chats Section (formerly Messages)
 */
function demoRenderSuperadminChats($action) {
    // Hardcoded conversation data
    $conversations = [
        ['id' => 'CONV001', 'recipient_id' => 'T001', 'recipient_name' => 'Alice Johnson', 'last_message' => 'Can we discuss the exam schedule?', 'last_message_time' => '2025-04-16 10:30', 'unread' => 2],
        ['id' => 'CONV002', 'recipient_id' => 'S001', 'recipient_name' => 'John Doe', 'last_message' => 'I have a question about homework.', 'last_message_time' => '2025-04-15 14:20', 'unread' => 0],
        ['id' => 'CONV003', 'recipient_id' => 'P001', 'recipient_name' => 'Mary Brown', 'last_message' => 'Please update on my child’s attendance.', 'last_message_time' => '2025-04-14 09:15', 'unread' => 1],
    ];

    // Hardcoded chats for the first conversation (CONV001)
    $chats = [
        ['sender' => 'T001', 'sender_name' => 'Alice Johnson', 'content' => 'Can we discuss the exam schedule?', 'time' => '2025-04-16 10:30', 'status' => 'received'],
        ['sender' => 'SA001', 'sender_name' => 'Super Admin', 'content' => 'Sure, let’s meet tomorrow.', 'time' => '2025-04-16 10:35', 'status' => 'sent'],
        ['sender' => 'T001', 'sender_name' => 'Alice Johnson', 'content' => 'Great, what time?', 'time' => '2025-04-16 10:40', 'status' => 'received'],
    ];

    ob_start();
    ?>
    <div class="dashboard-section chat-container">
        <h2>Chats Inbox</h2>
        <div class="chat-wrapper">
            <!-- Sidebar -->
            <div class="chat-sidebar">
                <div class="sidebar-header">
                    <h4>Conversations</h4>
                    <input type="text" id="conversation-search" class="form-control" placeholder="Search conversations...">
                </div>
                <ul class="conversation-list">
                    <?php foreach ($conversations as $index => $conv): ?>
                        <li class="conversation-item <?php echo $index === 0 ? 'active' : ''; ?>" data-conv-id="<?php echo esc_attr($conv['id']); ?>">
                            <strong><?php echo esc_html($conv['recipient_name']); ?> (<?php echo esc_html($conv['recipient_id']); ?>)</strong>
                            <p><?php echo esc_html($conv['last_message']); ?></p>
                            <span class="meta"><?php echo esc_html($conv['last_message_time']); ?></span>
                            <?php if ($conv['unread'] > 0): ?>
                                <span class="badge bg-primary"><?php echo esc_html($conv['unread']); ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- Main Chat Area -->
            <div class="chat-main">
                <div class="chat-header">
                    <h4>Conversation with <?php echo esc_html($conversations[0]['recipient_name']); ?> (<?php echo esc_html($conversations[0]['recipient_id']); ?>)</h4>
                </div>
                <div class="chat-messages" id="chat-messages">
                    <?php foreach ($chats as $msg): ?>
                        <div class="chat-message <?php echo $msg['status']; ?>">
                            <div class="bubble"><?php echo esc_html($msg['content']); ?></div>
                            <div class="meta"><?php echo esc_html($msg['sender_name']); ?> • <?php echo esc_html($msg['time']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <form class="chat-form" id="chat-form">
                    <div class="d-flex align-items-center">
                        <textarea class="form-control" id="chat-input" rows="2" placeholder="Type a chat..."></textarea>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </form>
                <div class="chat-loading" id="chat-loading">Loading...</div>
            </div>
        </div>
        <script>
        jQuery(document).ready(function($) {
            // Conversation search
            $('#conversation-search').on('input', function() {
                const query = $(this).val().toLowerCase();
                $('.conversation-item').each(function() {
                    const name = $(this).find('strong').text().toLowerCase();
                    const message = $(this).find('p').text().toLowerCase();
                    $(this).toggle(name.includes(query) || message.includes(query));
                });
            });

            // Conversation selection
            $('.conversation-item').on('click', function() {
                $('.conversation-item').removeClass('active');
                $(this).addClass('active');
                const convId = $(this).data('conv-id');
                $('#chat-messages').html('<div class="chat-loading active">Loading...</div>');
                // Simulate loading chats (hardcoded)
                setTimeout(() => {
                    $('#chat-messages').html(`
                        <div class="chat-message received">
                            <div class="bubble">Can we discuss the exam schedule?</div>
                            <div class="meta">Alice Johnson • 2025-04-16 10:30</div>
                        </div>
                        <div class="chat-message sent">
                            <div class="bubble">Sure, let’s meet tomorrow.</div>
                            <div class="meta">Super Admin • 2025-04-16 10:35</div>
                        </div>
                        <div class="chat-message received">
                            <div class="bubble">Great, what time?</div>
                            <div class="meta">Alice Johnson • 2025-04-16 10:40</div>
                        </div>
                    `);
                }, 500);
            });

            // Send chat
            $('#chat-form').on('submit', function(e) {
                e.preventDefault();
                const message = $('#chat-input').val().trim();
                if (message) {
                    $('#chat-messages').append(`
                        <div class="chat-message sent">
                            <div class="bubble">${message}</div>
                            <div class="meta">Super Admin • ${new Date().toLocaleString()}</div>
                        </div>
                    `);
                    $('#chat-input').val('');
                    $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Superadmin New Chat Conversation (formerly New Conversation)
 */
function demoRenderSuperadminNewConversation() {
    // Hardcoded recipient data
    $recipients = [
        'admins' => [
            ['id' => 'SA001', 'name' => 'Super Admin'],
            ['id' => 'SA002', 'name' => 'Admin Smith'],
        ],
        'teachers' => [
            ['id' => 'T001', 'name' => 'Alice Johnson'],
            ['id' => 'T002', 'name' => 'Bob Wilson'],
        ],
        'students' => [
            ['id' => 'S001', 'name' => 'John Doe'],
            ['id' => 'S002', 'name' => 'Jane Smith'],
        ],
        'parents' => [
            ['id' => 'P001', 'name' => 'Mary Brown'],
            ['id' => 'P002', 'name' => 'James Wilson'],
        ],
    ];

    ob_start();
    ?>
    <div class="dashboard-section chat-container">
        <h2>New Chat</h2>
        <form id="new-chat-form" class="edu-form">
            <div class="edu-form-group">
                <label class="edu-form-label" for="recipient">Recipient</label>
                <select id="recipient" class="edu-form-input" required>
                    <option value="">Select a recipient</option>
                    <optgroup label="Institute Admins">
                        <?php foreach ($recipients['admins'] as $admin): ?>
                            <option value="<?php echo esc_attr($admin['id']); ?>">
                                <?php echo esc_html($admin['name'] . ' (' . $admin['id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Teachers">
                        <?php foreach ($recipients['teachers'] as $teacher): ?>
                            <option value="<?php echo esc_attr($teacher['id']); ?>">
                                <?php echo esc_html($teacher['name'] . ' (' . $teacher['id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Students">
                        <?php foreach ($recipients['students'] as $student): ?>
                            <option value="<?php echo esc_attr($student['id']); ?>">
                                <?php echo esc_html($student['name'] . ' (' . $student['id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Parents">
                        <?php foreach ($recipients['parents'] as $parent): ?>
                            <option value="<?php echo esc_attr($parent['id']); ?>">
                                <?php echo esc_html($parent['name'] . ' (' . $parent['id'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="subject">Subject</label>
                <input type="text" id="subject" class="edu-form-input" placeholder="e.g., Exam Schedule Discussion" required>
            </div>
            <div class="edu-form-group">
                <label class="edu-form-label" for="chat">Chat</label>
                <textarea id="chat" class="edu-form-input" rows="5" placeholder="Type your chat..." required></textarea>
            </div>
            <div class="edu-form-actions">
                <button type="submit" class="edu-button edu-button-primary">Send Chat</button>
                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'chats'])); ?>" class="edu-button edu-button-secondary">Cancel</a>
            </div>
            <div class="edu-form-message" id="form-message"></div>
        </form>
        <script>
        jQuery(document).ready(function($) {
            $('#new-chat-form').on('submit', function(e) {
                e.preventDefault();
                const recipient = $('#recipient').val();
                const subject = $('#subject').val().trim();
                const chat = $('#chat').val().trim();
                if (recipient && subject && chat) {
                    $('#form-message').removeClass('edu-error').addClass('edu-success').text('Chat sent successfully!');
                    setTimeout(() => {
                        window.location.href = '<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'chats'])); ?>';
                    }, 1000);
                } else {
                    $('#form-message').removeClass('edu-success').addClass('edu-error').text('Please fill all fields.');
                }
            });
        });
        </script>
    </div>
    <?php
    return ob_get_clean();
}


/**
 * Render Institute Admin Content
 */

// Data Function
function demoGetInstituteAdminData() {
    return [
        'institute' => 'Sunrise International School',
        'students' => [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john.doe@example.com', 'grade' => '10'],
            ['id' => 2, 'name' => 'Aisha Khan', 'email' => 'aisha.k@example.com', 'grade' => '11'],
            ['id' => 3, 'name' => 'Michael Chen', 'email' => 'm.chen@example.com', 'grade' => '9'],
            ['id' => 4, 'name' => 'Priya Sharma', 'email' => '', 'grade' => '10'], // Missing email
            ['id' => 5, 'name' => 'Carlos Rivera', 'email' => 'carlos.rivera@example.com', 'grade' => '12'],
            ['id' => 6, 'name' => 'Emma Wilson', 'email' => 'emma.w@example.com', 'grade' => '11'],
        ],
        'teachers' => [
            ['id' => 1, 'name' => 'Jane Smith', 'email' => 'jane.smith@example.com', 'subject' => 'Mathematics'],
            ['id' => 2, 'name' => 'Robert Patel', 'email' => 'r.patel@example.com', 'subject' => 'Physics'],
            ['id' => 3, 'name' => 'Linda Brown', 'email' => 'linda.b@example.com', 'subject' => 'English'],
            ['id' => 4, 'name' => 'Ahmed Zaki', 'email' => 'ahmed.zaki@example.com', 'subject' => 'Chemistry'],
        ],
        'staff' => [
            ['id' => 1, 'name' => 'Mike Brown', 'email' => 'mike.brown@example.com', 'role' => 'Librarian'],
            ['id' => 2, 'name' => 'Sarah Lee', 'email' => 'sarah.lee@example.com', 'role' => 'Receptionist'],
            ['id' => 3, 'name' => 'David Kim', 'email' => '', 'role' => 'Janitor'], // Missing email
        ],
        'exams' => [
            ['id' => 1, 'name' => 'Midterm', 'date' => '2025-05-01', 'subject' => 'Mathematics'],
            ['id' => 2, 'name' => 'Quarterly', 'date' => '2025-04-25', 'subject' => 'Physics'],
            ['id' => 3, 'name' => 'Final', 'date' => '2025-06-15', 'subject' => 'English'],
            ['id' => 4, 'name' => 'Mock Test', 'date' => '2025-05-10', 'subject' => 'Chemistry'],
            ['id' => 5, 'name' => 'Unit Test', 'date' => '2025-04-20', 'subject' => 'Biology'],
        ],
        'student_attendance' => [
            ['id' => 1, 'student_name' => 'John Doe', 'date' => '2025-04-18', 'status' => 'Present'],
            ['id' => 2, 'student_name' => 'Aisha Khan', 'date' => '2025-04-18', 'status' => 'Absent'],
            ['id' => 3, 'student_name' => 'Michael Chen', 'date' => '2025-04-18', 'status' => 'Present'],
            ['id' => 4, 'student_name' => 'Priya Sharma', 'date' => '2025-04-18', 'status' => 'Late'],
            ['id' => 5, 'student_name' => 'Carlos Rivera', 'date' => '2025-04-19', 'status' => 'Present'],
            ['id' => 6, 'student_name' => 'Emma Wilson', 'date' => '2025-04-19', 'status' => 'Absent'],
            ['id' => 7, 'student_name' => 'John Doe', 'date' => '2025-04-19', 'status' => 'Present'],
        ],
        'teacher_attendance' => [
            ['id' => 1, 'teacher_name' => 'Jane Smith', 'date' => '2025-04-18', 'status' => 'Present'],
            ['id' => 2, 'teacher_name' => 'Robert Patel', 'date' => '2025-04-18', 'status' => 'Present'],
            ['id' => 3, 'teacher_name' => 'Linda Brown', 'date' => '2025-04-18', 'status' => 'Absent'],
            ['id' => 4, 'teacher_name' => 'Ahmed Zaki', 'date' => '2025-04-19', 'status' => 'Present'],
        ],
        'staff_attendance' => [
            ['id' => 1, 'staff_name' => 'Mike Brown', 'date' => '2025-04-18', 'status' => 'Present'],
            ['id' => 2, 'staff_name' => 'Sarah Lee', 'date' => '2025-04-18', 'status' => 'Present'],
            ['id' => 3, 'staff_name' => 'David Kim', 'date' => '2025-04-18', 'status' => 'Absent'],
            ['id' => 4, 'staff_name' => 'Mike Brown', 'date' => '2025-04-19', 'status' => 'Present'],
        ],
        'fees' => [
            ['id' => 1, 'student_name' => 'John Doe', 'amount' => 500, 'due_date' => '2025-05-01', 'status' => 'Pending'],
            ['id' => 2, 'student_name' => 'Aisha Khan', 'amount' => 600, 'due_date' => '2025-05-01', 'status' => 'Paid'],
            ['id' => 3, 'student_name' => 'Michael Chen', 'amount' => 450, 'due_date' => '2025-04-30', 'status' => 'Pending'],
            ['id' => 4, 'student_name' => 'Priya Sharma', 'amount' => 500, 'due_date' => '2025-05-01', 'status' => 'Overdue'],
            ['id' => 5, 'student_name' => 'Carlos Rivera', 'amount' => 700, 'due_date' => '2025-05-15', 'status' => 'Paid'],
        ],
        'notices' => [
            ['id' => 1, 'title' => 'Holiday Notice', 'content' => 'School closed on May 1 for Labor Day.', 'date' => '2025-04-18'],
            ['id' => 2, 'title' => 'Parent-Teacher Meeting', 'content' => 'Scheduled for April 25.', 'date' => '2025-04-20'],
        ],
        'announcements' => [
            ['id' => 1, 'title' => 'Annual Day', 'content' => 'Join us on June 1 for performances.', 'date' => '2025-04-18'],
            ['id' => 2, 'title' => 'Science Fair', 'content' => 'Submit projects by May 15.', 'date' => '2025-04-19'],
            ['id' => 3, 'title' => 'Sports Day', 'content' => 'Event on May 20, all welcome.', 'date' => '2025-04-20'],
            ['id' => 4, 'title' => 'Book Fair', 'content' => 'Visit the library on April 30.', 'date' => '2025-04-22'],
        ],
        'library' => [
            ['id' => 1, 'title' => 'Mathematics 101', 'author' => 'John Author', 'isbn' => '1234567890'],
            ['id' => 2, 'title' => 'Physics for Beginners', 'author' => 'Dr. Alice', 'isbn' => '0987654321'],
            ['id' => 3, 'title' => 'Classic Literature', 'author' => 'Various', 'isbn' => '1122334455'],
            ['id' => 4, 'title' => 'Chemistry Basics', 'author' => '', 'isbn' => '2233445566'], // Missing author
        ],
        'library_transactions' => [
            ['id' => 1, 'book_title' => 'Mathematics 101', 'user_name' => 'John Doe', 'issue_date' => '2025-04-18', 'status' => 'Issued'],
            ['id' => 2, 'book_title' => 'Physics for Beginners', 'user_name' => 'Aisha Khan', 'issue_date' => '2025-04-17', 'status' => 'Returned'],
            ['id' => 3, 'book_title' => 'Classic Literature', 'user_name' => 'Michael Chen', 'issue_date' => '2025-04-19', 'status' => 'Issued'],
            ['id' => 4, 'book_title' => 'Chemistry Basics', 'user_name' => 'Priya Sharma', 'issue_date' => '2025-04-16', 'status' => 'Overdue'],
        ],
        'inventory' => [
            ['id' => 1, 'item_name' => 'Projector', 'quantity' => 5, 'status' => 'Available'],
            ['id' => 2, 'item_name' => 'Laptop', 'quantity' => 10, 'status' => 'Available'],
            ['id' => 3, 'item_name' => 'Microscope', 'quantity' => 3, 'status' => 'In Use'],
            ['id' => 4, 'item_name' => 'Whiteboard', 'quantity' => 8, 'status' => 'Available'],
            ['id' => 5, 'item_name' => 'Sports Kit', 'quantity' => 2, 'status' => 'Damaged'],
        ],
        'inventory_transactions' => [
            ['id' => 1, 'item_name' => 'Projector', 'user_name' => 'Jane Smith', 'issue_date' => '2025-04-18', 'status' => 'Issued'],
            ['id' => 2, 'item_name' => 'Laptop', 'user_name' => 'Robert Patel', 'issue_date' => '2025-04-17', 'status' => 'Returned'],
            ['id' => 3, 'item_name' => 'Microscope', 'user_name' => 'Ahmed Zaki', 'issue_date' => '2025-04-19', 'status' => 'Issued'],
        ],
        'chats' => [
            ['id' => 1, 'sender' => 'John Doe', 'receiver' => 'Jane Smith', 'message' => 'Meeting at 3 PM?', 'timestamp' => '2025-04-18 14:00'],
            ['id' => 2, 'sender' => 'Jane Smith', 'receiver' => 'John Doe', 'message' => 'Yes, confirmed.', 'timestamp' => '2025-04-18 14:05'],
            ['id' => 3, 'sender' => 'Aisha Khan', 'receiver' => 'Robert Patel', 'message' => 'Need help with physics homework.', 'timestamp' => '2025-04-19 09:30'],
            ['id' => 4, 'sender' => 'Robert Patel', 'receiver' => 'Aisha Khan', 'message' => 'Come to my office at 11 AM.', 'timestamp' => '2025-04-19 09:35'],
            ['id' => 5, 'sender' => 'Michael Chen', 'receiver' => 'Linda Brown', 'message' => 'Can we discuss the essay?', 'timestamp' => '2025-04-19 10:00'],
        ],
        'reports' => [
            ['id' => 1, 'type' => 'Attendance', 'date' => '2025-04-18', 'summary' => '90% attendance rate'],
            ['id' => 2, 'type' => 'Fee Collection', 'date' => '2025-04-19', 'summary' => '75% fees collected'],
            ['id' => 3, 'type' => 'Exam Performance', 'date' => '2025-04-20', 'summary' => 'Average score: 82%'],
        ],
        'results' => [
            ['id' => 1, 'student_name' => 'John Doe', 'exam' => 'Midterm', 'marks' => 85],
            ['id' => 2, 'student_name' => 'Aisha Khan', 'exam' => 'Midterm', 'marks' => 92],
            ['id' => 3, 'student_name' => 'Michael Chen', 'exam' => 'Quarterly', 'marks' => 78],
            ['id' => 4, 'student_name' => 'Priya Sharma', 'exam' => 'Midterm', 'marks' => 88],
            ['id' => 5, 'student_name' => 'Carlos Rivera', 'exam' => 'Unit Test', 'marks' => 90],
            ['id' => 6, 'student_name' => 'Emma Wilson', 'exam' => 'Midterm', 'marks' => 76],
        ],
        'fee_templates' => [
            ['id' => 1, 'name' => 'Annual Fee', 'amount' => 1000, 'frequency' => 'Yearly'],
            ['id' => 2, 'name' => 'Monthly Tuition', 'amount' => 200, 'frequency' => 'Monthly'],
            ['id' => 3, 'name' => 'Transport Fee', 'amount' => 150, 'frequency' => 'Monthly'],
            ['id' => 4, 'name' => 'Lab Fee', 'amount' => 50, 'frequency' => 'Quarterly'],
        ],
        'transport' => [
            ['id' => 1, 'route' => 'Route A', 'vehicle' => 'Bus 101', 'capacity' => 50],
            ['id' => 2, 'route' => 'Route B', 'vehicle' => 'Van 202', 'capacity' => 20],
            ['id' => 3, 'route' => 'Route C', 'vehicle' => 'Bus 103', 'capacity' => 45],
        ],
        'transport_enrollments' => [
            ['id' => 1, 'student_name' => 'John Doe', 'route' => 'Route A', 'status' => 'Active'],
            ['id' => 2, 'student_name' => 'Aisha Khan', 'route' => 'Route B', 'status' => 'Active'],
            ['id' => 3, 'student_name' => 'Michael Chen', 'route' => 'Route A', 'status' => 'Inactive'],
            ['id' => 4, 'student_name' => 'Priya Sharma', 'route' => 'Route C', 'status' => 'Active'],
        ],
        'departments' => [
            ['id' => 1, 'name' => 'Science', 'head' => 'Jane Smith'],
            ['id' => 2, 'name' => 'Arts', 'head' => 'Linda Brown'],
            ['id' => 3, 'name' => 'Mathematics', 'head' => 'Robert Patel'],
        ],
        'subjects' => [
            ['id' => 1, 'name' => 'Mathematics', 'teacher' => 'Jane Smith', 'class' => '10'],
            ['id' => 2, 'name' => 'Physics', 'teacher' => 'Robert Patel', 'class' => '11'],
            ['id' => 3, 'name' => 'English', 'teacher' => 'Linda Brown', 'class' => '9'],
            ['id' => 4, 'name' => 'Chemistry', 'teacher' => 'Ahmed Zaki', 'class' => '10'],
            ['id' => 5, 'name' => 'Biology', 'teacher' => 'Ahmed Zaki', 'class' => '11'],
        ],
        'timetable' => [
            ['id' => 1, 'class' => '10', 'day' => 'Monday', 'period' => 1, 'subject' => 'Mathematics', 'teacher' => 'Jane Smith', 'time' => '08:00-09:00'],
            ['id' => 2, 'class' => '10', 'day' => 'Monday', 'period' => 2, 'subject' => 'English', 'teacher' => 'Linda Brown', 'time' => '09:00-10:00'],
            ['id' => 3, 'class' => '11', 'day' => 'Tuesday', 'period' => 1, 'subject' => 'Physics', 'teacher' => 'Robert Patel', 'time' => '08:00-09:00'],
            ['id' => 4, 'class' => '9', 'day' => 'Wednesday', 'period' => 3, 'subject' => 'English', 'teacher' => 'Linda Brown', 'time' => '10:00-11:00'],
            ['id' => 5, 'class' => '10', 'day' => 'Friday', 'period' => 4, 'subject' => 'Chemistry', 'teacher' => 'Ahmed Zaki', 'time' => '11:00-12:00'],
        ],
        'homework' => [
            ['id' => 1, 'subject' => 'Mathematics', 'class' => '10', 'due_date' => '2025-04-20', 'description' => 'Solve chapter 5 exercises'],
            ['id' => 2, 'subject' => 'English', 'class' => '9', 'due_date' => '2025-04-22', 'description' => 'Write an essay on Shakespeare'],
            ['id' => 3, 'subject' => 'Physics', 'class' => '11', 'due_date' => '2025-04-21', 'description' => 'Complete lab report'],
            ['id' => 4, 'subject' => 'Chemistry', 'class' => '10', 'due_date' => '2025-04-23', 'description' => 'Balance 10 chemical equations'],
        ],
        'classes_sections' => [
            ['id' => 1, 'class' => '10', 'section' => 'A', 'teacher' => 'Jane Smith'],
            ['id' => 2, 'class' => '10', 'section' => 'B', 'teacher' => 'Robert Patel'],
            ['id' => 3, 'class' => '11', 'section' => 'A', 'teacher' => 'Ahmed Zaki'],
            ['id' => 4, 'class' => '9', 'section' => 'A', 'teacher' => 'Linda Brown'],
        ],
        'subscriptions' => [
            ['id' => 1, 'plan' => 'Premium', 'start_date' => '2025-01-01', 'end_date' => '2025-12-31', 'status' => 'Active'],
            ['id' => 2, 'plan' => 'Basic', 'start_date' => '2025-03-01', 'end_date' => '2025-08-31', 'status' => 'Active'],
            ['id' => 3, 'plan' => 'Premium', 'start_date' => '2025-02-15', 'end_date' => '2026-02-14', 'status' => 'Active'],
            ['id' => 4, 'plan' => 'Standard', 'start_date' => '2025-04-01', 'end_date' => '2025-09-30', 'status' => 'Inactive'],
        ],
        'parents' => [
            ['id' => 1, 'parent_name' => 'Mary Doe', 'contact' => '0123456789', 'email' => 'mary.doe@example.com', 'student_name' => 'John Doe'],
            ['id' => 2, 'parent_name' => 'Rahul Khan', 'contact' => '0987654321', 'email' => 'rahul.k@example.com', 'student_name' => 'Aisha Khan'],
            ['id' => 3, 'parent_name' => 'Li Chen', 'contact' => '1122334455', 'email' => '', 'student_name' => 'Michael Chen'], // Missing email
            ['id' => 4, 'parent_name' => 'Anita Sharma', 'contact' => '2233445566', 'email' => 'anita.s@example.com', 'student_name' => 'Priya Sharma'],
            ['id' => 5, 'parent_name' => 'Maria Rivera', 'contact' => '3344556677', 'email' => 'maria.r@example.com', 'student_name' => 'Carlos Rivera'],
        ],
    ];
}

// Updated Content Rendering Function
function demoRenderInstituteAdminContent($section, $action, $data) {
    ob_start();
    switch ($section) {
        case 'overview':
            ?>
            <div class="dashboard-section">
                <h2>Institute Admin Dashboard</h2>
                <div class="alert alert-info">Welcome to the <?php echo esc_html($data['institute']); ?> Admin Dashboard.</div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Students</h5>
                                <p class="card-text"><?php echo count($data['students']); ?> Students</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Teachers</h5>
                                <p class="card-text"><?php echo count($data['teachers']); ?> Teachers</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Staff</h5>
                                <p class="card-text"><?php echo count($data['staff']); ?> Staff</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            break;

        case 'students':
            if ($action === 'add-student') {
                echo demoRenderInstituteAdminAddStudent();
            } elseif ($action === 'edit-student') {
                echo demoRenderInstituteAdminEditStudent();
            } elseif ($action === 'delete-student') {
                echo demoRenderInstituteAdminDeleteStudent();
            } else {
                echo demoRenderInstituteAdminStudents($data);
            }
            break;

        case 'teachers':
            if ($action === 'add-teacher') {
                echo demoRenderInstituteAdminAddTeacher();
            } elseif ($action === 'edit-teacher') {
                echo demoRenderInstituteAdminEditTeacher();
            } elseif ($action === 'delete-teacher') {
                echo demoRenderInstituteAdminDeleteTeacher();
            } else {
                echo demoRenderInstituteAdminTeachers($data);
            }
            break;

        case 'staff':
            if ($action === 'add-staff') {
                echo demoRenderInstituteAdminAddStaff();
            } elseif ($action === 'edit-staff') {
                echo demoRenderInstituteAdminEditStaff();
            } elseif ($action === 'delete-staff') {
                echo demoRenderInstituteAdminDeleteStaff();
            } else {
                echo demoRenderInstituteAdminStaff($data);
            }
            break;

        case 'exams':
            if ($action === 'add-exam') {
                echo demoRenderInstituteAdminAddExam();
            } elseif ($action === 'edit-exam') {
                echo demoRenderInstituteAdminEditExam();
            } elseif ($action === 'delete-exam') {
                echo demoRenderInstituteAdminDeleteExam();
            } else {
                echo demoRenderInstituteAdminExams($data);
            }
            break;

        case 'attendance':
            if ($action === 'manage-student-attendance') {
                echo demoRenderInstituteAdminStudentAttendance($data);
            } elseif ($action === 'add-student-attendance') {
                echo demoRenderInstituteAdminAddStudentAttendance();
            } elseif ($action === 'edit-student-attendance') {
                echo demoRenderInstituteAdminEditStudentAttendance();
            } elseif ($action === 'delete-student-attendance') {
                echo demoRenderInstituteAdminDeleteStudentAttendance();
            } elseif ($action === 'manage-teacher-attendance') {
                echo demoRenderInstituteAdminTeacherAttendance($data);
            } elseif ($action === 'add-teacher-attendance') {
                echo demoRenderInstituteAdminAddTeacherAttendance();
            } elseif ($action === 'edit-teacher-attendance') {
                echo demoRenderInstituteAdminEditTeacherAttendance();
            } elseif ($action === 'delete-teacher-attendance') {
                echo demoRenderInstituteAdminDeleteTeacherAttendance();
            } elseif ($action === 'manage-staff-attendance') {
                echo demoRenderInstituteAdminStaffAttendance($data);
            } elseif ($action === 'add-staff-attendance') {
                echo demoRenderInstituteAdminAddStaffAttendance();
            } elseif ($action === 'edit-staff-attendance') {
                echo demoRenderInstituteAdminEditStaffAttendance();
            } elseif ($action === 'delete-staff-attendance') {
                echo demoRenderInstituteAdminDeleteStaffAttendance();
            } else {
                echo demoRenderInstituteAdminAttendance($data);
            }
            break;

        case 'fees':
            if ($action === 'add-fee') {
                echo demoRenderInstituteAdminAddFee();
            } elseif ($action === 'edit-fee') {
                echo demoRenderInstituteAdminEditFee();
            } elseif ($action === 'delete-fee') {
                echo demoRenderInstituteAdminDeleteFee();
            } else {
                echo demoRenderInstituteAdminFees($data);
            }
            break;

        case 'notices':
            if ($action === 'add-notice') {
                echo demoRenderInstituteAdminAddNotice();
            } elseif ($action === 'edit-notice') {
                echo demoRenderInstituteAdminEditNotice();
            } elseif ($action === 'delete-notice') {
                echo demoRenderInstituteAdminDeleteNotice();
            } else {
                echo demoRenderInstituteAdminNotices($data);
            }
            break;

        case 'announcements':
            if ($action === 'add-announcement') {
                echo demoRenderInstituteAdminAddAnnouncement();
            } elseif ($action === 'edit-announcement') {
                echo demoRenderInstituteAdminEditAnnouncement();
            } elseif ($action === 'delete-announcement') {
                echo demoRenderInstituteAdminDeleteAnnouncement();
            } else {
                echo demoRenderInstituteAdminAnnouncements($data);
            }
            break;

        case 'library':
            if ($action === 'add-book') {
                echo demoRenderInstituteAdminAddBook();
            } elseif ($action === 'edit-book') {
                echo demoRenderInstituteAdminEditBook();
            } elseif ($action === 'delete-book') {
                echo demoRenderInstituteAdminDeleteBook();
            } else {
                echo demoRenderInstituteAdminLibrary($data);
            }
            break;

        case 'library_transactions':
            if ($action === 'add-transaction') {
                echo demoRenderInstituteAdminAddLibraryTransaction();
            } elseif ($action === 'edit-transaction') {
                echo demoRenderInstituteAdminEditLibraryTransaction();
            } elseif ($action === 'delete-transaction') {
                echo demoRenderInstituteAdminDeleteLibraryTransaction();
            } else {
                echo demoRenderInstituteAdminLibraryTransactions($data);
            }
            break;

        case 'inventory':
            if ($action === 'add-item') {
                echo demoRenderInstituteAdminAddInventoryItem();
            } elseif ($action === 'edit-item') {
                echo demoRenderInstituteAdminEditInventoryItem();
            } elseif ($action === 'delete-item') {
                echo demoRenderInstituteAdminDeleteInventoryItem();
            } else {
                echo demoRenderInstituteAdminInventory($data);
            }
            break;

        case 'inventory_transactions':
            if ($action === 'add-transaction') {
                echo demoRenderInstituteAdminAddInventoryTransaction();
            } elseif ($action === 'edit-transaction') {
                echo demoRenderInstituteAdminEditInventoryTransaction();
            } elseif ($action === 'delete-transaction') {
                echo demoRenderInstituteAdminDeleteInventoryTransaction();
            } else {
                echo demoRenderInstituteAdminInventoryTransactions($data);
            }
            break;

        case 'chats':
            if ($action === 'view-chat') {
                echo demoRenderInstituteAdminViewChat();
            } else {
                echo demoRenderInstituteAdminChats($data);
            }
            break;

        case 'reports':
            if ($action === 'generate-report') {
                echo demoRenderInstituteAdminGenerateReport();
            } else {
                echo demoRenderInstituteAdminReports($data);
            }
            break;

        case 'results':
            if ($action === 'add-result') {
                echo demoRenderInstituteAdminAddResult();
            } elseif ($action === 'edit-result') {
                echo demoRenderInstituteAdminEditResult();
            } elseif ($action === 'delete-result') {
                echo demoRenderInstituteAdminDeleteResult();
            } else {
                echo demoRenderInstituteAdminResults($data);
            }
            break;

        case 'fee_templates':
            if ($action === 'add-template') {
                echo demoRenderInstituteAdminAddFeeTemplate();
            } elseif ($action === 'edit-template') {
                echo demoRenderInstituteAdminEditFeeTemplate();
            } elseif ($action === 'delete-template') {
                echo demoRenderInstituteAdminDeleteFeeTemplate();
            } else {
                echo demoRenderInstituteAdminFeeTemplates($data);
            }
            break;

        case 'transport':
            if ($action === 'add-route') {
                echo demoRenderInstituteAdminAddTransportRoute();
            } elseif ($action === 'edit-route') {
                echo demoRenderInstituteAdminEditTransportRoute();
            } elseif ($action === 'delete-route') {
                echo demoRenderInstituteAdminDeleteTransportRoute();
            } else {
                echo demoRenderInstituteAdminTransport($data);
            }
            break;

        case 'transport_enrollments':
            if ($action === 'add-enrollment') {
                echo demoRenderInstituteAdminAddTransportEnrollment();
            } elseif ($action === 'edit-enrollment') {
                echo demoRenderInstituteAdminEditTransportEnrollment();
            } elseif ($action === 'delete-enrollment') {
                echo demoRenderInstituteAdminDeleteTransportEnrollment();
            } else {
                echo demoRenderInstituteAdminTransportEnrollments($data);
            }
            break;

        case 'departments':
            if ($action === 'add-department') {
                echo demoRenderInstituteAdminAddDepartment();
            } elseif ($action === 'edit-department') {
                echo demoRenderInstituteAdminEditDepartment();
            } elseif ($action === 'delete-department') {
                echo demoRenderInstituteAdminDeleteDepartment();
            } else {
                echo demoRenderInstituteAdminDepartments($data);
            }
            break;

        case 'subjects':
            if ($action === 'add-subject') {
                echo demoRenderInstituteAdminAddSubject();
            } elseif ($action === 'edit-subject') {
                echo demoRenderInstituteAdminEditSubject();
            } elseif ($action === 'delete-subject') {
                echo demoRenderInstituteAdminDeleteSubject();
            } else {
                echo demoRenderInstituteAdminSubjects($data);
            }
            break;

        case 'timetable':
            if ($action === 'add-timetable') {
                echo demoRenderInstituteAdminAddTimetable();
            } elseif ($action === 'edit-timetable') {
                echo demoRenderInstituteAdminEditTimetable();
            } elseif ($action === 'delete-timetable') {
                echo demoRenderInstituteAdminDeleteTimetable();
            } else {
                echo demoRenderInstituteAdminTimetable($data);
            }
            break;

        case 'homework':
            if ($action === 'add-homework') {
                echo demoRenderInstituteAdminAddHomework();
            } elseif ($action === 'edit-homework') {
                echo demoRenderInstituteAdminEditHomework();
            } elseif ($action === 'delete-homework') {
                echo demoRenderInstituteAdminDeleteHomework();
            } else {
                echo demoRenderInstituteAdminHomework($data);
            }
            break;

        case 'classes_sections':
            if ($action === 'add-class-section') {
                echo demoRenderInstituteAdminAddClassSection();
            } elseif ($action === 'edit-class-section') {
                echo demoRenderInstituteAdminEditClassSection();
            } elseif ($action === 'delete-class-section') {
                echo demoRenderInstituteAdminDeleteClassSection();
            } else {
                echo demoRenderInstituteAdminClassesSections($data);
            }
            break;

        case 'subscriptions':
            if ($action === 'extend-subscription') {
                echo demoRenderInstituteAdminExtendSubscription();
            } else {
                echo demoRenderInstituteAdminSubscriptions($data);
            }
            break;

        case 'parents':
            if ($action === 'add-parent') {
                echo demoRenderInstituteAdminAddParent();
            } elseif ($action === 'edit-parent') {
                echo demoRenderInstituteAdminEditParent();
            } elseif ($action === 'delete-parent') {
                echo demoRenderInstituteAdminDeleteParent();
            } else {
                echo demoRenderInstituteAdminParents($data);
            }
            break;

        default:
            ?>
            <div class="dashboard-section">
                <h2>Invalid Section</h2>
                <p>Please select a valid section.</p>
            </div>
            <?php
            break;
    }
    return ob_get_clean();
}

// Existing Rendering Functions (Assumed from Previous Responses)
// Note: These should already exist in your plugin from prior responses. Included here for reference.
// Students
function demoRenderInstituteAdminStudents($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Students Management</h2>
        <div class="alert alert-info">Manage students for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students', 'demo-action' => 'add-student'])); ?>" class="btn btn-primary">Add New Student</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Grade</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['students'])): ?>
                        <?php foreach ($data['students'] as $student): ?>
                            <tr>
                                <td><?php echo esc_html($student['id']); ?></td>
                                <td><?php echo esc_html($student['name']); ?></td>
                                <td><?php echo esc_html($student['email']); ?></td>
                                <td><?php echo esc_html($student['grade']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students', 'demo-action' => 'edit-student', 'id' => $student['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students', 'demo-action' => 'delete-student', 'id' => $student['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No students found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddStudent() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Student</h2>
        <div class="alert alert-info">Fill in the details to add a new student.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="student_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="student_email" name="student_email" required>
            </div>
            <div class="mb-3">
                <label for="student_grade" class="form-label">Grade</label>
                <input type="text" class="form-control" id="student_grade" name="student_grade" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Student</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditStudent() {
    ob_start();
    $student_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Student</h2>
        <div class="alert alert-info">Update the details for student ID: <?php echo esc_html($student_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="Sample Student" required>
            </div>
            <div class="mb-3">
                <label for="student_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="student_email" name="student_email" value="student@example.com" required>
            </div>
            <div class="mb-3">
                <label for="student_grade" class="form-label">Grade</label>
                <input type="text" class="form-control" id="student_grade" name="student_grade" value="10" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Student</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteStudent() {
    ob_start();
    $student_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Student</h2>
        <div class="alert alert-danger">Student ID: <?php echo esc_html($student_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'students'])); ?>" class="btn btn-primary">Back to Students</a>
    </div>
    <?php
    return ob_get_clean();
}

// Teachers
function demoRenderInstituteAdminTeachers($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Teachers Management</h2>
        <div class="alert alert-info">Manage teachers for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers', 'demo-action' => 'add-teacher'])); ?>" class="btn btn-primary">Add New Teacher</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['teachers'])): ?>
                        <?php foreach ($data['teachers'] as $teacher): ?>
                            <tr>
                                <td><?php echo esc_html($teacher['id']); ?></td>
                                <td><?php echo esc_html($teacher['name']); ?></td>
                                <td><?php echo esc_html($teacher['email']); ?></td>
                                <td><?php echo esc_html($teacher['subject']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers', 'demo-action' => 'edit-teacher', 'id' => $teacher['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers', 'demo-action' => 'delete-teacher', 'id' => $teacher['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this teacher?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No teachers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddTeacher() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Teacher</h2>
        <div class="alert alert-info">Fill in the details to add a new teacher.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="teacher_name" class="form-label">Teacher Name</label>
                <input type="text" class="form-control" id="teacher_name" name="teacher_name" required>
            </div>
            <div class="mb-3">
                <label for="teacher_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="teacher_email" name="teacher_email" required>
            </div>
            <div class="mb-3">
                <label for="teacher_subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="teacher_subject" name="teacher_subject" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Teacher</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditTeacher() {
    ob_start();
    $teacher_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Teacher</h2>
        <div class="alert alert-info">Update the details for teacher ID: <?php echo esc_html($teacher_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="teacher_name" class="form-label">Teacher Name</label>
                <input type="text" class="form-control" id="teacher_name" name="teacher_name" value="Sample Teacher" required>
            </div>
            <div class="mb-3">
                <label for="teacher_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="teacher_email" name="teacher_email" value="teacher@example.com" required>
            </div>
            <div class="mb-3">
                <label for="teacher_subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="teacher_subject" name="teacher_subject" value="Mathematics" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Teacher</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteTeacher() {
    ob_start();
    $teacher_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Teacher</h2>
        <div class="alert alert-danger">Teacher ID: <?php echo esc_html($teacher_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'teachers'])); ?>" class="btn btn-primary">Back to Teachers</a>
    </div>
    <?php
    return ob_get_clean();
}

// Staff
function demoRenderInstituteAdminStaff($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Staff Management</h2>
        <div class="alert alert-info">Manage staff for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'staff', 'demo-action' => 'add-staff'])); ?>" class="btn btn-primary">Add New Staff</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['staff'])): ?>
                        <?php foreach ($data['staff'] as $staff): ?>
                            <tr>
                                <td><?php echo esc_html($staff['id']); ?></td>
                                <td><?php echo esc_html($staff['name']); ?></td>
                                <td><?php echo esc_html($staff['email']); ?></td>
                                <td><?php echo esc_html($staff['role']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'staff', 'demo-action' => 'edit-staff', 'id' => $staff['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'staff', 'demo-action' => 'delete-staff', 'id' => $staff['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this staff?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No staff found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddStaff() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Staff</h2>
        <div class="alert alert-info">Fill in the details to add a new staff member.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="staff_name" class="form-label">Staff Name</label>
                <input type="text" class="form-control" id="staff_name" name="staff_name" required>
            </div>
            <div class="mb-3">
                <label for="staff_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="staff_email" name="staff_email" required>
            </div>
            <div class="mb-3">
                <label for="staff_role" class="form-label">Role</label>
                <input type="text" class="form-control" id="staff_role" name="staff_role" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Staff</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'staff'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditStaff() {
    ob_start();
    $staff_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Staff</h2>
        <div class="alert alert-info">Update the details for staff ID: <?php echo esc_html($staff_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="staff_name" class="form-label">Staff Name</label>
                <input type="text" class="form-control" id="staff_name" name="staff_name" value="Sample Staff" required>
            </div>
            <div class="mb-3">
                <label for="staff_email" class="form-label">Email</label>
                <input type="email" class="form-control" id="staff_email" name="staff_email" value="staff@example.com" required>
            </div>
            <div class="mb-3">
                <label for="staff_role" class="form-label">Role</label>
                <input type="text" class="form-control" id="staff_role" name="staff_role" value="Librarian" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Staff</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'staff'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteStaff() {
    ob_start();
    $staff_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Staff</h2>
        <div class="alert alert-danger">Staff ID: <?php echo esc_html($staff_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'staff'])); ?>" class="btn btn-primary">Back to Staff</a>
    </div>
    <?php
    return ob_get_clean();
}

// Exams
function demoRenderInstituteAdminExams($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Exams Management</h2>
        <div class="alert alert-info">Manage exams for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams', 'demo-action' => 'add-exam'])); ?>" class="btn btn-primary">Add New Exam</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Exam Name</th>
                        <th>Date</th>
                        <th>Subject</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['exams'])): ?>
                        <?php foreach ($data['exams'] as $exam): ?>
                            <tr>
                                <td><?php echo esc_html($exam['id']); ?></td>
                                <td><?php echo esc_html($exam['name']); ?></td>
                                <td><?php echo esc_html($exam['date']); ?></td>
                                <td><?php echo esc_html($exam['subject']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams', 'demo-action' => 'edit-exam', 'id' => $exam['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams', 'demo-action' => 'delete-exam', 'id' => $exam['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this exam?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No exams found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddExam() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Exam</h2>
        <div class="alert alert-info">Fill in the details to add a new exam.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" required>
            </div>
            <div class="mb-3">
                <label for="exam_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="exam_date" name="exam_date" required>
            </div>
            <div class="mb-3">
                <label for="exam_subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="exam_subject" name="exam_subject" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Exam</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditExam() {
    ob_start();
    $exam_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Exam</h2>
        <div class="alert alert-info">Update the details for exam ID: <?php echo esc_html($exam_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" value="Sample Exam" required>
            </div>
            <div class="mb-3">
                <label for="exam_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="exam_date" name="exam_date" value="2025-05-01" required>
            </div>
            <div class="mb-3">
                <label for="exam_subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="exam_subject" name="exam_subject" value="Mathematics" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Exam</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteExam() {
    ob_start();
    $exam_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Exam</h2>
        <div class="alert alert-danger">Exam ID: <?php echo esc_html($exam_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'exams'])); ?>" class="btn btn-primary">Back to Exams</a>
    </div>
    <?php
    return ob_get_clean();
}

// Attendance
function demoRenderInstituteAdminAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Attendance Management</h2>
        <div class="alert alert-info">Manage attendance for students, teachers, and staff at <?php echo esc_html($data['institute']); ?>.</div>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Student Attendance</h5>
                        <p class="card-text">Manage student attendance records.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-primary">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Teacher Attendance</h5>
                        <p class="card-text">Manage teacher attendance records.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="btn btn-primary">Manage</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Staff Attendance</h5>
                        <p class="card-text">Manage staff attendance records.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-staff-attendance'])); ?>" class="btn btn-primary">Manage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminStudentAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Student Attendance</h2>
        <div class="alert alert-info">Manage student attendance for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'add-student-attendance'])); ?>" class="btn btn-primary">Add New Attendance</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['student_attendance'])): ?>
                        <?php foreach ($data['student_attendance'] as $attendance): ?>
                            <tr>
                                <td><?php echo esc_html($attendance['id']); ?></td>
                                <td><?php echo esc_html($attendance['student_name']); ?></td>
                                <td><?php echo esc_html($attendance['date']); ?></td>
                                <td><?php echo esc_html($attendance['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'edit-student-attendance', 'id' => $attendance['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'delete-student-attendance', 'id' => $attendance['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this attendance record?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddStudentAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Student Attendance</h2>
        <div class="alert alert-info">Fill in the details to add a new student attendance record.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="attendance_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="attendance_date" name="attendance_date" required>
            </div>
            <div class="mb-3">
                <label for="attendance_status" class="form-label">Status</label>
                <select class="form-control" id="attendance_status" name="attendance_status" required>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditStudentAttendance() {
    ob_start();
    $attendance_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Student Attendance</h2>
        <div class="alert alert-info">Update the details for attendance ID: <?php echo esc_html($attendance_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="attendance_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="attendance_date" name="attendance_date" value="2025-04-18" required>
            </div>
            <div class="mb-3">
                <label for="attendance_status" class="form-label">Status</label>
                <select class="form-control" id="attendance_status" name="attendance_status" required>
                    <option value="Present" selected>Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteStudentAttendance() {
    ob_start();
    $attendance_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Student Attendance</h2>
        <div class="alert alert-danger">Attendance ID: <?php echo esc_html($attendance_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-student-attendance'])); ?>" class="btn btn-primary">Back to Student Attendance</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminTeacherAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Teacher Attendance</h2>
        <div class="alert alert-info">Manage teacher attendance for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'add-teacher-attendance'])); ?>" class="btn btn-primary">Add New Attendance</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teacher Name</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['teacher_attendance'])): ?>
                        <?php foreach ($data['teacher_attendance'] as $attendance): ?>
                            <tr>
                                <td><?php echo esc_html($attendance['id']); ?></td>
                                <td><?php echo esc_html($attendance['teacher_name']); ?></td>
                                <td><?php echo esc_html($attendance['date']); ?></td>
                                <td><?php echo esc_html($attendance['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'edit-teacher-attendance', 'id' => $attendance['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'delete-teacher-attendance', 'id' => $attendance['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this attendance record?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddTeacherAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Teacher Attendance</h2>
        <div class="alert alert-info">Fill in the details to add a new teacher attendance record.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="teacher_name" class="form-label">Teacher Name</label>
                <input type="text" class="form-control" id="teacher_name" name="teacher_name" required>
            </div>
            <div class="mb-3">
                <label for="attendance_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="attendance_date" name="attendance_date" required>
            </div>
            <div class="mb-3">
                <label for="attendance_status" class="form-label">Status</label>
                <select class="form-control" id="attendance_status" name="attendance_status" required>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditTeacherAttendance() {
    ob_start();
    $attendance_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Teacher Attendance</h2>
        <div class="alert alert-info">Update the details for attendance ID: <?php echo esc_html($attendance_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="teacher_name" class="form-label">Teacher Name</label>
                <input type="text" class="form-control" id="teacher_name" name="teacher_name" value="Jane Smith" required>
            </div>
            <div class="mb-3">
                <label for="attendance_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="attendance_date" name="attendance_date" value="2025-04-18" required>
            </div>
            <div class="mb-3">
                <label for="attendance_status" class="form-label">Status</label>
                <select class="form-control" id="attendance_status" name="attendance_status" required>
                    <option value="Present" selected>Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteTeacherAttendance() {
    ob_start();
    $attendance_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Teacher Attendance</h2>
        <div class="alert alert-danger">Attendance ID: <?php echo esc_html($attendance_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-teacher-attendance'])); ?>" class="btn btn-primary">Back to Teacher Attendance</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminStaffAttendance($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Staff Attendance</h2>
        <div class="alert alert-info">Manage staff attendance for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'add-staff-attendance'])); ?>" class="btn btn-primary">Add New Attendance</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Staff Name</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['staff_attendance'])): ?>
                        <?php foreach ($data['staff_attendance'] as $attendance): ?>
                            <tr>
                                <td><?php echo esc_html($attendance['id']); ?></td>
                                <td><?php echo esc_html($attendance['staff_name']); ?></td>
                                <td><?php echo esc_html($attendance['date']); ?></td>
                                <td><?php echo esc_html($attendance['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'edit-staff-attendance', 'id' => $attendance['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'delete-staff-attendance', 'id' => $attendance['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this attendance record?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddStaffAttendance() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Staff Attendance</h2>
        <div class="alert alert-info">Fill in the details to add a new staff attendance record.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="staff_name" class="form-label">Staff Name</label>
                <input type="text" class="form-control" id="staff_name" name="staff_name" required>
            </div>
            <div class="mb-3">
                <label for="attendance_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="attendance_date" name="attendance_date" required>
            </div>
            <div class="mb-3">
                <label for="attendance_status" class="form-label">Status</label>
                <select class="form-control" id="attendance_status" name="attendance_status" required>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-staff-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditStaffAttendance() {
    ob_start();
    $attendance_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Staff Attendance</h2>
        <div class="alert alert-info">Update the details for attendance ID: <?php echo esc_html($attendance_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="staff_name" class="form-label">Staff Name</label>
                <input type="text" class="form-control" id="staff_name" name="staff_name" value="Mike Brown" required>
            </div>
            <div class="mb-3">
                <label for="attendance_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="attendance_date" name="attendance_date" value="2025-04-18" required>
            </div>
            <div class="mb-3">
                <label for="attendance_status" class="form-label">Status</label>
                <select class="form-control" id="attendance_status" name="attendance_status" required>
                    <option value="Present" selected>Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-staff-attendance'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteStaffAttendance() {
    ob_start();
    $attendance_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Staff Attendance</h2>
        <div class="alert alert-danger">Attendance ID: <?php echo esc_html($attendance_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'attendance', 'demo-action' => 'manage-staff-attendance'])); ?>" class="btn btn-primary">Back to Staff Attendance</a>
    </div>
    <?php
    return ob_get_clean();
}

// Fees
function demoRenderInstituteAdminFees($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Fees Management</h2>
        <div class="alert alert-info">Manage fees for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees', 'demo-action' => 'add-fee'])); ?>" class="btn btn-primary">Add New Fee</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Amount</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['fees'])): ?>
                        <?php foreach ($data['fees'] as $fee): ?>
                            <tr>
                                <td><?php echo esc_html($fee['id']); ?></td>
                                <td><?php echo esc_html($fee['student_name']); ?></td>
                                <td><?php echo esc_html($fee['amount']); ?></td>
                                <td><?php echo esc_html($fee['due_date']); ?></td>
                                <td><?php echo esc_html($fee['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees', 'demo-action' => 'edit-fee', 'id' => $fee['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees', 'demo-action' => 'delete-fee', 'id' => $fee['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this fee?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No fees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddFee() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Fee</h2>
        <div class="alert alert-info">Fill in the details to add a new fee.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="fee_amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="fee_amount" name="fee_amount" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>
            <div class="mb-3">
                <label for="fee_status" class="form-label">Status</label>
                <select class="form-control" id="fee_status" name="fee_status" required>
                    <option value="Pending">Pending</option>
                    <option value="Paid">Paid</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Fee</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditFee() {
    ob_start();
    $fee_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Fee</h2>
        <div class="alert alert-info">Update the details for fee ID: <?php echo esc_html($fee_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="fee_amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="fee_amount" name="fee_amount" value="500" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="2025-05-01" required>
            </div>
            <div class="mb-3">
                <label for="fee_status" class="form-label">Status</label>
                <select class="form-control" id="fee_status" name="fee_status" required>
                    <option value="Pending" selected>Pending</option>
                    <option value="Paid">Paid</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Fee</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteFee() {
    ob_start();
    $fee_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Fee</h2>
        <div class="alert alert-danger">Fee ID: <?php echo esc_html($fee_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fees'])); ?>" class="btn btn-primary">Back to Fees</a>
    </div>
    <?php
    return ob_get_clean();
}

// Notices
function demoRenderInstituteAdminNotices($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Notices Management</h2>
        <div class="alert alert-info">Manage notices for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices', 'demo-action' => 'add-notice'])); ?>" class="btn btn-primary">Add New Notice</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['notices'])): ?>
                        <?php foreach ($data['notices'] as $notice): ?>
                            <tr>
                                <td><?php echo esc_html($notice['id']); ?></td>
                                <td><?php echo esc_html($notice['title']); ?></td>
                                <td><?php echo esc_html($notice['content']); ?></td>
                                <td><?php echo esc_html($notice['date']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices', 'demo-action' => 'edit-notice', 'id' => $notice['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices', 'demo-action' => 'delete-notice', 'id' => $notice['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this notice?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No notices found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddNotice() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Notice</h2>
        <div class="alert alert-info">Fill in the details to add a new notice.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="notice_title" class="form-label">Title</label>
                <input type="text" class="form-control" id="notice_title" name="notice_title" required>
            </div>
            <div class="mb-3">
                <label for="notice_content" class="form-label">Content</label>
                <textarea class="form-control" id="notice_content" name="notice_content" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="notice_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="notice_date" name="notice_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Notice</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditNotice() {
    ob_start();
    $notice_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Notice</h2>
        <div class="alert alert-info">Update the details for notice ID: <?php echo esc_html($notice_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="notice_title" class="form-label">Title</label>
                <input type="text" class="form-control" id="notice_title" name="notice_title" value="Holiday Notice" required>
            </div>
            <div class="mb-3">
                <label for="notice_content" class="form-label">Content</label>
                <textarea class="form-control" id="notice_content" name="notice_content" rows="5" required>School closed on May 1.</textarea>
            </div>
            <div class="mb-3">
                <label for="notice_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="notice_date" name="notice_date" value="2025-04-18" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Notice</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteNotice() {
    ob_start();
    $notice_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Notice</h2>
        <div class="alert alert-danger">Notice ID: <?php echo esc_html($notice_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'notices'])); ?>" class="btn btn-primary">Back to Notices</a>
    </div>
    <?php
    return ob_get_clean();
}

// Announcements
function demoRenderInstituteAdminAnnouncements($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Announcements Management</h2>
        <div class="alert alert-info">Manage announcements for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements', 'demo-action' => 'add-announcement'])); ?>" class="btn btn-primary">Add New Announcement</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['announcements'])): ?>
                        <?php foreach ($data['announcements'] as $announcement): ?>
                            <tr>
                                <td><?php echo esc_html($announcement['id']); ?></td>
                                <td><?php echo esc_html($announcement['title']); ?></td>
                                <td><?php echo esc_html($announcement['content']); ?></td>
                                <td><?php echo esc_html($announcement['date']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements', 'demo-action' => 'edit-announcement', 'id' => $announcement['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements', 'demo-action' => 'delete-announcement', 'id' => $announcement['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this announcement?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No announcements found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddAnnouncement() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Announcement</h2>
        <div class="alert alert-info">Fill in the details to add a new announcement.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="announcement_title" class="form-label">Title</label>
                <input type="text" class="form-control" id="announcement_title" name="announcement_title" required>
            </div>
            <div class="mb-3">
                <label for="announcement_content" class="form-label">Content</label>
                <textarea class="form-control" id="announcement_content" name="announcement_content" rows="5" required></textarea>
            </div>
            <div class="mb-3">
                <label for="announcement_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="announcement_date" name="announcement_date" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Announcement</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditAnnouncement() {
    ob_start();
    $announcement_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Announcement</h2>
        <div class="alert alert-info">Update the details for announcement ID: <?php echo esc_html($announcement_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="announcement_title" class="form-label">Title</label>
                <input type="text" class="form-control" id="announcement_title" name="announcement_title" value="School Event" required>
            </div>
            <div class="mb-3">
                <label for="announcement_content" class="form-label">Content</label>
                <textarea class="form-control" id="announcement_content" name="announcement_content" rows="5" required>Annual day on June 1.</textarea>
            </div>
            <div class="mb-3">
                <label for="announcement_date" class="form-label">Date</label>
                <input type="date" class="form-control" id="announcement_date" name="announcement_date" value="2025-04-18" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Announcement</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteAnnouncement() {
    ob_start();
    $announcement_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Announcement</h2>
        <div class="alert alert-danger">Announcement ID: <?php echo esc_html($announcement_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'announcements'])); ?>" class="btn btn-primary">Back to Announcements</a>
    </div>
    <?php
    return ob_get_clean();
}

// Library
function demoRenderInstituteAdminLibrary($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Library Management</h2>
        <div class="alert alert-info">Manage library books for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library', 'demo-action' => 'add-book'])); ?>" class="btn btn-primary">Add New Book</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['library'])): ?>
                        <?php foreach ($data['library'] as $book): ?>
                            <tr>
                                <td><?php echo esc_html($book['id']); ?></td>
                                <td><?php echo esc_html($book['title']); ?></td>
                                <td><?php echo esc_html($book['author']); ?></td>
                                <td><?php echo esc_html($book['isbn']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library', 'demo-action' => 'edit-book', 'id' => $book['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library', 'demo-action' => 'delete-book', 'id' => $book['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No books found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddBook() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Book</h2>
        <div class="alert alert-info">Fill in the details to add a new book.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="book_title" class="form-label">Title</label>
                <input type="text" class="form-control" id="book_title" name="book_title" required>
            </div>
            <div class="mb-3">
                <label for="book_author" class="form-label">Author</label>
                <input type="text" class="form-control" id="book_author" name="book_author" required>
            </div>
            <div class="mb-3">
                <label for="book_isbn" class="form-label">ISBN</label>
                <input type="text" class="form-control" id="book_isbn" name="book_isbn" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Book</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditBook() {
    ob_start();
    $book_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Book</h2>
        <div class="alert alert-info">Update the details for book ID: <?php echo esc_html($book_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="book_title" class="form-label">Title</label>
                <input type="text" class="form-control" id="book_title" name="book_title" value="Mathematics 101" required>
            </div>
            <div class="mb-3">
                <label for="book_author" class="form-label">Author</label>
                <input type="text" class="form-control" id="book_author" name="book_author" value="John Author" required>
            </div>
            <div class="mb-3">
                <label for="book_isbn" class="form-label">ISBN</label>
                <input type="text" class="form-control" id="book_isbn" name="book_isbn" value="1234567890" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Book</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteBook() {
    ob_start();
    $book_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Book</h2>
        <div class="alert alert-danger">Book ID: <?php echo esc_html($book_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library'])); ?>" class="btn btn-primary">Back to Library</a>
    </div>
    <?php
    return ob_get_clean();
}

// Library Transactions
function demoRenderInstituteAdminLibraryTransactions($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Library Transactions</h2>
        <div class="alert alert-info">Manage library transactions for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions', 'demo-action' => 'add-transaction'])); ?>" class="btn btn-primary">Add New Transaction</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Book Title</th>
                        <th>User Name</th>
                        <th>Issue Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['library_transactions'])): ?>
                        <?php foreach ($data['library_transactions'] as $transaction): ?>
                            <tr>
                                <td><?php echo esc_html($transaction['id']); ?></td>
                                <td><?php echo esc_html($transaction['book_title']); ?></td>
                                <td><?php echo esc_html($transaction['user_name']); ?></td>
                                <td><?php echo esc_html($transaction['issue_date']); ?></td>
                                <td><?php echo esc_html($transaction['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions', 'demo-action' => 'edit-transaction', 'id' => $transaction['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions', 'demo-action' => 'delete-transaction', 'id' => $transaction['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this transaction?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddLibraryTransaction() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Library Transaction</h2>
        <div class="alert alert-info">Fill in the details to add a new library transaction.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="book_title" class="form-label">Book Title</label>
                <input type="text" class="form-control" id="book_title" name="book_title" required>
            </div>
            <div class="mb-3">
                <label for="user_name" class="form-label">User Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" required>
            </div>
            <div class="mb-3">
                <label for="issue_date" class="form-label">Issue Date</label>
                <input type="date" class="form-control" id="issue_date" name="issue_date" required>
            </div>
            <div class="mb-3">
                <label for="transaction_status" class="form-label">Status</label>
                <select class="form-control" id="transaction_status" name="transaction_status" required>
                    <option value="Issued">Issued</option>
                    <option value="Returned">Returned</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Transaction</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditLibraryTransaction() {
    ob_start();
    $transaction_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Library Transaction</h2>
        <div class="alert alert-info">Update the details for transaction ID: <?php echo esc_html($transaction_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="book_title" class="form-label">Book Title</label>
                <input type="text" class="form-control" id="book_title" name="book_title" value="Mathematics 101" required>
            </div>
            <div class="mb-3">
                <label for="user_name" class="form-label">User Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="issue_date" class="form-label">Issue Date</label>
                <input type="date" class="form-control" id="issue_date" name="issue_date" value="2025-04-18" required>
            </div>
            <div class="mb-3">
                <label for="transaction_status" class="form-label">Status</label>
                <select class="form-control" id="transaction_status" name="transaction_status" required>
                    <option value="Issued" selected>Issued</option>
                    <option value="Returned">Returned</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Transaction</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteLibraryTransaction() {
    ob_start();
    $transaction_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Library Transaction</h2>
        <div class="alert alert-danger">Transaction ID: <?php echo esc_html($transaction_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'library_transactions'])); ?>" class="btn btn-primary">Back to Library Transactions</a>
    </div>
    <?php
    return ob_get_clean();
}

// Inventory
function demoRenderInstituteAdminInventory($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Inventory Management</h2>
        <div class="alert alert-info">Manage inventory items for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory', 'demo-action' => 'add-item'])); ?>" class="btn btn-primary">Add New Item</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['inventory'])): ?>
                        <?php foreach ($data['inventory'] as $item): ?>
                            <tr>
                                <td><?php echo esc_html($item['id']); ?></td>
                                <td><?php echo esc_html($item['item_name']); ?></td>
                                <td><?php echo esc_html($item['quantity']); ?></td>
                                <td><?php echo esc_html($item['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory', 'demo-action' => 'edit-item', 'id' => $item['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory', 'demo-action' => 'delete-item', 'id' => $item['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this item?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddInventoryItem() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Inventory Item</h2>
        <div class="alert alert-info">Fill in the details to add a new inventory item.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" required>
            </div>
            <div class="mb-3">
                <label for="item_quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="item_quantity" name="item_quantity" required>
            </div>
            <div class="mb-3">
                <label for="item_status" class="form-label">Status</label>
                <select class="form-control" id="item_status" name="item_status" required>
                    <option value="Available">Available</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Item</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditInventoryItem() {
    ob_start();
    $item_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Inventory Item</h2>
        <div class="alert alert-info">Update the details for item ID: <?php echo esc_html($item_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" value="Projector" required>
            </div>
            <div class="mb-3">
                <label for="item_quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="item_quantity" name="item_quantity" value="5" required>
            </div>
            <div class="mb-3">
                <label for="item_status" class="form-label">Status</label>
                <select class="form-control" id="item_status" name="item_status" required>
                    <option value="Available" selected>Available</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Item</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteInventoryItem() {
    ob_start();
    $item_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Inventory Item</h2>
        <div class="alert alert-danger">Item ID: <?php echo esc_html($item_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory'])); ?>" class="btn btn-primary">Back to Inventory</a>
    </div>
    <?php
    return ob_get_clean();
}

// Inventory Transactions
function demoRenderInstituteAdminInventoryTransactions($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Inventory Transactions</h2>
        <div class="alert alert-info">Manage inventory transactions for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions', 'demo-action' => 'add-transaction'])); ?>" class="btn btn-primary">Add New Transaction</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>User Name</th>
                        <th>Issue Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['inventory_transactions'])): ?>
                        <?php foreach ($data['inventory_transactions'] as $transaction): ?>
                            <tr>
                                <td><?php echo esc_html($transaction['id']); ?></td>
                                <td><?php echo esc_html($transaction['item_name']); ?></td>
                                <td><?php echo esc_html($transaction['user_name']);?></td>
                                <td><?php echo esc_html($transaction['issue_date']); ?></td>
                                <td><?php echo esc_html($transaction['status']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions', 'demo-action' => 'edit-transaction', 'id' => $transaction['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions', 'demo-action' => 'delete-transaction', 'id' => $transaction['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this transaction?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddInventoryTransaction() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add Inventory Transaction</h2>
        <div class="alert alert-info">Fill in the details to add a new inventory transaction.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" required>
            </div>
            <div class="mb-3">
                <label for="user_name" class="form-label">User Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" required>
            </div>
            <div class="mb-3">
                <label for="issue_date" class="form-label">Issue Date</label>
                <input type="date" class="form-control" id="issue_date" name="issue_date" required>
            </div>
            <div class="mb-3">
                <label for="transaction_status" class="form-label">Status</label>
                <select class="form-control" id="transaction_status" name="transaction_status" required>
                    <option value="Issued">Issued</option>
                    <option value="Returned">Returned</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Transaction</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditInventoryTransaction() {
    ob_start();
    $transaction_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Inventory Transaction</h2>
        <div class="alert alert-info">Update the details for transaction ID: <?php echo esc_html($transaction_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" value="Projector" required>
            </div>
            <div class="mb-3">
                <label for="user_name" class="form-label">User Name</label>
                <input type="text" class="form-control" id="user_name" name="user_name" value="Jane Smith" required>
            </div>
            <div class="mb-3">
                <label for="issue_date" class="form-label">Issue Date</label>
                <input type="date" class="form-control" id="issue_date" name="issue_date" value="2025-04-18" required>
            </div>
            <div class="mb-3">
                <label for="transaction_status" class="form-label">Status</label>
                <select class="form-control" id="transaction_status" name="transaction_status" required>
                    <option value="Issued" selected>Issued</option>
                    <option value="Returned">Returned</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Transaction</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteInventoryTransaction() {
    ob_start();
    $transaction_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Inventory Transaction</h2>
        <div class="alert alert-danger">Transaction ID: <?<a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'inventory_transactions'])); ?>" class="btn btn-primary">Back to Inventory Transactions</a>
    </div>
    <?php
    return ob_get_clean();
}

// Chats
function demoRenderInstituteAdminChats($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Chats Management</h2>
        <div class="alert alert-info">Manage chats for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats', 'demo-action' => 'start-chat'])); ?>" class="btn btn-primary">Start New Chat</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Participant</th>
                        <th>Last Message</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['chats'])): ?>
                        <?php foreach ($data['chats'] as $chat): ?>
                            <tr>
                                <td><?php echo esc_html($chat['id']); ?></td>
                                <td><?php echo esc_html($chat['participant']); ?></td>
                                <td><?php echo esc_html($chat['last_message']); ?></td>
                                <td><?php echo esc_html($chat['date']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats', 'demo-action' => 'view-chat', 'id' => $chat['id']])); ?>" class="btn btn-sm btn-primary">View</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats', 'demo-action' => 'delete-chat', 'id' => $chat['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this chat?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No chats found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminStartChat() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Start New Chat</h2>
        <div class="alert alert-info">Select a participant to start a new chat.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="participant" class="form-label">Participant</label>
                <select class="form-control" id="participant" name="participant" required>
                    <option value="">Select Participant</option>
                    <option value="John Doe">John Doe (Student)</option>
                    <option value="Jane Smith">Jane Smith (Teacher)</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Initial Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Start Chat</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminViewChat() {
    ob_start();
    $chat_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>View Chat</h2>
        <div class="alert alert-info">Chat ID: <?php echo esc_html($chat_id); ?> with John Doe.</div>
        <div class="chat-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
            <div class="chat-message"><strong>Admin:</strong> Hello, how can I assist you? <span class="text-muted">(2025-04-18 10:00)</span></div>
            <div class="chat-message"><strong>John Doe:</strong> I have a question about the exam schedule. <span class="text-muted">(2025-04-18 10:05)</span></div>
        </div>
        <form method="post" action="" class="mt-3">
            <div class="mb-3">
                <label for="message" class="form-label">Send Message</label>
                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats'])); ?>" class="btn btn-secondary">Back to Chats</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteChat() {
    ob_start();
    $chat_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Chat</h2>
        <div class="alert alert-danger">Chat ID: <?php echo esc_html($chat_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'chats'])); ?>" class="btn btn-primary">Back to Chats</a>
    </div>
    <?php
    return ob_get_clean();
}

// Reports
function demoRenderInstituteAdminReports($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Reports Management</h2>
        <div class="alert alert-info">Generate and view reports for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Attendance Report</h5>
                        <p class="card-text">View attendance statistics.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports', 'demo-action' => 'attendance-report'])); ?>" class="btn btn-primary">Generate</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Fee Report</h5>
                        <p class="card-text">View fee collection status.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports', 'demo-action' => 'fee-report'])); ?>" class="btn btn-primary">Generate</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Exam Report</h5>
                        <p class="card-text">View exam performance.</p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports', 'demo-action' => 'exam-report'])); ?>" class="btn btn-primary">Generate</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAttendanceReport($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Attendance Report</h2>
        <div class="alert alert-info">Attendance statistics for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Present Days</th>
                        <th>Absent Days</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['attendance_report'])): ?>
                        <?php foreach ($data['attendance_report'] as $report): ?>
                            <tr>
                                <td><?php echo esc_html($report['student_name']); ?></td>
                                <td><?php echo esc_html($report['present_days']); ?></td>
                                <td><?php echo esc_html($report['absent_days']); ?></td>
                                <td><?php echo esc_html($report['percentage']); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No attendance data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports'])); ?>" class="btn btn-primary">Back to Reports</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminFeeReport($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Fee Report</h2>
        <div class="alert alert-info">Fee collection status for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Total Due</th>
                        <th>Paid</th>
                        <th>Outstanding</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['fee_report'])): ?>
                        <?php foreach ($data['fee_report'] as $report): ?>
                            <tr>
                                <td><?php echo esc_html($report['student_name']); ?></td>
                                <td><?php echo esc_html($report['total_due']); ?></td>
                                <td><?php echo esc_html($report['paid']); ?></td>
                                <td><?php echo esc_html($report['outstanding']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No fee data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports'])); ?>" class="btn btn-primary">Back to Reports</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminExamReport($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Exam Report</h2>
        <div class="alert alert-info">Exam performance for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Exam Name</th>
                        <th>Marks</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['exam_report'])): ?>
                        <?php foreach ($data['exam_report'] as $report): ?>
                            <tr>
                                <td><?php echo esc_html($report['student_name']); ?></td>
                                <td><?php echo esc_html($report['exam_name']); ?></td>
                                <td><?php echo esc_html($report['marks']); ?></td>
                                <td><?php echo esc_html($report['percentage']); ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No exam data available.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'reports'])); ?>" class="btn btn-primary">Back to Reports</a>
    </div>
    <?php
    return ob_get_clean();
}

// Results
function demoRenderInstituteAdminResults($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Results Management</h2>
        <div class="alert alert-info">Manage exam results for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results', 'demo-action' => 'add-result'])); ?>" class="btn btn-primary">Add New Result</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Exam Name</th>
                        <th>Marks</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['results'])): ?>
                        <?php foreach ($data['results'] as $result): ?>
                            <tr>
                                <td><?php echo esc_html($result['id']); ?></td>
                                <td><?php echo esc_html($result['student_name']); ?></td>
                                <td><?php echo esc_html($result['exam_name']); ?></td>
                                <td><?php echo esc_html($result['marks']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results', 'demo-action' => 'edit-result', 'id' => $result['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results', 'demo-action' => 'delete-result', 'id' => $result['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this result?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No results found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddResult() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Result</h2>
        <div class="alert alert-info">Fill in the details to add a new result.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" required>
            </div>
            <div class="mb-3">
                <label for="marks" class="form-label">Marks</label>
                <input type="number" class="form-control" id="marks" name="marks" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Result</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditResult() {
    ob_start();
    $result_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Result</h2>
        <div class="alert alert-info">Update the details for result ID: <?php echo esc_html($result_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" value="Midterm" required>
            </div>
            <div class="mb-3">
                <label for="marks" class="form-label">Marks</label>
                <input type="number" class="form-control" id="marks" name="marks" value="85" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Result</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteResult() {
    ob_start();
    $result_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Result</h2>
        <div class="alert alert-danger">Result ID: <?php echo esc_html($result_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'results'])); ?>" class="btn btn-primary">Back to Results</a>
    </div>
    <?php
    return ob_get_clean();
}

// Fee Templates
function demoRenderInstituteAdminFeeTemplates($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Fee Templates Management</h2>
        <div class="alert alert-info">Manage fee templates for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates', 'demo-action' => 'add-template'])); ?>" class="btn btn-primary">Add New Template</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Template Name</th>
                        <th>Amount</th>
                        <th>Class</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['fee_templates'])): ?>
                        <?php foreach ($data['fee_templates'] as $template): ?>
                            <tr>
                                <td><?php echo esc_html($template['id']); ?></td>
                                <td><?php echo esc_html($template['template_name']); ?></td>
                                <td><?php echo esc_html($template['amount']); ?></td>
                                <td><?php echo esc_html($template['class']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates', 'demo-action' => 'edit-template', 'id' => $template['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates', 'demo-action' => 'delete-template', 'id' => $template['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this template?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No fee templates found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddFeeTemplate() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Fee Template</h2>
        <div class="alert alert-info">Fill in the details to add a new fee template.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="template_name" class="form-label">Template Name</label>
                <input type="text" class="form-control" id="template_name" name="template_name" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Template</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditFeeTemplate() {
    ob_start();
    $template_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Fee Template</h2>
        <div class="alert alert-info">Update the details for template ID: <?php echo esc_html($template_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="template_name" class="form-label">Template Name</label>
                <input type="text" class="form-control" id="template_name" name="template_name" value="Annual Fee" required>
            </div>
            <div class="mb-3">
                <label for="amount" class="form-label">Amount</label>
                <input type="number" class="form-control" id="amount" name="amount" value="1000" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Grade 10" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Template</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteFeeTemplate() {
    ob_start();
    $template_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Fee Template</h2>
        <div class="alert alert-danger">Template ID: <?php echo esc_html($template_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'fee_templates'])); ?>" class="btn btn-primary">Back to Fee Templates</a>
    </div>
    <?php
    return ob_get_clean();
}

// Transport
function demoRenderInstituteAdminTransport($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Transport Management</h2>
        <div class="alert alert-info">Manage transport vehicles for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport', 'demo-action' => 'add-vehicle'])); ?>" class="btn btn-primary">Add New Vehicle</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vehicle Number</th>
                        <th>Route</th>
                        <th>Driver</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['transport'])): ?>
                        <?php foreach ($data['transport'] as $vehicle): ?>
                            <tr>
                                <td><?php echo esc_html($vehicle['id']); ?></td>
                                <td><?php echo esc_html($vehicle['vehicle_number']); ?></td>
                                <td><?php echo esc_html($vehicle['route']); ?></td>
                                <td><?php echo esc_html($vehicle['driver']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport', 'demo-action' => 'edit-vehicle', 'id' => $vehicle['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport', 'demo-action' => 'delete-vehicle', 'id' => $vehicle['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this vehicle?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No vehicles found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddVehicle() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Vehicle</h2>
        <div class="alert alert-info">Fill in the details to add a new vehicle.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="vehicle_number" class="form-label">Vehicle Number</label>
                <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" required>
            </div>
            <div class="mb-3">
                <label for="route" class="form-label">Route</label>
                <input type="text" class="form-control" id="route" name="route" required>
            </div>
            <div class="mb-3">
                <label for="driver" class="form-label">Driver Name</label>
                <input type="text" class="form-control" id="driver" name="driver" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Vehicle</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditVehicle() {
    ob_start();
    $vehicle_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Vehicle</h2>
        <div class="alert alert-info">Update the details for vehicle ID: <?php echo esc_html($vehicle_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="vehicle_number" class="form-label">Vehicle Number</label>
                <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" value="XYZ123" required>
            </div>
            <div class="mb-3">
                <label for="route" class="form-label">Route</label>
                <input type="text" class="form-control" id="route" name="route" value="Route A" required>
            </div>
            <div class="mb-3">
                <label for="driver" class="form-label">Driver Name</label>
                <input type="text" class="form-control" id="driver" name="driver" value="Mike Driver" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Vehicle</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteVehicle() {
    ob_start();
    $vehicle_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Vehicle</h2>
        <div class="alert alert-danger">Vehicle ID: <?php echo esc_html($vehicle_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport'])); ?>" class="btn btn-primary">Back to Transport</a>
    </div>
    <?php
    return ob_get_clean();
}

// Transport Enrollments
function demoRenderInstituteAdminTransportEnrollments($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Transport Enrollments</h2>
        <div class="alert alert-info">Manage transport enrollments for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments', 'demo-action' => 'add-enrollment'])); ?>" class="btn btn-primary">Add New Enrollment</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Vehicle Number</th>
                        <th>Route</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['transport_enrollments'])): ?>
                        <?php foreach ($data['transport_enrollments'] as $enrollment): ?>
                            <tr>
                                <td><?php echo esc_html($enrollment['id']); ?></td>
                                <td><?php echo esc_html($enrollment['student_name']); ?></td>
                                <td><?php echo esc_html($enrollment['vehicle_number']); ?></td>
                                <td><?php echo esc_html($enrollment['route']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments', 'demo-action' => 'edit-enrollment', 'id' => $enrollment['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments', 'demo-action' => 'delete-enrollment', 'id' => $enrollment['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this enrollment?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No enrollments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddTransportEnrollment() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Transport Enrollment</h2>
        <div class="alert alert-info">Fill in the details to add a new transport enrollment.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="vehicle_number" class="form-label">Vehicle Number</label>
                <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" required>
            </div>
            <div class="mb-3">
                <label for="route" class="form-label">Route</label>
                <input type="text" class="form-control" id="route" name="route" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Enrollment</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditTransportEnrollment() {
    ob_start();
    $enrollment_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Transport Enrollment</h2>
        <div class="alert alert-info">Update the details for enrollment ID: <?php echo esc_html($enrollment_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="vehicle_number" class="form-label">Vehicle Number</label>
                <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" value="XYZ123" required>
            </div>
            <div class="mb-3">
                <label for="route" class="form-label">Route</label>
                <input type="text" class="form-control" id="route" name="route" value="Route A" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Enrollment</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteTransportEnrollment() {
    ob_start();
    $enrollment_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Transport Enrollment</h2>
        <div class="alert alert-danger">Enrollment ID: <?php echo esc_html($enrollment_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'transport_enrollments'])); ?>" class="btn btn-primary">Back to Transport Enrollments</a>
    </div>
    <?php
    return ob_get_clean();
}

// Departments
function demoRenderInstituteAdminDepartments($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Departments Management</h2>
        <div class="alert alert-info">Manage departments for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments', 'demo-action' => 'add-department'])); ?>" class="btn btn-primary">Add New Department</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Department Name</th>
                        <th>Head</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['departments'])): ?>
                        <?php foreach ($data['departments'] as $department): ?>
                            <tr>
                                <td><?php echo esc_html($department['id']); ?></td>
                                <td><?php echo esc_html($department['name']); ?></td>
                                <td><?php echo esc_html($department['head']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments', 'demo-action' => 'edit-department', 'id' => $department['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments', 'demo-action' => 'delete-department', 'id' => $department['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this department?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">No departments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddDepartment() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Department</h2>
        <div class="alert alert-info">Fill in the details to add a new department.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="department_name" class="form-label">Department Name</label>
                <input type="text" class="form-control" id="department_name" name="department_name" required>
            </div>
            <div class="mb-3">
                <label for="department_head" class="form-label">Head of Department</label>
                <input type="text" class="form-control" id="department_head" name="department_head" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Department</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditDepartment() {
    ob_start();
    $department_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Department</h2>
        <div class="alert alert-info">Update the details for department ID: <?php echo esc_html($department_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="department_name" class="form-label">Department Name</label>
                <input type="text" class="form-control" id="department_name" name="department_name" value="Mathematics" required>
            </div>
            <div class="mb-3">
                <label for="department_head" class="form-label">Head of Department</label>
                <input type="text" class="form-control" id="department_head" name="department_head" value="Dr. Smith" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Department</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteDepartment() {
    ob_start();
    $department_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Department</h2>
        <div class="alert alert-danger">Department ID: <?php echo esc_html($department_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'departments'])); ?>" class="btn btn-primary">Back to Departments</a>
    </div>
    <?php
    return ob_get_clean();
}

// Subjects
function demoRenderInstituteAdminSubjects($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Subjects Management</h2>
        <div class="alert alert-info">Manage subjects for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects', 'demo-action' => 'add-subject'])); ?>" class="btn btn-primary">Add New Subject</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject Name</th>
                        <th>Class</th>
                        <th>Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['subjects'])): ?>
                        <?php foreach ($data['subjects'] as $subject): ?>
                            <tr>
                                <td><?php echo esc_html($subject['id']); ?></td>
                                <td><?php echo esc_html($subject['name']); ?></td>
                                <td><?php echo esc_html($subject['class']); ?></td>
                                <td><?php echo esc_html($subject['teacher']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects', 'demo-action' => 'edit-subject', 'id' => $subject['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects', 'demo-action' => 'delete-subject', 'id' => $subject['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this subject?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No subjects found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddSubject() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Subject</h2>
        <div class="alert alert-info">Fill in the details to add a new subject.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject_name" class="form-label">Subject Name</label>
                <input type="text" class="form-control" id="subject_name" name="subject_name" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="teacher" class="form-label">Teacher</label>
                <input type="text" class="form-control" id="teacher" name="teacher" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Subject</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditSubject() {
    ob_start();
    $subject_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Subject</h2>
        <div class="alert alert-info">Update the details for subject ID: <?php echo esc_html($subject_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject_name" class="form-label">Subject Name</label>
                <input type="text" class="form-control" id="subject_name" name="subject_name" value="Physics" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Grade 11" required>
            </div>
            <div class="mb-3">
                <label for="teacher" class="form-label">Teacher</label>
                <input type="text" class="form-control" id="teacher" name="teacher" value="Ms. Johnson" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Subject</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteSubject() {
    ob_start();
    $subject_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Subject</h2>
        <div class="alert alert-danger">Subject ID: <?php echo esc_html($subject_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subjects'])); ?>" class="btn btn-primary">Back to Subjects</a>
    </div>
    <?php
    return ob_get_clean();
}

// Timetable
function demoRenderInstituteAdminTimetable($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Timetable Management</h2>
        <div class="alert alert-info">Manage timetable for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable', 'demo-action' => 'add-timetable'])); ?>" class="btn btn-primary">Add New Timetable Entry</a>
        </div>
        <div class="mb-3">
            <label for="class_filter" class="form-label">Filter by Class</label>
            <select class="form-control" id="class_filter" onchange="location = this.value;">
                <option value="">Select Class</option>
                <?php foreach ($data['classes'] as $class): ?>
                    <option value="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable', 'class' => $class['name']])); ?>" <?php echo isset($_GET['class']) && $_GET['class'] === $class['name'] ? 'selected' : ''; ?>>
                        <?php echo esc_html($class['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="timetable-container" style="overflow-x: auto;">
            <table class="table table-bordered" style="min-width: 800px;">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Monday</th>
                        <th>Tuesday</th>
                        <th>Wednesday</th>
                        <th>Thursday</th>
                        <th>Friday</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $time_slots = ['08:00-09:00', '09:00-10:00', '10:00-11:00', '11:00-12:00', '12:00-13:00', '13:00-14:00', '14:00-15:00'];
                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                    $colors = ['bg-primary', 'bg-success', 'bg-warning', 'bg-info', 'bg-danger'];
                    foreach ($time_slots as $slot): ?>
                        <tr>
                            <td><?php echo esc_html($slot); ?></td>
                            <?php foreach ($days as $day): ?>
                                <td>
                                    <?php
                                    $entry = null;
                                    foreach ($data['timetable'] as $t) {
                                        if ($t['time_slot'] === $slot && $t['day'] === $day && (!isset($_GET['class']) || $t['class'] === $_GET['class'])) {
                                            $entry = $t;
                                            break;
                                        }
                                    }
                                    if ($entry):
                                        $color = $colors[array_rand($colors)];
                                    ?>
                                        <div class="p-2 text-white <?php echo esc_attr($color); ?>" style="border-radius: 5px;">
                                            <?php echo esc_html($entry['subject']); ?><br>
                                            <small><?php echo esc_html($entry['teacher']); ?></small><br>
                                            <div class="mt-1">
                                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable', 'demo-action' => 'edit-timetable', 'id' => $entry['id']])); ?>" class="btn btn-sm btn-light">Edit</a>
                                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable', 'demo-action' => 'delete-timetable', 'id' => $entry['id']])); ?>" class="btn btn-sm btn-light" onclick="return confirm('Are you sure you want to delete this timetable entry?');">Delete</a>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddTimetable() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Timetable Entry</h2>
        <div class="alert alert-info">Fill in the details to add a new timetable entry.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="teacher" class="form-label">Teacher</label>
                <input type="text" class="form-control" id="teacher" name="teacher" required>
            </div>
            <div class="mb-3">
                <label for="day" class="form-label">Day</label>
                <select class="form-control" id="day" name="day" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="time_slot" class="form-label">Time Slot</label>
                <select class="form-control" id="time_slot" name="time_slot" required>
                    <option value="08:00-09:00">08:00-09:00</option>
                    <option value="09:00-10:00">09:00-10:00</option>
                    <option value="10:00-11:00">10:00-11:00</option>
                    <option value="11:00-12:00">11:00-12:00</option>
                    <option value="12:00-13:00">12:00-13:00</option>
                    <option value="13:00-14:00">13:00-14:00</option>
                    <option value="14:00-15:00">14:00-15:00</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Timetable Entry</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditTimetable() {
    ob_start();
    $timetable_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Timetable Entry</h2>
        <div class="alert alert-info">Update the details for timetable entry ID: <?php echo esc_html($timetable_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Grade 10" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="Mathematics" required>
            </div>
            <div class="mb-3">
                <label for="teacher" class="form-label">Teacher</label>
                <input type="text" class="form-control" id="teacher" name="teacher" value="Mr. Brown" required>
            </div>
            <div class="mb-3">
                <label for="day" class="form-label">Day</label>
                <select class="form-control" id="day" name="day" required>
                    <option value="Monday" selected>Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="time_slot" class="form-label">Time Slot</label>
                <select class="form-control" id="time_slot" name="time_slot" required>
                    <option value="08:00-09:00" selected>08:00-09:00</option>
                    <option value="09:00-10:00">09:00-10:00</option>
                    <option value="10:00-11:00">10:00-11:00</option>
                    <option value="11:00-12:00">11:00-12:00</option>
                    <option value="12:00-13:00">12:00-13:00</option>
                    <option value="13:00-14:00">13:00-14:00</option>
                    <option value="14:00-15:00">14:00-15:00</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Timetable Entry</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteTimetable() {
    ob_start();
    $timetable_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Timetable Entry</h2>
        <div class="alert alert-danger">Timetable entry ID: <?php echo esc_html($timetable_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'timetable'])); ?>" class="btn btn-primary">Back to Timetable</a>
    </div>
    <?php
    return ob_get_clean();
}

// Homework
function demoRenderInstituteAdminHomework($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Homework Management</h2>
        <div class="alert alert-info">Manage homework for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework', 'demo-action' => 'add-homework'])); ?>" class="btn btn-primary">Add New Homework</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>Class</th>
                        <th>Due Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['homework'])): ?>
                        <?php foreach ($data['homework'] as $homework): ?>
                            <tr>
                                <td><?php echo esc_html($homework['id']); ?></td>
                                <td><?php echo esc_html($homework['subject']); ?></td>
                                <td><?php echo esc_html($homework['class']); ?></td>
                                <td><?php echo esc_html($homework['due_date']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework', 'demo-action' => 'edit-homework', 'id' => $homework['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework', 'demo-action' => 'delete-homework', 'id' => $homework['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this homework?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No homework found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddHomework() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Homework</h2>
        <div class="alert alert-info">Fill in the details to add a new homework.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Homework</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditHomework() {
    ob_start();
    $homework_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Homework</h2>
        <div class="alert alert-info">Update the details for homework ID: <?php echo esc_html($homework_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" value="English" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Grade 9" required>
            </div>
            <div class="mb-3">
                <label for="due_date" class="form-label">Due Date</label>
                <input type="date" class="form-control" id="due_date" name="due_date" value="2025-04-25" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" required>Write an essay on Shakespeare.</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update Homework</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteHomework() {
    ob_start();
    $homework_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Homework</h2>
        <div class="alert alert-danger">Homework ID: <?php echo esc_html($homework_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'homework'])); ?>" class="btn btn-primary">Back to Homework</a>
    </div>
    <?php
    return ob_get_clean();
}

// Classes & Sections
function demoRenderInstituteAdminClassesSections($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Classes & Sections Management</h2>
        <div class="alert alert-info">Manage classes and sections for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections', 'demo-action' => 'add-class'])); ?>" class="btn btn-primary">Add New Class</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Class Name</th>
                        <th>Section</th>
                        <th>Class Teacher</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['classes_sections'])): ?>
                        <?php foreach ($data['classes_sections'] as $class): ?>
                            <tr>
                                <td><?php echo esc_html($class['id']); ?></td>
                                <td><?php echo esc_html($class['class_name']); ?></td>
                                <td><?php echo esc_html($class['section']); ?></td>
                                <td><?php echo esc_html($class['class_teacher']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections', 'demo-action' => 'edit-class', 'id' => $class['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections', 'demo-action' => 'delete-class', 'id' => $class['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this class?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No classes found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddClass() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Class</h2>
        <div class="alert alert-info">Fill in the details to add a new class.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="class_name" class="form-label">Class Name</label>
                <input type="text" class="form-control" id="class_name" name="class_name" required>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section" required>
            </div>
            <div class="mb-3">
                <label for="class_teacher" class="form-label">Class Teacher</label>
                <input type="text" class="form-control" id="class_teacher" name="class_teacher" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Class</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditClass() {
    ob_start();
    $class_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Class</h2>
        <div class="alert alert-info">Update the details for class ID: <?php echo esc_html($class_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="class_name" class="form-label">Class Name</label>
                <input type="text" class="form-control" id="class_name" name="class_name" value="Grade 8" required>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section" value="A" required>
            </div>
            <div class="mb-3">
                <label for="class_teacher" class="form-label">Class Teacher</label>
                <input type="text" class="form-control" id="class_teacher" name="class_teacher" value="Ms. Davis" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Class</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteClass() {
    ob_start();
    $class_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Class</h2>
        <div class="alert alert-danger">Class ID: <?php echo esc_html($class_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'classes_sections'])); ?>" class="btn btn-primary">Back to Classes & Sections</a>
    </div>
    <?php
    return ob_get_clean();
}

// Subscriptions
function demoRenderInstituteAdminSubscriptions($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Subscriptions Management</h2>
        <div class="alert alert-info">Manage subscriptions for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subscriptions', 'demo-action' => 'add-subscription'])); ?>" class="btn btn-primary">Add New Subscription</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                  
                        <th>Plan</th>
                        <th>Start Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['subscriptions'])): ?>
                        <?php foreach ($data['subscriptions'] as $subscription): ?>
                            <tr>
                                <td><?php echo esc_html($subscription['id']); ?></td>
                                <td><?php echo esc_html($subscription['plan']); ?></td>
                                <td><?php echo esc_html($subscription['start_date']); ?></td>
                                <td><?php echo esc_html($subscription['status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No subscriptions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}


// Parents
function demoRenderInstituteAdminParents($data) {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Parents Management</h2>
        <div class="alert alert-info">Manage parents for <?php echo esc_html($data['institute']); ?>.</div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents', 'demo-action' => 'add-parent'])); ?>" class="btn btn-primary">Add New Parent</a>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Parent Name</th>
                        <th>Student Name</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['parents'])): ?>
                        <?php foreach ($data['parents'] as $parent): ?>
                            <tr>
                                <td><?php echo esc_html($parent['id']); ?></td>
                                <td><?php echo esc_html($parent['parent_name']); ?></td>
                                <td><?php echo esc_html($parent['student_name']); ?></td>
                                <td><?php echo esc_html($parent['contact']); ?></td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents', 'demo-action' => 'edit-parent', 'id' => $parent['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents', 'demo-action' => 'delete-parent', 'id' => $parent['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this parent?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No parents found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminAddParent() {
    ob_start();
    ?>
    <div class="dashboard-section">
        <h2>Add New Parent</h2>
        <div class="alert alert-info">Fill in the details to add a new parent.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="parent_name" class="form-label">Parent Name</label>
                <input type="text" class="form-control" id="parent_name" name="parent_name" required>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="contact" class="form-label">Contact</label>
                <input type="text" class="form-control" id="contact" name="contact" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Parent</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminEditParent() {
    ob_start();
    $parent_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Edit Parent</h2>
        <div class="alert alert-info">Update the details for parent ID: <?php echo esc_html($parent_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="parent_name" class="form-label">Parent Name</label>
                <input type="text" class="form-control" id="parent_name" name="parent_name" value="Mr. Smith" required>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Smith" required>
            </div>
            <div class="mb-3">
                <label for="contact" class="form-label">Contact</label>
                <input type="text" class="form-control" id="contact" name="contact" value="123-456-7890" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Parent</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderInstituteAdminDeleteParent() {
    ob_start();
    $parent_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Delete Parent</h2>
        <div class="alert alert-danger">Parent ID: <?php echo esc_html($parent_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'parents'])); ?>" class="btn btn-primary">Back to Parents</a>
    </div>
    <?php
    return ob_get_clean();
}
function demoRenderInstituteAdminExtendSubscription() {
    ob_start();
    $subscription_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="dashboard-section">
        <h2>Extend Subscription</h2>
        <div class="alert alert-info">Extend the subscription for ID: <?php echo esc_html($subscription_id); ?>.</div>
        <form method="post" action="">
        
            <div class="mb-3">
                <label for="plan" class="form-label">Current Plan</label>
                <input type="text" class="form-control" id="plan" name="plan" value="Monthly" readonly>
            </div>
            <div class="mb-3">
                <label for="current_end_date" class="form-label">Current End Date</label>
                <input type="date" class="form-control" id="current_end_date" name="current_end_date" value="2025-04-30" readonly>
            </div>
            <div class="mb-3">
                <label for="extend_months" class="form-label">Extend by (Months)</label>
                <input type="number" class="form-control" id="extend_months" name="extend_months" min="1" required>
            </div>
            <div class="mb-3">
                <label for="new_end_date" class="form-label">Or Set New End Date</label>
                <input type="date" class="form-control" id="new_end_date" name="new_end_date">
            </div>
            <button type="submit" class="btn btn-primary">Extend Subscription</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'institute-admin', 'demo-section' => 'subscriptions'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}





/**
 * Data Functions
 */
// function demoGetTeacherData() {
//     return [
//         'role' => 'teacher',
//         'exams' => [
//             ['name' => 'Math Exam', 'date' => '2025-04-15', 'class' => 'Class 1A'],
//             ['name' => 'Science Exam', 'date' => '2025-04-20', 'class' => 'Class 1A'],
//             ['name' => 'History Quiz', 'date' => '2025-04-25', 'class' => 'Class 2B'],
//             ['name' => 'English Test', 'date' => '2025-05-01', 'class' => 'Class 3C']
//         ],
//         'attendance' => [
//             ['date' => '2025-04-01', 'student' => 'Student 1', 'status' => 'Present'],
//             ['date' => '2025-04-02', 'student' => 'Student 2', 'status' => 'Absent'],
//             ['date' => '2025-04-03', 'student' => 'Student 3', 'status' => 'Present'],
//             ['date' => '2025-04-04', 'student' => 'Student 4', 'status' => 'Late']
//         ],
//         'students' => [
//             ['id' => 'ST1001', 'name' => 'John Doe', 'email' => 'john@demo-pro.edu', 'class' => 'Class 1A', 'center' => 'Center A'],
//             ['id' => 'ST1002', 'name' => 'Jane Smith', 'email' => 'jane@demo-pro.edu', 'class' => 'Class 1A', 'center' => 'Center A']
//         ],
//         'student_attendance' => [
//             ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-10', 'status' => 'Present'],
//             ['student_id' => 'S002', 'student_name' => 'Jane Smith', 'class' => '10', 'section' => 'B', 'date' => '2025-04-10', 'status' => 'Absent'],
//             ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-11', 'status' => 'Late'],
//         ],
//         'profile' => [
//             'teacher_id' => 'TR1001',
//             'name' => 'Alice Brown',
//             'email' => 'alice@demo-pro.edu',
//             'subject' => 'Math',
//             'center' => 'Center A'
//         ]
//     ];
// }

function demoGetSuperadminData() {
    $data = [
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
        'staff' => [
            ['staff_id' => 'ST001', 'staff_name' => 'Carol Lee', 'role' => 'Admin', 'center' => 'Main Campus'],
            ['staff_id' => 'ST002', 'staff_name' => 'David Kim', 'role' => 'Clerk', 'center' => 'West Campus'],
        ],
        'student_attendance' => [
            ['student_id' => 'ST1001', 'student_name' => 'John Doe', 'education_center_id' => 'CA001', 'class' => '10A', 'section' => 'A', 'date' => '2025-04-10', 'status' => 'Present', 'subject' => 'Math', 'teacher_id' => 'TR1001'],
            ['student_id' => 'ST1002', 'student_name' => 'Jane Smith', 'education_center_id' => 'CB001', 'class' => '10B', 'section' => 'B', 'date' => '2025-04-10', 'status' => 'Absent', 'subject' => 'Science', 'teacher_id' => 'TR1002'],
        ],
        'teacher_attendance' => [
            ['teacher_id' => 'TR1001', 'teacher_name' => 'Alice Brown', 'education_center_id' => 'CA001', 'department' => 'Math', 'date' => '2025-04-10', 'status' => 'Present'],
            ['teacher_id' => 'TR1002', 'teacher_name' => 'Bob Wilson', 'education_center_id' => 'CB001', 'department' => 'Science', 'date' => '2025-04-10', 'status' => 'Late'],
        ],
        'staff_attendance' => [
            ['staff_id' => 'ST001', 'staff_name' => 'Carol Lee', 'education_center_id' => 'CA001', 'role' => 'Admin', 'date' => '2025-04-10', 'status' => 'Present'],
            ['staff_id' => 'ST002', 'staff_name' => 'David Kim', 'education_center_id' => 'CB001', 'role' => 'Clerk', 'date' => '2025-04-10', 'status' => 'Absent'],
            ['staff_id' => 'ST001', 'staff_name' => 'Carol Lee', 'education_center_id' => 'CA001', 'role' => 'Admin', 'date' => '2025-04-11', 'status' => 'Late'],
        ],
        'fees' => [
            ['fee_id' => 'F001', 'student_id' => 'S001', 'student_name' => 'John Doe', 'amount' => 500.00, 'due_date' => '2025-05-01', 'status' => 'Pending', 'center' => 'Main Campus'],
            ['fee_id' => 'F002', 'student_id' => 'S002', 'student_name' => 'Jane Smith', 'amount' => 600.00, 'due_date' => '2025-05-01', 'status' => 'Paid', 'center' => 'West Campus'],
        ],
        'fee_templates' => [
            ['template_id' => 'FT001', 'name' => 'Annual Tuition', 'amount' => 5000.00, 'frequency' => 'Yearly', 'center' => 'Main Campus'],
            ['template_id' => 'FT002', 'name' => 'Monthly Fee', 'amount' => 500.00, 'frequency' => 'Monthly', 'center' => 'West Campus'],
        ],
        'subscriptions' => [
            ['id' => 1, 'center' => 'Main Campus', 'plan' => 'Premium', 'start_date' => '2025-01-01'],
            ['id' => 2, 'center' => 'West Campus', 'plan' => 'Standard', 'start_date' => '2025-02-01'],
        ],
        'payment_methods' => [
            ['id' => 1, 'method' => 'Credit Card', 'center' => 'Main Campus'],
            ['id' => 2, 'method' => 'Bank Transfer', 'center' => 'West Campus'],
        ],
        'exams' => [
            ['id' => 1, 'title' => 'Math Midterm', 'date' => '2025-05-01', 'center' => 'Main Campus'],
            ['id' => 2, 'title' => 'Science Final', 'date' => '2025-06-01', 'center' => 'West Campus'],
        ],
        'classes' => [
            ['class_id' => 'CL001', 'name' => 'Class 10', 'section' => 'A', 'center' => 'Main Campus'],
            ['class_id' => 'CL002', 'name' => 'Class 10', 'section' => 'B', 'center' => 'West Campus'],
        ],
        'subjects' => [
            ['subject_id' => 'SUB001', 'name' => 'Mathematics', 'class' => 'Class 10', 'center' => 'Main Campus'],
            ['subject_id' => 'SUB002', 'name' => 'Science', 'class' => 'Class 10', 'center' => 'West Campus'],
        ],
        'homeworks' => [
            ['homework_id' => 'HW001', 'title' => 'Algebra Practice', 'subject' => 'Mathematics', 'class' => 'Class 10', 'due_date' => '2025-04-20', 'center' => 'Main Campus'],
            ['homework_id' => 'HW002', 'title' => 'Physics Lab Report', 'subject' => 'Science', 'class' => 'Class 10', 'due_date' => '2025-04-22', 'center' => 'West Campus'],
        ],
    ];
    return $data;
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




