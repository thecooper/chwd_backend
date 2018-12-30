<?php

namespace App\BusinessLogic\Models;

use \Exception;

class Address {
    public $address_line_1;
    public $address_line_2;
    public $city;
    public $state;
    public $zip;
    public $county;

    public function load($fields) {
        if(array_key_exists('address_line_1', $fields)) {
            $this->address_line_1 = $fields['address_line_1'];
        }

        if(array_key_exists('address_line_2', $fields)) {
            $this->address_line_2 = $fields['address_line_2'];
        }

        if(array_key_exists('city', $fields)) {
            $this->city = $fields['city'];
        }

        if(array_key_exists('state', $fields)) {
            $this->state = $fields['state'];
        }

        if(array_key_exists('zip', $fields)) {
            $this->zip = $fields['zip'];
        } else {
            throw new Exception('Address class must have a zip field');
        }

        if(array_key_exists('county', $fields)) {
            $this->county = $fields['county'];
        }
    }

    /**
     * @return string[]
     */
    public function explode() {
        return [
            $this->address_line_1 ?? '',
            $this->address_line_2 ?? '',
            $this->city ?? '',
            $this->state ?? '',
            $this->zip ?? '',
            $this->county ?? ''
        ];
    }
}