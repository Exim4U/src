<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

  if ($_GET['confirm'] == '1') {
    $query = "DELETE FROM users 
      WHERE user_id='{$_GET['user_id']}'
      AND domain_id='{$_SESSION['domain_id']}'
	  AND (type='alias' OR type='catch')";
    $result = $db->query($query);
    if (!DB::isError($result)) {
      header ("Location: adminalias.php?deleted={$_GET['localpart']}");
      die;
    } else {
      header ("Location: adminalias.php?faildeleted={$_GET['localpart']}");
      die;
    }
  } else if ($_GET['confirm'] == 'cancel') {
    header ("Location: adminalias.php?faildeleted={$_GET['localpart']}");
    die;
  }
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('Confirm Delete'); ?></title>
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
                        <li><a href="adminaliasadd.php"><?php echo _('Add Alias'); ?></a></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>
    
            <form name="aliasdelete" method="get" action="adminaliasdelete.php">
                <table>
                    <tr>
                        <td colspan="2">
                        <?php printf (_('Please confirm deleting alias %s@%s'),
                            $_GET['localpart'] ,
                            $_SESSION['domain']);
                        ?>:
                        </td>
                    </tr>
                    <tr>
                        <td><input name='confirm' type='radio' value='cancel' checked></td>
                        <td>
                            <b><?php printf (_('Do Not Delete %s@%s'),
                                $_GET['localpart'],
                                $_SESSION['domain']);
                            ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td><input name='confirm' type='radio' value='1'></td>
                        <td>
                            <b><?php printf (_('Delete %s@%s'),
                                $_GET['localpart'],
                                $_SESSION['domain']);
                            ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input name='domain' type='hidden' value='<?php echo $_SESSION['domain']; ?>'>
                            <input name='user_id' type='hidden' value='<?php echo $_GET['user_id']; ?>'>
                            <input name='localpart' type='hidden' value='<?php echo $_GET['localpart']; ?>'>
                            <input class="btn" name='submit' type='submit' value='<?php echo _('Continue'); ?>'>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>