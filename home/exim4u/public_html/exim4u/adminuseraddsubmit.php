<?php
  include_once dirname(__FILE__) . '/config/httpheaders.php';
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';

  # enforce limit on the maximum number of user accounts in the domain
  $query = "SELECT (count(users.user_id) < domains.max_accounts)
    OR (domains.max_accounts=0) AS allowed FROM users,domain
    WHERE users.domain_id=domains.domain_id
    AND domains.domain_id=:domain_id
	AND (users.type='local' OR users.type='piped')
    GROUP BY domains.max_accounts";
  $sth = $dbh->prepare($query);
  $success = $sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
  if ($success) {
    $domrow = $sth->fetch();
    if (!$domrow['allowed']) {
        header ("Location: adminuser.php?maxaccounts=true");
        die();
    }
  }

  # Strip off leading and trailing spaces
  $_POST['localpart'] = preg_replace("/^\s+/","",$_POST['localpart']);
  $_POST['localpart'] = preg_replace("/\s+$/","",$_POST['localpart']); 

  # get the settings for the domain 
  $query = "SELECT spamassassin,pipe,uid,gid,quotas,maxmsgsize FROM domains 
    WHERE domain_id=:domain_id";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
  if ($sth->rowCount()) {
  $row = $sth->fetch();
  }
  
  # Fix the boolean values
  if (isset($_POST['admin'])) {
    $_POST['admin'] = 1;
  } else {
    $_POST['admin'] = 0;
  }
  if (isset($_POST['enabled'])) {
    $_POST['enabled'] = 1;
  } else {
    $_POST['enabled'] = 0;
  }
  if ($postmasteruidgid == "yes"){
	  if(!isset($_POST['uid'])) {
		$_POST['uid'] = $row['uid'];
	  }
	  if(!isset($_POST['gid'])) {
		$_POST['gid'] = $row['gid'];
	  }
  }else{
	# customisation of the uid and gid is not permitted for postmasters, use the domain defaults
	$_POST['uid'] = $row['uid'];
	$_POST['gid'] = $row['gid'];  
  }
  if(!isset($_POST['quota'])) {
    $_POST['quota'] = $row['quotas'];
  }
  if($row['quotas'] != "0") {
    if (($_POST['quota'] > $row['quotas']) || ($_POST['quota'] === "0")) { 
      header ("Location: adminuser.php?quotahigh={$row['quotas']}");
      die;
    }
  }
  if($row['maxmsgsize'] !== "0") {
    if (($_POST['maxmsgsize'] > $row['maxmsgsize']) || ($_POST['maxmsgsize'] === "0")) {
      $_POST['maxmsgsize']=$row['maxmsgsize'];
    }
  }

  # Do some checking, to make sure the user is ALLOWED to make these changes
  if ((isset($_POST['on_piped'])) && ($row['pipe'] == 1)) {
    $_POST['on_piped'] = 1;
  } else {
    $_POST['on_piped'] = 0;
  }

   if ((isset($_POST['on_spambox'])) && (isset($_POST['on_spamassassin']))) {
    $_POST['on_spambox'] = 1;
  } else {
    $_POST['on_spambox'] = 0;
  }

  if (isset($_POST['on_spamboxreport'])) {
    $_POST['on_spamboxreport'] = 1;
  } else {
    $_POST['on_spamboxreport'] = 0;
  }
  if ((isset($_POST['on_spamassassin'])) && ($row['spamassassin'] == 1)) {
    $_POST['on_spamassassin'] = 1;
  } else {
    $_POST['on_spamassassin'] = 0;
  }

  check_user_exists(
    $dbh,$_POST['localpart'],$_SESSION['domain_id'],'adminuser.php'
  );

  if (preg_match("/^\s*$/",$_POST['realname'])) {
    header('Location: adminuser.php?blankname=yes');
    die;
  }

  if (preg_match("/['@%!\/\| ']/",$_POST['localpart'])
    || preg_match("/^\s*$/",$_POST['localpart'])) {
    header("Location: adminuser.php?badname={$_POST['localpart']}");
    die;
  }

  $query = "SELECT maildir FROM domains WHERE domain_id=:domain_id";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
  if ($sth->rowCount()) { $row = $sth->fetch(); }
  if (($_POST['on_piped'] == 1) && ($_POST['smtp'] != '')) {
    $smtphomepath = $_POST['smtp'];
    $pophomepath = "{$row['maildir']}/{$_POST['localpart']}";
    $_POST['type'] = 'piped';
  } else {
    $smtphomepath = "{$row['maildir']}/{$_POST['localpart']}/Maildir";
    $pophomepath = "{$row['maildir']}/{$_POST['localpart']}";
    $_POST['type'] = 'local';
  }

  if (validate_password($_POST['clear'], $_POST['vclear'])) {
    $query = "INSERT INTO users (localpart, username, domain_id, crypt,
      smtp, pop, uid, gid, realname, type, admin, on_piped,
      on_spamassassin, on_spambox, on_spamboxreport, sa_tag, sa_refuse, maxmsgsize, enabled, quota)
      VALUES (:localpart, :username, :domain_id, :crypt, :smtp, :pop,
      :uid, :gid, :realname, :type, :admin, :on_piped, :on_spamassassin, :on_spambox, :on_spamboxreport,
      :sa_tag, :sa_refuse, :maxmsgsize, :enabled, :quota)";
    $sth = $dbh->prepare($query);
    $success = $sth->execute(array(':localpart'=>$_POST['localpart'],
        ':localpart'=>$_POST['localpart'],
        ':username'=>$_POST['localpart'].'@'.$_SESSION['domain'],
        ':domain_id'=>$_SESSION['domain_id'],
        ':crypt'=>crypt_password($_POST['clear'],$salt),
        ':smtp'=>$smtphomepath,
        ':pop'=>$pophomepath,
        ':uid'=>$_POST['uid'],
        ':gid'=>$_POST['gid'],
        ':realname'=>$_POST['realname'],
        ':type'=>$_POST['type'],
        ':admin'=>$_POST['admin'],
        ':on_piped'=>$_POST['on_piped'],
        ':on_spamassassin'=>$_POST['on_spamassassin'],
        ':on_spambox'=>$_POST['on_spambox'],
        ':on_spamboxreport'=>$_POST['on_spamboxreport'],
        ':sa_tag'=>(isset($_POST['sa_tag']) ? $_POST['sa_tag'] : 0),
        ':sa_refuse'=>(isset($_POST['sa_refuse']) ? $_POST['sa_refuse'] : 0),
        ':maxmsgsize'=>$_POST['maxmsgsize'],
        ':enabled'=>$_POST['enabled'],
        ':quota'=>$_POST['quota'],
        ));
    if ($success) {
      header ("Location: adminuser.php?added={$_POST['localpart']}");
      mail("{$_POST['localpart']}@{$_SESSION['domain']}",
        vexim_encode_header(sprintf(_("Welcome %s!"), $_POST['realname'])),
        "$welcome_message",
        "From: {$_SESSION['localpart']}@{$_SESSION['domain']}\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: 8bit\r\n");
      die;
    } else {
      header ("Location: adminuser.php?failadded={$_POST['localpart']}");
      die;
    }
  } else {
    header ("Location: adminuser.php?badpass={$_POST['localpart']}");
    die;
  }
?>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
