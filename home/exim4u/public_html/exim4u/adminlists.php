<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  if (isset($_POST['listname'])) {
    if ($mailmandomain == "default") {
        header ("Location: $mailmanprotocol://{$_SESSION['domain']}/$mailmanpath/admin/{$_POST['listname']}");
    } else {
      if ($mailmanversion == "mailman3") {
        header ("Location: $mailmanprotocol://$mailmandomain/mailman3/$mailmanpath/lists/{$_POST['listname']}.{$_SESSION['domain']}");
      } else {
        header ("Location: $mailmanprotocol://$mailmandomain/$mailmanpath/admin/{$_POST['listname']}");
    }

    }
  }
?>
<html>
  <head>
    <title><?php echo _('Exim4U') . ': ' . _('Mailing List Administration'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body>
    <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="Menu">
      <?php print  _('Mailman Lists') . '<br><br>'; ?>
    <?php
    if ($mailmandomain == "default") {
      print "<a href=\"$mailmanprotocol://{$_SESSION['domain']}/$mailmanpath/listinfo\">" . _('View Lists') . '</a><br>';
      print "<a href=\"$mailmanprotocol://{$_SESSION['domain']}/$mailmanpath/create\">" . _('Add A List') . '</a><br>';
    } else {
      if ($mailmanversion == "mailman3") {
        print "<a href=\"$mailmanprotocol://$mailmandomain/mailman3/$mailmanpath\">" . _('View Lists') . '</a><br>';
        print "<a href=\"$mailmanprotocol://$mailmandomain/mailman3/$mailmanpath/lists/new\">" . _('Add A List') . '</a><br>';
      } else {
                    print "<a href=\"$mailmanprotocol://$mailmandomain/$mailmanpath/listinfo\">" . _('View Lists') . '</a><br>';
                    print "<a href=\"$mailmanprotocol://$mailmandomain/$mailmanpath/create\">" . _('Add A List') . '</a><br>';
        }
      } ?>
      <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="Forms">
      <form name="adminlists" method="post" action="adminlists.php">
      <table align="center">
        <tr>
            <td>
                  <?php echo _('Mailman List Administration') .'<br><br>'; ?>
              <?php echo _('Enter A List Name To Edit'); ?>:
            </td>
          </tr>
        <tr>
            <td>
	      <input name="listname" type="text" class="textfield"><?php  print "@".$_SESSION['domain']; ?>
            </td>
          </tr>
        <tr>
            <td class="button">
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
