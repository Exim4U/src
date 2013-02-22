<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authsite.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _("Exim4U") . ": " . _("Manage Sites"); ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/scripts.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body onLoad="document.sitepassword.password.focus()">
        <div class="container">

        <?php include dirname(__FILE__) . "/config/header.php"; ?>
        
        <div class="navbar">
            <div class="navbar-inner">
                <ul id="menu" class="nav">
                    <li><a href="site.php"><?php echo _("Manage Domains"); ?></a></li>
                    <li><a href="sitepassword.php"><?php echo _("Site Password"); ?></a></li>
                    <li><a href="logout.php"><?php echo _("Logout"); ?></a></li>
                </ul>
            </div>
        </div> 
    
        <form name="sitepassword" method="post" action="sitepasswordsubmit.php">
            <table>
                <tr>
                    <th colspan="2"><?php echo _("Change SiteAdmin Password"); ?>:</th>
                </tr>
                <tr>
                    <td><?php echo _("Password"); ?>:</td>
                    <td><input type="password" size="25" name="clear" /></td>
                </tr>
                <tr>
                    <td><?php echo _("Verify Password"); ?>:</td>
                    <td><input type="password" size="25" name="vclear" /></td>
                </tr>
                <tr>
                    <td></td>
                    <td id="button"><input class="btn" name="submit" type="submit" value="<?php echo _("Submit"); ?>"></td>
                </tr>
            </table>
        </form>
        
        </div>
    </body>
</html>