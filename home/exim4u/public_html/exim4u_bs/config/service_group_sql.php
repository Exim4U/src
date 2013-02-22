<?php

require_once("logger.php");
require_once("service_group.php");
require_once("service_user.php");

# http://framework.zend.com/manual/en/coding-standard.html
class GroupService4uSql implements IGroupService4u {

    private static $SELECT = 'select `name`, `email`, `enabled`, `memberCount`, `replyTo` from `ml` ';

    private $db;

    function __construct() {
        $a = func_get_args();
        if (func_num_args() == 1)
            $this->__construct1($a[0]);
    }

    function __construct1($myDb) {
        $this->db = $myDb;
    }

    public function setDb($myDb) {
        $this->db = $myDb;
    }

    public function getDb() {
        $this->db;
    }

    public function findMailingLists($domainId) {
        global $firephp;
        $sql = self::$SELECT."where `domain_id` = {$domainId} and `type`='h' order by `name`, `email`;";
        $firephp->log($sql, 'sql');
        $result = $this->db->query($sql);
        $mls = array();
        $ml = null;
        while ($row = $result->fetchRow()) {
            $mlName = $row['name'];
            if ($ml == null or $ml->getName() != $mlName) {
                $ml = new MailingListPreview4u($mlName);
                $ml->setEnabled($row['enabled'] != 0);
                $ml->setEmailCount($row['memberCount']);
                $ml->setReplyTo($row['replyTo']);
                $mls[$mlName] = $ml;
            }
            $ml->addEmail($row['email']);
        }
        return $mls;
    }
    
    public function deleteMailingList($domainId, $mlName) {
        global $firephp;
        $firephp->log($mlName, 'delete mailing list');
        $sql = "delete from `ml` where `domain_id` = {$domainId} and `name` = '{$mlName}';";
        $firephp->log($sql, 'sql');
        $result = $this->db->query($sql);
        if (DB::isError($result))
            throw new Exception("Failed to delete Mailing List {$mlName} in domain {$domainId}");
    }

    public function changeMailingListStatus($domainId, $mlName, $enabled) {
        global $firephp;
        $firephp->log('toggle mailing list '.$mlName.' from domain '.$domainId.' to '.$enabled);
        $sql = "update `ml` set `enabled` = ".($enabled?1:0).
                " where `domain_id` = {$domainId} and `name` = '{$mlName}';";
        $firephp->log($sql, 'sql');
        $firephp->log($enabled, 'enabled');
        $result = $this->db->query($sql);
        if (DB::isError($result))
            throw new Exception("Failed to change status of Mailing List {$mlName} in domain {$domainId}");
    }

    public function getMailingList($domainId, $mlName) {
        global $firephp;
        $sql = self::$SELECT.
            " where `domain_id` = {$domainId} and `name` = '{$mlName}'".
            " order by `name`, `email`";
        $firephp->log($sql, 'sql');
        $result = $this->db->query($sql);
        $mls = array();
        $ml = null;
        while ($row = $result->fetchRow()) {
            if ($ml == null) {
                $ml = new MailingList4u($row['name']);
                $ml->setEnabled($row['enabled'] != 0);
                //$ml->setEmailCount($row['memberCount']);
                $ml->setReplyTo($row['replyTo']);
            }
            $ml->addEmail($row['email']);
        }
        return $ml;
    }

    public function saveOrUpdateMailingListContent($domainId, $ml) {
        global $firephp;
        if (!isset($ml) or $ml->getName() == null)
           throw new Exception("Required non null Mailing List with non null name");
        $name = $ml->getName();
        $replyTo = $ml->getReplyTo();

        $sql = "select `enabled` from `ml` where `domain_id` = {$domainId} and `name` = '{$name}' limit 1;";
        $firephp->log($sql, 'sql');
        $result = $this->db->query($sql);
        $enabled = ($row = $result->fetchRow()) ? $row['enabled'] : 1;

        $this->deleteMailingList($domainId, $name);
        
        $sql = null;
        $memberCount = $ml->getEmailCount();
        $memberIndex = 0;
        foreach ($ml->getEmails() as $email) {
            if ($sql == null) {
                $sql = "insert into `ml` (`domain_id`, `name`, `email`, `enabled`, `memberCount`, `type`, `replyTo`)\n";
            } else {
                $sql .= "\nunion ";
            }
            $type = $memberIndex<2 ? 'h' : 'm';
            $sql .= "select {$domainId}, '{$name}', '{$email->toString()}', {$enabled}, {$memberCount}, '{$type}', '{$replyTo}'";
            $memberIndex++;
        }
        $sql .= ';';
        $firephp->log($sql, 'sql');
        $result = $this->db->query($sql);
        if (DB::isError($result))
            throw new Exception("Failed to change content of Mailing List {$name} in domain {$domainId}");
    }

    public function findMailingListsInternal($domainId, $mlName) {
        global $firephp;
        $sql =  "select `name`, `email`, `enabled`, `memberCount`, `replyTo` from `ml` ".
                "where `domain_id` = {$domainId}";
        if (isset($mlName)) 
            $sql .= " and `name` = '{$mlName}'";
        $sql .= " order by `name`, `email`";
        $firephp->log($sql, 'sql');

        $result = $this->db->query($sql);
        $mls = array();
        $ml = null;
        while ($row = $result->fetchRow()) {
            $mlName = $row['name'];
            if ($ml == null or (isset($mlName) and $ml->getName() != $mlName)) {
                $ml = new MailingListPreview4u($mlName);
                $ml->setEnabled($row['enabled'] != 0);
                $ml->setEmailCount($row['memberCount']);
                $ml->setReplyTo($row['replyTo']);
                $mls[$mlName] = $ml;
            }
            $ml->addEmail($row['email']);
        }
        return isset($mlName) ? $mls : $ml;
    }
    
    public function findEmailGroups() {
    }
    
}

