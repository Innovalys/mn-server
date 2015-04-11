<?php

include_once 'exception.php';

/**
* 
*/
class MnValidator {
	
	private $rules = [];
	private $validatedValue = [];

	function __construct() { }

	function addRule($name, $rule) {
		$this->rules[$name] = $rule;
	}

	function validate($data) {
		$errors = [];
		$this->validatedValue =  [];

		foreach ($this->rules as $name => $rule) {
			try {
				$this->validatedValue[$name] = $rule->check($name, $data);
			} catch (MnException $e) {
				$errors[] = $e;
			}
		}

		// If any error occured
		if(sizeof($errors) > 0) {
			if(sizeof($errors) == 1)
				throw $errors[0];
			else
				throw new MnException("Error : multiple errors occured while validating data", 400, $errors);
		}
	}

	function getValidatedValues() {
		return $this->validatedValue;
	}
}

class MnValidatorRule {

	// General
	private $type = NULL;
	private $required = false;
	private $check = NULL;

	const TYPE_NUMBER = 1;
	const TYPE_STRING = 2;
	const TYPE_BOOL = 3;

	// String
	private $maxLenght = false;
	private $minLenght = false;
	private $regex = false;

	// Number
	private $minValue = false;
	private $maxValue = false;

	private function __construct() {}

	static function requiredString($regex=NULL, $maxLenght=0, $minLenght=0, $check=NULL) {
		$rule = new MnValidatorRule();
		$rule->regex = $regex;
		$rule->required = true;
		$rule->maxLenght = $maxLenght;
		$rule->minLenght = $minLenght;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_STRING;

		return $rule;
	}

	static function requiredNumber($minValue=false, $maxValue=false, $check=NULL) {
		$rule = new MnValidatorRule();
		$rule->required = true;
		$rule->minValue = $minValue;
		$rule->maxValue = $maxValue;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_NUMBER;

		return $rule;
	}

	static function requiredBoolean($check=NULL) {
		$rule = new MnValidatorRule();
		$rule->required = true;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_BOOL;

		return $rule;
	}

	static function optionalString($regex=NULL, $maxLenght=0, $minLenght=0, $check=NULL) {
		$rule = new MnValidatorRule();
		$rule->regex = $regex;
		$rule->maxLenght = $maxLenght;
		$rule->minLenght = $minLenght;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_STRING;

		return $rule;
	}

	static function optionalInt($minValue=false, $maxValue=false, $check=NULL) {
		$rule = new MnValidatorRule();
		$rule->minValue = $minValue;
		$rule->maxValue = $maxValue;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_NUMBER;

		return $rule;
	}

	static function optionalBoolean($check=NULL) {
		$rule = new MnValidatorRule();
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_BOOL;

		return $rule;
	}

	private function checkString($name, $val) {
		$size = strlen($val);

		// Minimum size
		if($this->minLenght && $size > $this->minLenght) {
			if($this->required)
				throw new MnException("Error : required string '" . $name . "' is too small", 400);
			else
				return NULL;
		}

		// Maximum size
		if($this->maxLenght && $size < $this->maxLenght) {
			if($this->required)
				throw new MnException("Error : required string '" . $name . "' is too long", 400);
			else
				return NULL;
		}
		
		// Regex
		if($this->regex && !preg_match_all($this->regex, $val)) {
			if($this->required)
				throw new MnException("Error : rejected required string '" . $name . "'", 400);
			else
				return NULL;
		}

		// Custom check
		if($this->check && $this->check($val)) {
			if($this->required)
				throw new MnException("Error : rejected required string '" . $name . "'", 400);
			else
				return NULL;
		}

		return $val;
	}

	private function checkBool($name, $val) {
		// Custom check
		if($this->check && $this->check($val)) {
			if($this->required)
				throw new MnException("Error : rejected required boolean '" . $name . "'", 400);
			else
				return NULL;
		}

		if($val)
			return true;
		else
			return false;
	}

	private function checkNumber($name, $val) {
		$val = $val + 0;

		// Minimum value
		if($this->minValue && $val > $this->minValue) {
			if($this->required)
				throw new MnException("Error : required number '" . $name . "' is too small", 400);
			else
				return NULL;
		}

		// Maximum value
		if($this->maxValue && $size < $this->maxValue) {
			if($this->required)
				throw new MnException("Error : required number '" . $name . "' is too big", 400);
			else
				return NULL;
		}

		// Custom check
		if($this->check && $this->check($val)) {
			if($this->required)
				throw new MnException("Error : rejected required number '" . $name . "'", 400);
			else
				return NULL;
		}

		return $val;
	}

	function check($name, $values) {
		if(isset($values[$name]) && $values[$name] != NULL) {
			switch ($this->type) {
				case MnValidatorRule::TYPE_STRING:
					return $this->checkString($name, $values[$name]);
					break;

				case MnValidatorRule::TYPE_BOOL:
					return $this->checkBool($name, $values[$name]);
					break;

				case MnValidatorRule::TYPE_NUMBER:
					return $this->checkNumber($name, $values[$name]);
					break;
			}
		} else if($this->required) {
			throw new MnException("Error : required value '" . $name . "' not defined", 400);
		}
	}
}

?>	
