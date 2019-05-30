<?php

namespace App\DataSources;

class FieldMapper {
    private $index_mappings;

    public function __construct() {
      $this->index_mappings = [];
    }
    
    /**
     * load
     *
     * @param IndexMapping[] $index_mappings
     */
    public function load(array $index_mappings) {
      foreach($index_mappings as $index_mapping) {
        if(!$index_mapping instanceof IndexMapping) {
          throw new Exception('Array passed in to FieldMapper#__construct should be of type IndexMapping[]');
        }

        $this->index_mappings[$index_mapping->field_name] = $index_mapping->index;
      }
    }

    public function load_columns(array $fields) {
      $index_mappings = [];
      
      $index = 0;
      
      foreach($fields as $field) {
        array_push($index_mappings, new IndexMapping($index++, $field));
      }
  
      $this->load($index_mappings);
    }
    
    /**
     * get_field
     *
     * @param string[] $fields
     * @param string $field_name
     * @return string || null
     */
    function get_field(array $fields, string $field_name) {
      if(array_key_exists($field_name, $this->index_mappings)) {
        $field_index = $this->index_mappings[$field_name];
        $field_value = $fields[$field_index];
        return $field_value === '' ? null : $field_value;
      } else {
        return null;
      }
    }
}

class IndexMapping {
  public $index;
  public $field_name;

  public function __construct($index, $field_name) {
    $this->index = $index;
    $this->field_name = $field_name;
  }
}