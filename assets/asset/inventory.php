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

// Handlers (unchanged)
add_action('admin_post_add_inventory_item', 'handle_add_inventory_item');
function handle_add_inventory_item() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'inventory';
    $education_center_id = get_educational_center_data();

    if (!wp_verify_nonce($_POST['nonce'], 'inventory_nonce')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $item_id = sanitize_text_field($_POST['item_id']);
    $name = sanitize_text_field($_POST['name']);
    $quantity = intval($_POST['quantity']);
    $status = sanitize_text_field($_POST['status']);
    $category = sanitize_text_field($_POST['category']);
    $low_stock_threshold = intval($_POST['low_stock_threshold']);

    if (empty($item_id) || empty($name) || $quantity < 0 || !in_array($status, ['Available', 'Issued', 'Damaged'])) {
        wp_die('Invalid input data.', 'Validation Error', ['back_link' => true]);
    }

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if ($exists > 0) {
        wp_die('Item ID already exists!', 'Duplicate Error', ['back_link' => true]);
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
        wp_die('Failed to add item: ' . esc_html($wpdb->last_error), 'Database Error', ['back_link' => true]);
    }

    wp_redirect(home_url('/institute-dashboard/inventory/?section=inventory-list'));
    exit;
}

add_action('admin_post_edit_inventory_item', 'handle_edit_inventory_item');
function handle_edit_inventory_item() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'inventory';
    $education_center_id = get_educational_center_data();

    if (!wp_verify_nonce($_POST['nonce'], 'inventory_edit_nonce')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $item_id = sanitize_text_field($_POST['item_id']);
    $name = sanitize_text_field($_POST['name']);
    $quantity = intval($_POST['quantity']);
    $status = sanitize_text_field($_POST['status']);
    $category = sanitize_text_field($_POST['category']);
    $low_stock_threshold = intval($_POST['low_stock_threshold']);

    $wpdb->update(
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

    if ($wpdb->last_error) {
        wp_die('Failed to update item: ' . esc_html($wpdb->last_error), 'Database Error', ['back_link' => true]);
    }

    wp_redirect(home_url('/institute-dashboard/inventory/?section=inventory-list'));
    exit;
}

add_action('admin_post_inventory_transaction', 'handle_inventory_transaction');
function handle_inventory_transaction() {
    global $wpdb;
    $inventory_table = $wpdb->prefix . 'inventory';
    $trans_table = $wpdb->prefix . 'inventory_transactions';
    $education_center_id = get_educational_center_data();

    if (!wp_verify_nonce($_POST['nonce'], 'inventory_transaction_nonce')) {
        wp_die('Security check failed.', 'Error', ['back_link' => true]);
    }

    $item_id = sanitize_text_field($_POST['item_id']);
    $user_id = sanitize_text_field($_POST['user_id']);
    $user_type = sanitize_text_field($_POST['user_type']);
    $action = sanitize_text_field($_POST['action']);

    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $inventory_table WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if (!$item) {
        wp_die('Item not found.', 'Error', ['back_link' => true]);
    }

    if ($action === 'Issue' && $item->quantity <= 0) {
        wp_die('Item out of stock.', 'Stock Error', ['back_link' => true]);
    }

    if ($action === 'Return' && $item->status === 'Damaged') {
        wp_die('Cannot return a damaged item.', 'Status Error', ['back_link' => true]);
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

    wp_redirect(home_url('/institute-dashboard/inventory/?section=inventory-list'));
    exit;
}

// Updated Shortcodes with distinct UIs
function inventory_list_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
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
                                        <a href="?section=inventory-transaction&item_id=' . urlencode($item->item_id) . '" class="btn btn-sm btn-info">Transact</a>
                                        <a href="?section=inventory-edit&item_id=' . urlencode($item->item_id) . '" class="btn btn-sm btn-warning">Edit</a>
                                        <a href="?section=inventory-list&action=delete&item_id=' . urlencode($item->item_id) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">Delete</a>
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
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['item_id'])) {
        $item_id = sanitize_text_field($_GET['item_id']);
        $wpdb->delete($table_name, ['item_id' => $item_id, 'education_center_id' => $education_center_id], ['%s', '%s']);
        wp_redirect(home_url('/institute-dashboard/inventory/?section=inventory-list'));
        exit;
    }
    return ob_get_clean();
}

function inventory_add_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
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
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="add_inventory_item">
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
                    <a href="?section=inventory-list" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function inventory_edit_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $item_id = isset($_GET['item_id']) ? sanitize_text_field($_GET['item_id']) : '';
    
    if (empty($item_id)) {
        // return '<div class="alert alert-warning text-center">Please select an item to edit from the inventory list.</div>';
        $education_center_id = get_educational_center_data();
        $table_name = $wpdb->prefix . 'inventory';
        $items = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE education_center_id = %s", $education_center_id));
    
        ob_start();
        ?>
        <div class="card shadow-lg" style="border-radius: 15px; background: #fff; border: 3px solid #28a745;">
            <div class="card-header bg-success text-white" style="border-radius: 12px 12px 0 0; padding: 1.5rem;">
                <h3 class="card-title mb-0"><i class="bi bi-box-seam me-2"></i>Edit Inventory Dashboard</h3>
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
                                            <a href="?section=inventory-edit&item_id=' . urlencode($item->item_id) . '" class="btn btn-sm btn-warning">Edit</a>
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

    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if (!$item) {
        return '<div class="alert alert-danger text-center">Item not found.</div>';
    }

    ob_start();
    ?>
    <div class="card shadow-lg" style="max-width: 800px; margin: 0 auto; border: 3px solid #ffc107; background: #fffef0;">
        <div class="card-header bg-warning text-dark text-center" style="border-radius: 10px 10px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Item: <?php echo esc_html($item->name); ?></h3>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="edit_inventory_item">
                <input type="hidden" name="item_id" value="<?php echo esc_attr($item_id); ?>">
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
                <?php wp_nonce_field('inventory_edit_nonce', 'nonce'); ?>
                <div class="mt-4 text-center">
                    <button type="submit" class="btn btn-warning btn-lg">Update Item</button>
                    <a href="?section=inventory-list" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function inventory_transaction_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
    $item_id = isset($_GET['item_id']) ? sanitize_text_field($_GET['item_id']) : '';

    if (empty($item_id)) {
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
                <a href="?section=inventory-list" class="btn btn-outline-info mt-3">Back to Inventory</a>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}inventory WHERE item_id = %s AND education_center_id = %s",
        $item_id, $education_center_id
    ));

    if (!$item) {
        return '<div class="alert alert-danger text-center">Item not found.</div>';
    }

    ob_start();
    ?>
    <div class="card shadow-lg" style="max-width: 600px; margin: 0 auto; border: 3px solid #17a2b8; background: #f0faff;">
        <div class="card-header bg-info text-white text-center" style="border-radius: 10px 10px 0 0;">
            <h3 class="card-title mb-0"><i class="bi bi-arrow-left-right me-2"></i>Transaction: <?php echo esc_html($item->name); ?></h3>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="needs-validation" novalidate>
                <input type="hidden" name="action" value="inventory_transaction">
                <input type="hidden" name="item_id" value="<?php echo esc_attr($item_id); ?>">
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
                <?php wp_nonce_field('inventory_transaction_nonce', 'nonce'); ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-info btn-lg">Process</button>
                    <a href="?section=inventory-list" class="btn btn-outline-secondary btn-lg ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function inventory_issued_shortcode() {
    global $wpdb;
    $education_center_id = get_educational_center_data();
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
            <a href="?section=inventory-list" class="btn btn-outline-danger mt-3">Back to Inventory</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}