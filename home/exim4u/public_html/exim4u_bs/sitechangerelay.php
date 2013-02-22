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

            <table>
                <tr>
                    <th><?php echo _("Modify Relay Domain Properties"); ?>:</th>
                    <th>
                        <?php 
                            $query = "SELECT * FROM domains WHERE domain_id='{$_GET['domain_id']}'";
                            $result = $db->query($query);
                            if ($result->numRows()) { $row = $result->fetchRow(); }
                            echo _("Relay Domain: "); echo $row['domain'];      
                        ?>
                    </th>
                </tr>
                <form name="domainchange" method="post" action="sitechangerelaysubmit.php">
                <input name="domain_id" type="hidden" value="<?php print $_GET['domain_id']; ?>" />
                <input name="domain" type="hidden" value="<?php print $_GET['domain']; ?>" />
                <tr>
                    <td><?php echo _('Relay Server Address'); ?>:</td>
                    <td><input type="text" size="30" name="relaydest" value="<?php print $row['relay_address']; ?>" /></td>
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
                    <td></td>
                    <td>
                        <input name="domain_id" type="hidden" value="<?php print $_GET['domain_id']; ?>" />
                        <input name="domain" type="hidden" value="<?php print $_GET['domain']; ?>" />
                        <input class="btn" name="submit" size="25" type="submit" value="<?php echo _("Submit Changes"); ?>" />
                    </td>
                </tr>
            </table>
            
        </div>
    </body>
</html>