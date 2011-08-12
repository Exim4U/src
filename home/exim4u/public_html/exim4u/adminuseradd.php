<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

  # enforce limit on the maximum number of user accounts in the domain
  $query = "SELECT (count(users.user_id) < domains.max_accounts)
    OR (domains.max_accounts = 0) AS allowed FROM
    users,domains WHERE users.domain_id=domains.domain_id
    AND domains.domain_id='{$_SESSION['domain_id']}'
    AND (users.type='local' OR users.type='piped')
    GROUP BY domains.max_accounts";
  $result = $db->query($query);
  if ($result->numRows()) {
    $row = $result->fetchRow();
  }
  if (!$row['allowed']) {
    header ('Location: adminuser.php?maxaccounts=true');
  }

  $query = "SELECT * FROM domains WHERE domain_id='{$_SESSION['domain_id']}'";
  $result = $db->query($query);
  $row = $result->fetchRow();
?>
<html>
  <head>
    <title><?php echo _('Exim4U') . ': ' . _('Manage Users'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body onLoad="document.adminadd.realname.focus()">
    <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="menu">
      <a href="adminuser.php"><?php echo _('Manage Accounts'); ?></a><br>
      <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="forms">
    <form name="adminadd" method="post" action="adminuseraddsubmit.php">
      <table align="center">
        <tr>
          <td><?php echo _('Name'); ?>:</td>
          <td>
            <input type="textfield" size="25" name="realname" class="textfield">
          </td>
        </tr>
        <tr>
          <td><?php echo _('Address'); ?>:</td>
          <td colspan='2'>
            <input type="textfield" size="25" name="localpart"
              class="textfield">@<?php print $_SESSION['domain']; ?>
          </td>
        </tr>
        <tr>
          <td><?php echo _('Password'); ?>:</td>
          <td>
            <input type="password" size="25" name="clear" class="textfield">
          </td>
        </tr>
        <tr>
          <td><?php echo _('Verify Password'); ?>:</td>
          <td>
            <input type="password" size="25" name="vclear" class="textfield">
          </td>
        </tr>
        <?php if ($postmasteruidgid == "yes") {
          print '<tr><td>'
            . _('UID')
            . ':</td><td><input type="textfield" size="5" name="uid"'
            . 'class="textfield" value="'
            . $row['uid']
            . '"></td></tr>';
          print "<tr><td>"
            . _('GID')
            . ':</td><td><input type="textfield" size="5" name="gid"'
            . 'class="textfield" value="'
            . $row['gid']
            . '"></td></tr>'; 
        }
        if ($row['quotas'] > "0") {
          print '<tr><td>';
          printf (_('Mailbox quota (%s Mb max)'), $row['quotas']);
          print ':</td>';
          print '<td><input type="text" size="5" name="quota" value="'
            . $row['quotas']
            . '" class="textfield">'
            . _('Mb')
            . '</td></tr>';
        } ?>
        <tr>
          <td><?php echo _('Has domain admin privileges?'); ?></td>
          <td><input name="admin" type="checkbox"></td>
        </tr>
        <?php if ($row['pipe'] == "1") { ?>
           <tr>
            <td><?php echo _('Pipe To Command'); ?>:</td>
            <td><input type="textfield" size="25" name="smtp" class="textfield"></td>
          </tr>
          <tr>
            <td colspan="2" style="padding-bottom:1em">
              <?php echo _('Optional'); ?>:
              <?php echo _('Pipe all mail to a command (e.g. procmail).'); ?>
              <br>
              <?php echo _('Check box below to enable.'); ?>
            </td>
          </tr>
          <tr>
            <td><?php echo _('Enable Piped Command?'); ?></td>
            <td><input type="checkbox" name="on_piped"></td>
          </tr>
          <?php }
            if ($row['spamassassin'] == "1") {
          ?>
          <tr>
            <td><?php echo _('Spamassassin'); ?>:</td>
            <td><input name="on_spamassassin" type="checkbox" checked></td>
          </tr>
          <tr>
        <tr>
            <td><?php echo _('Enable Spam Box'); ?>:</td>
            <td><input name="on_spambox" type="checkbox" checked></td>
          </tr>
         <tr>
            <td><?php echo _('Enable Spam Box Report'); ?>:</td>
            <td><input name="on_spamboxreport" type="checkbox" ></td>
          </tr>
            <td><?php echo _('Spamassassin Tag Score'); ?>:</td>
            <td>
              <input name="sa_tag" size="5" type="text" class="textfield"
                value="<?php echo $row['sa_tag']; ?>"></td>
          </tr>
          <tr>
            <td><?php echo _('Spamassassin Discard Score'); ?>:</td>
            <td>
              <input name="sa_refuse" size="5" type="text" class="textfield"
                value="<?php echo $row['sa_refuse']; ?>">
            </td>
          </tr>
          <?php } ?>
          <tr>
            <td><?php echo _('Maximum Message Size'); ?>:</td>
            <td>
              <input name="maxmsgsize" size="5" type="text"
                value="<?php echo $row['maxmsgsize']; ?>">Kb
            </td>
          </tr>
        <tr>
          <td><?php echo _('Enabled'); ?>:</td>
            <td><input name="enabled" type="checkbox" checked>
          </td>
        </tr>
        <tr>
          <td colspan="2" class="button">
          <input name="submit" type="submit" value="<?php echo _('Submit'); ?>">
          </td>
        </tr>
      </table>
    </form>
  </body>
</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
