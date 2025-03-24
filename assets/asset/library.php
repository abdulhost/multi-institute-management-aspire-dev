<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Main Library Management Shortcode
function aspire_library_management_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
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
                    case 'library-delete':
                        echo library_delete_shortcode();
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


// Library List Shortcode
function library_list_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
    
    $table_name = $wpdb->prefix . 'library';
    $books = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $education_center_id));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #007bff;">
        <div class="card-header bg-primary text-white" style="border-radius: 12px 12px 0 0; padding: 1.5rem;">
            <h3 class="card-title mb-0"><i class="bi bi-book me-2"></i>Library Catalog</h3>
        </div>
        <div class="card-body p-4">
            <!-- <div class="d-flex justify-content-between mb-3">
                <p class="text-muted">"Browse and manage your library collection"</p>
                <a href="?section=library-add" class="btn btn-success btn-sm">+ New Book</a>
            </div> -->
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
            <!-- <a href="?section=library-overdue" class="btn btn-outline-info mt-3">View Overdue Books</a> -->
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
                                // setTimeout(() => {
                                //     hideEditModal();
                                //     location.reload();
                                // }, 1500);
                                window.location.href = window.location.href;

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
                            // setTimeout(() => {
                            //     hideDeleteModal();
                            //     location.reload();
                            // }, 1500);
                            window.location.href = window.location.href;

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
                                // setTimeout(() => {
                                //     hideTransactModal();
                                //     location.reload();
                                // }, 1500);
                                window.location.href = window.location.href;

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
    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
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
                    <!-- <a href="?section=library-list" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a> -->
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
                    // setTimeout(() => location.href = '?section=library-list', 1500);
                    window.location.href = window.location.href;

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
    global $wpdb;
    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
    
    $table_name = $wpdb->prefix . 'library';
    $books = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $education_center_id));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #007bff;">
        <div class="card-header bg-primary text-white" style="border-radius: 12px 12px 0 0; padding: 1.5rem;">
            <h3 class="card-title mb-0"><i class="bi bi-book me-2"></i>Library Catalog</h3>
        </div>
        <div class="card-body p-4">
            <!-- <div class="d-flex justify-content-between mb-3">
                <p class="text-muted">"Browse and manage your library collection"</p>
                <a href="?section=library-add" class="btn btn-success btn-sm">+ New Book</a>
            </div> -->
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
                                        <button class="btn btn-sm btn-warning edit-book-btn" data-book-id="' . esc_attr($book->book_id) . '" data-nonce="' . wp_create_nonce('edit_book_' . $book->book_id) . '">Edit</button>
                                    </div>
                                </td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- <a href="?section=library-overdue" class="btn btn-outline-info mt-3">View Overdue Books</a> -->
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
                                // setTimeout(() => {
                                //     hideEditModal();
                                //     location.reload();
                                // }, 1500);
                                window.location.href = window.location.href;

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
});
</script>
<?php
return ob_get_clean();
}

//delete
function library_delete_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
    
    $table_name = $wpdb->prefix . 'library';
    $books = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $education_center_id));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #007bff;">
        <div class="card-header bg-primary text-white" style="border-radius: 12px 12px 0 0; padding: 1.5rem;">
            <h3 class="card-title mb-0"><i class="bi bi-book me-2"></i>Library Catalog</h3>
        </div>
        <div class="card-body p-4">
            <!-- <div class="d-flex justify-content-between mb-3">
                <p class="text-muted">"Browse and manage your library collection"</p>
                <a href="?section=library-add" class="btn btn-success btn-sm">+ New Book</a>
            </div> -->
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
            <!-- <a href="?section=library-overdue" class="btn btn-outline-info mt-3">View Overdue Books</a> -->
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
                            // setTimeout(() => {
                            //     hideDeleteModal();
                            //     location.reload();
                            // }, 1500);
                            window.location.href = window.location.href;

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

          
        });
    </script>
    <?php
    return ob_get_clean();
}

// Library Transaction Shortcode
function library_transaction_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();

    // Determine educational_center_id based on user type
    if (is_teacher($current_user->ID)) {
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }

    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';

    // Get filter from URL parameter (default to 'all')
    $filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : 'all';
    $where_clause = '';
    if ($filter === 'active') {
        $where_clause = "AND t.return_date IS NULL";
    } elseif ($filter === 'returned') {
        $where_clause = "AND t.return_date IS NOT NULL";
    }

    // Fetch transactions
    $transactions = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, l.title 
         FROM $trans_table t 
         JOIN $library_table l ON t.book_id = l.book_id 
         WHERE l.education_center_id = %s $where_clause 
         ORDER BY t.issue_date DESC",
        $education_center_id
    ));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #17a2b8; background: #f0faff;">
        <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-arrow-left-right me-2"></i>Transaction History</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted">"View and manage all borrowing and return activities"</p>
                <div class="d-flex align-items-center">
                    <select id="transactionFilter" class="form-select me-2" style="width: 150px;" onchange="window.location.href='?section=library-transaction&filter='+this.value;">
                        <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>All Transactions</option>
                        <option value="active" <?php echo $filter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="returned" <?php echo $filter === 'returned' ? 'selected' : ''; ?>>Returned</option>
                    </select>
                    <button id="exportCsv" class="btn btn-outline-secondary">Export to CSV</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="transactionTable" class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #e6f3ff;">
                        <tr>
                            <th class="sortable" data-sort="book_id">Book ID</th>
                            <th class="sortable" data-sort="title">Title</th>
                            <th class="sortable" data-sort="user_id">User ID</th>
                            <th class="sortable" data-sort="user_type">User Type</th>
                            <th class="sortable" data-sort="issue_date">Issue Date</th>
                            <th class="sortable" data-sort="due_date">Due Date</th>
                            <th>Return Date</th>
                            <th>Fine</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($transactions)) {
                            echo '<tr><td colspan="9" class="text-center py-4">No transactions recorded yet.</td></tr>';
                        } else {
                            foreach ($transactions as $trans) {
                                $is_overdue = !$trans->return_date && strtotime($trans->due_date) < current_time('timestamp');
                                $days_overdue = $is_overdue ? max(0, (current_time('timestamp') - strtotime($trans->due_date)) / (60 * 60 * 24)) : 0;
                                $potential_fine = $days_overdue * 0.50;

                                echo '<tr>';
                                echo '<td>' . esc_html($trans->book_id) . '</td>';
                                echo '<td>' . esc_html($trans->title) . '</td>';
                                echo '<td>' . esc_html($trans->user_id) . '</td>';
                                echo '<td>' . esc_html($trans->user_type) . '</td>';
                                echo '<td>' . esc_html($trans->issue_date) . '</td>';
                                echo '<td>' . esc_html($trans->due_date) . '</td>';
                                echo '<td>' . ($trans->return_date ? esc_html($trans->return_date) : '<span class="badge bg-warning">Pending</span>') . '</td>';
                                echo '<td>' . ($trans->return_date ? number_format($trans->fine, 2) : ($is_overdue ? number_format($potential_fine, 2) . ' (Pending)' : '0.00')) . '</td>';
                                echo '<td>';
                                if (!$trans->return_date) {
                                    echo '<button class="btn btn-sm btn-primary return-book-btn" 
                                            data-transaction-id="' . esc_attr($trans->transaction_id) . '" 
                                            data-book-id="' . esc_attr($trans->book_id) . '" 
                                            data-user-id="' . esc_attr($trans->user_id) . '" 
                                            data-nonce="' . wp_create_nonce('return_book_' . $trans->transaction_id) . '">Return</button>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Return Confirmation Modal -->
    <div class="modal" id="returnBookModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-primary text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Return</h5>
            <div id="return-book-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Cancel</button>
                <button type="button" class="btn btn-primary confirm-return-btn">Return Book</button>
            </div>
        </div>
    </div>
<style>
    .sortable {
    cursor: pointer;
    position: relative;
    padding-right: 20px;
}
.sortable.asc::after {
    content: '↑';
    position: absolute;
    right: 5px;
}
.sortable.desc::after {
    content: '↓';
    position: absolute;
    right: 5px;
}
</style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            // Table Sorting
            const table = document.getElementById('transactionTable');
            const headers = table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sort;
                    const rows = Array.from(table.querySelector('tbody').rows);
                    const isAsc = header.classList.toggle('asc');
                    header.classList.toggle('desc', !isAsc);

                    rows.sort((a, b) => {
                        const aValue = a.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        const bValue = b.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        return isAsc ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
                    });

                    rows.forEach(row => table.querySelector('tbody').appendChild(row));
                });
            });

            // Return Modal Logic
            const returnModal = document.getElementById('returnBookModal');
            const returnContainer = document.getElementById('return-book-container');
            const returnButtons = document.querySelectorAll('.return-book-btn');
            const confirmReturnButton = document.querySelector('.confirm-return-btn');

            function showReturnModal() { returnModal.style.display = 'block'; }
            function hideReturnModal() { returnModal.style.display = 'none'; returnContainer.innerHTML = ''; }

            returnButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const transactionId = this.getAttribute('data-transaction-id');
                    const bookId = this.getAttribute('data-book-id');
                    const userId = this.getAttribute('data-user-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_return_confirmation');
                    data.append('transaction_id', transactionId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            returnContainer.innerHTML = data.data.confirm_html;
                            showReturnModal();
                            attachReturnHandler(transactionId, bookId, userId, nonce);
                        } else {
                            alert('Error: ' + (data.data.message || 'Unable to load return confirmation'));
                        }
                        this.disabled = false;
                        this.textContent = 'Return';
                    })
                    .catch(error => {
                        console.error('Return Load Error:', error);
                        alert('An error occurred: ' + error.message);
                        this.disabled = false;
                        this.textContent = 'Return';
                    });
                });
            });

            document.querySelectorAll('#returnBookModal .modal-close, #returnBookModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideReturnModal);
            });
            window.addEventListener('click', function(e) { if (e.target === returnModal) hideReturnModal(); });

            function attachReturnHandler(transactionId, bookId, userId, nonce) {
                confirmReturnButton.onclick = function() {
                    confirmReturnButton.disabled = true;
                    confirmReturnButton.textContent = 'Returning...';

                    const data = new FormData();
                    data.append('action', 'process_book_return');
                    data.append('transaction_id', transactionId);
                    data.append('book_id', bookId);
                    data.append('user_id', userId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            returnContainer.innerHTML = '<div class="alert alert-success">Book returned successfully!</div>';
                            setTimeout(() => {
                                hideReturnModal();
                                location.reload();
                            }, 1500);
                        } else {
                            returnContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error returning book') + '</div>';
                            confirmReturnButton.disabled = false;
                            confirmReturnButton.textContent = 'Return Book';
                        }
                    })
                    .catch(error => {
                        console.error('Return Error:', error);
                        returnContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                        confirmReturnButton.disabled = false;
                        confirmReturnButton.textContent = 'Return Book';
                    });
                };
            }

            // Export to CSV
            document.getElementById('exportCsv').addEventListener('click', function() {
                const rows = Array.from(document.querySelectorAll('#transactionTable tr'));
                const csvContent = rows.map(row => {
                    const cols = Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`);
                    return cols.join(',');
                }).join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'library_transactions.csv';
                link.click();
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX Handler for Return Confirmation
add_action('wp_ajax_load_return_confirmation', 'load_return_confirmation');
function load_return_confirmation() {
    global $wpdb;
    $transaction_id = isset($_POST['transaction_id']) ? sanitize_text_field($_POST['transaction_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'return_book_' . $transaction_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';
    $transaction = $wpdb->get_row($wpdb->prepare(
        "SELECT t.*, l.title 
         FROM $trans_table t 
         JOIN $library_table l ON t.book_id = l.book_id 
         WHERE t.transaction_id = %d AND t.return_date IS NULL",
        $transaction_id
    ));

    if (!$transaction) {
        wp_send_json_error(['message' => 'Transaction not found or already returned']);
    }

    ob_start();
    ?>
    <p>Are you sure you want to return the following book?</p>
    <ul>
        <li><strong>Book ID:</strong> <?php echo esc_html($transaction->book_id); ?></li>
        <li><strong>Title:</strong> <?php echo esc_html($transaction->title); ?></li>
        <li><strong>User ID:</strong> <?php echo esc_html($transaction->user_id); ?></li>
        <li><strong>User Type:</strong> <?php echo esc_html($transaction->user_type); ?></li>
        <li><strong>Issue Date:</strong> <?php echo esc_html($transaction->issue_date); ?></li>
        <li><strong>Due Date:</strong> <?php echo esc_html($transaction->due_date); ?></li>
    </ul>
    <?php
    $confirm_html = ob_get_clean();
    wp_send_json_success(['confirm_html' => $confirm_html]);
}

// AJAX Handler for Processing Return
add_action('wp_ajax_process_book_return', 'process_book_return');
function process_book_return() {
    global $wpdb;

    check_ajax_referer('return_book_' . sanitize_text_field($_POST['transaction_id']), 'nonce');

    $trans_table = $wpdb->prefix . 'library_transactions';
    $library_table = $wpdb->prefix . 'library';

    $transaction_id = sanitize_text_field($_POST['transaction_id']);
    $book_id = sanitize_text_field($_POST['book_id']);
    $user_id = sanitize_text_field($_POST['user_id']);

    // Get educational center ID
    $current_user = wp_get_current_user();
    if (is_teacher($current_user->ID)) {
        $education_center_id = educational_center_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
    }

    // Verify transaction exists and is active
    $trans = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $trans_table WHERE transaction_id = %d AND book_id = %s AND user_id = %s AND return_date IS NULL",
        $transaction_id, $book_id, $user_id
    ));

    if (!$trans) {
        wp_send_json_error(['message' => 'No active borrowing record found']);
    }

    // Calculate fine if overdue
    $return_date = current_time('mysql');
    $days_overdue = max(0, (strtotime($return_date) - strtotime($trans->due_date)) / (60 * 60 * 24));
    $fine = $days_overdue * 0.50;

    // Update transaction
    $result = $wpdb->update(
        $trans_table,
        ['return_date' => $return_date, 'fine' => $fine],
        ['transaction_id' => $transaction_id],
        ['%s', '%f'],
        ['%d']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to update transaction: ' . $wpdb->last_error]);
    }

    // Update book availability
    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT available FROM $library_table WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));
    $new_available = $book->available + 1;

    $update_result = $wpdb->update(
        $library_table,
        ['available' => $new_available],
        ['book_id' => $book_id, 'education_center_id' => $education_center_id],
        ['%d'],
        ['%s', '%s']
    );

    if ($update_result === false) {
        wp_send_json_error(['message' => 'Failed to update book availability: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Book returned successfully']);
}

// Library Overdue Shortcode
function library_overdue_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
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
            <!-- <a href="?section=library-list" class="btn btn-outline-danger mt-3">Back to Library</a> -->
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
    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }

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

    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
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
    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }

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

    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
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

    $current_user = wp_get_current_user();
    // Determine educational_center_id and teacher_id based on user type
    if (is_teacher($current_user->ID)) { 
        $education_center_id = educational_center_teacher_id();
        $current_teacher_id = aspire_get_current_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
        $current_teacher_id = get_current_teacher_id();
    }
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

    // Verify nonce
    if (!wp_verify_nonce($nonce, 'transact_book_' . $book_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    // Get educational center ID based on user type
    $current_user = wp_get_current_user();
    if (is_teacher($current_user->ID)) {
        $education_center_id = educational_center_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
    }

    // Fetch book details
    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}library WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        wp_send_json_error(['message' => 'Book not found']);
    }

    // Fetch students from 'student' post type with ACF field 'student_id'
    $students = get_posts([
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'educational_center_id',
                'value' => $education_center_id,
                'compare' => '='
            ]
        ]
    ]);

    // Fetch staff from 'wp_staff' table
    $staff = $wpdb->get_results($wpdb->prepare(
        "SELECT staff_id, name FROM {$wpdb->prefix}staff WHERE education_center_id = %s",
        $education_center_id
    ));

    // Generate form HTML
    ob_start();
    ?>
    <form id="library-transaction-form" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="user_select_<?php echo esc_attr($book_id); ?>" class="form-label fw-bold">Select User (Optional)</label>
            <select name="user_select" id="user_select_<?php echo esc_attr($book_id); ?>" class="form-select select2-transact" style="width: 100%;">
                <option value="" selected>Select a student or staff member</option>
                <optgroup label="Students">
                    <?php foreach ($students as $student) {
                        $student_id = get_field('student_id', $student->ID);
                        if ($student_id) {
                            echo '<option value="' . esc_attr($student_id) . '" data-type="Student">' . esc_html($student_id . ' - ' . $student->post_title) . '</option>';
                        }
                    } ?>
                </optgroup>
                <optgroup label="Staff">
                    <?php foreach ($staff as $staff_member) {
                        echo '<option value="' . esc_attr($staff_member->staff_id) . '" data-type="Staff">' . esc_html($staff_member->staff_id . ' - ' . $staff_member->name) . '</option>';
                    } ?>
                </optgroup>
            </select>
        </div>
        <div class="mb-3">
            <label for="user_id" class="form-label fw-bold">User ID</label>
            <input type="text" name="user_id" id="user_id" class="form-control" placeholder="Enter User ID or select from above" required>
            <div class="invalid-feedback">Please enter a valid User ID.</div>
        </div>
        <div class="mb-3">
            <label for="user_type" class="form-label fw-bold">User Type</label>
            <select name="user_type" id="user_type" class="form-control" required>
                <option value="" disabled selected>Select user type</option>
                <option value="Student">Student</option>
                <option value="Staff">Staff</option>
            </select>
            <div class="invalid-feedback">Please select a user type.</div>
        </div>
        <div class="mb-3">
            <label for="due_date" class="form-label fw-bold">Due Date</label>
            <input type="datetime-local" name="due_date" id="due_date" class="form-control" min="<?php echo date('Y-m-d\TH:i', current_time('timestamp')); ?>" required>
            <div class="invalid-feedback">Please select a valid due date in the future.</div>
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('library_transaction_nonce'); ?>">
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Destroy any existing Select2 instance to avoid conflicts
            var $select = $('#user_select_<?php echo esc_js($book_id); ?>');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            // Initialize Select2 with search functionality (extracted from sample)
            $select.select2({
                placeholder: 'Search for a student or staff member...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#transactBookModal') // Ensure dropdown stays within modal
            });

            // Handle selection to update user_id and user_type (adapted from sample)
            $select.on('change', function() {
                var userId = $(this).val();
                if (userId) {
                    var $selectedOption = $(this).find(':selected');
                    var userType = $selectedOption.data('type');
                    $('#user_id').val(userId);
                    $('#user_type').val(userType);
                } else {
                    $('#user_id').val('');
                    $('#user_type').val('');
                }
            });

            // Allow manual override of user_id (optional enhancement)
            $('#user_id').on('input', function() {
                $select.val(null).trigger('change');
            });
        });
    </script>
    <?php
    $form_html = ob_get_clean();
    wp_send_json_success(['form_html' => $form_html]);
}
add_action('wp_ajax_process_library_transaction', 'ajax_process_library_transaction');
function ajax_process_library_transaction() {
    global $wpdb;

    // Verify nonce
    check_ajax_referer('library_transaction_nonce', 'nonce');

    // Define table names
    $library_table = $wpdb->prefix . 'library';
    $trans_table = $wpdb->prefix . 'library_transactions';
    $staff_table = $wpdb->prefix . 'staff';

    // Get educational center ID based on user type
    $current_user = wp_get_current_user();
    if (is_teacher($current_user->ID)) {
        $education_center_id = educational_center_teacher_id();
    } else {
        $education_center_id = get_educational_center_data();
    }

    // Sanitize input data
    $book_id = sanitize_text_field($_POST['book_id']);
    $user_id = sanitize_text_field($_POST['user_id']);
    $user_type = sanitize_text_field($_POST['user_type']);
    $due_date = sanitize_text_field($_POST['due_date']);

    // Validate inputs
    if (empty($book_id) || empty($user_id) || empty($user_type) || empty($due_date)) {
        wp_send_json_error(['message' => 'Missing required fields']);
    }

    if (!in_array($user_type, ['Student', 'Staff'])) {
        wp_send_json_error(['message' => 'Invalid user type']);
    }

    // Validate due date
    $due_timestamp = strtotime($due_date);
    $current_timestamp = current_time('timestamp');
    if ($due_timestamp <= $current_timestamp) {
        wp_send_json_error(['message' => 'Due date must be in the future']);
    }
    $due_date_mysql = date('Y-m-d H:i:s', $due_timestamp);

    // Optional: Validate user_id existence
    if ($user_type === 'Student') {
        $student_exists = get_posts([
            'post_type' => 'students',
            'meta_query' => [
                [
                    'key' => 'student_id',
                    'value' => $user_id,
                    'compare' => '='
                ],
                [
                    'key' => 'educational_center_id',
                    'value' => $education_center_id,
                    'compare' => '='
                ]
            ]
        ]);
        if (empty($student_exists)) {
            // Allow manual entry even if not found, but log a warning
            error_log("Student ID $user_id not found for education_center_id $education_center_id");
        }
    } elseif ($user_type === 'Staff') {
        $staff_exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $staff_table WHERE staff_id = %s AND education_center_id = %s",
            $user_id, $education_center_id
        ));
        if ($staff_exists == 0) {
            // Allow manual entry even if not found, but log a warning
            error_log("Staff ID $user_id not found for education_center_id $education_center_id");
        }
    }

    // Fetch book details
    $book = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $library_table WHERE book_id = %s AND education_center_id = %s",
        $book_id, $education_center_id
    ));

    if (!$book) {
        wp_send_json_error(['message' => 'Book not found']);
    }

    if ($book->available <= 0) {
        wp_send_json_error(['message' => 'No copies available to borrow']);
    }

    // Insert borrowing transaction
    $result = $wpdb->insert(
        $trans_table,
        [
            'book_id' => $book_id,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'issue_date' => current_time('mysql'),
            'due_date' => $due_date_mysql,
            'return_date' => null,
            'fine' => 0.00
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s', '%f']
    );

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to record borrowing transaction: ' . $wpdb->last_error]);
    }

    // Update available quantity
    $new_available = $book->available - 1;
    $update_result = $wpdb->update(
        $library_table,
        ['available' => $new_available],
        ['book_id' => $book_id, 'education_center_id' => $education_center_id],
        ['%d'],
        ['%s', '%s']
    );

    if ($update_result === false) {
        wp_send_json_error(['message' => 'Failed to update book availability: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Book borrowed successfully']);
}
function enqueue_library_scripts() {
    wp_enqueue_style('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', [], '4.1.0');
    wp_enqueue_script('select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'enqueue_library_scripts');
add_action('admin_enqueue_scripts', 'enqueue_library_scripts'); // If used in admin