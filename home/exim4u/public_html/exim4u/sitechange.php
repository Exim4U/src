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
      <tr><td colspan="2"><h4><?php echo _("Modify Domain Admin"); ?>:</h4></td></tr>
      <form name="passwordchange" method="post" action="sitechangesubmit.php">
        <tr><td><?php echo _("Admin"); ?>:</td><td><select name="localpart" class="textfield">
          <?php
            $query = "SELECT localpart,domain FROM users,domains
              WHERE domains.domain_id=:domain_id
              AND admin=1 AND users.domain_id=domains.domain_id";
         $sth = $dbh->prepare($query);
         $sth->execute(array(':domain_id'=>$_GET['domain_id']));
         while ($row = $sth->fetch()) {
                 print '<option value="' . $row['localpart'] . '">' . $row['localpart'] . '</option>' . "\n\t";
         }
          ?>
          </select>@<?php 
           $query = "SELECT * FROM domains WHERE domain_id=:domain_id";
         $sth = $dbh->prepare($query);
         $sth->execute(array(':domain_id'=>$_GET['domain_id']));
         if ($sth->rowCount()) { $row = $sth->fetch(); }
          print $row['domain']; ?></td>
        <td><input name="domain_id" type="hidden" value="<?php print $_GET['domain_id']; ?>">
            <input name="domain" type="hidden" value="<?php print $_GET['domain']; ?>"></td></tr>
        <tr><td><?php echo _("Password"); ?>:</td><td><input name="clear" size="25" type="password" class="textfield"></td></tr>
        <tr><td><?php echo _("Verify Password"); ?>:</td><td><input name="vclear" size="25" type="password" class="textfield"></td></tr>
        <tr><td></td><td><input name="submit" size="25" type="submit" value="<?php echo _("Submit Password"); ?>"></td></tr>
      </form>
      <tr></tr><tr></tr>
      <tr><td colspan="2"><h4><?php echo _("Modify Domain Properties"); ?>:</h4></td></tr>
      <form name="domainchange" method="post" action="sitechangesubmit.php">
       <?php if ($multi_ip == "yes") { ?>
               <tr><td><?php echo _("Outgoing IP"); ?>:</td><td><input type="text" size="12" name="outgoing_ip" value="<?php print $row['outgoing_ip']; ?>" class="textfield"></td></tr>
       <?php } ?>
        <tr><td><?php echo _("System UID"); ?>:</td><td><input type="text" size="5" name="uid" value="<?php print $row['uid']; ?>" class="textfield"></td></tr>
        <tr><td><?php echo _("System GID"); ?>:</td><td><input type="text" size="5" name="gid" value="<?php print $row['gid']; ?>" class="textfield"></td></tr>
        <tr><td><?php echo _("Maximum Accounts") . "<br />(" . _("0 for unlimited") . ")"; ?>:</td><td><input type="text" size="5" name="max_accounts" value="<?php print $row['max_accounts']; ?>" class="textfield"></td></tr>
        <tr><td><?php echo _("Max Mailbox Quota In Mb") . "<br />(" . _("0 for disabled") . ")"; ?>:</td>
            <td><input type="text" size="5" name="quotas" value="<?php print $row['quotas']; ?>" class="textfield"></td></tr>
        <tr><td><?php echo _("Maximum Message Size"); ?>:</td>
            <td><input name="maxmsgsize" size="5" type="text" class="textfield" value="<?php print $row['maxmsgsize']; ?>"> Kb</td></tr>
        <tr><td><?php echo _("Spamassassin Tag Score"); ?>:</td>
            <td><input type="text" size="5" name="sa_tag" value="<?php print $row['sa_tag']; ?>" class="textfield"></td></tr>
        <tr><td><?php echo _("Spamassassin Discard Score"); ?>:</td>
            <td><input type="text" size="5" name="sa_refuse" value="<?php print $row['sa_refuse']; ?>" class="textfield"></td></tr>
        <tr><td><?php echo _("Spamassassin"); ?>:</td>
            <td><input type="checkbox" name="spamassassin" <?php if ($row['spamassassin'] == 1) {print "checked";} ?>></td></tr>
      <tr><td><?php echo _("Piping To Command"); ?>:</td>
            <td><input type="checkbox" name="pipe" <?php if ($row['pipe'] == 1) {print "checked";} ?>></td></tr>
        <tr><td><?php echo _("Enabled"); ?>:</td><td><input type="checkbox" name="enabled" <?php if ($row['enabled'] == 1) {print "checked";} ?>></td>
        <td><input name="domain_id" type="hidden" value="<?php print $_GET['domain_id']; ?>">
            <input name="domain" type="hidden" value="<?php print $_GET['domain']; ?>"></td></tr>
        <tr><td></td><td><input name="submit" size="25" type="submit" value="<?php echo _("Submit Changes"); ?>"></td></tr>
      </table>
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
