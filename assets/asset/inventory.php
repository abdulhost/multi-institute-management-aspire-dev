<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Main Inventory Management Shortcode
function aspire_inventory_management_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    if (empty($education_center_id)) {
        return '<div class="alert alert-danger">No Educational Center found.</div>';
    }
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'inventory-list';

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #e6ffe6, #ccffcc); min-height: 100vh;">
        <div class="row">
            <?php 
            $active_section = $section;
            include plugin_dir_path(__FILE__) . '../sidebar.php';
            ?>
            <div class="col-md-9 p-4">
                <?php
                switch ($section) {
                    case 'inventory-list':
                        echo inventory_list_shortcode();
                        break;
                    case 'inventory-add':
                        echo inventory_add_shortcode();
                        break;
                    case 'inventory-edit':
                        echo inventory_edit_shortcode();
                        break;
                    case 'inventory-transaction':
                        echo inventory_transaction_shortcode();
                        break;
                    case 'inventory-issued':
                        echo inventory_issued_shortcode();
                        break;
                    default:
                        echo inventory_list_shortcode(); // Default to list view
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('aspire_inventory_management', 'aspire_inventory_management_shortcode');

// Utility: Generate unique IDs (unchanged)
function generate_unique_id($wpdb, $table_name, $prefix, $education_center_id) {
    $max_attempts = 5;
    for ($attempt = 1; $attempt <= $max_attempts; $attempt++) {
        $time_part = substr(str_replace('.', '', microtime(true)), -10);
        $random_part = strtoupper(substr(bin2hex(random_bytes(1)), 0, 2));
        $id = $prefix . $time_part . $random_part;

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE " . ($prefix === 'ITEM-' ? 'item_id' : 'book_id') . " = %s AND education_center_id = %s",
            $id, $education_center_id
        ));

        if ($exists == 0) {
            return $id;
        }
        usleep(10000);
    }
    return new WP_Error('id_generation_failed', 'Unable to generate a unique ID.');
}

// Inventory List Shortcode
function inventory_list_shortcode() {
    global $wpdb;
     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    $table_name = $wpdb->prefix . 'inventory';
    $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $education_center_id));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #28a745;">
        <div class="card-header bg-success text-white" style="border-radius: 12px 12px 0 0; padding: 1.5rem;">
            <h3 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i>Inventory Dashboard</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between mb-3">
                <p class="text-muted">"Manage your school’s inventory efficiently"</p>
                <a href="?section=inventory-add" class="btn btn-primary btn-sm">+ Add Item</a>
            </div>
            <input type="text" id="inventorySearch" class="form-control mb-4" placeholder="Search items..." style="border-radius: 20px;" onkeyup="filterTable(this, 'inventoryTable')">
            <div class="table-responsive">
                <table id="inventoryTable" class="table table-hover">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Low Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($items)) {
                            echo '<tr><td colspan="7" class="text-center py-4">No items found in inventory.</td></tr>';
                        } else {
                            foreach ($items as $item) {
                                $low_stock = $item->quantity <= $item->low_stock_threshold ? '<span class="badge bg-danger">Low</span>' : '<span class="badge bg-success">OK</span>';
                                echo '<tr>';
                                echo '<td>' . esc_html($item->item_id) . '</td>';
                                echo '<td>' . esc_html($item->name) . '</td>';
                                echo '<td>' . esc_html($item->category) . '</td>';
                                echo '<td>' . esc_html($item->quantity) . '</td>';
                                echo '<td>' . esc_html($item->status) . '</td>';
                                echo '<td>' . $low_stock . '</td>';
                                echo '<td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-info transact-item-btn" data-item-id="' . esc_attr($item->item_id) . '" data-nonce="' . wp_create_nonce('transact_item_' . $item->item_id) . '">Transact</button>
                                        <button class="btn btn-sm btn-warning edit-item-btn" data-item-id="' . esc_attr($item->item_id) . '" data-nonce="' . wp_create_nonce('edit_item_' . $item->item_id) . '">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-item-btn" data-item-id="' . esc_attr($item->item_id) . '" data-nonce="' . wp_create_nonce('delete_item_' . $item->item_id) . '">Delete</button>
                                    </div>
                                </td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <a href="?section=inventory-issued" class="btn btn-outline-success mt-3">View Issued Items</a>
        </div>
    </div>

    <!-- Custom Modals -->
    <div class="modal" id="editItemModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-warning text-dark p-3 mb-3" style="border-radius: 8px 8px 0 0;">Edit Inventory Item</h5>
            <div id="edit-item-form-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Close</button>
                <button type="button" class="btn btn-warning update-item-btn">Update Item</button>
            </div>
        </div>
    </div>

    <div class="modal" id="deleteItemModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-danger text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Confirm Deletion</h5>
            <div id="delete-item-container"></div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary modal-close-btn me-2">Cancel</button>
                <button type="button" class="btn btn-danger confirm-delete-btn">Delete</button>
            </div>
        </div>
    </div>

    <div class="modal" id="transactItemModal">
        <div class="modal-content">
            <span class="modal-close">×</span>
            <h5 class="bg-info text-white p-3 mb-3" style="border-radius: 8px 8px 0 0;">Process Transaction</h5>
            <div id="transact-item-form-container"></div>
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
            const editModal = document.getElementById('editItemModal');
            const editContainer = document.getElementById('edit-item-form-container');
            const editButtons = document.querySelectorAll('.edit-item-btn');
            const updateButton = document.querySelector('.update-item-btn');

            function showEditModal() { editModal.style.display = 'block'; }
            function hideEditModal() { editModal.style.display = 'none'; editContainer.innerHTML = ''; }

            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemId = this.getAttribute('data-item-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_inventory_edit_form');
                    data.append('item_id', itemId);
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
                            attachEditFormHandler(itemId);
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

            document.querySelectorAll('#editItemModal .modal-close, #editItemModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideEditModal);
            });
            window.addEventListener('click', function(e) { if (e.target === editModal) hideEditModal(); });

            function attachEditFormHandler(itemId) {
                const form = editContainer.querySelector('#edit-inventory-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        updateButton.disabled = true;
                        updateButton.textContent = 'Updating...';

                        const formData = new FormData(this);
                        formData.append('action', 'edit_inventory_item');
                        formData.append('item_id', itemId);

                        fetch(ajaxUrl, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                editContainer.innerHTML = '<div class="alert alert-success">Item updated successfully!</div>';
                                // setTimeout(() => {
                                //     hideEditModal();
                                //     location.reload();
                                // }, 1500);
                                window.location.href = window.location.href;

                            } else {
                                editContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error updating item') + '</div>';
                                updateButton.disabled = false;
                                updateButton.textContent = 'Update Item';
                            }
                        })
                        .catch(error => {
                            console.error('Update Error:', error);
                            editContainer.innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
                            updateButton.disabled = false;
                            updateButton.textContent = 'Update Item';
                        });
                    });
                    updateButton.onclick = () => form.dispatchEvent(new Event('submit'));
                }
            }

            // Delete Modal
            const deleteModal = document.getElementById('deleteItemModal');
            const deleteContainer = document.getElementById('delete-item-container');
            const deleteButtons = document.querySelectorAll('.delete-item-btn');
            const confirmDeleteButton = document.querySelector('.confirm-delete-btn');

            function showDeleteModal() { deleteModal.style.display = 'block'; }
            function hideDeleteModal() { deleteModal.style.display = 'none'; deleteContainer.innerHTML = ''; }

            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemId = this.getAttribute('data-item-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_inventory_delete_confirm');
                    data.append('item_id', itemId);
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
                            attachDeleteHandler(itemId, nonce);
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

            document.querySelectorAll('#deleteItemModal .modal-close, #deleteItemModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideDeleteModal);
            });
            window.addEventListener('click', function(e) { if (e.target === deleteModal) hideDeleteModal(); });

            function attachDeleteHandler(itemId, nonce) {
                confirmDeleteButton.onclick = function() {
                    confirmDeleteButton.disabled = true;
                    confirmDeleteButton.textContent = 'Deleting...';

                    const data = new FormData();
                    data.append('action', 'delete_inventory_item');
                    data.append('item_id', itemId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            deleteContainer.innerHTML = '<div class="alert alert-success">Item deleted successfully!</div>';
                            setTimeout(() => {
                                hideDeleteModal();
                                // location.reload();
                                window.location.href = window.location.href;

                            }, 1500);
                        } else {
                            deleteContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error deleting item') + '</div>';
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
            const transactModal = document.getElementById('transactItemModal');
            const transactContainer = document.getElementById('transact-item-form-container');
            const transactButtons = document.querySelectorAll('.transact-item-btn');
            const processButton = document.querySelector('.process-transaction-btn');

            function showTransactModal() { transactModal.style.display = 'block'; }
            function hideTransactModal() { transactModal.style.display = 'none'; transactContainer.innerHTML = ''; }

            transactButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemId = this.getAttribute('data-item-id');
                    const nonce = this.getAttribute('data-nonce');

                    this.disabled = true;
                    this.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_inventory_transaction_form');
                    data.append('item_id', itemId);
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
                            attachTransactionFormHandler(itemId);
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

            document.querySelectorAll('#transactItemModal .modal-close, #transactItemModal .modal-close-btn').forEach(btn => {
                btn.addEventListener('click', hideTransactModal);
            });
            window.addEventListener('click', function(e) { if (e.target === transactModal) hideTransactModal(); });

            function attachTransactionFormHandler(itemId) {
                const form = transactContainer.querySelector('#inventory-transaction-form');
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        processButton.disabled = true;
                        processButton.textContent = 'Processing...';

                        const formData = new FormData(this);
                        formData.append('action', 'process_inventory_transaction');
                        formData.append('item_id', itemId);

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
                                    // location.reload();
                                    window.location.href = window.location.href;

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

// Inventory Add Shortcode
function inventory_add_shortcode() {
    global $wpdb;
     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    $table_name = $wpdb->prefix . 'inventory';
    $new_item_id = generate_unique_id($wpdb, $table_name, 'ITEM-', $education_center_id);
    $message = is_wp_error($new_item_id) ? '<div class="alert alert-danger">' . esc_html($new_item_id->get_error_message()) . '</div>' : '';

    ob_start();
    ?>
    <div class="card shadow-lg" style="max-width: 800px; margin: 0 auto; border: 3px solid #28a745; background: #f8fff8;">
        <div class="card-header bg-success text-white text-center" style="border-radius: 10px 10px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-plus-circle me-2"></i>Add New Inventory Item</h3>
        </div>
        <div class="card-body p-4">
            <?php echo $message; ?>
            <form id="add-inventory-form" class="needs-validation" novalidate>
                <div class="row g-4">
                    <div class="col-md-6">
                        <label for="item_id" class="form-label fw-bold">Item ID</label>
                        <input type="text" name="item_id" id="item_id" class="form-control" value="<?php echo esc_attr($new_item_id); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="name" class="form-label fw-bold">Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label fw-bold">Category</label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="Equipment">Equipment</option>
                            <option value="Stationery">Stationery</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-bold">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="Available">Available</option>
                            <option value="Issued">Issued</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="low_stock_threshold" class="form-label fw-bold">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" id="low_stock_threshold" class="form-control" value="5" min="1" required>
                    </div>
                </div>
                <?php wp_nonce_field('inventory_nonce', 'nonce'); ?>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-success btn-lg">Add Item</button>
                    <!-- <a href="?section=inventory-list" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a> -->
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('add-inventory-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'add_inventory_item');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.insertAdjacentHTML('beforebegin', '<div class="alert alert-success">Item added successfully!</div>');
                    // setTimeout(() => location.href = '?section=inventory-list', 1500);
    window.location.href = window.location.href;
                } else {
                    this.insertAdjacentHTML('beforebegin', '<div class="alert alert-danger">' + (data.data.message || 'Error adding item') + '</div>');
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

// Inventory Edit Shortcode (Placeholder)
function inventory_edit_shortcode() {
    return '<div class="alert alert-info text-center">Please select an item to edit from the inventory list.</div>';
}

// Inventory Transaction Shortcode
function inventory_transaction_shortcode() {
    global $wpdb;
     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    $trans_table = $wpdb->prefix . 'inventory_transactions';
    $transactions = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, i.name FROM $trans_table t JOIN {$wpdb->prefix}inventory i ON t.item_id = i.item_id WHERE i.education_center_id = %s",
        $education_center_id
    ));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #17a2b8; background: #f0faff;">
        <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-arrow-left-right me-2"></i>Transaction History</h3>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-4">"Track all inventory transactions"</p>
            <div class="table-responsive">
                <table class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #e6f3ff;">
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>User ID</th>
                            <th>User Type</th>
                            <th>Action</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($transactions)) {
                            echo '<tr><td colspan="6" class="text-center py-4">No transactions recorded yet.</td></tr>';
                        } else {
                            foreach ($transactions as $trans) {
                                echo '<tr>';
                                echo '<td>' . esc_html($trans->item_id) . '</td>';
                                echo '<td>' . esc_html($trans->name) . '</td>';
                                echo '<td>' . esc_html($trans->user_id) . '</td>';
                                echo '<td>' . esc_html($trans->user_type) . '</td>';
                                echo '<td>' . esc_html($trans->action) . '</td>';
                                echo '<td>' . esc_html($trans->date) . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- <a href="?section=inventory-list" class="btn btn-outline-info mt-3">Back to Inventory</a> -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// Inventory Issued Shortcode
function inventory_issued_shortcode() {
    global $wpdb;
     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    $trans_table = $wpdb->prefix . 'inventory_transactions';
    $inventory_table = $wpdb->prefix . 'inventory';
    $issued = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, i.name FROM $trans_table t 
         JOIN $inventory_table i ON t.item_id = i.item_id 
         WHERE t.action = 'Issue' AND t.status = 'Completed' 
         AND NOT EXISTS (SELECT 1 FROM $trans_table r WHERE r.item_id = t.item_id AND r.user_id = t.user_id AND r.action = 'Return')
         AND i.education_center_id = %s",
        $education_center_id
    ));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #dc3545; background: #fff0f0;">
        <div class="card-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Currently Issued Items</h3>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-4">"View items currently issued and not returned"</p>
            <div class="table-responsive">
                <table class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #ffe6e6;">
                        <tr>
                            <th>Item ID</th>
                            <th>Item Name</th>
                            <th>User ID</th>
                            <th>User Type</th>
                            <th>Issue Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($issued)) {
                            echo '<tr><td colspan="5" class="text-center py-4">No items currently issued.</td></tr>';
                        } else {
                            foreach ($issued as $item) {
                                echo '<tr>';
                                echo '<td>' . esc_html($item->item_id) . '</td>';
                                echo '<td>' . esc_html($item->name) . '</td>';
                                echo '<td>' . esc_html($item->user_id) . '</td>';
                                echo '<td>' . esc_html($item->user_type) . '</td>';
                                echo '<td>' . esc_html($item->date) . '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <!-- <a href="?section=inventory-list" class="btn btn-outline-danger mt-3">Back to Inventory</a> -->
        </div>
    </div>
    <?php
    return ob_get_clean();
}

// AJAX Handlers
add_action('wp_ajax_add_inventory_item', 'ajax_add_inventory_item');
function ajax_add_inventory_item() {
    global $wpdb;
    check_ajax_referer('inventory_nonce', 'nonce');

    $table_name = $wpdb->prefix . 'inventory';
     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();

    $item_id = sanitize_text_field($_POST['item_id']);
    $name = sanitize_text_field($_POST['name']);
    $quantity = intval($_POST['quantity']);
    $status = sanitize_text_field($_POST['status']);
    $category = sanitize_text_field($_POST['category']);
    $low_stock_threshold = intval($_POST['low_stock_threshold']);

    if (empty($item_id) || empty($name) || $quantity < 0 || !in_array($status, ['Available', 'Issued', 'Damaged'])) {
        wp_send_json_error(['message' => 'Invalid input data']);
    }

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if ($exists > 0) {
        wp_send_json_error(['message' => 'Item ID already exists']);
    }

    $wpdb->insert(
        $table_name,
        [
            'item_id' => $item_id,
            'name' => $name,
            'quantity' => $quantity,
            'status' => $status,
            'category' => $category,
            'education_center_id' => $education_center_id,
            'low_stock_threshold' => $low_stock_threshold
        ],
        ['%s', '%s', '%d', '%s', '%s', '%s', '%d']
    );

    if ($wpdb->last_error) {
        wp_send_json_error(['message' => 'Failed to add item: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Item added successfully']);
}

add_action('wp_ajax_load_inventory_edit_form', 'load_inventory_edit_form');
function load_inventory_edit_form() {
    global $wpdb;
    $item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'edit_item_' . $item_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
    }

    ob_start();
    ?>
    <form id="edit-inventory-form" class="needs-validation" novalidate>
        <div class="row g-4">
            <div class="col-md-6">
                <label for="name" class="form-label fw-bold">Name</label>
                <input type="text" name="name" id="name" class="form-control" value="<?php echo esc_attr($item->name); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="category" class="form-label fw-bold">Category</label>
                <select name="category" id="category" class="form-select" required>
                    <option value="Equipment" <?php selected($item->category, 'Equipment'); ?>>Equipment</option>
                    <option value="Stationery" <?php selected($item->category, 'Stationery'); ?>>Stationery</option>
                    <option value="Furniture" <?php selected($item->category, 'Furniture'); ?>>Furniture</option>
                    <option value="Other" <?php selected($item->category, 'Other'); ?>>Other</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="quantity" class="form-label fw-bold">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo esc_attr($item->quantity); ?>" min="0" required>
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label fw-bold">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="Available" <?php selected($item->status, 'Available'); ?>>Available</option>
                    <option value="Issued" <?php selected($item->status, 'Issued'); ?>>Issued</option>
                    <option value="Damaged" <?php selected($item->status, 'Damaged'); ?>>Damaged</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="low_stock_threshold" class="form-label fw-bold">Low Stock Threshold</label>
                <input type="number" name="low_stock_threshold" id="low_stock_threshold" class="form-control" value="<?php echo esc_attr($item->low_stock_threshold); ?>" min="1" required>
            </div>
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('inventory_edit_nonce'); ?>">
    </form>
    <?php
    $form_html = ob_get_clean();
    wp_send_json_success(['form_html' => $form_html]);
}

add_action('wp_ajax_edit_inventory_item', 'ajax_edit_inventory_item');
function ajax_edit_inventory_item() {
    global $wpdb;
    check_ajax_referer('inventory_edit_nonce', 'nonce');

    $table_name = $wpdb->prefix . 'inventory';
     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();

    $item_id = sanitize_text_field($_POST['item_id']);
    $name = sanitize_text_field($_POST['name']);
    $quantity = intval($_POST['quantity']);
    $status = sanitize_text_field($_POST['status']);
    $category = sanitize_text_field($_POST['category']);
    $low_stock_threshold = intval($_POST['low_stock_threshold']);

    $updated = $wpdb->update(
        $table_name,
        [
            'name' => $name,
            'quantity' => $quantity,
            'status' => $status,
            'category' => $category,
            'low_stock_threshold' => $low_stock_threshold
        ],
        ['item_id' => $item_id, 'education_center_id' => $education_center_id],
        ['%s', '%d', '%s', '%s', '%d'],
        ['%s', '%s']
    );

    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to update item: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Item updated successfully']);
}

add_action('wp_ajax_load_inventory_delete_confirm', 'load_inventory_delete_confirm');
function load_inventory_delete_confirm() {
    global $wpdb;
    $item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'delete_item_' . $item_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
    }

    ob_start();
    ?>
    <p>Are you sure you want to delete the following item?</p>
    <ul>
        <li><strong>ID:</strong> <?php echo esc_html($item->item_id); ?></li>
        <li><strong>Name:</strong> <?php echo esc_html($item->name); ?></li>
        <li><strong>Category:</strong> <?php echo esc_html($item->category); ?></li>
        <li><strong>Quantity:</strong> <?php echo esc_html($item->quantity); ?></li>
        <li><strong>Status:</strong> <?php echo esc_html($item->status); ?></li>
    </ul>
    <?php
    $confirm_html = ob_get_clean();
    wp_send_json_success(['confirm_html' => $confirm_html]);
}

add_action('wp_ajax_delete_inventory_item', 'ajax_delete_inventory_item');
function ajax_delete_inventory_item() {
    global $wpdb;
    $item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'delete_item_' . $item_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    $deleted = $wpdb->delete(
        $wpdb->prefix . 'inventory',
        ['item_id' => $item_id, 'education_center_id' => $education_center_id],
        ['%s', '%s']
    );

    if ($deleted === false || $deleted === 0) {
        wp_send_json_error(['message' => 'Failed to delete item']);
    }

    wp_send_json_success(['message' => 'Item deleted successfully']);
}

add_action('wp_ajax_load_inventory_transaction_form', 'load_inventory_transaction_form');
function load_inventory_transaction_form() {
    global $wpdb;
    $item_id = isset($_POST['item_id']) ? sanitize_text_field($_POST['item_id']) : '';
    $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';

    if (!wp_verify_nonce($nonce, 'transact_item_' . $item_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();
    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
    }

    ob_start();
    ?>
    <form id="inventory-transaction-form" class="needs-validation" novalidate>
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
                <option value="Issue">Issue</option>
                <option value="Return">Return</option>
            </select>
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('inventory_transaction_nonce'); ?>">
    </form>
    <?php
    $form_html = ob_get_clean();
    wp_send_json_success(['form_html' => $form_html]);
}

add_action('wp_ajax_process_inventory_transaction', 'ajax_process_inventory_transaction');
function ajax_process_inventory_transaction() {
    global $wpdb;
    check_ajax_referer('inventory_transaction_nonce', 'nonce');

    $inventory_table = $wpdb->prefix . 'inventory';
    $trans_table = $wpdb->prefix . 'inventory_transactions';
     $current_user = wp_get_current_user();

    $education_center_id = is_teacher($current_user->ID) ? 
        educational_center_teacher_id() : 
        get_educational_center_data();

    $item_id = sanitize_text_field($_POST['item_id']);
    $user_id = sanitize_text_field($_POST['user_id']);
    $user_type = sanitize_text_field($_POST['user_type']);
    $action = sanitize_text_field($_POST['action']);

    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $inventory_table WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
    }

    if ($action === 'Issue' && $item->quantity <= 0) {
        wp_send_json_error(['message' => 'Item out of stock']);
    }

    if ($action === 'Return' && $item->status === 'Damaged') {
        wp_send_json_error(['message' => 'Cannot return a damaged item']);
    }

    $wpdb->insert(
        $trans_table,
        [
            'item_id' => $item_id,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'action' => $action,
            'date' => current_time('mysql'),
            'status' => 'Completed'
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s']
    );

    $new_quantity = $action === 'Issue' ? $item->quantity - 1 : $item->quantity + 1;
    $new_status = $new_quantity > 0 ? 'Available' : 'Issued';

    $wpdb->update(
        $inventory_table,
        ['quantity' => $new_quantity, 'status' => $new_status],
        ['item_id' => $item_id, 'education_center_id' => $education_center_id],
        ['%d', '%s'],
        ['%s', '%s']
    );

    if ($new_quantity <= $item->low_stock_threshold) {
        wp_mail(get_option('admin_email'), 'Low Stock Alert', "Item {$item->name} (ID: $item_id) is low on stock: $new_quantity remaining.");
    }

    wp_send_json_success(['message' => 'Transaction processed successfully']);
}