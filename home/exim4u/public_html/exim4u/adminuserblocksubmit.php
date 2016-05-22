<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authuser.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

 # Deletions
  $action = (isset($_GET['action']) ? $_GET['action'] : null);
  if ($action == 'delete') {
    $query = "DELETE FROM blocklists WHERE block_id=:block_id
      AND domain_id=:domain_id AND user_id=:user_id";
    $sth = $dbh->prepare($query);
    $success = $sth->execute(array(':block_id'=>$_GET['block_id'],
      ':domain_id'=>$_SESSION['domain_id'], ':user_id'=>$_GET['user_id']));

    if ($success) {
      header ("Location: adminuserchange.php?user_id={$_GET['user_id']}&localpart={$_GET['localpart']}&updated={$_GET['localpart']}");
      die;
    } else {
      header ("Location: adminuserchange.php?user_id={$_GET['user_id']}&localpart={$_GET['localpart']}&failed={$_GET['localpart']}");
      die;
    }
  }

# Finally 'the rest' which is handled by the profile form
  if (preg_match("/^\s*$/",$_POST['blockval'])) {
    header ("Location: adminuserchange.php?user_id={$_POST['user_id']}&localpart={$_POST['localpart']}&failupdated");
    die;
  }
  $query = "INSERT INTO blocklists
    (domain_id, user_id, blockhdr, blockval, color)
    VALUES (:domain_id, :user_id, :blockhdr, :blockval, :color)";
  $sth = $dbh->prepare($query);
  $success = $sth->execute(array(':domain_id'=>$_SESSION['domain_id'],
      ':user_id'=>$_POST['user_id'],
      ':blockhdr'=>$_POST['blockhdr'],
      ':blockval'=>$_POST['blockval'],
      ':color'=>$_POST['color']));
  if ($success) {
    header ("Location: adminuserchange.php?user_id={$_POST['user_id']}&localpart={$_POST['localpart']}&updated={$_POST['localpart']}");
  } else {
    header ("Location: adminuserchange.php?user_id={$_POST['user_id']}&localpart={$_POST['localpart']}&failed={$_POST['localpart']}");
  }
?>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
