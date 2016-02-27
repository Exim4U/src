<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authsite.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";

  if (isset($_POST['spamassassin'])) {$_POST['spamassassin'] = 1;} else {$_POST['spamassassin'] = 0;}
  if (isset($_POST['enabled'])) {$_POST['enabled'] = 1;} else {$_POST['enabled'] = 0;}

  if (preg_match("/^\s*$/",$_POST['relay_address'])) {
    header("Location: sitechangerelay.php?domain_id={$_POST['domain_id']}&domain={$_POST['domain']}&blankrelayadd=yes");
    die;
  }

$query = "UPDATE domains SET relay_address=:relay_address,
  	sa_tag=:sa_tag, sa_refuse=:sa_refuse,
  	spamassassin=:spamassassin, enabled=:enabled
        WHERE domain_id=:domain_id";
$sth = $dbh->prepare($query);
$success = $sth->execute(array(':relay_address'=>$_POST['relay_address'],
        ':sa_tag'=>((isset($_POST['sa_tag'])) ? $_POST['sa_tag'] : 0),
        ':sa_refuse'=>((isset($_POST['sa_refuse'])) ? $_POST['sa_refuse'] : 0),
        ':spamassassin'=>$_POST['spamassassin'],
        ':enabled'=>$_POST['enabled'],
        ':domain_id'=>$_POST['domain_id']
        ));
if ($success) {
    header ("Location: site.php?updated={$_POST['domain']}");
    die; 
  } else {
    header ("Location: sitechangerelay.php?domain_id={$_POST['domain_id']}&domain={$_POST['domain']}&failupdated={$_POST['domain']}");
    die;
  }
  

# Just-in-case catchall
header ("Location: site.php?failupdated={$_POST['domain']}");
?>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
<!-- This module was added by GLD -->
