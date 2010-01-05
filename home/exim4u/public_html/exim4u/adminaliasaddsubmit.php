<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

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
  # Do some checking, to make sure the user is ALLOWED to make these changes
  $query = "SELECT spamassassin from domains
    WHERE domain_id = {$_SESSION['domain_id']}";
  $result = $db->query($query);
  $row = $result->fetchRow();
  if ((isset($_POST['on_spamassassin'])) && ($row['spamassassin'] == 1)) {
    $_POST['on_spamassassin'] = 0;
  } else {
    $_POST['on_spamassassin'] = 0;
  }
  // Don't accept blank passwords
  if (($_POST['clear'] == "") && ($_POST['vclear'] == "")) {
    $junk = md5(time());
    $_POST['clear'] = $junk;
    $_POST['vclear'] = $junk;
  }

  check_user_exists(
    $db,$_POST['localpart'],$_SESSION['domain_id'],'adminalias.php'
  );

  if ((preg_match("/['@%!\/\|\" ']/",$_POST['localpart']))
    || preg_match("/^\s*$/",$_POST['realname'])) {
    header("Location: adminalias.php?badname={$_POST['localpart']}");
    die;
  }

  $aliasto = preg_replace("/[', ']+/", ",", $_POST['smtp']);
  if (alias_validate_password($_POST['clear'], $_POST['vclear'])) {
    $query = "INSERT INTO users
      (localpart, username, domain_id, crypt, clear, smtp, pop, uid,
      gid, realname, type, admin, on_spamassassin, enabled)
      SELECT '{$_POST['localpart']}',
      '{$_POST['localpart']}@{$_SESSION['domain']}',
      '{$_SESSION['domain_id']}',
      '" . crypt_password($_POST['clear'],$salt) . "',
      '{$_POST['clear']}',
      '{$aliasto}',
      '{$aliasto}',
      uid,
      gid,
      '{$_POST['realname']}',
      'alias',
      {$_POST['admin']},
      {$_POST['on_spamassassin']},
      {$_POST['enabled']} from domains
      WHERE domains.domain_id={$_SESSION['domain_id']}";
    $result = $db->query($query);
    if (!DB::isError($result)) {
      header ("Location: adminalias.php?added={$_POST['localpart']}");
    } else {
      header ("Location: adminalias.php?failadded={$_POST['localpart']}");
    }
  } else {
    header ("Location: adminalias.php?badaliaspass={$_POST['localpart']}");
  } 
?>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
