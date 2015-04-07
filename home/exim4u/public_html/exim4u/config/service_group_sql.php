<?php

require_once("logger.php");
require_once("service_group.php");
require_once("service_user.php");

# http://framework.zend.com/manual/en/coding-standard.html
class GroupService4uSql implements IGroupService4u {

    private static $SELECT = 'select `name`, `email`, `enabled`, `memberCount`, `replyTo` from `ml` ';

    private $dbh;

    function __construct() {
        $a = func_get_args();
        if (func_num_args() == 1)
            $this->__construct1($a[0]);
    }

    function __construct1($myDb) {
        $this->dbh = $myDb;
    }

    public function setDb($myDb) {
        $this->dbh = $myDb;
    }

    public function getDb() {
        $this->dbh;
    }

    public function findMailingLists($domainId) {
        global $dbh, $domainID;
        global $firephp;
        $sql = self::$SELECT."where `domain_id` = :domain_id and `type`='h' order by `name`, `email`;";
        $firephp->log($sql, 'sql');
        $sth = $dbh->prepare($sql);
        $sth->execute(array(':domain_id'=>$domainId));
        $mls = array();
        $ml = null;
        while ($row = $sth->fetch()) {
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
	global $dbh, $domainID;
        global $firephp;
        $firephp->log($mlName, 'delete mailing list');
        $sql = "delete from `ml` where `domain_id` = :domain_id and `name` = :name;";
        $firephp->log($sql, 'sql');
	$sth = $dbh->prepare($sql);
        $success = $sth->execute(array(':domain_id'=>$domainId, ':name'=>$mlName));
	if (!$success) {
            throw new Exception("Failed to delete Mailing List {$mlName} in domain {$domainId}");
	}
    }

    public function changeMailingListStatus($domainId, $mlName, $enabled) {
	global $dbh, $domainID;
        global $firephp;
        $firephp->log('toggle mailing list '.$mlName.' from domain '.$domainId.' to '.$enabled);
        $sql = "update `ml` set `enabled` = ".($enabled?1:0).
                " where `domain_id` = :domain_id and `name` = :name}';";
        $firephp->log($sql, 'sql');
        $firephp->log($enabled, 'enabled');
	$success = $sth = $dbh->prepare($sql);
        $sth->execute(array(':domain_id'=>$domainId, ':name'=>$mlName));
	if (!$success) {
            throw new Exception("Failed to change status of Mailing List {$mlName} in domain {$domainId}");
	}
    }

    public function getMailingList($domainId, $mlName) {
	global $dbh, $domainID;
        global $firephp;
        $sql = self::$SELECT.
            " where `domain_id` = :domain_id and `name` = :name".
            " order by `name`, `email`";
        $firephp->log($sql, 'sql');
	$sth = $dbh->prepare($sql);
        $sth->execute(array(':domain_id'=>$domainId, ':name'=>$mlName));
        $mls = array();
        $ml = null;
        while ($row = $sth->fetch()) {
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
	global $dbh, $domainID;
        global $firephp;
        if (!isset($ml) or $ml->getName() == null)
           throw new Exception("Required non null Mailing List with non null name");
        $name = $ml->getName();
        $replyTo = $ml->getReplyTo();

        $sql = "select `enabled` from `ml` where `domain_id` = :domain_id and `name` = :name limit 1;";
        $firephp->log($sql, 'sql');
	$sth = $dbh->prepare($sql);
        $sth->execute(array(':domain_id'=>$domainId, ':name'=>$name));
        $enabled = ($row = $sth->fetch()) ? $row['enabled'] : 1;

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
	    $sql .= "select :domain_id, :name, :email, :enabled, :memberCount, :type, :replyTo";
            $memberIndex++;
        $sql .= ';';
        $firephp->log($sql, 'sql');
	$sth = $dbh->prepare($sql);
	$success = $sth->execute(array(':domain_id'=>$domainId,
		':name'=>$name,
		':email'=>$email->toString(),
		':enabled'=>$enabled,
		':memberCount'=>$memberCount,
		':type'=>$type,
		':replyTo'=>$replyTo
		));
	if(!$success) {
            throw new Exception("Failed to change content of Mailing List {$name} in domain {$domainId}");
	    }
    	}
    }

    public function findMailingListsInternal($domainId, $mlName) {
	global $dbh, $domainID;
        global $firephp;
        $sql =  "select `name`, `email`, `enabled`, `memberCount`, `replyTo` from `ml` ".
                "where `domain_id` = :domain_id";
        if (isset($mlName)) 
            $sql .= " and `name` = :name";
        $sql .= " order by `name`, `email`";
        $firephp->log($sql, 'sql');

	$sth = $dbh->prepare($sql);
	if (isset($mlName))
	{
	        $sth->execute(array(':domain_id'=>$domainId, ':name'=>$mlName));
	} else
	{
		$sth->execute(array(':domain_id'=>$domainId));
	}
        $mls = array();
        $ml = null;
        while ($row = $sth->fetch()) {
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

