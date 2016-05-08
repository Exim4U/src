<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
  $query = "SELECT localpart,realname,smtp,on_spamassassin,
    admin,enabled FROM users 	
    WHERE user_id=:user_id AND domain_id=:domain_id AND type='alias'";
  $sth = $dbh->prepare($query);
  $sth->execute(array(':user_id'=>$_GET['user_id'], ':domain_id'=>$_SESSION['domain_id']));
  if ($sth->rowCount()) {
  $row = $sth->fetch();
  }
?>
<html>
  <head>
    <title><?php echo _('Exim4U') . ': ' . _('Manage Users'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body onLoad="document.aliaschange.realname.focus()">
    <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="Menu">
      <a href="adminalias.php"><?php echo _('Manage Aliases'); ?></a><br>
      <a href="adminaliasadd.php"><?php echo _('Add Alias'); ?></a></br>
      <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="Forms">
	<?php 
		# ensure this page can only be used to view/edit aliases that already exist for the domain of the admin account
			if (!$sth->rowCount()) {
			echo '<table align="center"><tr><td>';
			echo "Invalid alias userid '" . htmlentities($_GET['user_id']) . "' for domain '" . htmlentities($_SESSION['domain']). "'";			
			echo '</td></tr></table>';
		}else{	
	?>
	<form name="aliaschange" method="post" action="adminaliaschangesubmit.php">
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
              <input name="localpart" type="text"
              value="<?php print $row['localpart']; ?>"class="textfield">@<?php print $_SESSION['domain']; ?>
            </td>
          </tr>
          <tr>
            <td>
              <input name="user_id" type="hidden"
              value="<?php print $_GET['user_id']; ?>"class="textfield">
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
              <input name="password" type="password" size="30" class="textfield">
            </td>
          </tr>
          <tr>
            <td colspan="2" style="padding-bottom:1em">
              <?php echo _('Password only needed to enable alias user
              log in or if the Alias is an admin account.'); ?>
            </td>
          </tr>
          <tr
            ><td><?php echo _('Verify Password'); ?>:</td>
            <td>
              <input name="vpassword" type="password" size="30" class="textfield">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Admin'); ?>:</td>
            <td>
              <input name="admin" type="checkbox"
              <?php if ($row['admin'] == 1) {
                print "checked";
              } ?> class="textfield">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Enabled'); ?>:</td>
            <td>
              <input name="enabled" type="checkbox"
              <?php if ($row['enabled'] == 1) {
                print "checked";
              } ?> class="textfield">
            </td>
          </tr>
          <tr>
            <td colspan="2" class="button">
              <input name="submit" type="submit"
              value="<?php echo _('Submit'); ?>">
            </td>
          </tr>
        </table>
      </form>
		<?php 		
			# end of the block editing an alias within the domain
		}  
		?>	
    </div>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
