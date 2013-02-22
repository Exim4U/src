<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

  if (isset($_GET['LETTER'])) {
    $letter = strtolower($_GET['LETTER']);
  } else {
    $letter = '';
  }
  if (!isset($_POST['searchfor'])) {
    $_POST['searchfor'] = '';
  }
  if (!isset($_POST['field']) || ($_POST['field'] != 'localpart')) {
    $_POST['field'] = 'realname';
  }
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
                        <li><a href="adminuseradd.php"><?php echo _('Add User'); ?></a></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <?php
                alpha_menu($alphausers)
            ?>

            <form name="search" method="post" action="adminuser.php">
                <div class="input-prepend input-append">
                <span class="add-on"><?php echo _('Search'); ?>:</span>
                <input type="text" size="20" name="searchfor" value="<?php echo $_POST['searchfor']; ?>" />
                <?php echo _('in'); ?>
                <select name="field">
                    <option value="realname" <?php if ($_POST['field'] == 'realname') {
                    echo 'selected="selected"';
                    } ?>><?php echo _('User'); ?></option>
                    <option value="localpart" <?php if ($_POST['field'] == 'localpart') {
                    echo "selected=\"selected\"";
                    } ?>><?php echo _('Email address'); ?></option>
                </select> 
                <input class="btn" type="submit" name="search" value="<?php echo _('search'); ?>" />
                </div>
            </form>
      
            <h4><?php
            $query = "SELECT count(users.user_id)
            AS used, max_accounts
            FROM domains,users
            WHERE users.domain_id='{$_SESSION['domain_id']}'
            AND domains.domain_id=users.domain_id
            AND (users.type='local' OR users.type='piped')
            GROUP BY max_accounts"; 
            $result = $db->query($query);
            $row = $result->fetchRow();
            if (($result->numRows()) && $row['max_accounts']) {
                print "({$row['used']} of {$row['max_accounts']})";
            }
            ?></h4>
      
            <table>
                <tr>
                    <th></th>
                    <th><?php echo _('User'); ?></th>
                    <th><?php echo _('Email address'); ?></th>
                    <th><?php echo _('Admin'); ?></th>
                </tr>
                <?php
                    $query = "SELECT user_id, localpart, realname, admin, enabled 
                    FROM users
                    WHERE domain_id = '{$_SESSION['domain_id']}' 
                    AND  (type = 'local' OR type= 'piped')";
                    if ($alphausers AND $letter != '') {
                    $query .= " AND lower(localpart) LIKE lower('{$letter}%')";
                    } elseif ($_POST['searchfor'] != '') {
                    $query .= ' AND '
                    . $_POST['field']
                    .  ' LIKE "%'
                    . $_POST['searchfor']
                    . '%"';
                    }
                    $query .= ' ORDER BY realname';
                    $result = $db->query($query);
                    while ($row = $result->fetchRow()) {
                    print '<tr>';
                    print '<td><a href="adminuserdelete.php?user_id='
                    . $row['user_id']
                    . '&localpart='
                    . $row['localpart']
                    . '">';
                    print '<img title="Delete '
                    . $row['realname']
                    . '" src="images/trashcan.gif" alt="trashcan"></a></td>';
                    print '<td><a href="adminuserchange.php?user_id=' . $row['user_id']
                    . '&localpart=' . $row['localpart']
                    . '" title="' . _('Click to modify')
                    . $row['realname']
                    . '">'
                    . $row['realname']
                    . '</a></td>';
                    print '<td>' . $row['localpart'] .'@'. $_SESSION['domain'] . '</td>';
                    print '<td>';
                    if ($row['admin'] == 1) {
                    print  '<img src="images/check.gif" title="'
                    . $row['realname']
                    . _(' is an administrator')
                    . '">';
                }
                    print "</td></tr>\n";
                }
                ?>
            </table>
        </div>
    </body>
</html>