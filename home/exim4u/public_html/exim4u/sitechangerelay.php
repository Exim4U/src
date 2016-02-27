<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authsite.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";
?>
<html>
  <head>
    <title><?php echo _("Exim4U") . ": " . _("Manage Domains"); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body onLoad="document.passwordchange.localpart.focus()">
    <?php include dirname(__FILE__) . "/config/header.php"; ?>
    <div id="menu">
      <a href="site.php"><?php echo _("Manage Domains"); ?></a><br>
      <a href="sitepassword.php"><?php echo _("Site Password"); ?></a><br>
      <br><a href="logout.php"><?php echo _("Logout"); ?></a><br>
    </div>
    <div id="Forms">
      <table align="center">

          <tr><td colspan="2"><h4><?php echo _("Modify Relay Domain Properties"); ?>:</h4>

          <?php 
          $query = "SELECT * FROM domains WHERE domain_id=:domain_id";
          $sth = $dbh->prepare($query);
          $sth->execute(array(':domain_id'=>$_GET['domain_id']));
          if ($sth->rowCount()) { $row = $sth->fetch(); }
                echo _("Relay Domain: "); echo $row['domain'];      
          ?>
          </td></tr>
      
        <td><input name="domain_id" type="hidden" value="<?php print $_GET['domain_id']; ?>">
            <input name="domain" type="hidden" value="<?php print $_GET['domain']; ?>"></td></tr>
      
      <form name="domainchange" method="post" action="sitechangerelaysubmit.php">

      <tr>
            <td><?php echo _('Relay Server Address'); ?>:</td>
            <td>
            <input type="text" size="30" name="relay_address" value="<?php print $row['relay_address']; ?>" class="textfield">
            </td>
          </tr>
            
        <tr><td><?php echo _("Spamassassin Tag Score"); ?>:</td>
            <td><input type="text" size="5" name="sa_tag" value="<?php print $row['sa_tag']; ?>" class="textfield"></td></tr>
        <tr><td><?php echo _("Spamassassin Discard Score"); ?>:</td>
            <td><input type="text" size="5" name="sa_refuse" value="<?php print $row['sa_refuse']; ?>" class="textfield"></td></tr>
        <tr><td><?php echo _("Spamassassin"); ?>:</td>
            <td><input type="checkbox" name="spamassassin" <?php if ($row['spamassassin'] == 1) {print "checked";} ?>></td></tr>
        <tr><td><?php echo _("Enabled"); ?>:</td>
            <td><input type="checkbox" name="enabled" <?php if ($row['enabled'] == 1) {print "checked";} ?>></td></tr>
        <tr>
        <td><input name="domain_id" type="hidden" value="<?php print $_GET['domain_id']; ?>">
            <input name="domain" type="hidden" value="<?php print $_GET['domain']; ?>"></td></tr>
        <tr><td></td><td><input name="submit" size="25" type="submit" value="<?php echo _("Submit Changes"); ?>"></td></tr>
      </table>
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
<!-- This module was added by GLD -->
