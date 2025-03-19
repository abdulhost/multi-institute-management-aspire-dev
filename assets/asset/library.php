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


function generate_unique_id($wpdb, $table_name, $prefix, $education_center_id) {
    $max_attempts = 5;
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        $time_part = substr(str_replace('.', '', microtime(true)), -10);
        $random_part = strtoupper(substr(bin2hex(random_bytes(1)), 0, 2));
        $id = $prefix . $time_part . $random_part;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE " . ($prefix === 'BOOK-' ? 'book_id' : 'item_id') . " = %s AND education_center_id = %s",
            $id, $education_center_id
        ));

        if ($exists == 0) {
            return $id;
        }
        usleep(10000);
    }
    return new WP_Error('id_generation_failed', 'Unable to generate a unique ID.');
}

// Library List Shortcode
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
                                        <button class="btn btn-sm btn-primary transact-book-btn" data-book-id="' . esc_attr($book->book_id) . '" data-nonce="' . wp_create_nonce('transact_book_' . $book->book_id) . '">Transact</button>
                                        <button class="btn btn-sm btn-warning edit-book-btn" data-book-id="' . esc_attr($book->book_id) . '" data-nonce="' . wp_create_nonce('edit_book_' . $book->book_id) . '">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-book-btn" data-book-id="' . esc_attr($book->book_id) . '" data-nonce="' . wp_create_nonce('delete_book_' . $book->book_id) . '">Delete</button>
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

    <!-- Custom Modals -->
    <div class="modal" id="editBookModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-warning text-dark p-3 mb-3" style="border-radius: 8px 8px 0 0;">Edit Library Book</h5>
            <div id="edit-book-form-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Close</button>
                <button type="button" class="btn btn-warning update-book-btn">Update Book</button>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteBookModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-danger text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Deletion</h5>
            <div id="delete-book-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Cancel</button>
                <button type="button" class="btn btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <div class="modal" id="transactBookModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-info text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Process Transaction</h5>
            <div id="transact-book-form-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Close</button>
                <button type="button" class="btn btn-info process-transaction-btn">Process</button>
            </div>
        </div>
    </div>

    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            overflow: auto;
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            position: relative;
            animation: modalFade 0.3s ease;
        }
        .modal-close {
            position: absolute;
            right: 20px;
            top: 10px;
            color: #666;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s;
        }
        .modal-close:hover {
            color: #dc3545;
        }
        @keyframes modalFade {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

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

            // Edit Modal
            const editModal = document.getElementById('editBookModal');
            const editContainer = document.getElementById('edit-book-form-container');
            const editButtons = document.querySelectorAll('.edit-book-btn');
            const updateButton = document.querySelector('.update-book-btn');

            function showEditModal() { editModal.style.display = 'block'; }
            function hideEditModal() { editModal.style.display = 'none'; editContainer.innerHTML = ''; }

            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const bookId = this.getAttribute('data-book-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_library_edit_form');
                    data.append('book_id', bookId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            editContainer.innerHTML = data.data.form_html;
                            showEditModal();
                            attachEditFormHandler(bookId);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load edit form'));
                        }
                        this.disabled = false;
                        this.textContent = 'Edit';
                    })
                    .catch(error => {
                        console.error('Edit Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Edit';
                    });
                });
            });

            document.querySelectorAll('#editBookModal .modal-close, #editBookModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideEditModal);
            });
            window.addEventListener('click', function(e) { if (e.target === editModal) hideEditModal(); });

            function attachEditFormHandler(bookId) {
                const form = editContainer.querySelector('#edit-library-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        updateButton.disabled = true;
                        updateButton.textContent = 'Updating...';

                        const formData = new FormData(this);
                        formData.append('action', 'edit_library_book');
                        formData.append('book_id', bookId);

                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                editContainer.innerHTML = '<div class="alert alert-success">Book updated successfully!</div>';
                                setTimeout(() => {
                                    hideEditModal();
                                    location.reload();
                                }, 1500);
                            } else {
                                editContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error updating book') + '</div>';
                                updateButton.disabled = false;
                                updateButton.textContent = 'Update Book';
                            }
                        })
                        .catch(error => {
                            console.error('Update Error:', error);
                            editContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                            updateButton.disabled = false;
                            updateButton.textContent = 'Update Book';
                        });
                    });
                    updateButton.onclick = () => form.dispatchEvent(new Event('submit'));
                }
            }

            // Delete Modal
            const deleteModal = document.getElementById('deleteBookModal');
            const deleteContainer = document.getElementById('delete-book-container');
            const deleteButtons = document.querySelectorAll('.delete-book-btn');
            const confirmDeleteButton = document.querySelector('.confirm-delete-btn');

            function showDeleteModal() { deleteModal.style.display = 'block'; }
            function hideDeleteModal() { deleteModal.style.display = 'none'; deleteContainer.innerHTML = ''; }

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const bookId = this.getAttribute('data-book-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_library_delete_confirm');
                    data.append('book_id', bookId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteContainer.innerHTML = data.data.confirm_html;
                            showDeleteModal();
                            attachDeleteHandler(bookId, nonce);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load delete confirmation'));
                        }
                        this.disabled = false;
                        this.textContent = 'Delete';
                    })
                    .catch(error => {
                        console.error('Delete Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Delete';
                    });
                });
            });

            document.querySelectorAll('#deleteBookModal .modal-close, #deleteBookModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideDeleteModal);
            });
            window.addEventListener('click', function(e) { if (e.target === deleteModal) hideDeleteModal(); });

            function attachDeleteHandler(bookId, nonce) {
                confirmDeleteButton.onclick = function() {
                    confirmDeleteButton.disabled = true;
                    confirmDeleteButton.textContent = 'Deleting...';

                    const data = new FormData();
                    data.append('action', 'delete_library_book');
                    data.append('book_id', bookId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteContainer.innerHTML = '<div class="alert alert-success">Book deleted successfully!</div>';
                            setTimeout(() => {
                                hideDeleteModal();
                                location.reload();
                            }, 1500);
                        } else {
                            deleteContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error deleting book') + '</div>';
                            confirmDeleteButton.disabled = false;
                            confirmDeleteButton.textContent = 'Delete';
                        }
                    })
                    .catch(error => {
                        console.error('Delete Error:', error);
                        deleteContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                        confirmDeleteButton.disabled = false;
                        confirmDeleteButton.textContent = 'Delete';
                    });
                };
            }

            // Transaction Modal
            const transactModal = document.getElementById('transactBookModal');
            const transactContainer = document.getElementById('transact-book-form-container');
            const transactButtons = document.querySelectorAll('.transact-book-btn');
            const processButton = document.querySelector('.process-transaction-btn');

            function showTransactModal() { transactModal.style.display = 'block'; }
            function hideTransactModal() { transactModal.style.display = 'none'; transactContainer.innerHTML = ''; }

            transactButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const bookId = this.getAttribute('data-book-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_library_transaction_form');
                    data.append('book_id', bookId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            transactContainer.innerHTML = data.data.form_html;
                            showTransactModal();
                            attachTransactionFormHandler(bookId);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load transaction form'));
                        }
                        this.disabled = false;
                        this.textContent = 'Transact';
                    })
                    .catch(error => {
                        console.error('Transaction Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Transact';
                    });
                });
            });

            document.querySelectorAll('#transactBookModal .modal-close, #transactBookModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideTransactModal);
            });
            window.addEventListener('click', function(e) { if (e.target === transactModal) hideTransactModal(); });

            function attachTransactionFormHandler(bookId) {
                const form = transactContainer.querySelector('#library-transaction-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        processButton.disabled = true;
                        processButton.textContent = 'Processing...';

                        const formData = new FormData(this);
                        formData.append('action', 'process_library_transaction');
                        formData.append('book_id', bookId);

                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                transactContainer.innerHTML = '<div class="alert alert-success">Transaction processed successfully!</div>';
                                setTimeout(() => {
                                    hideTransactModal();
                                    location.reload();
                                }, 1500);
                            } else {
                                transactContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error processing transaction') + '</div>';
                                processButton.disabled = false;
                                processButton.textContent = 'Process';
                            }
                        })
                        .catch(error => {
                            console.error('Transaction Error:', error);
                            transactContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                            processButton.disabled = false;
                            processButton.textContent = 'Process';
                        });
                    });
                    processButton.onclick = () => form.dispatchEvent(new Event('submit'));
                }
            }
        });
    </script>
    <?php
    return ob_get_clean();
}

// Library Add Shortcode
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
            <form id="add-library-form" class="needs-validation" novalidate>
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
    <script>
        document.getElementById('add-library-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_library_book');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.insertAdjacentHTML('beforebegin', '<div class="alert alert-success">Book added successfully!</div>');
                    setTimeout(() => location.href = '?section=library-list', 1500);
                } else {
                    this.insertAdjacentHTML('beforebegin', '<div class="alert alert-danger">' + (data.data.message || 'Error adding book') + '</div>');
                }
            })
            .catch(error => {
                console.error('Add Error:', error);
                this.insertAdjacentHTML('beforebegin', '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>');
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// Library Edit Shortcode (Placeholder)
function library_edit_shortcode() {
    return '<div class="alert alert-info text-center">Please select a book to edit from the library list.</div>';
}

// Library Transaction Shortcode
function library_transaction_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
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

// Library Overdue Shortcode
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

// AJAX Handlers
add_action('wp_ajax_add_library_book', 'ajax_add_library_book');
function ajax_add_library_book() {
    global $wpdb;
    check_ajax_referer('library_nonce', 'nonce');

    $table_name = $wpdb->prefix . 'library';
    $education_center_id = get_educational_center_data();

    $book_id = sanitize_text_field($_POST['book_id']);
    $isbn = sanitize_text_field($_POST['isbn']);
    $title = sanitize_text_field($_POST['title']);
    $author = sanitize_text_field($_POST['author']);
    $quantity = intval($_POST['quantity']);

    if (empty($book_id) || empty($title) || empty($author) || $quantity < 0) {
        wp_send_json_error(['message' => 'Invalid input data']);
    }

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if ($exists > 0) {
        wp_send_json_error(['message' => 'Book ID already exists']);
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
        wp_send_json_error(['message' => 'Failed to add book: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Book added successfully']);
}

add_action('wp_ajax_load_library_edit_form', 'load_library_edit_form');
function load_library_edit_form() {
    global $wpdb;
    $book_id = isset($_POST['book_id']) ? sanitize_text_field($_POST['book_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'edit_book_' . $book_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $education_center_id = get_educational_center_data();
    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        wp_send_json_error(['message' => 'Book not found']);
    }

    ob_start();
    ?>
    <form id="edit-library-form" class="needs-validation" novalidate>
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
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('library_edit_nonce'); ?>">
    </form>
    <?php
    $form_html = ob_get_clean();
    wp_send_json_success(['form_html' => $form_html]);
}

add_action('wp_ajax_edit_library_book', 'ajax_edit_library_book');
function ajax_edit_library_book() {
    global $wpdb;
    check_ajax_referer('library_edit_nonce', 'nonce');

    $table_name = $wpdb->prefix . 'library';
    $education_center_id = get_educational_center_data();

    $book_id = sanitize_text_field($_POST['book_id']);
    $isbn = sanitize_text_field($_POST['isbn']);
    $title = sanitize_text_field($_POST['title']);
    $author = sanitize_text_field($_POST['author']);
    $quantity = intval($_POST['quantity']);
    $available = intval($_POST['available']);

    $updated = $wpdb->update(
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

    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to update book: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Book updated successfully']);
}

add_action('wp_ajax_load_library_delete_confirm', 'load_library_delete_confirm');
function load_library_delete_confirm() {
    global $wpdb;
    $book_id = isset($_POST['book_id']) ? sanitize_text_field($_POST['book_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'delete_book_' . $book_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $education_center_id = get_educational_center_data();
    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        wp_send_json_error(['message' => 'Book not found']);
    }

    ob_start();
    ?>
    <p>Are you sure you want to delete the following book?</p>
    <ul>
        <li><strong>ID:</strong> <?php echo esc_html($book->book_id); ?></li>
        <li><strong>ISBN:</strong> <?php echo esc_html($book->isbn); ?></li>
        <li><strong>Title:</strong> <?php echo esc_html($book->title); ?></li>
        <li><strong>Author:</strong> <?php echo esc_html($book->author); ?></li>
        <li><strong>Quantity:</strong> <?php echo esc_html($book->quantity); ?></li>
        <li><strong>Available:</strong> <?php echo esc_html($book->available); ?></li>
    </ul>
    <?php
    $confirm_html = ob_get_clean();
    wp_send_json_success(['confirm_html' => $confirm_html]);
}

add_action('wp_ajax_delete_library_book', 'ajax_delete_library_book');
function ajax_delete_library_book() {
    global $wpdb;
    $book_id = isset($_POST['book_id']) ? sanitize_text_field($_POST['book_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'delete_book_' . $book_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $education_center_id = get_educational_center_data();
    $deleted = $wpdb->delete(
        $wpdb->prefix . 'library',
        ['book_id' => $book_id, 'education_center_id' => $education_center_id],
        ['%s', '%s']
    );

    if ($deleted === false || $deleted === 0) {
        wp_send_json_error(['message' => 'Failed to delete book']);
    }

    wp_send_json_success(['message' => 'Book deleted successfully']);
}

add_action('wp_ajax_load_library_transaction_form', 'load_library_transaction_form');
function load_library_transaction_form() {
    global $wpdb;
    $book_id = isset($_POST['book_id']) ? sanitize_text_field($_POST['book_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'transact_book_' . $book_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $education_center_id = get_educational_center_data();
    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        wp_send_json_error(['message' => 'Book not found']);
    }

    ob_start();
    ?>
    <form id="library-transaction-form" class="needs-validation" novalidate>
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
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('library_transaction_nonce'); ?>">
    </form>
    <?php
    $form_html = ob_get_clean();
    wp_send_json_success(['form_html' => $form_html]);
}

add_action('wp_ajax_process_library_transaction', 'ajax_process_library_transaction');
function ajax_process_library_transaction() {
    global $wpdb;
    check_ajax_referer('library_transaction_nonce', 'nonce');

    $library_table = $wpdb->prefix . 'library';
    $trans_table = $wpdb->prefix . 'library_transactions';
    $education_center_id = get_educational_center_data();

    $book_id = sanitize_text_field($_POST['book_id']);
    $user_id = sanitize_text_field($_POST['user_id']);
    $user_type = sanitize_text_field($_POST['user_type']);
    $action = sanitize_text_field($_POST['action']);

    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $library_table WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        wp_send_json_error(['message' => 'Book not found']);
    }

    if ($action === 'Borrow' && $book->available <= 0) {
        wp_send_json_error(['message' => 'No copies available']);
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
            wp_send_json_error(['message' => 'No active borrowing record found']);
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

    wp_send_json_success(['message' => 'Transaction processed successfully']);
}