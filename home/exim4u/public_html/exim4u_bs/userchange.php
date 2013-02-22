<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authuser.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";

  $domquery = "SELECT spamassassin FROM domains WHERE domain_id='{$_SESSION['domain_id']}'";
  $domresult = $db->query($domquery);
  if (!DB::isError($domresult)) { $domrow = $domresult->fetchRow(); }
  $query = "SELECT * FROM users WHERE user_id='{$_SESSION['user_id']}'";
  $result = $db->query($query);
  if (!DB::isError($result)) { $row = $result->fetchRow(); }
  $blockquery = "SELECT block_id,blockhdr,blockval FROM blocklists,users
              WHERE blocklists.user_id='{$_SESSION['user_id']}'
            AND users.user_id=blocklists.user_id";
  $blockresult = $db->query($blockquery);
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _("Exim4U") . ": " . _("Manage Users"); ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/scripts.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body onLoad="document.userchange.realname.focus()">
        <div class="container">
            <?php include dirname(__FILE__) . "/config/header.php"; ?>
            
            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="logout.php"><?php echo _("Logout"); ?></a></li>
                    </ul>
                </div>
            </div>

            <form name="userchange" method="post" action="userchangesubmit.php">
                <table align="center">
                    <tr>
                        <td><?php echo _("Name"); ?>:</td><td><input name="realname" type="text" value="<?php print $row['realname']; ?>" /></td>
                    </tr>
                    <tr>
                        <td><?php echo _("Email Address"); ?>:</td><td><?php print $row['localpart']."@".$_SESSION['domain']; ?></td>
                    </tr>
                    <tr>
                        <td><?php echo _("Password"); ?>:</td><td><input name="clear" type="password" /></td>
                    </tr>
                    <tr>
                        <td><?php echo _("Verify Password"); ?>:</td><td><input name="vclear" type="password" /></td>
                    </tr>
                    <tr>
                        <td colspan="2"><b><?php echo _("Note:"); ?></b> <?php echo _("Attempting to set blank passwords does not work!"); ?><td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input class="btn" name="submit" type="submit" value="<?php echo _("Submit Password"); ?>" /></td>
                    </tr>
                </table>

                <table align="center">
                    <tr>
                        <td colspan="2">
                        <?php
                            if ($row['quota'] != "0") {
                                printf (_("Your mailbox quota is currently: %s Mb"), $row['quota']);
                            } 
                            else {
                                print _("Your mailbox quota is currently: Unlimited");
                            }
                        ?>
                        </td>
                    </tr>
                    <?php 
                        if ($domrow['spamassassin'] == "1") {
                        print "<tr><td>" . _("Spamassassin") . ":</td><td><input name=\"on_spamassassin\" type=\"checkbox\"";
                        if ($row['on_spamassassin'] == "1") { 
                        print " checked "; 
                        }
                        print " /></td></tr>\n";
                        print "<tr><td>" . _("Enable Spam Box") . ":</td><td><input name=\"on_spambox\" type=\"checkbox\"";
                        if ($row['on_spambox'] == "1") {
                        print " checked ";
                        }
                        print " /></td></tr>\n";
                        print "<tr><td>" . _("Enable Spam Box Report") . ":</td><td><input name=\"on_spamboxreport\" type=\"checkbox\"";
                        if ($row['on_spamboxreport'] == "1") {
                        print " checked ";
                        }
                        print " /></td></tr>\n";
                        print "<tr><td>" . _("SpamAssassin Tag Score") . ":</td>";
                        print "<td><input type=\"text\" size=\"5\" name=\"sa_tag\" value=\"{$row['sa_tag']}\" /></td></tr>\n";
                        print "<tr><td>" . _("SpamAssassin Discard Score") . ":</td>";
                        print "<td><input type=\"text\" size=\"5\" name=\"sa_refuse\" value=\"{$row['sa_refuse']}\" /></td></tr>\n";
                        }
                        print "<tr><td>" . _("Maximum Message Size") . ":</td>";
                        print "<td><div class=\"input-append\"><input type=\"text\" size=\"5\" name=\"maxmsgsize\" value=\"{$row['maxmsgsize']}\" /><span class=\"add-on\">" . _("Kb") . "</span></div></td></tr>\n";
                        print "<tr><td>" . _("Vacation Enabled") . ":</td><td><input name=\"on_vacation\" type=\"checkbox\"";
                        if ($row['on_vacation'] == "1") { print " checked "; } 
                        print " /></td></tr>\n";
                        print "<tr><td>" . _("Vacation Message") . ":</td>";
                        print "<td><textarea name=\"vacation\" cols=\"40\" rows=\"5\">{$row['vacation']}</textarea>";
                        print "<tr><td>" . _("Forwarding Enabled") . ":</td><td><input name=\"on_forward\" type=\"checkbox\"";
                        if ($row['on_forward'] == "1") { print " checked "; } 
                        print " /></td></tr>\n";
                        print "<tr><td>" . _("Forward Mail To") . ":</td>";
                        print "<td><br><input type=\"text\" name=\"forward\" value=\"{$row['forward']}\" /><br>\n";
                        print _("Must be a full e-mail address") . "!</td></tr>\n";
                        print "<tr><td>" . _("Store Forwarded Mail Locally") . ":</td><td><input name=\"unseen\" type=\"checkbox\"";
                        if ($row['unseen'] == "1") { print " checked "; } print " /></td></tr>\n";
                    ?>
                    <tr>
                        <td></td>
                        <td><input class="btn" name="submit" type="submit" value="<?php echo _("Submit Profile"); ?>" /></td>
                    </tr>
                </table>
            </form>
            
            <form name="blocklist" method="post" action="userblocksubmit.php">
                <table align="center">
                    <tr>
                        <td colspan="3"><?php echo _("Add A New Header Blocking Filter"); ?>:</td>
                    </tr>
                    <tr>
                        <td><?php echo _("Header"); ?>:</td>
                        <td>
                            <select name="blockhdr">
                                <option value="From"><?php echo _("From"); ?>:</option>
                                <option value="To"><?php echo _("To"); ?>:</option>
                                <option value="Subject"><?php echo _("Subject"); ?>:</option>
                                <option value="X-Mailer"><?php echo _("X-Mailer"); ?>:</option>
                            </select>
                        </td>
                        <td>
                            <input name="blockval" type="text" size="25" /> 
                            <input name="color" type="hidden" value="black" />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="2"><input class="btn" name="submit" type="submit" value="Submit" /></td>
                    </tr>
                </table>
            </form>
            
            <table align="center">
                <tr>
                    <td><?php echo _("Blocked"); ?></td>
                    <td><?php echo _("Headers Listed Below"); ?></td>
                    <td><?php echo _("(mail will be deleted):"); ?></td>
                </tr>
                <?php 
                    if (!DB::isError($blockresult)) {
                        while ($blockrow = $blockresult->fetchRow()) {
                            print "<tr><td><a href=\"userblocksubmit.php?action=delete&block_id={$blockrow['block_id']}\"><img title=\"Delete\" src=\"images/trashcan.gif\" alt=\"trashcan\"></a></td>";
                            print "<td>{$blockrow['blockhdr']}</td><td>{$blockrow['blockval']}</td></tr>\n";
                        }
                    }
                ?>
            </table>
        </div>
    </body>
</html>