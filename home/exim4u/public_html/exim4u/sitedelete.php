<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authsite.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";

  if(!isset($_POST['confirm'])) {
  $_POST['confirm'] = null;
  }
  if(!isset($_POST['type'])) {
  $_POST['type'] = null;
  }
  if(!isset($_GET['type'])) {
  $_GET['type'] = null;
  }
  
  // Delete the domain's users
  if (($_POST['confirm'] == "1") && ($_POST['type'] != "alias")) {
     $usrdelquery = "DELETE FROM users WHERE domain_id=:domain_id";
     $usrdelsth = $dbh->prepare($usrdelquery);
     $usrdelsuccess = $usrdelsth->execute(array(':domain_id'=>$_POST['domain_id']));
    // if we were successful, delete the domain's blocklists
    if ($usrdelsuccess) {
    $usrdelquery = "DELETE FROM blocklists WHERE domain_id=:domain_id";
    $usrdelsth = $dbh->prepare($usrdelquery);
    $usrdelsuccess = $usrdelsth->execute(array(':domain_id'=>$_POST['domain_id']));
      // if we were successful, delete the domain itself
       if ($usrdelsuccess) {
         $domdelquery = "DELETE FROM domains WHERE domain_id=:domain_id";
         $domdelsth = $dbh->prepare($domdelquery);
         $domdelsuccess = $domdelsth->execute(array(':domain_id'=>$_POST['domain_id']));
         // If everything went well, redirect to a success page.
            if ($domdelsuccess) {
            header ("Location: site.php?deleted={$_POST['domain']}");
            die;
      	  }
      }
    } else {
      header ("Location: site.php?faildeleted={$_POST['domain']}");
      die;
    }
  } else if (($_POST['confirm'] == "1") && ($_POST['type'] == "alias")) {
      $aliasdeletequery = "DELETE FROM domainalias WHERE alias=:domain";
      $sth = $dbh->prepare($aliasdeletequery);
      $success = $sth->execute(array(':domain'=>$_POST['domain']));
      if ($success) {
        header ("Location: site.php?deleted={$_POST['domain']}");
        die;
    } else {
      header ("Location: site.php?faildeleted={$_POST['domain']}");
      die;
    }
  } else if ($_POST['confirm'] == "cancel") {
    header ("Location: site.php?canceldelete={$_POST['domain']}");
    die;
  }

  if ($_GET['type'] != "alias") {
    $query = "SELECT COUNT(*) AS count, domain, domains.type FROM users,domains
            WHERE (domains.domain_id=:domain_id
            AND users.domain_id=domains.domain_id)
            GROUP BY domain,domains.type";
     $sth = $dbh->prepare($query);
     $sth->execute(array(':domain_id'=>$_GET['domain_id']));
     $row = $sth->fetch();
  }
?>
<html>
  <head>
    <title><?php echo _("Exim4U") . ": " .  _("Confirm Delete"); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
    <body>
    <?php include dirname(__FILE__) . "/config/header.php"; ?>
    <div id='menu'>
      <a href='site.php'><?php echo _("Manage Domains"); ?></a><br>
      <a href='sitepassword.php'><?php echo _("Site Password"); ?></a><br>
      <br><a href='logout.php'><?php echo _("Logout"); ?></a><br>
    </div>
    <div id='Content'>
      <form name='domaindelete' method='post' action='sitedelete.php'>
      <table align="center">
        <tr><td colspan='2'><?php printf (_("Please confirm deleting domain %s."), $_GET['domain']); ?>:</td></tr>
        <?php if (($_GET['type'] != "relay") && ($_GET['type'] != "alias")) {
            print   "<tr><td colspan='2'>";
        printf (ngettext("There is currently <b>%1\$d</b> account in domain %2\$s", "There are currently <b>%1\$d</b> accounts in domain %2\$s", $row['count']), $row['count'], $_GET['domain']);
        print   "</td></tr>";
           }
        ?>
        <tr><td><input name='confirm' type='radio' value='cancel' checked><b> <?php printf (_("Do Not Delete %s"), $_GET['domain']); ?></b></td></tr>
        <tr><td><input name='confirm' type='radio' value='1'><b> <?php printf (_("Delete %s"), $_GET['domain']); ?></b></td></tr>
        <tr><td><input name='domain_id' type='hidden' value='<?php print $_GET['domain_id']; ?>'>
                <input name='domain' type='hidden' value='<?php print $_GET['domain']; ?>'>
                <input name='type' type='hidden' value='<?php print $_GET['type']; ?>'>
              <input name='submit' type='submit' value='<?php echo _("Continue"); ?>'></td></tr>
      </table>
      </form>
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
