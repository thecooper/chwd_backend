<?php

abstract class AbstractDataAdapter {
	abstract protected $data_model_class;
	abstract protected $data_model_fields;

	public function generateModel($input_array) {
		$data_model_fields = property_exists($data_model_class, $fields

		if(!is_array($data_model_fields)) {
			throw new Exception("field model_fields should be an array");
		}

		try {
			$data_model_instance = new $data_model_class();
		} catch (Exception $ex) {
			throw new Exception("Could not instantiate model of class {$data_model_class}", 0, $ex);
		}

		foreach ($data_model_fields as $field_name) {
			// Don't load id field because it likely won't exist
			if($field_name == "id") { continue; }

			if(array_key_exists($field_name, $input_array)) {
				$data_model_instance->$field_name = $input_array[$field_name];
			} else {
				// TODO: Log that this key was provided but not dealt with
			}
		}

		// TODO: figure out which data elements are supplied by $input_array that aren't stored

		return $data_model_instance;
	}
}