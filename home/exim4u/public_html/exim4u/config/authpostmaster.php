<?php
  include_once dirname(__FILE__) . "/variables.php";
  include_once dirname(__FILE__) . "/httpheaders.php";

 // Test to confirm that the session variables have been set.
  if ((isset($_SESSION['localpart'])) &&  (isset($_SESSION['domain_id'])) && (isset($_SESSION['crypt'])) )
       {
       // Match the crypted password to the database entry
       // and confirm the user is an admin
       $query = "SELECT crypt FROM users WHERE localpart='".$_SESSION['localpart']."' and domain_id='".$_SESSION['domain_id']."' AND admin='1';";
       $results = $db->query($query);
       $row = $results->fetchRow();

       // If the database password doesn't match the cookie
       // password, reject the user to the login screen
       // print_r($_SESSION);
       if ($row['crypt'] != $_SESSION['crypt']) { header ("Location: index.php?login=failed"); exit(); };
       }
  else
       { header ("Location: index.php?login=failed"); exit(); }
?>
