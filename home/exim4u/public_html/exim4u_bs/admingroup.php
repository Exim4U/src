<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('List groups'); ?></title>
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
                        <li><a href="admingroupadd.php"><?php echo _('Add Group'); ?></a></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>

            <table>
                <tr>
                    <th></th>
                    <th><?php echo _('Email address'); ?></th>
                    <th><?php echo _('Is public'); ?></th>
                    <th><?php echo _('Enabled'); ?></th>
                </tr>
                <?php
                $query = "SELECT id, name, is_public, enabled FROM groups
                WHERE domain_id = '{$_SESSION['domain_id']}'
                ORDER BY NAME ASC";
                $result = $db->query($query);
                while ($row = $result->fetchRow()) {
                ?>
                <tr>
                    <td>
                        <a href="admingroupdelete.php?group_id=<?php echo $row['id']; ?>&localpart=<?php echo $row['name']; ?>">
                            <img title="<?php print _('Delete group') . $row['name']; ?>" src="images/trashcan.gif" alt="trashcan" />
                        </a>
                    </td>
                    <td>
                        <a href="admingroupchange.php?group_id=<?php echo $row['id']; ?>" title="<?php print _('Click to modify ') . $row['name']; ?>"><?php echo $row['name'].'@'.$_SESSION['domain']; ?></a>
                    </td>
                    <td>
                    <?php if ('Y' == $row['is_public']) { ?>
                        <img class="check" src="images/check.gif" title="<?php print _('Anyone can write to') . ' '. $row['name']; ?>">
                    <?php } ?>
                    </td>
                    <td>
                    <?php if ('1' == $row['enabled']) { ?>
                        <img class="check" src="images/check.gif" title="<?php print $row['name'] . _(' is enabled'); ?>">
                    <?php } ?>
                    </td>
                </tr>
                <?php
                }
                ?>
            </table>
        </div>
    </body>
</html>