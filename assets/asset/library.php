<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Main Library Management Shortcode
function aspire_library_management_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'library-list';

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #e6f7ff, #cce5ff); min-height: 100vh;">
        <div class="row">
            <?php 
            $active_section = $section;
            include plugin_dir_path(__FILE__) . '../sidebar.php';
            ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'library-list':
                        echo library_list_shortcode();
                        break;
                    case 'library-add':
                        echo library_add_shortcode();
                        break;
                    case 'library-edit':
                        echo library_edit_shortcode();
                        break;
                    case 'library-transaction':
                        echo library_transaction_shortcode();
                        break;
                    case 'library-overdue':
                        echo library_overdue_shortcode();
                        break;
                    default:
                        echo library_list_shortcode(); // Default to list view
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_library_management', 'aspire_library_management_shortcode');

// Handlers remain unchanged
add_action('admin_post_add_library_book', 'handle_add_library_book');
function handle_add_library_book() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'library';
    $education_center_id = get_educational_center_data();

    if (!wp_verify_nonce($_POST['nonce'], 'library_nonce')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $book_id = sanitize_text_field($_POST['book_id']);
    $isbn = sanitize_text_field($_POST['isbn']);
    $title = sanitize_text_field($_POST['title']);
    $author = sanitize_text_field($_POST['author']);
    $quantity = intval($_POST['quantity']);

    if (empty($book_id) || empty($title) || empty($author) || $quantity < 0) {
        wp_die('Invalid input data.', 'Validation Error', ['back_link' => true]);
    }

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if ($exists > 0) {
        wp_die('Book ID already exists!', 'Duplicate Error', ['back_link' => true]);
    }

    $wpdb->insert(
        $table_name,
        [
            'book_id' => $book_id,
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'quantity' => $quantity,
            'available' => $quantity,
            'education_center_id' => $education_center_id
        ],
        ['%s', '%s', '%s', '%s', '%d', '%d', '%s']
    );

    if ($wpdb->last_error) {
        wp_die('Failed to add book: ' . esc_html($wpdb->last_error), 'Database Error', ['back_link' => true]);
    }

    wp_redirect(home_url('/institute-dashboard/library/?section=library-list'));
    exit;
}

add_action('admin_post_edit_library_book', 'handle_edit_library_book');
function handle_edit_library_book() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'library';
    $education_center_id = get_educational_center_data();

    if (!wp_verify_nonce($_POST['nonce'], 'library_edit_nonce')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $book_id = sanitize_text_field($_POST['book_id']);
    $isbn = sanitize_text_field($_POST['isbn']);
    $title = sanitize_text_field($_POST['title']);
    $author = sanitize_text_field($_POST['author']);
    $quantity = intval($_POST['quantity']);
    $available = intval($_POST['available']);

    $wpdb->update(
        $table_name,
        [
            'isbn' => $isbn,
            'title' => $title,
            'author' => $author,
            'quantity' => $quantity,
            'available' => $available
        ],
        ['book_id' => $book_id, 'education_center_id' => $education_center_id],
        ['%s', '%s', '%s', '%d', '%d'],
        ['%s', '%s']
    );

    if ($wpdb->last_error) {
        wp_die('Failed to update book: ' . esc_html($wpdb->last_error), 'Database Error', ['back_link' => true]);
    }

    wp_redirect(home_url('/institute-dashboard/library/?section=library-list'));
    exit;
}

add_action('admin_post_library_transaction', 'handle_library_transaction');
function handle_library_transaction() {
    global $wpdb;
    $library_table = $wpdb->prefix . 'library';
    $trans_table = $wpdb->prefix . 'library_transactions';
    $education_center_id = get_educational_center_data();

    if (!wp_verify_nonce($_POST['nonce'], 'library_transaction_nonce')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $book_id = sanitize_text_field($_POST['book_id']);
    $user_id = sanitize_text_field($_POST['user_id']);
    $user_type = sanitize_text_field($_POST['user_type']);
    $action = sanitize_text_field($_POST['action']);

    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $library_table WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        wp_die('Book not found.', 'Error', ['back_link' => true]);
    }

    if ($action === 'Borrow' && $book->available <= 0) {
        wp_die('No copies available.', 'Stock Error', ['back_link' => true]);
    }

    if ($action === 'Borrow') {
        $due_date = date('Y-m-d H:i:s', strtotime('+14 days'));
        $wpdb->insert(
            $trans_table,
            [
                'book_id' => $book_id,
                'user_id' => $user_id,
                'user_type' => $user_type,
                'issue_date' => current_time('mysql'),
                'due_date' => $due_date
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );
        $new_available = $book->available - 1;
    } else { // Return
        $trans = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $trans_table WHERE book_id = %s AND user_id = %s AND return_date IS NULL",
            $book_id, $user_id
        ));
        if (!$trans) {
            wp_die('No active borrowing record found.', 'Error', ['back_link' => true]);
        }

        $return_date = current_time('mysql');
        $days_overdue = max(0, (strtotime($return_date) - strtotime($trans->due_date)) / (60 * 60 * 24));
        $fine = $days_overdue * 0.50;

        $wpdb->update(
            $trans_table,
            ['return_date' => $return_date, 'fine' => $fine],
            ['transaction_id' => $trans->transaction_id],
            ['%s', '%f'],
            ['%d']
        );
        $new_available = $book->available + 1;
    }

    $wpdb->update(
        $library_table,
        ['available' => $new_available],
        ['book_id' => $book_id, 'education_center_id' => $education_center_id],
        ['%d'],
        ['%s', '%s']
    );

    wp_redirect(home_url('/institute-dashboard/library/?section=library-list'));
    exit;
}

// Updated Shortcodes with distinct UIs
function library_list_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $table_name = $wpdb->prefix . 'library';
    $books = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $education_center_id));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #007bff;">
        <div class="card-header bg-primary text-white" style="border-radius: 12px 12px 0 0; padding: 1.5rem;">
            <h3 class="card-title mb-0"><i class="bi bi-book me-2"></i>Library Catalog</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between mb-3">
                <p class="text-muted">"Browse and manage your library collection"</p>
                <a href="?section=library-add" class="btn btn-success btn-sm">+ New Book</a>
            </div>
            <input type="text" id="librarySearch" class="form-control mb-4" placeholder="Search books..." style="border-radius: 20px;" onkeyup="filterTable(this, 'libraryTable')">
            <div class="table-responsive">
                <table id="libraryTable" class="table table-hover">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>ID</th>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Total</th>
                            <th>Available</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($books)) {
                            echo '<tr><td colspan="7" class="text-center py-4">No books found in the library.</td></tr>';
                        } else {
                            foreach ($books as $book) {
                                echo '<tr>';
                                echo '<td>' . esc_html($book->book_id) . '</td>';
                                echo '<td>' . esc_html($book->isbn) . '</td>';
                                echo '<td>' . esc_html($book->title) . '</td>';
                                echo '<td>' . esc_html($book->author) . '</td>';
                                echo '<td>' . esc_html($book->quantity) . '</td>';
                                echo '<td>' . ($book->available < 1 ? '<span class="badge bg-danger">Out</span>' : esc_html($book->available)) . '</td>';
                                echo '<td>
                                    <div class="btn-group">
                                        <a href="?section=library-transaction&book_id=' . urlencode($book->book_id) . '" class="btn btn-sm btn-primary">Transact</a>
                                        <a href="?section=library-edit&book_id=' . urlencode($book->book_id) . '" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="?section=library-list&action=delete&book_id=' . urlencode($book->book_id) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</a>
                                    </div>
                                </td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <a href="?section=library-overdue" class="btn btn-outline-info mt-3">View Overdue Books</a>
        </div>
    </div>
    <script>
    function filterTable(input, tableId) {
        const filter = input.value.toLowerCase();
        const table = document.getElementById(tableId);
        const rows = table.getElementsByTagName('tr');
        for (let i = 1; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;
            for (let j = 0; j < cells.length - 1; j++) {
                if (cells[j].textContent.toLowerCase().includes(filter)) {
                    match = true;
                    break;
                }
            }
            rows[i].style.display = match ? '' : 'none';
        }
    }
    </script>
    <?php
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['book_id'])) {
        $book_id = sanitize_text_field($_GET['book_id']);
        $wpdb->delete($table_name, ['book_id' => $book_id, 'education_center_id' => $education_center_id], ['%s', '%s']);
        wp_redirect(home_url('/institute-dashboard/library/?section=library-list'));
        exit;
    }
    return ob_get_clean();
}

function library_add_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $table_name = $wpdb->prefix . 'library';
    $new_book_id = generate_unique_id($wpdb, $table_name, 'BOOK-', $education_center_id);
    $message = is_wp_error($new_book_id) ? '<div class="alert alert-danger">' . esc_html($new_book_id->get_error_message()) . '</div>' : '';

    ob_start();
    ?>
    <div class="card shadow-lg" style="max-width: 800px; margin: 0 auto; border: 3px solid #28a745; background: #f8fff8;">
        <div class="card-header bg-success text-white text-center" style="border-radius: 10px 10px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-book-fill me-2"></i>Add New Book</h3>
        </div>
        <div class="card-body p-4">
            <?php echo $message; ?>
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_library_book">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="book_id" class="form-label fw-bold">Book ID</label>
                        <input type="text" name="book_id" id="book_id" class="form-control" value="<?php echo esc_attr($new_book_id); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="isbn" class="form-label fw-bold">ISBN</label>
                        <input type="text" name="isbn" id="isbn" class="form-control" placeholder="Enter ISBN">
                    </div>
                    <div class="col-md-12">
                        <label for="title" class="form-label fw-bold">Title</label>
                        <input type="text" name="title" id="title" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="author" class="form-label fw-bold">Author</label>
                        <input type="text" name="author" id="author" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-bold">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
                    </div>
                </div>
                <?php wp_nonce_field('library_nonce', 'nonce'); ?>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-success btn-lg">Add Book</button>
                    <a href="?section=library-list" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function library_edit_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $book_id = isset($_GET['book_id']) ? sanitize_text_field($_GET['book_id']) : '';
    
    if (empty($book_id)) {
        // return '<div class="alert alert-warning text-center">Please select a book to edit from the library list.</div>';
        $table_name = $wpdb->prefix . 'library';
        $books = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $education_center_id));
    
        ob_start();
        ?>
        <div class="card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #007bff;">
            <div class="card-header bg-primary text-white" style="border-radius: 12px 12px 0 0; padding: 1.5rem;">
                <h3 class="card-title mb-0"><i class="bi bi-book me-2"></i>Edit Library Catalog</h3>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3">
                    <p class="text-muted">"Browse and manage your library collection"</p>
                    <a href="?section=library-add" class="btn btn-success btn-sm">+ New Book</a>
                </div>
                <input type="text" id="librarySearch" class="form-control mb-4" placeholder="Search books..." style="border-radius: 20px;" onkeyup="filterTable(this, 'libraryTable')">
                <div class="table-responsive">
                    <table id="libraryTable" class="table table-hover">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th>ID</th>
                                <th>ISBN</th>
                                <th>Title</th>
                                <th>Author</th>
                                <th>Total</th>
                                <th>Available</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($books)) {
                                echo '<tr><td colspan="7" class="text-center py-4">No books found in the library.</td></tr>';
                            } else {
                                foreach ($books as $book) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($book->book_id) . '</td>';
                                    echo '<td>' . esc_html($book->isbn) . '</td>';
                                    echo '<td>' . esc_html($book->title) . '</td>';
                                    echo '<td>' . esc_html($book->author) . '</td>';
                                    echo '<td>' . esc_html($book->quantity) . '</td>';
                                    echo '<td>' . ($book->available < 1 ? '<span class="badge bg-danger">Out</span>' : esc_html($book->available)) . '</td>';
                                    echo '<td>
                                        <div class="btn-group">
                                            <a href="?section=library-edit&book_id=' . urlencode($book->book_id) . '" class="btn btn-sm btn-warning">Edit</a>
                                        </div>
                                    </td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <a href="?section=library-overdue" class="btn btn-outline-info mt-3">View Overdue Books</a>
            </div>
        </div>
        <script>
        function filterTable(input, tableId) {
            const filter = input.value.toLowerCase();
            const table = document.getElementById(tableId);
            const rows = table.getElementsByTagName('tr');
            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let match = false;
                for (let j = 0; j < cells.length - 1; j++) {
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        match = true;
                        break;
                    }
                }
                rows[i].style.display = match ? '' : 'none';
            }
        }
        </script>
        <?php
      
        return ob_get_clean();
    
    }

    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        return '<div class="alert alert-danger text-center">Book not found.</div>';
    }

    ob_start();
    ?>
    <div class="card shadow-lg" style="max-width: 800px; margin: 0 auto; border: 3px solid #ffc107; background: #fffef0;">
        <div class="card-header bg-warning text-dark text-center" style="border-radius: 10px 10px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Book: <?php echo esc_html($book->title); ?></h3>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit_library_book">
                <input type="hidden" name="book_id" value="<?php echo esc_attr($book_id); ?>">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="isbn" class="form-label fw-bold">ISBN</label>
                        <input type="text" name="isbn" id="isbn" class="form-control" value="<?php echo esc_attr($book->isbn); ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="title" class="form-label fw-bold">Title</label>
                        <input type="text" name="title" id="title" class="form-control" value="<?php echo esc_attr($book->title); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="author" class="form-label fw-bold">Author</label>
                        <input type="text" name="author" id="author" class="form-control" value="<?php echo esc_attr($book->author); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-bold">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo esc_attr($book->quantity); ?>" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label for="available" class="form-label fw-bold">Available</label>
                        <input type="number" name="available" id="available" class="form-control" value="<?php echo esc_attr($book->available); ?>" min="0" max="<?php echo esc_attr($book->quantity); ?>" required>
                    </div>
                </div>
                <?php wp_nonce_field('library_edit_nonce', 'nonce'); ?>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-warning btn-lg">Update Book</button>
                    <a href="?section=library-list" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function library_transaction_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $book_id = isset($_GET['book_id']) ? sanitize_text_field($_GET['book_id']) : '';

    if (empty($book_id)) {
        $trans_table = $wpdb->prefix . 'library_transactions';
        $transactions = $wpdb->get_results($wpdb->prepare(
            "SELECT t.*, l.title FROM $trans_table t JOIN {$wpdb->prefix}library l ON t.book_id = l.book_id WHERE l.education_center_id = %s",
            $education_center_id
        ));

        ob_start();
        ?>
        <div class="card shadow-lg" style="border: 3px solid #17a2b8; background: #f0faff;">
            <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                <h3 class="card-title mb-0"><i class="bi bi-arrow-left-right me-2"></i>Transaction History</h3>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-4">"View all borrowing and return activities"</p>
                <div class="table-responsive">
                    <table class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                        <thead style="background: #e6f3ff;">
                            <tr>
                                <th>Book ID</th>
                                <th>Title</th>
                                <th>User ID</th>
                                <th>User Type</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Return Date</th>
                                <th>Fine</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($transactions)) {
                                echo '<tr><td colspan="8" class="text-center py-4">No transactions recorded yet.</td></tr>';
                            } else {
                                foreach ($transactions as $trans) {
                                    echo '<tr>';
                                    echo '<td>' . esc_html($trans->book_id) . '</td>';
                                    echo '<td>' . esc_html($trans->title) . '</td>';
                                    echo '<td>' . esc_html($trans->user_id) . '</td>';
                                    echo '<td>' . esc_html($trans->user_type) . '</td>';
                                    echo '<td>' . esc_html($trans->issue_date) . '</td>';
                                    echo '<td>' . esc_html($trans->due_date) . '</td>';
                                    echo '<td>' . ($trans->return_date ? esc_html($trans->return_date) : '<span class="badge bg-warning">Pending</span>') . '</td>';
                                    echo '<td>' . number_format($trans->fine, 2) . '</td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <a href="?section=library-list" class="btn btn-outline-info mt-3">Back to Library</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        return '<div class="alert alert-danger text-center">Book not found.</div>';
    }

    ob_start();
    ?>
    <div class="card shadow-lg" style="max-width: 600px; margin: 0 auto; border: 3px solid #17a2b8; background: #f0faff;">
        <div class="card-header bg-info text-white text-center" style="border-radius: 10px 10px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-arrow-left-right me-2"></i>Transaction: <?php echo esc_html($book->title); ?></h3>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="library_transaction">
                <input type="hidden" name="book_id" value="<?php echo esc_attr($book_id); ?>">
                <div class="mb-4">
                    <label for="user_id" class="form-label fw-bold">User ID</label>
                    <input type="text" name="user_id" id="user_id" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label for="user_type" class="form-label fw-bold">User Type</label>
                    <select name="user_type" id="user_type" class="form-select" required>
                        <option value="Staff">Staff</option>
                        <option value="Student">Student</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="action" class="form-label fw-bold">Action</label>
                    <select name="action" id="action" class="form-select" required>
                        <option value="Borrow">Borrow</option>
                        <option value="Return">Return</option>
                    </select>
                </div>
                <?php wp_nonce_field('library_transaction_nonce', 'nonce'); ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-info btn-lg">Process</button>
                    <a href="?section=library-list" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function library_overdue_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';
    $overdue = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, l.title FROM $trans_table t 
         JOIN $library_table l ON t.book_id = l.book_id 
         WHERE t.return_date IS NULL AND t.due_date < %s AND l.education_center_id = %s",
        current_time('mysql'), $education_center_id
    ));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #dc3545; background: #fff0f0;">
        <div class="card-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Overdue Books</h3>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-4">"Track books that are past due"</p>
            <div class="table-responsive">
                <table class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #ffe6e6;">
                        <tr>
                            <th>Book ID</th>
                            <th>Title</th>
                            <th>User ID</th>
                            <th>User Type</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Fine</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($overdue)) {
                            echo '<tr><td colspan="8" class="text-center py-4">No overdue books at this time.</td></tr>';
                        } else {
                            foreach ($overdue as $item) {
                                $days_overdue = max(0, (strtotime(current_time('mysql')) - strtotime($item->due_date)) / (60 * 60 * 24));
                                $fine = $days_overdue * 0.50;
                                echo '<tr>';
                                echo '<td>' . esc_html($item->book_id) . '</td>';
                                echo '<td>' . esc_html($item->title) . '</td>';
                                echo '<td>' . esc_html($item->user_id) . '</td>';
                                echo '<td>' . esc_html($item->user_type) . '</td>';
                                echo '<td>' . esc_html($item->issue_date) . '</td>';
                                echo '<td>' . esc_html($item->due_date) . '</td>';
                                echo '<td>' . esc_html($days_overdue) . '</td>';
                                echo '<td>' . number_format($fine, 2) . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <a href="?section=library-list" class="btn btn-outline-danger mt-3">Back to Library</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}