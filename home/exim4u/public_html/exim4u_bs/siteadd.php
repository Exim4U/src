<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authsite.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('Manage Domains'); ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/scripts.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body onLoad="document.siteadd.domain.focus()">

        <div class="container">
        
            <?php include dirname(__FILE__) . '/config/header_domain.php'; ?>
            
            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="site.php"><?php echo _('Manage Domains'); ?></a></li>
                        <li><a href="sitepassword.php"><?php echo _('Site Password'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <form name="siteadd" method="post" action="siteaddsubmit.php">
                <table>
                    <tr>
                        <td><?php echo _('New Domain'); ?>:</td>
                        <td><input name="domain" type="text" /></td>
                        <td><?php echo _('Domain that you are adding.'); ?></td>
                    </tr>
                    <?php
                    if ($_GET['type'] == "local") {
                    ?>
                    <tr>
                        <td><?php echo _('Domain Admin'); ?>:</td>
                        <td><input name="localpart" type="text" value="postmaster" /></td>
                        <td>
                        <?php
                        echo _('Username of domain\'s administrator');
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Password'); ?>:</td>
                        <td><input name="clear" type="password" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Verify Password'); ?>:</td>
                        <td><input name="vclear" type="password" /></td>
                        <td></td>
                    </tr>
                    <?php if ($multi_ip == "yes") {      ?>
                    <tr>
                        <td><?php echo _('Outgoing IP'); ?>:</td>
                        <td><input name="outgoingip" size="12" type="text" value="<?php echo $outgoing_IP; ?>" /></td>
                        <td></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><?php echo _('System UID'); ?>:</td>
                        <td><input name="uid" type="text" value="<?php echo $uid; ?>" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('System GID'); ?>:</td>
                        <td><input name="gid" type="text" value="<?php echo $gid; ?>" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Domain Mail Directory'); ?>:</td>
                        <td><input name="maildir" type="text" value="<?php echo $mailroot; ?>" /></td>
                        <td>
                            <?php echo _('Create the domain directory below this top-level mailstore');?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo _('Maximum Accounts'); ?><br>
                            (<?php echo _("0 for unlimited"); ?>):
                        </td>
                        <td>
                            <input type="text" size="5" name="max_accounts" value="0" />
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>
                            <?php echo _('Max Mailbox Quota'); ?>
                            (<?php echo _('0 for disabled'); ?>):
                        </td>
                        <td>
                            <div class="input-append">
                                <input name="quotas" size="5" type="text" value="0" /><span class="add-on"><? echo _('Mb'); ?></span>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Maximum Message Size'); ?>:</td>
                        <td>
                            <div class="input-append">
                                <input name="maxmsgsize" size="5" type="text" value="0" /><span class="add-on"><?php echo _('Kb'); ?></span></td>
                            </div>
                        <td><?php echo _('The maximum size for incoming mail (user tunable)'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin Tag Score'); ?>:</td>
                        <td><input name="sa_tag" size="5" type="text" value="<?php echo $sa_tag; ?>" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin Discard Score'); ?>:</td>
                        <td><input name="sa_refuse" size="5" type="text" value="<?php echo $sa_refuse; ?>" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin enabled?'); ?></td>
                        <td><input name="spamassassin" type="checkbox" checked /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Enable piping mail to command?'); ?></td>
                        <td><input name="pipe" type="checkbox" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Domain enabled?'); ?></td>
                        <td><input name="enabled" type="checkbox" checked /></td>
                        <td></td>
                    </tr>
                    <?php
                    } else if ($_GET['type'] == "alias") {
                    ?>
                    <tr>
                        <td><?php echo _('Redirect Messages To Domain'); ?>:</td>
                        <td><input name="aliasdest" type="text" /></td>
                        <td></td>
                    </tr>
                    <?php
                    /* GLD MOD to get relay server address along with spamassassin options */
                    } else if ($_GET['type'] == "relay") {
                    ?>
                    <tr>
                        <td><?php echo _('Relay Server'); ?>:</td>
                        <td><input name="relaydest" type="text" size="22" /></td>
                        <td><?php echo _('Destination mail server name.'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin Tag Score'); ?>:</td>
                        <td><input name="sa_tag" size="5" type="text" value="<?php echo $sa_tag; ?>" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin Discard Score'); ?>:</td>
                        <td><input name="sa_refuse" size="5" type="text" value="<?php echo $sa_refuse; ?>" /></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin enabled?'); ?></td>
                        <td><input name="spamassassin" type="checkbox" checked /></td>
                        <td></td>
                    </tr>
                    <?php
                    }
                    /* GLD MOD end */
                    ?>
                    <tr>
                        <td></td>
                        <td>
                            <input name="type" type="hidden" value="<?php print $_GET['type']; ?>" />
                            <input name="admin" type="hidden" value="1" />
                            <input class="btn" name="submit" type="submit" value="<?php echo _('Submit'); ?>" />
                        </td>
                        <td></td>
                    </tr>
                </table>
            </form>

        </div>
    </body>
</html>