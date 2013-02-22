<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  if (isset($_POST['listname'])) {
    if ($mailmandomain == "default") {
        header ("Location: $mailmanprotocol://{$_SESSION['domain']}/$mailmanpath/admin/{$_POST['listname']}");
    } else {
        header ("Location: $mailmanprotocol://$mailmandomain/$mailmanpath/admin/{$_POST['listname']}");
    }

  }
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('Mailing List Administration'); ?></title>
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
                        <?php
                        if ($mailmandomain == "default") {
                          print "<li><a href=\"$mailmanprotocol://{$_SESSION['domain']}/$mailmanpath/listinfo\">" . _('View Lists') . '</a></li>';
                          print "<li><a href=\"$mailmanprotocol://{$_SESSION['domain']}/$mailmanpath/create\">" . _('Add A List') . '</a></li>';
                        } else {
                          print "<li><a href=\"$mailmanprotocol://$mailmandomain/$mailmanpath/listinfo\">" . _('View Lists') . '</a></li>';
                          print "<li><a href=\"$mailmanprotocol://$mailmandomain/$mailmanpath/create\">" . _('Add A List') . '</a></li>';
                        } ?>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <h4><?php print  _('Mailman Lists'); ?></h4>
            
            <form name="adminlists" method="post" action="adminlists.php">
                <table align="center">
                    <tr>
                        <td>
                            <?php echo _('Mailman List Administration') .'<br><br>'; ?>
                            <?php echo _('Please Enter A List Name'); ?>:
                        </td>
                    </tr>
                    <tr>
                        <td><input name="listname" type="text" /></td>
                    </tr>
                    <tr>
                        <td><input class="btn" name="submit" type="submit" value="<?php echo _('Submit'); ?>" /></td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>