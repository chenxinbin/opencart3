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
    }

    public function createTables() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "squareup_transaction` (
          `squareup_transaction_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
          `transaction_id` char(40) NOT NULL,
          `merchant_id` char(32) NOT NULL,
          `location_id` varchar(32) NOT NULL,
          `order_id` int(11) NOT NULL,
          `transaction_type` char(20) NOT NULL,
          `transaction_amount` decimal(15,2) NOT NULL,
          `transaction_currency` char(3) NOT NULL,
          `billing_address_city` char(100) NOT NULL,
          `billing_address_company` char(100) NOT NULL,
          `billing_address_country` char(3) NOT NULL,
          `billing_address_postcode` char(10) NOT NULL,
          `billing_address_province` char(20) NOT NULL,
          `billing_address_street` char(100) NOT NULL,
          `device_browser` char(255) NOT NULL,
          `device_ip` char(15) NOT NULL,
          `created_at` char(29) NOT NULL,
          `is_refunded` tinyint(1) NOT NULL,
          `refunded_at` varchar(29) NOT NULL,
          `tenders` text NOT NULL,
          `refunds` text NOT NULL,
          PRIMARY KEY (`squareup_transaction_id`),
          KEY `order_id` (`order_id`),
          KEY `transaction_id` (`transaction_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "squareup_token` (
         `squareup_token_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
         `oc_customer_id` int(11) NOT NULL,
         `token` char(40) NOT NULL,
         `sandbox` tinyint(1) NOT NULL,
         `date_added` datetime NOT NULL,
          `brand` VARCHAR(32) NOT NULL,
         `ends_in` VARCHAR(4) NOT NULL,
         PRIMARY KEY (`squareup_token_id`),
         KEY `oc_customer_id` (`oc_customer_id`),
         KEY `card_exists` (`oc_customer_id`, `brand`, `ends_in`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "squareup_customer` (
         `oc_customer_id` int(11) NOT NULL,
         `square_customer_id` varchar(32) NOT NULL,
         `squareup_token_id` int(11) unsigned NOT NULL,
         `sandbox` tinyint(1) NOT NULL,
         PRIMARY KEY (`oc_customer_id`, `sandbox`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        if (version_compare(VERSION, '2.0.0.0', '>=')) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` ADD INDEX `squareup_reference` (`reference`)");
        } else if (version_compare(VERSION, '1.5.6', '>=')) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` ADD INDEX `squareup_reference` (`profile_reference`)");
        }
    }

    public function dropTables() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "squareup_transaction`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "squareup_token`");
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "squareup_customer`");

        if (version_compare(VERSION, '1.5.6', '>=')) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "order_recurring` DROP INDEX `squareup_reference`");
        }
    }

    public function deleteTokens($customer_id) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "squareup_token` WHERE customer_id='" . (int)$customer_id . "'");
    }

    public function getTransaction($squareup_transaction_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_transaction` WHERE squareup_transaction_id='" . (int)$squareup_transaction_id . "'")->row;
    }

    public function getTransactionByOrderId($order_id) {
        return $this->db->query("SELECT * FROM `" . DB_PREFIX . "squareup_transaction` WHERE order_id='" . (int)$order_id . "'")->row;
    }

    public function getTransactions($data) {
        $sql = "SELECT * FROM `" . DB_PREFIX . "squareup_transaction`";

        if (isset($data['order_id'])) {
            $sql .= " WHERE order_id='" . (int)$data['order_id'] . "'";
        }

        $sql .= " ORDER BY created_at DESC";

        if (isset($data['start']) && isset($data['limit'])) {
            $sql .= " LIMIT " . $data['start'] . ', ' . $data['limit'];
        }

        return $this->db->query($sql)->rows;
    }

    public function getTotalTransactions($data) {
        $sql = "SELECT COUNT(*) as total FROM `" . DB_PREFIX . "squareup_transaction`";

        if (isset($data['order_id'])) {
            $sql .= " WHERE order_id='" . (int)$data['order_id'] . "'";
        }

        return $this->db->query($sql)->row['total'];
    }

    public function findTransactionId($order_id) {
        $response = $this->api('order/' . $order_id, array(), 'GET');

        if (!empty($response['result']) && $response['result'] == 'SUCCESS' && !empty($response['transaction'])) {
            $max = 0;

            foreach ($response['transaction'] as $transaction) {
                if ((int)$transaction['transaction']['id'] > $max) {
                    $max = (int)$transaction['transaction']['id'];
                }
            }

            return $max + 1;
        }

        return 0;
    }

    public function refundTransaction($transactionId, $reason, $amount) {
        $transaction = $this->getTransaction($transactionId);
        $endpoint = $this->config->get($this->imodule.'_endpoint_refund');
        $endpoint = str_replace('%location%', $transaction['location_id'], $endpoint);
        $endpoint = str_replace('%transactionId%', $transaction['transaction_id'], $endpoint);
        $tenders = json_decode($transaction['tenders'], true);

        $parameters = array(
            'idempotency_key' => uniqid(),
            'tender_id' => $tenders[0]['id'],
            'reason' => $reason,
            'amount_money' => array(
                'amount' => $this->squareupCurrency->lowestDenomination($amount, $transaction['transaction_currency']),
                'currency' => $transaction['transaction_currency']
            )
        );

        return $this->squareService->api('POST', $endpoint, $parameters, null, \vendor\squareup\Service::API_CONTENT_JSON, true);
    }

    public function captureTransaction($transactionId) {
        $transaction = $this->getTransaction($transactionId);
        $endpoint = $this->config->get($this->imodule.'_endpoint_capture');
        $endpoint = str_replace('%location%', $transaction['location_id'], $endpoint);
        $endpoint = str_replace('%transactionId%', $transaction['transaction_id'], $endpoint);

        return $this->squareService->api('POST', $endpoint, null, null, \vendor\squareup\Service::API_CONTENT_JSON, true);
    }

    public function voidTransaction($transactionId) {
        $transaction = $this->getTransaction($transactionId);
        $endpoint = $this->config->get($this->imodule.'_endpoint_void');
        $endpoint = str_replace('%location%', $transaction['location_id'], $endpoint);
        $endpoint = str_replace('%transactionId%', $transaction['transaction_id'], $endpoint);

        return $this->squareService->api('POST', $endpoint, null, null, \vendor\squareup\Service::API_CONTENT_JSON, true);
    }

    public function refreshTransaction($transactionId, $new_refund = array()) {
        $transaction = $this->getTransaction($transactionId);

        $endpoint = $this->config->get($this->imodule.'_endpoint_retrieve_transaction');
        $endpoint = str_replace('%location%', $transaction['location_id'], $endpoint);
        $endpoint = str_replace('%transactionId%', $transaction['transaction_id'], $endpoint);
        $result = $this->squareService->api('GET', $endpoint, null, null, \vendor\squareup\Service::API_CONTENT_JSON, true);

        $type = $result['transaction']['tenders'][0]['card_details']['status'];
        $return_type = strtolower($type);

        $refunds = array();

        if (!empty($result['transaction']['refunds'])) {
            $refunds = $result['transaction']['refunds'];
        }

        if (!empty($new_refund)) {
            $found = false;
            foreach ($refunds as $refund) {
                if ($refund['id'] == $new_refund['id']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $refunds[] = $new_refund;
            }
        }

        $refunded_amount = 0;
        $has_pending = false;
        foreach ($refunds as $refund) {
            if ($refund['status'] == 'REJECTED' || $refund['status'] == 'FAILED') {
                continue;
            }

            if ($refund['status'] == 'PENDING') {
                $has_pending = true;
            }

            $refunded_amount += $refund['amount_money']['amount'];
        }

        if (!$has_pending && !empty($refunds)) {
            if ($refunded_amount == $this->squareupCurrency->lowestDenomination($transaction['transaction_amount'], $transaction['transaction_currency'])) {
                $return_type = 'fully_refunded';
            } else {
                $return_type = 'partially_refunded';
            }
        }

        $this->db->query("UPDATE `" . DB_PREFIX . "squareup_transaction` SET transaction_type='" . $this->db->escape($type) . "', is_refunded='" . (int)!empty($refunds) . "', refunds='" . $this->db->escape(json_encode($refunds)) . "' WHERE squareup_transaction_id='" . (int)$transactionId . "'");

        return array(
            'transaction_status' => $return_type,
            'order_id' => $transaction['order_id']
        );
    }

    public function addOrderHistory($order_id, $transaction_status, $comment) {
        if ($transaction_status) {
            $order_status_id = $this->config->get($this->imodule . '_status_' . $transaction_status);
        } else {
            $order_status_id = $this->getOrderStatusId($order_id);
        }

        if ($order_status_id) {
            $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . (int)$order_status_id . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");

            $this->db->query("INSERT INTO " . DB_PREFIX . "order_history SET order_id = '" . (int)$order_id . "', order_status_id = '" . (int)$order_status_id . "', notify = '0', comment = '" . $this->db->escape($comment) . "', date_added = NOW()");
        }
    }

    public function getOrderStatusId($order_id) {
        return $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id='" . (int)$order_id . "'")->row['order_status_id'];
    }

    public function editOrderRecurringStatus($order_recurring_id, $status) {
        $this->db->query("UPDATE `" . DB_PREFIX . "order_recurring` SET `status` = '" . (int)$status . "' WHERE `order_recurring_id` = '" . (int)$order_recurring_id . "'");
    }
}