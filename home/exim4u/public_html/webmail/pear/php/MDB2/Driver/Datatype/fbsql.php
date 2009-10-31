<?php
// vim: set et ts=4 sw=4 fdm=marker:
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2006 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith                                         |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2 is a merge of PEAR DB and Metabases that provides a unified DB  |
// | API as well as database abstraction for PHP applications.            |
// | This LICENSE is in the BSD license style.                            |
// |                                                                      |
// | Redistribution and use in source and binary forms, with or without   |
// | modification, are permitted provided that the following conditions   |
// | are met:                                                             |
// |                                                                      |
// | Redistributions of source code must retain the above copyright       |
// | notice, this list of conditions and the following disclaimer.        |
// |                                                                      |
// | Redistributions in binary form must reproduce the above copyright    |
// | notice, this list of conditions and the following disclaimer in the  |
// | documentation and/or other materials provided with the distribution. |
// |                                                                      |
// | Neither the name of Manuel Lemos, Tomas V.V.Cox, Stig. S. Bakken,    |
// | Lukas Smith nor the names of his contributors may be used to endorse |
// | or promote products derived from this software without specific prior|
// | written permission.                                                  |
// |                                                                      |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS  |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT    |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS    |
// | FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE      |
// | REGENTS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,          |
// | INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, |
// | BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS|
// |  OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED  |
// | AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT          |
// | LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY|
// | WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE          |
// | POSSIBILITY OF SUCH DAMAGE.                                          |
// +----------------------------------------------------------------------+
// | Author: Lukas Smith <smith@pooteeweet.org>                           |
// +----------------------------------------------------------------------+
//
// $Id: fbsql.php,v 1.26 2006/03/01 12:15:32 lsmith Exp $
//

require_once 'MDB2/Driver/Datatype/Common.php';

/**
 * MDB2 FrontbaseSQL driver
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Datatype_fbsql extends MDB2_Driver_Datatype_Common
{
    // }}}
    // {{{ convertResult()

    /**
     * convert a value to a RDBMS indepdenant MDB2 type
     *
     * @param mixed  $value   value to be converted
     * @param int    $type    constant that specifies which type to convert to
     * @return mixed converted value
     * @access public
     */
    function convertResult($value, $type)
    {
        if (is_null($value)) {
            return null;
        }
        switch ($type) {
         case 'boolean':
             return $value == 'T';
         case 'time':
            if ($value[0] == '+') {
                return substr($value, 1);
            } else {
                return $value;
            }
        default:
            return $this->_baseConvertResult($value, $type);
        }
        return $this->_baseConvertResult($value, $type);
    }

    // }}}
    // {{{ getTypeDeclaration()

    /**
     * Obtain DBMS specific SQL code portion needed to declare an text type
     * field to be used in statements like CREATE TABLE.
     *
     * @param array $field  associative array with the name of the properties
     *      of the field being declared as array indexes. Currently, the types
     *      of supported field properties are as follows:
     *
     *      length
     *          Integer value that determines the maximum length of the text
     *          field. If this argument is missing the field should be
     *          declared to have the longest length allowed by the DBMS.
     *
     *      default
     *          Text value to be used as default for this field.
     *
     *      notnull
     *          Boolean flag that indicates whether this field is constrained
     *          to not be set to null.
     * @return string  DBMS specific SQL code portion that should be used to
     *      declare the specified field.
     * @access public
     */
    function getTypeDeclaration($field)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        switch ($field['type']) {
        case 'text':
            $length = array_key_exists('length', $field)
                ? $field['length'] : $db->options['default_text_field_length'];
            return 'VARCHAR ('.$length.')';
        case 'clob':
            return 'CLOB';
        case 'blob':
            return 'BLOB';
        case 'integer':
            return 'INT';
        case 'boolean':
            return 'BOOLEAN';
        case 'date':
            return 'DATE';
        case 'time':
            return 'TIME';
        case 'timestamp':
            return 'TIMESTAMP';
        case 'float':
            return 'FLOAT';
        case 'decimal':
            $length = array_key_exists('length', $field)
                ? $field['length'] : 18;
            return 'DECIMAL('.$length.','.$db->options['decimal_places'].')';
        }
        return '';
    }

    // }}}
    // {{{ _quoteBLOB()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @return string  text string that represents the given argument value in
     *                 a DBMS specific format.
     * @access protected
     */
    function _quoteBLOB($value, $quote)
    {
        $value = $this->_readFile($value);
        if (!$quote) {
            return $value;
        }
        return "'".addslashes($value)."'";
    }

    // }}}
    // {{{ _quoteBoolean()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @return string text string that represents the given argument value in
     *       a DBMS specific format.
     * @access protected
     */
    function _quoteBoolean($value, $quote)
    {
        return ($value ? 'True' : 'False');
    }

    // }}}
    // {{{ _quoteDate()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteDate($value, $quote)
    {
        return 'DATE'.$this->_quoteText($value, $quote);
    }

    // }}}
    // {{{ _quoteTimestamp()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     * compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteTimestamp($value, $quote)
    {
        return 'TIMESTAMP'.$this->_quoteText($value, $quote);
    }

    // }}}
    // {{{ _quoteTime()

    /**
     * Convert a text value into a DBMS specific format that is suitable to
     *        compose query statements.
     *
     * @param string $value text string value that is intended to be converted.
     * @param bool $quote determines if the value should be quoted and escaped
     * @return string text string that represents the given argument value in
     *        a DBMS specific format.
     * @access protected
     */
    function _quoteTime($value, $quote)
    {
        return 'TIME'.$this->_quoteText($value, $quote);
    }
}

?>
