<?php
/**
 * Manga Network validator
 * @package MangaNetwork
 */

include_once 'exception.php';

/**
 * Validator class. This class is used to validate associative array (usually $_POST or $_GET values)
 * using predefined rules that can be be set using the addRule method
 */
class MnValidator {
	
	/**
	 * @var \MnValidatorRule[] The list rules, by names
	 */
	private $rules = [];

	/**
	 * @var mixed[] The validated values, by name
	 */
	private $validatedValue = [];

	/**
	 * Empty constructor
	 */
	function __construct() { }

	/**
	 * Add a rule to the validator. See the MnValidationRule for
	 * more information
	 * @param string $name The name of the value to use with the given rule
	 * @param MnValidatorRule $rule The rule to use with the given name
	 * @return void
	 */
	function addRule($name, $rule) {
		$this->rules[$name] = $rule;
	}

	/**
	 * Validate the given data using the previously registered names and
	 * rules. This method will throw an exception if any required value
	 * is invalidated. In case of success, the previously validated data
	 * wil be overwritten 
	 * @param  mixed[] $data The data to validate. This data can be any enumerable element
	 * @return void
	 * @throws \MnException Thrown if any required value is invalidated
	 */
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

	/**
	 * Return the validated result. You must first run the validate methods to generate results
	 * @return mixed[] The validated value. Any optional value not validated will be set to NULL
	 */
	function getValidatedValues() {
		return $this->validatedValue;
	}
}

/**
 * Validator rule. This rule is used to know if any value is good to be used
 * using a set of simple yet powerfull tests
 */
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

	/**
	 * Empty private constructor
	 */
	private function __construct() {}

	/**
	 * Create a rule for a required string
	 * @param  string  $regex     The regex that the string must match
	 * @param  integer $maxLenght The max lenght of the string
	 * @param  integer $minLenght The min lenght of the string
	 * @param  function  $check     User defined test
	 * @return void
	 */
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

	/**
	 * Create a rule for a required number
	 * @param  integer $minValue The minimum value of the number
	 * @param  integer $maxValue The maximum value of the number
	 * @param  function  $check     User defined test
	 * @return void
	 */
	static function requiredNumber($minValue=false, $maxValue=false, $check=NULL) {
		$rule = new MnValidatorRule();
		$rule->required = true;
		$rule->minValue = $minValue;
		$rule->maxValue = $maxValue;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_NUMBER;

		return $rule;
	}

	/**
	 * Create a rule for an required boolean
	 * @param  function  $check     User defined test
	 * @return void
	 */
	static function requiredBoolean($check=NULL) {
		$rule = new MnValidatorRule();
		$rule->required = true;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_BOOL;

		return $rule;
	}

	/**
	 * Create a rule for an optional string
	 * @param  string  $regex     The regex that the string must match
	 * @param  integer $maxLenght The max lenght of the string
	 * @param  integer $minLenght The min lenght of the string
	 * @param  function  $check     User defined test
	 * @return void
	 */
	static function optionalString($regex=NULL, $maxLenght=0, $minLenght=0, $check=NULL) {
		$rule = new MnValidatorRule();
		$rule->regex = $regex;
		$rule->maxLenght = $maxLenght;
		$rule->minLenght = $minLenght;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_STRING;

		return $rule;
	}

	/**
	 * Create a rule for an optional number
	 * @param  integer $minValue The minimum value of the number
	 * @param  integer $maxValue The maximum value of the number
	 * @param  function  $check     User defined test
	 * @return void
	 */
	static function optionalNumber($minValue=false, $maxValue=false, $check=NULL) {
		$rule = new MnValidatorRule();
		$rule->minValue = $minValue;
		$rule->maxValue = $maxValue;
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_NUMBER;

		return $rule;
	}

	/**
	 * Create a rule for an optional boolean
	 * @param  function  $check     User defined test
	 * @return void
	 */
	static function optionalBoolean($check=NULL) {
		$rule = new MnValidatorRule();
		$rule->check = $check;
		$rule->type = MnValidatorRule::TYPE_BOOL;

		return $rule;
	}

	/**
	 * Check if a string is valid
	 * @param  string $name The value name
	 * @param  string $val  The value
	 * @return string       The validated string or NULL
	 */
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

	/**
	 * Check if a boolean is valid
	 * @param  string $name The value name
	 * @param  bool $val  The value
	 * @return bool       The validated boolean or NULL 
	 */
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

	/**
	 * Check if an int is valid
	 * @param  string $name The value name
	 * @param  int $val  The value
	 * @return int       The validated int or NULL 
	 */
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

	/**
	 * Check if a value if valid
	 * @param  string $name   The value's name
	 * @param  mixed[] $values The array of value
	 * @return mixed The validated value
	 */
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