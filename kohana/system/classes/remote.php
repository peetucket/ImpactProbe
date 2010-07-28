<?php defined('SYSPATH') or die('No direct script access.');

class Remote extends Kohana_Remote {

        /**
	 * Returns the output of a remote URL. Any [curl option](http://php.net/curl_setopt)
	 * may be used.
	 *
	 *     // Do a simple GET request
	 *     $data = Remote::get($url);
	 *
	 *     // Do a POST request
	 *     $data = Remote::get($url, array(
	 *         CURLOPT_POST       => TRUE,
	 *         CURLOPT_POSTFIELDS => http_build_query($array),
	 *     ));
	 *
	 * @param   string   remote URL
	 * @param   array    curl options
	 * @return  string
	 * @throws  Kohana_Exception
	 */
	public static function get($url, array $options = NULL)
	{
		if ($options === NULL)
		{
			// Use default options
			$options = Remote::$default_options;
		}
		else
		{
			// Add default options
			$options = $options + Remote::$default_options;
		}

		// The transfer must always be returned
		$options[CURLOPT_RETURNTRANSFER] = TRUE;

		// Open a new remote connection
		$remote = curl_init($url);

		// Set connection options
		if ( ! curl_setopt_array($remote, $options))
		{
			throw new Kohana_Exception('Failed to set CURL options, check CURL documentation: :url',
				array(':url' => 'http://php.net/curl_setopt_array'));
		}

		// Get the response
		$response = curl_exec($remote);

		// Get the response information
		$code = curl_getinfo($remote, CURLINFO_HTTP_CODE);

		if ($code < 200 OR $code > 299)
		{
			$error = $response;
		}
		elseif ($response === FALSE)
		{
			$error = curl_error($remote);
		}

		// Close the connection
		curl_close($remote);
                
                // CUSTOM/EDITED:
                if($code == 403) 
                {
                	return "REMOTE_FORBIDDEN: 403";
                }
		elseif (isset($error))
		{
			return "REMOTE_ERROR: $code"; 
			//throw new Kohana_Exception('Error fetching remote :url [ status :code ] :error',
			//	array(':url' => $url, ':code' => $code, ':error' => $error));
		}

		return $response;
	}

}
