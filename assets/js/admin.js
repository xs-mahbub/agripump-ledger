jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize date pickers
    $('.agripump-datepicker').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showAnim: 'fadeIn',
        duration: 200,
        beforeShow: function(input, inst) {
            // Ensure the datepicker appears above other elements
            setTimeout(function() {
                inst.dpDiv.css({
                    'z-index': 10000,
                    'position': 'absolute'
                });
            }, 0);
        }
    });
    
    // Location management
    $('.agripump-location-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();
        
        submitBtn.text(agripump_ajax.strings.saving).prop('disabled', true);
        
        $.ajax({
            url: agripump_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'agripump_save_location',
                nonce: agripump_ajax.nonce,
                location_name: form.find('#location_name').val(),
                post_office: form.find('#post_office').val(),
                thana: form.find('#thana').val(),
                district: form.find('#district').val(),
                location_id: form.find('#location_id').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(agripump_ajax.strings.saved);
                    location.reload();
                } else {
                    alert(response.data || agripump_ajax.strings.error);
                }
            },
            error: function() {
                alert(agripump_ajax.strings.error);
            },
            complete: function() {
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Season management
    $('.agripump-season-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();
        
        submitBtn.text(agripump_ajax.strings.saving).prop('disabled', true);
        
        $.ajax({
            url: agripump_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'agripump_save_season',
                nonce: agripump_ajax.nonce,
                season_name: form.find('#season_name').val(),
                price: form.find('#price').val(),
                season_id: form.find('#season_id').val()
            },
            success: function(response) {
                if (response.success) {
                    alert(agripump_ajax.strings.saved);
                    location.reload();
                } else {
                    alert(response.data || agripump_ajax.strings.error);
                }
            },
            error: function() {
                alert(agripump_ajax.strings.error);
            },
            complete: function() {
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Customer management
    $('.agripump-customer-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();
        
        // Debug logging
        console.log('Customer form submitted');
        console.log('Customer ID:', form.find('#customer_id').val());
        console.log('Customer Name:', form.find('#customer_name').val());
        console.log('Form data:', {
            action: 'agripump_save_customer',
            nonce: agripump_ajax.nonce,
            customer_name: form.find('#customer_name').val(),
            father_name: form.find('#father_name').val(),
            location_id: form.find('#location_id').val(),
            mobile: form.find('#mobile').val(),
            customer_id: form.find('#customer_id').val()
        });
        
        submitBtn.text(agripump_ajax.strings.saving).prop('disabled', true);
        
        $.ajax({
            url: agripump_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'agripump_save_customer',
                nonce: agripump_ajax.nonce,
                customer_name: form.find('#customer_name').val(),
                father_name: form.find('#father_name').val(),
                location_id: form.find('#location_id').val(),
                mobile: form.find('#mobile').val(),
                customer_id: form.find('#customer_id').val()
            },
            success: function(response) {
                console.log('AJAX Response:', response);
                if (response.success) {
                    alert(agripump_ajax.strings.saved);
                    location.reload();
                } else {
                    alert(response.data || agripump_ajax.strings.error);
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX Error:', xhr, status, error);
                alert(agripump_ajax.strings.error);
            },
            complete: function() {
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Bill management
    var billItemCount = 0;
    
    function addBillItem() {
        billItemCount++;
        var itemHtml = `
            <div class="agripump-bill-item" data-item="${billItemCount}">
                <div class="agripump-form-group">
                    <label>${agripump_ajax.strings.season || 'Season'}</label>
                    <select class="agripump-form-control season-select" name="bill_items[${billItemCount}][season_id]" required>
                        <option value="">${agripump_ajax.strings.select_season || 'Select Season'}</option>
                        ${getSeasonOptions()}
                    </select>
                </div>
                <div class="agripump-form-group">
                    <label>${agripump_ajax.strings.land || 'Land'}</label>
                    <div class="land-inputs-container">
                        <div class="land-input-row">
                            <input type="number" class="agripump-form-control land-input" name="bill_items[${billItemCount}][land_amounts][]" step="0.01" placeholder="Land amount" required>
                            <button type="button" class="add-land-btn agripump-btn agripump-btn-sm agripump-btn-success">+</button>
                        </div>
                    </div>
                    <input type="hidden" class="total-land-input" name="bill_items[${billItemCount}][land]" value="">
                    <input type="hidden" class="land-display-input" name="bill_items[${billItemCount}][land_display]" value="">
                </div>
                <div class="agripump-form-group">
                    <label>${agripump_ajax.strings.amount || 'Amount'}</label>
                    <input type="number" class="agripump-form-control amount-input" name="bill_items[${billItemCount}][amount]" step="0.01" readonly>
                </div>
                <button type="button" class="remove-item">${agripump_ajax.strings.remove || 'Remove'}</button>
            </div>
        `;
        $('.agripump-bill-items').append(itemHtml);
    }
    
    function getSeasonOptions() {
        var options = '';
        if (typeof agripump_seasons !== 'undefined') {
            agripump_seasons.forEach(function(season) {
                options += `<option value="${season.ID}" data-price="${season.price}">${season.post_title} (${parseFloat(season.price).toFixed(2)})</option>`;
            });
        }
        return options;
    }
    
    $('.add-bill-item').on('click', function() {
        addBillItem();
    });
    
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.agripump-bill-item').remove();
        calculateTotal();
    });
    
    $(document).on('change', '.season-select', function() {
        updateLandCalculation($(this).closest('.agripump-bill-item'));
    });
    
    // Add land input button
    $(document).on('click', '.add-land-btn', function() {
        var container = $(this).closest('.land-inputs-container');
        var newRow = `
            <div class="land-input-row">
                <input type="number" class="agripump-form-control land-input" name="bill_items[${$(this).closest('.agripump-bill-item').data('item')}][land_amounts][]" step="0.01" placeholder="Land amount" required>
                <button type="button" class="remove-land-btn agripump-btn agripump-btn-sm agripump-btn-danger">-</button>
            </div>
        `;
        container.append(newRow);
        updateLandCalculation($(this).closest('.agripump-bill-item'));
    });
    
    // Remove land input button
    $(document).on('click', '.remove-land-btn', function() {
        $(this).closest('.land-input-row').remove();
        updateLandCalculation($(this).closest('.agripump-bill-item'));
    });
    
    // Update land calculation when any land input changes
    $(document).on('input', '.land-input', function() {
        updateLandCalculation($(this).closest('.agripump-bill-item'));
    });
    
    function updateLandCalculation(item) {
        var totalLand = 0;
        var landAmounts = [];
        
        // Calculate total land and collect individual amounts
        item.find('.land-input').each(function() {
            var landValue = parseFloat($(this).val()) || 0;
            if (landValue > 0) {
                totalLand += landValue;
                landAmounts.push(landValue);
            }
        });
        
        // Update hidden inputs
        item.find('.total-land-input').val(totalLand);
        item.find('.land-display-input').val(landAmounts.join('+'));
        
        // Update amount calculation
        var seasonSelect = item.find('.season-select');
        var amountInput = item.find('.amount-input');
        var selectedOption = seasonSelect.find('option:selected');
        var price = parseFloat(selectedOption.data('price')) || 0;
        
        amountInput.val((price * totalLand).toFixed(2));
        calculateTotal();
    }
    
    function calculateTotal() {
        var total = 0;
        $('.amount-input').each(function() {
            total += parseFloat($(this).val()) || 0;
        });
        $('.agripump-total').text(agripump_ajax.strings.total + ': ' + total.toFixed(2));
    }
    
    // Calculate total paid amount for display
    function calculateTotalPaid(ledgerData) {
        var totalPaid = 0;
        if (ledgerData && ledgerData.length > 0) {
            ledgerData.forEach(function(bill) {
                // Use item-specific payments first (new system)
                if (bill.item_payments) {
                    Object.values(bill.item_payments).forEach(function(amount) {
                        totalPaid += parseFloat(amount) || 0;
                    });
                }
                // Fallback to season payments for backward compatibility (old system)
                else if (bill.season_payments) {
                    Object.values(bill.season_payments).forEach(function(amount) {
                        totalPaid += parseFloat(amount) || 0;
                    });
                }
            });
        }
        return totalPaid;
    }
    
    // Calculate total due amount for display
    function calculateTotalDue(ledgerData) {
        var totalDue = 0;
        if (ledgerData && ledgerData.length > 0) {
            ledgerData.forEach(function(bill) {
                totalDue += parseFloat(bill.total || 0);
            });
        }
        return totalDue;
    }
    
    // Calculate total remaining amount for display
    function calculateTotalRemaining(ledgerData) {
        var totalDue = calculateTotalDue(ledgerData);
        var totalPaid = calculateTotalPaid(ledgerData);
        return totalDue - totalPaid;
    }
    
    $('.agripump-bill-form').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.text();
        
        // Validate form
        if ($('.agripump-bill-item').length === 0) {
            alert(agripump_ajax.strings.add_at_least_one_item || 'Please add at least one item');
            return;
        }
        
        submitBtn.text(agripump_ajax.strings.saving).prop('disabled', true);
        
        var formData = new FormData(form[0]);
        formData.append('action', 'agripump_save_bill');
        formData.append('nonce', agripump_ajax.nonce);
        
        // Debug logging
        console.log('Bill form submitted');
        console.log('Form data entries:');
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        $.ajax({
            url: agripump_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert(agripump_ajax.strings.saved);
                    loadCustomerLedger();
                    form[0].reset();
                    $('.agripump-bill-items').empty();
                    billItemCount = 0; // Reset the bill item counter
                    $('.agripump-total').text(agripump_ajax.strings.total + ': 0.00');
                } else {
                    alert(response.data || agripump_ajax.strings.error);
                }
            },
            error: function() {
                alert(agripump_ajax.strings.error);
            },
            complete: function() {
                submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
    
    // Load customer ledger
    function loadCustomerLedger() {
        var customerId = $('.agripump-bill-form').data('customer-id');
        console.log('Loading ledger for customer ID:', customerId);
        
        if (!customerId) {
            console.log('No customer ID found');
            return;
        }
        
        $('.agripump-ledger').html('<div class="agripump-loading">' + (agripump_ajax.strings.loading || 'Loading...') + '</div>');
        
        $.ajax({
            url: agripump_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'agripump_get_customer_ledger',
                nonce: agripump_ajax.nonce,
                customer_id: customerId
            },
            success: function(response) {
                console.log('AJAX response:', response);
                if (response.success) {
                    displayLedger(response.data);
                } else {
                    console.log('AJAX error:', response.data);
                    $('.agripump-ledger').html('<div class="agripump-empty-state"><h3>' + (response.data || agripump_ajax.strings.no_ledger || 'No ledger found') + '</h3></div>');
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', xhr, status, error);
                $('.agripump-ledger').html('<div class="agripump-empty-state"><h3>' + agripump_ajax.strings.error + '</h3></div>');
            }
        });
    }
    
    function displayLedger(ledgerData) {
        console.log('Ledger data received:', ledgerData);
        
        if (!ledgerData || ledgerData.length === 0) {
            $('.agripump-ledger').html('<div class="agripump-empty-state"><h3>' + (agripump_ajax.strings.no_ledger || 'No ledger found') + '</h3></div>');
            return;
        }
        
        var ledgerHtml = '';
        var totalPaid = calculateTotalPaid(ledgerData);
        var totalDue = calculateTotalDue(ledgerData);
        var totalRemaining = calculateTotalRemaining(ledgerData);
        
        ledgerHtml += `<div class="agripump-ledger-summary">
            <strong>Total Due: ৳${totalRemaining.toFixed(2)} | Total Paid: ৳${totalPaid.toFixed(2)}</strong>
        </div>`;
        
        ledgerData.forEach(function(bill) {
            console.log('Processing bill:', bill);
            
            // Check if bill has items before creating the table structure
            var hasItems = bill.items && typeof bill.items === 'object' && Object.keys(bill.items).length > 0;
            
            // Calculate the sum of remaining amounts for this bill
            var billRemainingTotal = 0;
            if (hasItems) {
                var itemsArray = Array.isArray(bill.items) ? bill.items : Object.values(bill.items);
                itemsArray.forEach(function(item, itemIndex) {
                    var originalIndex = (item && typeof item.original_index !== 'undefined') ? item.original_index : itemIndex;
                    var itemPaid = 0;
                    var itemPaymentKey = item.season_id + '_' + originalIndex;
                    
                    // Check item-specific payments first (new system)
                    if (bill.item_payments && bill.item_payments[itemPaymentKey]) {
                        itemPaid = parseFloat(bill.item_payments[itemPaymentKey]);
                    }
                    // Fallback to season payments for backward compatibility (old system)
                    else if (bill.season_payments && bill.season_payments[item.season_id]) {
                        itemPaid = parseFloat(bill.season_payments[item.season_id]);
                    }
                    
                    var itemRemaining = parseFloat(item.amount || 0) - itemPaid;
                    billRemainingTotal += itemRemaining;
                });
            }
            
            // Only create the ledger item if there are items to display
            if (hasItems) {
                ledgerHtml += `
                    <div class="agripump-ledger-item">
                        <div class="agripump-ledger-header">
                            <span class="agripump-ledger-date">${bill.date || 'No date'}</span>
                            <span class="agripump-ledger-total">${agripump_ajax.strings.total || 'Total'}: ${billRemainingTotal.toFixed(2)}</span>
                        </div>
                        <div class="agripump-ledger-items">
                            <div class="agripump-ledger-table-header">
                                <div class="agripump-ledger-header-cell">${agripump_ajax.strings.season_name || 'Season Name'}</div>
                                <div class="agripump-ledger-header-cell">${agripump_ajax.strings.land_amount || 'Land Amount'}</div>
                                <div class="agripump-ledger-header-cell">${agripump_ajax.strings.due_amount || 'Due Amount'}</div>
                                <div class="agripump-ledger-header-cell">Paid Amount</div>
                                <div class="agripump-ledger-header-cell">Remaining</div>
                                <div class="agripump-ledger-header-cell">Actions</div>
                            </div>
                `;
                
                // Convert object to array if needed
                var itemsArray = Array.isArray(bill.items) ? bill.items : Object.values(bill.items);
                
                itemsArray.forEach(function(item, itemIndex) {
                    console.log('Processing item:', item);
                    
                    // Calculate item-specific paid amount
                    var originalIndex = (item && typeof item.original_index !== 'undefined') ? item.original_index : itemIndex;
                    var itemPaid = 0;
                    var itemPaymentKey = item.season_id + '_' + originalIndex;
                    
                    // Check item-specific payments first (new system)
                    if (bill.item_payments && bill.item_payments[itemPaymentKey]) {
                        itemPaid = parseFloat(bill.item_payments[itemPaymentKey]);
                    }
                    // Fallback to season payments for backward compatibility (old system)
                    else if (bill.season_payments && bill.season_payments[item.season_id]) {
                        itemPaid = parseFloat(bill.season_payments[item.season_id]);
                    }
                    
                    var itemRemaining = parseFloat(item.amount || 0) - itemPaid;
                    
                    // Display land amount with plus format if available, otherwise show total land
                    var landDisplay = item.land_display || item.land || '0';
                    
                    ledgerHtml += `
                        <div class="agripump-ledger-item-row">
                            <div class="agripump-ledger-item-label">${item.season_name || 'Unknown Season'}</div>
                            <div class="agripump-ledger-item-label">${landDisplay}</div>
                            <div class="agripump-ledger-item-label">${parseFloat(item.amount || 0).toFixed(2)}</div>
                            <div class="agripump-ledger-item-label">${itemPaid.toFixed(2)}</div>
                            <div class="agripump-ledger-item-label">${itemRemaining.toFixed(2)}</div>
                            <div class="agripump-ledger-item-label">
                                <button class="edit-season-item-btn agripump-btn agripump-btn-sm agripump-btn-secondary" 
                                        data-bill-id="${bill.bill_id}" 
                                        data-item-index="${originalIndex}" 
                                        data-season-id="${item.season_id}" 
                                        data-season-name="${item.season_name || 'Unknown Season'}" 
                                        data-land="${item.land || '0'}" 
                                        data-land-display="${landDisplay}"
                                        data-amount="${parseFloat(item.amount || 0).toFixed(2)}">Edit</button>
                                <button class="delete-season-item-btn agripump-btn agripump-btn-sm agripump-btn-danger" 
                                        data-bill-id="${bill.bill_id}" 
                                        data-item-index="${originalIndex}" 
                                        data-season-name="${item.season_name || 'Unknown Season'}">Delete</button>
                            </div>
                        </div>
                    `;
                });
                
                ledgerHtml += `
                        </div>
                    </div>
                `;
            } else {
                console.log('No items found for bill:', bill);
                // Don't create any table structure for bills with no items
            }
        });
        
        $('.agripump-ledger').html(ledgerHtml);
    }
    
    // Initialize ledger on page load
    if ($('.agripump-bill-form').length > 0) {
        loadCustomerLedger();
    }
    
    // Debug bill data
    $('.debug-bill-btn').on('click', function() {
        var customerId = $('.agripump-bill-form').data('customer-id');
        console.log('Debugging bills for customer ID:', customerId);
        
        $.ajax({
            url: agripump_ajax.ajax_url,
            type: 'GET',
            data: {
                action: 'agripump_debug_bill',
                nonce: agripump_ajax.nonce,
                customer_id: customerId
            },
            success: function(response) {
                console.log('Debug response:', response);
                if (response.success) {
                    alert('Check console for debug data. Found ' + response.data.length + ' bills.');
                } else {
                    alert('Debug failed: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('Debug error:', xhr, status, error);
                alert('Debug error occurred');
            }
        });
    });
    
    // Search functionality
    $('.agripump-search-input').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        var table = $(this).closest('.agripump-card').find('table');
        
        table.find('tbody tr').each(function() {
            var text = $(this).text().toLowerCase();
            if (text.indexOf(searchTerm) > -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Delete confirmations
    $('.agripump-delete-btn').on('click', function(e) {
        if (!confirm(agripump_ajax.strings.confirm_delete)) {
            e.preventDefault();
        }
    });
    
    // View customers from location
    $('.view-customers-btn').on('click', function(e) {
        e.preventDefault();
        var locationId = $(this).data('location-id');
        window.location.href = 'admin.php?page=agripump-customers&location_id=' + locationId;
    });
    
    // Edit forms
    $('.agripump-edit-btn').on('click', function(e) {
        e.preventDefault();
        var itemId = $(this).data('id');
        var itemType = $(this).data('type');
        
        // Load item data and populate form
        $.ajax({
            url: agripump_ajax.ajax_url,
            type: 'GET',
            data: {
                action: 'agripump_get_item',
                nonce: agripump_ajax.nonce,
                item_id: itemId,
                item_type: itemType
            },
            success: function(response) {
                if (response.success) {
                    populateEditForm(response.data, itemType);
                } else {
                    alert(response.data || agripump_ajax.strings.error);
                }
            },
            error: function() {
                alert(agripump_ajax.strings.error);
            }
        });
    });
    
    function populateEditForm(data, type) {
        var form = $('.agripump-' + type + '-form');
        
        if (type === 'location') {
            form.find('#location_id').val(data.ID);
            form.find('#location_name').val(data.post_title);
            form.find('#post_office').val(data.post_office);
            form.find('#thana').val(data.thana);
            form.find('#district').val(data.district);
        } else if (type === 'season') {
            form.find('#season_id').val(data.ID);
            form.find('#season_name').val(data.post_title);
            form.find('#price').val(data.price);
        } else if (type === 'customer') {
            form.find('#customer_id').val(data.ID);
            form.find('#customer_name').val(data.post_title);
            form.find('#father_name').val(data.father_name);
            form.find('#location_id').val(data.location_id);
            form.find('#mobile').val(data.mobile);
        }
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: form.offset().top - 100
        }, 500);
    }
    
    // Reset form
    $('.agripump-reset-btn').on('click', function(e) {
        e.preventDefault();
        var form = $(this).closest('form');
        form[0].reset();
        form.find('input[type="hidden"]').val('');
    });
    
    // Edit season ledger item
    $(document).on('click', '.edit-season-item-btn', function(e) {
        e.preventDefault();
        var button = $(this);
        var billId = button.data('bill-id');
        var itemIndex = button.data('item-index');
        var seasonId = button.data('season-id');
        var seasonName = button.data('season-name');
        var land = button.data('land');
        var landDisplay = button.data('land-display') || land;
        var amount = button.data('amount');
        
        // Parse land amounts from display format (e.g., "45+20+10" -> [45, 20, 10])
        var landAmounts = landDisplay.split('+').map(function(val) {
            return parseFloat(val.trim()) || 0;
        }).filter(function(val) {
            return val > 0;
        });
        
        // If no land amounts found, use the total land as single amount
        if (landAmounts.length === 0) {
            landAmounts = [parseFloat(land) || 0];
        }
        
        // Create land input fields HTML
        var landInputsHtml = '';
        landAmounts.forEach(function(landAmount, index) {
            if (index === 0) {
                landInputsHtml += `
                    <div class="land-input-row">
                        <input type="number" class="agripump-form-control edit-land-input" step="0.01" min="0" value="${landAmount}" required>
                        <button type="button" class="add-edit-land-btn agripump-btn agripump-btn-sm agripump-btn-success">+</button>
                    </div>
                `;
            } else {
                landInputsHtml += `
                    <div class="land-input-row">
                        <input type="number" class="agripump-form-control edit-land-input" step="0.01" min="0" value="${landAmount}" required>
                        <button type="button" class="remove-edit-land-btn agripump-btn agripump-btn-sm agripump-btn-danger">-</button>
                    </div>
                `;
            }
        });
        
        // Create edit modal
        var modalHtml = `
            <div id="edit-season-modal" class="agripump-modal">
                <div class="agripump-modal-content">
                    <div class="agripump-modal-header">
                        <h2>Edit Season Item</h2>
                        <span class="agripump-modal-close">&times;</span>
                    </div>
                    <div class="agripump-modal-body">
                        <form id="edit-season-form">
                            <div class="agripump-form-group">
                                <label for="edit-season-name">Season Name</label>
                                <input type="text" id="edit-season-name" class="agripump-form-control" value="${seasonName}" readonly>
                            </div>
                            <div class="agripump-form-group">
                                <label>Land Amounts *</label>
                                <div class="edit-land-inputs-container">
                                    ${landInputsHtml}
                                </div>
                                <small class="form-text">Add multiple land parcels using the + button</small>
                            </div>
                            <div class="agripump-form-group">
                                <label for="edit-due-amount">Due Amount *</label>
                                <input type="number" id="edit-due-amount" class="agripump-form-control" step="0.01" min="0" value="${amount}" required>
                            </div>
                            <div class="agripump-form-actions">
                                <button type="submit" class="agripump-btn agripump-btn-primary">Update</button>
                                <button type="button" class="agripump-btn agripump-btn-secondary cancel-edit">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        $('#edit-season-modal').remove();
        
        // Add modal to page
        $('body').append(modalHtml);
        $('#edit-season-modal').show();
        
        // Add land input button for edit modal
        $(document).on('click', '.add-edit-land-btn', function() {
            var container = $(this).closest('.edit-land-inputs-container');
            var newRow = `
                <div class="land-input-row">
                    <input type="number" class="agripump-form-control edit-land-input" step="0.01" min="0" required>
                    <button type="button" class="remove-edit-land-btn agripump-btn agripump-btn-sm agripump-btn-danger">-</button>
                </div>
            `;
            container.append(newRow);
        });
        
        // Remove land input button for edit modal
        $(document).on('click', '.remove-edit-land-btn', function() {
            $(this).closest('.land-input-row').remove();
        });
        
        // Handle form submission
        $('#edit-season-form').on('submit', function(e) {
            e.preventDefault();
            
            // Calculate total land and collect individual amounts
            var totalLand = 0;
            var landAmounts = [];
            $('#edit-season-modal .edit-land-input').each(function() {
                var landValue = parseFloat($(this).val()) || 0;
                if (landValue > 0) {
                    totalLand += landValue;
                    landAmounts.push(landValue);
                }
            });
            
            var newAmount = parseFloat($('#edit-due-amount').val()) || 0;
            
            if (totalLand <= 0 || newAmount <= 0) {
                alert('Please enter valid values for land and due amount.');
                return;
            }
            
            // Show loading
            $('#edit-season-form button[type="submit"]').prop('disabled', true).text('Updating...');
            
            $.ajax({
                url: agripump_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'agripump_edit_season_ledger',
                    nonce: agripump_ajax.nonce,
                    bill_id: billId,
                    item_index: itemIndex,
                    new_amount: newAmount,
                    new_land: totalLand,
                    new_land_display: landAmounts.join('+'),
                    new_season_id: seasonId,
                    new_season_name: seasonName
                },
                success: function(response) {
                    if (response.success) {
                        alert('Season item updated successfully!');
                        $('#edit-season-modal').remove();
                        loadCustomerLedger(); // Reload ledger
                    } else {
                        alert('Error updating item: ' + response.data);
                    }
                },
                error: function() {
                    alert('Error updating season item.');
                },
                complete: function() {
                    $('#edit-season-form button[type="submit"]').prop('disabled', false).text('Update');
                }
            });
        });
        
        // Handle modal close
        $('#edit-season-modal .agripump-modal-close, #edit-season-modal .cancel-edit').on('click', function() {
            $('#edit-season-modal').remove();
        });
        
        // Close modal when clicking outside
        $('#edit-season-modal').on('click', function(e) {
            if ($(e.target).is('#edit-season-modal')) {
                $('#edit-season-modal').remove();
            }
        });
    });
    
    // Delete season ledger item
    $(document).on('click', '.delete-season-item-btn', function(e) {
        e.preventDefault();
        var button = $(this);
        var billId = button.data('bill-id');
        var itemIndex = button.data('item-index');
        var seasonName = button.data('season-name');
        
        if (!confirm('Are you sure you want to delete the season item "' + seasonName + '"? This action cannot be undone.')) {
            return;
        }
        
        // Show loading
        button.prop('disabled', true).text('Deleting...');
        
        $.ajax({
            url: agripump_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'agripump_delete_season_ledger',
                nonce: agripump_ajax.nonce,
                bill_id: billId,
                item_index: itemIndex
            },
            success: function(response) {
                if (response.success) {
                    alert('Season item deleted successfully!');
                    loadCustomerLedger(); // Reload ledger
                } else {
                    alert('Error deleting item: ' + response.data);
                }
            },
            error: function() {
                alert('Error deleting season item.');
            },
            complete: function() {
                button.prop('disabled', false).text('Delete');
            }
        });
    });
}); 