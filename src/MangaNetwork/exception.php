<?php
/**
 * Manga Network exception
 * @package MangaNetwork
 */

/**
 * Define a custom exception class. This exception must be used if any error
 * interrupt the creation of the request, and will be handled outside of the
 * action
 */
class MnException extends Exception implements JsonSerializable {

    private $data;

	/**
	 * The Manga Network exception
	 *
	 * @param string $message The string message to display
	 * @param int $code The error code, also used as the HTTP return code
	 */
    public function __construct($message, $code = 200, $data = NULL) {
        parent::__construct($message, $code, NULL);
        $this->data = $data;
    }

    /**
     * Set the elements of the exception into a JSONable format
     *
     * @return string[] Array containing all the exception informations
     */
    public function jsonSerialize() {
        if($this->data) 
           return [ "message" => $this->message, "code" => $this->code, "data" => $this->data ];
        else
    	   return [ "message" => $this->message, "code" => $this->code ];
    }
}

?>