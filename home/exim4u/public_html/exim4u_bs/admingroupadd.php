<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . "/config/functions.php";
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('Add Group'); ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/scripts.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body onLoad="document.adminadd.localpart.focus()">
        <div class="container">
            <?php include dirname(__FILE__) . '/config/header.php'; ?>

            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="admingroup.php"><?php echo _('Manage Groups'); ?></a></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <form name="adminadd" method="post" action="admingroupaddsubmit.php">
                <table>
                    <tr>
                        <td><?php echo _('Group Address'); ; ?>:</td>
                        <td>
                            <div class="input-append">
                                <input name="localpart" type="text" /><span class="add-on">@<?php echo $_SESSION['domain']; ?></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input class="btn" name="submit" type="submit" value="<?php echo _('Submit'); ?>" /></td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>