<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authuser.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

   $query = "SELECT * FROM users WHERE user_id=:user_id";
   $sth = $dbh->prepare($query);
   $success = $sth->execute(array(':user_id'=>$_SESSION['user_id']));
   if ($success) { $row = $sth->fetch(); }
?>
<html>
  <head>
    <title><?php echo _('Exim4U') . ': ' . _('Manage Users'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body onLoad="document.aliaschange.realname.focus()">
    <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="Menu">
      <a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="Forms">
	<form name="aliaschange" method="post" action="useraliaschangesubmit.php">
        <table align="center">
          <tr>
            <td><?php echo _('Alias Name'); ?>:</td>
            <td>
              <input name="realname" type="text"
              value="<?php print $row['realname']; ?>"class="textfield">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Address'); ?>:</td>
            <td>
              <?php print $row['localpart']."@".$_SESSION['domain']; ?>
            </td>
          </tr>
          <tr>
            <td>
              <input name="user_id" type="hidden"
              value="<?php print $_GET['user_id']; ?>" class="textfield">
            </td>
          </tr>
          <tr>
            <td colspan="2" style="padding-bottom:1em">
              <?php
                echo _('Multiple addresses should be comma separated,
                with no spaces.');
              ?>
            </td>
          </tr>
          <tr>
            <td><?php echo _('Forward To'); ?>:</td>
            <td>
              <input name="target" type="text" size="30"
              value="<?php print $row['smtp']; ?>" class="textfield">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Password'); ?>:</td>
            <td>
              <input name="clear" type="password" size="30" class="textfield">
            </td>
          </tr>
          <tr
            ><td><?php echo _('Verify Password'); ?>:</td>
            <td>
              <input name="vclear" type="password" size="30" class="textfield">
            </td>
          </tr>
          <tr>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <input name="submit" type="submit"
              value="<?php echo _('Submit'); ?>">
            </td>
          </tr>
        </table>
      </form>
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
