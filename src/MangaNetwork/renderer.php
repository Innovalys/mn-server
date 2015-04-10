<?php
/**
 * Renderer
 * @package MangaNetwork
 */

/**
 * Renderer used to write the response in a formated way. The json_encode method
 * will by itself walk throw every elements of objets, so objects along with
 * arrays or natural values can be sended
 *
 * @param mixed $data The data to display. This data will be converted to JSON using
 * PHP's json_encode method
 * @param bool $error True if an error occurred, false otherwise
 * @param int $code The HTTP code of the request
 */
function render($data, $error=false, $code=200) {
	http_response_code($code);
	header('Content-Type: application/json');
	echo json_encode([ "error" => $error, "code" => $code, "data" => $data ]);
}

?>
