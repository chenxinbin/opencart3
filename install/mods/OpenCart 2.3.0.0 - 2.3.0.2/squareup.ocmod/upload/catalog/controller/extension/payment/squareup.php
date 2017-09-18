<?php

class ControllerExtensionPaymentSquareup extends Controller {
    private $imodule = 'squareup';
    private $imodule_route;
    private $imodule_model;
    private $imodule_extension_route;
    private $imodule_extension_type;

    public function __construct($registry) {
        parent::__construct($registry);
        
        $this->registry->set('squareService', new \vendor\squareup\Service($this->registry));
        $this->registry->set('squareupCurrency', new \vendor\squareup\Currency($this->registry));

        $this->load->config('vendor/' . $this->imodule);

        $this->imodule_route = $this->config->get($this->imodule . '_route');
        $this->imodule_extension_route = $this->config->get($this->imodule . '_extension_route');
        $this->imodule_extension_type = $this->config->get($this->imodule . '_extension_type');

        $this->load->model($this->imodule_route);
        $this->imodule_model = $this->{$this->config->get($this->imodule . '_model_property')};

        //$this->document->addScript($this->url->link($this->imodule_route . '/js', '', true));
    }

    public function index() {
        $this->load->language($this->imodule_route);

        $data = array(
            'action' => $this->url->link($this->imodule_route . '/checkout', null, true),
            'text_loading' => $this->language->get('text_loading'),
            'text_new_card' => $this->language->get('text_new_card'),
            'text_card_details' => $this->language->get('text_card_details'),
            'text_saved_card' => $this->language->get('text_saved_card'),
            'text_card_number' => $this->language->get('text_card_number'),
            'text_card_expiry' => $this->language->get('text_card_expiry'),
            'text_card_cvc' => $this->language->get('text_card_cvc'),
            'text_card_zip' => $this->language->get('text_card_zip'),
            'text_card_save' => $this->language->get('text_card_save'),
            'button_confirm' => $this->language->get('button_confirm'),
            'squareup_js_api' => $this->config->get('squareup_js_api'),
            'error_browser_not_supported' => $this->language->get('error_browser_not_supported'),
            'is_logged' => $this->customer->isLogged()
        );

        if (!empty($this->session->data['payment_address']['postcode'])) {
            $data['payment_zip'] = $this->session->data['payment_address']['postcode'];
        } else {
            $data['payment_zip'] = '';
        }

        if ($this->config->get('squareup_enable_sandbox')) {
            $data['app_id'] = $this->config->get('squareup_sandbox_client_id');
            $data['sandbox_message'] = $this->language->get('warning_test_mode');
        } else {
            $data['app_id'] = $this->config->get('squareup_client_id');
            $data['sandbox_message'] = '';
        }

        $data['has_selected_card'] = false;
        
        if ($this->customer->isLogged()) {
            $data['cards'] = array();
            
            $square_customer = $this->imodule_model->findCustomer($this->customer->getId(), $this->config->get($this->imodule . '_enable_sandbox'));
                    
            foreach ($this->imodule_model->getCards($this->customer->getId(), $this->config->get($this->imodule . '_enable_sandbox')) as $card) {
                $selected = $card['squareup_token_id'] == $square_customer['squareup_token_id'];

                if ($selected) {
                    $data['has_selected_card'] = true;
                }

                $data['cards'][] = array(
                    'id' => $card['squareup_token_id'],
                    'selected' => $selected,
                    'text' => sprintf($this->language->get('text_card_ends_in'), $card['brand'], $card['ends_in'])
                );
            }
        }

        if (version_compare(VERSION, '2.2.0.0', '>=')) {
            $tpl_path = $this->imodule_route . '_card_details';
        } else {
            $tpl_path = $this->config->get('config_template') . '/template/' . $this->imodule_route . '_card_details.tpl';
            
            if (!file_exists(DIR_TEMPLATE . $tpl_path)) {
                $tpl_path = 'default/template/' . $this->imodule_route . '_card_details.tpl';
            }
        }

        $data = array_merge($data, $this->load->language($this->imodule_route));

        return $this->load->view($tpl_path, $data);
    }

    /* Public call for handling the checkout, use stored square credentials to issue charge */
    public function checkout() {
        $json = array();

        $this->imodule_model->setErrorHandler();

        try {
            $this->load->language($this->imodule_route);

            $this->load->model('checkout/order');
            $order = $this->model_checkout_order->getOrder($this->session->data['order_id']);

            $this->load->model('localisation/country');
            $shippingCountryInfo = $this->model_localisation_country->getCountry($order['shipping_country_id']);
            $billingCountryInfo = $this->model_localisation_country->getCountry($order['payment_country_id']);

            if (!empty($billingCountryInfo)) {
                $billingAddress = array(
                    'first_name' => $order['payment_firstname'],
                    'last_name' => $order['payment_lastname'],
                    'address_line_1' => $order['payment_address_1'],
                    'address_line_2' => $order['payment_address_2'],
                    'locality' => $order['payment_city'],
                    'sublocality' => $order['payment_zone'],
                    'postal_code' => $order['payment_postcode'],
                    'country' => $billingCountryInfo['iso_code_2'],
                    'organization' => $order['payment_company']
                );
            } else {
                $billingAddress = array();
            }

            if (!empty($shippingCountryInfo)) {
                $shippingAddress = array(
                    'first_name' => $order['shipping_firstname'],
                    'last_name' => $order['shipping_lastname'],
                    'address_line_1' => $order['shipping_address_1'],
                    'address_line_2' => $order['shipping_address_2'],
                    'locality' => $order['shipping_city'],
                    'sublocality' => $order['shipping_zone'],
                    'postal_code' => $order['shipping_postcode'],
                    'country' => $shippingCountryInfo['iso_code_2'],
                    'organization' => $order['shipping_company']
                );
            } else {
                $shippingAddress = array();
            }

            // ensure we have registered the customer with square
            $customer = $this->imodule_model->findCustomer($this->customer->getId(), $this->config->get('squareup_enable_sandbox'));
            if ($customer == null && $this->customer->isLogged()) {
                // create the customer
                $parameters = array(
                    'given_name' => $this->customer->getFirstName(),
                    'family_name' => $this->customer->getLastName(),
                    'email_address' => $this->customer->getEmail(),
                    'phone_number' => $this->customer->getTelephone(),
                    'reference_id' => $this->customer->getId()
                );
                $newCustomer = $this->squareService->api('POST', $this->config->get('squareup_endpoint_create_customer'), $parameters, null,
                    \vendor\squareup\Service::API_CONTENT_JSON, true);

                $this->imodule_model->createSquareCustomer($parameters['reference_id'], $newCustomer['customer']['id'], $this->config->get('squareup_enable_sandbox'));
                $customer = array('oc_customer_id' => $parameters['reference_id'], 'square_customer_id' => $newCustomer['customer']['id']);
            }

            $paymentInformation = array('use_saved'=>false);
            // check if user is logged in and wanted to save this card
            if ($this->customer->isLogged() && !empty($this->request->post['squareup_select_card'])) {
                $paymentInformation['use_saved'] = true;
                $cardVerified = $this->imodule_model->verifyCardCustomer((int)$this->request->post['squareup_select_card'], (int)$this->customer->getId());

                if (!$cardVerified) {
                    throw new \vendor\squareup\Exception($this->language->get('error_card_invalid'), $this->registry);
                }

                $card = $this->imodule_model->getCard((int)$this->request->post['squareup_select_card']);
                $paymentInformation['card_id'] = $card['token'];
                $paymentInformation['customer_id'] = $customer['square_customer_id'];
            } else if ($this->customer->isLogged() && isset($this->request->post['squareup_save_card'])) {
                // save the card
                $saveCardURI = $e = str_replace('%customerID%', $customer['square_customer_id'], $this->config->get('squareup_endpoint_cards'));
                $cardParams = array(
                    'card_nonce' => $this->request->post['squareup_nonce'],
                    'billing_address' => $billingAddress,
                    'cardholder_name' => $order['payment_firstname'] . ' ' . $order['payment_lastname']
                );
                $card = $this->squareService->api('POST', $saveCardURI, $cardParams, null, \vendor\squareup\Service::API_CONTENT_JSON, true);

                if (!$this->imodule_model->cardExists($this->customer->getId(), $card['card']['card_brand'], $card['card']['last_4'])) {
                    $this->imodule_model->saveCard($this->customer->getId(), $card['card']['id'], $card['card']['card_brand'], $card['card']['last_4'], $this->config->get($this->imodule . '_enable_sandbox'));
                }
                
                $paymentInformation['use_saved'] = true;
                $paymentInformation['card_id'] = $card['card']['id'];
                $paymentInformation['customer_id'] = $customer['square_customer_id'];
            } else {
                $paymentInformation['card_nonce'] = $this->request->post['squareup_nonce'];
            }
            $squareUpDelayCaptureSetting = $this->config->get('squareup_delay_capture');
            $delayCapture = !$this->cart->hasRecurringProducts() && !empty($squareUpDelayCaptureSetting);

            $transaction = array(
                'note' => sprintf($this->language->get('text_order_id'), $order['order_id']),
                'idempotency_key' => uniqid(),
                'amount_money' => array(
                    'amount' => $this->squareupCurrency->lowestDenomination($order['total'], $order['currency_code']),
                    'currency' => $order['currency_code']
                ),
                'billing_address' => $billingAddress,
                'buyer_email_address' => $order['email'],
                'delay_capture' => $delayCapture,
                'integration_id' => $this->config->get('squareup_integration_id')
            );
            
            if (!empty($shippingAddress)) {
                $transaction['shipping_address'] = $shippingAddress;
            }

            if ($paymentInformation['use_saved']) {
                $transaction['customer_card_id'] = $paymentInformation['card_id'];
                $transaction['customer_id'] = $paymentInformation['customer_id'];

                $square_token_id = $this->imodule_model->getTokenIdByCustomerAndToken($this->customer->getId(), $this->config->get($this->imodule . '_enable_sandbox'), $paymentInformation['card_id']);
                $this->imodule_model->updateDefaultCustomerToken($this->customer->getId(), $this->config->get($this->imodule . '_enable_sandbox'), $square_token_id);
            } else {
                $transaction['card_nonce'] = $paymentInformation['card_nonce'];
            }
            $chargeURI = $this->config->get('squareup_endpoint_charge');

            $chargeURI = str_replace('%location%', $this->config->get('squareup_enable_sandbox')?$this->config->get('squareup_sandbox_location_id'):$this->config->get('squareup_location_id'), $chargeURI);
            $transx = $this->squareService->api('POST', $chargeURI, $transaction, null, \vendor\squareup\Service::API_CONTENT_JSON, true);

            if (isset($this->request->server['HTTP_USER_AGENT'])) {
                $user_agent = $this->request->server['HTTP_USER_AGENT'];
            } else {
                $user_agent = '';
            }

            if (isset($this->request->server['REMOTE_ADDR'])) {
                $ip = $this->request->server['REMOTE_ADDR'];
            } else {
                $ip = '';
            }

            $this->imodule_model->saveTransaction($transx['transaction'], $this->config->get('squareup_merchant_id'), $billingAddress, $this->session->data['order_id'], $user_agent, $ip);

            $transaction_status = !empty($transx['transaction']['tenders'][0]['card_details']['status']) ?
                strtolower($transx['transaction']['tenders'][0]['card_details']['status']) : '';

            $order_status_id = $this->config->get($this->imodule . '_status_' . $transaction_status);

            if ($order_status_id) {
                if ($this->cart->hasRecurringProducts() && $transaction_status == 'captured') {
                    foreach ($this->cart->getRecurringProducts() as $item) {
                        if ($item['recurring']['trial']) {
                            $trial_price = $this->tax->calculate($item['recurring']['trial_price'] * $item['quantity'], $item['tax_class_id']);
                            $trial_amt = $this->currency->format($trial_price, $this->session->data['currency']);
                            $trial_text =  sprintf($this->language->get('text_trial'), $trial_amt, $item['recurring']['trial_cycle'], $item['recurring']['trial_frequency'], $item['recurring']['trial_duration']);
                            $item['recurring']['trial_price'] = $trial_price;
                        } else {
                            $trial_text = '';
                        }

                        $recurring_price = $this->tax->calculate($item['recurring']['price'] * $item['quantity'], $item['tax_class_id']);
                        $recurring_amt = $this->currency->format($recurring_price, $this->session->data['currency']);
                        $recurring_description = $trial_text . sprintf($this->language->get('text_recurring'), $recurring_amt, $item['recurring']['cycle'], $item['recurring']['frequency']);
                        $item['recurring']['price'] = $recurring_price;

                        if ($item['recurring']['duration'] > 0) {
                            $recurring_description .= sprintf($this->language->get('text_length'), $item['recurring']['duration']);
                        }

                        $this->imodule_model->createRecurring($item, $this->session->data['order_id'], $recurring_description, $transx['transaction']['id']);
                    }
                }

                $order_status_comment = $this->language->get($this->imodule . '_status_comment_' . $transaction_status);

                $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $order_status_id, $order_status_comment, true);
            }

            $json['redirect'] = $this->url->link('checkout/success', '', true);
        } catch (\vendor\squareup\Exception $e) {
            if ($e->curlError()) {
                $json['error'] = $this->language->get('text_token_issue_customer_error');
            } else {
                $errors = $e->getErrors();

                if (is_array($errors)) {
                    foreach ($errors as $err) {
                        switch ($err['code']) {
                            case \vendor\squareup\Service::ERR_CODE_ACCESS_TOKEN_REVOKED:
                                $this->imodule_model->tokenRevokedEmail();

                                $json['error'] = $this->language->get('text_token_issue_customer_error');

                                break;
                            case \vendor\squareup\Service::ERR_CODE_ACCESS_TOKEN_EXPIRED:
                                $this->imodule_model->tokenExpiredEmail();

                                $json['error'] = $this->language->get('text_token_issue_customer_error');

                                break;
                        }
                    }
                }

                if (!isset($json['error'])) {
                    $json['error'] = $e->getMessage();
                }
            }
        }

        $this->imodule_model->restoreErrorHandler();

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function info() {
        if (!$this->validate()) {
            $this->response->redirect($this->url->link($this->config->get('action_default'), '', true));
        } else {
            $append_token = !empty($this->session->data['token']) ? '&token=' . $this->session->data['token'] : '';

            $this->response->redirect(sprintf($this->config->get('squareup_admin_url'), (int)$this->request->get['squareup_transaction_id'], $append_token));
        }
    }

    protected function validate() {
        if (empty($this->request->get['cron_token']) || $this->request->get['cron_token'] != $this->config->get('squareup_cron_token')) {
            return false;
        }

        if (empty($this->request->get['squareup_transaction_id'])) {
            return false;
        }

        if (!$this->config->get('squareup_admin_url')) {
            return false;
        }

        return true;
    }

    public function recurring() {
        if (!$this->imodule_model->validateCRON()) {
            return;
        }

        $this->load->language($this->imodule_route);

        $result = array(
            'transaction_success' => array(),
            'transaction_error' => array(),
            'transaction_fail' => array(),
            'token_update_error' => ''
        );

        $expirations = array(
            'expired_authorized_transactions' => array(),
            'expiring_authorized_transactions' => array()
        );

        $result['token_update_error'] = $this->imodule_model->updateToken();

        $this->load->model('checkout/order');

        foreach ($this->imodule_model->nextRecurringPayments() as $payment) {
            try {
                if (!$payment['is_free']) {
                    $chargeURI = $this->config->get('squareup_endpoint_charge');
                    $chargeURI = str_replace('%location%', $this->config->get('squareup_enable_sandbox') ? $this->config->get('squareup_sandbox_location_id') : $this->config->get('squareup_location_id'), $chargeURI);
                    $transx = $this->squareService->api('POST', $chargeURI, $payment['transaction'], null, \vendor\squareup\Service::API_CONTENT_JSON, true);

                    $transaction_status = !empty($transx['transaction']['tenders'][0]['card_details']['status']) ?
                        strtolower($transx['transaction']['tenders'][0]['card_details']['status']) : '';

                    $target_currency = $transx['transaction']['tenders'][0]['amount_money']['currency'];

                    $amount = $this->squareupCurrency->standardDenomination($transx['transaction']['tenders'][0]['amount_money']['amount'], $target_currency);

                    $this->imodule_model->saveTransaction($transx['transaction'], $this->config->get('squareup_merchant_id'), $payment['billing_address'], $payment['order_id'], "CRON JOB", "127.0.0.1");

                    $reference = $transx['transaction']['id'];
                } else {
                    $amount = 0;
                    $target_currency = $this->config->get('config_currency');
                    $reference = '';
                    $transaction_status = 'captured';
                }

                $success = $transaction_status == 'captured';

                $this->imodule_model->addRecurringTransaction($payment['order_recurring_id'], $reference, $amount, $success);

                $trial_expired = false;
                $recurring_expired = false;
                $profile_suspended = false;

                if ($success) {
                    $trial_expired = $this->imodule_model->updateRecurringTrial($payment['order_recurring_id']);

                    $recurring_expired = $this->imodule_model->updateRecurringExpired($payment['order_recurring_id']);

                    $result['transaction_success'][$payment['order_recurring_id']] = $this->currency->format($amount, $target_currency);
                } else {
                    // Transaction was not successful. Suspend the recurring profile.
                    $profile_suspended = $this->imodule_model->suspendRecurringProfile($payment['order_recurring_id']);

                    $result['transaction_fail'][$payment['order_recurring_id']] = $this->currency->format($amount, $target_currency);
                }


                $order_status_id = $this->config->get($this->imodule . '_status_' . $transaction_status);

                if ($order_status_id) {
                    if (!$payment['is_free']) {
                        $order_status_comment = $this->language->get($this->imodule . '_status_comment_' . $transaction_status);
                    } else {
                        $order_status_comment = '';
                    }

                    if ($profile_suspended) {
                        $order_status_comment .= $this->language->get('text_squareup_profile_suspended');
                    }

                    if ($trial_expired) {
                        $order_status_comment .= $this->language->get('text_squareup_trial_expired');
                    }

                    if ($recurring_expired) {
                        $order_status_comment .= $this->language->get('text_squareup_recurring_expired');
                    }

                    if ($success) {
                        $notify = (bool)$this->config->get('squareup_notify_recurring_success');
                    } else {
                        $notify = (bool)$this->config->get('squareup_notify_recurring_fail');
                    }

                    $this->model_checkout_order->addOrderHistory($payment['order_id'], $order_status_id, trim($order_status_comment), $notify);
                }
            } catch (\vendor\squareup\Exception $e) {
                $result['transaction_error'][] = '[ID: ' . $payment['order_recurring_id'] . '] - ' . $e->getMessage();
            }
        };

        $this->load->model('checkout/order');

        foreach ($this->imodule_model->getExpiringAuthorizedTransactions() as $expiring_authorized_transaction) {
            $new_transaction = $this->imodule_model->refreshTransaction($expiring_authorized_transaction['squareup_transaction_id']);

            $order_info = $this->model_checkout_order->getOrder($new_transaction['order_id']);

            $transaction_data = array(
                'transaction_id' => $new_transaction['transaction_id'],
                'order_id' => $new_transaction['order_id'],
                'customer_name' => trim($order_info['firstname']) . ' ' . trim($order_info['lastname']),
                'transaction_url' => $this->url->link($this->imodule_route . '/info', 'squareup_transaction_id=' . $new_transaction['squareup_transaction_id'] . '&cron_token=' . $this->config->get('squareup_cron_token'), true)
            );

            if ($new_transaction['transaction_type'] != 'AUTHORIZED') {
                $expirations['expired_authorized_transactions'][] = $transaction_data;

                $status = strtolower($new_transaction['transaction_type']);

                $order_status_id = $this->config->get($this->imodule . '_status_' . $status);

                $order_status_comment = $this->language->get($this->imodule . '_status_comment_' . $status);

                $this->model_checkout_order->addOrderHistory($new_transaction['order_id'], $order_status_id, $order_status_comment, true);
            } else {
                $expirations['expiring_authorized_transactions'][] = $transaction_data;
            }
        }

        $this->imodule_model->expirationEmail($expirations);


        if ($this->config->get('squareup_cron_email_status')) {
            $this->imodule_model->cronEmail($result);
        }
    }
}
