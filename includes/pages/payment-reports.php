<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get date range filters
$start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y-m-d');

// Get all payments within date range
$payments = get_posts(array(
    'post_type' => 'agripump_payment',
    'numberposts' => -1,
    'post_status' => 'publish',
    'meta_query' => array(
        array(
            'key' => 'payment_date',
            'value' => array($start_date, $end_date),
            'compare' => 'BETWEEN',
            'type' => 'DATE'
        )
    ),
    'orderby' => 'meta_value',
    'meta_key' => 'payment_date',
    'order' => 'DESC'
));

$total_collected = 0;
$payment_count = 0;
$customer_payments = array();

foreach ($payments as $payment) {
    $payment_amount = floatval(get_post_meta($payment->ID, 'payment_amount', true));
    $payment_date = get_post_meta($payment->ID, 'payment_date', true);
    $customer_id = get_post_meta($payment->ID, 'customer_id', true);
    $payment_notes = get_post_meta($payment->ID, 'payment_notes', true);
    $created_by = get_post_meta($payment->ID, 'created_by', true);
    
    $customer = get_post($customer_id);
    $customer_name = $customer ? $customer->post_title : 'Unknown Customer';
    $location_id = get_post_meta($customer_id, 'location_id', true);
    $location = get_post($location_id);
    $location_name = $location ? $location->post_title : 'Unknown Location';
    
    $total_collected += $payment_amount;
    $payment_count++;
    
    $customer_payments[] = array(
        'payment_id' => $payment->ID,
        'customer_name' => $customer_name,
        'location_name' => $location_name,
        'payment_amount' => $payment_amount,
        'payment_date' => $payment_date,
        'payment_notes' => $payment_notes,
        'created_by' => $created_by
    );
}

// Get location-wise collection summary
$location_collections = array();
foreach ($customer_payments as $payment) {
    $location = $payment['location_name'];
    if (!isset($location_collections[$location])) {
        $location_collections[$location] = array(
            'total_collected' => 0,
            'payment_count' => 0,
            'customers' => array()
        );
    }
    
    $location_collections[$location]['total_collected'] += $payment['payment_amount'];
    $location_collections[$location]['payment_count']++;
    
    if (!in_array($payment['customer_name'], $location_collections[$location]['customers'])) {
        $location_collections[$location]['customers'][] = $payment['customer_name'];
    }
}

// Get current outstanding amounts
$all_customers = get_posts(array(
    'post_type' => 'agripump_customer',
    'numberposts' => -1,
    'post_status' => 'publish'
));

$total_outstanding = 0;
$customers_with_dues = 0;

foreach ($all_customers as $customer) {
    $bills = get_posts(array(
        'post_type' => 'agripump_bill',
        'meta_key' => 'customer_id',
        'meta_value' => $customer->ID,
        'numberposts' => -1,
        'post_status' => 'publish'
    ));
    
    $customer_total = 0;
    $customer_paid = 0;
    
    foreach ($bills as $bill) {
        $customer_total += floatval(get_post_meta($bill->ID, 'total_amount', true));
        $customer_paid += floatval(get_post_meta($bill->ID, 'paid_amount', true));
    }
    
    $customer_outstanding = $customer_total - $customer_paid;
    if ($customer_outstanding > 0) {
        $total_outstanding += $customer_outstanding;
        $customers_with_dues++;
    }
}
?>

<div class="wrap agripump-wrap">
    <div class="agripump-header">
        <h1><?php _e('Payment Reports', 'agripump-ledger'); ?></h1>
        <p><?php _e('Track payment collections and outstanding amounts', 'agripump-ledger'); ?></p>
    </div>
    
    <!-- Date Range Filter -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Filter by Date Range', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <form method="get" class="agripump-filter-form">
                <input type="hidden" name="page" value="agripump-payment-reports">
                <div class="agripump-form-row">
                    <div class="agripump-form-group">
                        <label for="start_date"><?php _e('Start Date:', 'agripump-ledger'); ?></label>
                        <input type="date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date); ?>" class="agripump-form-control">
                    </div>
                    <div class="agripump-form-group">
                        <label for="end_date"><?php _e('End Date:', 'agripump-ledger'); ?></label>
                        <input type="date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date); ?>" class="agripump-form-control">
                    </div>
                    <div class="agripump-form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="agripump-btn agripump-btn-primary">
                            <?php _e('Filter', 'agripump-ledger'); ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Summary Cards -->
    <div class="agripump-stats-grid">
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo number_format($total_collected, 2); ?></div>
            <div class="agripump-stat-label"><?php _e('Total Collected', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo $payment_count; ?></div>
            <div class="agripump-stat-label"><?php _e('Payment Transactions', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo number_format($total_outstanding, 2); ?></div>
            <div class="agripump-stat-label"><?php _e('Total Outstanding', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo $customers_with_dues; ?></div>
            <div class="agripump-stat-label"><?php _e('Customers with Dues', 'agripump-ledger'); ?></div>
        </div>
    </div>
    
    <!-- Location-wise Collection Summary -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Location-wise Collection Summary', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <?php if ($location_collections): ?>
                <table class="agripump-table">
                    <thead>
                        <tr>
                            <th><?php _e('Location', 'agripump-ledger'); ?></th>
                            <th><?php _e('Total Collected', 'agripump-ledger'); ?></th>
                            <th><?php _e('Payment Count', 'agripump-ledger'); ?></th>
                            <th><?php _e('Customers Paid', 'agripump-ledger'); ?></th>
                            <th><?php _e('Average Collection', 'agripump-ledger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($location_collections as $location_name => $summary): ?>
                        <tr>
                            <td><strong><?php echo esc_html($location_name); ?></strong></td>
                            <td><strong><?php echo number_format($summary['total_collected'], 2); ?></strong></td>
                            <td><?php echo $summary['payment_count']; ?></td>
                            <td><?php echo count($summary['customers']); ?></td>
                            <td><?php echo number_format($summary['total_collected'] / $summary['payment_count'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="agripump-empty-state">
                    <h3><?php _e('No payments found', 'agripump-ledger'); ?></h3>
                    <p><?php _e('No payments were collected in the selected date range.', 'agripump-ledger'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Payment Details -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Payment Details', 'agripump-ledger'); ?></h2>
            <div class="agripump-search-box">
                <input type="text" class="agripump-form-control agripump-search-input" 
                       placeholder="<?php _e('Search payments...', 'agripump-ledger'); ?>">
            </div>
        </div>
        <div class="agripump-card-body">
            <?php if ($customer_payments): ?>
                <table class="agripump-table">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'agripump-ledger'); ?></th>
                            <th><?php _e('Customer', 'agripump-ledger'); ?></th>
                            <th><?php _e('Location', 'agripump-ledger'); ?></th>
                            <th><?php _e('Amount', 'agripump-ledger'); ?></th>
                            <th><?php _e('Notes', 'agripump-ledger'); ?></th>
                            <th><?php _e('Collected By', 'agripump-ledger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customer_payments as $payment): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($payment['payment_date'])); ?></td>
                            <td><strong><?php echo esc_html($payment['customer_name']); ?></strong></td>
                            <td><?php echo esc_html($payment['location_name']); ?></td>
                            <td><strong class="agripump-amount">৳<?php echo number_format($payment['payment_amount'], 2); ?></strong></td>
                            <td><?php echo esc_html($payment['payment_notes']); ?></td>
                            <td>
                                <?php 
                                $user = get_user_by('id', $payment['created_by']);
                                echo $user ? esc_html($user->display_name) : 'Unknown';
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="agripump-empty-state">
                    <h3><?php _e('No payments found', 'agripump-ledger'); ?></h3>
                    <p><?php _e('No payments were collected in the selected date range.', 'agripump-ledger'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Export Options -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Export Reports', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <div class="agripump-actions">
                <button type="button" class="agripump-btn agripump-btn-primary" onclick="window.print()">
                    <?php _e('Print Report', 'agripump-ledger'); ?>
                </button>
                
                <button type="button" class="agripump-btn agripump-btn-secondary" onclick="exportPaymentCSV()">
                    <?php _e('Export to CSV', 'agripump-ledger'); ?>
                </button>
            </div>
            
            <div class="agripump-report-summary">
                <h3><?php _e('Report Summary', 'agripump-ledger'); ?></h3>
                <p><strong><?php _e('Date Range:', 'agripump-ledger'); ?></strong> <?php echo date('M j, Y', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?></p>
                <p><strong><?php _e('Total Collected:', 'agripump-ledger'); ?></strong> ৳<?php echo number_format($total_collected, 2); ?></p>
                <p><strong><?php _e('Payment Transactions:', 'agripump-ledger'); ?></strong> <?php echo $payment_count; ?></p>
                <p><strong><?php _e('Report Generated:', 'agripump-ledger'); ?></strong> <?php echo current_time('F j, Y g:i A'); ?></p>
            </div>
        </div>
    </div>
</div>

<script>
function exportPaymentCSV() {
    var csv = 'Date,Customer,Location,Amount,Notes,Collected By\n';
    
    <?php foreach ($customer_payments as $payment): ?>
    var date = '<?php echo date('M j, Y', strtotime($payment['payment_date'])); ?>';
    var customer = '<?php echo addslashes($payment['customer_name']); ?>';
    var location = '<?php echo addslashes($payment['location_name']); ?>';
    var amount = <?php echo $payment['payment_amount']; ?>;
    var notes = '<?php echo addslashes($payment['payment_notes']); ?>';
    var collectedBy = '<?php 
        $user = get_user_by('id', $payment['created_by']);
        echo addslashes($user ? $user->display_name : 'Unknown');
    ?>';
    
    csv += '"' + date + '","' + customer + '","' + location + '",' + amount.toFixed(2) + ',"' + notes + '","' + collectedBy + '"\n';
    <?php endforeach; ?>
    
    var blob = new Blob([csv], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'payment_report_<?php echo $start_date; ?>_to_<?php echo $end_date; ?>.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Search functionality
jQuery(document).ready(function($) {
    $('.agripump-search-input').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.agripump-table tbody tr').each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});
</script> 