<?php 
/**
 * Uptrends PHP Wrapper
 *
 * LICENSE: This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 *
 * @author Youri van den Bogert <yvdbogert@archixl.nl>
 * @link http://www.xl-knowledge.nl
 * @version 09/12/2015 <First version, DELETE PUT POST methods NOT tested!>
 *
 */
class Uptrends {

	// Define some constants for later usage
	const METHOD_POST = 1;
	const METHOD_PUT = 2;
	const METHOD_GET = 3;
	const METHOD_DELETE = 4;

	// Rest service location
	const REST_SERVICE = 'https://api.uptrends.com/';
	const REST_VERSION = 'v3';

	/** @var boolean **/
	private $debug = false;

	/** @var int **/
	private $timeout = 30;

	/** @var string **/
	private $username;

	/** @var string **/
	private $password;

	/** @var string **/
	private $endpointUrl;

	public function __construct($options = null) {

		// Validate input
		if (!is_array($options)) {
			throw new Exception("You need to specify a username and password!");
		} elseif (empty($options['username']) || empty($options['password'])) {
			throw new Exception("Defined options should be: username, and password.");
		}

		// Set credentials
		$this->username = $options['username'];
		$this->password = $options['password'];

		// Set endpoint url
		$this->endpointUrl = self::REST_SERVICE . self::REST_VERSION . '/';

	}

	/**
	 * This function will handle all incoming function calls
	 * and transforms it to REST calls
	 *
	 * @param string $name
	 * @param null|array $arguments
	 * @throws Exception
	 * @return boolean|string
	 */
	public function __call($name, $arguments = null) {

		// Get operation name from $name
		$operation = $this->getOperationName($name);

		// Create the URL
		$url = $this->endpointUrl . $operation;

		// Get additional data
		$additional_data = $this->parseArguments($arguments);
			
		// Add url prefix if needed
		if (!empty($additional_data['prefix'])) {
				
			$url .= '/' . $additional_data['prefix'];
				
		}
		
		// Add URL parameters if necessary
		if (!empty($additional_data['parameters'])) {
			
			$url .= '?';
			$url .= $additional_data['parameters'];
			
		}

		// Get request
		if( strstr($name, 'get')) {
				
			$result = $this->getRestCall($url);
				
		// Handle post
		} elseif (strstr($name, 'post') || strstr($name, 'put')) {
				
			// Validate postfields
			if (!empty($additional_data['postfields'])) {

				if (strstr($name, 'post')) {
					$method = self::METHOD_POST;
				} else {
					$method = self::METHOD_PUT;
				}

				$result = $this->getRestCall($url, $additional_data['postfields'], $method);

			} else {

				throw new Exception("POST or PUT method requested, but no postfields have been configured.");

			}
				
		// Handle DELETE request
		} elseif (strstr($name, 'DELETE')) {
				
			$result = $this->getRestCall($url, null, self::METHOD_DELETE);

		// Unkown function called
		} else {
				
			throw new Exception("Invalid function called: $name");
				
		}

		return $result;

	}

	/**
	 * Parses the arguments received from the _call method
	 * Returns an array with: prefix, postfields, and arguments.
	 *
	 * @param array|null $arg
	 * @return array
	 */
	private function parseArguments($arg) {

		$prefix = null;
		$postfields = array();
		$urlParams = array();

		foreach($arg as $argKey => $argData) {
			if (is_array($argData)) {
				foreach($argData as $dataName => $dataVal) {
					if ($dataName == 'prefix') {
						$prefix = $dataVal;
					} elseif ($dataName == 'postfields') {
						$postfields = $dataVal;
					} elseif ($dataName == 'parameters') {
						$urlParams = http_build_query($dataVal);
					}
				}
			}
		}

		return array(
				'prefix' => $prefix,
				'postfields' => $postfields,
				'parameters' => $urlParams
		);

	}


	/**
	 * Gets the operation name from the argument name
	 * Renives get,post,delete and put from the string
	 *
	 * @param string $string
	 * @return string
	 */
	private function getOperationName($string) {

		$operation = str_replace('get', '', $string);
		$operation = str_replace('post', '', $operation);
		$operation = str_replace('delete', '', $operation);
		$operation = str_replace('put', '', $operation);

		return $operation;

	}

	/**
	 * Retrieve the result
	 *
	 * @param string $url
	 * @param null|array $data
	 * @param string $method
	 * @return Ambigous <NULL, mixed>
	 */
	private function getRestCall($url, $data = null, $method = self::METHOD_GET) {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);

		// Set authentication parameters
		curl_setopt($curl, CURLOPT_USERPWD, $this->username . ':' . $this->password);

		// Don't send as json when attaching files to tasks.
		if (is_string($data) || empty($data['file'])) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Send as JSON
		}

		// Dont print result
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		// Set maximum timeout limit
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_FAILONERROR, true);

		// Don't verify SSL connection
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

		// Define methods
		if ($method == self::METHOD_POST) {
			curl_setopt($curl, CURLOPT_POST, true);
		} elseif ($method == self::METHOD_PUT) {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
		} elseif ($method == self::METHOD_DELETE) {
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}

		// Define post data if we have the correct method
		if (!is_null($data) && ($method == self::METHOD_POST || $method == self::METHOD_PUT)) {
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}

		try {
				
			$return = curl_exec($curl);
				
			if ($this->debug) {

				$info = curl_getinfo($curl);

				echo '<pre>';
				print_r($info);
				echo '</pre>';

				if ($info['http_code'] == 0) {
					echo '<br>error num: ' . curl_errno($curl);
					echo '<br>error: ' . curl_error($curl);
				}

				if (!is_null($data)) {
					echo '<br>Sent info:<br><pre>';
					print_r($data);
					echo '</pre>';
				}

			}
				
		} catch (Exception $e) {
				
			$return = null;
				
			if ($this->debug) {
				echo "Exception caught: " . $e->getMessage();
			}
				
		}

		curl_close($curl);

		return $return;

	}

}
