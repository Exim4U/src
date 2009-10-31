<?php

require_once 'Horde/IMAP/ACL/rfc4314.php';

/**
 * IMP_IMAP_ACL_rfc4314:: class extends the IMAP_ACL_rfc4314 class in
 * order to ensure backwards compatibility with Horde 3.0.
 *
 * $Horde: imp/lib/IMAP/ACL/rfc4314.php,v 1.5.2.3 2009/01/06 15:24:07 jan Exp $
 *
 * Copyright 2006-2009 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Eric Garrido <ekg2002@columbia.edu>
 * @since   IMP 4.2
 * @package Horde_IMAP
 */
class IMP_IMAP_ACL_rfc4314 extends IMAP_ACL_rfc4314 {

    /**
     * Hash containing the list of possible rights and a human
     * readable, short title of each
     *
     * Array (
     *     right-id => right-title
     * )
     *
     * @var array
     */
    var $_rightsListTitles = array();

    function getRightsTitles()
    {
        return $this->_rightsListTitles;
    }

    /**
     * Constructor.
     *
     * @param array $params  Any additional parameters this driver may need.
     */
    function IMP_IMAP_ACL_rfc4314($params = array())
    {
        parent::IMAP_ACL_rfc4314($params);

        $this->_rightsListTitles = array(
             'l' => _("List"),
             'r' => _("Read"),
             's' => _("Mark (Seen)"),
             'w' => _("Mark (Other)"),
             'i' => _("Insert"),
             'p' => _("Post"),
             'k' => _("Create Folders"),
             'x' => _("Delete Folders"),
             't' => _("Delete"),
             'e' => _("Purge"),
             'a' => _("Administer")
        );
    }

}
