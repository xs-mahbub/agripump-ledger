# AgriPump Ledger - WordPress Plugin

A comprehensive CRM plugin for managing agricultural pump customers, locations, seasons, and due collections.

## Features

### 🏠 Dashboard
- Overview statistics (total locations, seasons, customers, bills, revenue)
- Quick action buttons
- Recent activity feed

### 📍 Location Management
- Add/edit/delete locations
- Fields: Location Name, Post Office, Thana, District
- View customers by location
- Search functionality

### 🌾 Season Management
- Add/edit/delete seasons
- Fields: Season Name, Price per Unit
- Used for bill calculations

### 👥 Customer Management
- Add/edit/delete customers
- Fields: Customer Name, Father Name, Address (Location), Mobile Number
- Individual customer ledger view
- Bill management per customer

### 💰 Bill Management
- Add multiple season items per bill
- Automatic calculation (Season Price × Land Amount = Total)
- Date picker for bill dates
- Customer-specific bill history

### 📊 Due Collection
- Outstanding amount tracking
- Location-wise summary
- Customer-wise details
- Export to CSV functionality
- Print reports

## Installation

1. Upload the `agripump-ledger` folder to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Access the plugin from the 'AgriPump Ledger' menu in the admin dashboard

## Usage

### Setting Up Locations
1. Go to AgriPump Ledger → Locations
2. Add location details (name, post office, thana, district)
3. Use "View Customers" to see customers in specific locations

### Setting Up Seasons
1. Go to AgriPump Ledger → Seasons
2. Add season name and price per unit
3. These will be used for bill calculations

### Managing Customers
1. Go to AgriPump Ledger → Customers
2. Add customer information
3. Click "Manage Bills" to add bills for specific customers

### Creating Bills
1. From customer management, click "Manage Bills"
2. Select bill date
3. Add season items with land amounts
4. System automatically calculates totals
5. Save bill to update customer ledger

### Tracking Collections
1. Go to AgriPump Ledger → Due Collection
2. View outstanding amounts by location and customer
3. Export reports or print for collection purposes

## Technical Details

### Custom Post Types
- `agripump_location` - Stores location information
- `agripump_season` - Stores season and pricing data
- `agripump_customer` - Stores customer information
- `agripump_bill` - Stores bill and ledger data

### Database Structure
Uses WordPress native post and postmeta tables:
- Locations: post_title, post_office, thana, district
- Seasons: post_title, price
- Customers: post_title, father_name, location_id, mobile
- Bills: post_title, customer_id, bill_date, bill_items, total_amount

### Features
- Mobile responsive design
- AJAX-powered interactions
- Search functionality
- Export capabilities
- Print-friendly reports
- Translation ready

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## Support

For support and feature requests, please contact the development team.

## Changelog

### Version 1.0.0
- Initial release
- Complete CRM functionality
- Mobile responsive design
- Export and print features 