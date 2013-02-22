<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authuser.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";
  if (isset($_POST['on_vacation'])) {$_POST['on_vacation'] = 1;} else {$_POST['on_vacation'] = 0;}
  if (isset($_POST['on_forward'])) {$_POST['on_forward'] = 1;} else {$_POST['on_forward'] = 0;}
  if (isset($_POST['unseen'])) {$_POST['unseen'] = 1;} else {$_POST['unseen'] = 0;}
  # Do some checking, to make sure the user is ALLOWED to make these changes
  $query = "SELECT spamassassin,maxmsgsize from domains WHERE domain_id = '{$_SESSION['domain_id']}'";
  $result = $db->query($query);
  $row = $result->fetchRow();
  if ((isset($_POST['on_spambox'])) && (isset($_POST['on_spamassassin']))) {$_POST['on_spambox'] = 1;} else {$_POST['on_spambox'] = 0;}
  if ((isset($_POST['on_spamassassin'])) && ($row['spamassassin'] = 1)) {$_POST['on_spamassassin'] = 1;} else {$_POST['on_spamassassin'] = 0;}
  if ((isset($_POST['maxmsgsize'])) && ($_POST['maxmsgsize'] > $row['maxmsgsize'])) {$_POST['maxmsgsize'] = $row['maxmsgsize'];}

  if ($_POST['realname'] != "") {
    $query = "UPDATE users SET realname='{$_POST['realname']}'
            WHERE user_id='{$_SESSION['user_id']}'";
    $result = $db->query($query);
  }
  if (isset($_POST['on_spamboxreport'])) {
    $_POST['on_spamboxreport'] = 1;
  } else {
    $_POST['on_spamboxreport'] = 0;
  }

# Update the password, if the password was given
  if (validate_password($_POST['clear'], $_POST['vclear'])) {
    $cryptedpassword = crypt_password($_POST['clear']);
    $query = "UPDATE users SET crypt='$cryptedpassword',
            clear='{$_POST['clear']}'
            WHERE user_id='{$_SESSION['user_id']}'";
    $result = $db->query($query);
    if (!DB::isError($result)) {
      $_SESSION['crypt'] = $cryptedpassword;
      header ("Location: userchange.php?userupdated");
      die;
    } else {
      header ("Location: userchange.php?badpass");
      die;
    }
    header ("Location: userchange.php?badpass");
    die;
  }


    # Finally 'the rest' which is handled by the profile form
    $query = "UPDATE users SET on_spamassassin='{$_POST['on_spamassassin']}',
            on_spambox='{$_POST['on_spambox']}',
            on_spamboxreport='{$_POST['on_spamboxreport']}',
            sa_tag='{$_POST['sa_tag']}',
            sa_refuse='{$_POST['sa_refuse']}',
            on_vacation='{$_POST['on_vacation']}',
            vacation='{$_POST['vacation']}',
            on_forward='{$_POST['on_forward']}',
            forward='{$_POST['forward']}',
            maxmsgsize='{$_POST['maxmsgsize']}',
            unseen='{$_POST['unseen']}'
            WHERE user_id='{$_SESSION['user_id']}'";
    $result = $db->query($query);
    if (!DB::isError($result)) {
      if (strlen($_POST['vacation']) > $max_vacation_length)
      {
        header ("Location: userchange.php?uservacationtolong=" . strlen($_POST['vacation']));
      }
      else
      {
        header ("Location: userchange.php?userupdated");
      }
      die;
    } else {
      header ("Location: userchange.php?userfailed");
      die;
    }

  header ("Location: userchange.php?userupdated");
?>