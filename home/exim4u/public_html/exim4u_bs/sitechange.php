<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authsite.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _("Exim4U") . ": " . _("Manage Domains"); ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/scripts.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body onLoad="document.passwordchange.localpart.focus()">
        <div class="container">
            <?php include dirname(__FILE__) . "/config/header_domain.php"; ?>
            
            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="site.php"><?php echo _("Manage Domains"); ?></a></li>
                        <li><a href="sitepassword.php"><?php echo _("Site Password"); ?></a></li>
                        <li><a href="logout.php"><?php echo _("Logout"); ?></a></li>
                    </ul>
                </div>
            </div>
            
            <table align="center">
                <tr>
                    <th colspan="2"><?php echo _("Modify Domain Admin"); ?>:</th>
                </tr>
                <form name="passwordchange" method="post" action="sitechangesubmit.php">
                <tr>
                    <td><?php echo _("Admin"); ?>:</td>
                    <td>
                        <div class="input-append">
                            <select name="localpart">
                                <?php
                                $query = "SELECT localpart,domain FROM users,domains
                                WHERE domains.domain_id='" . $_GET['domain_id'] . "'
                                AND admin=1 AND users.domain_id=domains.domain_id";
                                $result = $db->query($query);
                                if ($result->numRows()) {
                                while ($row = $result->fetchRow()) {
                                print '<option value="' . $row['localpart'] . '">' . $row['localpart'] . '</option>' . "\n\t";
                                }
                                }
                                ?>
                            </select>
                            <span class="add-on">
                                @<?php 
                                $query = "SELECT * FROM domains WHERE domain_id='{$_GET['domain_id']}'";
                                $result = $db->query($query);
                                if ($result->numRows()) { $row = $result->fetchRow(); }
                                print $row['domain']; ?>
                            </span>
                        </div>
                        <input name="domain_id" type="hidden" value="<?php print $_GET['domain_id']; ?>" />
                        <input name="domain" type="hidden" value="<?php print $_GET['domain']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo _("Password"); ?>:
                    </td>
                    <td>
                        <input name="clear" size="25" type="password" />
                    </td>
                </tr>
                <tr>
                    <td><?php echo _("Verify Password"); ?>:</td>
                    <td><input name="vclear" size="25" type="password"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input class="btn" name="submit" size="25" type="submit" value="<?php echo _("Submit Password"); ?>"></td>
                </tr>
                </form>
            </table>
            
            <table>
                <tr>
                    <th colspan="2"><?php echo _("Modify Domain Properties"); ?>:</th>
                </tr>
                <form name="domainchange" method="post" action="sitechangesubmit.php">
                <?php if ($multi_ip == "yes") { ?>
                <tr>
                    <td><?php echo _("Outgoing IP"); ?>:</td>
                    <td><input type="text" size="12" name="outgoingip" value="<?php print $row['outgoing_ip']; ?>" /></td>
                </tr>
                <?php } ?>
                <tr>
                    <td><?php echo _("System UID"); ?>:</td>
                    <td><input type="text" size="5" name="uid" value="<?php print $row['uid']; ?>" /></td>
                </tr>
                <tr>
                    <td><?php echo _("System GID"); ?>:</td>
                    <td><input type="text" size="5" name="gid" value="<?php print $row['gid']; ?>" /></td>
                </tr>
                <tr>
                    <td><?php echo _("Maximum Accounts") . "<br />(" . _("0 for unlimited") . ")"; ?>:</td>
                    <td><input type="text" size="5" name="max_accounts" value="<?php print $row['max_accounts']; ?>"></td>
                </tr>
                <tr>
                    <td><?php echo _("Max Mailbox Quota In Mb") . "<br />(" . _("0 for disabled") . ")"; ?>:</td>
                    <td>
                        <div class="input-append">
                            <input type="text" size="5" name="quotas" value="<?php print $row['quotas']; ?>"><span class="add-on">Mb</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><?php echo _("Maximum Message Size"); ?>:</td>
                    <td>
                        <div class="input-append">
                            <input name="maxmsgsize" size="5" type="text" value="<?php print $row['maxmsgsize']; ?>"><span class="add-on">Kb</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td><?php echo _("Spamassassin Tag Score"); ?>:</td>
                    <td><input type="text" size="5" name="sa_tag" value="<?php print $row['sa_tag']; ?>" /></td>
                </tr>
                <tr>
                    <td><?php echo _("Spamassassin Discard Score"); ?>:</td>
                    <td><input type="text" size="5" name="sa_refuse" value="<?php print $row['sa_refuse']; ?>" /></td>
                </tr>
                <tr>
                    <td><?php echo _("Spamassassin"); ?>:</td>
                    <td><input type="checkbox" name="spamassassin" <?php if ($row['spamassassin'] == 1) {print "checked";} ?> /></td>
                </tr>
                <tr>
                    <td><?php echo _("Piping To Command"); ?>:</td>
                    <td><input type="checkbox" name="pipe" <?php if ($row['pipe'] == 1) {print "checked";} ?> /></td>
                </tr>
                <tr>
                    <td><?php echo _("Enabled"); ?>:</td>
                    <td>
                        <input type="checkbox" name="enabled" <?php if ($row['enabled'] == 1) {print "checked";} ?> />
                        <input name="domain_id" type="hidden" value="<?php print $_GET['domain_id']; ?>" />
                        <input name="domain" type="hidden" value="<?php print $_GET['domain']; ?>" />
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td><input class="btn" name="submit" size="25" type="submit" value="<?php echo _("Submit Changes"); ?>"></td>
                </tr>
            </table>

        </div>
    </body>
</html>