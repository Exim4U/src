<?php

require_once 'SyncML/Command.php';

/**
 * The SyncML_Command_Map class provides a SyncML implementation of the Map
 * command as defined in SyncML Representation Protocol, version 1.1, section
 * 5.5.8.
 *
 * The Map command is used to update identifier maps.
 *
 * $Horde: framework/SyncML/SyncML/Command/Map.php,v 1.1.10.12 2009/04/05 20:24:43 jan Exp $
 *
 * Copyright 2004-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Karsten Fourmont <karsten@horde.org>
 * @author  Jan Schneider <jan@horde.org>
 * @since   Horde 3.0
 * @package SyncML
 */
class SyncML_Command_Map extends SyncML_Command {

    /**
     * Name of the command.
     *
     * @var string
     */
    var $_cmdName = 'Map';

    /**
     * Source database of the Map command.
     *
     * @var string
     */
    var $_sourceLocURI;

    /**
     * Target database of the Map command.
     *
     * @var string
     */
    var $_targetLocURI;

    /**
     * Recipient map item specifier.
     *
     * @var string
     */
    var $_mapTarget;

    /**
     * Originator map item specifier.
     *
     * @var string
     */
    var $_mapSource;

    /**
     * Whether we have seen a MapItem element.
     *
     * @var boolean
     */
    var $_mapItem = false;

    /**
     * Start element handler for the XML parser, delegated from
     * SyncML_ContentHandler::startElement().
     *
     * @param string $uri      The namespace URI of the element.
     * @param string $element  The element tag name.
     * @param array $attrs     A hash with the element's attributes.
     */
    function startElement($uri, $element, $attrs)
    {
        parent::startElement($uri, $element, $attrs);

        if (count($this->_stack) == 2 &&
            $element == 'MapItem') {
            $this->_mapItem = true;
            unset($this->_mapTarget);
            unset($this->_mapSource);
        }
    }

    /**
     * End element handler for the XML parser, delegated from
     * SyncML_ContentHandler::endElement().
     *
     * @param string $uri      The namespace URI of the element.
     * @param string $element  The element tag name.
     */
    function endElement($uri, $element)
    {
        switch (count($this->_stack)) {
        case 3:
            if ($element == 'LocURI') {
                if ($this->_stack[1] == 'Source') {
                    $this->_sourceLocURI = trim($this->_chars);
                } elseif ($this->_stack[1] == 'Target') {
                    $this->_targetLocURI = trim($this->_chars);
                }
            }
            break;

        case 4:
            if ($element == 'LocURI') {
                if ($this->_stack[2] == 'Source') {
                    $this->_mapSource = trim($this->_chars);
                } elseif ($this->_stack[2] == 'Target') {
                    $this->_mapTarget = trim($this->_chars);
                }
            }
            break;
        }

        parent::endElement($uri, $element);
    }

    /**
     * Implements the actual business logic of the Alert command.
     *
     * @todo No OK response on error.
     */
    function handleCommand($debug = false)
    {
        if (!$debug && $this->_mapItem) {
            $state = &$_SESSION['SyncML.state'];
            $sync = &$state->getSync($this->_targetLocURI);
            if (!$state->authenticated) {
                $GLOBALS['backend']->logMessage(
                    'Not authenticated while processing MapItem',
                    __FILE__, __LINE__, PEAR_LOG_ERR);
            } else {
                $sync->createUidMap($this->_targetLocURI,
                                    $this->_mapSource,
                                    $this->_mapTarget);
            }
        }

        // Create status response.
        $this->_outputHandler->outputStatus($this->_cmdID,$this->_cmdName,
                                            RESPONSE_OK,
                                            $this->_targetLocURI,
                                            $this->_sourceLocURI);
    }

}
