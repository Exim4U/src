<?php
  include_once dirname(__FILE__) . "/httpheaders.php";
  include_once dirname(__FILE__) . "/variables.php";

  // Test to confirm that the session variables have been set.
  if ((isset($_SESSION['localpart'])) &&  (isset($_SESSION['crypt'])) &&  (isset($_SESSION['user_id'])) &&  (isset($_SESSION['domain_id'])) )
       {
       $query = "SELECT user_id,localpart,crypt,domain_id FROM users WHERE localpart='".$_SESSION['localpart']."'
                   AND domain_id='".$_SESSION['domain_id']."';";
       $result = $db->query($query);
       $row = $result->fetchRow();

       // If the localpart isn't in the cookie, or the database
       // password doesn't match the cookie password, reject the
       // user to the login screen
       if ($row['localpart'] != $_SESSION['localpart']) { header ("Location: index.php?login=failed");  exit(); };
       if ($row['crypt'] != $_SESSION['crypt']) { header ("Location: index.php?login=failed");  exit(); };
       if ($row['user_id'] != $_SESSION['user_id']) {header ("Location: index.php?login=failed");  exit(); };
       }
  else
       { header ("Location: index.php?login=failed"); exit(); }
?>
