<?php

namespace vendor\squareup;

class Currency extends Registry {
    public function __construct($registry) {
        parent::__construct($registry);

        if (!$this->registry->has('currency')) {
            if (version_compare(VERSION, '2.2.0.0', '>=')) {
                $currency = new \Cart\Currency($this->registry);
            } else {
                $currency = new \Currency($this->registry);
            }

            $this->registry->set('currency', $currency);
        }
    }

    public function lowestDenomination($value, $currency) {
        $power = $this->currency->getDecimalPlace($currency);

        $value = (float)$value;

        return (int)($value * pow(10, $power));
    }

    public function standardDenomination($value, $currency) {
        $power = $this->currency->getDecimalPlace($currency);

        $value = (int)$value;

        return (float)($value / pow(10, $power));
    }
}