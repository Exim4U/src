<?php
  require_once dirname(__FILE__) . '/config/variables.php';
  require_once dirname(__FILE__) . '/config/functions.php';
  require_once dirname(__FILE__) . '/config/httpheaders.php';
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U'); ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/scripts.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
  <body onLoad="document.login.localpart.focus()">

    <div class="container">
    <?php include dirname(__FILE__) . '/config/header.php'; ?>


    <form name="login" method="post" action="login.php">
        <label class="control-label" for="localpart"><?php echo _('Username'); ?></label> 
        <div class="controls">
        <input name="localpart" type="text" placeholder="<?php echo _("Username"); ?>"/>
        <?php
            $domain = preg_replace ("/^mail\./", "", $_SERVER["SERVER_NAME"]);
            if ($domaininput == 'dropdown') {
                $query = "SELECT domain FROM domains WHERE type='local' AND domain!='admin' ORDER BY domain";
            $result = $db->query($query);
        ?>
    <select name="domain">
                <option value=''>
<?php
        if ($result->numRows()) {
            while ($row = $result->fetchRow()) {
                print "                <option value='{$row['domain']}'>{$row['domain']}</option>\n";
            }
        }
    print '            </select>';
    }
else if ($domaininput == 'textbox') {
    print '<input type="text" name="domain" placeholder="Domain">';
} 
else if ($domaininput == 'static') {
    print $domain
    . '<input type="hidden" name="domain" value='
    . $domain
    . '>';
}
?>
        </div>
        
        <label class="control-label" for="crypt"><?php echo _("Password"); ?></label>
        <div class="controls">
            <input name="crypt" type="password" placeholder="<?php echo _("Password"); ?>">
        </div>

        <div class="form-actions">
            <input class="btn" name="submit" type="submit" value="<?php echo _("Submit"); ?>" class="longbutton">
        </div>

    </form>
    
    <img id="Exim4U" height="52" width="88" src="./images/logo.gif" border="0" alt="Exim4U" title="Exim4U">
    </div>
</body>
</html>