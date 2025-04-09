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
        wp_redirect(home_url('/login'));
        exit();
    }
    $section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'inventory-list';
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #e6ffe6, #ccffcc); min-height: 100vh;">
        <div class="row">
            <?php   echo render_admin_header(wp_get_current_user());
              if (!is_center_subscribed($education_center_id)) {
                  return render_subscription_expired_message($education_center_id);
              }
                        $active_section = $section;

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
                        case 'homework':
                            if ($action === 'add-homework') {
                                echo render_homework_add($user_id, $user_id);
                            } elseif ($action === 'edit-homework') {
                                $homework_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                echo render_homework_edit($user_id, $user_id, $homework_id);
                            } elseif ($action === 'delete-homework') {
                                $homework_id = isset($_GET['id']) ? intval($_GET['id']) : null;
                                handle_homework_delete($user_id, $homework_id);
                                echo render_homework_assignments($user_id, $user_id);
                            } else {
                                echo render_homework_assignments($user_id, $user_id);
                            }
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


// Inventory List Shortcode
function inventory_list_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to view inventory.</div>';
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $table_name = $wpdb->prefix . 'inventory';
    $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s ORDER BY item_id", $education_center_id));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #28a745; background: #f4fff4;">
        <div class="card-header bg-success text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i>Inventory Dashboard</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted">"Manage your school’s inventory efficiently"</p>
                <div class="d-flex align-items-center">
                    <input type="text" id="inventorySearch" class="form-control me-2" placeholder="Search items..." style="border-radius: 20px; width: 200px;">
                    <a href="?section=inventory-add" class="btn btn-primary me-2">+ Add Item</a>
                    <button id="exportCsv" class="btn btn-outline-success">Export to CSV</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="inventoryTable" class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #e6ffe6;">
                        <tr>
                            <th class="sortable" data-sort="item_id">ID</th>
                            <th class="sortable" data-sort="name">Name</th>
                            <th class="sortable" data-sort="category">Category</th>
                            <th class="sortable" data-sort="quantity">Quantity</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th>Low Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($items)) {
                            echo '<tr><td colspan="7" class="text-center py-4">No items found in inventory.</td></tr>';
                        } else {
                            foreach ($items as $index => $item) {
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
                                        <button class="btn btn-sm btn-info transact-item-btn" 
                                                data-index="' . $index . '" 
                                                data-item-id="' . esc_attr($item->item_id) . '" 
                                                data-nonce="' . wp_create_nonce('transact_item_' . $item->item_id) . '">Transact</button>
                                        <button class="btn btn-sm btn-warning edit-item-btn" 
                                                data-index="' . $index . '" 
                                                data-item-id="' . esc_attr($item->item_id) . '" 
                                                data-nonce="' . wp_create_nonce('edit_item_' . $item->item_id) . '">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-item-btn" 
                                                data-index="' . $index . '" 
                                                data-item-id="' . esc_attr($item->item_id) . '" 
                                                data-nonce="' . wp_create_nonce('delete_item_' . $item->item_id) . '">Delete</button>
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

    <!-- Transact Modal -->
    <div class="modal fade" id="transactItemModal" tabindex="-1" aria-labelledby="transactItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="transactItemModalLabel">Process Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="transact-item-form-container"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-info process-transaction-btn" disabled>Process</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title" id="editItemModalLabel">Edit Inventory Item</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="edit-item-form-container"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning update-item-btn" disabled>Update Item</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

            // Transact Modal Handling
            const transactModal = new bootstrap.Modal(document.getElementById('transactItemModal'));
            const transactButtons = document.querySelectorAll('.transact-item-btn');
            const processButton = document.querySelector('.process-transaction-btn');
            const transactContainer = document.getElementById('transact-item-form-container');

            transactButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemId = this.dataset.itemId;
                    const nonce = this.dataset.nonce;
                    button.disabled = true;
                    button.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_inventory_transaction_form');
                    data.append('item_id', itemId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok: ' + response.statusText);
                        return response.json();
                    })
                    .then(data => {
                        button.disabled = false;
                        button.textContent = 'Transact';
                        if (data.success) {
                            transactContainer.innerHTML = data.data.form_html;
                            transactModal.show();
                            const form = transactContainer.querySelector('#inventory-transaction-form');
                            processButton.disabled = false;
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    processTransaction(form, itemId, transactModal);
                                });
                                processButton.onclick = () => form.dispatchEvent(new Event('submit'));
                            }
                        } else {
                            transactContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Failed to load transaction form') + '</div>';
                            transactModal.show();
                        }
                    })
                    .catch(error => {
                        button.disabled = false;
                        button.textContent = 'Transact';
                        transactContainer.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                        transactModal.show();
                    });
                });
            });

            function processTransaction(form, itemId, modal) {
                const processBtn = document.querySelector('.process-transaction-btn');
                processBtn.disabled = true;
                processBtn.textContent = 'Processing...';

                const formData = new FormData(form);
                formData.append('action', 'process_inventory_transaction');
                formData.append('item_id', itemId);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        transactContainer.innerHTML = '<div class="alert alert-success">' + (data.data.message || 'Transaction processed successfully!') + '</div>';
                        setTimeout(() => {
                            modal.hide();
                            window.location.reload();
                        }, 1500);
                    } else {
                        transactContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error processing transaction') + '</div>';
                        processBtn.disabled = false;
                        processBtn.textContent = 'Process';
                    }
                })
                .catch(error => {
                    transactContainer.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                    processBtn.disabled = false;
                    processBtn.textContent = 'Process';
                });
            }

            // Edit Modal Handling
            const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));
            const editButtons = document.querySelectorAll('.edit-item-btn');
            const updateButton = document.querySelector('.update-item-btn');
            const editContainer = document.getElementById('edit-item-form-container');

            editButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const itemId = this.dataset.itemId;
                    const nonce = this.dataset.nonce;
                    button.disabled = true;
                    button.textContent = 'Loading...';

                    const data = new FormData();
                    data.append('action', 'load_inventory_edit_form');
                    data.append('item_id', itemId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        button.disabled = false;
                        button.textContent = 'Edit';
                        if (data.success) {
                            editContainer.innerHTML = data.data.form_html;
                            editModal.show();
                            const form = editContainer.querySelector('#edit-inventory-form');
                            updateButton.disabled = false;
                            if (form) {
                                form.addEventListener('submit', function(e) {
                                    e.preventDefault();
                                    updateItem(form, itemId, editModal);
                                });
                                updateButton.onclick = () => form.dispatchEvent(new Event('submit'));
                            }
                        } else {
                            editContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Failed to load form') + '</div>';
                            editModal.show();
                        }
                    })
                    .catch(error => {
                        button.disabled = false;
                        button.textContent = 'Edit';
                        editContainer.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                        editModal.show();
                    });
                });
            });

            function updateItem(form, itemId, modal) {
                const updateBtn = document.querySelector('.update-item-btn');
                updateBtn.disabled = true;
                updateBtn.textContent = 'Updating...';

                const formData = new FormData(form);
                formData.append('action', 'edit_inventory_item');
                formData.append('item_id', itemId);

                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        editContainer.innerHTML = '<div class="alert alert-success">' + (data.data.message || 'Item updated successfully!') + '</div>';
                        setTimeout(() => {
                            modal.hide();
                            window.location.reload();
                        }, 1500);
                    } else {
                        editContainer.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error updating item') + '</div>';
                        updateBtn.disabled = false;
                        updateBtn.textContent = 'Update Item';
                    }
                })
                .catch(error => {
                    editContainer.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                    updateBtn.disabled = false;
                    updateBtn.textContent = 'Update Item';
                });
            }

            // Search, Sort, and CSV Export (unchanged)
            const table = document.getElementById('inventoryTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const searchInput = document.getElementById('inventorySearch');

            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                rows.forEach(row => {
                    const text = Array.from(row.cells).slice(0, -1).map(cell => cell.textContent.toLowerCase()).join(' ');
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });

            const headers = table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sort;
                    const isAsc = header.classList.toggle('asc');
                    header.classList.toggle('desc', !isAsc);
                    const visibleRows = rows.filter(row => row.style.display !== 'none');
                    visibleRows.sort((a, b) => {
                        const aValue = a.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        const bValue = b.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        return isAsc ? aValue.localeCompare(bValue, { numeric: true }) : bValue.localeCompare(aValue, { numeric: true });
                    });
                    visibleRows.forEach(row => tbody.appendChild(row));
                });
            });

            document.getElementById('exportCsv').addEventListener('click', function() {
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                const csvContent = [
                    '"ID","Name","Category","Quantity","Status","Low Stock","Actions"',
                    ...visibleRows.map(row => Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`).join(','))
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'inventory_list_<?php echo esc_js($education_center_id); ?>.csv';
                link.click();
            });
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php
    return ob_get_clean();
}
add_shortcode('inventory_list', 'inventory_list_shortcode');

// Inventory Add Shortcode
function inventory_add_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to add inventory items.</div>';
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $new_item_id = get_unique_id_for_role('inventory', $education_center_id);
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
                        <div class="invalid-feedback">Please enter an item name.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label fw-bold">Category</label>
                        <select name="category" id="category" class="form-select" required>
                            <option value="" disabled selected>Select Category</option>
                            <option value="Equipment">Equipment</option>
                            <option value="Stationery">Stationery</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="invalid-feedback">Please select a category.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="quantity" class="form-label fw-bold">Quantity</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="0" required>
                        <div class="invalid-feedback">Please enter a valid quantity (0 or more).</div>
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold">Status</label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="" disabled selected>Select Status</option>
                            <option value="Available">Available</option>
                            <option value="Issued">Issued</option>
                            <option value="Damaged">Damaged</option>
                        </select>
                        <div class="invalid-feedback">Please select a status.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="low_stock_threshold" class="form-label fw-bold">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" id="low_stock_threshold" class="form-control" value="5" min="1" required>
                        <div class="invalid-feedback">Please enter a valid threshold (1 or more).</div>
                    </div>
                </div>
                <?php wp_nonce_field('inventory_nonce', 'nonce'); ?>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-success btn-lg">Add Item</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('add-inventory-form').addEventListener('submit', function(e) {
            e.preventDefault();
            if (!this.checkValidity()) {
                e.stopPropagation();
                this.classList.add('was-validated');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Adding...';

            const formData = new FormData(this);
            formData.append('action', 'add_inventory_item');

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const alertDiv = document.createElement('div');
                alertDiv.className = data.success ? 'alert alert-success' : 'alert alert-danger';
                alertDiv.textContent = data.success ? 'Item added successfully!' : (data.data.message || 'Error adding item');
                this.insertAdjacentElement('beforebegin', alertDiv);
                if (data.success) {
                    setTimeout(() => window.location.href = window.location.href, 1500);
                } else {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Item';
                }
            })
            .catch(error => {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.textContent = 'An error occurred: ' + error.message;
                this.insertAdjacentElement('beforebegin', alertDiv);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Add Item';
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('inventory_add', 'inventory_add_shortcode');

// Inventory Edit Shortcode (Placeholder)
function inventory_edit_shortcode() {
    return '<div class="alert alert-info text-center">Please select an item to edit from the inventory list.</div>';
}

// Inventory Transaction Shortcode
function inventory_transaction_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to view transaction history.</div>';
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $trans_table = $wpdb->prefix . 'inventory_transactions';
    $inventory_table = $wpdb->prefix . 'inventory';
    $items_per_page = 10;
    $current_page = isset($_GET['ipage']) ? max(1, intval($_GET['ipage'])) : 1;
    $offset = ($current_page - 1) * $items_per_page;

    $total_transactions = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $trans_table t JOIN $inventory_table i ON t.item_id = i.item_id WHERE i.education_center_id = %s", $education_center_id));
    $total_pages = ceil($total_transactions / $items_per_page);

    $transactions = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, i.name FROM $trans_table t 
         JOIN $inventory_table i ON t.item_id = i.item_id 
         WHERE i.education_center_id = %s 
         ORDER BY t.date DESC 
         LIMIT %d OFFSET %d",
        $education_center_id,
        $items_per_page,
        $offset
    ));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #17a2b8; background: #f0faff;">
        <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-arrow-left-right me-2"></i>Transaction History</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <p class="text-muted">"Track all inventory transactions (<?php echo esc_html($total_transactions); ?> total)"</p>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <input type="text" id="transactionSearch" class="form-control" placeholder="Search transactions..." style="border-radius: 20px; width: 200px;">
                    <select id="statusFilter" class="form-select" style="width: 150px;">
                        <option value="">All Statuses</option>
                        <option value="Completed">Completed</option>
                        <option value="Pending">Pending</option>
                    </select>
                    <button id="exportCsv" class="btn btn-outline-info">Export to CSV</button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="transactionTable" class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #e6f3ff;">
                        <tr>
                            <th class="sortable" data-sort="item_id">Item ID</th>
                            <th class="sortable" data-sort="name">Item Name</th>
                            <th class="sortable" data-sort="user_id">User ID</th>
                            <th class="sortable" data-sort="user_type">User Type</th>
                            <th class="sortable" data-sort="action">Action</th>
                            <th class="sortable" data-sort="status">Status</th>
                            <th class="sortable" data-sort="date">Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($transactions)) {
                            echo '<tr><td colspan="8" class="text-center py-4">No transactions recorded yet.</td></tr>';
                        } else {
                            foreach ($transactions as $trans) {
                                $status_badge = $trans->status === 'Completed' ? 'bg-success' : 'bg-warning';
                                echo '<tr>';
                                echo '<td>' . esc_html($trans->item_id) . '</td>';
                                echo '<td>' . esc_html($trans->name) . '</td>';
                                echo '<td>' . esc_html($trans->user_id) . '</td>';
                                echo '<td>' . esc_html($trans->user_type) . '</td>';
                                echo '<td>' . esc_html($trans->action) . '</td>';
                                echo '<td><span class="badge ' . $status_badge . '">' . esc_html($trans->status) . '</span></td>';
                                echo '<td>' . esc_html($trans->date) . '</td>';
                                echo '<td>';
                                if ($trans->status === 'Pending') {
                                    echo '<button class="btn btn-sm btn-success mark-complete-btn" data-trans-id="' . esc_attr($trans->transaction_id) . '" data-nonce="' . wp_create_nonce('mark_transaction_complete_' . $trans->transaction_id) . '">Mark as Complete</button>';
                                }
                                echo '</td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Transaction Pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?section=inventory-transaction&ipage=<?php echo $current_page - 1; ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                        <a class="page-link" href="?section=inventory-transaction&ipage=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?section=inventory-transaction&ipage=<?php echo $current_page + 1; ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            const table = document.getElementById('transactionTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const searchInput = document.getElementById('transactionSearch');
            const statusFilter = document.getElementById('statusFilter');

            function filterRows() {
                const filter = searchInput.value.toLowerCase();
                const status = statusFilter.value.toLowerCase();
                rows.forEach(row => {
                    const text = Array.from(row.cells).slice(0, -1).map(cell => cell.textContent.toLowerCase()).join(' ');
                    const statusMatch = status ? row.cells[5].textContent.toLowerCase() === status : true;
                    row.style.display = text.includes(filter) && statusMatch ? '' : 'none';
                });
            }

            searchInput.addEventListener('keyup', filterRows);
            statusFilter.addEventListener('change', filterRows);

            const headers = table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sort;
                    const isAsc = header.classList.toggle('asc');
                    header.classList.toggle('desc', !isAsc);
                    const visibleRows = rows.filter(row => row.style.display !== 'none');
                    visibleRows.sort((a, b) => {
                        const aValue = a.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        const bValue = b.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        return isAsc ? aValue.localeCompare(bValue, { numeric: true }) : bValue.localeCompare(aValue, { numeric: true });
                    });
                    visibleRows.forEach(row => tbody.appendChild(row));
                });
            });

            document.getElementById('exportCsv').addEventListener('click', function() {
                const visibleRows = rows.filter(row => row.style.display !== 'none');
                const csvContent = [
                    '"Item ID","Item Name","User ID","User Type","Action","Status","Date","Actions"',
                    ...visibleRows.map(row => Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`).join(','))
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'inventory_transactions_<?php echo esc_js($education_center_id); ?>.csv';
                link.click();
            });

            // Mark as Complete Handler
            const markCompleteButtons = document.querySelectorAll('.mark-complete-btn');
            markCompleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const transId = this.dataset.transId;
                    const nonce = this.dataset.nonce;
                    button.disabled = true;
                    button.textContent = 'Completing...';

                    const data = new FormData();
                    data.append('action', 'mark_inventory_transaction_complete');
                    data.append('transaction_id', transId);
                    data.append('nonce', nonce);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            button.parentElement.previousElementSibling.innerHTML = '<span class="badge bg-success">Completed</span>';
                            button.remove();
                        } else {
                            alert('Error: ' + (data.data.message || 'Failed to mark as complete'));
                            button.disabled = false;
                            button.textContent = 'Mark as Complete';
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                        button.disabled = false;
                        button.textContent = 'Mark as Complete';
                    });
                });
            });
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php
    return ob_get_clean();
}
add_shortcode('inventory_transaction', 'inventory_transaction_shortcode');

// AJAX Handler for Marking Transaction Complete
// AJAX Handler for Marking Transaction Complete
add_action('wp_ajax_mark_inventory_transaction_complete', 'mark_inventory_transaction_complete');
function mark_inventory_transaction_complete() {
    global $wpdb;
    $transaction_id = intval($_POST['transaction_id'] ?? 0);
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');

    if (!wp_verify_nonce($nonce, 'mark_transaction_complete_' . $transaction_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error(['message' => 'User not authenticated']);
        return;
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $trans_table = $wpdb->prefix . 'inventory_transactions';
    $inventory_table = $wpdb->prefix . 'inventory';

    $transaction = $wpdb->get_row($wpdb->prepare("SELECT t.*, i.quantity, i.low_stock_threshold FROM $trans_table t JOIN $inventory_table i ON t.item_id = i.item_id WHERE t.transaction_id = %d AND i.education_center_id = %s", $transaction_id, $education_center_id));
    if (!$transaction || $transaction->status === 'Completed') {
        wp_send_json_error(['message' => 'Transaction not found or already completed']);
        return;
    }

    $new_quantity = $transaction->action === 'Issue' ? $transaction->quantity - 1 : $transaction->quantity + 1;
    $new_status = $new_quantity > 0 ? 'Available' : 'Issued';

    $wpdb->query('START TRANSACTION');
    $updated_trans = $wpdb->update(
        $trans_table,
        ['status' => 'Completed'],
        ['transaction_id' => $transaction_id],
        ['%s'],
        ['%d']
    );

    $updated_inventory = $wpdb->update(
        $inventory_table,
        ['quantity' => $new_quantity, 'status' => $new_status],
        ['item_id' => $transaction->item_id, 'education_center_id' => $education_center_id],
        ['%d', '%s'],
        ['%s', '%s']
    );

    if ($updated_trans === false || $updated_inventory === false) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => 'Failed to update transaction: ' . $wpdb->last_error]);
        return;
    }

    $wpdb->query('COMMIT');

    if ($new_quantity <= $transaction->low_stock_threshold) {
        wp_mail(get_option('admin_email'), 'Low Stock Alert', "Item {$transaction->name} (ID: {$transaction->item_id}) is low on stock: $new_quantity remaining.");
    }

    wp_send_json_success(['message' => 'Transaction marked as complete']);
}
//
// Inventory Issued Shortcode
function inventory_issued_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to view issued items.</div>';
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $trans_table = $wpdb->prefix . 'inventory_transactions';
    $inventory_table = $wpdb->prefix . 'inventory';
    $issued = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, i.name FROM $trans_table t 
         JOIN $inventory_table i ON t.item_id = i.item_id 
         WHERE t.action = 'Issue' AND t.status = 'Completed' 
         AND NOT EXISTS (SELECT 1 FROM $trans_table r WHERE r.item_id = t.item_id AND r.user_id = t.user_id AND r.action = 'Return')
         AND i.education_center_id = %s ORDER BY t.date DESC",
        $education_center_id
    ));

    ob_start();
    ?>
    <div class="card shadow-lg" style="border: 3px solid #dc3545; background: #fff0f0;">
        <div class="card-header bg-danger text-white" style="border-radius: 15px 15px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Currently Issued Items</h3>
        </div>
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="text-muted">"View items currently issued and not returned"</p>
                <button id="exportCsv" class="btn btn-outline-danger">Export to CSV</button>
            </div>
            <div class="table-responsive">
                <table id="issuedTable" class="table table-striped" style="border-radius: 10px; overflow: hidden;">
                    <thead style="background: #ffe6e6;">
                        <tr>
                            <th class="sortable" data-sort="item_id">Item ID</th>
                            <th class="sortable" data-sort="name">Item Name</th>
                            <th class="sortable" data-sort="user_id">User ID</th>
                            <th class="sortable" data-sort="user_type">User Type</th>
                            <th class="sortable" data-sort="date">Issue Date</th>
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
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('issuedTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            const headers = table.querySelectorAll('.sortable');
            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const sortKey = header.dataset.sort;
                    const isAsc = header.classList.toggle('asc');
                    header.classList.toggle('desc', !isAsc);
                    rows.sort((a, b) => {
                        const aValue = a.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        const bValue = b.cells[[...headers].findIndex(h => h.dataset.sort === sortKey)].textContent;
                        return isAsc ? aValue.localeCompare(bValue, { numeric: true }) : bValue.localeCompare(aValue, { numeric: true });
                    });
                    rows.forEach(row => tbody.appendChild(row));
                });
            });

            document.getElementById('exportCsv').addEventListener('click', function() {
                const csvContent = [
                    '"Item ID","Item Name","User ID","User Type","Issue Date"',
                    ...rows.map(row => Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`).join(','))
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'inventory_issued_<?php echo esc_js($education_center_id); ?>.csv';
                link.click();
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('inventory_issued', 'inventory_issued_shortcode');

// AJAX Handlers
add_action('wp_ajax_add_inventory_item', 'ajax_add_inventory_item');
function ajax_add_inventory_item() {
    global $wpdb;
    if (!check_ajax_referer('inventory_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error(['message' => 'User not authenticated']);
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $table_name = $wpdb->prefix . 'inventory';
    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    $name = sanitize_text_field($_POST['name'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    $category = sanitize_text_field($_POST['category'] ?? '');
    $low_stock_threshold = intval($_POST['low_stock_threshold'] ?? 0);

    if (empty($item_id) || empty($name) || $quantity < 0 || !in_array($status, ['Available', 'Issued', 'Damaged']) || empty($category) || $low_stock_threshold < 1) {
        wp_send_json_error(['message' => 'Invalid or missing input data']);
    }

    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE item_id = %s AND education_center_id = %s", $item_id, $education_center_id));
    if ($exists > 0) {
        wp_send_json_error(['message' => 'Item ID already exists']);
    }

    $result = $wpdb->insert(
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

    if ($result === false) {
        wp_send_json_error(['message' => 'Failed to add item: ' . $wpdb->last_error]);
    }

    wp_send_json_success(['message' => 'Item added successfully']);
}

add_action('wp_ajax_load_inventory_edit_form', 'load_inventory_edit_form');
function load_inventory_edit_form() {
    global $wpdb;
    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');

    if (!wp_verify_nonce($nonce, 'edit_item_' . $item_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error(['message' => 'User not authenticated']);
        return;
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s", $item_id, $education_center_id));
    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
        return;
    }

    ob_start();
    ?>
    <form id="edit-inventory-form" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="item_id" class="form-label fw-bold">Item ID</label>
            <input type="text" name="item_id" id="item_id" class="form-control" value="<?php echo esc_attr($item->item_id); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label fw-bold">Name</label>
            <input type="text" name="name" id="name" class="form-control" value="<?php echo esc_attr($item->name); ?>" required>
            <div class="invalid-feedback">Please enter an item name.</div>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label fw-bold">Category</label>
            <select name="category" id="category" class="form-select" required>
                <option value="Equipment" <?php selected($item->category, 'Equipment'); ?>>Equipment</option>
                <option value="Stationery" <?php selected($item->category, 'Stationery'); ?>>Stationery</option>
                <option value="Furniture" <?php selected($item->category, 'Furniture'); ?>>Furniture</option>
                <option value="Other" <?php selected($item->category, 'Other'); ?>>Other</option>
            </select>
            <div class="invalid-feedback">Please select a category.</div>
        </div>
        <div class="mb-3">
            <label for="quantity" class="form-label fw-bold">Quantity</label>
            <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo esc_attr($item->quantity); ?>" min="0" required>
            <div class="invalid-feedback">Please enter a valid quantity (0 or more).</div>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label fw-bold">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="Available" <?php selected($item->status, 'Available'); ?>>Available</option>
                <option value="Issued" <?php selected($item->status, 'Issued'); ?>>Issued</option>
                <option value="Damaged" <?php selected($item->status, 'Damaged'); ?>>Damaged</option>
            </select>
            <div class="invalid-feedback">Please select a status.</div>
        </div>
        <div class="mb-3">
            <label for="low_stock_threshold" class="form-label fw-bold">Low Stock Threshold</label>
            <input type="number" name="low_stock_threshold" id="low_stock_threshold" class="form-control" value="<?php echo esc_attr($item->low_stock_threshold); ?>" min="1" required>
            <div class="invalid-feedback">Please enter a valid threshold (1 or more).</div>
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
    if (!check_ajax_referer('inventory_edit_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error(['message' => 'User not authenticated']);
        return;
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $table_name = $wpdb->prefix . 'inventory';

    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    $name = sanitize_text_field($_POST['name'] ?? '');
    $quantity = intval($_POST['quantity'] ?? -1); // Default to -1 to catch invalid input
    $status = sanitize_text_field($_POST['status'] ?? '');
    $category = sanitize_text_field($_POST['category'] ?? '');
    $low_stock_threshold = intval($_POST['low_stock_threshold'] ?? -1);

    if (empty($item_id) || empty($name) || $quantity < 0 || !in_array($status, ['Available', 'Issued', 'Damaged']) || empty($category) || $low_stock_threshold < 1) {
        wp_send_json_error(['message' => 'Invalid or missing input data']);
        return;
    }

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
        return;
    } elseif ($updated === 0) {
        wp_send_json_error(['message' => 'No changes made or item not found']);
        return;
    }

    if ($quantity <= $low_stock_threshold) {
        wp_mail(get_option('admin_email'), 'Low Stock Alert', "Item {$name} (ID: $item_id) is low on stock: $quantity remaining.");
    }

    wp_send_json_success(['message' => 'Item updated successfully']);
}

add_action('wp_ajax_load_inventory_delete_confirm', 'load_inventory_delete_confirm');
function load_inventory_delete_confirm() {
    global $wpdb;
    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');

    if (!wp_verify_nonce($nonce, 'delete_item_' . $item_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $current_user = wp_get_current_user();
    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }
    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s", $item_id, $education_center_id));

    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
    }

    ob_start();
    ?>
    <p>Are you sure you want to delete the following item?</p>
    <ul class="list-unstyled">
        <li><strong>ID:</strong> <?php echo esc_html($item->item_id); ?></li>
        <li><strong>Name:</strong> <?php echo esc_html($item->name); ?></li>
        <li><strong>Category:</strong> <?php echo esc_html($item->category); ?></li>
        <li><strong>Quantity:</strong> <?php echo esc_html($item->quantity); ?></li>
        <li><strong>Status:</strong> <?php echo esc_html($item->status); ?></li>
    </ul>
    <?php
    wp_send_json_success(['confirm_html' => ob_get_clean()]);
}

add_action('wp_ajax_delete_inventory_item', 'ajax_delete_inventory_item');
function ajax_delete_inventory_item() {
    global $wpdb;
    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');

    if (!wp_verify_nonce($nonce, 'delete_item_' . $item_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $current_user = wp_get_current_user();
    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }
    $deleted = $wpdb->delete($wpdb->prefix . 'inventory', ['item_id' => $item_id, 'education_center_id' => $education_center_id], ['%s', '%s']);

    if ($deleted === false) {
        wp_send_json_error(['message' => 'Failed to delete item: ' . $wpdb->last_error]);
    } elseif ($deleted === 0) {
        wp_send_json_error(['message' => 'Item not found']);
    }

    wp_send_json_success(['message' => 'Item deleted successfully']);
}

add_action('wp_ajax_load_inventory_transaction_form', 'load_inventory_transaction_form');
function load_inventory_transaction_form() {
    global $wpdb;
    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    $nonce = sanitize_text_field($_POST['nonce'] ?? '');

    if (!wp_verify_nonce($nonce, 'transact_item_' . $item_id)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error(['message' => 'User not authenticated']);
        return;
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s", $item_id, $education_center_id));
    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
        return;
    }

    // Fetch students from 'students' post type with ACF field 'student_id'
    $students = get_posts([
        'post_type' => 'students',
        'posts_per_page' => -1,
        'meta_query' => [
            [
                'key' => 'educational_center_id', // Assuming this ACF field links students to the center
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

    ob_start();
    ?>
    <form id="inventory-transaction-form" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="item_details" class="form-label fw-bold">Item Details</label>
            <input type="text" id="item_details" class="form-control" value="<?php echo esc_attr($item->name . ' (ID: ' . $item->item_id . ', Qty: ' . $item->quantity . ')'); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="user_select_<?php echo esc_attr($item_id); ?>" class="form-label fw-bold">Select User (Optional)</label>
            <select name="user_select" id="user_select_<?php echo esc_attr($item_id); ?>" class="form-select select2-transact" style="width: 100%;">
                <option value="" selected>Select a student or staff member</option>
                <optgroup label="Students">
                    <?php foreach ($students as $student) {
                        $student_id = get_field('student_id', $student->ID); // ACF field for student ID
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
            <label for="action" class="form-label fw-bold">Action</label>
            <select name="action" id="action" class="form-select" required>
                <option value="" disabled selected>Select action</option>
                <option value="Issue" <?php echo $item->quantity <= 0 ? 'disabled' : ''; ?>>Issue</option>
                <option value="Return">Return</option>
            </select>
            <div class="invalid-feedback">Please select an action.</div>
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('inventory_transaction_nonce'); ?>">
    </form>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Destroy any existing Select2 instance to avoid conflicts
            var $select = $('#user_select_<?php echo esc_js($item_id); ?>');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            // Initialize Select2 with search functionality
            $select.select2({
                placeholder: 'Search for a student or staff member...',
                allowClear: true,
                width: '100%',
                dropdownParent: $('#transactItemModal') // Ensure dropdown stays within modal
            });

            // Handle selection to update user_id and user_type
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

            // Allow manual override of user_id
            $('#user_id').on('input', function() {
                $select.val(null).trigger('change');
            });
        });
    </script>
    <?php
    $form_html = ob_get_clean();
    wp_send_json_success(['form_html' => $form_html]);
}

add_action('wp_ajax_process_inventory_transaction', 'ajax_process_inventory_transaction');
function ajax_process_inventory_transaction() {
    global $wpdb;
    if (!check_ajax_referer('inventory_transaction_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid security token']);
        return;
    }

    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        wp_send_json_error(['message' => 'User not authenticated']);
        return;
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (empty($education_center_id)) {
        wp_redirect(home_url('/login'));
        exit();
    }

    $inventory_table = $wpdb->prefix . 'inventory';
    $trans_table = $wpdb->prefix . 'inventory_transactions';

    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    $user_id = sanitize_text_field($_POST['user_id'] ?? '');
    $user_type = sanitize_text_field($_POST['user_type'] ?? '');
    $action = sanitize_text_field($_POST['action'] ?? '');

    if (empty($item_id) || empty($user_id) || !in_array($user_type, ['Staff', 'Student']) || !in_array($action, ['Issue', 'Return'])) {
        wp_send_json_error(['message' => 'Invalid or missing input data']);
        return;
    }

    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $inventory_table WHERE item_id = %s AND education_center_id = %s", $item_id, $education_center_id));
    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
        return;
    }

    if ($action === 'Issue') {
        if ($item->quantity <= 0) {
            wp_send_json_error(['message' => 'Item out of stock']);
            return;
        }
    } elseif ($action === 'Return') {
        $issued = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $trans_table 
             WHERE item_id = %s AND user_id = %s AND action = 'Issue' AND status = 'Completed' 
             AND NOT EXISTS (SELECT 1 FROM $trans_table r WHERE r.item_id = t.item_id AND r.user_id = t.user_id AND r.action = 'Return' AND r.status = 'Completed')",
            $item_id, $user_id
        ));
        if (!$issued) {
            wp_send_json_error(['message' => 'Item not issued to this user']);
            return;
        }
    }

    $new_quantity = $action === 'Issue' ? $item->quantity - 1 : $item->quantity + 1;
    $new_status = $new_quantity > 0 ? 'Available' : 'Issued';

    $wpdb->query('START TRANSACTION');
    $inserted = $wpdb->insert(
        $trans_table,
        [
            'item_id' => $item_id,
            'user_id' => $user_id,
            'user_type' => $user_type,
            'action' => $action,
            'date' => current_time('mysql'),
            'status' => 'Pending' // Initially pending, like a library checkout
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s']
    );

    if ($inserted === false) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => 'Failed to record transaction: ' . $wpdb->last_error]);
        return;
    }

    $wpdb->query('COMMIT');

    wp_send_json_success(['message' => 'Transaction recorded as pending. Please mark as complete when finalized.']);
}