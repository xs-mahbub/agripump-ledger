<?php
if (!defined('ABSPATH')) {
    exit;
}

// Note: Form submission is now handled via AJAX in admin.js

// Handle delete
if (isset($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_location_' . $_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $result = wp_delete_post($delete_id, true);
    
    if ($result) {
        echo '<div class="notice notice-success"><p>' . __('Location deleted successfully!', 'agripump-ledger') . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>' . __('Error deleting location.', 'agripump-ledger') . '</p></div>';
    }
}

// Pagination setup
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Get total count
$total_locations = wp_count_posts('agripump_location')->publish;

// Get locations with pagination
$locations = get_posts(array(
    'post_type' => 'agripump_location',
    'numberposts' => $per_page,
    'offset' => $offset,
    'post_status' => 'publish',
    'orderby' => 'title',
    'order' => 'ASC'
));

// Calculate pagination
$total_pages = ceil($total_locations / $per_page);

// Get location for editing
$edit_location = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_location = get_post($edit_id);
}
?>

<div class="wrap agripump-wrap">
    <div class="agripump-header">
        <h1><?php _e('Manage Locations', 'agripump-ledger'); ?></h1>
        <p><?php _e('Add and manage locations for your customers', 'agripump-ledger'); ?></p>
    </div>
    
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php echo $edit_location ? __('Edit Location', 'agripump-ledger') : __('Add New Location', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <form class="agripump-location-form">
                <input type="hidden" id="location_id" name="location_id" value="<?php echo $edit_location ? $edit_location->ID : ''; ?>">
                
                <div class="agripump-form-group">
                    <label for="location_name"><?php _e('Location Name', 'agripump-ledger'); ?> *</label>
                    <input type="text" id="location_name" name="location_name" class="agripump-form-control" 
                           value="<?php echo $edit_location ? esc_attr($edit_location->post_title) : ''; ?>" 
                           placeholder="<?php _e('Enter location name', 'agripump-ledger'); ?>" required>
                </div>
                
                <div class="agripump-form-group">
                    <label for="post_office"><?php _e('Post Office', 'agripump-ledger'); ?></label>
                    <input type="text" id="post_office" name="post_office" class="agripump-form-control" 
                           value="<?php echo $edit_location ? esc_attr(get_post_meta($edit_location->ID, 'post_office', true)) : ''; ?>" 
                           placeholder="<?php _e('Enter post office name', 'agripump-ledger'); ?>">
                </div>
                
                <div class="agripump-form-group">
                    <label for="thana"><?php _e('Thana', 'agripump-ledger'); ?></label>
                    <input type="text" id="thana" name="thana" class="agripump-form-control" 
                           value="<?php echo $edit_location ? esc_attr(get_post_meta($edit_location->ID, 'thana', true)) : ''; ?>" 
                           placeholder="<?php _e('Enter thana name', 'agripump-ledger'); ?>">
                </div>
                
                <div class="agripump-form-group">
                    <label for="district"><?php _e('District', 'agripump-ledger'); ?></label>
                    <input type="text" id="district" name="district" class="agripump-form-control" 
                           value="<?php echo $edit_location ? esc_attr(get_post_meta($edit_location->ID, 'district', true)) : ''; ?>" 
                           placeholder="<?php _e('Enter district name', 'agripump-ledger'); ?>">
                </div>
                
                <div class="agripump-actions">
                    <button type="submit" class="agripump-btn agripump-btn-primary">
                        <?php echo $edit_location ? __('Update Location', 'agripump-ledger') : __('Add Location', 'agripump-ledger'); ?>
                    </button>
                    
                    <?php if ($edit_location): ?>
                        <a href="<?php echo admin_url('admin.php?page=agripump-locations'); ?>" class="agripump-btn agripump-btn-secondary">
                            <?php _e('Cancel', 'agripump-ledger'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('All Locations', 'agripump-ledger'); ?></h2>
            <div class="agripump-search-box">
                <input type="text" class="agripump-form-control agripump-search-input" 
                       placeholder="<?php _e('Search locations...', 'agripump-ledger'); ?>">
            </div>
        </div>
        <div class="agripump-card-body">
            <?php if ($locations): ?>
                <table class="agripump-table">
                    <thead>
                        <tr>
                            <th><?php _e('Location Name', 'agripump-ledger'); ?></th>
                            <th><?php _e('Post Office', 'agripump-ledger'); ?></th>
                            <th><?php _e('Thana', 'agripump-ledger'); ?></th>
                            <th><?php _e('District', 'agripump-ledger'); ?></th>
                            <th><?php _e('Actions', 'agripump-ledger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($locations as $location): 
                            $post_office = get_post_meta($location->ID, 'post_office', true);
                            $thana = get_post_meta($location->ID, 'thana', true);
                            $district = get_post_meta($location->ID, 'district', true);
                            
                            // Count customers in this location
                            $customers_in_location = get_posts(array(
                                'post_type' => 'agripump_customer',
                                'meta_key' => 'location_id',
                                'meta_value' => $location->ID,
                                'numberposts' => -1,
                                'post_status' => 'publish'
                            ));
                            $customer_count = count($customers_in_location);
                        ?>
                        <tr>
                            <td><?php echo esc_html($location->post_title); ?></td>
                            <td><?php echo esc_html($post_office); ?></td>
                            <td><?php echo esc_html($thana); ?></td>
                            <td><?php echo esc_html($district); ?></td>
                            <td>
                                <div class="agripump-actions">
                                    <a href="<?php echo admin_url('admin.php?page=agripump-customers&location_id=' . $location->ID); ?>" 
                                       class="agripump-btn agripump-btn-sm agripump-btn-primary view-customers-btn" 
                                       data-location-id="<?php echo $location->ID; ?>">
                                        <?php _e('View Customers', 'agripump-ledger'); ?> (<?php echo $customer_count; ?>)
                                    </a>
                                    
                                    <a href="<?php echo admin_url('admin.php?page=agripump-locations&edit=' . $location->ID); ?>" 
                                       class="agripump-btn agripump-btn-sm agripump-btn-secondary">
                                        <?php _e('Edit', 'agripump-ledger'); ?>
                                    </a>
                                    
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=agripump-locations&delete=' . $location->ID), 'delete_location_' . $location->ID); ?>" 
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
                    <h3><?php _e('No locations found', 'agripump-ledger'); ?></h3>
                    <p><?php _e('Add your first location to get started.', 'agripump-ledger'); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="agripump-pagination">
                    <?php
                    $base_url = add_query_arg(array('page' => 'agripump-locations'), admin_url('admin.php'));
                    
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
                        <?php printf(__('Showing %d-%d of %d locations', 'agripump-ledger'), 
                            $offset + 1, 
                            min($offset + $per_page, $total_locations), 
                            $total_locations); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 