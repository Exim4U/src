<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
/**
* LDIF.php
*
* PHP version 4, 5
*
* @category  Net
* @package   Net_LDAP
* @author    Benedikt Hallinger <beni@php.net>
* @copyright 2003-2007 Tarjej Huse, Jan Wagner, Del Elson, Benedikt Hallinger
* @license   http://www.gnu.org/copyleft/lesser.html LGPL
* @version   CVS: $Id: LDIF.php,v 1.33 2008/10/26 15:31:06 clockwerx Exp $
* @link      http://pear.php.net/package/Net_LDAP/
*/

require_once 'PEAR.php';
require_once 'Net/LDAP.php';
require_once 'Net/LDAP/Entry.php';
require_once 'Net/LDAP/Util.php';

/**
* LDIF capabilitys for Net_LDAP, closely taken from PERLs Net::LDAP
*
* It provides a means to convert between Net_LDAP_Entry objects and LDAP entries
* represented in LDIF format files. Reading and writing are supported and may
* manipulate single entries or lists of entries.
*
* Usage example:
* <code>
* // Read and parse an ldif-file into Net_LDAP_Entry objects
* // and print out the DNs. Store the entries for later use.
* require 'Net/LDAP/LDIF.php';
* $options = array(
*       'onerror' => 'die'
* );
* $entries = array();
* $ldif = new Net_LDAP_LDIF('test.ldif', 'r', $options);
* do {
*       $entry = $ldif->read_entry();
*       $dn    = $entry->dn();
*       echo " done building entry: $dn\n";
*       array_push($entries, $entry);
* } while (!$ldif->eof());
* $ldif->done();
*
*
* // write those entries to another file
* $ldif = new Net_LDAP_LDIF('test.out.ldif', 'w', $options);
* $ldif->write_entry($entries);
* $ldif->done();
* </code>
*
* @category Net
* @package  Net_LDAP
* @author   Benedikt Hallinger <beni@php.net>
* @license  http://www.gnu.org/copyleft/lesser.html LGPL
* @link     http://pear.php.net/package/Net_LDAP/
* @see      http://www.ietf.org/rfc/rfc2849.txt
* @todo     Error handling should be PEARified
* @todo     LDAPv3 controls are not implemented yet
*/
class Net_LDAP_LDIF extends PEAR
{
    /**
    * Options
    *
    * @access private
    * @var array
    */
    var $_options = array(
        'encode'    => 'base64',
        'onerror'   => 'undef',
        'change'    => 0,
        'lowercase' => 0,
        'sort'      => 0,
        'version'   => 1,
        'wrap'      => 78,
        'raw'       => ''
    );

    /**
    * Errorcache
    *
    * @access private
    * @var array
    */
    var $_error = array(
        'error' => null,
        'line'  => 0
    );

    /**
    * Filehandle for read/write
    *
    * @access private
    * @var array
    */
    var $_FH = null;

    /**
    * Says, if we opened the filehandle ourselves
    *
    * @access private
    * @var array
    */
    var $_FH_opened = false;

    /**
    * Linecounter for input file handle
    *
    * @access private
    * @var array
    */
    var $_input_line = 0;

    /**
    * counter for processed entries
    *
    * @access private
    * @var int
    */
    var $_entrynum = 0;

    /**
    * Mode we are working in
    *
    * Either 'r', 'a' or 'w'
    *
    * @access private
    * @var string
    */
    var $_mode = false;

    /**
    * Tells, if the LDIF version string was already written
    *
    * @access private
    * @var boolean
    */
    var $_version_written = false;

    /**
    * Cache for lines that have build the current entry
    *
    * @access private
    * @var boolean
    */
    var $_lines_cur = array();

    /**
    * Cache for lines that will build the next entry
    *
    * @access private
    * @var boolean
    */
    var $_lines_next = array();

    /**
    * Open LDIF file for reading or for writing
    *
    * new (FILE):
    * Open the file read-only. FILE may be the name of a file
    * or an already open filehandle.
    * If the file doesn't exist, it will be created if in write mode.
    *
    * new (FILE, MODE, OPTIONS):
    *     Open the file with the given MODE (see PHPs fopen()), eg "w" or "a".
    *     FILE may be the name of a file or an already open filehandle.
    *     PERLs Net_LDAP "FILE|" mode does not work curently.
    *
    *     OPTIONS is an associative array and may contain:
    *       encode => 'none' | 'canonical' | 'base64'
    *         Some DN values in LDIF cannot be written verbatim and have to be encoded in some way:
    *         'none'       No encoding.
    *         'canonical'  See "canonical_dn()" in Net::LDAP::Util.
    *         'base64'     Use base64. (default, this differs from the Perl interface.
    *                                   The perl default is "none"!)
    *
    *       onerror => 'die' | 'warn' | undef
    *         Specify what happens when an error is detected.
    *         'die'  Net_LDAP_LDIF will croak with an appropriate message.
    *         'warn' Net_LDAP_LDIF will warn (echo) with an appropriate message.
    *         undef  Net_LDAP_LDIF will not warn (default), use error().
    *
    *       change => 1
    *         Write entry changes to the LDIF file instead of the entries itself. I.e. write LDAP
    *         operations acting on the entries to the file instead of the entries contents.
    *         This writes the changes usually carried out by an update() to the LDIF file.
    *
    *       lowercase => 1
    *         Convert attribute names to lowercase when writing.
    *
    *       sort => 1
    *         Sort attribute names when writing entries according to the rule:
    *         objectclass first then all other attributes alphabetically sorted by attribute name
    *
    *       version => '1'
    *         Set the LDIF version to write to the resulting LDIF file.
    *         According to RFC 2849 currently the only legal value for this option is 1.
    *         When this option is set Net_LDAP_LDIF tries to adhere more strictly to
    *         the LDIF specification in RFC2489 in a few places.
    *         The default is undef meaning no version information is written to the LDIF file.
    *
    *       wrap => 78
    *         Number of columns where output line wrapping shall occur.
    *         Default is 78. Setting it to 40 or lower inhibits wrapping.
    *
    *       [NOT IMPLEMENTED] raw => REGEX
    *         Use REGEX to denote the names of attributes that are to be
    *         considered binary in search results if writing entries.
    *         Example: raw => "/(?i:^jpegPhoto|;binary)/i"
    *
    * @param string|ressource $file    Filename or filehandle
    * @param string           $mode    Mode to open filename
    * @param array            $options Options like described above
    */
    function Net_LDAP_LDIF($file, $mode = 'r', $options = array())
    {
        $this->PEAR('Net_LDAP_Error'); // default error class

        // First, parse options
        // todo: maybe implement further checks on possible values
        foreach ($options as $option => $value) {
            if (!array_key_exists($option, $this->_options)) {
                $this->_dropError('Net_LDAP_LDIF error: option '.$option.' not known!');
                return;
            } else {
                $this->_options[$option] = strtolower($value);
            }
        }

        // setup LDIF class
        $this->version($this->_options['version']);

        // setup file mode
        if (!preg_match('/^[rwa]\+?$/', $mode)) {
            $this->_dropError('Net_LDAP_LDIF error: file mode '.$mode.' not supported!');
        } else {
            $this->_mode = $mode;

            // setup filehandle
            if (is_resource($file)) {
                // TODO: checks on mode possible?
                $this->_FH =& $file;
            } else {
                $imode = substr($this->_mode, 0, 1);
                if ($imode == 'r') {
                    if (!file_exists($file)) {
                        $this->_dropError('Unable to open '.$file.' for read: file not found');
                        $this->_mode = false;
                    }
                    if (!is_readable($file)) {
                        $this->_dropError('Unable to open '.$file.' for read: permission denied');
                        $this->_mode = false;
                    }
                }

                if (($imode == 'w' || $imode == 'a')) {
                    if (file_exists($file)) {
                        if (!is_writable($file)) {
                            $this->_dropError('Unable to open '.$file.' for write: permission denied');
                            $this->_mode = false;
                        }
                    } else {
                        if (!@touch($file)) {
                            $this->_dropError('Unable to create '.$file.' for write: permission denied');
                            $this->_mode = false;
                        }
                    }
                }

                if ($this->_mode) {
                    $this->_FH = @fopen($file, $this->_mode);
                    if (false === $this->_FH) {
                        // Fallback; should never be reached if tests above are good enough!
                        $this->_dropError('Net_LDAP_LDIF error: Could not open file '.$file);
                    } else {
                        $this->_FH_opened = true;
                    }
                }
            }
        }
    }

    /**
    * Read one entry from the file and return it as a Net::LDAP::Entry object.
    *
    * @return Net_LDAP_Entry
    */
    function read_entry()
    {
        // read fresh lines, set them as current lines and create the entry
        $attrs = $this->next_lines(true);
        if (count($attrs) > 0) {
            $this->_lines_cur = $attrs;
        }
        return $this->current_entry();
    }

    /**
    * Returns true when the end of the file is reached.
    *
    * @return boolean
    */
    function eof()
    {
        return feof($this->_FH);
    }

    /**
    * Write the entry or entries to the LDIF file.
    *
    * If you want to build an LDIF file containing several entries AND
    * you want to call write_entry() several times, you must open the filehandle
    * in append mode ("a"), otherwise you will always get the last entry only.
    *
    * @param Net_LDAP_Entry|array $entries Entry or array of entries
    *
    * @return void
    * @todo implement operations on whole entries (adding a whole entry)
    */
    function write_entry($entries)
    {
        if (!is_array($entries)) {
            $entries = array($entries);
        }

        foreach ($entries as $entry) {
            $this->_entrynum++;
            if (!is_a($entry, 'Net_LDAP_Entry')) {
                $this->_dropError('Net_LDAP_LDIF error: entry '.$this->_entrynum.' is not an Net_LDAP_Entry object');
            } else {
                if ($this->_options['change']) {
                    // LDIF change mode
                    // fetch change information from entry
                    $entry_attrs_changes = $entry->getChanges();
                    $num_of_changes      = count($entry_attrs_changes['add'])
                                           + count($entry_attrs_changes['replace'])
                                           + count($entry_attrs_changes['delete']);

                    $is_changed = ($num_of_changes > 0 || $entry->willBeDeleted() || $entry->willBeMoved());

                    // write version if not done yet
                    // also write DN of entry
                    if ($is_changed) {
                        if (!$this->_version_written) {
                            $this->write_version();
                        }
                        $this->_writeDN($entry->currentDN());
                    }

                    // process changes
                    // TODO: consider DN add!
                    if ($entry->willBeDeleted()) {
                        $this->_writeLine("changetype: delete".PHP_EOL);
                    } elseif ($entry->willBeMoved()) {
                        $this->_writeLine("changetype: modrdn".PHP_EOL);
                        $olddn     = Net_LDAP_Util::ldap_explode_dn($entry->currentDN(), array('casefold' => 'none')); // maybe gives a bug if using multivalued RDNs
                        $oldrdn    = array_shift($olddn);
                        $oldparent = implode(',', $olddn);
                        $newdn     = Net_LDAP_Util::ldap_explode_dn($entry->dn(), array('casefold' => 'none')); // maybe gives a bug if using multivalued RDNs
                        $rdn       = array_shift($newdn);
                        $parent    = implode(',', $newdn);
                        $this->_writeLine("newrdn: ".$rdn.PHP_EOL);
                        $this->_writeLine("deleteoldrdn: 1".PHP_EOL);
                        if ($parent !== $oldparent) {
                            $this->_writeLine("newsuperior: ".$parent.PHP_EOL);
                        }
                        // TODO: What if the entry has attribute changes as well?
                        //       I think we should check for that and make a dummy
                        //       entry with the changes that is written to the LDIF file
                    } elseif ($num_of_changes > 0) {
                        // write attribute change data
                        $this->_writeLine("changetype: modify".PHP_EOL);
                        foreach ($entry_attrs_changes as $changetype => $entry_attrs) {
                            foreach ($entry_attrs as $attr_name => $attr_values) {
                                $this->_writeLine("$changetype: $attr_name".PHP_EOL);
                                if ($attr_values !== null) {
                                    $this->_writeAttribute($attr_name, $attr_values, $changetype);
                                }
                                $this->_writeLine("-".PHP_EOL);
                            }
                        }
                    }

                    // finish this entrys data if we had changes
                    if ($is_changed) {
                        $this->_finishEntry();
                    }
                } else {
                    // LDIF-content mode
                    // fetch attributes for further processing
                    $entry_attrs = $entry->getValues();

                    // sort and put objectclass-attrs to first position
                    if ($this->_options['sort']) {
                        ksort($entry_attrs);
                        if (array_key_exists('objectclass', $entry_attrs)) {
                            $oc = $entry_attrs['objectclass'];
                            unset($entry_attrs['objectclass']);
                            $entry_attrs = array_merge(array('objectclass' => $oc), $entry_attrs);
                        }
                    }

                    // write data
                    if (!$this->_version_written) {
                        $this->write_version();
                    }
                    $this->_writeDN($entry->dn());
                    foreach ($entry_attrs as $attr_name => $attr_values) {
                        $this->_writeAttribute($attr_name, $attr_values);
                    }
                    $this->_finishEntry();
                }
            }
        }
    }

    /**
    * Write version to LDIF
    *
    * If the object's version is defined, this method allows to explicitely write the version before an entry is written.
    * If not called explicitely, it gets called automatically when writing the first entry.
    *
    * @return void
    */
    function write_version()
    {
        $this->_version_written = true;
        return $this->_writeLine('version: '.$this->version().PHP_EOL, 'Net_LDAP_LDIF error: unable to write version');
    }

    /**
    * Get or set LDIF version
    *
    * If called without arguments it returns the version of the LDIF file or undef if no version has been set.
    * If called with an argument it sets the LDIF version to VERSION.
    * According to RFC 2849 currently the only legal value for VERSION is 1.
    *
    * @param int $version (optional) LDIF version to set
    *
    * @return int
    */
    function version($version = null)
    {
        if ($version !== null) {
            if ($version != 1) {
                $this->_dropError('Net_LDAP_LDIF error: illegal LDIF version set');
            } else {
                $this->_version = $version;
            }
        }
        return $this->_version;
    }

    /**
    * Returns the file handle the Net_LDAP_LDIF object reads from or writes to.
    *
    * You can, for example, use this to fetch the content of the LDIF file yourself
    *
    * @return null|resource
    */
    function &handle()
    {
        if (!is_resource($this->_FH)) {
            $this->_dropError('Net_LDAP_LDIF error: invalid file resource');
            $null = null;
            return $null;
        } else {
            return $this->_FH;
        }
    }

    /**
    * Clean up
    *
    * This method signals that the LDIF object is no longer needed.
    * You can use this to free up some memory and close the file handle.
    * The file handle is only closed, if it was opened from Net_LDAP_LDIF.
    *
    * @return void
    */
    function done()
    {
        // close FH if we opened it
        if ($this->_FH_opened) {
            fclose($this->handle());
        }

        // free variables
        foreach (get_object_vars($this) as $name => $value) {
            unset($this->$name);
        }
    }

    /**
    * Returns last error message if error was found.
    *
    * Example:
    * <code>
    *  $ldif->someAction();
    *  if ($ldif->error()) {
    *     echo "Error: ".$ldif->error()." at input line: ".$ldif->error_lines();
    *  }
    * </code>
    *
    * @param boolean $as_string If set to true, only the message is returned
    *
    * @return false|Net_LDAP_Error
    */
    function error($as_string = false)
    {
        if (Net_LDAP::isError($this->_error['error'])) {
            return ($as_string)? $this->_error['error']->getMessage() : $this->_error['error'];
        } else {
            return false;
        }
    }

    /**
    * Returns lines that resulted in error.
    *
    * Perl returns an array of faulty lines in list context,
    * but we always just return an int because of PHPs language.
    *
    * @return int
    */
    function error_lines()
    {
        return $this->_error['line'];
    }

    /**
    * Returns the current Net::LDAP::Entry object.
    *
    * @return Net_LDAP_Entry|false
    */
    function current_entry()
    {
        return $this->parseLines($this->current_lines());
    }

    /**
    * Parse LDIF lines of one entry into an Net_LDAP_Entry object
    *
    * @param array $lines LDIF lines for one entry
    *
    * @return Net_LDAP_Entry|false Net_LDAP_Entry object for those lines
    * @todo what about file inclusions and urls? "jpegphoto:< file:///usr/local/directory/photos/fiona.jpg"
    */
    function parseLines($lines)
    {
        // parse lines into an array of attributes and build the entry
        $attributes = array();

        $dn = false;
        foreach ($lines as $line) {
            if (preg_match('/^(\w+)(:|::|:<)\s(.+)$/', $line, $matches)) {
                $attr  =& $matches[1];
                $delim =& $matches[2];
                $data  =& $matches[3];

                if ($delim == ':') {
                    // normal data
                    $attributes[$attr][] = $data;
                } elseif ($delim == '::') {
                    // base64 data
                    $attributes[$attr][] = base64_decode($data);
                } elseif ($delim == ':<') {
                    // file inclusion
                    // TODO: Is this the job of the LDAP-client or the server?
                    $this->_dropError('File inclusions are currently not supported');
                    //$attributes[$attr][] = ...;
                } else {
                    // since the pattern above, the delimeter cannot be something else.
                    $this->_dropError('Net_LDAP_LDIF parsing error: invalid syntax at parsing entry line: '.$line);
                    continue;
                }

                if (strtolower($attr) == 'dn') {
                    // DN line detected
                    $dn = $attributes[$attr][0];  // save possibly decoded DN
                    unset($attributes[$attr]);    // remove wrongly added "dn: " attribute
                }
            } else {
                // line not in "attr: value" format -> ignore
                // maybe we should rise an error here, but this should be covered by
                // next_lines() already. A problem arises, if users try to feed data of
                // several entries to this method - the resulting entry will
                // get wrong attributes. However, this is already mentioned in the
                // methods documentation above.
            }
        }

        if (false === $dn) {
            $this->_dropError('Net_LDAP_LDIF parsing error: unable to detect DN for entry');
            return false;
        } else {
            $newentry = Net_LDAP_Entry::createFresh($dn, $attributes);
            return $newentry;
        }
    }

    /**
    * Returns the lines that generated the current Net::LDAP::Entry object.
    *
    * Note that this returns an empty array if no lines have been read so far.
    *
    * @return array Array of lines
    */
    function current_lines()
    {
        return $this->_lines_cur;
    }

    /**
    * Returns the lines that will generate the next Net::LDAP::Entry object.
    *
    * If you set $force to TRUE then you can iterate over the lines that build
    * up entries manually. Otherwise, iterating is done using {@link read_entry()}.
    * Force will move the file pointer forward, thus returning the next entries lines.
    *
    * Wrapped lines will be unwrapped. Comments are stripped.
    *
    * @param boolean $force Set this to true if you want to iterate over the lines manually
    *
    * @return array
    */
    function next_lines($force = false)
    {
        // if we already have those lines, just return them, otherwise read
        if (count($this->_lines_next) == 0 || $force) {
            $this->_lines_next = array(); // empty in case something was left (if used $force)
            $entry_done        = false;
            $fh                = &$this->handle();
            $commentmode       = false; // if we are in an comment, for wrapping purposes
            $datalines_read    = 0;     // how many lines with data we have read

            while (!$entry_done && !$this->eof()) {
                $this->_input_line++;
                // Read line. Remove line endings, we want only data;
                // this is okay since ending spaces should be encoded
                $data = rtrim(fgets($fh));
                if ($data === false) {
                    // error only, if EOF not reached after fgets() call
                    if (!$this->eof()) {
                        $this->_dropError('Net_LDAP_LDIF error: error reading from file at input line '.$this->_input_line, $this->_input_line);
                    }
                    break;
                } else {
                    if (count($this->_lines_next) > 0 && preg_match('/^$/', $data)) {
                        // Entry is finished if we have an empty line after we had data
                        $entry_done = true;

                        // Look ahead if the next EOF is nearby. Comments and empty
                        // lines at the file end may cause problems otherwise
                        $current_pos = ftell($fh);
                        $data        = fgets($fh);
                        while (!feof($fh)) {
                            if (preg_match('/^\s*$/', $data) || preg_match('/^#/', $data)) {
                                // only empty lines or comments, continue to seek
                                // TODO: Known bug: Wrappings for comments are okay but are treaten as
                                //       error, since we do not honor comment mode here.
                                //       This should be a very theoretically case, however
                                //       i am willing to fix this if really necessary.
                                $this->_input_line++;
                                $current_pos = ftell($fh);
                                $data        = fgets($fh);
                            } else {
                                // Data found if non emtpy line and not a comment!!
                                // Rewind to position prior last read and stop lookahead
                                fseek($fh, $current_pos);
                                break;
                            }
                        }
                        // now we have either the file pointer at the beginning of
                        // a new data position or at the end of file causing feof() to return true

                    } else {
                        // build lines
                        if (preg_match('/^version:\s(.+)$/', $data, $match)) {
                            // version statement, set version
                            $this->version($match[1]);
                        } elseif (preg_match('/^\w+::?\s.+$/', $data)) {
                            // normal attribute: add line
                            $commentmode         = false;
                            $this->_lines_next[] = trim($data);
                            $datalines_read++;
                        } elseif (preg_match('/^\s(.+)$/', $data, $matches)) {
                            // wrapped data: unwrap if not in comment mode
                            if (!$commentmode) {
                                if ($datalines_read == 0) {
                                    // first line of entry: wrapped data is illegal
                                    $this->_dropError('Net_LDAP_LDIF error: illegal wrapping at input line '.$this->_input_line, $this->_input_line);
                                } else {
                                    $last                = array_pop($this->_lines_next);
                                    $last                = $last.trim($matches[1]);
                                    $this->_lines_next[] = $last;
                                    $datalines_read++;
                                }
                            }
                        } elseif (preg_match('/^#/', $data)) {
                            // LDIF comments
                            $commentmode = true;
                        } elseif (preg_match('/^\s*$/', $data)) {
                            // empty line but we had no data for this
                            // entry, so just ignore this line
                            $commentmode = false;
                        } else {
                            $this->_dropError('Net_LDAP_LDIF error: invalid syntax at input line '.$this->_input_line, $this->_input_line);
                            continue;
                        }

                    }
                }
            }
        }
        return $this->_lines_next;
    }

    /**
    * Convert an attribute and value to LDIF string representation
    *
    * It honors correct encoding of values according to RFC 2849.
    * Line wrapping will occur at the configured maximum but only if
    * the value is greater than 40 chars.
    *
    * @param string $attr_name  Name of the attribute
    * @param string $attr_value Value of the attribute
    *
    * @access private
    * @return string LDIF string for that attribute and value
    */
    function _convertAttribute($attr_name, $attr_value)
    {
        // Handle empty attribute or process
        if (strlen($attr_value) == 0) {
            $attr_value = " ";
        } else {
            $base64 = false;
            // ASCII-chars that are NOT safe for the
            // start and for being inside the value.
            // These are the int values of those chars.
            $unsafe_init = array(0, 10, 13, 32, 58, 60);
            $unsafe      = array(0, 10, 13);

            // Test for illegal init char
            $init_ord = ord(substr($attr_value, 0, 1));
            if ($init_ord >= 127 || in_array($init_ord, $unsafe_init)) {
                $base64 = true;
            }

            // Test for illegal content char
            for ($i = 0; $i < strlen($attr_value); $i++) {
                $char = substr($attr_value, $i, 1);
                if (ord($char) >= 127 || in_array($init_ord, $unsafe)) {
                    $base64 = true;
                }
            }

            // Test for ending space
            if (substr($attr_value, -1) == ' ') {
                $base64 = true;
            }

            // If converting is needed, do it
            if ($base64 && !($this->_options['raw'] && preg_match($this->_options['raw'], $attr_name))) {
                $attr_name .= ':';
                $attr_value = base64_encode($attr_value);
            }

            // Lowercase attr names if requested
            if ($this->_options['lowercase']) {
                $attr_name = strtolower($attr_name);
            }

            // Handle line wrapping
            if ($this->_options['wrap'] > 40 && strlen($attr_value) > $this->_options['wrap']) {
                $attr_value = wordwrap($attr_value, $this->_options['wrap'], PHP_EOL." ", true);
            }
        }

        return $attr_name.': '.$attr_value;
    }

    /**
    * Convert an entries DN to LDIF string representation
    *
    * It honors correct encoding of values according to RFC 2849.
    *
    * @param string $dn UTF8-Encoded DN
    *
    * @access private
    * @return string LDIF string for that DN
    * @todo I am not sure, if the UTF8 stuff is correctly handled right now
    */
    function _convertDN($dn)
    {
        $base64 = false;
        // ASCII-chars that are NOT safe for the
        // start and for being inside the dn.
        // These are the int values of those chars.
        $unsafe_init = array(0, 10, 13, 32, 58, 60);
        $unsafe      = array(0, 10, 13);

        // Test for illegal init char
        $init_ord = ord(substr($dn, 0, 1));
        if ($init_ord >= 127 || in_array($init_ord, $unsafe_init)) {
            $base64 = true;
        }

        // Test for illegal content char
        for ($i = 0; $i < strlen($dn); $i++) {
            $char = substr($dn, $i, 1);
            if (ord($char) >= 127 || in_array($init_ord, $unsafe)) {
                $base64 = true;
            }
        }

        // Test for ending space
        if (substr($dn, -1) == ' ') {
            $base64 = true;
        }

        // if converting is needed, do it
        return ($base64)? 'dn:: '.base64_encode($dn) : 'dn: '.$dn;
    }

    /**
    * Writes an attribute to the filehandle
    *
    * @param string       $attr_name   Name of the attribute
    * @param string|array $attr_values Single attribute value or array with attribute values
    *
    * @access private
    * @return void
    */
    function _writeAttribute($attr_name, $attr_values)
    {
        // write out attribute content
        if (!is_array($attr_values)) {
            $attr_values = array($attr_values);
        }
        foreach ($attr_values as $attr_val) {
            $line = $this->_convertAttribute($attr_name, $attr_val).PHP_EOL;
            $this->_writeLine($line, 'Net_LDAP_LDIF error: unable to write attribute '.$attr_name.' of entry '.$this->_entrynum);
        }
    }

    /**
    * Writes a DN to the filehandle
    *
    * @param string $dn DN to write
    *
    * @access private
    * @return void
    */
    function _writeDN($dn)
    {
        // prepare DN
        if ($this->_options['encode'] == 'base64') {
            $dn = $this->_convertDN($dn).PHP_EOL;
        } elseif ($this->_options['encode'] == 'canonical') {
            $dn = Net_LDAP_Util::canonical_dn($dn, array('casefold' => 'none')).PHP_EOL;
        } else {
            $dn = $dn.PHP_EOL;
        }
        $this->_writeLine($dn, 'Net_LDAP_LDIF error: unable to write DN of entry '.$this->_entrynum);
    }

    /**
    * Finishes an LDIF entry
    *
    * @access private
    * @return void
    */
    function _finishEntry()
    {
        $this->_writeLine(PHP_EOL, 'Net_LDAP_LDIF error: unable to close entry '.$this->_entrynum);
    }

    /**
    * Just write an arbitary line to the filehandle
    *
    * @param string $line  Content to write
    * @param string $error If error occurs, drop this message
    *
    * @access private
    * @return true|false
    */
    function _writeLine($line, $error = 'Net_LDAP_LDIF error: unable to write to filehandle')
    {
        if (is_resource($this->handle()) && fwrite($this->handle(), $line, strlen($line)) === false) {
            $this->_dropError($error);
            return false;
        } else {
            return true;
        }
    }

    /**
    * Optionally raises an error and pushes the error on the error cache
    *
    * @param string $msg  Errortext
    * @param int    $line Line in the LDIF that caused the error
    *
    * @access private
    * @return void
    */
    function _dropError($msg, $line = null)
    {
        $this->_error['error'] = new Net_LDAP_Error($msg);
        if ($line !== null) {
            $this->_error['line'] = $line;
        }

        if ($this->_options['onerror'] == 'die') {
            die($msg.PHP_EOL);
        } elseif ($this->_options['onerror'] == 'warn') {
            echo $msg.PHP_EOL;
        }
    }
}
?>
