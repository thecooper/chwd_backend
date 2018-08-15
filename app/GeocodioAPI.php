<?php

namespace App;

class GeocodioAPI
{
    private const api_key = "ba4b524e5bbe5de46652a5bb884eada65d521d8";

    public static function get_geolocation_information($address_line_1, $address_line_2, $state_abbreviation, $city, $zip) {
        $request_url_base = "http://api.geocod.io/v1.3/geocode";
        
        $full_address = "$address_line_1 $address_line_2 {$city}, $state_abbreviation $zip";

        $optional_fields = "school,cd,stateleg";

        $request_url_query_params = array(
            "api_key" => static::api_key,
            "q" => urlencode($full_address),
            "fields" => urlencode($optional_fields)
        );

        $full_request = $request_url_base . "?" . implode("=", $request_url_query_params);

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

            curl_close($ch);
        } catch (Exception $ex) {
            throw $ex;
        }
        
        if($response_code == 200) {
            $json_response = json_decode($curl_result)->results;
            var_dump($json_response);
            $data_fields = $json_response->fields;
    
            $congressional_districts = $data_fields->congressional_districts;
            $state_legislative_districts = $data_fields->state_legislative_districts;
    
            if(count($congressional_districts) == 1) {
                $congressional_district = $congressional_districts[0]->district_number;
            } elseif(count($congressional_districts) > 1) {
                throw new \Exception("More than one congressional district has been found for the given address");
            } else {
                throw new \Exception("No congressional districts were found for the given address");
            }
    
            if(count($state_legislative_districts) == 1) {
                $state_legislative_district = $state_legislative_districts[0]->district_number;
            } elseif(count($state_legislative_districts) > 1) {
                throw new \Exception("More than one legislative district has been found for the given address");
            } else {
                throw new \Exception("No legislative districts were found for the given address");
            }
    
            return array($congressional_district, $state_legislative_district);
        } else {
            return array("error" => "response from the server was not good: " . $response_code);
            // TODO: present error if not 200 response code
        }
    }
}
