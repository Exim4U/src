<?php

require_once("logger.php");
require_once("service_user.php");

interface IGroupService4u {

    // Mailing List methods
    public function findMailingLists($domainId);
    public function deleteMailingList($domainId, $mlName);
    public function changeMailingListStatus($domainId, $mlName, $enabled);
    public function saveOrUpdateMailingListContent($domainId, $ml);
    public function getMailingList($domainId, $mlName);

    // Groups methods
    public function findEmailGroups();

}

class ReplyTo4u {
    public static $SENDER = 's';
    public static $MAILING_LIST = 'm';
    private static $ENUM = array('s', 'm');

    public static function check($theValue) {
        if (!(in_array($theValue, self::$ENUM)))
            throw new Exception("Unknown reply-to '".$theValue."'");
    }
}

class MailingListPreview4u {
    private $list = array(), $name, $enabled = true, $emailCount;
    private $description, $replyTo;

    function __construct() {
        $a = func_get_args();
        if (func_num_args() == 1)
            $this->__construct1($a[0]);
    }
    function __construct1($theName) {
        $this->name = $theName;
    }
    public function setName($theName) {
        $this->name = $theName;
    }
    public function getName() {
        return $this->name;
    }
    public function setEmailCount($i) {
        $this->emailCount = $i;
    }
    public function getEmailCount() {
        return $this->emailCount;
    }
    public function getEmails() {
        return $this->list;
    }
    public function addEmail($e) {
        $this->list[$e] = new Email4u($e);
    }
    public function setDescription($v) {
        $this->description = $v;
    }
    public function getDescription() {
        return $this->description;
    }
    public function isEnabled() {
        return $this->enabled;
    }
    public function setEnabled($v) {
        if (! is_bool($v)) throw new Exception("A boolean is required here");
        $this->enabled = $v;
    }
    public function getReplyTo() {
        return $this->replyTo;
    }
    public function setReplyTo($v) {
        ReplyTo4u::check($v);
        $this->replyTo = $v;
    }
}

class MailingList4u {
    private $list = array(), $name, $enabled = true, $replyTo; 

    function __construct() {
        $this->replyTo = ReplyTo4u::$SENDER;
        $a = func_get_args();
        if (func_num_args() == 1)
            $this->__construct1($a[0]);
    }
    function __construct1($theName) {
        $this->name = $theName;
    }
    public function setName($theName) {
        // TODO : check for valid name (no space)
        $this->name = $theName;
    }
    public function getName() {
        return $this->name;
    }
    public function addEmail($e) {
        $this->list[$e] = new Email4u($e);
    }
    public function removeEmails() {
        $this->list = array();
    }
    public function removeEmail($e) {
        if (! is_string($e)) throw new Exception("A string is required here");
        unset($this->list[$e]);
    }
    public function getEmails() {
        return $this->list;
    }
    public function getEmailCount() {
        return count($this->list);
    }
    public function isEnabled() {
        return $this->enabled;
    }
    public function setEnabled($v) {
        if (! is_bool($v)) throw new Exception("A boolean is required here");
        $this->enabled = $v;
    }
    public function getReplyTo() {
        return $this->replyTo;
    }
    public function setReplyTo($v) {
        ReplyTo4u::check($v);
        $this->replyTo = $v;
    }
}

class Group4uHead {
    private $name, $enabled = true, $internal = true;

    public function setName($theName) {
        // TODO : check for valid name (no space)
        $this->name = $theName;
    }
    public function getName() {
        return $this->name;
    }
    public function isEnabled() {
        return $this->enabled;
    }
    public function setEnabled($v) {
        if (! is_bool($v)) throw new Exception("A boolean is required here");
        $this->enabled = $v;
    }
    public function isPublic() {
        return not($this->internal);
    }
    public function setPublic($v) {
        if (! is_bool($v)) throw new Exception("A boolean is required here");
        $this->internal = not($v);
    }
}

class Group4uMembers {
    private $list = array();


}

class GroupService4uMock implements IGroupService4u {

    private $mls = array();

    function __construct() {
        $ml = new MailingList4u();
        $ml->setName("kino");
        $ml->addEmail("Robert.Redford@twentycent.com");
        $ml->addEmail("John@farwest.com");
        $this->mls["kino"] = $ml;

        $ml = new MailingList4u();
        $ml->setName("official");
        $ml->addEmail("leak@gov.com");
        $ml->addEmail("anything@noop.com");
        $ml->setEnabled(false);
        $ml->setReplyTo(ReplyTo4u::$MAILING_LIST);
        $this->mls["official"] = $ml;
    }
    public function findMailingLists($domainId) {
        return $this->mls;
    }
    public function deleteMailingList($domainId, $mlName) {
        global $firephp;
        $firephp->log($mlName, 'delete mailing list');
        unset($this->mls[$mlName]);
    }
    public function changeMailingListStatus($domainId, $mlName, $enabled) {
        global $firephp;
        $firephp->log($mlName, 'toggle mailing list status ' + $enabled);
        $this->mls[$mlName]->setEnabled($enabled);
    }
    public function getMailingList($domainId, $mlName) {
        //global $firephp;
        //$firephp->log($mlName, 'looking for');
        return $this->mls[$mlName];
    }
    public function saveOrUpdateMailingListContent($domainId, $ml) {
        global $firephp;
        if (!isset($ml) or $ml->getName() == null)
           throw new Exception("Required non null Mailing List with non null name");
        $this->mls[$ml->getName()] = $ml;
        $firephp->log($ml->getName()." has ".$ml->getEmailCount()." emails");
    }
    public function findEmailGroups() {
    }
    
}

