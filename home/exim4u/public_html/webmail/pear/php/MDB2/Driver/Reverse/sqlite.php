<?php
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
// $Id: sqlite.php,v 1.70 2007/03/29 18:18:06 quipo Exp $
//

require_once 'MDB2/Driver/Reverse/Common.php';

/**
 * MDB2 SQlite driver for the schema reverse engineering module
 *
 * @package MDB2
 * @category Database
 * @author  Lukas Smith <smith@pooteeweet.org>
 */
class MDB2_Driver_Reverse_sqlite extends MDB2_Driver_Reverse_Common
{
    function _getTableColumns($sql)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }
        $start_pos = strpos($sql, '(');
        $end_pos = strrpos($sql, ')');
        $column_def = substr($sql, $start_pos+1, $end_pos-$start_pos-1);
        // replace the decimal length-places-separator with a colon
        $column_def = preg_replace('/(\d),(\d)/', '\1:\2', $column_def);
        $column_sql = split(',', $column_def);
        $columns = array();
        $count = count($column_sql);
        if ($count == 0) {
            return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                'unexpected empty table column definition list', __FUNCTION__);
        }
        $regexp = '/^([^ ]+) (CHAR|VARCHAR|VARCHAR2|TEXT|BOOLEAN|SMALLINT|INT|INTEGER|DECIMAL|BIGINT|DOUBLE|FLOAT|DATETIME|DATE|TIME|LONGTEXT|LONGBLOB)( ?\(([1-9][0-9]*)(:([1-9][0-9]*))?\))?( UNSIGNED)?( PRIMARY KEY)?( DEFAULT (\'[^\']*\'|[^ ]+))?( NULL| NOT NULL)?( PRIMARY KEY)?$/i';
        $regexp2 = '/^([^ ]+) (PRIMARY|UNIQUE|CHECK)$/i';
        for ($i=0, $j=0; $i<$count; ++$i) {
            if (!preg_match($regexp, trim($column_sql[$i]), $matches)) {
                if (!preg_match($regexp2, trim($column_sql[$i]))) {
                    continue;
                }
                return $db->raiseError(MDB2_ERROR_UNSUPPORTED, null, null,
                    'unexpected table column SQL definition: "'.$column_sql[$i].'"', __FUNCTION__);
            }
            $columns[$j]['name'] = trim($matches[1], implode('', $db->identifier_quoting));
            $columns[$j]['type'] = strtolower($matches[2]);
            if (isset($matches[4]) && strlen($matches[4])) {
                $columns[$j]['length'] = $matches[4];
            }
            if (isset($matches[6]) && strlen($matches[6])) {
                $columns[$j]['decimal'] = $matches[6];
            }
            if (isset($matches[7]) && strlen($matches[7])) {
                $columns[$j]['unsigned'] = true;
            }
            if (isset($matches[8]) && strlen($matches[8])) {
                $columns[$j]['autoincrement'] = true;
            }
            if (isset($matches[10]) && strlen($matches[10])) {
                $default = $matches[10];
                if (strlen($default) && $default[0]=="'") {
                    $default = str_replace("''", "'", substr($default, 1, strlen($default)-2));
                }
                if ($default === 'NULL') {
                    $default = null;
                }
                $columns[$j]['default'] = $default;
            }
            if (isset($matches[11]) && strlen($matches[11])) {
                $columns[$j]['notnull'] = ($matches[11] === ' NOT NULL');
            }
            ++$j;
        }
        return $columns;
    }

    // {{{ getTableFieldDefinition()

    /**
     * Get the stucture of a field into an array
     *
     * @param string    $table       name of table that should be used in method
     * @param string    $field_name  name of field that should be used in method
     * @return mixed data array on success, a MDB2 error on failure.
     *          The returned array contains an array for each field definition,
     *          with (some of) these indices:
     *          [notnull] [nativetype] [length] [fixed] [default] [type] [mdb2type]
     * @access public
     */
    function getTableFieldDefinition($table, $field_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $result = $db->loadModule('Datatype', null, true);
        if (PEAR::isError($result)) {
            return $result;
        }
        $query = "SELECT sql FROM sqlite_master WHERE type='table' AND ";
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $query.= 'LOWER(name)='.$db->quote(strtolower($table), 'text');
        } else {
            $query.= 'name='.$db->quote($table, 'text');
        }
        $sql = $db->queryOne($query);
        if (PEAR::isError($sql)) {
            return $sql;
        }
        $columns = $this->_getTableColumns($sql);
        foreach ($columns as $column) {
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                if ($db->options['field_case'] == CASE_LOWER) {
                    $column['name'] = strtolower($column['name']);
                } else {
                    $column['name'] = strtoupper($column['name']);
                }
            } else {
                $column = array_change_key_case($column, $db->options['field_case']);
            }
            if ($field_name == $column['name']) {
                $mapped_datatype = $db->datatype->mapNativeDatatype($column);
                if (PEAR::IsError($mapped_datatype)) {
                    return $mapped_datatype;
                }
                list($types, $length, $unsigned, $fixed) = $mapped_datatype;
                $notnull = false;
                if (!empty($column['notnull'])) {
                    $notnull = $column['notnull'];
                }
                $default = false;
                if (array_key_exists('default', $column)) {
                    $default = $column['default'];
                    if (is_null($default) && $notnull) {
                        $default = '';
                    }
                }
                $autoincrement = false;
                if (!empty($column['autoincrement'])) {
                    $autoincrement = true;
                }

                $definition[0] = array(
                    'notnull' => $notnull,
                    'nativetype' => preg_replace('/^([a-z]+)[^a-z].*/i', '\\1', $column['type'])
                );
                if (!is_null($length)) {
                    $definition[0]['length'] = $length;
                }
                if (!is_null($unsigned)) {
                    $definition[0]['unsigned'] = $unsigned;
                }
                if (!is_null($fixed)) {
                    $definition[0]['fixed'] = $fixed;
                }
                if ($default !== false) {
                    $definition[0]['default'] = $default;
                }
                if ($autoincrement !== false) {
                    $definition[0]['autoincrement'] = $autoincrement;
                }
                foreach ($types as $key => $type) {
                    $definition[$key] = $definition[0];
                    if ($type == 'clob' || $type == 'blob') {
                        unset($definition[$key]['default']);
                    }
                    $definition[$key]['type'] = $type;
                    $definition[$key]['mdb2type'] = $type;
                }
                return $definition;
            }
        }

        return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
            'it was not specified an existing table column', __FUNCTION__);
    }

    // }}}
    // {{{ getTableIndexDefinition()

    /**
     * Get the stucture of an index into an array
     *
     * @param string    $table      name of table that should be used in method
     * @param string    $index_name name of index that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableIndexDefinition($table, $index_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT sql FROM sqlite_master WHERE type='index' AND ";
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $query.= 'LOWER(name)=%s AND LOWER(tbl_name)=' . $db->quote(strtolower($table), 'text');
        } else {
            $query.= 'name=%s AND tbl_name=' . $db->quote($table, 'text');
        }
        $query.= ' AND sql NOT NULL ORDER BY name';
        $index_name_mdb2 = $db->getIndexName($index_name);
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $qry = sprintf($query, $db->quote(strtolower($index_name_mdb2), 'text'));
        } else {
            $qry = sprintf($query, $db->quote($index_name_mdb2, 'text'));
        }
        $sql = $db->queryOne($qry, 'text');
        if (PEAR::isError($sql) || empty($sql)) {
            // fallback to the given $index_name, without transformation
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                $qry = sprintf($query, $db->quote(strtolower($index_name), 'text'));
            } else {
                $qry = sprintf($query, $db->quote($index_name, 'text'));
            }
            $sql = $db->queryOne($qry, 'text');
        }
        if (PEAR::isError($sql)) {
            return $sql;
        }
        if (!$sql) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing table index', __FUNCTION__);
        }

        $sql = strtolower($sql);
        $start_pos = strpos($sql, '(');
        $end_pos = strrpos($sql, ')');
        $column_names = substr($sql, $start_pos+1, $end_pos-$start_pos-1);
        $column_names = split(',', $column_names);

        if (preg_match("/^create unique/", $sql)) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing table index', __FUNCTION__);
        }

        $definition = array();
        $count = count($column_names);
        for ($i=0; $i<$count; ++$i) {
            $column_name = strtok($column_names[$i], ' ');
            $collation = strtok(' ');
            $definition['fields'][$column_name] = array(
                'position' => $i+1
            );
            if (!empty($collation)) {
                $definition['fields'][$column_name]['sorting'] =
                    ($collation=='ASC' ? 'ascending' : 'descending');
            }
        }

        if (empty($definition['fields'])) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing table index', __FUNCTION__);
        }
        return $definition;
    }

    // }}}
    // {{{ getTableConstraintDefinition()

    /**
     * Get the stucture of a constraint into an array
     *
     * @param string    $table      name of table that should be used in method
     * @param string    $constraint_name name of constraint that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTableConstraintDefinition($table, $constraint_name)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT sql FROM sqlite_master WHERE type='index' AND ";
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $query.= 'LOWER(name)=%s AND LOWER(tbl_name)=' . $db->quote(strtolower($table), 'text');
        } else {
            $query.= 'name=%s AND tbl_name=' . $db->quote($table, 'text');
        }
        $query.= ' AND sql NOT NULL ORDER BY name';
        $constraint_name_mdb2 = $db->getIndexName($constraint_name);
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $qry = sprintf($query, $db->quote(strtolower($constraint_name_mdb2), 'text'));
        } else {
            $qry = sprintf($query, $db->quote($constraint_name_mdb2, 'text'));
        }
        $sql = $db->queryOne($qry, 'text');
        if (PEAR::isError($sql) || empty($sql)) {
            // fallback to the given $index_name, without transformation
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                $qry = sprintf($query, $db->quote(strtolower($constraint_name), 'text'));
            } else {
                $qry = sprintf($query, $db->quote($constraint_name, 'text'));
            }
            $sql = $db->queryOne($qry, 'text');
        }
        if (PEAR::isError($sql)) {
            return $sql;
        }
        if (!$sql && $constraint_name == 'primary') {
            // search in table definition for PRIMARY KEYs
            $query = "SELECT sql FROM sqlite_master WHERE type='table' AND ";
            if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
                $query.= 'LOWER(name)='.$db->quote(strtolower($table), 'text');
            } else {
                $query.= 'name='.$db->quote($table, 'text');
            }
            $query.= " AND sql NOT NULL ORDER BY name";
            $sql = $db->queryOne($query, 'text');
            if (PEAR::isError($sql)) {
                return $sql;
            }
            if (preg_match("/\bPRIMARY\s+KEY\b\s*\(([^)]+)/i", $sql, $tmp)) {
                $definition = array();
                $definition['primary'] = true;
                $definition['fields'] = array();
                $column_names = split(',', $tmp[1]);
                $colpos = 1;
                foreach ($column_names as $column_name) {
                    $definition['fields'][$column_name] = array(
                        'position' => $colpos++
                    );
                }
                return $definition;
            }
            $sql = false;
        }
        if (!$sql) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                $constraint_name . ' is not an existing table constraint', __FUNCTION__);
        }

        $sql = strtolower($sql);
        $start_pos = strpos($sql, '(');
        $end_pos = strrpos($sql, ')');
        $column_names = substr($sql, $start_pos+1, $end_pos-$start_pos-1);
        $column_names = split(',', $column_names);

        if (!preg_match("/^create unique/", $sql)) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                $constraint_name . ' is not an existing table constraint', __FUNCTION__);
        }

        $definition = array();
        $definition['unique'] = true;
        $count = count($column_names);
        for ($i=0; $i<$count; ++$i) {
            $column_name = strtok($column_names[$i]," ");
            $collation = strtok(" ");
            $definition['fields'][$column_name] = array(
                'position' => $i+1
            );
            if (!empty($collation)) {
                $definition['fields'][$column_name]['sorting'] =
                    ($collation=='ASC' ? 'ascending' : 'descending');
            }
        }

        if (empty($definition['fields'])) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                $constraint_name . ' is not an existing table constraint', __FUNCTION__);
        }
        return $definition;
    }

    // }}}
    // {{{ getTriggerDefinition()

    /**
     * Get the structure of a trigger into an array
     *
     * EXPERIMENTAL
     *
     * WARNING: this function is experimental and may change the returned value
     * at any time until labelled as non-experimental
     *
     * @param string    $trigger    name of trigger that should be used in method
     * @return mixed data array on success, a MDB2 error on failure
     * @access public
     */
    function getTriggerDefinition($trigger)
    {
        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        $query = "SELECT name as trigger_name,
                         tbl_name AS table_name,
                         sql AS trigger_body,
                         NULL AS trigger_type,
                         NULL AS trigger_event,
                         NULL AS trigger_comment,
                         1 AS trigger_enabled
                    FROM sqlite_master
                   WHERE type='trigger'";
        if ($db->options['portability'] & MDB2_PORTABILITY_FIX_CASE) {
            $query.= ' AND LOWER(name)='.$db->quote(strtolower($trigger), 'text');
        } else {
            $query.= ' AND name='.$db->quote($trigger, 'text');
        }
        $types = array(
            'trigger_name'    => 'text',
            'table_name'      => 'text',
            'trigger_body'    => 'text',
            'trigger_type'    => 'text',
            'trigger_event'   => 'text',
            'trigger_comment' => 'text',
            'trigger_enabled' => 'boolean',
        );
        $def = $db->queryRow($query, $types, MDB2_FETCHMODE_ASSOC);
        if (PEAR::isError($def)) {
            return $def;
        }
        if (empty($def)) {
            return $db->raiseError(MDB2_ERROR_NOT_FOUND, null, null,
                'it was not specified an existing trigger', __FUNCTION__);
        }
        if (preg_match("/^create\s+(?:temp|temporary)?trigger\s+(?:if\s+not\s+exists\s+)?.*(before|after)?\s+(insert|update|delete)/Uims", $def['trigger_body'], $tmp)) {
            $def['trigger_type'] = strtoupper($tmp[1]);
            $def['trigger_event'] = strtoupper($tmp[2]);
        }
        return $def;
    }

    // }}}
    // {{{ tableInfo()

    /**
     * Returns information about a table
     *
     * @param string         $result  a string containing the name of a table
     * @param int            $mode    a valid tableInfo mode
     *
     * @return array  an associative array with the information requested.
     *                 A MDB2_Error object on failure.
     *
     * @see MDB2_Driver_Common::tableInfo()
     * @since Method available since Release 1.7.0
     */
    function tableInfo($result, $mode = null)
    {
        if (is_string($result)) {
           return parent::tableInfo($result, $mode);
        }

        $db =& $this->getDBInstance();
        if (PEAR::isError($db)) {
            return $db;
        }

        return $db->raiseError(MDB2_ERROR_NOT_CAPABLE, null, null,
           'This DBMS can not obtain tableInfo from result sets', __FUNCTION__);
    }
}

?>