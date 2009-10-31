<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once 'PEAR.php';
require_once 'Util.php';

/**
* Object representation of a part of a LDAP filter.
*
* This Class is not completely compatible to the PERL interface!
*
* The purpose of this class is, that users can easily build LDAP filters
* without having to worry about right escaping etc.
* A Filter is built using several independent filter objects
* which are combined afterwards. This object works in two
* modes, depending how the object is created.
* If the object is created using the {@link create()} method, then this is a leaf-object.
* If the object is created using the {@link combine()} method, then this is a container object.
*
* LDAP filters are defined in RFC-2254 and can be found under
* {@link http://www.ietf.org/rfc/rfc2254.txt}
*
* Here a quick copy&paste example:
* <code>
* $filter0 = Net_LDAP_Filter::create('stars', 'equals', '***');
* $filter_not0 = Net_LDAP_Filter::combine('not', $filter0);
*
* $filter1 = Net_LDAP_Filter::create('gn', 'begins', 'bar');
* $filter2 = Net_LDAP_Filter::create('gn', 'ends', 'baz');
* $filter_comp = Net_LDAP_Filter::combine('or',array($filter_not0, $filter1, $filter2));
*
* echo $filter_comp->asString();
* // This will output: (|(!(stars=\0x5c0x2a\0x5c0x2a\0x5c0x2a))(gn=bar*)(gn=*baz))
* // The stars in $filter0 are treaten as real stars unless you disable escaping.
* </code>
*
* @category Net
* @package  Net_LDAP
* @author   Benedikt Hallinger <beni@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html LGPL
* @version  CVS: $Id: Filter.php,v 1.27 2008/06/04 06:12:04 beni Exp $
* @link     http://pear.php.net/package/Net_LDAP/
*/
class Net_LDAP_Filter extends PEAR
{
    /**
    * Storage for combination of filters
    *
    * This variable holds a array of filter objects
    * that should be combined by this filter object.
    *
    * @access private
    * @var array
    */
    var $_subfilters = array();

    /**
    * Match of this filter
    *
    * If this is a leaf filter, then a matching rule is stored,
    * if it is a container, then it is a logical operator
    *
    * @access private
    * @var string
    */
    var $_match;

    /**
    * Single filter
    *
    * If we operate in leaf filter mode,
    * then the constructing method stores
    * the filter representation here
    *
    * @acces private
    * @var string
    */
    var $_filter;

    /**
    * Create a new Net_LDAP_Filter object and parse $filter.
    *
    * This is for PERL Net::LDAP interface.
    * Construction of Net_LDAP_Filter objects should happen through either
    * {@link create()} or {@link combine()} which give you more control.
    * However, you may use the perl iterface if you already have generated filters.
    *
    * @param string $filter LDAP filter string
    *
    * @see parse()
    */
    function Net_LDAP_Filter($filter = false)
    {
        // The optional parameter must remain here, because otherwise create() crashes
        if (false !== $filter) {
            $filter_o = Net_LDAP_Filter::parse($filter);
            if (PEAR::isError($filter_o)) {
                $this->_filter = $filter_o; // assign error, so asString() can report it
            } else {
                $this->_filter = $filter_o->asString();
            }
        }
    }

    /**
    * Constructor of a new part of a LDAP filter.
    *
    * The following matching rules exists:
    *    - equals:         One of the attributes values is exactly $value
    *                      Please note that case sensitiviness is depends on the
    *                      attributes syntax configured in the server.
    *    - begins:         One of the attributes values must begin with $value
    *    - ends:           One of the attributes values must end with $value
    *    - contains:       One of the attributes values must contain $value
    *    - any:            The attribute can contain any value but must be existent
    *    - greater:        The attributes value is greater than $value
    *    - less:           The attributes value is less than $value
    *    - greaterOrEqual: The attributes value is greater or equal than $value
    *    - lessOrEqual:    The attributes value is less or equal than $value
    *    - approx:         One of the attributes values is similar to $value
    *
    * If $escape is set to true (default) then $value will be escaped
    * properly. If it is set to false then $value will be treaten as raw value.
    *
    * Examples:
    * <code>
    *   // This will find entries that contain an attribute "sn" that ends with "foobar":
    *   $filter = new Net_LDAP_Filter('sn', 'ends', 'foobar');
    *
    *   // This will find entries that contain an attribute "sn" that has any value set:
    *   $filter = new Net_LDAP_Filter('sn', 'any');
    * </code>
    *
    * @param string  $attr_name Name of the attribute the filter should apply to
    * @param string  $match     Matching rule (equals, begins, ends, contains, greater, less, greaterOrEqual, lessOrEqual, approx, any)
    * @param string  $value     (optional) if given, then this is used as a filter
    * @param boolean $escape    Should $value be escaped? (default: yes, see {@link Net_LDAP_Util::escape_filter_value()} for detailed information)
    *
    * @return Net_LDAP_Filter|Net_LDAP_Error
    */
    function &create($attr_name, $match, $value = '', $escape = true)
    {
        $leaf_filter = new Net_LDAP_Filter();
        if ($escape) {
            $array = Net_LDAP_Util::escape_filter_value(array($value));
            $value = $array[0];
        }
        switch (strtolower($match)) {
        case 'equals':
            $leaf_filter->_filter = '(' . $attr_name . '=' . $value . ')';
            break;
        case 'begins':
            $leaf_filter->_filter = '(' . $attr_name . '=' . $value . '*)';
            break;
        case 'ends':
            $leaf_filter->_filter = '(' . $attr_name . '=*' . $value . ')';
            break;
        case 'contains':
            $leaf_filter->_filter = '(' . $attr_name . '=*' . $value . '*)';
            break;
        case 'greater':
            $leaf_filter->_filter = '(' . $attr_name . '>' . $value . ')';
            break;
        case 'less':
            $leaf_filter->_filter = '(' . $attr_name . '<' . $value . ')';
            break;
        case 'greaterorequal':
            $leaf_filter->_filter = '(' . $attr_name . '>=' . $value . ')';
            break;
        case 'lessorequal':
            $leaf_filter->_filter = '(' . $attr_name . '<=' . $value . ')';
            break;
        case 'approx':
            $leaf_filter->_filter = '(' . $attr_name . '~=' . $value . ')';
            break;
        case 'any':
            $leaf_filter->_filter = '(' . $attr_name . '=*)';
            break;
        default:
            return PEAR::raiseError('Net_LDAP_Filter create error: matching rule "' . $match . '" not known!');
        }
        return $leaf_filter;
    }

    /**
    * Combine two or more filter objects using a logical operator
    *
    * This static method combines two or more filter objects and returns one single
    * filter object that contains all the others.
    * Call this method statically: $filter =& Net_LDAP_Filter('or', array($filter1, $filter2))
    * If the array contains filter strings instead of filter objects, we will try to parse them.
    *
    * @param string                $log_op  The locicall operator. May be "and", "or", "not" or the subsequent logical equivalents "&", "|", "!"
    * @param array|Net_LDAP_Filter $filters array with Net_LDAP_Filter objects
    *
    * @return Net_LDAP_Filter|Net_LDAP_Error
    * @static
    */
    function &combine($log_op, $filters)
    {
        if (PEAR::isError($filters)) {
            return $filters;
        }

        // substitude named operators to logical operators
        if ($log_op == 'and') $log_op = '&';
        if ($log_op == 'or')  $log_op = '|';
        if ($log_op == 'not') $log_op = '!';

        // tests for sane operation
        if ($log_op == '!') {
            // Not-combination, here we also accept one filter object or filter string
            if (!is_array($filters) && is_a($filters, 'Net_LDAP_Filter')) {
                $filters = array($filters); // force array
            } elseif (is_string($filters)) {
                $filter_o = Net_LDAP_Filter::parse($filters);
                if (PEAR::isError($filter_o)) {
                    $err = PEAR::raiseError('Net_LDAP_Filter combine error: '.$filter_o->getMessage());
                    return $err;
                } else {
                    $filters = array($filter_o);
                }
            } else {
                $err = PEAR::raiseError('Net_LDAP_Filter combine error: operator is "not" but $filter is not a valid Net_LDAP_Filter nor an array nor a filter string!');
                return $err;
            }
        } elseif ($log_op == '&' || $log_op == '|') {
            if (!is_array($filters) || count($filters) < 2) {
                $err = PEAR::raiseError('Net_LDAP_Filter combine error: parameter $filters is not an array or contains less than two Net_LDAP_Filter objects!');
                return $err;
            }
        } else {
            $err = PEAR::raiseError('Net_LDAP_Filter combine error: logical operator is not known!');
            return $err;
        }

        $combined_filter = new Net_LDAP_Filter();
        foreach ($filters as $key => $testfilter) {     // check for errors
            if (PEAR::isError($testfilter)) {
                return $testfilter;
            } elseif (is_string($testfilter)) {
                // string found, try to parse into an filter object
                $filter_o = Net_LDAP_Filter::parse($testfilter);
                if (PEAR::isError($filter_o)) {
                    return $filter_o;
                } else {
                    $filters[$key] = $filter_o;
                }
            } elseif (!is_a($testfilter, 'Net_LDAP_Filter')) {
                $err = PEAR::raiseError('Net_LDAP_Filter combine error: invalid object passed in array $filters!');
                return $err;
            }
        }

        $combined_filter->_subfilters = $filters;
        $combined_filter->_match      = $log_op;
        return $combined_filter;
    }

    /**
    * Parse FILTER into a Net_LDAP_Filter object
    *
    * This parses an filter string into Net_LDAP_Filter objects.
    *
    * @param string $FILTER The filter string
    *
    * @access static
    * @return Net_LDAP_Filter|Net_LDAP_Error
    * @todo Leaf-mode: Do we need to escape at all? what about *-chars?check for the need of encoding values, tackle problems (see code comments)
    */
    function parse($FILTER)
    {
        if (preg_match('/^\((.+?)\)$/', $FILTER, $matches)) {
            if (in_array(substr($matches[1], 0, 1), array('!', '|', '&'))) {
                // Subfilter processing: pass subfilters to parse() and combine
                // the objects using the logical operator detected
                // we have now something like "(...)(...)(...)" but at least one part ("(...)").

                // extract logical operator and subfilters
                $log_op              = substr($matches[1], 0, 1);
                $remaining_component = substr($matches[1], 1);

                // bite off the next filter part and parse
                $subfilters = array();
                while (preg_match('/^(\(.+?\))(.*)/', $remaining_component, $matches)) {
                    $remaining_component = $matches[2];
                    $filter_o = Net_LDAP_Filter::parse($matches[1]);
                    if (PEAR::isError($filter_o)) {
                        return $filter_o;
                    }
                    array_push($subfilters, $filter_o);
                }

                // combine subfilters using the logical operator
                $filter_o = Net_LDAP_Filter::combine($log_op, $subfilters);
                return $filter_o;
            } else {
                // This is one leaf filter component, do some syntax checks, then escape and build filter_o
                // $matches[1] should be now something like "foo=bar"

                // detect multiple leaf components
                // [TODO] Maybe this will make problems with filters containing brackets inside the value
                if (stristr($matches[1], ')(')) {
                    return PEAR::raiseError("Filter parsing error: invalid filter syntax - multiple leaf components detected!");
                } else {
                    $filter_parts = preg_split('/(?<!\\\\)(=|=~|>|<|>=|<=)/', $matches[1], 2, PREG_SPLIT_DELIM_CAPTURE);
                    if (count($filter_parts) != 3) {
                        return PEAR::raiseError("Filter parsing error: invalid filter syntax - unknown matching rule used");
                    } else {
                        $filter_o          = new Net_LDAP_Filter();
                        // [TODO]: Do we need to escape at all? what about *-chars user provide and that should remain special?
                        //         I think, those prevent escaping! We need to check against PERL Net::LDAP!
                        // $value_arr         = Net_LDAP_Util::escape_filter_value(array($filter_parts[2]));
                        // $value             = $value_arr[0];
                        $value             = $filter_parts[2];
                        $filter_o->_filter = '('.$filter_parts[0].$filter_parts[1].$value.')';
                        return $filter_o;
                    }
                }
            }
        } else {
               // ERROR: Filter components must be enclosed in round brackets
               return PEAR::raiseError("Filter parsing error: invalid filter syntax - filter components must be enclosed in round brackets");
        }
    }

    /**
    * Get the string representation of this filter
    *
    * This method runs through all filter objects and creates
    * the string representation of the filter. If this
    * filter object is a leaf filter, then it will return
    * the string representation of this filter.
    *
    * @return string|Net_LDAP_Error
    */
    function asString()
    {
        if ($this->_isLeaf()) {
            $return = $this->_filter;
        } else {
            $return = '';
            foreach ($this->_subfilters as $filter) {
                $return = $return.$filter->asString();
            }
            $return = '(' . $this->_match . $return . ')';
        }
        return $return;
    }

    /**
    * Alias for perl interface as_string()
    *
    * @see asString()
    */
    function as_string()
    {
        return $this->asString();
    }

    /**
    * Print the text representation of the filter to FH, or the currently selected output handle if FH is not given
    *
    * This method is only for compatibility to the perl interface.
    * However, the original method was called "print" but due to PHP language restrictions,
    * we can't have a print() method.
    *
    * @param resource $FH (optional) A filehandle resource
    *
    * @return true|Net_LDAP_Error
    */
    function printMe($FH = false)
    {
        if (!is_resource($FH)) {
            if (PEAR::isError($FH)) {
                return $FH;
            }
            $filter_str = $this->asString();
            if (PEAR::isError($filter_str)) {
                return $filter_str;
            } else {
                print($filter_str);
            }
        } else {
            $filter_str = $this->asString();
            if (PEAR::isError($filter_str)) {
                return $filter_str;
            } else {
                $res = @fwrite($FH, $this->asString());
                if ($res == false) {
                    return PEAR::raiseError("Unable to write filter string to filehandle \$FH!");
                }
            }
        }
        return true;
    }

    /**
    * This can be used to escape a string to provide a valid LDAP-Filter.
    *
    * LDAP will only recognise certain characters as the
    * character istself if they are properly escaped. This is
    * what this method does.
    * The method can be called statically, so you can use it outside
    * for your own purposes (eg for escaping only parts of strings)
    *
    * In fact, this is just a shorthand to {@link Net_LDAP_Util::escape_filter_value()}.
    * For upward compatibiliy reasons you are strongly encouraged to use the escape
    * methods provided by the Net_LDAP_Util class.
    *
    * @param string $value Any string who should be escaped
    *
    * @static
    * @return string         The string $string, but escaped
    * @deprecated  Do not use this method anymore, instead use Net_LDAP_Util::escape_filter_value() directly
    */
    function escape($value)
    {
        $return = Net_LDAP_Util::escape_filter_value(array($value));
        return $return[0];
    }

    /**
    * Is this a container or a leaf filter object?
    *
    * @access private
    * @return boolean
    */
    function _isLeaf()
    {
        if (count($this->_subfilters) > 0) {
            return false; // Container!
        } else {
            return true; // Leaf!
        }
    }
}
?>
