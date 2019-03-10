<?php

namespace App\DataSources\Ballotpedia;

class DistrictIdentityGenerator {
  public function generate($district_name) {
    if($district_name === null) {
      return '';
    }

    // TODO: refactor district identifier into more extensible code
    $regex = '';

    if(strpos($district_name, "Alaska State Senate") !== false) {
        $regex = '/District ([a-zA-Z])/';
    } else {
        $regex = '/District ([\d]+)|Circuit Place ([\d]+)/';
    }
    
    $match_count = preg_match_all($regex, $district_name, $matches, PREG_SET_ORDER);
    
    if($match_count == 0 || $match_count == false) {
        return null;
    }

    $id = $matches[0][1];

    return $id;
}
}