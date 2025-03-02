<?php
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('enroll_transport', 'my_education_erp_enroll_transport_shortcode');
function my_education_erp_enroll_transport_shortcode() {
    $student_id = get_current_user_id();
    if (my_education_erp_is_enrolled_in_transport($student_id)) {
        return '<p>You are already enrolled in transport.</p>';
    }

    if (isset($_POST['enroll_transport']) && wp_verify_nonce($_POST['nonce'], 'enroll_transport_nonce')) {
        my_education_erp_enroll_student_in_transport($student_id);
        return '<p>You are now enrolled in transport!</p>';
    }

    ob_start();
    ?>
    <form method="post">
        <p>Enroll in transport services?</p>
        <?php wp_nonce_field('enroll_transport_nonce', 'nonce'); ?>
        <input type="submit" name="enroll_transport" value="Enroll Now">
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('select_fees', 'my_education_erp_select_fees_shortcode');
function my_education_erp_select_fees_shortcode() {
    $student_id = get_current_user_id();
    $templates = my_education_erp_get_templates('tuition');

    if (isset($_POST['submit_payment']) && wp_verify_nonce($_POST['nonce'], 'fee_selection_nonce')) {
        $selected_templates = array_map('intval', $_POST['templates']);
        $months = array_map('sanitize_text_field', $_POST['months']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $cheque_number = $payment_method == 'cheque' ? sanitize_text_field($_POST['cheque_number']) : '';

        foreach ($selected_templates as $template_id) {
            foreach ($months as $month) {
                my_education_erp_record_fee_payment($student_id, $template_id, $month, $payment_method, $cheque_number);
            }
        }
        return '<p>' . ($payment_method == 'cheque' ? 'Cheque submitted! Awaiting verification.' : 'Payment recorded successfully!') . '</p>';
    }

    ob_start();
    ?>
    <form method="post">
        <h3>Select Tuition Fees</h3>
        <?php foreach ($templates as $template) : ?>
            <label><input type="checkbox" name="templates[]" value="<?php echo $template->id; ?>"> <?php echo $template->name . ' ($' . $template->amount . ')'; ?></label><br>
        <?php endforeach; ?>
        <h3>Select Months</h3>
        <?php for ($i = 0; $i < 12; $i++) : $month = date('Y-m', strtotime("+$i months")); ?>
            <label><input type="checkbox" name="months[]" value="<?php echo $month; ?>"> <?php echo date('F Y', strtotime($month)); ?></label><br>
        <?php endfor; ?>
        <h3>Payment Method</h3>
        <select name="payment_method" required>
            <option value="cod">Cash on Delivery</option>
            <option value="cheque">Cheque</option>
        </select><br>
        <input type="text" name="cheque_number" placeholder="Cheque Number" style="display:none;" id="cheque_number">
        <script>
            document.querySelector('[name="payment_method"]').addEventListener('change', function() {
                document.getElementById('cheque_number').style.display = this.value === 'cheque' ? 'block' : 'none';
            });
        </script>
        <?php wp_nonce_field('fee_selection_nonce', 'nonce'); ?>
        <input type="submit" name="submit_payment" value="Submit Payment">
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('select_transport_fees', 'my_education_erp_select_transport_fees_shortcode');
function my_education_erp_select_transport_fees_shortcode() {
    $student_id = get_current_user_id();
    if (!my_education_erp_is_enrolled_in_transport($student_id)) {
        return '<p>Please enroll in transport first.</p>';
    }

    $templates = my_education_erp_get_templates('transport');

    if (isset($_POST['submit_payment']) && wp_verify_nonce($_POST['nonce'], 'transport_fee_nonce')) {
        $selected_templates = array_map('intval', $_POST['templates']);
        $months = array_map('sanitize_text_field', $_POST['months']);
        $payment_method = sanitize_text_field($_POST['payment_method']);
        $cheque_number = $payment_method == 'cheque' ? sanitize_text_field($_POST['cheque_number']) : '';

        foreach ($selected_templates as $template_id) {
            foreach ($months as $month) {
                my_education_erp_record_transport_fee_payment($student_id, $template_id, $month, $payment_method, $cheque_number);
            }
        }
        return '<p>' . ($payment_method == 'cheque' ? 'Cheque submitted! Awaiting verification.' : 'Payment recorded successfully!') . '</p>';
    }

    ob_start();
    ?>
    <form method="post">
        <h3>Select Transport Fees</h3>
        <?php foreach ($templates as $template) : ?>
            <label><input type="checkbox" name="templates[]" value="<?php echo $template->id; ?>"> <?php echo $template->name . ' ($' . $template->amount . ')'; ?></label><br>
        <?php endforeach; ?>
        <h3>Select Months</h3>
        <?php for ($i = 0; $i < 12; $i++) : $month = date('Y-m', strtotime("+$i months")); ?>
            <label><input type="checkbox" name="months[]" value="<?php echo $month; ?>"> <?php echo date('F Y', strtotime($month)); ?></label><br>
        <?php endfor; ?>
        <h3>Payment Method</h3>
        <select name="payment_method" required>
            <option value="cod">Cash on Delivery</option>
            <option value="cheque">Cheque</option>
        </select><br>
        <input type="text" name="cheque_number" placeholder="Cheque Number" style="display:none;" id="cheque_number">
        <script>
            document.querySelector('[name="payment_method"]').addEventListener('change', function() {
                document.getElementById('cheque_number').style.display = this.value === 'cheque' ? 'block' : 'none';
            });
        </script>
        <?php wp_nonce_field('transport_fee_nonce', 'nonce'); ?>
        <input type="submit" name="submit_payment" value="Submit Payment">
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('display_fees', 'my_education_erp_display_fees_shortcode');
function my_education_erp_display_fees_shortcode() {
    $student_id = get_current_user_id();
    $fees = my_education_erp_get_student_fees($student_id);

    ob_start();
    ?>
    <h3>Your Tuition Fees</h3>
    <table border="1">
        <tr><th>Type</th><th>Amount</th><th>Month</th><th>Status</th></tr>
        <?php foreach ($fees as $fee) : ?>
            <tr>
                <td><?php echo $fee->name; ?></td>
                <td><?php echo $fee->amount; ?></td>
                <td><?php echo $fee->month_year; ?></td>
                <td><?php echo $fee->status; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php
    $current_month = current_time('Y-m');
    $paid_months = array_column($fees, 'month_year');
    $all_months = array_map(function($i) { return date('Y-m', strtotime("+$i months")); }, range(0, 11));
    $pending_months = array_diff($all_months, $paid_months);
    ?>
    <h3>Pending/Due Months</h3>
    <ul>
        <?php foreach ($pending_months as $month) : ?>
            <li><?php echo date('F Y', strtotime($month)) . ' - ' . ($month < $current_month ? 'Due' : 'Pending'); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}

add_shortcode('display_transport_fees', 'my_education_erp_display_transport_fees_shortcode');
function my_education_erp_display_transport_fees_shortcode() {
    $student_id = get_current_user_id();
    if (!my_education_erp_is_enrolled_in_transport($student_id)) {
        return '<p>You are not enrolled in transport.</p>';
    }

    $fees = my_education_erp_get_transport_fees($student_id);

    ob_start();
    ?>
    <h3>Your Transport Fees</h3>
    <table border="1">
        <tr><th>Type</th><th>Amount</th><th>Month</th><th>Status</th></tr>
        <?php foreach ($fees as $fee) : ?>
            <tr>
                <td><?php echo $fee->name; ?></td>
                <td><?php echo $fee->amount; ?></td>
                <td><?php echo $fee->month_year; ?></td>
                <td><?php echo $fee->status; ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

    <?php
    $current_month = current_time('Y-m');
    $paid_months = array_column($fees, 'month_year');
    $all_months = array_map(function($i) { return date('Y-m', strtotime("+$i months")); }, range(0, 11));
    $pending_months = array_diff($all_months, $paid_months);
    ?>
    <h3>Pending/Due Months</h3>
    <ul>
        <?php foreach ($pending_months as $month) : ?>
            <li><?php echo date('F Y', strtotime($month)) . ' - ' . ($month < $current_month ? 'Due' : 'Pending'); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}