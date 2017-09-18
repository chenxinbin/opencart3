<?php

class ModelExtensionPaymentSquareup extends Model {
    private $imodule = 'squareup';
    private $imodule_route;
    private $imodule_model;
    private $imodule_extension_route;
    private $imodule_extension_type;
    private $extension_version;
    private $api_version;

    const RECURRING_ACTIVE = 1;
    const RECURRING_INACTIVE = 2;
    const RECURRING_CANCELLED = 3;
    const RECURRING_SUSPENDED = 4;
    const RECURRING_EXPIRED = 5;
    const RECURRING_PENDING = 6;

    const TRANSACTION_DATE_ADDED = 0;
    const TRANSACTION_PAYMENT = 1;
    const TRANSACTION_OUTSTANDING_PAYMENT = 2;
    const TRANSACTION_SKIPPED = 3;
    const TRANSACTION_FAILED = 4;
    const TRANSACTION_CANCELLED = 5;
    const TRANSACTION_SUSPENDED = 6;
    const TRANSACTION_SUSPENDED_FAILED = 7;
    const TRANSACTION_OUTSTANDING_FAILED = 8;
    const TRANSACTION_EXPIRED = 9;

    /*
    Loads some config
    */
    public function __construct($registry) {
        parent::__construct($registry);

        $this->load->config('vendor/' . $this->imodule);

        $this->imodule_route = $this->config->get($this->imodule . '_route');
        $this->imodule_extension_route = $this->config->get($this->imodule . '_extension_route');
        $this->imodule_extension_type = $this->config->get($this->imodule . '_extension_type');
        $this->extension_version = $this->config->get($this->imodule . '_extension_version');
        $this->api_version = $this->config->get($this->imodule . '_api_version');
        
        $this->registry->set('squareupCurrency', new \vendor\squareup\Currency($this->registry));

        $this->imodule_model = $this->{$this->config->get($this->imodule . '_model_property')};

        $this->load->model('setting/setting');
    }

    public function recurringPayments() {
        /*
         * Used by the checkout to state the module
         * supports recurring recurrings.
         */
        $squareup_recurring_status = $this->config->get('squareup_recurring_status');
        return !empty($squareup_recurring_status);
    }

    public function createRecurring($recurring, $order_id, $description, $reference) {
        // We need to override this value for the proper calculation in updateRecurringExpired
        $trial_duration = (bool)$recurring['recurring']['trial'] ?
            (int)$recurring['recurring']['trial_duration'] : 0;

        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring` SET `order_id` = '" . (int)$order_id . "', `date_added` = NOW(), `status` = '" . self::RECURRING_ACTIVE . "', `product_id` = '" . (int)$recurring['product_id'] . "', `product_name` = '" . $this->db->escape($recurring['name']) . "', `product_quantity` = '" . $this->db->escape($recurring['quantity']) . "', `recurring_id` = '" . (int)$recurring['recurring']['recurring_id'] . "', `recurring_name` = '" . $this->db->escape($recurring['recurring']['name']) . "', `recurring_description` = '" . $this->db->escape($description) . "', `recurring_frequency` = '" . $this->db->escape($recurring['recurring']['frequency']) . "', `recurring_cycle` = '" . (int)$recurring['recurring']['cycle'] . "', `recurring_duration` = '" . (int)$recurring['recurring']['duration'] . "', `recurring_price` = '" . (float)$recurring['recurring']['price'] . "', `trial` = '" . (int)$recurring['recurring']['trial'] . "', `trial_frequency` = '" . $this->db->escape($recurring['recurring']['trial_frequency']) . "', `trial_cycle` = '" . (int)$recurring['recurring']['trial_cycle'] . "', `trial_duration` = '" . (int)$trial_duration . "', `trial_price` = '" . (float)$recurring['recurring']['trial_price'] . "', `reference` = '" . $this->db->escape($reference) . "'");

        return $this->db->getLastId();
    }

    public function getMethod($address, $total) {
        $geo_zone_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('squareup_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

        $squareup_display_name = $this->config->get('squareup_display_name');
        $title = !empty($squareup_display_name[$this->config->get('config_language_id')]) ?
            $this->config->get('squareup_display_name')[$this->config->get('config_language_id')] : 'Credit / Debit Card';

        $status = true;

        $minimum_total = (float)$this->config->get('squareup_total');

        $squareup_geo_zone_id = $this->config->get('squareup_geo_zone_id');
        if ($minimum_total > 0 && $minimum_total > $total) {
            $status = false;
        } else if (empty($squareup_geo_zone_id)) {
            $status = true;
        } else if ($geo_zone_query->num_rows == 0) {
            $status = false;
        }

        $method_data = array();

        if ($status) {
            $method_data = array(
                'code'        => 'squareup',
                'title'        => $title,
                'terms'        => '',
                'sort_order' => (int)$this->config->get('squareup_sort_order')
            );
        }

        return $method_data;
    }

    public function getCard($cardId) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_token` WHERE squareup_token_id='" . (int)$cardId . "'")->row;
    }

    public function cardExists($customer_id, $brand, $ends_in) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_token` WHERE oc_customer_id='" . (int)$customer_id . "' AND brand='" . $this->db->escape($brand) . "' AND ends_in='" . (int)$ends_in . "'")->num_rows > 0;
    }

    public function getCards($openCartCustomerId, $sandbox) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "squareup_token` WHERE oc_customer_id=\"" . $this->db->escape($openCartCustomerId) . "\" AND sandbox=\"" . (int)$sandbox . "\";";
        return $this->db->query($sql)->rows;
    }

    public function saveCard($openCartCustomerId, $cardToken, $brand, $endsIn, $sandbox) {
        $sql = "INSERT INTO `" . DB_PREFIX . "squareup_token` (oc_customer_id, sandbox, token, brand, ends_in) values (".$openCartCustomerId.",\"" . (int)$sandbox . "\",\"".$this->db->escape($cardToken)."\",".
            "\"" . $this->db->escape($brand) . "\"," . 
            "\"" . $this->db->escape($endsIn) . "\"" .
        ");";
        $this->db->query($sql);
    }

    public function updateDefaultCustomerToken($customer_id, $sandbox, $squareup_token_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "squareup_customer` SET squareup_token_id='" . (int)$squareup_token_id . "' WHERE oc_customer_id = '" . (int)$customer_id . "' AND sandbox='" . (int)$sandbox . "'");
    }

    public function getFirstTokenId($customer_id, $sandbox) {
        foreach ($this->getCards($customer_id, $sandbox) as $card) {
            return (int)$card['squareup_token_id'];
        }

        return 0;
    }

    public function getTokenIdByCustomerAndToken($customer_id, $sandbox, $token) {
        foreach ($this->getCards($customer_id, $sandbox) as $card) {
            if ($card['token'] == $token) {
                return (int)$card['squareup_token_id'];
            }
        }

        return 0;
    }

    public function saveTransaction($transaction, $merchantId, $paymentAddress, $orderId, $userAgent, $ip) {
        $isRefunded = (isset($transaction['refunds']) && is_aray($transaction['refunds']) && count($transaction['refunds']) > 0);

        $amount = $this->squareupCurrency->standardDenomination($transaction['tenders'][0]['amount_money']['amount'], $transaction['tenders'][0]['amount_money']['currency']);

        $sql = "INSERT INTO `" . DB_PREFIX . "squareup_transaction` (".
            "transaction_id,merchant_id,location_id,order_id,transaction_type,transaction_amount,transaction_currency,billing_address_city,billing_address_country,billing_address_postcode,billing_address_province,billing_address_street,device_browser,device_ip,created_at,is_refunded,refunded_at,tenders,refunds" .
        ") values (" .
            "\"" . $this->db->escape($transaction['id']) . "\"," .
            "\"" . $this->db->escape($merchantId) . "\"," .
            "\"" . $this->db->escape($transaction['location_id']) . "\"," .
            $orderId . "," .
            "\"" . $this->db->escape($transaction['tenders'][0]['card_details']['status']) . "\"," .
            "\"" . $this->db->escape($amount) . "\"," .
            "\"" . $this->db->escape($transaction['tenders'][0]['amount_money']['currency']) . "\"," .
            "\"" . $this->db->escape($paymentAddress['locality']) . "\"," .
            "\"" . $this->db->escape($paymentAddress['country']) . "\"," .
            "\"" . $this->db->escape($paymentAddress['postal_code']) . "\"," .
            "\"" . $this->db->escape($paymentAddress['sublocality']) . "\"," .
            "\"" . $this->db->escape($paymentAddress['address_line_1'] . " " . $paymentAddress['address_line_2']) . "\"," .
            "\"" . $this->db->escape($userAgent) . "\"," .
            "\"" . $this->db->escape($ip) . "\"," .
            "\"" . $this->db->escape($transaction['created_at']) . "\"," .
            (($isRefunded)?1:0) . "," . 
            "\"" . $this->db->escape(($isRefunded)?$transaction['refunds'][0]['created_at']:'') . "\"," .
            "\"" . $this->db->escape(json_encode($transaction['tenders'])) . "\"," .
            "\"" . $this->db->escape(($isRefunded)?json_encode($transaction['refunds']):'[]') . "\"" .
        ");";
        $this->db->query($sql);
    }

    public function updateTransaction($squareTransactionId, $updates) {
        $updateAssignments = array();
        foreach ($updates as $key => $value) {
            $updateAssignments[] = $key . "=\"" . $this->db->escape($value) . "\"";
        }
        $sql = "UPDATE `" . DB_PREFIX . "squareup_transaction` SET " . implode(',', $updateAssignments) . " WHERE transaction_id=\"" . $this->db->escape($squareTransactionId) . "\";";
        $this->db->query($sql);
    }

    public function findCustomer($openCartCustomerId, $sandbox) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "squareup_customer` WHERE oc_customer_id = '" . $this->db->escape($openCartCustomerId) . "' AND sandbox='" . (int)$sandbox . "'";
        $row = $this->db->query($sql)->row;
        return $row;
    }

    public function createSquareCustomer($openCartCustomerId, $squareCustomerId, $sandbox) {
        $sql = "INSERT INTO `" . DB_PREFIX . "squareup_customer` (oc_customer_id, square_customer_id, sandbox) values (" . 
            $this->db->escape($openCartCustomerId) . ", \"" . 
            $this->db->escape($squareCustomerId) . "\", " . 
            (int)$sandbox . ");";
        $this->db->query($sql);
    }

    public function verifyCardCustomer($card_id, $customer_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_token` WHERE squareup_token_id='" . (int)$card_id . "' AND oc_customer_id='" . (int)$customer_id . "'")->num_rows > 0;
    }

    public function deleteCard($card_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "squareup_token` WHERE squareup_token_id='" . (int)$card_id . "'");
    }

    public function addRecurringTransaction($order_recurring_id, $reference, $amount, $status) {
        
        if ($status) {
            $type = self::TRANSACTION_PAYMENT;
        } else {
            $type = self::TRANSACTION_FAILED;
        }

        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_recurring_transaction` SET order_recurring_id='" . (int)$order_recurring_id . "', reference='" . $this->db->escape($reference) . "', type='" . (int)$type . "', amount='" . (float)$amount . "', date_added=NOW()");
    }

    public function updateToken() {
        try {
            $endpoint = $this->config->get($this->imodule . '_endpoint_refresh_token');
            $endpoint = str_replace('%clientID%', $this->config->get('squareup_client_id'), $endpoint);
            $params = array(
                'access_token' => $this->config->get('squareup_access_token')
            );

            $response = $this->squareService->api(
                'POST',
                $endpoint,
                $params,
                null,
                \vendor\squareup\Service::API_CONTENT_JSON,
                true,
                $this->config->get('squareup_client_secret'),
                'Client',
                true
            );
            if (!isset($response['access_token']) || !isset($response['token_type']) || !isset($response['expires_at']) || !isset($response['merchant_id']) ||
                $response['merchant_id'] != $this->config->get('squareup_merchant_id')) {

                return $this->language->get('error_squareup_cron_token');
            } else {
                $this->modSettings(array(
                    'squareup_access_token' => $response['access_token'],
                    'squareup_access_token_expires' => $response['expires_at']
                ));
            }
        } catch (\vendor\squareup\Exception $e) {
            return $e->getMessage();
        }

        return '';
    }

    private function modSettings($settings) {
        $group_key = version_compare(VERSION, '2.0.0.0', '>') ? 'code' : 'group';

        foreach ($settings as $key => $value) {
            $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `" . $group_key . "`='" . $this->imodule . "' AND `key`='" . $key . "'");

            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET `" . $group_key . "`='" . $this->imodule . "', `key`='" . $key . "', `value`='" . $this->db->escape($value) . "', serialized=0, store_id=0");
        }
    }

    private function getLastSuccessfulRecurringPaymentDate($order_recurring_id) {
        return $this->db->query("SELECT date_added FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE order_recurring_id='" . (int)$order_recurring_id . "' AND type='" . self::TRANSACTION_PAYMENT . "' ORDER BY date_added DESC LIMIT 0,1")->row['date_added'];
    }

    private function paymentIsDue($order_recurring_id) {
        // We know the recurring profile is active.
        $recurring_info = $this->getRecurring($order_recurring_id);

        if ($recurring_info['trial']) {
            $frequency = $recurring_info['trial_frequency'];
            $cycle = (int)$recurring_info['trial_cycle'];
        } else {
            $frequency = $recurring_info['recurring_frequency'];
            $cycle = (int)$recurring_info['recurring_cycle'];
        }

        // Find date of last payment
        if (!$this->getTotalSuccessfulPayments($order_recurring_id)) {
            $previous_time = strtotime($recurring_info['date_added']);
        } else {
            $previous_time = strtotime($this->getLastSuccessfulRecurringPaymentDate($order_recurring_id));
        }

        switch ($frequency) {
            case 'day' : $time_interval = 24 * 3600; break;
            case 'week' : $time_interval = 7 * 24 * 3600; break;
            case 'semi_month' : $time_interval = 15 * 24 * 3600; break;
            case 'month' : $time_interval = 30 * 24 * 3600; break;
            case 'year' : $time_interval = 365 * 24 * 3600; break;
        }

        $due_date = date('Y-m-d', $previous_time + ($time_interval * $cycle));

        $this_date = date('Y-m-d');

        return $this_date >= $due_date;
    }

    public function getRecurring($order_recurring_id) {
        $recurring_sql = "SELECT * FROM `" . DB_PREFIX . "order_recurring` WHERE order_recurring_id='" . (int)$order_recurring_id . "'";

        return $this->db->query($recurring_sql)->row;
    }

    public function getTotalSuccessfulPayments($order_recurring_id) {
        return $this->db->query("SELECT COUNT(*) as total FROM `" . DB_PREFIX . "order_recurring_transaction` WHERE order_recurring_id='" . (int)$order_recurring_id . "' AND type='" . self::TRANSACTION_PAYMENT . "'")->row['total'];
    }

    public function updateRecurringExpired($order_recurring_id) {
        $recurring_info = $this->getRecurring($order_recurring_id);

        if ($recurring_info['trial']) {
            // If we are in trial, we need to check if the trial will end at some point
            $expirable = (bool)$recurring_info['trial_duration'];
        } else {
            // If we are not in trial, we need to check if the recurring will end at some point
            $expirable = (bool)$recurring_info['recurring_duration'];
        }

        // If recurring payment can expire (trial_duration > 0 AND recurring_duration > 0)
        if ($expirable) {
            $number_of_successful_payments = $this->getTotalSuccessfulPayments($order_recurring_id);

            $total_duration = (int)$recurring_info['trial_duration'] + (int)$recurring_info['recurring_duration'];
            
            // If successful payments exceed total_duration
            if ($number_of_successful_payments >= $total_duration) {
                $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET status='" . self::RECURRING_EXPIRED . "' WHERE order_recurring_id='" . (int)$order_recurring_id . "'");

                return true;
            }
        }

        return false;
    }

    public function updateRecurringTrial($order_recurring_id) {
        $recurring_info = $this->getRecurring($order_recurring_id);

        // If recurring payment is in trial and can expire (trial_duration > 0)
        if ($recurring_info['trial'] && $recurring_info['trial_duration']) {
            $number_of_successful_payments = $this->getTotalSuccessfulPayments($order_recurring_id);

            // If successful payments exceed trial_duration
            if ($number_of_successful_payments >= $recurring_info['trial_duration']) {
                $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET trial='0' WHERE order_recurring_id='" . (int)$order_recurring_id . "'");

                return true;
            }
        }

        return false;
    }

    public function suspendRecurringProfile($order_recurring_id) {
        $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET status='" . self::RECURRING_SUSPENDED . "' WHERE order_recurring_id='" . (int)$order_recurring_id . "'");

        return true;
    }

    public function nextRecurringPayments() {
        $payments = array();

        if (!$this->config->get('squareup_recurring_status')) {
            return $payments;
        }

        $recurring_sql = "SELECT * FROM `" . DB_PREFIX . "order_recurring` `or` INNER JOIN `" . DB_PREFIX . "squareup_transaction` st ON (st.transaction_id = `or`.reference) WHERE `or`.status='" . self::RECURRING_ACTIVE . "'";

        $this->load->model('checkout/order');

        foreach ($this->db->query($recurring_sql)->rows as $recurring) {
            if (!$this->paymentIsDue($recurring['order_recurring_id'])) {
                continue;
            }

            $order_info = $this->model_checkout_order->getOrder($recurring['order_id']);

            $billingAddress = array(
                'first_name' => $order_info['payment_firstname'],
                'last_name' => $order_info['payment_lastname'],
                'address_line_1' => $recurring['billing_address_street'],
                'address_line_2' => '',
                'locality' => $recurring['billing_address_city'],
                'sublocality' => $recurring['billing_address_province'],
                'postal_code' => $recurring['billing_address_postcode'],
                'country' => $recurring['billing_address_country'],
                'organization' => $recurring['billing_address_company']
            );

            $transaction_info = @json_decode($recurring['tenders'], true);

            $price = (float)($recurring['trial'] ? $recurring['trial_price'] : $recurring['recurring_price']);

            $transaction = array(
                'note' => sprintf($this->language->get('text_order_id'), $order_info['order_id']),
                'idempotency_key' => uniqid(),
                'amount_money' => array(
                    'amount' => $this->squareupCurrency->lowestDenomination($price * $recurring['product_quantity'], $recurring['transaction_currency']),
                    'currency' => $recurring['transaction_currency']
                ),
                'billing_address' => $billingAddress,
                'buyer_email_address' => $order_info['email'],
                'delay_capture' => false,
                'customer_id' => $transaction_info[0]['customer_id'],
                'customer_card_id' => $transaction_info[0]['card_details']['card']['id'],
                'integration_id' => $this->config->get('squareup_integration_id')
            );

            $payments[] = array(
                'is_free' => $price == 0,
                'order_id' => $recurring['order_id'],
                'order_recurring_id' => $recurring['order_recurring_id'],
                'billing_address' => $billingAddress,
                'transaction' => $transaction
            );
        }

        return $payments;
    }

    public function cronEmail($result) {
        if (VERSION >= '2.0.0.0' && VERSION < '2.0.2.0') {
            $mail = new Mail($this->config->get('config_mail'));
        } else {
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');

            if (VERSION >= '2.0.2.0') {
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            } else {
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');
            }
        }

        $br = '<br />';

        $subject = $this->language->get('text_cron_subject');

        $message = $this->language->get('text_cron_message') . $br . $br;

        $message .= '<strong>' . $this->language->get('text_cron_summary_token_heading') . '</strong>' . $br;

        if ($result['token_update_error']) {
            $message .= $result['token_update_error'] . $br . $br;
        } else {
            $message .= $this->language->get('text_cron_summary_token_updated') . $br . $br;
        }

        if (!empty($result['transaction_error'])) {
            $message .= '<strong>' . $this->language->get('text_cron_summary_error_heading') . '</strong>' . $br;

            $message .= implode($br, $result['transaction_error']) . $br . $br;
        }

        if (!empty($result['transaction_fail'])) {
            $message .= '<strong>' . $this->language->get('text_cron_summary_fail_heading') . '</strong>' . $br;

            foreach ($result['transaction_fail'] as $order_recurring_id => $amount) {
                $message .= sprintf($this->language->get('text_cron_fail_charge'), $order_recurring_id, $amount) . $br;
            }
        }

        if (!empty($result['transaction_success'])) {
            $message .= '<strong>' . $this->language->get('text_cron_summary_success_heading') . '</strong>' . $br;

            foreach ($result['transaction_success'] as $order_recurring_id => $amount) {
                $message .= sprintf($this->language->get('text_cron_success_charge'), $order_recurring_id, $amount) . $br;
            }
        }

        $mail->setTo($this->config->get('squareup_cron_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText(strip_tags($message));
        $mail->setHtml($message);
        $mail->send();
    }

    public function validateCRON() {
        $squareup_status = $this->config->get('squareup_status');
        if (empty($squareup_status)) {
            return false;
        }

        if (!empty($this->request->get['cron_token']) && $this->request->get['cron_token'] == $this->config->get('squareup_cron_token')) {
            return true;
        }

        if (defined('SQUAREUP_ROUTE')) {
            return true;
        }

        return false;
    }

    private function checkTokenIssueEmailFrequency($lastOccuranceSettingKey, $period, $update = false) {
        $now = new DateTime();
        $lastOccurance = $this->config->get($lastOccuranceSettingKey);
        $last = null;
        if (!empty($lastOccurance)) {
            $last = DateTime::createFromFormat('Y-m-d H:i:s', $lastOccurance);
        } else {
            $last = DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 00:00:01');
        }
        $last->modify('+' . $period);
        if ($now->getTimestamp() > $last->getTimestamp()) {
            if ($update) {
                $this->modSettings(array($lastOccuranceSettingKey => $now->format('Y-m-d H:i:s')));
            }
            return true;
        }
        return false;
    }

    public function errorHandler($code, $message, $file, $line) {
        // error suppressed with @
        if (error_reporting() === 0) {
            return false;
        }

        switch ($code) {
            case E_NOTICE:
            case E_USER_NOTICE:
                $error = 'Notice';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                $error = 'Warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
                $error = 'Fatal Error';
                break;
            default:
                $error = 'Unknown';
                break;
        }

        throw new \vendor\squareup\Exception('<b>' . $error . '</b>: ' . $message . ' in <b>' . $file . '</b> on line <b>' . $line . '</b>', $this->registry);
        
        return true;
    }

    public function setErrorHandler() {
        set_error_handler(array($this, 'errorHandler'));
    }

    public function restoreErrorHandler() {
        restore_error_handler();
    }

    public function getExpiringAuthorizedTransactions() {
        $two_days_ago = date('Y-m-d', time() - (2 * 24 * 3600));

        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_transaction` WHERE transaction_type='AUTHORIZED' AND created_at < '" . $two_days_ago . "'")->rows;
    }

    public function refreshTransaction($transactionId) {
        $transaction = $this->getTransaction($transactionId);

        $endpoint = $this->config->get($this->imodule.'_endpoint_retrieve_transaction');
        $endpoint = str_replace('%location%', $transaction['location_id'], $endpoint);
        $endpoint = str_replace('%transactionId%', $transaction['transaction_id'], $endpoint);
        $result = $this->squareService->api('GET', $endpoint, null, null, \vendor\squareup\Service::API_CONTENT_JSON, true);

        $type = $result['transaction']['tenders'][0]['card_details']['status'];

        $refunds = array();

        if (!empty($result['transaction']['refunds'])) {
            $refunds = $result['transaction']['refunds'];
        }

        $this->db->query("UPDATE `" . DB_PREFIX . "squareup_transaction` SET transaction_type='" . $this->db->escape($type) . "', is_refunded='" . (int)!empty($refunds) . "', refunds='" . $this->db->escape(json_encode($refunds)) . "' WHERE squareup_transaction_id='" . (int)$transactionId . "'");

        return $this->getTransaction($transactionId);
    }

    public function getTransaction($squareup_transaction_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_transaction` WHERE squareup_transaction_id='" . (int)$squareup_transaction_id . "'")->row;
    }

    public function tokenExpiredEmail() {
        if (!$this->checkTokenIssueEmailFrequency('squareup_token_expired_last_mail', $this->config->get('squareup_token_expired_mail_frequency'), true)) {
            return;
        }

        if (VERSION >= '2.0.0.0' && VERSION < '2.0.2.0') {
            $mail = new Mail($this->config->get('config_mail'));
        } else {
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');

            if (VERSION >= '2.0.2.0') {
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            } else {
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');
            }
        }

        $subject = $this->language->get('text_token_expired_subject');
        $message = $this->language->get('text_token_expired_message');

        $mail->setTo($this->config->get('config_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText(strip_tags($message));
        $mail->setHtml($message);
        $mail->send();
    }

    public function tokenRevokedEmail() {
        if (!$this->checkTokenIssueEmailFrequency('squareup_token_revoked_last_mail', $this->config->get('squareup_token_revoked_mail_frequency'), true)) {
            return;
        }

        if (VERSION >= '2.0.0.0' && VERSION < '2.0.2.0') {
            $mail = new Mail($this->config->get('config_mail'));
        } else {
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');

            if (VERSION >= '2.0.2.0') {
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            } else {
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');
            }
        }

        $subject = $this->language->get('text_token_revoked_subject');
        $message = $this->language->get('text_token_revoked_message');

        $mail->setTo($this->config->get('config_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText(strip_tags($message));
        $mail->setHtml($message);
        $mail->send();
    }

    public function expirationEmail($expirations) {
        if (empty($expirations['expiring_authorized_transactions']) && empty($expirations['expired_authorized_transactions'])) {
            return;
        }

        if (VERSION >= '2.0.0.0' && VERSION < '2.0.2.0') {
            $mail = new Mail($this->config->get('config_mail'));
        } else {
            $mail = new Mail();
            $mail->protocol = $this->config->get('config_mail_protocol');
            $mail->parameter = $this->config->get('config_mail_parameter');

            if (VERSION >= '2.0.2.0') {
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
            } else {
                $mail->hostname = $this->config->get('config_smtp_host');
                $mail->username = $this->config->get('config_smtp_username');
                $mail->password = $this->config->get('config_smtp_password');
                $mail->port = $this->config->get('config_smtp_port');
                $mail->timeout = $this->config->get('config_smtp_timeout');
            }
        }

        $br = '<br />';

        $subject = $this->language->get('text_cron_expiration_subject');

        $message = '';

        if (!empty($expirations['expiring_authorized_transactions'])) {
            $message .= '<strong>' . $this->language->get('text_cron_expiration_message_expiring') . '</strong>' . $br . $br;

            $message .= '<table>';
            foreach ($expirations['expiring_authorized_transactions'] as $transaction) {
                $message .= '<tr>';
                $message .= '<td>' . $transaction['transaction_id'] . '</td>';
                $message .= '<td>| ' . sprintf($this->language->get('text_order_id'), $transaction['order_id']) . '</td>';
                $message .= '<td>| ' . $transaction['customer_name'] . '</td>';
                $message .= '<td>| <a href="' . $transaction['transaction_url'] . '" target="_blank">' . $this->language->get('text_view') . '</a></td>';
                $message .= '</tr>';
            }
            $message .= '</table>';

            $message .= $br . $br;
        }

        if (!empty($expirations['expired_authorized_transactions'])) {
            $message .= '<strong>' . $this->language->get('text_cron_expiration_message_expired') . '</strong>' . $br . $br;

            $message .= '<table>';
            foreach ($expirations['expired_authorized_transactions'] as $transaction) {
                $message .= '<tr>';
                $message .= '<td>' . $transaction['transaction_id'] . '</td>';
                $message .= '<td>| ' . sprintf($this->language->get('text_order_id'), $transaction['order_id']) . '</td>';
                $message .= '<td>| ' . $transaction['customer_name'] . '</td>';
                $message .= '<td>| <a href="' . $transaction['transaction_url'] . '" target="_blank">' . $this->language->get('text_view') . '</a></td>';
                $message .= '</tr>';
            }
            $message .= '</table>';

            $message .= $br . $br;
        }

        $mail->setTo($this->config->get('config_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setSender($this->config->get('config_name'));
        $mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
        $mail->setText(strip_tags($message));
        $mail->setHtml($message);
        $mail->send();
    }
}
