<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authuser.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

  $query = "SELECT localpart,realname,smtp,enabled FROM users 
       WHERE user_id=:user_id AND domain_id=:domain_id AND type='alias'";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':user_id'=>$_POST['user_id'], ':domain_id'=>$_SESSION['domain_id']));
  
  # Fix the boolean values
  if (isset($_POST['enabled'])) {
    $_POST['enabled'] = 1;
  } else {
    $_POST['enabled'] = 0;
  }

if ($_POST['realname'] != "") {
   $query = "UPDATE users SET realname=:realname
        WHERE user_id=:user_id";
   $sth = $dbh->prepare($query);
   $sth->execute(array(':realname'=>$_POST['realname'], ':user_id'=>$_SESSION['user_id']));
  }

# Update the password, if the password was given
  if (validate_password($_POST['clear'], $_POST['vclear'])) {
    $cryptedpassword = crypt_password($_POST['clear']);
    $query = "UPDATE users SET crypt=:crypt WHERE user_id=:user_id";
    $sth = $dbh->prepare($query);
    $success = $sth->execute(array(':crypt'=>$cryptedpassword, ':user_id'=>$_SESSION['user_id']));
    if ($success) {
      $_SESSION['crypt'] = $cryptedpassword;
      header ("Location: useraliaschange.php?userupdated");
      die;
    } else {
      header ("Location: useraliaschange.php?badpass");
      die;
    }
    header ("Location: useraliaschange.php?badpass");
    die;
  }

  # update the actual alias in the users table
  $forwardto=explode(",",$_POST['target']);
  for($i=0; $i<count($forwardto); $i++){
    $forwardto[$i]=trim($forwardto[$i]);
    if(!filter_var($forwardto[$i], FILTER_VALIDATE_EMAIL)) {
      header ("Location: useraliaschange.php?invalidforward=".htmlentities($forwardto[$i]));
      die;
    }
  }
  $aliasto = implode(",",$forwardto);
   $query = "UPDATE users SET
    smtp=:smtp,
    pop=:pop
    WHERE user_id=:user_id";
    $sth = $dbh->prepare($query);
    $success = $sth->execute(array(
      ':smtp'=>$aliasto,
      ':pop'=>$aliasto,
      ':user_id'=>$_SESSION['user_id'],
      ));
    if ($success) {
    header ("Location: useraliaschange.php?updated={$_POST['localpart']}");
  } else {
    header ("Location: useraliaschange.php?failupdated={$_POST['localpart']}");
  }
?>
