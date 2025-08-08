<?php
if (!defined('ABSPATH')) {
    exit;
}

// Pagination setup
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Get all customers with their bills
$all_customers = get_posts(array(
    'post_type' => 'agripump_customer',
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC'
));

$total_due = 0;
$customer_dues = array();

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
        $customer_dues[] = array(
            'customer' => $customer,
            'total_due' => $customer_outstanding,
            'bill_count' => count($bills)
        );
        $total_due += $customer_outstanding;
    }
}

// Sort by due amount (highest first)
usort($customer_dues, function($a, $b) {
    return $b['total_due'] - $a['total_due'];
});

// Apply pagination to customer dues
$total_customers_with_dues = count($customer_dues);
$customer_dues = array_slice($customer_dues, $offset, $per_page);
$total_pages = ceil($total_customers_with_dues / $per_page);

// Get location-wise summary
$location_summary = array();
foreach ($customer_dues as $due) {
    $location_id = get_post_meta($due['customer']->ID, 'location_id', true);
    $location = get_post($location_id);
    $location_name = $location ? $location->post_title : __('Unknown Location', 'agripump-ledger');
    
    if (!isset($location_summary[$location_name])) {
        $location_summary[$location_name] = array(
            'location_id' => $location_id,
            'total_due' => 0,
            'customer_count' => 0
        );
    }
    
    $location_summary[$location_name]['total_due'] += $due['total_due'];
    $location_summary[$location_name]['customer_count']++;
}
?>

<div class="wrap agripump-wrap">
    <div class="agripump-header">
        <h1><?php _e('Due Collection', 'agripump-ledger'); ?></h1>
        <p><?php _e('Track outstanding payments and manage collections', 'agripump-ledger'); ?></p>
    </div>
    
    <!-- Summary Cards -->
    <div class="agripump-stats-grid">
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo number_format($total_due, 2); ?></div>
            <div class="agripump-stat-label"><?php _e('Total Outstanding', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo count($customer_dues); ?></div>
            <div class="agripump-stat-label"><?php _e('Customers with Dues', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo count($location_summary); ?></div>
            <div class="agripump-stat-label"><?php _e('Locations with Dues', 'agripump-ledger'); ?></div>
        </div>
    </div>
    
    <!-- Location-wise Summary -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Location-wise Summary', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <?php if ($location_summary): ?>
                <table class="agripump-table">
                    <thead>
                        <tr>
                            <th><?php _e('Location', 'agripump-ledger'); ?></th>
                            <th><?php _e('Customers with Dues', 'agripump-ledger'); ?></th>
                            <th><?php _e('Total Outstanding', 'agripump-ledger'); ?></th>
                            <th><?php _e('Actions', 'agripump-ledger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($location_summary as $location_name => $summary): ?>
                        <tr>
                            <td><?php echo esc_html($location_name); ?></td>
                            <td><?php echo $summary['customer_count']; ?></td>
                            <td><?php echo number_format($summary['total_due'], 2); ?></td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=agripump-customers&location_id=' . $summary['location_id']); ?>" 
                                   class="agripump-btn agripump-btn-sm agripump-btn-primary">
                                    <?php _e('View Customers', 'agripump-ledger'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="agripump-empty-state">
                    <h3><?php _e('No outstanding dues', 'agripump-ledger'); ?></h3>
                    <p><?php _e('All customers have paid their bills.', 'agripump-ledger'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Customer-wise Details -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Customer-wise Outstanding', 'agripump-ledger'); ?></h2>
            <div class="agripump-search-box">
                <input type="text" class="agripump-form-control agripump-search-input" 
                       placeholder="<?php _e('Search customers...', 'agripump-ledger'); ?>">
            </div>
        </div>
        <div class="agripump-card-body">
            <?php if ($customer_dues): ?>
                <table class="agripump-table">
                    <thead>
                        <tr>
                            <th><?php _e('Customer Name', 'agripump-ledger'); ?></th>
                            <th><?php _e('Location', 'agripump-ledger'); ?></th>
                            <th><?php _e('Mobile', 'agripump-ledger'); ?></th>
                            <th><?php _e('Bill Count', 'agripump-ledger'); ?></th>
                            <th><?php _e('Total Outstanding', 'agripump-ledger'); ?></th>
                            <th><?php _e('Actions', 'agripump-ledger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customer_dues as $due): 
                            $customer = $due['customer'];
                            $location_id = get_post_meta($customer->ID, 'location_id', true);
                            $location = get_post($location_id);
                            $mobile = get_post_meta($customer->ID, 'mobile', true);
                        ?>
                        <tr>
                            <td><?php echo esc_html($customer->post_title); ?></td>
                            <td><?php echo $location ? esc_html($location->post_title) : __('Unknown', 'agripump-ledger'); ?></td>
                            <td><?php echo esc_html($mobile); ?></td>
                            <td><?php echo $due['bill_count']; ?></td>
                            <td><strong><?php echo number_format($due['total_due'], 2); ?></strong></td>
                            <td>
                                <div class="agripump-actions">
                                    <button type="button" 
                                            class="agripump-btn agripump-btn-sm agripump-btn-danger due-collect-btn" 
                                            data-customer-id="<?php echo $customer->ID; ?>"
                                            data-customer-name="<?php echo esc_attr($customer->post_title); ?>"
                                            data-total-due="<?php echo $due['total_due']; ?>">
                                        <?php _e('Due Collect', 'agripump-ledger'); ?>
                                    </button>
                                    
                                    <a href="<?php echo admin_url('admin.php?page=agripump-customers&customer_id=' . $customer->ID); ?>" 
                                       class="agripump-btn agripump-btn-sm agripump-btn-primary">
                                        <?php _e('View Bills', 'agripump-ledger'); ?>
                                    </a>
                                    
                                    <a href="<?php echo admin_url('admin.php?page=agripump-customers&edit=' . $customer->ID); ?>" 
                                       class="agripump-btn agripump-btn-sm agripump-btn-secondary">
                                        <?php _e('Edit', 'agripump-ledger'); ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="agripump-empty-state">
                    <h3><?php _e('No outstanding dues', 'agripump-ledger'); ?></h3>
                    <p><?php _e('All customers have paid their bills.', 'agripump-ledger'); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="agripump-pagination">
                    <?php
                    $base_url = add_query_arg(array('page' => 'agripump-due-collection'), admin_url('admin.php'));
                    
                    // Previous page
                    if ($current_page > 1): ?>
                        <a href="<?php echo add_query_arg('paged', $current_page - 1, $base_url); ?>" class="agripump-btn agripump-btn-secondary">
                            <?php _e('← Previous', 'agripump-ledger'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Page numbers -->
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="agripump-page-current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo add_query_arg('paged', $i, $base_url); ?>" class="agripump-btn agripump-btn-secondary">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <!-- Next page -->
                    <?php if ($current_page < $total_pages): ?>
                        <a href="<?php echo add_query_arg('paged', $current_page + 1, $base_url); ?>" class="agripump-btn agripump-btn-secondary">
                            <?php _e('Next →', 'agripump-ledger'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <span class="agripump-pagination-info">
                        <?php printf(__('Showing %d-%d of %d customers with dues', 'agripump-ledger'), 
                            $offset + 1, 
                            min($offset + $per_page, $total_customers_with_dues), 
                            $total_customers_with_dues); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Collection Report -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Collection Report', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <div class="agripump-actions">
                <button type="button" class="agripump-btn agripump-btn-primary" onclick="window.print()">
                    <?php _e('Print Report', 'agripump-ledger'); ?>
                </button>
                
                <button type="button" class="agripump-btn agripump-btn-secondary" onclick="exportToCSV()">
                    <?php _e('Export to CSV', 'agripump-ledger'); ?>
                </button>
            </div>
            
            <div class="agripump-report-summary">
                <h3><?php _e('Summary', 'agripump-ledger'); ?></h3>
                <p><strong><?php _e('Total Outstanding Amount:', 'agripump-ledger'); ?></strong> <?php echo number_format($total_due, 2); ?></p>
                <p><strong><?php _e('Total Customers with Dues:', 'agripump-ledger'); ?></strong> <?php echo count($customer_dues); ?></p>
                <p><strong><?php _e('Report Generated:', 'agripump-ledger'); ?></strong> <?php echo current_time('F j, Y g:i A'); ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Due Collection Modal -->
<div id="due-collection-modal" class="agripump-modal">
    <div class="agripump-modal-content">
        <div class="agripump-modal-header">
            <h2 id="modal-customer-name"><?php _e('Due Collection', 'agripump-ledger'); ?></h2>
            <span class="agripump-modal-close">&times;</span>
        </div>
        <div class="agripump-modal-body">
            <div class="agripump-loading" id="modal-loading">
                <div class="agripump-spinner"></div>
                <p><?php _e('Loading customer details...', 'agripump-ledger'); ?></p>
            </div>
            
            <div id="modal-content" style="display: none;">
                <div class="agripump-customer-summary">
                    <div class="agripump-summary-row">
                        <div class="agripump-summary-item">
                            <label><?php _e('Total Outstanding:', 'agripump-ledger'); ?></label>
                            <span id="modal-total-due" class="agripump-amount"></span>
                        </div>
                        <div class="agripump-summary-item">
                            <label><?php _e('Location:', 'agripump-ledger'); ?></label>
                            <span id="modal-customer-location"></span>
                        </div>
                        <div class="agripump-summary-item">
                            <label><?php _e('Mobile:', 'agripump-ledger'); ?></label>
                            <span id="modal-customer-mobile"></span>
                        </div>
                    </div>
                </div>
                
                <div class="agripump-bills-section">
                    <h3><?php _e('Bill Details', 'agripump-ledger'); ?></h3>
                    <div id="bills-container">
                        <!-- Bills will be loaded here -->
                    </div>
                </div>
                
                <div class="agripump-payment-section">
                    <h3><?php _e('Payment Collection', 'agripump-ledger'); ?></h3>
                    <div class="agripump-payment-form">
                        <div class="agripump-form-row">
                            <div class="agripump-form-group">
                                <label for="payment-season"><?php _e('Select Season:', 'agripump-ledger'); ?></label>
                                <select id="payment-season" class="agripump-form-control" required>
                                    <option value=""><?php _e('Select a season...', 'agripump-ledger'); ?></option>
                                </select>
                            </div>
                            <div class="agripump-form-group">
                                <label for="payment-date"><?php _e('Payment Date:', 'agripump-ledger'); ?></label>
                                <input type="date" id="payment-date" class="agripump-form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div class="agripump-form-row">
                            <div class="agripump-form-group">
                                <label for="payment-amount"><?php _e('Payment Amount:', 'agripump-ledger'); ?></label>
                                <input type="number" id="payment-amount" class="agripump-form-control" step="0.01" min="0">
                                <small id="payment-amount-help" class="form-text text-muted"></small>
                            </div>
                            <div class="agripump-form-group">
                                <label for="payment-notes"><?php _e('Payment Notes:', 'agripump-ledger'); ?></label>
                                <textarea id="payment-notes" class="agripump-form-control" rows="3" placeholder="<?php _e('Optional notes about this payment...', 'agripump-ledger'); ?>"></textarea>
                            </div>
                        </div>
                        <div class="agripump-form-actions">
                            <button type="button" id="save-payment-btn" class="agripump-btn agripump-btn-primary">
                                <?php _e('Save Payment', 'agripump-ledger'); ?>
                            </button>
                            <button type="button" id="cancel-payment-btn" class="agripump-btn agripump-btn-secondary">
                                <?php _e('Cancel', 'agripump-ledger'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Due Collection Modal functionality
jQuery(document).ready(function($) {
    var currentCustomerId = null;
    var currentTotalDue = 0;
    
    // Open modal when Due Collect button is clicked
    $('.due-collect-btn').on('click', function() {
        var customerId = $(this).data('customer-id');
        var customerName = $(this).data('customer-name');
        var totalDue = $(this).data('total-due');
        
        console.log('Due Collect button clicked:', {
            customerId: customerId,
            customerName: customerName,
            totalDue: totalDue
        });
        
        currentCustomerId = customerId;
        currentTotalDue = totalDue;
        
        $('#modal-customer-name').text('Due Collection - ' + customerName);
        $('#modal-total-due').text('৳' + parseFloat(totalDue).toFixed(2));
        
        // Show modal
        $('#due-collection-modal').show();
        $('#modal-loading').show();
        $('#modal-content').hide();
        
        // Load customer details
        loadCustomerDetails(customerId);
    });
    
    // Close modal
    $('.agripump-modal-close, #cancel-payment-btn').on('click', function() {
        $('#due-collection-modal').hide();
        resetModal();
    });
    
    // Close modal when clicking outside
    $(window).on('click', function(e) {
        if ($(e.target).is('#due-collection-modal')) {
            $('#due-collection-modal').hide();
            resetModal();
        }
    });
    
    // Save payment
    $('#save-payment-btn').on('click', function() {
        savePayment();
    });
    
    // Update payment amount help text when season is selected
    $('#payment-season').on('change', function() {
        var selectedSeason = $(this).val();
        var helpText = $('#payment-amount-help');
        
        if (selectedSeason && window.currentSeasonOptions[selectedSeason]) {
            var option = window.currentSeasonOptions[selectedSeason];
            helpText.text('Maximum payment: ৳' + option.remaining_amount.toFixed(2) + ' for ' + option.season_name);
            $('#payment-amount').attr('max', option.remaining_amount);
        } else {
            helpText.text('');
            $('#payment-amount').attr('max', currentTotalDue);
        }
    });
    
    function loadCustomerDetails(customerId) {
        console.log('Loading customer details for ID:', customerId);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'agripump_get_customer_ledger',
                customer_id: customerId,
                nonce: agripump_ajax.nonce
            },
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    displayCustomerDetails(response.data, customerId);
                } else {
                    alert('Error loading customer details: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', xhr, status, error);
                alert('Error loading customer details.');
            },
            complete: function() {
                $('#modal-loading').hide();
                $('#modal-content').show();
            }
        });
    }
    
    function displayCustomerDetails(ledgerData, customerId) {
        console.log('Displaying customer details for ID:', customerId);
        console.log('Ledger data:', ledgerData);
        
        var billsHtml = '';
        var seasonOptions = {};
        
        if (ledgerData.length > 0) {
            billsHtml = '<table class="agripump-table">';
            billsHtml += '<thead><tr>';
            billsHtml += '<th><?php _e('Date', 'agripump-ledger'); ?></th>';
            billsHtml += '<th><?php _e('Bill Items', 'agripump-ledger'); ?></th>';
            billsHtml += '<th><?php _e('Total Due', 'agripump-ledger'); ?></th>';
            billsHtml += '<th><?php _e('Paid Amount', 'agripump-ledger'); ?></th>';
            billsHtml += '<th><?php _e('Remaining', 'agripump-ledger'); ?></th>';
            billsHtml += '</tr></thead><tbody>';
            
            var totalPaid = 0;
            var totalRemaining = 0;
            
            ledgerData.forEach(function(bill) {
                console.log('Processing bill:', bill);
                
                var paidAmount = parseFloat(bill.paid_amount || 0);
                var totalAmount = parseFloat(bill.total || 0);
                var remaining = totalAmount - paidAmount;
                
                totalPaid += paidAmount;
                totalRemaining += remaining;
                
                var itemsHtml = '';
                console.log('Bill items:', bill.items);
                
                if (bill.items && (Array.isArray(bill.items) ? bill.items.length > 0 : Object.keys(bill.items).length > 0)) {
                    // Handle both array and object structures
                    var items = Array.isArray(bill.items) ? bill.items : Object.values(bill.items);
                    
                    items.forEach(function(item, itemIndex) {
                        console.log('Processing item:', item);
                        // Calculate paid amount for this specific item
                        var itemAmount = parseFloat(item.amount || 0);
                        var itemPaidForDisplay = 0;
                        var itemPaymentKeyForDisplay = item.season_id + '_' + itemIndex;
                        
                        // Check item-specific payments first (new system)
                        if (bill.item_payments && bill.item_payments[itemPaymentKeyForDisplay]) {
                            itemPaidForDisplay = parseFloat(bill.item_payments[itemPaymentKeyForDisplay]);
                        }
                        // Fallback to season payments for backward compatibility (old system)
                        else if (bill.season_payments && bill.season_payments[item.season_id]) {
                            itemPaidForDisplay = parseFloat(bill.season_payments[item.season_id]);
                        }
                        
                        var itemRemainingForDisplay = itemAmount - itemPaidForDisplay;
                        
                        itemsHtml += '<div class="bill-item">';
                        itemsHtml += '<strong>' + (item.season_name || 'Unknown Season') + '</strong>';
                        itemsHtml += ' - Land: ' + (item.land || 0) + ' shotok';
                        itemsHtml += ' - Amount: ৳' + itemAmount.toFixed(2);
                        itemsHtml += ' - Paid: ৳' + itemPaidForDisplay.toFixed(2);
                        itemsHtml += ' - Remaining: ৳' + itemRemainingForDisplay.toFixed(2);
                        itemsHtml += '</div>';
                        
                        // Collect season options for payment dropdown
                        // Make seasonKey unique by including item index to handle multiple items with same season
                        var seasonKey = item.season_id + '_' + bill.bill_id + '_' + itemIndex;
                        var seasonAmount = parseFloat(item.amount || 0);
                        
                        // Calculate item-specific remaining amount
                        var itemPaid = 0;
                        var itemPaymentKey = item.season_id + '_' + itemIndex;
                        
                        // Check item-specific payments first (new system)
                        if (bill.item_payments && bill.item_payments[itemPaymentKey]) {
                            itemPaid = parseFloat(bill.item_payments[itemPaymentKey]);
                        }
                        // Fallback to season payments for backward compatibility (old system)
                        else if (bill.season_payments && bill.season_payments[item.season_id]) {
                            itemPaid = parseFloat(bill.season_payments[item.season_id]);
                        }
                        
                        var seasonRemaining = seasonAmount - itemPaid;
                        
                        if (seasonRemaining > 0) {
                            seasonOptions[seasonKey] = {
                                season_id: item.season_id,
                                season_name: item.season_name || 'Unknown Season',
                                bill_id: bill.bill_id,
                                original_amount: seasonAmount,
                                remaining_amount: seasonRemaining,
                                land: item.land || 0,
                                item_index: itemIndex
                            };
                        }
                    });
                } else {
                    console.log('No items found for bill or items is empty');
                }
                
                billsHtml += '<tr>';
                billsHtml += '<td>' + (bill.date || 'N/A') + '</td>';
                billsHtml += '<td>' + itemsHtml + '</td>';
                billsHtml += '<td>৳' + totalAmount.toFixed(2) + '</td>';
                billsHtml += '<td>৳' + paidAmount.toFixed(2) + '</td>';
                billsHtml += '<td><strong>৳' + remaining.toFixed(2) + '</strong></td>';
                billsHtml += '</tr>';
            });
            
            billsHtml += '</tbody></table>';
            
            // Update summary
            $('#modal-total-due').text('৳' + totalRemaining.toFixed(2));
            currentTotalDue = totalRemaining;
        } else {
            billsHtml = '<p><?php _e('No bills found for this customer.', 'agripump-ledger'); ?></p>';
        }
        

        
        $('#bills-container').html(billsHtml);
        
        // Populate season dropdown
        var seasonSelect = $('#payment-season');
        seasonSelect.empty();
        seasonSelect.append('<option value=""><?php _e('Select a season...', 'agripump-ledger'); ?></option>');
        
        Object.keys(seasonOptions).forEach(function(key) {
            var option = seasonOptions[key];
            var displayText = option.season_name + ' - Land: ' + option.land + ' shotok - ৳' + option.remaining_amount.toFixed(2) + ' remaining';
            seasonSelect.append('<option value="' + key + '" data-remaining="' + option.remaining_amount + '">' + displayText + '</option>');
        });
        
        // Store season options globally
        window.currentSeasonOptions = seasonOptions;
        
        // Set max payment amount
        $('#payment-amount').attr('max', currentTotalDue);
    }
    
    function savePayment() {
        var selectedSeason = $('#payment-season').val();
        var paymentAmount = parseFloat($('#payment-amount').val()) || 0;
        var paymentDate = $('#payment-date').val();
        var paymentNotes = $('#payment-notes').val();
        
        if (!selectedSeason) {
            alert('<?php _e('Please select a season.', 'agripump-ledger'); ?>');
            return;
        }
        
        if (paymentAmount <= 0) {
            alert('<?php _e('Please enter a valid payment amount.', 'agripump-ledger'); ?>');
            return;
        }
        
        var seasonOption = window.currentSeasonOptions[selectedSeason];
        if (!seasonOption) {
            alert('<?php _e('Invalid season selected.', 'agripump-ledger'); ?>');
            return;
        }
        
        if (paymentAmount > seasonOption.remaining_amount) {
            alert('<?php _e('Payment amount cannot exceed the remaining amount for this season.', 'agripump-ledger'); ?>');
            return;
        }
        
        if (!paymentDate) {
            alert('<?php _e('Please select a payment date.', 'agripump-ledger'); ?>');
            return;
        }
        
        // Show loading
        $('#save-payment-btn').prop('disabled', true).text('<?php _e('Saving...', 'agripump-ledger'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'agripump_save_payment',
                customer_id: currentCustomerId,
                season_id: seasonOption.season_id,
                bill_id: seasonOption.bill_id,
                item_index: seasonOption.item_index,
                payment_amount: paymentAmount,
                payment_date: paymentDate,
                payment_notes: paymentNotes,
                nonce: agripump_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Payment saved successfully!', 'agripump-ledger'); ?>');
                    $('#due-collection-modal').hide();
                    resetModal();
                    // Reload the page to refresh the data
                    location.reload();
                } else {
                    alert('Error saving payment: ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Error saving payment.', 'agripump-ledger'); ?>');
            },
            complete: function() {
                $('#save-payment-btn').prop('disabled', false).text('<?php _e('Save Payment', 'agripump-ledger'); ?>');
            }
        });
    }
    
    function resetModal() {
        $('#payment-amount').val('');
        $('#payment-date').val('<?php echo date('Y-m-d'); ?>');
        $('#payment-notes').val('');
        currentCustomerId = null;
        currentTotalDue = 0;
    }
});

function exportToCSV() {
    var csv = 'Customer Name,Location,Mobile,Bill Count,Total Outstanding\n';
    
    <?php foreach ($customer_dues as $due): ?>
    var customer = '<?php echo addslashes($due['customer']->post_title); ?>';
    var location = '<?php 
        $location_id = get_post_meta($due['customer']->ID, 'location_id', true);
        $location = get_post($location_id);
        echo addslashes($location ? $location->post_title : 'Unknown');
    ?>';
    var mobile = '<?php echo addslashes(get_post_meta($due['customer']->ID, 'mobile', true)); ?>';
    var billCount = <?php echo $due['bill_count']; ?>;
    var totalDue = <?php echo $due['total_due']; ?>;
    
    csv += '"' + customer + '","' + location + '","' + mobile + '",' + billCount + ',' + totalDue.toFixed(2) + '\n';
    <?php endforeach; ?>
    
    var blob = new Blob([csv], { type: 'text/csv' });
    var url = window.URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'due_collection_report_<?php echo current_time('Y-m-d'); ?>.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script> 