<?php
/**
 * This class is used for basic validation functions
 *
 * @author  WIM
 * @version Release: $Id:$
 */
class Validator
{
    /**
     * Check if a string is not empty
     *
     * @param string $string The string that needs to be checked
     *
     * @return boolean
     * @access public
     * @author WIM
     */
    public function notEmptyString($string = '')
    {
        return !empty($string) && is_string($string);
    }

    /**
     * Check if a object is not empty
     *
     * @param object $object The object that needs to be checked
     *
     * @return boolean
     * @access public
     * @author WIM
     */
    public function notEmptyObject($object = '')
    {
        return !empty($object) && is_object($object);
    }
}
