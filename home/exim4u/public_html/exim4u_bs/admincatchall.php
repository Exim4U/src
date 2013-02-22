<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
  $query = "SELECT smtp FROM users WHERE user_id='{$_GET['user_id']}' 
			AND domain_id='{$_SESSION['domain_id']}' AND type='catch'";
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
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>
    
        	<?php 
        		# ensure this page can only be used to view/edit the catchall that already exist for the domain of the admin account
        		if (!$result->numRows()) {			
        			echo '<table><tr><td>';
        			echo "Invalid catchall userid '" . htmlentities($_GET['user_id']) . "' for domain '" . htmlentities($_SESSION['domain']). "'";
        			echo '</td></tr></table>';
        		}
        		else{	
        	?>
            <form name="admincatchall" method="post" action="admincatchallsubmit.php">
                <table>
                    <tr>
                        <td><?php echo _('Alias Name'); ?>:</td>
                        <td><?php echo _('Catchall'); ?></td>
                    </tr>
                    <tr>
                        <td><?php echo _('For Email Addressed To'); ?>:</td>
                        <td>*@<?php echo $_SESSION['domain']; ?></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Forward The Email To'); ?>:</td>
                        <td>
                            <input name="smtp" type="text" value="<?php echo $row['smtp']; ?>" /><br />
                            <?php echo _('Must be a full e-mail address'); ?>!
                        </td>
                    </tr>
                    <tr>
                        <td><input name="user_id" type="hidden" value="<?php print $_GET['user_id']; ?>" /></td>
                        <td><input class="btn" name="submit" type="submit" value="<?php echo _('Submit'); ?>" /></td>
                    </tr>
                </table>
            </form>
		<?php 		
			# end of block shown for editing the domains catchall
		}  
		?>  
		</div>
    </body>
</html>