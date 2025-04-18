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
    wp_enqueue_script('export-utils', plugin_dir_url(__FILE__) . 'export-utils.js', ['jquery'], '1.0.0', true);
    wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', [], '2.5.1', true);
    wp_enqueue_script('papaparse', 'https://cdnjs.cloudflare.com/ajax/libs/PapaParse/5.3.2/papaparse.min.js', [], '5.3.2', true);
    wp_enqueue_script('xlsx', 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js', [], '0.18.5', true);
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
    <div id="edu-loader" class="edu-loader" style="display: none;">
        <div class="edu-loader-container">
            <img src="<?php echo plugin_dir_url(__FILE__) . '../custom-loader.png'; ?>" alt="Loading..." class="edu-loader-png">
        </div>
    </div>
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
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance'])); ?>" class="nav-item <?php echo $section === 'attendance' ? 'active' : ''; ?>">Attendance</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students'])); ?>" class="nav-item <?php echo $section === 'students' ? 'active' : ''; ?>">Students</a>
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework'])); ?>" class="nav-item <?php echo $section === 'homework' ? 'active' : ''; ?>">Homework</a>
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
                                        <li><a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams', 'demo-action' => 'add-exam'])); ?>" class="dropdown-link">Add Exam</a></li>
                                        <li><a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'add-homework'])); ?>" class="dropdown-link">Add Homework</a></li>
                                    <?php elseif ($role === 'superadmin'): ?>
                                        <li><a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'add-exam'])); ?>" class="dropdown-link">Add Exam</a></li>
                                        <li><a href="<?php echo esc_url(add_query_arg(['demo-role' => 'superadmin', 'demo-section' => 'subscription', 'demo-action' => 'add-subscription'])); ?>" class="dropdown-link">Add Subscription</a></li>
                                    <?php endif; ?>
                                    <li><a href="https://support.demo-pro.edu" target="_blank" class="dropdown-link">Support</a></li>
                                </ul>
                            </div>
                        </div>
                        <!-- Messages, Notifications, Settings, Help, Dark Mode, Profile (unchanged) -->
                        <div class="header-messages">
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'messages'])); ?>" class="action-btn" id="messages-toggle">
                                <i class="fas fa-envelope fa-lg"></i>
                                <span class="action-badge <?php echo $role ? '' : 'd-none'; ?>" id="messages-count">3</span>
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
                        <div class="header-notifications">
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'notifications'])); ?>" class="action-btn" id="notifications-toggle">
                                <i class="fas fa-bell fa-lg"></i>
                                <span class="action-badge <?php echo $role ? '' : 'd-none'; ?>" id="notifications-count">2</span>
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
                        <div class="header-settings">
                            <a href="<?php echo esc_url(add_query_arg(['demo-role' => $role, 'demo-section' => 'settings'])); ?>" class="action-btn" id="settings-toggle">
                                <i class="fas fa-cog fa-lg"></i>
                            </a>
                        </div>
                        <div class="header-help">
                            <a href="https://support.demo-pro.edu" target="_blank" class="action-btn" id="help-toggle">
                                <i class="fas fa-question-circle fa-lg"></i>
                            </a>
                        </div>
                        <div class="header-dark-mode">
                            <button class="action-btn" id="dark-mode-toggle">
                                <i class="fas fa-moon fa-lg"></i>
                            </button>
                        </div>
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
                            switch ($role) {
                                case 'superadmin':
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
    <style>
        .teacher-dashboard-section { margin-bottom: 2rem; }
        .summary-widget { background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .summary-widget h4 { margin: 0; }
        .card-exam { border: 1px solid #ddd; border-radius: 5px; padding: 1rem; margin-bottom: 1rem; }
        .status-badge { font-size: 0.85rem; padding: 0.25rem 0.5rem; border-radius: 10px; }
        .attendance-toggle { cursor: pointer; }
        .student-card { border: 1px solid #ddd; border-radius: 5px; padding: 1rem; text-align: center; }
        .homework-accordion .accordion-button { background: #e9ecef; }
    </style>
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
        const loader = document.getElementById('edu-loader');

        let activeDropdown = null;

        function showLoader() {
            loader.style.display = 'block';
        }

        function hideLoader() {
            loader.style.display = 'none';
        }

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
                showLoader();
                debounceTimer = setTimeout(() => {
                    const query = this.value.trim();
                    const section = '<?php echo $section; ?>';
                    if (query.length < 2) {
                        searchResults.classList.remove('visible');
                        hideLoader();
                        return;
                    }

                    searchResults.querySelector('.results-list').innerHTML = '<li>Loading...</li>';
                    searchResults.classList.add('visible');

                    const searchData = {
                        'exams': [
                            { title: 'Math Exam', url: '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams'])); ?>' },
                            { title: 'Science Exam', url: '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams'])); ?>' }
                        ],
                        'attendance': [
                            { title: 'John Doe - Present', url: '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance'])); ?>' },
                            { title: 'Jane Smith - Absent', url: '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance'])); ?>' }
                        ],
                        'students': [
                            { title: 'John Doe', url: '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students'])); ?>' },
                            { title: 'Jane Smith', url: '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students'])); ?>' }
                        ],
                        'homework': [
                            { title: 'Algebra Practice', url: '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework'])); ?>' },
                            { title: 'Physics Lab Report', url: '<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework'])); ?>' }
                        ]
                    };

                    const resultsList = searchResults.querySelector('.results-list');
                    resultsList.innerHTML = '';
                    const filtered = (searchData[section] || []).filter(item => item.title.toLowerCase().includes(query.toLowerCase()));
                    if (filtered.length > 0) {
                        filtered.forEach(item => {
                            resultsList.innerHTML += `<li><a href="${item.url}">${item.title}</a></li>`;
                        });
                    } else {
                        resultsList.innerHTML = '<li>No results found</li>';
                    }
                    hideLoader();
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

        // Export button handlers
        document.querySelectorAll('.export-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                showLoader();
                const type = e.target.dataset.type;
                const section = e.target.dataset.section;
                const data = window[section + 'Data'] || [];
                const filename = `${section}-${new Date().toISOString().split('T')[0]}`;
                setTimeout(() => {
                    if (type === 'pdf') {
                        exportToPDF(data, section, filename);
                    } else if (type === 'csv') {
                        exportToCSV(data, filename);
                    } else if (type === 'excel') {
                        exportToExcel(data, filename);
                    }
                    hideLoader();
                }, 500);
            });
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
 * Teacher Content Rendering
 */
function demoRenderTeacherContent($section, $action, $data) {
    switch ($section) {
        case 'exams':
            if ($action === 'add-exam') {
                return demoRenderTeacherAddExam();
            } elseif ($action === 'edit-exam') {
                return demoRenderTeacherEditExam();
            } elseif ($action === 'delete-exam') {
                return demoRenderTeacherDeleteExam();
            } else {
                return demoRenderTeacherExams($data);
            }
        case 'attendance':
            if ($action === 'add-student-attendance') {
                return demoRenderTeacherAddAttendance();
            } elseif ($action === 'edit-student-attendance') {
                return demoRenderTeacherEditAttendance();
            } elseif ($action === 'delete-student-attendance') {
                return demoRenderTeacherDeleteAttendance();
            } else {
                return demoRenderTeacherAttendance($data);
            }
        case 'students':
            if ($action === 'add-student') {
                return demoRenderTeacherAddStudent();
            } elseif ($action === 'edit-student') {
                return demoRenderTeacherEditStudent();
            } elseif ($action === 'delete-student') {
                return demoRenderTeacherDeleteStudent();
            } else {
                return demoRenderTeacherStudents($data);
            }
        case 'homework':
            if ($action === 'add-homework') {
                return demoRenderTeacherAddHomework();
            } elseif ($action === 'edit-homework') {
                return demoRenderTeacherEditHomework();
            } elseif ($action === 'delete-homework') {
                return demoRenderTeacherDeleteHomework();
            } else {
                return demoRenderTeacherHomework($data);
            }
        case 'profile':
            return demoRenderTeacherProfile($data);
        default:
            return demoRenderTeacherOverview($data);
    }
}

function demoRenderTeacherOverview($data) {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Teacher Dashboard</h2>
        <div class="alert alert-success">Welcome, <?php echo esc_html($data['profile']['name']); ?>! Manage your classes and students.</div>
        <div class="row">
            <div class="col-md-4">
                <div class="summary-widget">
                    <h4><i class="fas fa-book"></i> Exams</h4>
                    <p><?php echo count($data['exams']); ?> scheduled exams</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-widget">
                    <h4><i class="fas fa-calendar-check"></i> Attendance</h4>
                    <p><?php echo count($data['attendance']); ?> records today</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="summary-widget">
                    <h4><i class="fas fa-users"></i> Students</h4>
                    <p><?php echo count($data['students']); ?> students assigned</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherExams($data) {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Exams Management</h2>
        <div class="alert alert-primary">Manage exams for your classes.</div>
        <div class="summary-widget">
            <h4>Total Exams: <?php echo count($data['exams']); ?></h4>
        </div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams', 'demo-action' => 'add-exam'])); ?>" class="btn btn-primary">Add New Exam</a>
            <button class="btn btn-secondary export-btn" data-type="pdf" data-section="exams">Export to PDF</button>
            <button class="btn btn-secondary export-btn" data-type="csv" data-section="exams">Export to CSV</button>
            <button class="btn btn-secondary export-btn" data-type="excel" data-section="exams">Export to Excel</button>
        </div>
        <div class="row">
            <?php if (!empty($data['exams'])): ?>
                <?php foreach ($data['exams'] as $exam): ?>
                    <div class="col-md-6">
                        <div class="card-exam">
                            <h5><?php echo esc_html($exam['name']); ?></h5>
                            <p>Class: <?php echo esc_html($exam['class']); ?></p>
                            <p>Date: <?php echo esc_html($exam['date']); ?></p>
                            <span class="status-badge <?php echo strtotime($exam['date']) > time() ? 'bg-success' : 'bg-danger'; ?>">
                                <?php echo strtotime($exam['date']) > time() ? 'Upcoming' : 'Past'; ?>
                            </span>
                            <div class="mt-2">
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams', 'demo-action' => 'edit-exam', 'id' => $exam['name']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams', 'demo-action' => 'delete-exam', 'id' => $exam['name']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this exam?');">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p>No exams found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        window.examsData = <?php echo json_encode($data['exams']); ?>;
    </script>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddExam() {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Add New Exam</h2>
        <div class="alert alert-info">Fill in the details to add a new exam.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Exam</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditExam() {
    ob_start();
    $exam_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="teacher-dashboard-section">
        <h2>Edit Exam</h2>
        <div class="alert alert-info">Update the details for exam: <?php echo esc_html($exam_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="exam_name" class="form-label">Exam Name</label>
                <input type="text" class="form-control" id="exam_name" name="exam_name" value="<?php echo esc_html($exam_id); ?>" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Class 1A" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="2025-04-15" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Exam</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteExam() {
    ob_start();
    $exam_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="teacher-dashboard-section">
        <h2>Delete Exam</h2>
        <div class="alert alert-danger">Exam: <?php echo esc_html($exam_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams'])); ?>" class="btn btn-primary">Back to Exams</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAttendance($data) {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Attendance Management</h2>
        <div class="alert alert-warning">Manage student attendance for your classes.</div>
        <div class="summary-widget">
            <h4>Total Records: <?php echo count($data['student_attendance']); ?></h4>
        </div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'add-student-attendance'])); ?>" class="btn btn-primary">Add Attendance</a>
            <button class="btn btn-secondary export-btn" data-type="pdf" data-section="attendance">Export to PDF</button>
            <button class="btn btn-secondary export-btn" data-type="csv" data-section="attendance">Export to CSV</button>
            <button class="btn btn-secondary export-btn" data-type="excel" data-section="attendance">Export to Excel</button>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Class</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($data['student_attendance'])): ?>
                        <?php foreach ($data['student_attendance'] as $record): ?>
                            <tr>
                                <td><?php echo esc_html($record['student_id']); ?></td>
                                <td><?php echo esc_html($record['student_name']); ?></td>
                                <td><?php echo esc_html($record['class'] . ' ' . $record['section']); ?></td>
                                <td><?php echo esc_html($record['date']); ?></td>
                                <td>
                                    <span class="status-badge attendance-toggle <?php echo $record['status'] === 'Present' ? 'bg-success' : ($record['status'] === 'Absent' ? 'bg-danger' : 'bg-warning'); ?>" data-id="<?php echo esc_html($record['student_id']); ?>">
                                        <?php echo esc_html($record['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'edit-student-attendance', 'id' => $record['student_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'delete-student-attendance', 'id' => $record['student_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this attendance record?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No attendance records found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        window.attendanceData = <?php echo json_encode($data['student_attendance']); ?>;
        document.querySelectorAll('.attendance-toggle').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const statuses = ['Present', 'Absent', 'Late'];
                const current = toggle.textContent.trim();
                const next = statuses[(statuses.indexOf(current) + 1) % statuses.length];
                toggle.textContent = next;
                toggle.classList.remove('bg-success', 'bg-danger', 'bg-warning');
                toggle.classList.add(next === 'Present' ? 'bg-success' : (next === 'Absent' ? 'bg-danger' : 'bg-warning'));
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddAttendance() {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Add New Attendance</h2>
        <div class="alert alert-info">Fill in the details to add a new attendance record.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditAttendance() {
    ob_start();
    $student_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="teacher-dashboard-section">
        <h2>Edit Attendance</h2>
        <div class="alert alert-info">Update the details for student ID: <?php echo esc_html($student_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo esc_html($student_id); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="student_name" class="form-label">Student Name</label>
                <input type="text" class="form-control" id="student_name" name="student_name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="10" required>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Section</label>
                <input type="text" class="form-control" id="section" name="section" value="A" required>
            </div>
            <div class="mb-3">
                <label for="date" class="form-label">Date</label>
                <input type="date" class="form-control" id="date" name="date" value="2025-04-10" required>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-control" id="status" name="status" required>
                    <option value="Present" selected>Present</option>
                    <option value="Absent">Absent</option>
                    <option value="Late">Late</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Attendance</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteAttendance() {
    ob_start();
    $student_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="teacher-dashboard-section">
        <h2>Delete Attendance</h2>
        <div class="alert alert-danger">Attendance record for student ID: <?php echo esc_html($student_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance'])); ?>" class="btn btn-primary">Back to Attendance</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherStudents($data) {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Students Management</h2>
        <div class="alert alert-success">Manage your assigned students.</div>
        <div class="summary-widget">
            <h4>Total Students: <?php echo count($data['students']); ?></h4>
        </div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'add-student'])); ?>" class="btn btn-primary">Add New Student</a>
            <button class="btn btn-secondary export-btn" data-type="pdf" data-section="students">Export to PDF</button>
            <button class="btn btn-secondary export-btn" data-type="csv" data-section="students">Export to CSV</button>
            <button class="btn btn-secondary export-btn" data-type="excel" data-section="students">Export to Excel</button>
        </div>
        <div class="row">
            <?php if (!empty($data['students'])): ?>
                <?php foreach ($data['students'] as $student): ?>
                    <div class="col-md-4">
                        <div class="student-card">
                            <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../logo instituto.jpg'); ?>" alt="Student" class="img-fluid mb-2" style="width: 100px; border-radius: 50%;">
                            <h5><?php echo esc_html($student['name']); ?></h5>
                            <p>ID: <?php echo esc_html($student['id']); ?></p>
                            <p>Class: <?php echo esc_html($student['class']); ?></p>
                            <p>Email: <?php echo esc_html($student['email']); ?></p>
                            <div>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'edit-student', 'id' => $student['id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'delete-student', 'id' => $student['id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p>No students found.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>
        window.studentsData = <?php echo json_encode($data['students']); ?>;
    </script>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddStudent() {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Add New Student</h2>
        <div class="alert alert-info">Fill in the details to add a new student.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" required>
            </div>
            <div class="mb-3">
                <label for="center" class="form-label">Center</label>
                <input type="text" class="form-control" id="center" name="center" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Student</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditStudent() {
    ob_start();
    $student_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="teacher-dashboard-section">
        <h2>Edit Student</h2>
        <div class="alert alert-info">Update the details for student ID: <?php echo esc_html($student_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="student_id" class="form-label">Student ID</label>
                <input type="text" class="form-control" id="student_id" name="student_id" value="<?php echo esc_html($student_id); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="John Doe" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="john@demo-pro.edu" required>
            </div>
            <div class="mb-3">
                <label for="class" class="form-label">Class</label>
                <input type="text" class="form-control" id="class" name="class" value="Class 1A" required>
            </div>
            <div class="mb-3">
                <label for="center" class="form-label">Center</label>
                <input type="text" class="form-control" id="center" name="center" value="Center A" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Student</button>
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students'])); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherDeleteStudent() {
    ob_start();
    $student_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="teacher-dashboard-section">
        <h2>Delete Student</h2>
        <div class="alert alert-danger">Student ID: <?php echo esc_html($student_id); ?> has been deleted.</div>
        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students'])); ?>" class="btn btn-primary">Back to Students</a>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherHomework($data) {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Homework Management</h2>
        <div class="alert alert-info">Manage homework assignments for your classes.</div>
        <div class="summary-widget">
            <h4>Total Assignments: <?php echo count($data['homework']); ?></h4>
        </div>
        <div class="mb-3">
            <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'add-homework'])); ?>" class="btn btn-primary">Add New Homework</a>
            <button class="btn btn-secondary export-btn" data-type="pdf" data-section="homework">Export to PDF</button>
            <button class="btn btn-secondary export-btn" data-type="csv" data-section="homework">Export to CSV</button>
            <button class="btn btn-secondary export-btn" data-type="excel" data-section="homework">Export to Excel</button>
        </div>
        <div class="accordion homework-accordion" id="homeworkAccordion">
            <?php if (!empty($data['homework'])): ?>
                <?php foreach ($data['homework'] as $index => $homework): ?>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading<?php echo $index; ?>">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $index; ?>" aria-expanded="true" aria-controls="collapse<?php echo $index; ?>">
                                <?php echo esc_html($homework['title']); ?> (Due: <?php echo esc_html($homework['due_date']); ?>)
                            </button>
                        </h2>
                        <div id="collapse<?php echo $index; ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $index; ?>" data-bs-parent="#homeworkAccordion">
                            <div class="accordion-body">
                                <p><strong>Subject:</strong> <?php echo esc_html($homework['subject']); ?></p>
                                <p><strong>Class:</strong> <?php echo esc_html($homework['class']); ?></p>
                                <div>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'edit-homework', 'id' => $homework['homework_id']])); ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'delete-homework', 'id' => $homework['homework_id']])); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this homework?');">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No homework assignments found.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        window.homeworkData = <?php echo json_encode($data['homework']); ?>;
    </script>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherAddHomework() {
    ob_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Add New Homework</h2>
        <div class="alert alert-info">Fill in the details to add a new homework assignment.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
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
            <button type="submit" class="btn btn-primary">Add Homework</button>
<a href="<?php echo esc_url(add_query_arg(array('demo-role' => 'teacher', 'demo-section' => 'homework'))); ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

function demoRenderTeacherEditHomework() {
    ob_start();
    $homework_id = isset($_GET['id']) ? sanitize_text_field($_GET['id']) : '';
    ?>
    <div class="teacher-dashboard-section">
        <h2>Edit Homework</h2>
        <div class="alert alert-info">Update the details for homework ID: <?php echo esc_html($homework_id); ?>.</div>
        <form method="post" action="">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="Algebra Practice" required>
            </div>
            <div class="mbobj_start();
    ?>
    <div class="teacher-dashboard-section">
        <h2>Teacher Profile</h2>
        <div class="alert alert-info">View and manage your profile details.</div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '../logo instituto.jpg'); ?>" alt="Profile" class="img-fluid mb-3" style="width: 150px; border-radius: 50%;">
                    </div>
                    <div class="col-md-8">
                        <h4><?php echo esc_html($data['profile']['name']); ?></h4>
                        <p><strong>ID:</strong> <?php echo esc_html($data['profile']['teacher_id']); ?></p>
                        <p><strong>Email:</strong> <?php echo esc_html($data['profile']['email']); ?></p>
                        <p><strong>Subject:</strong> <?php echo esc_html($data['profile']['subject']); ?></p>
                        <p><strong>Center:</strong> <?php echo esc_html($data['profile']['center']); ?></p>
                        <a href="<?php echo esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'settings'])); ?>" class="btn btn-primary">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Sidebar Function (Updated for Teacher)
 */
function demoRenderSidebar($role, $active_section) {
    $active_action = isset($_GET['demo-action']) ? sanitize_text_field($_GET['demo-action']) : '';
    $links = [
        'teacher' => [
            ['section' => 'overview', 'label' => 'Overview', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'overview'])), 'icon' => 'tachometer-alt'],
            ['section' => 'exams', 'label' => 'Exams', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams'])), 'icon' => 'list', 'submenu' => [
                ['action' => 'add-exam', 'label' => 'Add Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams', 'demo-action' => 'add-exam']))],
                ['action' => 'edit-exam', 'label' => 'Edit Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams', 'demo-action' => 'edit-exam']))],
                ['action' => 'delete-exam', 'label' => 'Delete Exam', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'exams', 'demo-action' => 'delete-exam']))],
            ]],
            ['section' => 'attendance', 'label' => 'Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance'])), 'icon' => 'calendar-check', 'submenu' => [
                ['action' => 'add-student-attendance', 'label' => 'Add Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'add-student-attendance']))],
                ['action' => 'edit-student-attendance', 'label' => 'Edit Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'edit-student-attendance']))],
                ['action' => 'delete-student-attendance', 'label' => 'Delete Attendance', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'attendance', 'demo-action' => 'delete-student-attendance']))],
            ]],
            ['section' => 'students', 'label' => 'Students', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students'])), 'icon' => 'users', 'submenu' => [
                ['action' => 'add-student', 'label' => 'Add Student', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'add-student']))],
                ['action' => 'edit-student', 'label' => 'Edit Student', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'edit-student']))],
                ['action' => 'delete-student', 'label' => 'Delete Student', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'students', 'demo-action' => 'delete-student']))],
            ]],
            ['section' => 'homework', 'label' => 'Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework'])), 'icon' => 'tasks', 'submenu' => [
                ['action' => 'add-homework', 'label' => 'Add Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'add-homework']))],
                ['action' => 'edit-homework', 'label' => 'Edit Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'edit-homework']))],
                ['action' => 'delete-homework', 'label' => 'Delete Homework', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'homework', 'demo-action' => 'delete-homework']))],
            ]],
            ['section' => 'profile', 'label' => 'View Profile', 'url' => esc_url(add_query_arg(['demo-role' => 'teacher', 'demo-section' => 'profile'])), 'icon' => 'user'],
        ],
        // Other roles (unchanged)
        'student' => [...],
        'parent' => [...],
        'superadmin' => [...],
    ];
    ob_start();
    ?>
    <div class="sidebar">
        <ul class="sidebar-menu">
            <?php foreach ($links[$role] as $link): ?>
                <li class="<?php echo $active_section === $link['section'] ? 'active' : ''; ?>">
                    <a href="<?php echo $link['url']; ?>">
                        <i class="fas fa-<?php echo esc_attr($link['icon']); ?>"></i> <?php echo esc_html($link['label']); ?>
                    </a>
                    <?php if (!empty($link['submenu'])): ?>
                        <ul class="submenu">
                            <?php foreach ($link['submenu'] as $sub): ?>
                                <li class="<?php echo $active_action === $sub['action'] ? 'active' : ''; ?>">
                                    <a href="<?php echo $sub['url']; ?>"><?php echo esc_html($sub['label']); ?></a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Data Functions (Updated for Teacher)
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
        'student_attendance' => [
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-10', 'status' => 'Present'],
            ['student_id' => 'S002', 'student_name' => 'Jane Smith', 'class' => '10', 'section' => 'B', 'date' => '2025-04-10', 'status' => 'Absent'],
            ['student_id' => 'S001', 'student_name' => 'John Doe', 'class' => '10', 'section' => 'A', 'date' => '2025-04-11', 'status' => 'Late'],
        ],
        'students' => [
            ['id' => 'ST1001', 'name' => 'John Doe', 'email' => 'john@demo-pro.edu', 'class' => 'Class 1A', 'center' => 'Center A'],
            ['id' => 'ST1002', 'name' => 'Jane Smith', 'email' => 'jane@demo-pro.edu', 'class' => 'Class 1A', 'center' => 'Center A']
        ],
        'homework' => [
            ['homework_id' => 'HW001', 'title' => 'Algebra Practice', 'subject' => 'Mathematics', 'class' => 'Class 1A', 'due_date' => '2025-04-20'],
            ['homework_id' => 'HW002', 'title' => 'Physics Lab Report', 'subject' => 'Science', 'class' => 'Class 1A', 'due_date' => '2025-04-22']
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
?>