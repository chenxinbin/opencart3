<?php

// Heading
$_['heading_title']                                        = 'Square';
$_['heading_title_transaction']                            = 'View Transaction #%s';

// Help
$_['help_total']                                        = 'The checkout total the order must reach before this payment method becomes active.';
$_['help_local_cron']                                   = 'Insert this command in your web server CRON tab. Set it up to run at least once per day.';
$_['help_remote_cron']                                  = 'Use this URL to set up a CRON task via a web-based CRON service. Set it up to run at least once per day.';
$_['help_recurring_status']                             = 'Enable to allow periodic recurring payments.<br />NOTE: You must also setup a daily CRON task.';
$_['help_cron_email']                                   = 'A summary of the recurring task will be sent to this e-mail after completion.';
$_['help_cron_email_status']                            = 'Enable to receive a summary after every CRON task.';
$_['help_notify_recurring_success']                     = 'Notify customers about successful recurring transactions.';
$_['help_notify_recurring_fail']                        = 'Notify customers about failed recurring transactions.';
$_['text_cron_settings']                                = 'CRON Settings';
$_['text_basic_settings']                               = 'Basic Settings';
$_['text_advanced_settings']                            = 'Advanced Settings';

// Tab
$_['tab_setting']                                         = 'Settings';
$_['tab_transaction']                                   = 'Transactions';
$_['tab_cron']                                             = 'CRON';
$_['tab_recurring']                                     = 'Recurring Payments';

// Text
$_['text_edit_heading']                                 = 'Edit Square';
$_['text_extension']                                    = 'Extensions';
$_['text_squareup']                                        = '<a target="_BLANK" href="https://squareup.com"><img src="view/image/payment/squareup.png" alt="Square" title="Square" style="border: 1px solid #EEEEEE;" /></a>';
$_['text_success']                                        = 'Success: You have modified Square payment module!';
$_['text_notification_ssl']                                = 'Make sure you have SSL enabled on your checkout page. Otherwise, the extension will not work.';

$_['text_loading']                                        = 'Loading data... Please wait...';
$_['text_loading_short']                                = 'Please wait...';
$_['text_view']                                            = 'View More';
$_['text_void']                                            = 'Void';
$_['text_refund']                                        = 'Refund';
$_['text_capture']                                        = 'Capture';
$_['text_confirm_void']                                    = 'You are about to void the following amount: <strong>%s</strong>. Click OK to proceed.';
$_['text_confirm_refund']                                = 'Please provide a reason for the refund:';
$_['text_confirm_capture']                                = 'You are about to capture the following amount: <strong>%s</strong>. Click OK to proceed.';
$_['text_success_void']                                    = 'Transaction successfully voided!';
$_['text_success_refund']                                = 'Transaction successfully refunded!';
$_['text_success_capture']                                = 'Transaction successfully captured!';
$_['text_transaction_statuses']                         = 'Transaction Statuses';
$_['text_no_transactions']                              = 'No transactions have been logged yet.';
$_['text_refunds']                                      = 'Refunds (%s)';
$_['text_insert_amount']                                = 'Please insert the refund amount. Maximum: %s in %s:';
$_['text_debug_label']                                  = 'Debug Logging';
$_['text_debug_help']                                   = 'API requests and responses will be logged in the OpenCart error log. Use this for only for debugging and development purposes.';
$_['text_debug_enabled']                                = 'Enabled'; 
$_['text_debug_disabled']                               = 'Disabled'; 
$_['text_ok']                                           = 'OK';
$_['text_confirm_action']                               = 'Are you sure?';
$_['text_refund_details']                               = 'Refund details';
$_['text_select_location']                              = 'Select location';
$_['text_refunded_amount']                              = 'Refunded: <strong>%s</strong>. Status of the refund: <strong>%s</strong>. Reason for the refund: <strong>%s</strong>';
$_['text_manage']                                       = 'Credit Card Transaction (Square)';
$_['text_manage_tooltip']                               = 'See details / Capture / Void / Refund';
$_['text_token_expiry_warning']                         = 'Your Square access token will expire on %s. <a href="%s">Click here</a> to renew it now.';
$_['text_token_expired']                                = 'Your Square access token has expired! <a href="%s">Click here</a> to renew it now.';
$_['text_token_revoked']                                = 'Your Square access token has been revoked! <a href="%s" id="focus_connect">Click here</a> to reauthorize the Square extension.';
$_['text_local_cron']                                   = 'Method #1 - CRON Task:';
$_['text_remote_cron']                                  = 'Method #2 - Remote CRON:';
$_['text_recurring_status']                             = 'Status of recurring payments:';
$_['text_recurring_info']                               = 'Please make sure to set up a daily CRON task using one of the methods below. CRON jobs help you with:<br /><br />&bull; Automatic refresh of your API access token<br />&bull; Processing of recurring transactions';
$_['text_executables']                                  = 'CRON execution methods';
$_['text_cron_email_status']                            = 'Send e-mail summary:';
$_['text_cron_email']                                   = 'Send task summary to this e-mail:';
$_['text_refresh_token']                                = 'Re-create token';
$_['text_admin_notifications']                          = 'Admin notifications';
$_['text_customer_notifications']                       = 'Customer notifications';
$_['text_acknowledge_cron']                             = 'I confirm that I have set up an automated CRON task using one of the methods above.';
$_['text_notify_recurring_success']                     = 'Recurring Transaction Successful:';
$_['text_notify_recurring_fail']                        = 'Recurring Transaction Failed:';
$_['text_confirm_cancel']                               = 'Are you sure you want to cancel the recurring payments?';
$_['text_canceled_success']                             = 'Success: You have succesfully canceled this payment!';
$_['text_order_history_cancel']                         = 'An administrator has canceled your recurring payments. Your card will no longer be charged.';
$_['text_refresh_access_token_success']                 = 'Successfully refreshed the connection to your Square account.'; 
$_['text_warning_cron']                                 = 'Make sure to set up a CRON job for this payment extension. <a href="javascript:void(0)" id="cron_click">Click here</a> to see the CRON settings.';
$_['text_fully_refunded']                               = 'Fully Refunded';
$_['text_partially_refunded']                           = 'Partially Refunded';
$_['text_refund_pending']                               = '%s (Refund Pending)';
$_['text_select_status']                                = '-- Select Status (Required) --';
$_['text_enable_payment']                               = 'Make sure to enable online payments for your Square account.';
$_['text_please_connect']                               = 'Please connect your Square application before saving.';
$_['text_fully_refunded_comment']                       = 'Order refund processed. Transaction was fully refunded.';
$_['text_partially_refunded_comment']                   = 'Order refund processed. Transaction was partially refunded.';

// Statuses
$_['squareup_status_comment_authorized']                = 'The card transaction has been authorized but not yet captured.';
$_['squareup_status_comment_captured']                  = 'The card transaction was authorized and subsequently captured (i.e., completed).';
$_['squareup_status_comment_voided']                    = 'The card transaction was authorized and subsequently voided (i.e., canceled).   ';
$_['squareup_status_comment_failed']                    = 'The card transaction failed.';
$_['squareup_status_comment_partially_refunded']        = 'The card transaction was partially refunded.';
$_['squareup_status_comment_fully_refunded']            = 'The card transaction was fully refunded.';

// Entry
$_['entry_total']                                        = 'Total';
$_['entry_geo_zone']                                    = 'Geo Zone';
$_['entry_sort_order']                                    = 'Sort Order';
$_['entry_merchant']                                    = 'Merchant ID';
$_['entry_transaction_id']                                = 'Transaction ID';
$_['entry_order_id']                                    = 'Order ID';
$_['entry_partner_solution_id']                            = 'Partner Solution ID';
$_['entry_transaction_status']                            = 'Transaction Status';
$_['entry_currency']                                    = 'Currency';
$_['entry_amount']                                        = 'Amount';
$_['entry_browser']                                        = 'Customer User Agent';
$_['entry_ip']                                            = 'Customer IP';
$_['entry_date_created']                                = 'Date Created';
$_['entry_billing_address_company']                     = 'Billing Company';
$_['entry_billing_address_street']                      = 'Billing Street';
$_['entry_billing_address_city']                        = 'Billing City';
$_['entry_billing_address_postcode']                    = 'Billing ZIP';
$_['entry_billing_address_province']                    = 'Billing Province/State';
$_['entry_billing_address_country']                     = 'Billing Country';
$_['entry_status_authorized']                           = 'Order status for Authorized';
$_['entry_status_captured']                             = 'Order status for Captured';
$_['entry_status_voided']                               = 'Order status for Voided';
$_['entry_status_failed']                               = 'Order status for Failed';
$_['entry_status_partially_refunded']                   = 'Order status for Partially Refunded';
$_['entry_status_fully_refunded']                       = 'Order status for Fully Refunded';
$_['entry_setup_confirmation']                          = 'Setup confirmation:';

// Error
$_['error_permission']                                     = 'Warning: You do not have permission to modify payment Square!';
$_['error_permission_recurring']                        = '<strong>Warning:</strong> You do not have permission to modify recurring payments!';
$_['error_transaction_missing']                         = 'Transaction not found!';
$_['error_no_ssl']                                      = '<strong>Warning:</strong> SSL is not enabled on your admin panel. Please enable it to finish your configuration.';
$_['error_user_rejected_connect_attempt']                = 'Connection attempt was canceled by the user.';
$_['error_possible_xss']                                = 'We detected a possible cross site attack and have terminated your connection attempt. Please verify your application ID and secret and try again using the buttons in the admin panel.';
$_['error_invalid_email']                               = 'The provided e-mail address is not valid!';
$_['error_cron_acknowledge']                            = 'Please confirm you have set up a CRON job.';

// Column
$_['column_transaction_id']                             = 'Transaction ID';
$_['column_order_id']                                     = 'Order ID';
$_['column_customer']                                    = 'Customer';
$_['column_status']                                     = 'Status';
$_['column_type']                                         = 'Payment Status';
$_['column_amount']                                     = 'Amount';
$_['column_ip']                                         = 'IP';
$_['column_date_created']                                 = 'Date Created';
$_['column_action']                                     = 'Action';
$_['column_refunds']                                    = 'Refunds';
$_['column_reason']                                     = 'Reason';
$_['column_fee']                                        = 'Processing Fee';

// Button
$_['button_void']                                        = 'Void';
$_['button_refund']                                        = 'Refund';
$_['button_capture']                                    = 'Capture';
$_['button_help']                                       = 'Documentation';

//
$_['text_connection_success']                            = 'Successfully connected!';
$_['text_location_error']                                = 'There was an error when trying to sync locations.';
$_['text_no_location_selected_warning']                    = 'There is no selected location.';
$_['text_no_appropriate_locations_warning']                = 'There are no locations capable of online card processing setup in your Square account.';
$_['text_connection_section']                            = 'Connect';
$_['text_not_connected']                                = 'Not connected';
$_['text_connected']                                    = 'Connected';
$_['button_connect']                                    = 'Connect';
$_['button_reconnect']                                    = 'Reconnect';
$_['button_refresh']                                    = 'Refresh token';
$_['text_not_connected_info']                            = 'By clicking this button you will connect this module to your Square account.';
$_['text_connected_info']                                = "Reconnect if you want to switch accounts or have manually revoked this extension's access from the Square App console.";
$_['text_disabled_connect_help_text']                    = 'The client id and secret are required fields.';

$_['text_extension_status']                                = 'Extension status';
$_['text_extension_status_help']                        = 'Enable or disable the payment method'; 
$_['text_extension_status_enabled']                     = 'Enabled'; 
$_['text_extension_status_disabled']                    = 'Disabled'; 

$_['text_payment_method_name_label']                    = 'Payment method name';
$_['text_payment_method_name_help']                        = 'Checkout payment method name';
$_['text_payment_method_name_placeholder']                = 'Credit / Debit Card';

$_['text_redirect_uri_label']                            = 'Square OAuth Redirect URL';
$_['text_redirect_uri_help']                            = 'Paste this link into the Redirect URI field under Manage Application/oAuth';

$_['text_client_id_label']                                = 'Square Application ID';
$_['text_client_id_help']                                = 'Get this from the Manage Application page on Square';
$_['text_client_id_placeholder']                        = 'Square Application ID';

$_['text_client_secret_label']                            = 'OAuth Application Secret';
$_['text_client_secret_help']                            = 'Get this from the Manage Application page on Square';
$_['text_client_secret_placeholder']                    = 'OAuth Application Secret';

$_['text_delay_capture_label']                            = 'Transaction type';
$_['text_delay_capture_help']                            = 'Only authorize transactions or perform charges automatically';
$_['text_authorize_label']                                = 'Authorize';
$_['text_sale_label']                                    = 'Sale';

$_['text_enable_sandbox_label']                            = 'Enable sandbox mode';
$_['text_enable_sandbox_help']                            = 'Enable sandbox mode for testing transactions';
$_['text_sandbox_enabled_label']                        = 'Enabled'; 
$_['text_sandbox_disabled_label']                       = 'Disabled'; 

$_['text_sandbox_section_heading']                        = 'Square Sandbox Settings';
$_['text_settings_section_heading']                     = 'Square Settings';

$_['text_sandbox_client_id_label']                        = 'Sandbox Application ID';
$_['text_sandbox_client_id_help']                        = 'Get this from the Manage Application page on Square';
$_['text_sandbox_client_id_placeholder']                = 'Sandbox Application ID';

$_['text_sandbox_access_token_label']                    = 'Sandbox Access Token';
$_['text_sandbox_access_token_help']                    = 'Get this from the Manage Application page on  Square';
$_['text_sandbox_access_token_placeholder']                = 'Sandbox Access Token';

$_['text_merchant_info_section_heading']                = 'Mechant Information';

$_['text_merchant_name_label']                            = 'Merchant name';
$_['text_merchant_name_placeholder']                    = 'Not setup';
$_['text_na']                                           = 'N/A';

$_['text_access_token_expires_label']                    = 'Access token expires';
$_['text_access_token_expires_placeholder']                = 'Not setup';

$_['text_location_label']                                = 'Location';
$_['text_sandbox_location_label']                        = 'Sandbox Location';
$_['text_no_locations_label']                            = 'No valid locations';
$_['text_location_help']                                = 'Select which configured Square location to be used for transactions. Has to have card processing capabilities enabled.';

$_['text_sandbox_enabled']                              = 'Sandbox mode is enabled! Transactions will appear to go through, but no charges will be carried out.';
$_['text_auth_voided_6_days']                           = 'You have enabled delayed capture. If you do not capture authorized transactions, Square will automatically void them 6 days after they have been placed.';
$_['text_extension_disabled']                           = 'The Square payment extension is currently disabled.';

$_['text_status_authorized']                           = 'Authorized';
$_['text_status_captured']                             = 'Captured';
$_['text_status_voided']                               = 'Voided';
$_['text_status_failed']                               = 'Failed';

$_['tooltip_video_help']                               = 'Video tutorial';
$_['text_installation_video']                           = 'Installation Video';
$_['tooltip_integration_settings_help']                = 'Documentation';

$_['error_client_id']                                    = 'Square Application ID is a required field, 32 characters long!';
$_['error_client_secret']                                = 'OAuth Application Secret is a required field, 32 characters long!';
$_['error_sandbox_client_id']                            = 'The sandbox client ID is a required field when sandbox mode is enabled';
$_['error_sandbox_token']                                = 'The sandbox token is a required field when sandbox mode is enabled';
$_['error_no_location_selected']                        = 'The location is a required field';
$_['error_refresh_access_token']                        = "An error occurred when trying to refresh the extension's connection to your Square account. Please verify your application credentials and try again.";
$_['error_form']                                        = 'Please check the form carefully for errors!';
$_['error_status_not_set']                              = 'Please select a status!';
