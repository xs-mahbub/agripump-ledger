<?php
if (!defined('ABSPATH')) {
    exit;
}

// Note: Form submission is now handled via AJAX in admin.js

// Handle delete
if (isset($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_customer_' . $_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $result = wp_delete_post($delete_id, true);
    
    if ($result) {
        echo '<div class="notice notice-success"><p>' . __('Customer deleted successfully!', 'agripump-ledger') . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>' . __('Error deleting customer.', 'agripump-ledger') . '</p></div>';
    }
}

// Get locations for dropdown
$locations = get_posts(array(
    'post_type' => 'agripump_location',
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC'
));

// Get seasons for bill form
$seasons = get_posts(array(
    'post_type' => 'agripump_season',
    'numberposts' => -1,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC'
));

// Build seasons data for JavaScript
$seasons_data = array();
foreach ($seasons as $season) {
    $seasons_data[] = array(
        'ID' => $season->ID,
        'post_title' => $season->post_title,
        'price' => get_post_meta($season->ID, 'price', true)
    );
}

// Pagination setup
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Get customers with filter
$customer_args = array(
    'post_type' => 'agripump_customer',
    'numberposts' => $per_page,
    'offset' => $offset,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'ASC'
);

// Filter by location if specified
if (isset($_GET['location_id']) && !empty($_GET['location_id'])) {
    $customer_args['meta_key'] = 'location_id';
    $customer_args['meta_value'] = intval($_GET['location_id']);
}

$customers = get_posts($customer_args);

// Get total count for pagination
$total_customers_args = array(
    'post_type' => 'agripump_customer',
    'numberposts' => -1,
    'post_status' => 'publish'
);

if (isset($_GET['location_id']) && !empty($_GET['location_id'])) {
    $total_customers_args['meta_key'] = 'location_id';
    $total_customers_args['meta_value'] = intval($_GET['location_id']);
}

$total_customers = count(get_posts($total_customers_args));
$total_pages = ceil($total_customers / $per_page);

// Get customer for editing
$edit_customer = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_customer = get_post($edit_id);
}

// Get customer for bill management
$bill_customer = null;
if (isset($_GET['customer_id'])) {
    $bill_customer_id = intval($_GET['customer_id']);
    $bill_customer = get_post($bill_customer_id);
    
    // Check if customer exists
    if (!$bill_customer || $bill_customer->post_type !== 'agripump_customer') {
        echo '<div class="notice notice-error"><p>' . __('Customer not found or invalid customer ID.', 'agripump-ledger') . '</p></div>';
        $bill_customer = null;
    }
}
?>

<div class="wrap agripump-wrap">
    <div class="agripump-header">
        <h1><?php _e('Manage Customers', 'agripump-ledger'); ?></h1>
        <p><?php _e('Add and manage customers with their billing information', 'agripump-ledger'); ?></p>
    </div>
    
    <?php if (!$bill_customer): ?>
    <!-- Customer Form -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php echo $edit_customer ? __('Edit Customer', 'agripump-ledger') : __('Add New Customer', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <form class="agripump-customer-form">
                <input type="hidden" id="customer_id" name="customer_id" value="<?php echo $edit_customer ? $edit_customer->ID : ''; ?>">
                
                <div class="agripump-form-group">
                    <label for="customer_name"><?php _e('Customer Name', 'agripump-ledger'); ?> *</label>
                    <input type="text" id="customer_name" name="customer_name" class="agripump-form-control" 
                           value="<?php echo $edit_customer ? esc_attr($edit_customer->post_title) : ''; ?>" 
                           placeholder="<?php _e('Enter customer name', 'agripump-ledger'); ?>" required>
                </div>
                
                <div class="agripump-form-group">
                    <label for="father_name"><?php _e('Father Name', 'agripump-ledger'); ?></label>
                    <input type="text" id="father_name" name="father_name" class="agripump-form-control" 
                           value="<?php echo $edit_customer ? esc_attr(get_post_meta($edit_customer->ID, 'father_name', true)) : ''; ?>" 
                           placeholder="<?php _e('Enter father name', 'agripump-ledger'); ?>">
                </div>
                
                <div class="agripump-form-group">
                    <label for="location_id"><?php _e('Address (Location)', 'agripump-ledger'); ?> *</label>
                    <select id="location_id" name="location_id" class="agripump-form-control" required>
                        <option value=""><?php _e('Select location', 'agripump-ledger'); ?></option>
                        <?php foreach ($locations as $location): ?>
                            <option value="<?php echo $location->ID; ?>" 
                                    <?php echo $edit_customer && get_post_meta($edit_customer->ID, 'location_id', true) == $location->ID ? 'selected' : ''; ?>>
                                <?php echo esc_html($location->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="agripump-form-group">
                    <label for="mobile"><?php _e('Mobile Number', 'agripump-ledger'); ?></label>
                    <input type="text" id="mobile" name="mobile" class="agripump-form-control" 
                           value="<?php echo $edit_customer ? esc_attr(get_post_meta($edit_customer->ID, 'mobile', true)) : ''; ?>" 
                           placeholder="<?php _e('Enter mobile number', 'agripump-ledger'); ?>">
                </div>
                
                <div class="agripump-actions">
                    <button type="submit" class="agripump-btn agripump-btn-primary">
                        <?php echo $edit_customer ? __('Update Customer', 'agripump-ledger') : __('Add Customer', 'agripump-ledger'); ?>
                    </button>
                    
                    <?php if ($edit_customer): ?>
                        <a href="<?php echo admin_url('admin.php?page=agripump-customers'); ?>" class="agripump-btn agripump-btn-secondary">
                            <?php _e('Cancel', 'agripump-ledger'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Customers List -->
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('All Customers', 'agripump-ledger'); ?></h2>
            <div class="agripump-search-box">
                <input type="text" class="agripump-form-control agripump-search-input" 
                       placeholder="<?php _e('Search customers...', 'agripump-ledger'); ?>">
            </div>
        </div>
        <div class="agripump-card-body">
            <?php if ($customers): ?>
                <table class="agripump-table">
                    <thead>
                        <tr>
                            <th><?php _e('Customer Name', 'agripump-ledger'); ?></th>
                            <th><?php _e('Father Name', 'agripump-ledger'); ?></th>
                            <th><?php _e('Location', 'agripump-ledger'); ?></th>
                            <th><?php _e('Mobile', 'agripump-ledger'); ?></th>
                            <th><?php _e('Actions', 'agripump-ledger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): 
                            $father_name = get_post_meta($customer->ID, 'father_name', true);
                            $location_id = get_post_meta($customer->ID, 'location_id', true);
                            $location = get_post($location_id);
                            $mobile = get_post_meta($customer->ID, 'mobile', true);
                        ?>
                        <tr>
                            <td><?php echo esc_html($customer->post_title); ?></td>
                            <td><?php echo esc_html($father_name); ?></td>
                            <td><?php echo $location ? esc_html($location->post_title) : __('Unknown', 'agripump-ledger'); ?></td>
                            <td><?php echo esc_html($mobile); ?></td>
                            <td>
                                <div class="agripump-actions">
                                    <a href="<?php echo admin_url('admin.php?page=agripump-customers&customer_id=' . $customer->ID); ?>" 
                                       class="agripump-btn agripump-btn-sm agripump-btn-primary">
                                        <?php _e('Manage Bills', 'agripump-ledger'); ?>
                                    </a>
                                    
                                    <a href="<?php echo admin_url('admin.php?page=agripump-customers&edit=' . $customer->ID); ?>" 
                                       class="agripump-btn agripump-btn-sm agripump-btn-secondary">
                                        <?php _e('Edit', 'agripump-ledger'); ?>
                                    </a>
                                    
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=agripump-customers&delete=' . $customer->ID), 'delete_customer_' . $customer->ID); ?>" 
                                       class="agripump-btn agripump-btn-sm agripump-btn-danger agripump-delete-btn">
                                        <?php _e('Delete', 'agripump-ledger'); ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="agripump-empty-state">
                    <h3><?php _e('No customers found', 'agripump-ledger'); ?></h3>
                    <p><?php _e('Add your first customer to get started.', 'agripump-ledger'); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="agripump-pagination">
                    <?php
                    $base_url = add_query_arg(array('page' => 'agripump-customers'), admin_url('admin.php'));
                    if (isset($_GET['location_id'])) {
                        $base_url = add_query_arg('location_id', $_GET['location_id'], $base_url);
                    }
                    
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
                        <?php printf(__('Showing %d-%d of %d customers', 'agripump-ledger'), 
                            $offset + 1, 
                            min($offset + $per_page, $total_customers), 
                            $total_customers); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php else: ?>
    <!-- Bill Management for Specific Customer -->
    <?php
    // Calculate total due for this customer
    $customer_bills = get_posts(array(
        'post_type' => 'agripump_bill',
        'meta_query' => array(
            array(
                'key' => 'customer_id',
                'value' => $bill_customer->ID,
                'compare' => '='
            )
        ),
        'numberposts' => -1,
        'post_status' => 'publish'
    ));
    
    $total_due = 0;
    $total_paid = 0;
    $total_remaining = 0;
    foreach ($customer_bills as $bill) {
        $bill_total = floatval(get_post_meta($bill->ID, 'total_amount', true));
        $paid_amount = floatval(get_post_meta($bill->ID, 'paid_amount', true));
        $total_due += $bill_total;
        $total_paid += $paid_amount;
        $total_remaining += ($bill_total - $paid_amount);
    }
    ?>
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php printf(__('Bill Management - %s', 'agripump-ledger'), $bill_customer->post_title); ?></h2>
            <div style="display: flex; align-items: center; gap: 15px;">
                <div style="border: 2px solid #dc3545; padding: 10px 15px; border-radius: 5px; background: #fff5f5;">
                    <strong style="color: #dc3545;"><?php _e('Total Due:', 'agripump-ledger'); ?> <?php echo number_format($total_remaining, 2); ?></strong>
                </div>
                <a href="<?php echo admin_url('admin.php?page=agripump-customers'); ?>" class="agripump-btn agripump-btn-secondary">
                    <?php _e('Back to Customers', 'agripump-ledger'); ?>
                </a>
            </div>
        </div>
        <div class="agripump-card-body">
            <div class="agripump-customer-info">
                <h3><?php _e('Customer Information', 'agripump-ledger'); ?></h3>
                <p><strong><?php _e('Name:', 'agripump-ledger'); ?></strong> <?php echo esc_html($bill_customer->post_title); ?></p>
                <p><strong><?php _e('Father Name:', 'agripump-ledger'); ?></strong> <?php echo esc_html(get_post_meta($bill_customer->ID, 'father_name', true)); ?></p>
                <p><strong><?php _e('Location:', 'agripump-ledger'); ?></strong> 
                    <?php 
                    $location_id = get_post_meta($bill_customer->ID, 'location_id', true);
                    $location = get_post($location_id);
                    echo $location ? esc_html($location->post_title) : __('Unknown', 'agripump-ledger');
                    ?>
                </p>
                <p><strong><?php _e('Mobile:', 'agripump-ledger'); ?></strong> <?php echo esc_html(get_post_meta($bill_customer->ID, 'mobile', true)); ?></p>
                <p><strong><?php _e('Total Paid:', 'agripump-ledger'); ?></strong> <?php echo number_format($total_paid, 2); ?></p>
            </div>
            
            <!-- Customer Ledger -->
            <div class="agripump-ledger">
                <h3><?php _e('Customer Ledger', 'agripump-ledger'); ?></h3>
                <div class="agripump-loading"><?php _e('Loading ledger...', 'agripump-ledger'); ?></div>
                <button type="button" class="agripump-btn agripump-btn-secondary debug-bill-btn" style="margin-top: 10px;">
                    <?php _e('Debug Bill Data', 'agripump-ledger'); ?>
                </button>
            </div>
            
            <!-- Add New Bill Form -->
            <div class="agripump-bill-form" data-customer-id="<?php echo $bill_customer->ID; ?>">
                <h3><?php _e('Add New Bill', 'agripump-ledger'); ?></h3>
                <form class="agripump-bill-form">
                    <input type="hidden" name="customer_id" value="<?php echo $bill_customer->ID; ?>">
                    
                    <div class="agripump-form-group">
                        <label for="bill_date"><?php _e('Bill Date', 'agripump-ledger'); ?> *</label>
                        <input type="text" id="bill_date" name="bill_date" class="agripump-form-control agripump-datepicker" 
                               value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="agripump-bill-items">
                        <!-- Bill items will be added here dynamically -->
                    </div>
                    
                    <button type="button" class="agripump-btn agripump-btn-success add-bill-item">
                        <?php _e('Add More +', 'agripump-ledger'); ?>
                    </button>
                    
                    <div class="agripump-total"><?php _e('Total: 0.00', 'agripump-ledger'); ?></div>
                    
                    <div class="agripump-actions">
                        <button type="submit" class="agripump-btn agripump-btn-primary">
                            <?php _e('Save Bill', 'agripump-ledger'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Pass seasons data to JavaScript
var agripump_seasons = <?php echo json_encode($seasons_data); ?>;
</script> 