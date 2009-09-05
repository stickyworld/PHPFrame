<?php
/**
 * PHPFrame/Utils/Filter.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Utils
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Filter Class
 * 
 * This class requires that PHPs filter functions are available.
 * 
 * More info about PHP Filter:
 * http://uk.php.net/manual/en/book.filter.php
 * 
 * @category PHPFrame
 * @package  Utils
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since    1.0
 */
class PHPFrame_Utils_Filter
{
    /**
     * An array with the different types of filters
     * 
     * @staticvar array
     */
    private static $_types=array("default", 
                                 "int", 
                                 "boolean", 
                                 "float", 
                                 "regexp", 
                                 "url", 
                                 "email", 
                                 "ip");
    
    /**
     * Validate data stored in variable
     * 
     * <code>
     *  $validEmail = PHPFrame_Utils_Filter::validate($email, 'email');
     *  // validEmail will contain either the filtered string or 
     *  // boolean FALSE if validation fails
     * </code>
     * 
     * @param int|bool|float|string|array $subject    A value os array of values 
     *                                                to be tested.
     * @param string                      $type       The filter type to use.
     *                                                Valid types are int, boolean,
     *                                                float, regexp, url, email, ip
     * @param array                       $options    The filter options array.
     * @param bool                        $exceptions Switch to indicate whether 
     *                                                we want to throw exceptions
     *                                                or simply return FALSE on 
     *                                                failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since 1.0
     */
    public static function validate(
        $subject, 
        $type='default', 
        $options=null, 
        $exceptions=true
    ) {
        // Make sure type is string and make lower case
        $type = (string) strtolower($type);
        // Check if filter type is supported
        if (!in_array($type, self::$_types)) {
            throw new RuntimeException("Filter type (".$type.") not supported.");
        }
        
        // Handle primitive value subject
        if (
            is_int($subject) 
            || is_bool($subject) 
            || is_float($subject) 
            || is_string($subject)
        ) {
            $subject = array($subject);
        }
        
        // Check that we are working with an array
        if (!is_array($subject)) {
            if ($exceptions) {
                $msg = "Wrong data type passed as 'subject' to ";
                $msg .= "PHPFrame_Utils_Filter::validate().";
                $msg .= " Expected (int|bool|float|string|array)";
                $msg .= " and got (".gettype($subject).")";
                throw new RuntimeException($msg);
            } 
            return false;
        }
        
        // Make filter constant using passed type
        if ($type == 'default') {
            $filter = FILTER_DEFAULT;
        } else {
            $filter = "FILTER_VALIDATE_".strtoupper($type);
            eval("\$filter = $filter;");    
        }
        
        // Declare array to hold filtered data
        $filtered_array = array();
        
        // Loop through array and validate
        foreach ($subject as $var) {
            $filtered_var = filter_var($var, $filter, $options);
            if ($filtered_var === false) {
                if ($exceptions) {
                    $msg = "Wrong parameter type.";
            
                    $backtrace = debug_backtrace();
                    
                    if (is_array($backtrace)) {
                        foreach ($backtrace as $call) {
                            if ($call["class"] != "PHPFrame_Utils_Filter") {
                                break;
                            }
                        }
                    }
                    
                    $msg .= " ".$call["class"]."::".$call["function"]."() expected argument";
                    $msg .= " to be of type (".$type.") and got (".gettype($var).").";
                    
                    throw new RuntimeException($msg);
            
                }
                return false;
            }
            $filtered_array[] = $filtered_var;
        }
        
        // Validation passed, now return filtered data
        if (count($filtered_array) == 1) {
            // Return primitive value
            return $filtered_array[0];
        } else {
            // Return array of values
            return $filtered_array;
        }
    }
    
    /**
     * Validate using default filter
     * 
     * @param mixed $subject    The subject to be tested
     * @param bool  $exceptions Switch to indicate whether we want to throw
     *                          exceptions or simply return FALSE on failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateDefault($subject, $exceptions=true)
    {
        return self::validate($subject, "default", null, $exceptions);
    }
    
    /**
     * Validate using int filter
     * 
     * @param mixed $int        The subject to be tested
     * @param bool  $exceptions Switch to indicate whether we want to throw
     *                          exceptions or simply return FALSE on failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateInt($int, $exceptions=true)
    {
        return self::validate($int, "int", null, $exceptions);
    }
    
    /**
     * Validate using boolean filter
     * 
     * @param mixed $bool       The subject to be tested
     * @param bool  $exceptions Switch to indicate whether we want to throw
     *                          exceptions or simply return FALSE on failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateBoolean($bool, $exceptions=true)
    {
        return self::validate($bool, "boolean", null, $exceptions);
    }
    
    /**
     * Validate using float filter
     * 
     * @param mixed $float      The subject to be tested
     * @param bool  $exceptions Switch to indicate whether we want to throw
     *                          exceptions or simply return FALSE on failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateFloat($float, $exceptions=true)
    {
        return self::validate($float, "float", null, $exceptions);
    }
    
    /**
     * Validate a string or array of srings using a regular expresion
     * 
     * @param string|array $subject    The subject(s) to validate
     * @param string       $regexp     The regular expression
     * @param bool         $exceptions Switch to indicate whether we want to throw
     *                                 exceptions or simply return FALSE on failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateRegExp($subject, $regexp, $exceptions=true)
    {
        // Prepare array with filter options
        $options = array("options"=>array("regexp"=>$regexp));
        
        return self::validate($subject, "regexp", $options, $exceptions);
    }
    
    /**
     * Validate using URL filter
     * 
     * @param mixed $url        The subject to be tested
     * @param bool  $exceptions Switch to indicate whether we want to throw
     *                          exceptions or simply return FALSE on failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateURL($url, $exceptions=true)
    {
        return self::validate($url, "url", null, $exceptions);
    }
    
    /**
     * Validate using email filter
     * 
     * @param mixed $email      The subject to be tested
     * @param bool  $exceptions Switch to indicate whether we want to throw
     *                          exceptions or simply return FALSE on failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateEmail($email, $exceptions=true)
    {
        return self::validate($email, "email", null, $exceptions);
    }
    
    /**
     * Validate using IP filter
     * 
     * @param mixed $ip         The subject to be tested
     * @param bool  $exceptions Switch to indicate whether we want to throw
     *                          exceptions or simply return FALSE on failure.
     * 
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateIP($ip, $exceptions=true)
    {
        return self::validate($ip, "ip", null, $exceptions);
    }
    
    /**
     * Validate MySQL style datetime (YYYY-MM-DD HH:MM:SS)
     * 
     * @param mixed $datetime   The subject to be tested
     * @param bool  $exceptions Switch to indicate whether we want to throw
     *                          exceptions or simply return FALSE on failure.
     *                          
     * @static
     * @access public
     * @return mixed  Returns the filtered value(s) on success. Throws exception or 
     *                returns FALSE on failure depending on $exceptions argument.
     * @since  1.0
     */
    public static function validateDateTime($datetime, $exceptions=true)
    {
        $pattern = '/^([0-9]{4})-([0-1][0-9])-([0-3][0-9]) ([0-2][0-9]):([0-6][0-9]):([0-6][0-9])$/';
        return self::validateRegExp($datetime, $pattern, $exceptions);
    }
}
