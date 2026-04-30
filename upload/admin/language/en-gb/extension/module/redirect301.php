<?php
// Heading
$_['heading_title']        = '301 Redirects';

// Text
$_['text_success']         = 'Success: You have modified redirects!';
$_['text_list']            = 'Redirect List';
$_['text_filter']          = 'Filter';
$_['text_add']             = 'Add Redirect';
$_['text_edit']            = 'Edit Redirect';
$_['text_default']         = 'Default';
$_['text_enabled']         = 'Enabled';
$_['text_disabled']        = 'Disabled';
$_['text_all']             = 'All';
$_['text_no_results']      = 'No results!';
$_['text_home']            = 'Home';
$_['text_extension']       = 'Extensions';
$_['text_pagination']      = 'Showing %d to %d of %d (%d Pages)';
$_['text_confirm_delete']  = 'Are you sure you want to delete the selected redirects?';
$_['text_confirm_reset']   = 'Reset the usage counter?';

$_['text_301']             = 'Moved Permanently';
$_['text_302']             = 'Found (Temporary)';
$_['text_303']             = 'See Other';
$_['text_307']             = 'Temporary Redirect';
$_['text_308']             = 'Permanent Redirect';

// Column
$_['column_from_url']      = 'From URL';
$_['column_to_url']        = 'To URL / Product';
$_['column_response_code'] = 'Code';
$_['column_status']        = 'Status';
$_['column_date_start']    = 'Active From';
$_['column_date_end']      = 'Active Until';
$_['column_times_used']    = 'Hits';
$_['column_action']        = 'Action';

// Entry
$_['entry_from_url']       = 'From URL';
$_['entry_to_url']         = 'To URL';
$_['entry_product']        = 'Product (optional)';
$_['entry_response_code']  = 'HTTP Code';
$_['entry_status']         = 'Status';
$_['entry_date_start']     = 'Active From';
$_['entry_date_end']       = 'Active Until';
$_['entry_times_used']     = 'Times Used';

// Help
$_['help_from_url']        = 'Relative path, e.g. /old-page or /catalog/product. No domain, no query string.';
$_['help_to_url']          = 'Where to redirect. Either a relative path (/new-page) or an absolute URL (https://...). May be empty if a Product is selected below.';
$_['help_product']         = 'If a product is selected, the destination URL is built automatically from product_id. Takes priority over "To URL".';

// Error
$_['error_warning']        = 'Warning: Please check the form carefully for errors!';
$_['error_permission']     = 'Warning: You do not have permission to modify redirects!';
$_['error_from_url']       = '"From URL" is required (1-1000 chars)!';
$_['error_from_url_exists']= 'A redirect with this "From URL" already exists (id %d).';
$_['error_self_redirect']  = '"From URL" and "To URL" are the same — self-redirect is not allowed!';
$_['error_to_url']         = 'Either "To URL" or a Product must be set!';
$_['error_response_code']  = 'Invalid HTTP code. Allowed: 301, 302, 303, 307, 308.';
$_['error_date']           = 'Invalid date format (expected YYYY-MM-DD).';
$_['error_date_range']     = 'End date must be later than start date.';

// 404 log
$_['heading_title_404']       = '404 Log';
$_['column_404_url']          = 'URL (404)';
$_['column_404_referer']      = 'Referer';
$_['column_404_ip']           = 'IP';
$_['column_404_ua']           = 'User Agent';
$_['column_404_hits']         = 'Hits';
$_['column_404_first_seen']   = 'First Seen';
$_['column_404_last_seen']    = 'Last Seen';
$_['text_confirm_clear']      = 'Clear the entire 404 log? This cannot be undone.';
$_['text_redirect_exists']    = 'Redirect already created';
$_['button_log404']           = '404 Log';
$_['button_create_redirect']  = 'Create Redirect';
$_['button_edit_redirect']    = 'Edit Redirect';
$_['button_clear']            = 'Clear Log';
$_['button_delete_selected']  = 'Delete Selected';
$_['button_back']             = 'Back';

// Button
$_['button_add']           = 'Add';
$_['button_delete']        = 'Delete';
$_['button_save']          = 'Save';
$_['button_cancel']        = 'Cancel';
$_['button_edit']          = 'Edit';
$_['button_filter']        = 'Filter';
$_['button_reset']         = 'Reset Counter';
