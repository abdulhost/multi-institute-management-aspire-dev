<?php
// function setup_edu_subscription_tables() {
//     global $wpdb;
//     $charset_collate = $wpdb->get_charset_collate();

//     // Subscription Plans Table
//     $plans_table = "{$wpdb->prefix}edu_subscription_plans";
//     $plans_sql = "CREATE TABLE $plans_table (
//         id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
//         plan_name VARCHAR(50) NOT NULL,
//         plan_duration VARCHAR(20) NOT NULL,
//         plan_price DECIMAL(10, 2) NOT NULL,
//         is_active TINYINT(1) DEFAULT 1,
//         created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//         PRIMARY KEY (id),
//         UNIQUE KEY unique_plan_name (plan_name)
//     ) $charset_collate;";

//     // Subscriptions Table
//     $subscriptions_table = "{$wpdb->prefix}edu_subscriptions";
//     $subscriptions_sql = "CREATE TABLE $subscriptions_table (
//         id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
//         educational_center_id VARCHAR(50) NOT NULL,
//         plan_type VARCHAR(50) NOT NULL,
//         subscription_status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'pending',
//         subscription_start DATETIME DEFAULT NULL,
//         subscription_end DATETIME DEFAULT NULL,
//         payment_method VARCHAR(50) DEFAULT NULL,
//         transaction_id VARCHAR(100) DEFAULT NULL,
//         payment_details TEXT DEFAULT NULL,
//         created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//         PRIMARY KEY (id),
//         INDEX idx_educational_center_id (educational_center_id)
//     ) $charset_collate;";

//     // Payment Methods Table
//     $payment_methods_table = "{$wpdb->prefix}edu_payment_methods";
//     $payment_methods_sql = "CREATE TABLE $payment_methods_table (
//         id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
//         method_name VARCHAR(50) NOT NULL,
//         is_active TINYINT(1) DEFAULT 1,
//         details TEXT DEFAULT NULL,
//         created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
//         updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
//         PRIMARY KEY (id),
//         UNIQUE KEY unique_method_name (method_name)
//     ) $charset_collate;";

//     require_once ABSPATH . 'wp-admin/includes/upgrade.php';
//     dbDelta($plans_sql);
//     dbDelta($subscriptions_sql);
//     dbDelta($payment_methods_sql);


//     // // Seed initial plans
//     // if ($wpdb->get_var("SELECT COUNT(*) FROM $plans_table") == 0) {
//     //     $wpdb->insert($plans_table, ['plan_name' => 'Trial', 'plan_duration' => '15 days', 'plan_price' => 0.00]);
//     //     $wpdb->insert($plans_table, ['plan_name' => '3 Months', 'plan_duration' => '3 months', 'plan_price' => 49.99]);
//     //     $wpdb->insert($plans_table, ['plan_name' => '6 Months', 'plan_duration' => '6 months', 'plan_price' => 89.99]);
//     // }

//     // // Seed initial payment methods
//     // if ($wpdb->get_var("SELECT COUNT(*) FROM $payment_methods_table") == 0) {
//     //     $wpdb->insert($payment_methods_table, ['method_name' => 'COD', 'details' => json_encode(['note' => 'For testing only'])]);
//     //     $wpdb->insert($payment_methods_table, ['method_name' => 'Cheque', 'details' => json_encode(['bank_name' => 'Example Bank', 'account_number' => '1234567890'])]);
//     //     $wpdb->insert($payment_methods_table, ['method_name' => 'QR Online', 'details' => json_encode(['qr_code_url' => ''])]);
//     //     $wpdb->insert($payment_methods_table, ['method_name' => 'Bank Transfer', 'details' => json_encode(['bank_name' => 'Example Bank', 'account_number' => '0987654321', 'ifsc' => 'EXAM0001234'])]);
//     //     $wpdb->insert($payment_methods_table, ['method_name' => 'WooCommerce']);
//     //     $wpdb->insert($payment_methods_table, ['method_name' => 'Razorpay']);
//     // }
// }

// register_activation_hook(__FILE__, 'setup_edu_subscription_tables');
setup_edu_subscription_tables();

// Subscription Management Functions (unchanged from previous, included for completeness)
function render_institute_subscription_management($center_id) {
    global $wpdb;
    $subscriptions = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}edu_subscriptions WHERE educational_center_id = %s ORDER BY subscription_start DESC",
        $center_id
    ));

    ob_start();
    ?>
    <div class="payment-methods-wrap">
        <h1 class="payment-title">Subscription Management</h1>
        <div class="subscription-actions">
            <button class="payment-button" onclick="loadSubscriptionForm('renew')">Renew Subscription</button>
            <button class="payment-button" onclick="loadSubscriptionForm('extend')">Extend Subscription</button>
        </div>
        <table class="wp-list-table widefat fixed striped payment-methods-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Plan Type</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Payment Method</th>
                    <th>Transaction ID</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscriptions as $sub) : ?>
                    <tr>
                        <td><?php echo esc_html($sub->id); ?></td>
                        <td><?php echo esc_html($sub->plan_type); ?></td>
                        <td><?php echo esc_html($sub->subscription_status); ?></td>
                        <td><?php echo esc_html($sub->subscription_start ?: 'N/A'); ?></td>
                        <td><?php echo esc_html($sub->subscription_end ?: 'N/A'); ?></td>
                        <td><?php echo esc_html($sub->payment_method); ?></td>
                        <td><?php echo esc_html($sub->transaction_id); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div id="subscription-form-container"></div>
    </div>
    <script>
    function loadSubscriptionForm(action) {
        jQuery.ajax({
            url: '<?php echo rest_url('institute/v1/subscriptions'); ?>',
            method: 'POST',
            data: {
                action: action + '-form',
                center_id: '<?php echo esc_attr($center_id); ?>',
                nonce: '<?php echo wp_create_nonce('subscription_nonce'); ?>'
            },
            headers: {'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'},
            success: function(response) {
                if (response.success) {
                    jQuery('#subscription-form-container').html(response.data.html);
                } else {
                    alert('Error: ' + response.data);
                }
            },
            error: function(xhr) {
                alert('Error: ' + xhr.responseText);
            }
        });
    }
    </script>
    <?php
    return ob_get_clean();
}

function render_subscription_renewal_form($center_id) {
    global $wpdb;
    $plans = get_subscription_plans(true);
    $payment_methods = get_payment_methods();

    ob_start();
    ?>
    <div class="payment-methods-wrap subscription-form-container">
        <h1 class="payment-title">Renew Subscription</h1>
        <form id="renew-subscription-form" class="payment-form">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('subscription_nonce'); ?>">
            <input type="hidden" name="center_id" value="<?php echo esc_attr($center_id); ?>">
            <div class="form-group">
                <label>Plan Type:</label>
                <select name="plan_type" required class="payment-input" onchange="updatePlanDetails(this.value)">
                    <option value="">Select a Plan</option>
                    <?php foreach ($plans as $plan) : ?>
                        <option value="<?php echo esc_attr($plan->plan_name); ?>" data-duration="<?php echo esc_attr($plan->plan_duration); ?>" data-price="<?php echo esc_attr($plan->plan_price); ?>">
                            <?php echo esc_html($plan->plan_name . " ($plan->plan_duration, $$plan->plan_price)"); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="plan-details" class="subscription-details"></div>
            </div>
            <div class="form-group">
                <label>Payment Method:</label>
                <select name="payment_method" required class="payment-input" onchange="toggleTransactionField(this.value)">
                    <option value="">Select Payment Method</option>
                    <?php foreach ($payment_methods as $method) : ?>
                        <option value="<?php echo esc_attr($method->method_name); ?>"><?php echo esc_html($method->method_name); ?></option>
                    <?php endforeach; ?>
                </select>
                <div id="payment_details" class="payment-details"></div>
                <input type="text" name="transaction_id" id="transaction-id-field" class="payment-input" placeholder="Enter Transaction ID (if applicable)" style="display:none; margin-top:10px;">
            </div>
            <div class="form-group">
                <button type="submit" class="button-primary payment-button" id="renew-submit-btn">Renew Now</button>
                <span id="renew-status" class="subscription-status"></span>
            </div>
        </form>
    </div>
    <style>
    .subscription-form-container {
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .subscription-details, .payment-details {
        margin-top: 10px;
        font-size: 14px;
        color: #666;
    }
    .subscription-status {
        margin-left: 10px;
        font-size: 14px;
        color: #1abc9c;
    }
    .subscription-expired {
        padding: 20px;
        background: #ffe6e6;
        border: 1px solid #e74c3c;
        border-radius: 5px;
        text-align: center;
    }
    .subscription-expired h2 {
        color: #e74c3c;
        margin-bottom: 10px;
    }
    .subscription-options {
        margin-top: 20px;
    }
    .payment-button {
        display: inline-block;
        padding: 10px 20px;
        margin: 5px;
        background: #1abc9c;
        color: #fff;
        text-decoration: none;
        border-radius: 5px;
    }
    .payment-button.secondary {
        background: #3498db;
    }
    .login-prompt {
        padding: 20px;
        background: #f1f1f1;
        border-radius: 5px;
        text-align: center;
    }
    </style>
    <script>
    jQuery('#renew-subscription-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'renew');
        const submitBtn = jQuery('#renew-submit-btn');
        const status = jQuery('#renew-status');

        submitBtn.prop('disabled', true);
        status.text('Processing...');

        jQuery.ajax({
            url: '<?php echo rest_url('institute/v1/subscriptions'); ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'},
            success: function(response) {
                if (response.success) {
                    status.text('Renewal requested. Awaiting verification.');
                    setTimeout(() => {
                        status.text('');
                        window.location.href = '?section=subscription';
                    }, 2000);
                } else {
                    status.text('Error: ' + response.data);
                    submitBtn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                status.text('Error: ' + xhr.responseText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    function updatePlanDetails(plan) {
        const select = jQuery('select[name="plan_type"] option:selected');
        const duration = select.data('duration');
        const price = select.data('price');
        jQuery('#plan-details').html(`Duration: ${duration} | Price: $${price}`);
    }

    function toggleTransactionField(method) {
        const details = <?php echo json_encode(array_column($payment_methods, 'details', 'method_name')); ?>;
        const parsedDetails = JSON.parse(details[method] || '{}');
        let html = '';
        if (parsedDetails.bank_name) html += '<p>Bank Name: ' + parsedDetails.bank_name + '</p>';
        if (parsedDetails.account_number) html += '<p>Account Number: ' + parsedDetails.account_number + '</p>';
        if (parsedDetails.ifsc) html += '<p>IFSC: ' + parsedDetails.ifsc + '</p>';
        if (parsedDetails.qr_code_url) html += '<p><img src="' + parsedDetails.qr_code_url + '" alt="QR Code" style="max-width:200px;"></p>';
        jQuery('#payment_details').html(html || 'No additional details required.');

        const transactionField = jQuery('#transaction-id-field');
        if (method === 'Bank Transfer' || method === 'UPI') {
            transactionField.show().prop('required', true);
        } else {
            transactionField.hide().prop('required', false);
        }
    }
    </script>
    <?php
    return ob_get_clean();
}

function render_subscription_extension_form($center_id) {
    global $wpdb;
    $current_subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}edu_subscriptions WHERE educational_center_id = %s AND subscription_status = 'active' ORDER BY subscription_end DESC LIMIT 1",
        $center_id
    ));
    $payment_methods = get_payment_methods();

    ob_start();
    ?>
    <div class="payment-methods-wrap subscription-form-container">
        <h1 class="payment-title">Extend Subscription</h1>
        <?php if ($current_subscription) : ?>
            <div class="subscription-info">
                <p>Current Plan: <strong><?php echo esc_html($current_subscription->plan_type); ?></strong></p>
                <p>Ends: <strong><?php echo esc_html($current_subscription->subscription_end); ?></strong></p>
            </div>
            <form id="extend-subscription-form" class="payment-form">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('subscription_nonce'); ?>">
                <input type="hidden" name="center_id" value="<?php echo esc_attr($center_id); ?>">
                <input type="hidden" name="subscription_id" value="<?php echo esc_attr($current_subscription->id); ?>">
                <div class="form-group">
                    <label>Extend By:</label>
                    <select name="extend_duration" required class="payment-input" onchange="updateExtensionDetails(this.value)">
                        <option value="">Select Duration</option>
                        <option value="1 month">1 Month</option>
                        <option value="3 months">3 Months</option>
                        <option value="6 months">6 Months</option>
                        <option value="1 year">1 Year</option>
                    </select>
                    <div id="extension-details" class="subscription-details"></div>
                </div>
                <div class="form-group">
                    <label>Payment Method:</label>
                    <select name="payment_method" required class="payment-input" onchange="toggleTransactionField(this.value)">
                        <option value="">Select Payment Method</option>
                        <?php foreach ($payment_methods as $method) : ?>
                            <option value="<?php echo esc_attr($method->method_name); ?>"><?php echo esc_html($method->method_name); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div id="payment_details" class="payment-details"></div>
                    <input type="text" name="transaction_id" id="transaction-id-field" class="payment-input" placeholder="Enter Transaction ID (if applicable)" style="display:none; margin-top:10px;">
                </div>
                <div class="form-group">
                    <button type="submit" class="button-primary payment-button" id="extend-submit-btn">Extend Now</button>
                    <span id="extend-status" class="subscription-status"></span>
                </div>
            </form>
        <?php else : ?>
            <p>No active subscription found to extend. Please renew instead.</p>
            <a href="?section=subscription&action=renew-subscription" class="payment-button">Go to Renew</a>
        <?php endif; ?>
    </div>
    <script>
    jQuery('#extend-subscription-form').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'extend');
        const submitBtn = jQuery('#extend-submit-btn');
        const status = jQuery('#extend-status');

        submitBtn.prop('disabled', true);
        status.text('Processing...');

        jQuery.ajax({
            url: '<?php echo rest_url('institute/v1/subscriptions'); ?>',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'},
            success: function(response) {
                if (response.success) {
                    status.text('Extension requested. Awaiting verification.');
                    setTimeout(() => {
                        status.text('');
                        window.location.href = '?section=subscription';
                    }, 2000);
                } else {
                    status.text('Error: ' + response.data);
                    submitBtn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                status.text('Error: ' + xhr.responseText);
                submitBtn.prop('disabled', false);
            }
        });
    });

    function updateExtensionDetails(duration) {
        const currentEnd = '<?php echo esc_attr($current_subscription ? $current_subscription->subscription_end : ''); ?>';
        if (currentEnd) {
            const newEnd = new Date(new Date(currentEnd).getTime());
            const months = {'1 month': 1, '3 months': 3, '6 months': 6, '1 year': 12};
            newEnd.setMonth(newEnd.getMonth() + months[duration]);
            jQuery('#extension-details').html(`New End Date: ${newEnd.toLocaleDateString()}`);
        }
    }

    function toggleTransactionField(method) {
        const details = <?php echo json_encode(array_column($payment_methods, 'details', 'method_name')); ?>;
        const parsedDetails = JSON.parse(details[method] || '{}');
        let html = '';
        if (parsedDetails.bank_name) html += '<p>Bank Name: ' + parsedDetails.bank_name + '</p>';
        if (parsedDetails.account_number) html += '<p>Account Number: ' + parsedDetails.account_number + '</p>';
        if (parsedDetails.ifsc) html += '<p>IFSC: ' + parsedDetails.ifsc + '</p>';
        if (parsedDetails.qr_code_url) html += '<p><img src="' + parsedDetails.qr_code_url + '" alt="QR Code" style="max-width:200px;"></p>';
        jQuery('#payment_details').html(html || 'No additional details required.');

        const transactionField = jQuery('#transaction-id-field');
        if (method === 'Bank Transfer' || method === 'UPI') {
            transactionField.show().prop('required', true);
        } else {
            transactionField.hide().prop('required', false);
        }
    }
    </script>
    <?php
    return ob_get_clean();
}


// Helper Functions (unchanged)
function setup_edu_subscription_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $plans_table = "{$wpdb->prefix}edu_subscription_plans";
    $plans_sql = "CREATE TABLE $plans_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        plan_name VARCHAR(50) NOT NULL,
        plan_duration VARCHAR(20) NOT NULL,
        plan_price DECIMAL(10, 2) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_plan_name (plan_name)
    ) $charset_collate;";

    $subscriptions_table = "{$wpdb->prefix}edu_subscriptions";
    $subscriptions_sql = "CREATE TABLE $subscriptions_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        educational_center_id VARCHAR(50) NOT NULL,
        plan_type VARCHAR(50) NOT NULL,
        subscription_status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'pending',
        subscription_start DATETIME DEFAULT NULL,
        subscription_end DATETIME DEFAULT NULL,
        payment_method VARCHAR(50) DEFAULT NULL,
        transaction_id VARCHAR(100) DEFAULT NULL,
        payment_details TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_educational_center_id (educational_center_id)
    ) $charset_collate;";

    $payment_methods_table = "{$wpdb->prefix}edu_payment_methods";
    $payment_methods_sql = "CREATE TABLE $payment_methods_table (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        method_name VARCHAR(50) NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        details TEXT DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_method_name (method_name)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($plans_sql);
    dbDelta($subscriptions_sql);
    dbDelta($payment_methods_sql);
}

setup_edu_subscription_tables();
add_action('check_subscription_expiry', 'update_expired_subscriptions');
function update_expired_subscriptions() {
    global $wpdb;
    $wpdb->query("UPDATE {$wpdb->prefix}edu_subscriptions SET subscription_status = 'expired' WHERE subscription_end < NOW() AND subscription_status = 'active'");
}

if (!wp_next_scheduled('check_subscription_expiry')) {
    wp_schedule_event(time(), 'daily', 'check_subscription_expiry');
}

function get_subscription_plans($exclude_trial = false) {
    global $wpdb;
    $where = $exclude_trial ? "WHERE is_active = 1 AND plan_name != 'Trial'" : "WHERE is_active = 1";
    return $wpdb->get_results("SELECT id, plan_name, plan_duration, plan_price FROM {$wpdb->prefix}edu_subscription_plans $where");
}

function get_subscription_duration($plan_name) {
    global $wpdb;
    $plan = $wpdb->get_row($wpdb->prepare(
        "SELECT plan_duration FROM {$wpdb->prefix}edu_subscription_plans WHERE plan_name = %s AND is_active = 1",
        $plan_name
    ));
    return $plan ? '+' . $plan->plan_duration : '+3 months';
}

function is_center_subscribed($educational_center_id) {
    global $wpdb;
    $subscription = $wpdb->get_row($wpdb->prepare(
        "SELECT subscription_status, subscription_end 
         FROM {$wpdb->prefix}edu_subscriptions 
         WHERE educational_center_id = %s 
         AND subscription_status = 'active' 
         ORDER BY subscription_end DESC 
         LIMIT 1",
        $educational_center_id
    ));
    return $subscription && $subscription->subscription_status === 'active' && strtotime($subscription->subscription_end) > time();
}

function get_payment_methods() {
    global $wpdb;
    return $wpdb->get_results("SELECT id, method_name, details FROM {$wpdb->prefix}edu_payment_methods WHERE is_active = 1");
}

