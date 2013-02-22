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
    <body onLoad="document.adminadd.realname.focus()">
        <div class="container">
            <?php include dirname(__FILE__) . '/config/header.php'; ?>

            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="adminuser.php"><?php echo _('Manage Accounts'); ?></a></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <form name="adminadd" method="post" action="adminuseraddsubmit.php">
                <table>
                    <tr>
                        <td><?php echo _('Name'); ?>:</td>
                        <td><input type="text" size="25" name="realname" /></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Address'); ?>:</td>
                        <td>
                            <div class="input-append">
                                <input type="text" size="25" name="localpart" /><span class="add-on">@<?php print $_SESSION['domain']; ?></span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Password'); ?>:</td>
                        <td><input type="password" size="25" name="clear" /></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Verify Password'); ?>:</td>
                        <td><input type="password" size="25" name="vclear"></td>
                    </tr>
                    <?php 
                    if ($postmasteruidgid == "yes") {
                        print '<tr><td>'
                            . _('UID')
                            . ':</td><td><input type="text" size="5" name="uid"'
                            . 'value="'
                            . $row['uid']
                            . '" /></td></tr>';
                        print "<tr><td>"
                            . _('GID')
                            . ':</td><td><input type="text" size="5" name="gid"'
                            . 'value="'
                            . $row['gid']
                            . '" /></td></tr>'; 
                    }
                    if ($row['quotas'] > "0") {
                        print '<tr><td>';
                        printf (_('Mailbox quota (%s Mb max)'), $row['quotas']);
                        print ':</td>';
                        print '<td><div class="input-append"><input type="text" size="5" name="quota" value="'
                        . $row['quotas']
                        . '" /><span class="add-on">'
                        . _('Mb')
                        . '</span></div></td></tr>';
                    } 
                    ?>
                    <tr>
                        <td><?php echo _('Has domain admin privileges?'); ?></td>
                        <td><input name="admin" type="checkbox" /></td>
                    </tr>
                    <?php 
                        if ($row['pipe'] == "1") { 
                    ?>
                    <tr>
                        <td><?php echo _('Pipe To Command'); ?>:</td>
                        <td><input type="text" size="25" name="smtp" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                        <?php echo _('Optional'); ?>:
                        <?php echo _('Pipe all mail to a command (e.g. procmail).'); ?> 
                        <?php echo _('Check box below to enable.'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Enable Piped Command?'); ?></td>
                        <td><input type="checkbox" name="on_piped"></td>
                    </tr>
                    <?php 
                        }
                        if ($row['spamassassin'] == "1") {
                    ?>
                    <tr>
                        <td><?php echo _('Spamassassin'); ?>:</td>
                        <td><input name="on_spamassassin" type="checkbox" checked /></td>
                    </tr>
                    <tr>
                    <tr>
                        <td><?php echo _('Enable Spam Box'); ?>:</td>
                        <td><input name="on_spambox" type="checkbox" checked /></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Enable Spam Box Report'); ?>:</td>
                        <td><input name="on_spamboxreport" type="checkbox" /></td>
                    </tr>
                        <td><?php echo _('Spamassassin Tag Score'); ?>:</td>
                        <td><input name="sa_tag" size="5" type="text" value="<?php echo $row['sa_tag']; ?>" /></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin Discard Score'); ?>:</td>
                        <td><input name="sa_refuse" size="5" type="text" value="<?php echo $row['sa_refuse']; ?>" /></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td><?php echo _('Maximum Message Size'); ?>:</td>
                        <td>
                            <div class="input-append">
                                <input name="maxmsgsize" size="5" type="text" value="<?php echo $row['maxmsgsize']; ?>" /><span class="add-on">Kb</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Enabled'); ?>:</td>
                        <td><input name="enabled" type="checkbox" checked /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><input class="btn" name="submit" type="submit" value="<?php echo _('Submit'); ?>" /></td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>