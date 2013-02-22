<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
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
                        <li><a href="adminaliasadd.php"><?php echo _('Add Alias'); ?></a></li>
                        <?php $query = "SELECT user_id,realname,smtp FROM users
                        WHERE domain_id='{$_SESSION['domain_id']}' AND type='catch'";
                        $result = $db->query($query);
                        if (!$result->numRows()) {
                            print '<li><a href="admincatchalladd.php">'
                            . _('Add Catchall')
                            . '</a></li>';
                        }
                        ?>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <table>
                <tr>
                    <th></th>
                    <th><?php echo _('Alias'); ?></th>
                    <th><?php echo _('Target address'); ?></th>
                    <th><?php echo _('Forwards to..'); ?></th>
                    <th><?php echo _('Admin'); ?></th>
                </tr>
                <?php
                if ($result->numRows()) {
                    $row = $result->fetchRow();
                    print '<tr><td>'
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
                $query = "SELECT user_id,localpart,smtp,realname,type,admin
                FROM users
                WHERE domain_id='{$_SESSION['domain_id']}' AND type='alias' 
                ORDER BY localpart;";
                $result = $db->query($query);
                if ($result->numRows()) {
                    while ($row = $result->fetchRow()) {
                        print '<tr><td>'
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
                    <td colspan="5">
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