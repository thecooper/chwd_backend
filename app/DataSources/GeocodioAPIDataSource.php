<?php

namespace App\DataSources;

use \Exception;
use App\Models\Address;
use App\Models\Districts;
use App\Models\Location;

class GeocodioAPIDataSource
{
    private $api_key;

    public function __construct() {
        $this->api_key = env('GEOCODIO_API_KEY', null);

        if($this->api_key == null) {
            throw new Exception("Geocodio API Key was not provided in configuration");
        }
    }

    /**
     * @param Address $address
     * @return Location | array - returns districs information based on address, or return array with error key
     */
    public function get_geolocation_information(Address $address) {
        $request_url_base = "http://api.geocod.io/v1.3/geocode";
        
        $address_parts = $address->explode();

        $full_address = implode(' ', $address_parts);
        $full_address_query = urlencode($full_address);
        $optional_fields = "school,cd,stateleg";
        $optional_fields_query = urlencode($optional_fields);

        $request_url_query_params = array(
            "api_key={$this->api_key}",
            "q=$full_address_query",
            "fields=$optional_fields_query"
        );

        $full_request = $request_url_base . "?" . implode("&", $request_url_query_params);

        $response_code = -1;
        
        try {
            $ch = curl_init($full_request);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $curl_result = curl_exec($ch);

            if($curl_result == false) {
                throw new \Exception("There was a problem getting your Geocodio data: " . curl_error($ch));
            }

            $curl_info = curl_getinfo($ch);

            $response_code = $curl_info['http_code'];

            if($response_code == 200) {
                $json_response = json_decode($curl_result)->results;
    
                $location = $this->process_response_json($address, $json_response);
                
                return $location;
            } else {
                $curl_error = curl_error($ch);
                return array("error" => "response from the server ($response_code) was not good: " . $curl_error);
            }
            
            curl_close($ch);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

    /**
     * @param mixed $response_json - the json resposne from the API endpoint that has already been json decoded
     * @return Location
     */
    private function process_response_json(Address $address, $response_json) {
        $districts = new Districts();
        $location = new Location();
        $selected_source = null;
        
        if(is_array($response_json)) {
            if(count($response_json) == 0) {
                return $districts;
            }
            $selected_source = $response_json[0];
        } else {
            $selected_source = $response_json;
        }

        $address_parts = $selected_source->address_components;
        $data_fields = $selected_source->fields;

        $address = $this->repopulate_address($address, $address_parts);
        
        $congressional_districts = $data_fields->congressional_districts;
        $state_legislative_districts = $data_fields->state_legislative_districts;

        if(count($congressional_districts) == 1) {
            $district_value = $congressional_districts[0]->district_number;
            $districts->congressional_district = $district_value == 0 ? null : $district_value;
        }

        if(isset($state_legislative_districts->senate)) {
            $districts->state_legislative_district = $state_legislative_districts->senate->district_number;
        }

        if(isset($state_legislative_districts->house)) {
            $districts->state_house_district = $state_legislative_districts->house->district_number;
        }

        $location->load_address($address);
        $location->load_districts($districts);

        return $location;
    }

    private function repopulate_address(Address $address, $address_components) {
        if(!empty($address_components->number) && !empty($address_components->formatted_street)) {
            $address->address_line_1 = trim("{$address_components->number} {$address_components->formatted_street}");
        }
        
        $address->city = $address_components->city;
        $address->state = $address_components->state;
        $address->zip = $address_components->zip;
        $address->county = $address_components->county;

        return $address;
    }
}
