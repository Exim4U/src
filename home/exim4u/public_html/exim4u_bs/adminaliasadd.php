<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
  $query = "SELECT avscan,spamassassin FROM domains
    WHERE domain_id='{$_SESSION['domain_id']}'";
  $result = $db->query($query);
  if ($result->numRows()) { $row = $result->fetchRow(); }
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('Manage Users'); ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/scripts.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body>
        <div class="container">
            <?php include dirname(__FILE__) . '/config/header.php'; ?>

            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="adminalias.php"><?php echo _('Manage Aliases'); ?></a></li>
                        <li class="divider"></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <form name="adminadd" method="post" action="adminaliasaddsubmit.php">
                <table>
                    <tr>
                        <td><?php echo _('Alias Name'); ?>:</td>
                        <td><input name="realname" type="text"></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Address'); ?>:</td>
                        <td>
                            <div class="input-append">
                                <input name="localpart" type="text"><span class="add-on">@<?php print $_SESSION['domain']; ?></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo _('Multiple addresses should be comma separated, with no spaces.'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Forward To'); ?>:</td>
                        <td><input name="smtp" type="text" size="30"></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Password'); ?>:</td>
                        <td><input name="clear" type="password" size="30"></td>
                    </tr>
                    <tr>
                        <td colspan="2"><?php echo _('Password only needed if you want the user to be able to log in, or if the Alias is the admin account.'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Verify Password'); ?>:</td>
                        <td><input name="vclear" type="password" size="30"></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Admin'); ?>:</td>
                        <td><input name="admin" type="checkbox"></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Enabled'); ?>:</td>
                        <td><input name="enabled" type="checkbox" checked></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input class="btn" name="submit" type="submit" value="<?php echo _('Submit'); ?>"></td>
                    </tr>
                </table>
            </form>
      
        </div>
    </body>
</html>