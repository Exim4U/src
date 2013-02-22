<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . "/config/functions.php";
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
                        <li><a href="adminfailadd.php"><?php echo _('Add Fail'); ?></a></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <table align="center">
                <tr>
                    <th></th>
                    <th><?php echo _('Failed Address'); ?>..</th>
                </tr>
                <?php
                $query = "SELECT user_id,localpart FROM users
                WHERE domain_id='{$_SESSION['domain_id']}'
                AND users.type='fail'
                ORDER BY localpart;";
                $result = $db->query($query);
                if ($result->numRows()) {
                    while ($row = $result->fetchRow()) {
                        print '<tr>'
                        . '<td>'
                        . '<a href="adminfaildelete.php?user_id='
                        . $row['user_id']
                        . '"><img class="trash" src="images/trashcan.gif" title="'
                        . _('Delete fail ')
                        . $row['localpart']
                        . '"></a></td>';
                        print '<td>'
                        . '<a href="adminfailchange.php?user_id='
                        . $row['user_id']
                        . '">'
                        . $row['localpart']
                        . '@'
                        . $_SESSION['domain']
                        . '</a></td>';
                        print '</tr>';
                    }
                }
                ?>
            </table>
        </div>
    </body>
</html>