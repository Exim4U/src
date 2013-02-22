<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authsite.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";

  if (isset($_POST['spamassassin'])) {$_POST['spamassassin'] = 1;} else {$_POST['spamassassin'] = 0;}

  $query = "UPDATE domains SET relay_address='{$_POST['relaydest']}',
            sa_tag='" . ((isset($_POST['sa_tag'])) ? $_POST['sa_tag'] : 0) . "',
            sa_refuse='" .((isset($_POST['sa_refuse'])) ? $_POST['sa_refuse'] : 0) . "',
            spamassassin='{$_POST['spamassassin']}' WHERE domain_id='{$_POST['domain_id']}'";
  $result = $db->query($query);
  if (!DB::isError($result)) {
    header ("Location: site.php?updated={$_POST['domain']}");
    die; 
  } else {
    header ("Location: site.php?failupdated={$_POST['domain']}");
    die;
  }
  

# Just-in-case catchall
header ("Location: site.php?failupdated={$_POST['domain']}");
?>