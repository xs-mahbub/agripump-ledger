<?php
if (!defined('ABSPATH')) {
    exit;
}

// Note: Form submission is now handled via AJAX in admin.js

// Handle delete
if (isset($_GET['delete']) && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_season_' . $_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    
    // Check if season is being used in any bills
    $bills_using_season = get_posts(array(
        'post_type' => 'agripump_bill',
        'meta_query' => array(
            array(
                'key' => 'bill_items',
                'value' => '"season_id":"' . $delete_id . '"',
                'compare' => 'LIKE'
            )
        ),
        'numberposts' => 1,
        'post_status' => 'publish'
    ));
    
    if (!empty($bills_using_season)) {
        echo '<div class="notice notice-error"><p>' . __('Cannot delete season. It is being used in existing bills. Please delete the bills first or update them to use a different season.', 'agripump-ledger') . '</p></div>';
    } else {
    $result = wp_delete_post($delete_id, true);
    
    if ($result) {
        echo '<div class="notice notice-success"><p>' . __('Season deleted successfully!', 'agripump-ledger') . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>' . __('Error deleting season.', 'agripump-ledger') . '</p></div>';
        }
    }
}

// Pagination setup
$per_page = 20;
$current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($current_page - 1) * $per_page;

// Get total count
$total_seasons = wp_count_posts('agripump_season')->publish;

// Get seasons with pagination
$seasons = get_posts(array(
    'post_type' => 'agripump_season',
    'numberposts' => $per_page,
    'offset' => $offset,
    'post_status' => 'publish',
    'orderby' => 'date',
    'order' => 'ASC'
));

// Calculate pagination
$total_pages = ceil($total_seasons / $per_page);

// Get season for editing
$edit_season = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_season = get_post($edit_id);
}
?>

<div class="wrap agripump-wrap">
    <div class="agripump-header">
        <h1><?php _e('Manage Seasons', 'agripump-ledger'); ?></h1>
        <p><?php _e('Add and manage seasons with their prices', 'agripump-ledger'); ?></p>
    </div>
    
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php echo $edit_season ? __('Edit Season', 'agripump-ledger') : __('Add New Season', 'agripump-ledger'); ?></h2>
        </div>
        <div class="agripump-card-body">
            <form class="agripump-season-form">
                <input type="hidden" id="season_id" name="season_id" value="<?php echo $edit_season ? $edit_season->ID : ''; ?>">
                
                <div class="agripump-form-group">
                    <label for="season_name"><?php _e('Season Name', 'agripump-ledger'); ?> *</label>
                    <input type="text" id="season_name" name="season_name" class="agripump-form-control" 
                           value="<?php echo $edit_season ? esc_attr($edit_season->post_title) : ''; ?>" 
                           placeholder="<?php _e('Enter season name', 'agripump-ledger'); ?>" required>
                </div>
                
                <div class="agripump-form-group">
                    <label for="price"><?php _e('Price per Unit', 'agripump-ledger'); ?> *</label>
                    <input type="number" id="price" name="price" class="agripump-form-control" 
                           value="<?php echo $edit_season ? esc_attr(get_post_meta($edit_season->ID, 'price', true)) : ''; ?>" 
                           placeholder="<?php _e('Enter price', 'agripump-ledger'); ?>" step="0.01" min="0" required>
                </div>
                
                <div class="agripump-actions">
                    <button type="submit" class="agripump-btn agripump-btn-primary">
                        <?php echo $edit_season ? __('Update Season', 'agripump-ledger') : __('Add Season', 'agripump-ledger'); ?>
                    </button>
                    
                    <?php if ($edit_season): ?>
                        <a href="<?php echo admin_url('admin.php?page=agripump-seasons'); ?>" class="agripump-btn agripump-btn-secondary">
                            <?php _e('Cancel', 'agripump-ledger'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <div class="agripump-card">
        <div class="agripump-card-header">
            <h2><?php _e('All Seasons', 'agripump-ledger'); ?></h2>
            <div class="agripump-search-box">
                <input type="text" class="agripump-form-control agripump-search-input" 
                       placeholder="<?php _e('Search seasons...', 'agripump-ledger'); ?>">
            </div>
        </div>
        <div class="agripump-card-body">
            <?php if ($seasons): ?>
                <table class="agripump-table">
                    <thead>
                        <tr>
                            <th><?php _e('Season Name', 'agripump-ledger'); ?></th>
                            <th><?php _e('Price per Unit', 'agripump-ledger'); ?></th>
                            <th><?php _e('Actions', 'agripump-ledger'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($seasons as $season): 
                            $price = get_post_meta($season->ID, 'price', true);
                        ?>
                        <tr>
                            <td><?php echo esc_html($season->post_title); ?></td>
                            <td><?php echo number_format($price, 2); ?></td>
                            <td>
                                <div class="agripump-actions">
                                    <a href="<?php echo admin_url('admin.php?page=agripump-seasons&edit=' . $season->ID); ?>" 
                                       class="agripump-btn agripump-btn-sm agripump-btn-secondary">
                                        <?php _e('Edit', 'agripump-ledger'); ?>
                                    </a>
                                    
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=agripump-seasons&delete=' . $season->ID), 'delete_season_' . $season->ID); ?>" 
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
                    <h3><?php _e('No seasons found', 'agripump-ledger'); ?></h3>
                    <p><?php _e('Add your first season to get started.', 'agripump-ledger'); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="agripump-pagination">
                    <?php
                    $base_url = add_query_arg(array('page' => 'agripump-seasons'), admin_url('admin.php'));
                    
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
                        <?php printf(__('Showing %d-%d of %d seasons', 'agripump-ledger'), 
                            $offset + 1, 
                            min($offset + $per_page, $total_seasons), 
                            $total_seasons); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> 