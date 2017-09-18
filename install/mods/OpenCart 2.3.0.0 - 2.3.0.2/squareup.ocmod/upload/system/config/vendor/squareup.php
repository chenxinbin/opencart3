<?php

$_['squareup_integration_id'] = 'sqi_65a5ac54459940e3600a8561829fd970';
$_['squareup_js_api'] = 'https://js.squareup.com/v2/paymentform';
$_['squareup_transaction_link'] = 'https://squareup.com/dashboard/sales/transactions/%s/by-unit/%s';

$_['squareup_extension_version'] = '2.0.2';
$_['squareup_route'] = 'extension/payment/squareup';
$_['squareup_recurring_route'] = 'extension/recurring/squareup';
$_['squareup_model_class'] = 'ModelExtensionPaymentSquareup';
$_['squareup_model_property'] = 'model_extension_payment_squareup';
$_['squareup_extension_route'] = 'extension/extension';
$_['squareup_extension_type'] = '&type=payment';
$_['squareup_base_url'] = 'https://connect.squareup.com';
$_['squareup_api_version'] = 'v2';
$_['squareup_redirect_uri'] = 'extension/payment/squareup/oauth_callback';
$_['squareup_scopes'] = array(
    'MERCHANT_PROFILE_READ',
    'PAYMENTS_READ',
    'PAYMENTS_WRITE',
    'SETTLEMENTS_READ',
    'CUSTOMERS_READ',
    'CUSTOMERS_WRITE'
);
$_['squareup_token_expired_mail_frequency'] = '15 minutes';
$_['squareup_token_revoked_mail_frequency'] = '15 minutes';

$_['squareup_endpoint_authorize'] = 'oauth2/authorize';
$_['squareup_endpoint_obtain_token'] = 'oauth2/token';
$_['squareup_endpoint_refresh_token'] = 'oauth2/clients/%clientID%/access-token/renew';
$_['squareup_endpoint_locations'] = 'locations';
$_['squareup_endpoint_charge'] = 'locations/%location%/transactions';
$_['squareup_endpoint_retrieve_transaction'] = 'locations/%location%/transactions/%transactionId%';
$_['squareup_endpoint_refund'] = 'locations/%location%/transactions/%transactionId%/refund';
$_['squareup_endpoint_capture'] = 'locations/%location%/transactions/%transactionId%/capture';
$_['squareup_endpoint_void'] = 'locations/%location%/transactions/%transactionId%/void';
$_['squareup_endpoint_retrieve_customer'] = 'customers/%customerID%';
$_['squareup_endpoint_create_customer'] = 'customers';
$_['squareup_endpoint_cards'] = 'customers/%customerID%/cards';
$_['squareup_endpoint_delete_card'] = 'customers/%customerID%/cards/%cardID%';