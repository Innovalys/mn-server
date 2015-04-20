<?php
/**
 * Action router
 * @package MangaNetwork
 */


/**
* Manga Network action rule. This action rule is used inside the action router which
* will select the first rule to match, and performs its action with the current context
*/
class MnActionRule {
	
	/**
	 * @var string The Regex that should match the URI
	 */
	private $regex;

	/**
	 * @var string The HTTP method (get|post|put|delete)
	 */
	private $method;

	/**
	 * @var string[] The credentials needed for the page
	 */
	private $credentials;

	/**
	 * @var string[] The list of capture groups
	 */
	private $captureGroups;

	/**
	 * @var callable The action to performs if the rule match
	 */
	private $action;

	/**
	 * Constructor for action rule.
	 *
	 * @param string $regex The regex that should match the URI. Capturing groups used here could be
	 * retrieved by providing capture group entry
	 * @param string $method The HTTP method that can be used for this rule (get|post|put|delete)
	 * @param int $credentials A bitfield describing the credentials needed to display the page. 
	 * MnUser::None means no credentials are needed
	 * @param string[] $captureGroups The list of groups to be captured in the regex URI
	 * @param callable $action Action to be performed when the rule match. This action can have an optional
	 * argument which is the request's context
	 */
	function __construct($regex, $method, $credentials, $captureGroups, $action) {
		$this->regex = $regex;
		$this->method = $method;
		$this->credentials = $credentials;
		$this->captureGroups = $captureGroups;
		$this->action = $action;
	}

	/**
	 * Test if the current context match the rule. In case of match, capturing groups will
	 * be added to the parameters using the rule's capturing groups' names.
	 *
	 * @param \MnContext $context The request context
	 *
	 * @return bool True if the rule match, false otherwise
	 */
	function match($context) {
		$data = [];

		// Test URI
		if(!preg_match($this->regex, $context->uri, $data))
			return false;

		// Test HTTP method
		if($this->method != $context->method)
			return false;

		// Test credentials
		if ($this->credentials != MnUser::NONE) {
			if ($context->user == null)
				return false;
			else if ($this->credentials != ($this->credentials & $context->user->credentials))
				return false;
		}

		// Set the capturing groups elements
		array_shift($data);
		foreach ($this->captureGroups as $captureGroup) {
			if (empty($data)) // In case more capture group where provided
				break;

			$context->params[$captureGroup] = array_shift($data);
		}

		return true; // All tests passed
	}

	/**
	 * Performs the action with the given context
	 *
	 * @param MnContext $context The request context
	 *
	 * @return void
	 */
	function doAction($context) {
		$this->action->__invoke($context);
	}

}

/**
* Action router. This class will redirect requests using the provided rules and
* request's context.
*/
class MnActionRouter {

	/**
	 * @var \MnActionRule[] The rules contained in the router
	 */
	private $rules = [];
	
	/**
	 * Constructor of Manga Network Action Router
	 */
	function __construct() { }

	/**
	 * Add a rule to the router.
	 *
	 * @param \MnActionRule $rule The new rule to add
	 *
	 * @return void
	 */
	function addRule($rule) {
		$this->rules[] = $rule;
	}

	/**
	 * Add a rule to the router.
	 *
	 * @param MnContext $context The request context
	 *
	 * @return void
	 */
	function route($context) {
		foreach ($this->rules as $rule) {
			if($rule->match($context)) {
				$rule->doAction($context);
				return;
			}
		}

		throw new MnException('Error 404', 404);
	}

}

?>
