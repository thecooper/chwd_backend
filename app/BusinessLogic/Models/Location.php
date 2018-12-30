<?php

namespace App\BusinessLogic\Models;

class Location extends Address {
    public $congressional_district;
    public $state_legislative_district;
    public $state_house_district;

    public function load_address(Address $address) {
        $this->address_line_1 = $address->address_line_1;
        $this->address_line_2 = $address->address_line_2;
        $this->city = $address->city;
        $this->state = $address->state;
        $this->zip = $address->zip;
        $this->county = $address->county;
    }

    public function load_districts(Districts $districts) {
        $this->congressional_district = $districts->congressional_district;
        $this->state_legislative_district = $districts->state_legislative_district;
        $this->state_house_district = $districts->state_house_district;
    }
}