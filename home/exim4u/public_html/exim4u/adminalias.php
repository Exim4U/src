<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
?>
<html>
  <head>
    <title><?php echo _('Exim4U') . ': ' . _('Manage Users'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body>
    <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="menu">
      <a href="adminaliasadd.php"><?php echo _('Add Alias'); ?></a></br>
          <?php $query = "SELECT user_id,realname,smtp,localpart FROM users
            WHERE domain_id=:domain_id AND type='catch'";
            $sth = $dbh->prepare($query);
            $sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
            if (!$sth->rowCount()) {
          print '<a href="admincatchalladd.php">'
            . _('Add Catchall')
            . '</a></br>';
        }
      ?>
      <a href="admin.php"><?php echo _('Main Menu'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="Content">
    <table align="center">
      <tr>
        <th>&nbsp;</th>
        <th><?php echo _('Alias'); ?></th>
        <th><?php echo _('Target address'); ?></th>
        <th><?php echo _('Forwards to..'); ?></th>
        <th><?php echo _('Admin'); ?></th>
      </tr>
      <?php
        if ($sth->rowCount()) {
            $row = $sth->fetch();
            print '<tr><td align="center">'
            . '<a href="adminaliasdelete.php?user_id='
            . $row['user_id']
            . '&localpart='
            . $row['localpart']
            . '">'
            . '<img class="trash" src="images/trashcan.gif" title="'
            . _("Delete alias ")
            . $row['localpart']
            . '"></a></td>';
          print '<td>'
            . '<a href="admincatchall.php?user_id=' 
            . $row['user_id'] 
            . '">'
            . $row['realname']
            . '</a></td>';
          print '<td>*</td>';
          print '<td>' . $row['smtp'] . '</td>';
          print '<td class="check">';
          print '</tr>';
        }
        $query = "SELECT user_id,localpart,smtp,realname,type,admin,enabled
          FROM users
          WHERE domain_id=:domain_id AND type='alias' 
  		  ORDER BY localpart;";
	$sth = $dbh->prepare($query);
        $sth->execute(array(':domain_id'=>$_SESSION['domain_id']));
        if ($sth->rowCount()) {
          while ($row = $sth->fetch()) {
            if($row['enabled']==="0") print '<tr class="disabled">'; else print '<tr>';
            print '<td align="center">'
              . '<a href="adminaliasdelete.php?user_id='
              . $row['user_id']
              . '&localpart='
              . $row['localpart']
              . '"><img class="trash"src="images/trashcan.gif" title="'
              . _('Delete alias ')
              . $row['localpart']
              .  '"></a></td>';
            print '<td>';
            print '<a href="adminaliaschange.php?user_id='
              . $row['user_id']
              . '">'
              . $row['realname']
              . '</a></td>';
            print '<td>' . $row['localpart'] . '</td>';
            print '<td>' . $row['smtp'] . '</td>';
            print '<td class="check">';
            if ($row['admin'] == "1") {
            print '<img class="check" src="images/check.gif" title="'
              . $row['realname'] . _(' is an administrator')
              . '">';
            }
            print '</tr>';
          }
        }
      ?>
      <tr>
        <td colspan="4" style="padding-top:1em">
          <b><?php echo _('Note'); ?>:</b>
          <?php
            echo _('You can only have one catchall per domain.')
            . '<br />'
            . _('It will catch and forward all email that does not get delivered to a specific mailbox.');
          ?>
        </td>
      </tr>
    </table>
    </div>
  </body>
</html>

<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
