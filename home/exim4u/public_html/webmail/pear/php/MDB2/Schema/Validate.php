<?php
// +----------------------------------------------------------------------+
// | PHP versions 4 and 5                                                 |
// +----------------------------------------------------------------------+
// | Copyright (c) 1998-2008 Manuel Lemos, Tomas V.V.Cox,                 |
// | Stig. S. Bakken, Lukas Smith, Igor Feghali                           |
// | All rights reserved.                                                 |
// +----------------------------------------------------------------------+
// | MDB2_Schema enables users to maintain RDBMS independant schema files |
// | in XML that can be used to manipulate both data and database schemas |
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
// | Lukas Smith, Igor Feghali nor the names of his contributors may be   |
// | used to endorse or promote products derived from this software       |
// | without specific prior written permission.                           |
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
// | Author: Christian Dickmann <dickmann@php.net>                        |
// | Author: Igor Feghali <ifeghali@php.net>                              |
// +----------------------------------------------------------------------+
//
// $Id: Validate.php,v 1.38 2008/02/06 23:13:51 ifeghali Exp $
//

/**
 * Validates an XML schema file
 *
 * @package MDB2_Schema
 * @category Database
 * @access protected
 * @author Igor Feghali <ifeghali@php.net>
 */
class MDB2_Schema_Validate
{
    // {{{ properties

    var $fail_on_invalid_names = true;
    var $valid_types = array();
    var $force_defaults = true;

    // }}}
    // {{{ constructor

    function __construct($fail_on_invalid_names = true, $valid_types = array(), $force_defaults = true)
    {
        if (empty($GLOBALS['_MDB2_Schema_Reserved'])) {
            $GLOBALS['_MDB2_Schema_Reserved'] = array();
        }

        if (is_array($fail_on_invalid_names)) {
            $this->fail_on_invalid_names
                = array_intersect($fail_on_invalid_names, array_keys($GLOBALS['_MDB2_Schema_Reserved']));
        } elseif ($fail_on_invalid_names === true) {
            $this->fail_on_invalid_names = array_keys($GLOBALS['_MDB2_Schema_Reserved']);
        } else {
            $this->fail_on_invalid_names = array();
        }
        $this->valid_types = $valid_types;
        $this->force_defaults = $force_defaults;
    }

    function MDB2_Schema_Validate($fail_on_invalid_names = true, $valid_types = array(), $force_defaults = true)
    {
        $this->__construct($fail_on_invalid_names, $valid_types, $force_defaults);
    }

    // }}}
    // {{{ raiseError()

    function &raiseError($ecode, $msg = null)
    {
        $error =& MDB2_Schema::raiseError($ecode, null, null, $msg);
        return $error;
    }

    // }}}
    // {{{ isBoolean()

    /**
     * Verifies if a given value can be considered boolean. If yes, set value
     * to true or false according to its actual contents and return true. If
     * not, keep its contents untouched and return false.
     *
     * @param mixed  value to be checked
     *
     * @return bool
     *
     * @access public
     * @static
     */
    function isBoolean(&$value)
    {
        if (is_bool($value)) {
            return true;
        }
        if ($value === 0 || $value === 1 || $value === '') {
            $value = (bool)$value;
            return true;
        }
        if (!is_string($value)) {
            return false;
        }
        switch ($value) {
        case '0':
        case 'N':
        case 'n':
        case 'no':
        case 'false':
            $value = false;
            break;
        case '1':
        case 'Y':
        case 'y':
        case 'yes':
        case 'true':
            $value = true;
            break;
        default:
            return false;
        }
        return true;
    }

    // }}}
    // {{{ validateTable()

    /* Definition */
    /**
     * Checks whether the definition of a parsed table is valid. Modify table
     * definition when necessary.
     *
     * @param array  multi dimensional array that contains the
     *                tables of current database.
     * @param array  multi dimensional array that contains the
     *                structure and optional data of the table.
     * @param string  name of the parsed table
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateTable($tables, &$table, $table_name)
    {
        /* Have we got a name? */
        if (!$table_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'a table has to have a name');
        }

        /* Table name duplicated? */
        if (is_array($tables) && isset($tables[$table_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'table "'.$table_name.'" already exists');
        }

        /* Table name reserved? */
        if (is_array($this->fail_on_invalid_names)) {
            $name = strtoupper($table_name);
            foreach ($this->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                        'table name "'.$table_name.'" is a reserved word in: '.$rdbms);
                }
            }
        }

        /* Was */
        if (empty($table['was'])) {
            $table['was'] = $table_name;
        }

        /* Have we got fields? */
        if (empty($table['fields']) || !is_array($table['fields'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'tables need one or more fields');
        }

        /* Autoincrement */
        $autoinc = $primary = false;
        foreach ($table['fields'] as $field_name => $field) {
            if (!empty($field['autoincrement'])) {
                if ($primary) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                        'there was already an autoincrement field in "'.$table_name.'" before "'.$field_name.'"');
                }
                $autoinc = $primary = true;
            }
        }

        /*
         * Checking Indexes
         * this have to be done here otherwise we can't
         * guarantee that all table fields were already
         * defined in the moment we are parsing indexes
         */
        if (!empty($table['indexes']) && is_array($table['indexes'])) {
            foreach ($table['indexes'] as $name => $index) {
                $skip_index = false;
                if (!empty($index['primary'])) {
                    /*
                     * Lets see if we should skip this index since there is
                     * already an auto increment on this field this implying
                     * a primary key index.
                     */
                    if ($autoinc && count($index['fields']) == '1') {
                        $skip_index = true;
                    } elseif ($primary) {
                        return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                            'there was already an primary index or autoincrement field in "'.$table_name.'" before "'.$name.'"');
                    } else {
                        $primary = true;
                    }
                }

                if (!$skip_index && is_array($index['fields'])) {
                    foreach ($index['fields'] as $field_name => $field) {
                        if (!isset($table['fields'][$field_name])) {
                            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                                'index field "'.$field_name.'" does not exist');
                        }
                        if (!empty($index['primary'])
                            && !$table['fields'][$field_name]['notnull']
                        ) {
                            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                                'all primary key fields must be defined notnull in "'.$table_name.'"');
                        }
                    }
                } else {
                    unset($table['indexes'][$name]);
                }
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateField()

    /**
     * Checks whether the definition of a parsed field is valid. Modify field
     * definition when necessary.
     *
     * @param array  multi dimensional array that contains the
     *                fields of current table.
     * @param array  multi dimensional array that contains the
     *                structure of the parsed field.
     * @param string  name of the parsed field
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateField($fields, &$field, $field_name)
    {
        /* Have we got a name? */
        if (!$field_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field name missing');
        }

        /* Field name duplicated? */
        if (is_array($fields) && isset($fields[$field_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field "'.$field_name.'" already exists');
        }

        /* Field name reserverd? */
        if (is_array($this->fail_on_invalid_names)) {
            $name = strtoupper($field_name);
            foreach ($this->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                        'field name "'.$field_name.'" is a reserved word in: '.$rdbms);
                }
            }
        }

        /* Type check */
        if (empty($field['type'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'no field type specified');
        }
        if (!empty($this->valid_types) && !array_key_exists($field['type'], $this->valid_types)) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'no valid field type ("'.$field['type'].'") specified');
        }

        /* Unsigned */
        if (array_key_exists('unsigned', $field) && !$this->isBoolean($field['unsigned'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'unsigned has to be a boolean value');
        }

        /* Fixed */
        if (array_key_exists('fixed', $field) && !$this->isBoolean($field['fixed'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'fixed has to be a boolean value');
        }

        /* Length */
        if (array_key_exists('length', $field) && $field['length'] <= 0) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'length has to be an integer greater 0');
        }
 
        // if it's a DECIMAL datatype, check if a 'scale' value is provided:
        // <length>8,4</length> should be translated to DECIMAL(8,4)
        if (is_float($this->valid_types[$field['type']])
            && !empty($field['length'])
            && strpos($field['length'], ',') !== false
        ) {
            list($field['length'], $field['scale']) = explode(',', $field['length']);
        }

        /* Was */
        if (empty($field['was'])) {
            $field['was'] = $field_name;
        }

        /* Notnull */
        if (empty($field['notnull'])) {
            $field['notnull'] = false;
        }
        if (!$this->isBoolean($field['notnull'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field "notnull" has to be a boolean value');
        }

        /* Default */
        if ($this->force_defaults
            && !array_key_exists('default', $field)
            && $field['type'] != 'clob' && $field['type'] != 'blob'
        ) {
            $field['default'] = $this->valid_types[$field['type']];
        }
        if (array_key_exists('default', $field)) {
            if ($field['type'] == 'clob' || $field['type'] == 'blob') {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field['type'].'"-fields are not allowed to have a default value');
            }
            if ($field['default'] === '' && !$field['notnull']) {
                $field['default'] = null;
            }
        }
        if (isset($field['default'])
            && PEAR::isError($result = $this->validateDataFieldValue($field, $field['default'], $field_name))
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'default value of "'.$field_name.'" is incorrect: '.$result->getUserinfo());
        }

        /* Autoincrement */
        if (!empty($field['autoincrement'])) {
            if (!$field['notnull']) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    'all autoincrement fields must be defined notnull');
            }

            if (empty($field['default'])) {
                $field['default'] = '0';
            } elseif ($field['default'] !== '0' && $field['default'] !== 0) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    'all autoincrement fields must be defined default "0"');
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateIndex()

    /**
     * Checks whether a parsed index is valid. Modify index definition when
     * necessary.
     *
     * @param array  multi dimensional array that contains the
     *                indexes of current table.
     * @param array  multi dimensional array that contains the
     *                structure of the parsed index.
     * @param string  name of the parsed index
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateIndex($table_indexes, &$index, $index_name)
    {
        if (!$index_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'an index has to have a name');
        }
        if (is_array($table_indexes) && isset($table_indexes[$index_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'index "'.$index_name.'" already exists');
        }
        if (array_key_exists('unique', $index) && !$this->isBoolean($index['unique'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field "unique" has to be a boolean value');
        }
        if (array_key_exists('primary', $index) && !$this->isBoolean($index['primary'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field "primary" has to be a boolean value');
        }

        /* Have we got fields? */
        if (empty($index['fields']) || !is_array($index['fields'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'indexes need one or more fields');
        }

        if (empty($index['was'])) {
            $index['was'] = $index_name;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateIndexField()

    /**
     * Checks whether a parsed index-field is valid. Modify its definition when
     * necessary.
     *
     * @param array  multi dimensional array that contains the
     *                fields of current index.
     * @param array  multi dimensional array that contains the
     *                structure of the parsed index-field.
     * @param string  name of the parsed index-field
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateIndexField($index_fields, &$field, $field_name)
    {
        if (is_array($index_fields) && isset($index_fields[$field_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'index field "'.$field_name.'" already exists');
        }
        if (!$field_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'the index-field-name is required');
        }
        if (empty($field['sorting'])) {
            $field['sorting'] = 'ascending';
        } elseif($field['sorting'] !== 'ascending' && $field['sorting'] !== 'descending') {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'sorting type unknown');
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateConstraint()

    /**
     * Checks whether a parsed foreign key is valid. Modify its definition when
     * necessary.
     *
     * @param array  multi dimensional array that contains the
     *                constraints of current table.
     * @param array  multi dimensional array that contains the
     *                structure of the parsed foreign key.
     * @param string  name of the parsed foreign key
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateConstraint($table_constraints, &$constraint, $constraint_name)
    {
        if (!$constraint_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'a foreign key has to have a name');
        }
        if (is_array($table_constraints) && isset($table_constraints[$constraint_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'foreign key "'.$constraint_name.'" already exists');
        }

        /* Have we got fields? */
        if (empty($constraint['fields']) || !is_array($constraint['fields'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'foreign key "'.$constraint_name.'" need one or more fields');
        }

        /* Have we got referenced fields? */
        if (empty($constraint['references']) || !is_array($constraint['references'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'foreign key "'.$constraint_name.'" need to reference one or more fields');
        }

        /* Have we got referenced table? */
        if (empty($constraint['references']['table'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'foreign key "'.$constraint_name.'" need to reference a table');
        }

        if (empty($constraint['was'])) {
            $constraint['was'] = $constraint_name;
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateConstraintField()

    /**
     * Checks whether a foreign-field is valid.
     *
     * @param array  multi dimensional array that contains the
     *                fields of current foreign key.
     * @param string  name of the parsed foreign-field
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateConstraintField($constraint_fields, $field_name)
    {
        if (!$field_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'empty value for foreign-field');
        }
        if (is_array($constraint_fields) && isset($constraint_fields[$field_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'foreign field "'.$field_name.'" already exists');
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateConstraintReferencedField()

    /**
     * Checks whether a foreign-referenced field is valid.
     *
     * @param array  multi dimensional array that contains the
     *                fields of current foreign key.
     * @param string  name of the parsed foreign-field
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateConstraintReferencedField($referenced_fields, $field_name)
    {
        if (!$field_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'empty value for referenced foreign-field');
        }
        if (is_array($referenced_fields) && isset($referenced_fields[$field_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'foreign field "'.$field_name.'" already referenced');
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateSequence()

    /**
     * Checks whether the definition of a parsed sequence is valid. Modify
     * sequence definition when necessary.
     *
     * @param array  multi dimensional array that contains the
     *                sequences of current database.
     * @param array  multi dimensional array that contains the
     *                structure of the parsed sequence.
     * @param string  name of the parsed sequence
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateSequence($sequences, &$sequence, $sequence_name)
    {
        if (!$sequence_name) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'a sequence has to have a name');
        }

        if (is_array($sequences) && isset($sequences[$sequence_name])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'sequence "'.$sequence_name.'" already exists');
        }

        if (is_array($this->fail_on_invalid_names)) {
            $name = strtoupper($sequence_name);
            foreach ($this->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                        'sequence name "'.$sequence_name.'" is a reserved word in: '.$rdbms);
                }
            }
        }

        if (empty($sequence['was'])) {
            $sequence['was'] = $sequence_name;
        }

        if (!empty($sequence['on'])
            && (empty($sequence['on']['table']) || empty($sequence['on']['field']))
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'sequence "'.$sequence_name.'" on a table was not properly defined');
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateDatabase()

    /**
     * Checks whether a parsed database is valid. Modify its structure and
     * data when necessary.
     *
     * @param array  multi dimensional array that contains the
     *                structure and optional data of the database.
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateDatabase(&$database)
    {
        /* Have we got a name? */
        if (!is_array($database) || !isset($database['name']) || !$database['name']) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'a database has to have a name');
        }

        /* Database name reserved? */
        if (is_array($this->fail_on_invalid_names)) {
            $name = strtoupper($database['name']);
            foreach ($this->fail_on_invalid_names as $rdbms) {
                if (in_array($name, $GLOBALS['_MDB2_Schema_Reserved'][$rdbms])) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                        'database name "'.$database['name'].'" is a reserved word in: '.$rdbms);
                }
            }
        }

        /* Create */
        if (isset($database['create'])
            && !$this->isBoolean($database['create'])
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field "create" has to be a boolean value');
        }

        /* Overwrite */
        if (isset($database['overwrite'])
            && $database['overwrite'] !== ''
            && !$this->isBoolean($database['overwrite'])
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field "overwrite" has to be a boolean value');
        }

        /*
         * This have to be done here otherwise we can't guarantee that all
         * tables were already defined in the moment we are parsing constraints
         */
        if (isset($database['tables'])) {
            foreach ($database['tables'] as $table_name => $table) {
                if (!empty($table['constraints'])) {
                    foreach ($table['constraints'] as $constraint_name => $constraint) {
                        $referenced_table_name = $constraint['references']['table'];

                        if (!isset($database['tables'][$referenced_table_name])) {
                            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                                'referenced table "'.$referenced_table_name.'" of foreign key "'.$constraint_name.'" of table "'.$table_name.'" does not exist');
                        }

                        if (empty($constraint['references']['fields'])) {
                            $referenced_table = $database['tables'][$referenced_table_name];
                            $primary = false;

                            if (!empty($referenced_table['indexes'])) {
                                foreach ($referenced_table['indexes'] as $index_name => $index) {
                                    if (array_key_exists('primary', $index)
                                        && $index['primary']
                                    ) {
                                        $primary = array();
                                        foreach ($index['fields'] as $field_name => $field) {
                                            $primary[$field_name] = '';
                                        }
                                        break;
                                    }
                                }
                            }

                            if (!$primary) {
                                foreach ($referenced_table['fields'] as $field_name => $field) {
                                    if (array_key_exists('autoincrement', $field)
                                        && $field['autoincrement']
                                    ) {
                                        $primary = array( $field_name => '' );
                                        break;
                                    }
                                }
                            }

                            if (!$primary) {
                                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                                    'referenced table "'.$referenced_table_name.'" has no primary key and no referenced field was specified for foreign key "'.$constraint_name.'" of table "'.$table_name.'"');
                            }

                            $constraint['references']['fields'] = $primary;
                        }

                        /* the same number of referencing and referenced fields ? */
                        if (count($constraint['fields']) != count($constraint['references']['fields'])) {
                            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                                'The number of fields in the referenced key must match those of the foreign key "'.$constraint_name.'"');
                        }

                        $database['tables'][$table_name]['constraints'][$constraint_name]['references']['fields'] = $constraint['references']['fields'];
                    }
                }
            }
        }

        /*
         * This have to be done here otherwise we can't guarantee that all
         * tables were already defined in the moment we are parsing sequences
         */
        if (isset($database['sequences'])) {
            foreach ($database['sequences'] as $seq_name => $seq) {
                if (!empty($seq['on'])
                    && empty($database['tables'][$seq['on']['table']]['fields'][$seq['on']['field']])
                ) {
                    return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                        'sequence "'.$seq_name.'" was assigned on unexisting field/table');
                }
            }
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateDataField()

    /* Data Manipulation */
    /**
     * Checks whether a parsed DML-field is valid. Modify its structure when
     * necessary. This is called when validating INSERT and
     * UPDATE.
     *
     * @param array  multi dimensional array that contains the
     *                definition for current table's fields.
     * @param array  multi dimensional array that contains the
     *                parsed fields of the current DML instruction.
     * @param string  array that contains the parsed instruction field
     *
     * @return bool|error object
     *
     * @access public
     */
    function validateDataField($table_fields, $instruction_fields, &$field)
    {
        if (!$field['name']) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field-name has to be specified');
        }
        if (is_array($instruction_fields) && isset($instruction_fields[$field['name']])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'field "'.$field['name'].'" already initialized');
        }
        if (is_array($table_fields) && !isset($table_fields[$field['name']])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                '"'.$field['name'].'" is not defined');
        }
        if (!isset($field['group']['type'])) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                '"'.$field['name'].'" has no initial value');
        }
        if (isset($field['group']['data'])
            && $field['group']['type'] == 'value'
            && $field['group']['data'] !== ''
            && PEAR::isError($result = $this->validateDataFieldValue($table_fields[$field['name']], $field['group']['data'], $field['name']))
        ) {
            return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                'value of "'.$field['name'].'" is incorrect: '.$result->getUserinfo());
        }
        return MDB2_OK;
    }

    // }}}
    // {{{ validateDataFieldValue()

    /**
     * Checks whether a given value is compatible with a table field. This is
     * done when parsing a field for a INSERT or UPDATE instruction.
     *
     * @param array  multi dimensional array that contains the
     *                definition for current table's fields.
     * @param string  value to fill the parsed field
     * @param string  name of the parsed field
     *
     * @return bool|error object
     *
     * @access public
     * @see MDB2_Schema_Validate::validateInsertField()
     */
    function validateDataFieldValue($field_def, &$field_value, $field_name)
    {
        switch ($field_def['type']) {
        case 'text':
        case 'clob':
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" is larger than "'.$field_def['length'].'"');
            }
            break;
        case 'blob':
            $field_value = pack('H*', $field_value);
            if (!empty($field_def['length']) && strlen($field_value) > $field_def['length']) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" is larger than "'.$field_def['type'].'"');
            }
            break;
        case 'integer':
            if ($field_value != ((int)$field_value)) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            //$field_value = (int)$field_value;
            if (!empty($field_def['unsigned']) && $field_def['unsigned'] && $field_value < 0) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" signed instead of unsigned');
            }
            break;
        case 'boolean':
            if (!$this->isBoolean($field_value)) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            break;
        case 'date':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/', $field_value)
                && $field_value !== 'CURRENT_DATE'
            ) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            break;
        case 'timestamp':
            if (!preg_match('/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})/', $field_value)
                && $field_value !== 'CURRENT_TIMESTAMP'
            ) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            break;
        case 'time':
            if (!preg_match("/([0-9]{2}):([0-9]{2}):([0-9]{2})/", $field_value)
                && $field_value !== 'CURRENT_TIME'
            ) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            break;
        case 'float':
        case 'double':
            if ($field_value != (double)$field_value) {
                return $this->raiseError(MDB2_SCHEMA_ERROR_VALIDATE,
                    '"'.$field_value.'" is not of type "'.$field_def['type'].'"');
            }
            //$field_value = (double)$field_value;
            break;
        }
        return MDB2_OK;
    }
}

?>
