<?php

namespace vendor\squareup;

class Exception extends \Exception {
    private $config;
    private $log;
    private $language;
    private $errors;
    private $curlError;

    private $overrideFields = array(
        'billing_address.country',
        'shipping_address.country',
        'email_address',
        'phone_number'
    );

    public function __construct($errors, $registry, $curlError=null) {
        $this->errors = $errors;
        $this->curlError = $curlError;

        $this->config = $registry->get('config');
        $this->log = $registry->get('log');
        $this->language = $registry->get('language');

        $message = $this->parseErrors($errors);

        if ($this->config->get('config_error_log')) {
            $this->log->write($message);
        }

        parent::__construct($message);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function curlError() {
        return ($this->curlError !== null)?$this->curlError:false;
    }

    private function parseErrors($errors) {
        $messages = array();

        if (is_array($errors)) {
            foreach ($errors as $error) {
                $messages[] = $this->parseError($error);
            }
        } else {
            $messages[] = $errors;
        }

        return implode(' ', array_map('strip_tags', $messages));
    }

    private function overrideError($field) {
        return $this->language->get('squareup_override_error_' . $field);
    }

    private function parseError($error) {
        if (!empty($error['field']) && in_array($error['field'], $this->overrideFields)) {
            return $this->overrideError($error['field']);
        }

        $message = $error['detail'];

        if (!empty($error['field'])) {
            $message .= ' Field: ' . $error['field'];
        }

        return $message;
    }
}