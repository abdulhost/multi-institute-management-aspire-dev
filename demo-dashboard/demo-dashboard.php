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
            ['section' => 'students', 'label' => 'Students', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students']), 'icon' => 'users'],
            ['section' => 'exam-management', 'label' => 'Exam Management', 'icon' => 'book', 'submenu' => [
                ['action' => 'view-exams', 'label' => 'View Exams', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'view-exams'])],
                ['action' => 'add-exam', 'label' => 'Add Exam', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'add-exam'])],
                ['action' => 'edit-exam', 'label' => 'Edit Exam', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'edit-exam'])],
                ['action' => 'delete-exam', 'label' => 'Delete Exam', 'url' => add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exam-management', 'demo-action' => 'delete-exam'])]
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
            ['section' => 'centers', 'label' => 'Centers Management', 'icon' => 'school', 'submenu' => [
                ['action' => 'manage-centers', 'label' => 'Manage Centers', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'manage-centers'])],
                ['action' => 'add-center', 'label' => 'Add Center', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'add-center'])],
                ['action' => 'edit-center', 'label' => 'Edit Center', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'edit-center'])],
                ['action' => 'delete-center', 'label' => 'Delete Center', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'delete-center'])],
                ['action' => 'reset-password', 'label' => 'Reset Admin Password', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'reset-password'])],
                ['action' => 'add-admin', 'label' => 'Add New Admin', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'centers', 'demo-action' => 'add-admin'])]
            ]],
            ['section' => 'students', 'label' => 'Students Management', 'icon' => 'users', 'submenu' => [
                ['action' => 'manage-students', 'label' => 'Manage Students', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'manage-students'])],
                ['action' => 'add-student', 'label' => 'Add Student', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'add-student'])],
                ['action' => 'edit-student', 'label' => 'Edit Student', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'edit-student'])],
                ['action' => 'delete-student', 'label' => 'Delete Student', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'students', 'demo-action' => 'delete-student'])]
            ]],
            ['section' => 'subscription', 'label' => 'Subscriptions', 'icon' => 'calendar-alt', 'submenu' => [
                ['action' => 'view-subscription', 'label' => 'Subscription', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'view-subscription'])],
                ['action' => 'add-subscription', 'label' => 'Add Subscription', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'add-subscription'])],
                ['action' => 'edit-subscription', 'label' => 'Edit Subscription', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'edit-subscription'])],
                ['action' => 'delete-subscription', 'label' => 'Delete Subscription', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'delete-subscription'])]
            ]],
            ['section' => 'payment_methods', 'label' => 'Payment Methods', 'icon' => 'calendar-alt', 'submenu' => [
                ['action' => 'view-payment_methods', 'label' => 'Payment Methods', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'view-payment_methods'])],
                ['action' => 'add-payment_methods', 'label' => 'Add Payment Methods', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'add-payment_methods'])],
                ['action' => 'edit-payment_methods', 'label' => 'Edit Payment Methods', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'edit-payment_methods'])],
                ['action' => 'delete-payment_methods', 'label' => 'Delete Payment Methods', 'url' => add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'payment_methods', 'demo-action' => 'delete-payment_methods'])]
            ]]
        ]
    ];

    $title = ucfirst($role) === 'Superadmin' ? 'Super Admin' : ucfirst($role);
    // Rest of the sidebar rendering code remains unchanged...
    ob_start();
    ?>
    <nav id="sidebar">
        <ul>
            <li>
                <div class="logo-title-section">
                    <div class="institute-logo">
                        <img src="<?php echo plugin_dir_url(__FILE__) . '../logo instituto.jpg'; ?>" alt="Avatar" style="border-radius: 50%; width: 60px; height: 60px; object-fit: cover;" class="profile-avatar mb-3">
                    </div>
                    <h4 class="institute-title" style="margin-bottom:0; margin-left:4px; color: var(--text-clr);"><?php echo esc_html($title); ?></h4>
                </div>
                <button onclick="toggleSidebar()" id="toggle-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="m313-480 155 156q11 11 11.5 27.5T468-268q-11 11-28 11t-28-11L228-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T468-692q11 11 11 28t-11 28L313-480Zm264 0 155 156q11 11 11.5 27.5T732-268q-11 11-28 11t-28-11L492-452q-6-6-8.5-13t-2.5-15q0-8 2.5-15t8.5-13l184-184q11-11 27.5-11.5T732-692q11 11 11 28t-11 28L577-480Z"/></svg>
                </button>
            </li>
            <?php foreach ($links[$role] as $link): ?>
                <?php if (isset($link['submenu'])): ?>
                    <li>
                        <button onclick="toggleSubMenu(this)" class="dropdown-btn <?php echo $active_section === $link['section'] ? 'rotate' : ''; ?>">
                            <?php if ($link['icon'] === 'tachometer-alt'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.05-7.44 7-7.93V6h2V4c3.94.49 7 3.85 7 7.93s-3.05 7.44-7 7.93v-2h-2v2zM12 14l3-3h-2V7h-2v4H9l3 3z"/></svg>
                            <?php elseif ($link['icon'] === 'school'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm-1 12.91L5.09 12 3.31 13 11 17.09 20.69 12 18.91 13 11 15.91z"/></svg>
                            <?php elseif ($link['icon'] === 'list'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>
                            <?php elseif ($link['icon'] === 'calendar'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                            <?php elseif ($link['icon'] === 'calendar-alt'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                            <?php elseif ($link['icon'] === 'book'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/></svg>
                            <?php elseif ($link['icon'] === 'users'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                            <?php elseif ($link['icon'] === 'user'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            <?php endif; ?>
                            <span><?php echo esc_html($link['label']); ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e8eaed"><path d="M480-361q-8 0-15-2.5t-13-8.5L268-556q-11-11-11-28t11-28q11-11 28-11t28 11l156 156 156-156q11-11 28-11t28 11q11 11 11 28t-11 28L508-372q-6 6-13 8.5t-15 2.5Z"/></svg>
                        </button>
                        <ul class="sub-menu <?php echo $active_section === $link['section'] ? 'show' : ''; ?>">
                            <div>
                                <?php foreach ($link['submenu'] as $sub_link): ?>
                                    <li class="<?php echo $active_section === $link['section'] && $active_action === $sub_link['action'] ? 'active' : ''; ?>">
                                        <a href="<?php echo esc_url($sub_link['url']); ?>"><?php echo esc_html($sub_link['label']); ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </div>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="<?php echo $active_section === $link['section'] ? 'active' : ''; ?>">
                        <a href="<?php echo esc_url($link['url']); ?>">
                            <?php if ($link['icon'] === 'tachometer-alt'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.94-.49-7-3.85-7-7.93s3.05-7.44 7-7.93V6h2V4c3.94.49 7 3.85 7 7.93s-3.05 7.44-7 7.93v-2h-2v2zM12 14l3-3h-2V7h-2v4H9l3 3z"/></svg>
                            <?php elseif ($link['icon'] === 'school'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M12 3L1 9l11 6 9-4.91V17h2V9L12 3zm-1 12.91L5.09 12 3.31 13 11 17.09 20.69 12 18.91 13 11 15.91z"/></svg>
                            <?php elseif ($link['icon'] === 'list'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>
                            <?php elseif ($link['icon'] === 'calendar'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                            <?php elseif ($link['icon'] === 'calendar-alt'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/></svg>
                            <?php elseif ($link['icon'] === 'book'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M18 2H6c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 4h5v8l-2.5-1.5L6 12V4z"/></svg>
                            <?php elseif ($link['icon'] === 'users'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                            <?php elseif ($link['icon'] === 'user'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 0 24 24" width="24px" fill="#e8eaed"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            <?php endif; ?>
                            <span><?php echo esc_html($link['label']); ?></span>
                        </a>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </nav>
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
        echo demoRenderTeacherStudents($data); // Now handles full CRUD
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
    
    if ($section === 'subscription') {
        switch ($action) {
            case 'view-subscription':
                echo demoRenderSuperadminSubscription($data);
                break;
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
            case 'view-payment_methods':
                echo demoRenderSuperadminPaymentMethods($data);
                break;
            case 'add-payment_methods':
                echo demoRenderSuperadminAddPaymentMethods();
                break;
            case 'edit-payment_methods':
                echo demoRenderSuperadminEditPaymentMethods($data);
                break;
            case 'delete-payment_methods':
                echo demoRenderSuperadminDeletePaymentMethods($data);
                break;
            default:
                echo demoRenderSuperadminPaymentMethods($data);
        }
    } elseif ($section === 'students') {
        echo demoRenderSuperadminStudents($data); // Now correctly handles students section
    } else {
        switch ($section) {
            case 'centers':
                echo demoRenderSuperadminCenters();
                break;
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
    $action = isset($_GET['demo-action']) ? sanitize_text_field($_GET['demo-action']) : 'manage-students';
    ob_start();
    ?>
    <div class="edu-students-container" style="margin-top: 80px;">
        <div id="edu-loader" class="edu-loader" style="display: none;">
            <div class="edu-loader-container">
                <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
            </div>
        </div>

        <?php if ($action === 'manage-students'): ?>
            <h2 class="edu-students-title">My Students</h2>
            <div class="edu-students-actions">
                <button class="edu-button edu-button-primary" id="add-student-btn">Add New Student</button>
                <input type="text" id="student-search" class="edu-search-input" placeholder="Search Students..." style="margin-left: 20px; padding: 8px; width: 300px;">
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
                <table class="edu-table" id="students-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone Number</th>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Roll Number</th>
                            <th>Admission Number</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="students-table-body">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Add Student Modal -->
            <div id="add-student-modal" class="edu-modal" style="display: none;">
                <div class="edu-modal-content">
                    <span class="edu-modal-close" id="add-student-close">&times;</span>
                    <h2>Add New Student</h2>
                    <form id="add-student-form" class="edu-form">
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="add-student-id">Student ID</label>
                            <input type="text" class="edu-form-input" id="add-student-id" name="student_id" readonly value="ST<?php echo rand(1000, 9999); ?>">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="add-class-name">Class</label>
                            <select id="add-class-name" class="edu-form-input" name="class_name" required>
                                <option value="">Select Class</option>
                                <option value="1">Class 1</option>
                                <option value="2">Class 2</option>
                                <option value="3">Class 3</option>
                            </select>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="add-section">Section</label>
                            <select id="add-section" class="edu-form-input" name="section" disabled required>
                                <option value="">Select Class First</option>
                            </select>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="add-student-name">Student Name</label>
                            <input type="text" class="edu-form-input" id="add-student-name" name="student_name" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="add-student-email">Email</label>
                            <input type="email" class="edu-form-input" id="add-student-email" name="student_email">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="add-phone-number">Phone Number</label>
                            <input type="text" class="edu-form-input" id="add-phone-number" name="phone_number">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="add-roll-number">Roll Number</label>
                            <input type="text" class="edu-form-input" id="add-roll-number" name="roll_number" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="add-admission-number">Admission Number</label>
                            <input type="text" class="edu-form-input" id="add-admission-number" name="admission_number" required>
                        </div>
                        <button type="button" class="edu-button edu-button-primary" id="save-student">Add Student</button>
                    </form>
                    <div class="edu-form-message" id="add-student-message"></div>
                </div>
            </div>

            <!-- Edit Student Modal -->
            <div id="edit-student-modal" class="edu-modal" style="display: none;">
                <div class="edu-modal-content">
                    <span class="edu-modal-close" id="edit-student-close">&times;</span>
                    <h2>Edit Student</h2>
                    <form id="edit-student-form" class="edu-form">
                        <input type="hidden" id="edit-student-post-id" name="student_post_id">
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-student-id">Student ID</label>
                            <input type="text" class="edu-form-input" id="edit-student-id" name="student_id" readonly>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-class-name">Class</label>
                            <select id="edit-class-name" class="edu-form-input" name="class_name" required>
                                <option value="">Select Class</option>
                                <option value="1">Class 1</option>
                                <option value="2">Class 2</option>
                                <option value="3">Class 3</option>
                            </select>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-section">Section</label>
                            <select id="edit-section" class="edu-form-input" name="section" disabled required>
                                <option value="">Select Class First</option>
                            </select>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-student-name">Student Name</label>
                            <input type="text" class="edu-form-input" id="edit-student-name" name="student_name" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-student-email">Email</label>
                            <input type="email" class="edu-form-input" id="edit-student-email" name="student_email">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-phone-number">Phone Number</label>
                            <input type="text" class="edu-form-input" id="edit-phone-number" name="phone_number">
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-roll-number">Roll Number</label>
                            <input type="text" class="edu-form-input" id="edit-roll-number" name="roll_number" required>
                        </div>
                        <div class="edu-form-group">
                            <label class="edu-form-label" for="edit-admission-number">Admission Number</label>
                            <input type="text" class="edu-form-input" id="edit-admission-number" name="admission_number" required>
                        </div>
                        <button type="button" class="edu-button edu-button-primary" id="update-student">Update Student</button>
                    </form>
                    <div class="edu-form-message" id="edit-student-message"></div>
                </div>
            </div>

            <!-- Delete Student Modal -->
            <div id="delete-student-modal" class="edu-modal" style="display: none;">
                <div class="edu-modal-content">
                    <span class="edu-modal-close" id="delete-student-close">&times;</span>
                    <h2>Delete Student</h2>
                    <p>Are you sure you want to delete <span id="delete-student-name"></span> (<span id="delete-student-id"></span>)?</p>
                    <input type="hidden" id="delete-student-post-id">
                    <button type="button" class="edu-button edu-button-delete" id="confirm-delete-student">Delete</button>
                    <button type="button" class="edu-button edu-button-secondary" id="cancel-delete-student">Cancel</button>
                    <div class="edu-form-message" id="delete-student-message"></div>
                </div>
            </div>

        <?php endif; ?>

        <style>
            .edu-modal {
                position: fixed;
                z-index: 1000;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgba(0,0,0,0.4);
            }
            .edu-modal-content {
                background-color: #fff;
                margin: 15% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 50%;
                border-radius: 5px;
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }
            .edu-modal-close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
                cursor: pointer;
            }
            .edu-modal-close:hover,
            .edu-modal-close:focus {
                color: #000;
                text-decoration: none;
                cursor: pointer;
            }
            .edu-success { color: green; }
            .edu-error { color: red; }
        </style>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
        jQuery(document).ready(function($) {
            let currentPage = 1;
            let perPage = 10;
            let searchQuery = '';
            let studentsData = [
                { post_id: 1, student_id: 'ST1001', name: 'John Doe', email: 'john@demo-pro.edu', phone: '123-456-7890', class: '1', section: 'A', roll_number: '001', admission_number: 'AD1001' },
                { post_id: 2, student_id: 'ST1002', name: 'Jane Smith', email: 'jane@demo-pro.edu', phone: '234-567-8901', class: '1', section: 'A', roll_number: '002', admission_number: 'AD1002' }
            ];
            const classSections = {
                '1': ['A', 'B', 'C'],
                '2': ['A', 'B'],
                '3': ['A', 'C']
            };

            function loadStudents(page, limit, query) {
                const filtered = studentsData.filter(s => 
                    !query || s.name.toLowerCase().includes(query.toLowerCase())
                );
                const total = filtered.length;
                const start = (page - 1) * limit;
                const end = start + limit;
                const paginated = filtered.slice(start, end);
                let html = '';
                paginated.forEach(student => {
                    html += `
                        <tr data-student-id="${student.post_id}">
                            <td>${student.student_id}</td>
                            <td>${student.name}</td>
                            <td>${student.email || 'N/A'}</td>
                            <td>${student.phone || 'N/A'}</td>
                            <td>${student.class}</td>
                            <td>${student.section}</td>
                            <td>${student.roll_number}</td>
                            <td>${student.admission_number}</td>
                            <td>
                                <button class="edu-button edu-button-edit edit-student" data-student-id="${student.post_id}">Edit</button>
                                <button class="edu-button edu-button-delete delete-student" data-student-id="${student.post_id}">Delete</button>
                            </td>
                        </tr>
                    `;
                });
                $('#students-table-body').html(html || '<tr><td colspan="9">No students found.</td></tr>');
                const totalPages = Math.ceil(total / limit);
                $('#page-info').text(`Page ${page} of ${totalPages}`);
                $('#prev-page').prop('disabled', page === 1);
                $('#next-page').prop('disabled', page === totalPages || total === 0);
            }

            function updateSections(classSelectId, sectionSelectId) {
                const classVal = $(classSelectId).val();
                const sectionSelect = $(sectionSelectId);
                sectionSelect.prop('disabled', !classVal);
                if (classVal) {
                    let options = '<option value="">Select Section</option>';
                    classSections[classVal].forEach(section => {
                        options += `<option value="${section}">${section}</option>`;
                    });
                    sectionSelect.html(options);
                } else {
                    sectionSelect.html('<option value="">Select Class First</option>');
                }
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

            $('#add-class-name, #edit-class-name').on('change', function() {
                const id = $(this).attr('id');
                updateSections(`#${id}`, `#${id.replace('class-name', 'section')}`);
            });

            // Add Student Modal
            $('#add-student-btn').on('click', function() {
                $('#add-student-modal').show();
                updateSections('#add-class-name', '#add-section');
            });
            $('#add-student-close').on('click', function() { $('#add-student-modal').hide(); });
            $('#save-student').on('click', function() {
                const student = {
                    post_id: studentsData.length + 1,
                    student_id: $('#add-student-id').val(),
                    class: $('#add-class-name').val(),
                    section: $('#add-section').val(),
                    name: $('#add-student-name').val(),
                    email: $('#add-student-email').val(),
                    phone: $('#add-phone-number').val(),
                    roll_number: $('#add-roll-number').val(),
                    admission_number: $('#add-admission-number').val()
                };
                if (student.name && student.class && student.section && student.roll_number && student.admission_number) {
                    studentsData.push(student);
                    $('#add-student-message').addClass('edu-success').text('Student added successfully!');
                    setTimeout(() => {
                        $('#add-student-modal').hide();
                        $('#add-student-message').removeClass('edu-success').text('');
                        $('#add-student-form')[0].reset();
                        $('#add-student-id').val('ST' + Math.floor(1000 + Math.random() * 9000));
                        loadStudents(currentPage, perPage, searchQuery);
                    }, 1000);
                } else {
                    $('#add-student-message').addClass('edu-error').text('Please fill all required fields.');
                }
            });

            // Edit Student Modal
            $(document).on('click', '.edit-student', function() {
                const studentId = $(this).data('student-id');
                const student = studentsData.find(s => s.post_id == studentId);
                $('#edit-student-post-id').val(student.post_id);
                $('#edit-student-id').val(student.student_id);
                $('#edit-class-name').val(student.class);
                updateSections('#edit-class-name', '#edit-section');
                $('#edit-section').val(student.section);
                $('#edit-student-name').val(student.name);
                $('#edit-student-email').val(student.email);
                $('#edit-phone-number').val(student.phone);
                $('#edit-roll-number').val(student.roll_number);
                $('#edit-admission-number').val(student.admission_number);
                $('#edit-student-modal').show();
            });
            $('#edit-student-close').on('click', function() { $('#edit-student-modal').hide(); });
            $('#update-student').on('click', function() {
                const studentId = $('#edit-student-post-id').val();
                const student = studentsData.find(s => s.post_id == studentId);
                student.class = $('#edit-class-name').val();
                student.section = $('#edit-section').val();
                student.name = $('#edit-student-name').val();
                student.email = $('#edit-student-email').val();
                student.phone = $('#edit-phone-number').val();
                student.roll_number = $('#edit-roll-number').val();
                student.admission_number = $('#edit-admission-number').val();
                if (student.name && student.class && student.section && student.roll_number && student.admission_number) {
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

            // Delete Student Modal
            $(document).on('click', '.delete-student', function() {
                const studentId = $(this).data('student-id');
                const student = studentsData.find(s => s.post_id == studentId);
                $('#delete-student-post-id').val(student.post_id);
                $('#delete-student-name').text(student.name);
                $('#delete-student-id').text(student.student_id);
                $('#delete-student-modal').show();
            });
            $('#delete-student-close, #cancel-delete-student').on('click', function() { $('#delete-student-modal').hide(); });
            $('#confirm-delete-student').on('click', function() {
                const studentId = $('#delete-student-post-id').val();
                studentsData = studentsData.filter(s => s.post_id != studentId);
                $('#delete-student-message').addClass('edu-success').text('Student deleted successfully!');
                setTimeout(() => {
                    $('#delete-student-modal').hide();
                    $('#delete-student-message').removeClass('edu-success').text('');
                    loadStudents(currentPage, perPage, searchQuery);
                }, 1000);
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
        ]
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

function demoGetSuperadminData() {
    return [
        'role' => 'superadmin',
        'exams' => [
            ['name' => 'Math Exam', 'date' => '2025-04-15', 'center' => 'Center A'],
            ['name' => 'Science Exam', 'date' => '2025-04-20', 'center' => 'Center B'],
            ['name' => 'History Exam', 'date' => '2025-04-25', 'center' => 'Center C'],
            ['name' => 'English Exam', 'date' => '2025-05-01', 'center' => 'Center D']
        ],
        'users' => [
            ['name' => 'Teacher 1', 'role' => 'Teacher', 'center' => 'Center A'],
            ['name' => 'Student 1', 'role' => 'Student', 'center' => 'Center B'],
            ['name' => 'Teacher 2', 'role' => 'Teacher', 'center' => 'Center C'],
            ['name' => 'Parent 1', 'role' => 'Parent', 'center' => 'Center D']
        ]
    ];
}