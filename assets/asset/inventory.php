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
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';

    ob_start();
    ?>
    <div class="container-fluid" style="background: linear-gradient(135deg, #e6ffe6, #ccffcc); min-height: 100vh;">
        <div class="row">
            <?php 
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
    if (!$education_center_id) {
        return '<div class="alert alert-warning">Educational center not found.</div>';
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
                                        <button class="btn btn-sm btn-info transact-item-btn" data-index="' . $index . '" data-item-id="' . esc_attr($item->item_id) . '" data-nonce="' . wp_create_nonce('transact_item_' . $item->item_id) . '">Transact</button>
                                        <button class="btn btn-sm btn-warning edit-item-btn" data-index="' . $index . '" data-item-id="' . esc_attr($item->item_id) . '" data-nonce="' . wp_create_nonce('edit_item_' . $item->item_id) . '">Edit</button>
                                        <button class="btn btn-sm btn-danger delete-item-btn" data-index="' . $index . '" data-item-id="' . esc_attr($item->item_id) . '" data-nonce="' . wp_create_nonce('delete_item_' . $item->item_id) . '">Delete</button>
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

    <!-- Modals -->
    <div class="modal" id="editItemModal" tabindex="-1">
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

    <div class="modal" id="deleteItemModal" tabindex="-1">
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

    <div class="modal" id="transactItemModal" tabindex="-1">
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
            const table = document.getElementById('inventoryTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const searchInput = document.getElementById('inventorySearch');

            // Search Filter
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                rows.forEach(row => {
                    const text = Array.from(row.cells).slice(0, -1).map(cell => cell.textContent.toLowerCase()).join(' ');
                    row.style.display = text.includes(filter) ? '' : 'none';
                });
            });

            // Table Sorting
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

            // CSV Export
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

            // Modal Handlers
            function setupModal(modalId, containerId, buttonClass, loadAction, processAction, processButtonClass) {
                const modal = document.getElementById(modalId);
                const container = document.getElementById(containerId);
                const buttons = document.querySelectorAll(`.${buttonClass}`);
                const processButton = document.querySelector(`.${processButtonClass}`);

                function showModal() { modal.style.display = 'block'; }
                function hideModal() { modal.style.display = 'none'; container.innerHTML = ''; }

                buttons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        const itemId = this.dataset.itemId;
                        const nonce = this.dataset.nonce;
                        button.disabled = true;
                        button.textContent = 'Loading...';

                        const data = new FormData();
                        data.append('action', loadAction);
                        data.append('item_id', itemId);
                        data.append('nonce', nonce);

                        fetch(ajaxUrl, { method: 'POST', body: data })
                            .then(response => response.json())
                            .then(data => {
                                button.disabled = false;
                                button.textContent = buttonClass.includes('transact') ? 'Transact' : buttonClass.includes('edit') ? 'Edit' : 'Delete';
                                if (data.success) {
                                    container.innerHTML = data.data[buttonClass.includes('delete') ? 'confirm_html' : 'form_html'];
                                    showModal();
                                    if (processAction) {
                                        attachFormHandler(container.querySelector('form'), processAction, itemId, processButton, hideModal);
                                    } else {
                                        processButton.onclick = function() {
                                            processButton.disabled = true;
                                            processButton.textContent = 'Deleting...';
                                            const deleteData = new FormData();
                                            deleteData.append('action', 'delete_inventory_item');
                                            deleteData.append('item_id', itemId);
                                            deleteData.append('nonce', nonce);
                                            fetch(ajaxUrl, { method: 'POST', body: deleteData })
                                                .then(res => res.json())
                                                .then(result => {
                                                    if (result.success) {
                                                        container.innerHTML = '<div class="alert alert-success">Item deleted successfully!</div>';
                                                        setTimeout(() => { hideModal(); window.location.href = window.location.href; }, 1500);
                                                    } else {
                                                        container.innerHTML = '<div class="alert alert-danger">' + (result.data.message || 'Error deleting item') + '</div>';
                                                        processButton.disabled = false;
                                                        processButton.textContent = 'Delete';
                                                    }
                                                })
                                                .catch(err => {
                                                    container.innerHTML = '<div class="alert alert-danger">Error: ' + err.message + '</div>';
                                                    processButton.disabled = false;
                                                    processButton.textContent = 'Delete';
                                                });
                                        };
                                    }
                                } else {
                                    alert('Error: ' + (data.data.message || 'Unable to load form'));
                                }
                            })
                            .catch(error => {
                                button.disabled = false;
                                button.textContent = buttonClass.includes('transact') ? 'Transact' : buttonClass.includes('edit') ? 'Edit' : 'Delete';
                                alert('Error: ' + error.message);
                            });
                    });
                });

                document.querySelectorAll(`#${modalId} .modal-close, #${modalId} .modal-close-btn`).forEach(btn => {
                    btn.addEventListener('click', hideModal);
                });
                window.addEventListener('click', e => { if (e.target === modal) hideModal(); });
            }

            function attachFormHandler(form, action, itemId, button, hideModal) {
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        button.disabled = true;
                        button.textContent = action.includes('edit') ? 'Updating...' : 'Processing...';

                        const formData = new FormData(this);
                        formData.append('action', action);
                        formData.append('item_id', itemId);

                        fetch(ajaxUrl, { method: 'POST', body: formData })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    form.parentElement.innerHTML = '<div class="alert alert-success">' + (data.data.message || 'Action completed successfully!') + '</div>';
                                    setTimeout(() => { hideModal(); window.location.href = window.location.href; }, 1500);
                                } else {
                                    form.parentElement.innerHTML = '<div class="alert alert-danger">' + (data.data.message || 'Error processing action') + '</div>';
                                    button.disabled = false;
                                    button.textContent = action.includes('edit') ? 'Update Item' : 'Process';
                                }
                            })
                            .catch(error => {
                                form.parentElement.innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                                button.disabled = false;
                                button.textContent = action.includes('edit') ? 'Update Item' : 'Process';
                            });
                    });
                    button.onclick = () => form.dispatchEvent(new Event('submit'));
                }
            }

            setupModal('editItemModal', 'edit-item-form-container', 'edit-item-btn', 'load_inventory_edit_form', 'edit_inventory_item', 'update-item-btn');
            setupModal('deleteItemModal', 'delete-item-container', 'delete-item-btn', 'load_inventory_delete_confirm', null, 'confirm-delete-btn');
            setupModal('transactItemModal', 'transact-item-form-container', 'transact-item-btn', 'load_inventory_transaction_form', 'process_inventory_transaction', 'process-transaction-btn');
        });
    </script>
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
    if (!$education_center_id) {
        return '<div class="alert alert-warning">Educational center not found.</div>';
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
    if (!$education_center_id) {
        return '<div class="alert alert-warning">Educational center not found.</div>';
    }

    $trans_table = $wpdb->prefix . 'inventory_transactions';
    $inventory_table = $wpdb->prefix . 'inventory';
    $transactions = $wpdb->get_results($wpdb->prepare(
        "SELECT t.*, i.name FROM $trans_table t JOIN $inventory_table i ON t.item_id = i.item_id WHERE i.education_center_id = %s ORDER BY t.date DESC",
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
                <p class="text-muted">"Track all inventory transactions"</p>
                <button id="exportCsv" class="btn btn-outline-info">Export to CSV</button>
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
                            <th class="sortable" data-sort="date">Date</th>
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
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.getElementById('transactionTable');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));

            // Table Sorting
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

            // CSV Export
            document.getElementById('exportCsv').addEventListener('click', function() {
                const csvContent = [
                    '"Item ID","Item Name","User ID","User Type","Action","Date"',
                    ...rows.map(row => Array.from(row.cells).map(cell => `"${cell.textContent.trim().replace(/"/g, '""')}"`).join(','))
                ].join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = 'inventory_transactions_<?php echo esc_js($education_center_id); ?>.csv';
                link.click();
            });
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <?php
    return ob_get_clean();
}
add_shortcode('inventory_transaction', 'inventory_transaction_shortcode');

// Inventory Issued Shortcode
function inventory_issued_shortcode() {
    global $wpdb;
    $current_user = wp_get_current_user();
    if (!$current_user->ID) {
        return '<div class="alert alert-warning">Please log in to view issued items.</div>';
    }

    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    if (!$education_center_id) {
        return '<div class="alert alert-warning">Educational center not found.</div>';
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

            // Table Sorting
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

            // CSV Export
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
    if (!$education_center_id) {
        wp_send_json_error(['message' => 'Educational center not found']);
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
    }

    $current_user = wp_get_current_user();
    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s", $item_id, $education_center_id));

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
                <div class="invalid-feedback">Please enter an item name.</div>
            </div>
            <div class="col-md-6">
                <label for="category" class="form-label fw-bold">Category</label>
                <select name="category" id="category" class="form-select" required>
                    <option value="Equipment" <?php selected($item->category, 'Equipment'); ?>>Equipment</option>
                    <option value="Stationery" <?php selected($item->category, 'Stationery'); ?>>Stationery</option>
                    <option value="Furniture" <?php selected($item->category, 'Furniture'); ?>>Furniture</option>
                    <option value="Other" <?php selected($item->category, 'Other'); ?>>Other</option>
                </select>
                <div class="invalid-feedback">Please select a category.</div>
            </div>
            <div class="col-md-6">
                <label for="quantity" class="form-label fw-bold">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="<?php echo esc_attr($item->quantity); ?>" min="0" required>
                <div class="invalid-feedback">Please enter a valid quantity (0 or more).</div>
            </div>
            <div class="col-md-6">
                <label for="status" class="form-label fw-bold">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="Available" <?php selected($item->status, 'Available'); ?>>Available</option>
                    <option value="Issued" <?php selected($item->status, 'Issued'); ?>>Issued</option>
                    <option value="Damaged" <?php selected($item->status, 'Damaged'); ?>>Damaged</option>
                </select>
                <div class="invalid-feedback">Please select a status.</div>
            </div>
            <div class="col-md-6">
                <label for="low_stock_threshold" class="form-label fw-bold">Low Stock Threshold</label>
                <input type="number" name="low_stock_threshold" id="low_stock_threshold" class="form-control" value="<?php echo esc_attr($item->low_stock_threshold); ?>" min="1" required>
                <div class="invalid-feedback">Please enter a valid threshold (1 or more).</div>
            </div>
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('inventory_edit_nonce'); ?>">
    </form>
    <?php
    wp_send_json_success(['form_html' => ob_get_clean()]);
}

add_action('wp_ajax_edit_inventory_item', 'ajax_edit_inventory_item');
function ajax_edit_inventory_item() {
    global $wpdb;
    if (!check_ajax_referer('inventory_edit_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $current_user = wp_get_current_user();
    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
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

    $updated = $wpdb->update(
        $table_name,
        ['name' => $name, 'quantity' => $quantity, 'status' => $status, 'category' => $category, 'low_stock_threshold' => $low_stock_threshold],
        ['item_id' => $item_id, 'education_center_id' => $education_center_id],
        ['%s', '%d', '%s', '%s', '%d'],
        ['%s', '%s']
    );

    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to update item: ' . $wpdb->last_error]);
    } elseif ($updated === 0) {
        wp_send_json_error(['message' => 'No changes made or item not found']);
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
    }

    $current_user = wp_get_current_user();
    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s", $item_id, $education_center_id));

    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
    }

    ob_start();
    ?>
    <form id="inventory-transaction-form" class="needs-validation" novalidate>
        <div class="mb-4">
            <label for="user_id" class="form-label fw-bold">User ID</label>
            <input type="text" name="user_id" id="user_id" class="form-control" required>
            <div class="invalid-feedback">Please enter a user ID.</div>
        </div>
        <div class="mb-4">
            <label for="user_type" class="form-label fw-bold">User Type</label>
            <select name="user_type" id="user_type" class="form-select" required>
                <option value="Staff">Staff</option>
                <option value="Student">Student</option>
            </select>
            <div class="invalid-feedback">Please select a user type.</div>
        </div>
        <div class="mb-4">
            <label for="action" class="form-label fw-bold">Action</label>
            <select name="action" id="action" class="form-select" required>
                <option value="Issue">Issue</option>
                <option value="Return">Return</option>
            </select>
            <div class="invalid-feedback">Please select an action.</div>
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('inventory_transaction_nonce'); ?>">
    </form>
    <?php
    wp_send_json_success(['form_html' => ob_get_clean()]);
}

add_action('wp_ajax_process_inventory_transaction', 'ajax_process_inventory_transaction');
function ajax_process_inventory_transaction() {
    global $wpdb;
    if (!check_ajax_referer('inventory_transaction_nonce', 'nonce', false)) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    $current_user = wp_get_current_user();
    $education_center_id = is_teacher($current_user->ID) ? educational_center_teacher_id() : get_educational_center_data();
    $inventory_table = $wpdb->prefix . 'inventory';
    $trans_table = $wpdb->prefix . 'inventory_transactions';

    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    $user_id = sanitize_text_field($_POST['user_id'] ?? '');
    $user_type = sanitize_text_field($_POST['user_type'] ?? '');
    $action = sanitize_text_field($_POST['action'] ?? '');

    if (empty($item_id) || empty($user_id) || !in_array($user_type, ['Staff', 'Student']) || !in_array($action, ['Issue', 'Return'])) {
        wp_send_json_error(['message' => 'Invalid or missing input data']);
    }

    $item = $wpdb->get_row($wpdb->prepare("SELECT * FROM $inventory_table WHERE item_id = %s AND education_center_id = %s", $item_id, $education_center_id));
    if (!$item) {
        wp_send_json_error(['message' => 'Item not found']);
    }

    if ($action === 'Issue' && $item->quantity <= 0) {
        wp_send_json_error(['message' => 'Item out of stock']);
    }
    if ($action === 'Return' && $item->status === 'Damaged') {
        wp_send_json_error(['message' => 'Cannot return a damaged item']);
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
            'status' => 'Completed'
        ],
        ['%s', '%s', '%s', '%s', '%s', '%s']
    );

    $updated = $wpdb->update(
        $inventory_table,
        ['quantity' => $new_quantity, 'status' => $new_status],
        ['item_id' => $item_id, 'education_center_id' => $education_center_id],
        ['%d', '%s'],
        ['%s', '%s']
    );

    if ($inserted === false || $updated === false) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error(['message' => 'Failed to process transaction: ' . $wpdb->last_error]);
    }

    $wpdb->query('COMMIT');

    if ($new_quantity <= $item->low_stock_threshold) {
        wp_mail(get_option('admin_email'), 'Low Stock Alert', "Item {$item->name} (ID: $item_id) is low on stock: $new_quantity remaining.");
    }

    wp_send_json_success(['message' => 'Transaction processed successfully']);
}