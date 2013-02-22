<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

  $query = "SELECT localpart FROM users WHERE user_id='{$_GET['user_id']}' AND domain_id='{$_SESSION['domain_id']}' AND users.type='fail'";
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
    <body onLoad="document.failchange.localpart.focus()">
        <div class="container">
            <?php include dirname(__FILE__) . '/config/header.php'; ?>
            
            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="adminfail.php"><?php echo _('Manage Fails'); ?></a></li>
                        <li><a href="adminfailadd.php"><?php echo _('Add Fail'); ?></a></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

        	<?php 
        		# ensure this page can only be used to view/edit fail's that already exist for the domain of the admin account
        		if (!$result->numRows()) {			
        			echo '<table><tr><td>';
        			echo "Invalid fail userid '" . htmlentities($_GET['user_id']) . "' for domain '" . htmlentities($_SESSION['domain']). "'";			
        			echo '</td></tr></table>';
        		}else{	
        	?>
            <form name="failchange" method="post" action="adminfailchangesubmit.php">
                <table>
                    <tr>
                        <td><?php echo _('Fail address'); ?>:</td>
                        <td>
                            <div class="input-append">
                                <input name="localpart" type="text" value="<?php print $row['localpart']; ?>" /><span class="add-on">@<?php print $_SESSION['domain']; ?></span>
                            </div>
                            <input name="user_id" type="hidden" value="<?php print $_GET['user_id']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input class="btn" name="submit" type="submit" value="<?php echo _('Submit'); ?>" /></td>
                    </tr>
                </table>
            </form>
            <?php
                # end of the block editing a fail within the domain
                }  
            ?>	  
		</div>
    </body>
</html>