<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get statistics
$total_locations = wp_count_posts('agripump_location')->publish;
$total_seasons = wp_count_posts('agripump_season')->publish;
$total_customers = wp_count_posts('agripump_customer')->publish;
$total_bills = wp_count_posts('agripump_bill')->publish;

// Calculate total revenue
$bills = get_posts(array(
    'post_type' => 'agripump_bill',
    'numberposts' => -1,
    'post_status' => 'publish'
));

$total_revenue = 0;
foreach ($bills as $bill) {
    $total_revenue += floatval(get_post_meta($bill->ID, 'total_amount', true));
}
?>

<div class="wrap agripump-wrap">
    <div class="agripump-header">
        <h1><?php _e('AgriPump Ledger Dashboard', 'agripump-ledger'); ?></h1>
        <p><?php _e('Manage your agricultural pump business efficiently', 'agripump-ledger'); ?></p>
    </div>
    
    <div class="agripump-stats-grid">
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo number_format($total_locations); ?></div>
            <div class="agripump-stat-label"><?php _e('Total Locations', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo number_format($total_seasons); ?></div>
            <div class="agripump-stat-label"><?php _e('Total Seasons', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo number_format($total_customers); ?></div>
            <div class="agripump-stat-label"><?php _e('Total Customers', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo number_format($total_bills); ?></div>
            <div class="agripump-stat-label"><?php _e('Total Bills', 'agripump-ledger'); ?></div>
        </div>
        
        <div class="agripump-stat-card">
            <div class="agripump-stat-number"><?php echo number_format($total_revenue, 2); ?></div>
            <div class="agripump-stat-label"><?php _e('Total Revenue', 'agripump-ledger'); ?></div>
        </div>
    </div>
    
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Quick Actions', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <div class="agripump-actions">
                <a href="<?php echo admin_url('admin.php?page=agripump-locations'); ?>" class="agripump-btn agripump-btn-primary">
                    <?php _e('Manage Locations', 'agripump-ledger'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=agripump-seasons'); ?>" class="agripump-btn agripump-btn-success">
                    <?php _e('Manage Seasons', 'agripump-ledger'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=agripump-customers'); ?>" class="agripump-btn agripump-btn-secondary">
                    <?php _e('Manage Customers', 'agripump-ledger'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=agripump-due-collection'); ?>" class="agripump-btn agripump-btn-danger">
                    <?php _e('Due Collection', 'agripump-ledger'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('Recent Activity', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <?php
            $recent_bills = get_posts(array(
                'post_type' => 'agripump_bill',
                'numberposts' => 5,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            
            if ($recent_bills): ?>
                <table class="agripump-table">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'agripump-ledger'); ?></th>
                            <th><?php _e('Customer', 'agripump-ledger'); ?></th>
                            <th><?php _e('Amount', 'agripump-ledger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bills as $bill): 
                            $customer_id = get_post_meta($bill->ID, 'customer_id', true);
                            $customer = get_post($customer_id);
                            $amount = get_post_meta($bill->ID, 'total_amount', true);
                        ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($bill->post_date)); ?></td>
                            <td><?php echo $customer ? $customer->post_title : __('Unknown Customer', 'agripump-ledger'); ?></td>
                            <td><?php echo number_format($amount, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="agripump-empty-state">
                    <h3><?php _e('No recent activity', 'agripump-ledger'); ?></h3>
                    <p><?php _e('Start by adding customers and creating bills.', 'agripump-ledger'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 