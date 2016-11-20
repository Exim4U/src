<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authuser.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";

   $domquery = "SELECT spamassassin,maxmsgsize FROM domains WHERE domain_id=:domain_id";
   $domsth = $dbh->prepare($domquery);
   $success = $domsth->execute(array(':domain_id'=>$_SESSION['domain_id']));
   if ($success) { $domrow = $domsth->fetch(); }
   $query = "SELECT * FROM users WHERE user_id=:user_id";
   $sth = $dbh->prepare($query);
   $success = $sth->execute(array(':user_id'=>$_SESSION['user_id']));
   if ($success) { $row = $sth->fetch(); }
   $blockquery = "SELECT block_id,blockhdr,blockval FROM blocklists,users
            WHERE blocklists.user_id=:user_id
            AND users.user_id=blocklists.user_id";
   $blocksth = $dbh->prepare($blockquery);
   $blocksuccess = $blocksth->execute(array(':user_id'=>$_SESSION['user_id']));
?>
<html>
  <head>
    <title><?php echo _("Exim4U") . ": " . _("Manage Users"); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
    <script type='text/javascript'>
      function fwac() {
      document.getElementById('forward').disabled = !document.getElementById('on_forward').checked;
      }
    </script>
  </head>
  <body onLoad="document.forms[0].elements[0].focus(); fwac()">
    <?php include dirname(__FILE__) . "/config/header.php"; ?>
    <div id="Menu">
      <a href="logout.php"><?php echo _("Logout"); ?></a><br>
    </div>
    <div id="forms">
      <form name="userchange" method="post" action="userchangesubmit.php">
       <table align="center">
        <tr><td><?php echo _("Email Address"); ?>:</td><td><?php print $row['localpart']."@".$_SESSION['domain']; ?></td>
        <tr><td><?php echo _("Password"); ?>:</td><td><input name="clear" type="password" class="textfield"></td></tr>
        <tr><td><?php echo _("Verify Password"); ?>:</td><td><input name="vclear" type="password" class="textfield"></td></tr>
           <tr><td colspan="3"><b><?php echo _("Note:"); ?></b> <?php echo _("Attempting to set blank passwords does not work!"); ?><td></tr>
        <tr><td></td><td class="button"><input name="submit" type="submit" value="<?php echo _("Submit Password"); ?>"></td></tr>
        </table>
      </form>
      <form name="userchange" method="post" action="userchangesubmit.php">
       <table align="center">
        <tr><td colspan="2"><?php
          if ($row['quota'] != "0") {
            printf (_("Your mailbox quota limit is %s MB"), $row['quota']);
          } else {
            print _("Your mailbox quota limit is: Unlimited");
          }
          printf(_("<br />Your maximum message size limit is %s KB (0=unlimited)"),$domrow['maxmsgsize']);?></td></tr>
<tr><td><?php echo _("Name"); ?>:</td><td><input name="realname" type="text" value="<?php print $row['realname']; ?>" class="textfield"></td></tr>

<?php 
      if ($domrow['spamassassin'] == "1") {
      print "<tr><td>" . _("Spamassassin") . ":</td><td><input name=\"on_spamassassin\" type=\"checkbox\"";
      if ($row['on_spamassassin'] == "1") { 
        print " checked "; 
      }
      print "></td></tr>\n";
      print "<tr><td>" . _("Enable Spam Box") . ":</td><td><input name=\"on_spambox\" type=\"checkbox\"";
        if ($row['on_spambox'] == "1") {
          print " checked ";
        }
      print "></td></tr>\n";
      print "<tr><td>" . _("Enable Spam Box Report") . ":</td><td><input name=\"on_spamboxreport\" type=\"checkbox\"";
        if ($row['on_spamboxreport'] == "1") {
          print " checked ";
      }
      print "></td></tr>\n";
      print "<tr><td>" . _("SpamAssassin Tag Score") . ":</td>";
      print "<td><input type=\"text\" size=\"5\" name=\"sa_tag\" value=\"{$row['sa_tag']}\" class=\"textfield\"></td></tr>\n";
      print "<tr><td>" . _("SpamAssassin Discard Score") . ":</td>";
      print "<td><input type=\"text\" size=\"5\" name=\"sa_refuse\" value=\"{$row['sa_refuse']}\" class=\"textfield\"></td></tr>\n";
      }
      print "<tr><td>" . _("Maximum Message Size") . ":</td>";
      print "<td><input type=\"text\" size=\"5\" name=\"maxmsgsize\" value=\"{$row['maxmsgsize']}\" class=\"textfield\"> " . _("KB") . "</td></tr>\n";
      print "<tr><td>" . _("Vacation Enabled") . ":</td><td><input name=\"on_vacation\" type=\"checkbox\"";
      if ($row['on_vacation'] == "1") { print " checked "; } 
      print "></td></tr>\n";
      print "<tr><td>" . _("Vacation Message") . ":</td>";
      print "<td><textarea name=\"vacation\" cols=\"40\" rows=\"5\" class=\"textfield\">".(function_exists('imap_qprint') ? imap_qprint($row['vacation']) : $row['vacation'])."</textarea>";
      print "<tr><td>" . _("Forwarding Enabled") . ":</td>";
      ?>
      <td><input name="on_forward" type="checkbox" id="on_forward"
      <?php if($row['on_forward'] == "1") { print " checked "; } ?> onchange="fwac()" onclick="fwac()">
      </td>
      <?php 
      print "</tr>\n";
      print "<tr><td>" . _("Forward Mail To") . ":</td>";
      ?>
      <td><input type="text" name="forward" id="forward" value="<?php print $row['forward']; ?>" class="textfield"><br>
      <?php echo _("Enter full e-mail addresses, use commas to separate them."); ?>
      </td><tr>
      <?php
      print "<tr><td>" . _("Store Forwarded Mail Locally") . ":</td><td><input name=\"unseen\" type=\"checkbox\"";
      if ($row['unseen'] == "1") { print " checked "; } print "></td></tr>\n";
    ?>

    <tr><td></td><td class="button"><input name="submit" type="submit" value="<?php echo _("Submit Profile"); ?>"></td></tr>
  </table>
  </form>
  <form name="blocklist" method="post" action="userblocksubmit.php">
    <table align="center">
      <tr><td><?php echo _("Add A New Header Blocking Filter"); ?>:</td></tr>
     <tr><td><?php echo _("Header"); ?>:</td>
      <td><select name="blockhdr" class="textfield">
        <option value="From"><?php echo _("From"); ?>:</option>
        <option value="To"><?php echo _("To"); ?>:</option>
        <option value="Subject"><?php echo _("Subject"); ?>:</option>
          <option value="X-Mailer"><?php echo _("X-Mailer"); ?>:</option>
        </select></td>
        <td><input name="blockval" type="text" size="25" class="textfield">
        <input name="color" type="hidden" value="black"></td></tr>
      <tr><td></td><td class="button"><input name="submit" type="submit" value="Submit"></td></tr>
    </table>
    </form>
    <table align="center">
      <tr><td><?php echo _("Blocked"); ?></td><td><?php echo _("Headers Listed Below"); ?></td><td><?php echo _("(mail will be deleted):"); ?></td></tr>
      <?php if ($blocksuccess) {
       while ($blockrow = $blocksth->fetch()) {
        print "<tr><td><a href=\"userblocksubmit.php?action=delete&block_id={$blockrow['block_id']}\"><img style=\"border:0;width:10px;height:16px\" title=\"Delete\" src=\"images/trashcan.gif\" alt=\"trashcan\"></a></td>";
        print "<td>{$blockrow['blockhdr']}</td><td>{$blockrow['blockval']}</td></tr>\n";
      }
      }
      ?>
      </table>
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
