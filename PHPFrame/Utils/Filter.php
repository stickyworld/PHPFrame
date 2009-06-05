<?php
/**
 * @version		$Id$
 * @package		PHPFrame
 * @subpackage 	utils
 * @copyright	Copyright (C) 2009 E-noise.com Limited. All rights reserved.
 * @license		BSD revised. See LICENSE.
 */

/**
 * Filter Class
 * 
 * This class requires that PHPs filter functions are available.
 * 
 * More info about PHP Filter:
 * http://uk.php.net/manual/en/book.filter.php
 * 
 * @package		PHPFrame
 * @subpackage 	utils
 * @since 		1.0
 */
class PHPFrame_Utils_Filter {
	/**
	 * Validate data stored in variable
	 * 
	 * <code>
	 *  $validEmail = PHPFrame_Utils_Filter::validate($email, 'email');
	 *  //validEmail will contain either the filtered string or boolean FALSE if validation fails
	 * </code>
	 * 
	 * @param	mixed	$variable to be evaluated	
	 * @param	string	$type valid types are int, boolean, float, regexp, url, email, ip
	 * @return	mixed	Returns the filtered data, or FALSE if the filter fails
	 */
	static function validate($variable, $type='default') {
		// Check that passed type is recognised
		$allowed_types = array("default", "int", "boolean", "float", "regexp", "url", "email", "ip");
		if (!in_array($type, $allowed_types)) {
			throw new PHPFrame_Exception('PHPFrame Filter error: Data type not recognised by filter.');
		}
		
		// Make filter constant using passed type
		if ($type == 'default') {
			$filter = FILTER_DEFAULT;
		}
		else {
			$filter = "FILTER_VALIDATE_".strtoupper($type);
			eval("\$filter = $filter;");	
		}
		
		return filter_var($variable, $filter);
	}
}
