<?php
/**
 * Plugin Name: AgriPump Ledger
 * Plugin URI: https://agripump.com
 * Description: A comprehensive CRM plugin for managing agricultural pump customers, locations, seasons, and due collections.
 * Version: 1.0.0
 * Author: AgriPump Team
 * Text Domain: agripump-ledger
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('AGRIPUMP_LEDGER_VERSION', '1.0.0');
define('AGRIPUMP_LEDGER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AGRIPUMP_LEDGER_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('AGRIPUMP_LEDGER_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Main plugin class
class AgriPumpLedger {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
            add_action('wp_ajax_agripump_save_bill', array($this, 'save_bill'));
    add_action('wp_ajax_agripump_get_customer_ledger', array($this, 'get_customer_ledger'));
    add_action('wp_ajax_agripump_save_location', array($this, 'save_location'));
    add_action('wp_ajax_agripump_save_season', array($this, 'save_season'));
    add_action('wp_ajax_agripump_save_customer', array($this, 'save_customer'));
    add_action('wp_ajax_agripump_get_item', array($this, 'get_item'));
    add_action('wp_ajax_agripump_debug_bill', array($this, 'debug_bill'));
    add_action('wp_ajax_agripump_save_payment', array($this, 'save_payment'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    public function init() {
        $this->register_post_types();
        $this->load_textdomain();
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('agripump-ledger', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function register_post_types() {
        // Register Location post type
        register_post_type('agripump_location', array(
            'labels' => array(
                'name' => __('Locations', 'agripump-ledger'),
                'singular_name' => __('Location', 'agripump-ledger'),
                'add_new' => __('Add New Location', 'agripump-ledger'),
                'add_new_item' => __('Add New Location', 'agripump-ledger'),
                'edit_item' => __('Edit Location', 'agripump-ledger'),
                'new_item' => __('New Location', 'agripump-ledger'),
                'view_item' => __('View Location', 'agripump-ledger'),
                'search_items' => __('Search Locations', 'agripump-ledger'),
                'not_found' => __('No locations found', 'agripump-ledger'),
                'not_found_in_trash' => __('No locations found in trash', 'agripump-ledger'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
        ));
        
        // Register Season post type
        register_post_type('agripump_season', array(
            'labels' => array(
                'name' => __('Seasons', 'agripump-ledger'),
                'singular_name' => __('Season', 'agripump-ledger'),
                'add_new' => __('Add New Season', 'agripump-ledger'),
                'add_new_item' => __('Add New Season', 'agripump-ledger'),
                'edit_item' => __('Edit Season', 'agripump-ledger'),
                'new_item' => __('New Season', 'agripump-ledger'),
                'view_item' => __('View Season', 'agripump-ledger'),
                'search_items' => __('Search Seasons', 'agripump-ledger'),
                'not_found' => __('No seasons found', 'agripump-ledger'),
                'not_found_in_trash' => __('No seasons found in trash', 'agripump-ledger'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
        ));
        
        // Register Customer post type
        register_post_type('agripump_customer', array(
            'labels' => array(
                'name' => __('Customers', 'agripump-ledger'),
                'singular_name' => __('Customer', 'agripump-ledger'),
                'add_new' => __('Add New Customer', 'agripump-ledger'),
                'add_new_item' => __('Add New Customer', 'agripump-ledger'),
                'edit_item' => __('Edit Customer', 'agripump-ledger'),
                'new_item' => __('New Customer', 'agripump-ledger'),
                'view_item' => __('View Customer', 'agripump-ledger'),
                'search_items' => __('Search Customers', 'agripump-ledger'),
                'not_found' => __('No customers found', 'agripump-ledger'),
                'not_found_in_trash' => __('No customers found in trash', 'agripump-ledger'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
        ));
        
        // Register Bill post type
        register_post_type('agripump_bill', array(
            'labels' => array(
                'name' => __('Bills', 'agripump-ledger'),
                'singular_name' => __('Bill', 'agripump-ledger'),
                'add_new' => __('Add New Bill', 'agripump-ledger'),
                'add_new_item' => __('Add New Bill', 'agripump-ledger'),
                'edit_item' => __('Edit Bill', 'agripump-ledger'),
                'new_item' => __('New Bill', 'agripump-ledger'),
                'view_item' => __('View Bill', 'agripump-ledger'),
                'search_items' => __('Search Bills', 'agripump-ledger'),
                'not_found' => __('No bills found', 'agripump-ledger'),
                'not_found_in_trash' => __('No bills found in trash', 'agripump-ledger'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
        ));
        
        // Register Payment post type
        register_post_type('agripump_payment', array(
            'labels' => array(
                'name' => __('Payments', 'agripump-ledger'),
                'singular_name' => __('Payment', 'agripump-ledger'),
                'add_new' => __('Add New Payment', 'agripump-ledger'),
                'add_new_item' => __('Add New Payment', 'agripump-ledger'),
                'edit_item' => __('Edit Payment', 'agripump-ledger'),
                'new_item' => __('New Payment', 'agripump-ledger'),
                'view_item' => __('View Payment', 'agripump-ledger'),
                'search_items' => __('Search Payments', 'agripump-ledger'),
                'not_found' => __('No payments found', 'agripump-ledger'),
                'not_found_in_trash' => __('No payments found in trash', 'agripump-ledger'),
            ),
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => array('title'),
            'capability_type' => 'post',
        ));
    }
    
    public function admin_menu() {
        add_menu_page(
            __('AgriPump Ledger', 'agripump-ledger'),
            __('AgriPump Ledger', 'agripump-ledger'),
            'manage_options',
            'agripump-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-chart-area',
            30
        );
        
        add_submenu_page(
            'agripump-dashboard',
            __('Dashboard', 'agripump-ledger'),
            __('Dashboard', 'agripump-ledger'),
            'manage_options',
            'agripump-dashboard',
            array($this, 'dashboard_page')
        );
        
        add_submenu_page(
            'agripump-dashboard',
            __('Locations', 'agripump-ledger'),
            __('Locations', 'agripump-ledger'),
            'manage_options',
            'agripump-locations',
            array($this, 'locations_page')
        );
        
        add_submenu_page(
            'agripump-dashboard',
            __('Seasons', 'agripump-ledger'),
            __('Seasons', 'agripump-ledger'),
            'manage_options',
            'agripump-seasons',
            array($this, 'seasons_page')
        );
        
        add_submenu_page(
            'agripump-dashboard',
            __('Customers', 'agripump-ledger'),
            __('Customers', 'agripump-ledger'),
            'manage_options',
            'agripump-customers',
            array($this, 'customers_page')
        );
        
        add_submenu_page(
            'agripump-dashboard',
            __('Due Collection', 'agripump-ledger'),
            __('Due Collection', 'agripump-ledger'),
            'manage_options',
            'agripump-due-collection',
            array($this, 'due_collection_page')
        );
        
        add_submenu_page(
            'agripump-dashboard',
            __('Payment Reports', 'agripump-ledger'),
            __('Payment Reports', 'agripump-ledger'),
            'manage_options',
            'agripump-payment-reports',
            array($this, 'payment_reports_page')
        );
    }
    
    public function admin_scripts($hook) {
        if (strpos($hook, 'agripump') !== false) {
            wp_enqueue_style('agripump-admin-style', AGRIPUMP_LEDGER_PLUGIN_URL . 'assets/css/admin.css', array(), AGRIPUMP_LEDGER_VERSION);
            wp_enqueue_script('agripump-admin-script', AGRIPUMP_LEDGER_PLUGIN_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-datepicker'), AGRIPUMP_LEDGER_VERSION, true);
            wp_localize_script('agripump-admin-script', 'agripump_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('agripump_nonce'),
                'strings' => array(
                    'confirm_delete' => __('Are you sure you want to delete this item?', 'agripump-ledger'),
                    'saving' => __('Saving...', 'agripump-ledger'),
                    'saved' => __('Saved successfully!', 'agripump-ledger'),
                    'error' => __('An error occurred. Please try again.', 'agripump-ledger'),
                    'season_name' => __('Season Name', 'agripump-ledger'),
                    'land_amount' => __('Land Amount', 'agripump-ledger'),
                    'due_amount' => __('Due Amount', 'agripump-ledger'),
                    'total' => __('Total', 'agripump-ledger'),
                    'loading' => __('Loading...', 'agripump-ledger'),
                    'no_ledger' => __('No ledger found', 'agripump-ledger'),
                    'add_at_least_one_item' => __('Please add at least one item', 'agripump-ledger'),
                )
            ));
        }
    }
    
    public function dashboard_page() {
        include AGRIPUMP_LEDGER_PLUGIN_PATH . 'includes/pages/dashboard.php';
    }
    
    public function locations_page() {
        include AGRIPUMP_LEDGER_PLUGIN_PATH . 'includes/pages/locations.php';
    }
    
    public function seasons_page() {
        include AGRIPUMP_LEDGER_PLUGIN_PATH . 'includes/pages/seasons.php';
    }
    
    public function customers_page() {
        include AGRIPUMP_LEDGER_PLUGIN_PATH . 'includes/pages/customers.php';
    }
    
    public function due_collection_page() {
        include AGRIPUMP_LEDGER_PLUGIN_PATH . 'includes/pages/due-collection.php';
    }
    
    public function payment_reports_page() {
        include AGRIPUMP_LEDGER_PLUGIN_PATH . 'includes/pages/payment-reports.php';
    }
    
    public function save_bill() {
        check_ajax_referer('agripump_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'agripump-ledger'));
        }
        
        $customer_id = intval($_POST['customer_id']);
        $bill_date = sanitize_text_field($_POST['bill_date']);
        $bill_items = $_POST['bill_items'];
        
        // Debug logging
        error_log('AgriPump Bill Save Debug:');
        error_log('Customer ID: ' . $customer_id);
        error_log('Bill Date: ' . $bill_date);
        error_log('Bill Items (raw): ' . print_r($bill_items, true));
        error_log('Bill Items type: ' . gettype($bill_items));
        error_log('Bill Items count: ' . (is_array($bill_items) ? count($bill_items) : 'N/A'));
        
        if (empty($customer_id) || empty($bill_date) || empty($bill_items)) {
            wp_send_json_error(__('Required fields are missing.', 'agripump-ledger'));
        }
        
        // Capture season information at creation time to preserve historical data
        foreach ($bill_items as &$item) {
            if (isset($item['season_id']) && !empty($item['season_id'])) {
                $season = get_post($item['season_id']);
                if ($season) {
                    $item['season_name'] = $season->post_title;
                    $item['season_price'] = get_post_meta($season->ID, 'price', true);
                } else {
                    $item['season_name'] = 'Unknown Season';
                    $item['season_price'] = 0;
                }
            } else {
                $item['season_name'] = 'Unknown Season';
                $item['season_price'] = 0;
            }
        }
        
        // Create bill post
        $bill_data = array(
            'post_title' => sprintf(__('Bill for Customer #%d - %s', 'agripump-ledger'), $customer_id, $bill_date),
            'post_type' => 'agripump_bill',
            'post_status' => 'publish',
            'meta_input' => array(
                'customer_id' => $customer_id,
                'bill_date' => $bill_date,
                'bill_items' => $bill_items,
                'total_amount' => array_sum(array_column($bill_items, 'amount')),
            )
        );
        
        $bill_id = wp_insert_post($bill_data);
        
        if (is_wp_error($bill_id)) {
            wp_send_json_error(__('Failed to save bill.', 'agripump-ledger'));
        }
        
        wp_send_json_success(array(
            'message' => __('Bill saved successfully!', 'agripump-ledger'),
            'bill_id' => $bill_id
        ));
    }
    
    public function save_location() {
        check_ajax_referer('agripump_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'agripump-ledger'));
        }
        
        $location_name = sanitize_text_field($_POST['location_name']);
        $post_office = sanitize_text_field($_POST['post_office']);
        $thana = sanitize_text_field($_POST['thana']);
        $district = sanitize_text_field($_POST['district']);
        $location_id = intval($_POST['location_id']);
        
        if (empty($location_name)) {
            wp_send_json_error(__('Location name is required.', 'agripump-ledger'));
        }
        
        $location_data = array(
            'post_title' => $location_name,
            'post_type' => 'agripump_location',
            'post_status' => 'publish',
            'meta_input' => array(
                'post_office' => $post_office,
                'thana' => $thana,
                'district' => $district,
            )
        );
        
        if ($location_id > 0) {
            $location_data['ID'] = $location_id;
            $result = wp_update_post($location_data);
        } else {
            $result = wp_insert_post($location_data);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(__('Failed to save location.', 'agripump-ledger'));
        }
        
        wp_send_json_success(array(
            'message' => __('Location saved successfully!', 'agripump-ledger'),
            'location_id' => $result
        ));
    }
    
    public function save_season() {
        check_ajax_referer('agripump_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'agripump-ledger'));
        }
        
        $season_name = sanitize_text_field($_POST['season_name']);
        $price = floatval($_POST['price']);
        $season_id = intval($_POST['season_id']);
        
        if (empty($season_name) || $price <= 0) {
            wp_send_json_error(__('Season name and price are required.', 'agripump-ledger'));
        }
        
        $season_data = array(
            'post_title' => $season_name,
            'post_type' => 'agripump_season',
            'post_status' => 'publish',
            'meta_input' => array(
                'price' => $price,
            )
        );
        
        if ($season_id > 0) {
            $season_data['ID'] = $season_id;
            $result = wp_update_post($season_data);
        } else {
            $result = wp_insert_post($season_data);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(__('Failed to save season.', 'agripump-ledger'));
        }
        
        wp_send_json_success(array(
            'message' => __('Season saved successfully!', 'agripump-ledger'),
            'season_id' => $result
        ));
    }
    
    public function save_customer() {
        check_ajax_referer('agripump_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'agripump-ledger'));
        }
        
        // Debug logging
        error_log('AgriPump Customer Save Debug:');
        error_log('POST data: ' . print_r($_POST, true));
        
        $customer_name = sanitize_text_field($_POST['customer_name']);
        $father_name = sanitize_text_field($_POST['father_name']);
        $location_id = intval($_POST['location_id']);
        $mobile = sanitize_text_field($_POST['mobile']);
        $customer_id = intval($_POST['customer_id']);
        
        error_log('Customer ID: ' . $customer_id);
        error_log('Customer Name: ' . $customer_name);
        
        if (empty($customer_name) || $location_id <= 0) {
            wp_send_json_error(__('Customer name and location are required.', 'agripump-ledger'));
        }
        
        $customer_data = array(
            'post_title' => $customer_name,
            'post_type' => 'agripump_customer',
            'post_status' => 'publish',
            'meta_input' => array(
                'father_name' => $father_name,
                'location_id' => $location_id,
                'mobile' => $mobile,
            )
        );
        
        if ($customer_id > 0) {
            $customer_data['ID'] = $customer_id;
            error_log('Updating existing customer with ID: ' . $customer_id);
            $result = wp_update_post($customer_data);
        } else {
            error_log('Creating new customer (no customer_id provided)');
            $result = wp_insert_post($customer_data);
        }
        
        if (is_wp_error($result)) {
            error_log('Error saving customer: ' . $result->get_error_message());
            wp_send_json_error(__('Failed to save customer.', 'agripump-ledger'));
        }
        
        error_log('Customer saved successfully with ID: ' . $result);
        wp_send_json_success(array(
            'message' => __('Customer saved successfully!', 'agripump-ledger'),
            'customer_id' => $result
        ));
    }
    
    public function get_item() {
        check_ajax_referer('agripump_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'agripump-ledger'));
        }
        
        $item_id = intval($_GET['item_id']);
        $item_type = sanitize_text_field($_GET['item_type']);
        
        if (empty($item_id) || empty($item_type)) {
            wp_send_json_error(__('Item ID and type are required.', 'agripump-ledger'));
        }
        
        $item = get_post($item_id);
        
        if (!$item) {
            wp_send_json_error(__('Item not found.', 'agripump-ledger'));
        }
        
        $data = array(
            'ID' => $item->ID,
            'post_title' => $item->post_title,
        );
        
        // Add meta data based on type
        if ($item_type === 'location') {
            $data['post_office'] = get_post_meta($item->ID, 'post_office', true);
            $data['thana'] = get_post_meta($item->ID, 'thana', true);
            $data['district'] = get_post_meta($item->ID, 'district', true);
        } elseif ($item_type === 'season') {
            $data['price'] = get_post_meta($item->ID, 'price', true);
        } elseif ($item_type === 'customer') {
            $data['father_name'] = get_post_meta($item->ID, 'father_name', true);
            $data['location_id'] = get_post_meta($item->ID, 'location_id', true);
            $data['mobile'] = get_post_meta($item->ID, 'mobile', true);
        }
        
        wp_send_json_success($data);
    }
    
    public function get_customer_ledger() {
        check_ajax_referer('agripump_nonce', 'nonce');
        
        // Debug logging
        error_log('AgriPump Ledger - get_customer_ledger called');
        error_log('POST data: ' . print_r($_POST, true));
        
        $customer_id = intval($_POST['customer_id']);
        
        error_log('Customer ID: ' . $customer_id);
        
        if (empty($customer_id)) {
            wp_send_json_error(__('Customer ID is required.', 'agripump-ledger'));
        }
        
        $bills = get_posts(array(
            'post_type' => 'agripump_bill',
            'meta_query' => array(
                array(
                    'key' => 'customer_id',
                    'value' => $customer_id,
                    'compare' => '='
                )
            ),
            'numberposts' => -1,
            'orderby' => 'meta_value',
            'meta_key' => 'bill_date',
            'order' => 'ASC'
        ));
        
        $ledger_data = array();
        foreach ($bills as $bill) {
            $bill_items = get_post_meta($bill->ID, 'bill_items', true);
            $bill_date = get_post_meta($bill->ID, 'bill_date', true);
            $total_amount = get_post_meta($bill->ID, 'total_amount', true);
            
            // Debug: Log the raw bill items
            error_log('Bill ID: ' . $bill->ID . ' - Raw bill_items: ' . print_r($bill_items, true));
            error_log('Bill items type: ' . gettype($bill_items));
            error_log('Bill items count: ' . (is_array($bill_items) ? count($bill_items) : 'N/A'));
            
            // Ensure bill_items is an array
            if (!is_array($bill_items)) {
                $bill_items = array();
            }
            
            // Use stored season information (preserves historical data)
            foreach ($bill_items as &$item) {
                error_log('Processing item: ' . print_r($item, true));
                
                // Use stored season name and price if available (for historical accuracy)
                if (isset($item['season_name']) && !empty($item['season_name'])) {
                    // Use the stored season name from when the bill was created
                    error_log('Using stored season name: ' . $item['season_name']);
                } else if (isset($item['season_id']) && !empty($item['season_id'])) {
                    // Fallback to current season data if stored data not available
                    $season = get_post($item['season_id']);
                    $item['season_name'] = $season ? $season->post_title : 'Unknown Season';
                    error_log('Season found: ' . $item['season_name']);
                } else {
                    $item['season_name'] = 'Unknown Season';
                    error_log('No season_id found, using Unknown Season');
                }
                
                // Ensure all required fields exist
                if (!isset($item['land'])) $item['land'] = 0;
                if (!isset($item['amount'])) $item['amount'] = 0;
            }
            
            // Get paid amount for this bill
            $paid_amount = floatval(get_post_meta($bill->ID, 'paid_amount', true));
            
            // Get season-specific payments
            $season_payments_key = 'season_payments_' . $bill->ID;
            $season_payments = get_post_meta($bill->ID, $season_payments_key, true);
            if (!is_array($season_payments)) {
                $season_payments = array();
            }
            
            $ledger_data[] = array(
                'bill_id' => $bill->ID,
                'date' => $bill_date,
                'items' => $bill_items,
                'total' => $total_amount,
                'paid_amount' => $paid_amount,
                'season_payments' => $season_payments
            );
        }
        
        error_log('Final ledger data: ' . print_r($ledger_data, true));
        wp_send_json_success($ledger_data);
    }
    
    public function debug_bill() {
        check_ajax_referer('agripump_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'agripump-ledger'));
        }
        
        $customer_id = intval($_POST['customer_id']);
        
        if (empty($customer_id)) {
            wp_send_json_error(__('Customer ID is required.', 'agripump-ledger'));
        }
        
        $bills = get_posts(array(
            'post_type' => 'agripump_bill',
            'meta_query' => array(
                array(
                    'key' => 'customer_id',
                    'value' => $customer_id,
                    'compare' => '='
                )
            ),
            'numberposts' => -1,
            'orderby' => 'meta_value',
            'meta_key' => 'bill_date',
            'order' => 'ASC'
        ));
        
        $debug_data = array();
        foreach ($bills as $bill) {
            $bill_items = get_post_meta($bill->ID, 'bill_items', true);
            $bill_date = get_post_meta($bill->ID, 'bill_date', true);
            $total_amount = get_post_meta($bill->ID, 'total_amount', true);
            
            $debug_data[] = array(
                'bill_id' => $bill->ID,
                'bill_title' => $bill->post_title,
                'bill_date' => $bill_date,
                'total_amount' => $total_amount,
                'raw_bill_items' => $bill_items,
                'bill_items_type' => gettype($bill_items),
                'bill_items_count' => is_array($bill_items) ? count($bill_items) : 'N/A'
            );
        }
        
        wp_send_json_success($debug_data);
    }
    
    public function save_payment() {
        check_ajax_referer('agripump_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to perform this action.', 'agripump-ledger'));
        }
        
        $customer_id = intval($_POST['customer_id']);
        $season_id = intval($_POST['season_id']);
        $bill_id = intval($_POST['bill_id']);
        $payment_amount = floatval($_POST['payment_amount']);
        $payment_date = sanitize_text_field($_POST['payment_date']);
        $payment_notes = sanitize_textarea_field($_POST['payment_notes']);
        
        if (empty($customer_id) || empty($season_id) || empty($bill_id) || $payment_amount <= 0) {
            wp_send_json_error(__('Invalid customer ID, season ID, bill ID, or payment amount.', 'agripump-ledger'));
        }
        
        // Get customer details
        $customer = get_post($customer_id);
        if (!$customer || $customer->post_type !== 'agripump_customer') {
            wp_send_json_error(__('Customer not found.', 'agripump-ledger'));
        }
        
        // Get the specific bill
        $bill = get_post($bill_id);
        if (!$bill || $bill->post_type !== 'agripump_bill') {
            wp_send_json_error(__('Bill not found.', 'agripump-ledger'));
        }
        
        // Verify the bill belongs to the customer
        $bill_customer_id = get_post_meta($bill_id, 'customer_id', true);
        if ($bill_customer_id != $customer_id) {
            wp_send_json_error(__('Bill does not belong to this customer.', 'agripump-ledger'));
        }
        
        // Get bill items
        $bill_items = get_post_meta($bill_id, 'bill_items', true);
        if (!is_array($bill_items)) {
            wp_send_json_error(__('Invalid bill structure.', 'agripump-ledger'));
        }
        
        // Find the specific season item
        $season_item = null;
        foreach ($bill_items as $item) {
            if (isset($item['season_id']) && intval($item['season_id']) == $season_id) {
                $season_item = $item;
                break;
            }
        }
        
        if (!$season_item) {
            wp_send_json_error(__('Season not found in this bill.', 'agripump-ledger'));
        }
        
        // Get season-specific payment tracking
        $season_payments_key = 'season_payments_' . $bill_id;
        $season_payments = get_post_meta($bill_id, $season_payments_key, true);
        if (!is_array($season_payments)) {
            $season_payments = array();
        }
        
        // Calculate season-specific remaining amount
        $season_amount = floatval($season_item['amount']);
        $season_paid = isset($season_payments[$season_id]) ? floatval($season_payments[$season_id]) : 0;
        $season_remaining = $season_amount - $season_paid;
        
        if ($payment_amount > $season_remaining) {
            wp_send_json_error(__('Payment amount exceeds the remaining amount for this season.', 'agripump-ledger'));
        }
        
        // Update season-specific payment
        $season_payments[$season_id] = $season_paid + $payment_amount;
        update_post_meta($bill_id, $season_payments_key, $season_payments);
        
        // Update total bill paid amount
        $current_paid_amount = floatval(get_post_meta($bill_id, 'paid_amount', true));
        $new_paid_amount = $current_paid_amount + $payment_amount;
        update_post_meta($bill_id, 'paid_amount', $new_paid_amount);
        
        $payment_records = array(
            array(
                'bill_id' => $bill_id,
                'season_id' => $season_id,
                'season_name' => get_post($season_id) ? get_post($season_id)->post_title : 'Unknown Season',
                'bill_date' => get_post_meta($bill_id, 'bill_date', true),
                'payment_amount' => $payment_amount,
                'season_remaining' => $season_remaining - $payment_amount,
                'bill_remaining' => $total_bill_amount - $new_paid_amount,
                'season_total_paid' => $season_paid + $payment_amount
            )
        );
        
        // Create payment record
        $payment_post = array(
            'post_title' => 'Payment - ' . $customer->post_title . ' - ' . $payment_date,
            'post_type' => 'agripump_payment',
            'post_status' => 'publish',
            'meta_input' => array(
                'customer_id' => $customer_id,
                'season_id' => $season_id,
                'bill_id' => $bill_id,
                'payment_amount' => $payment_amount,
                'payment_date' => $payment_date,
                'payment_notes' => $payment_notes,
                'payment_records' => $payment_records,
                'created_by' => get_current_user_id(),
                'created_at' => current_time('mysql')
            )
        );
        
        $payment_id = wp_insert_post($payment_post);
        
        if ($payment_id) {
            wp_send_json_success(array(
                'message' => __('Payment saved successfully.', 'agripump-ledger'),
                'payment_id' => $payment_id,
                'payment_records' => $payment_records
            ));
        } else {
            wp_send_json_error(__('Error saving payment.', 'agripump-ledger'));
        }
    }
    
    public function activate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin
new AgriPumpLedger(); 