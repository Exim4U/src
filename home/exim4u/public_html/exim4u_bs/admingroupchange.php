<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . "/config/functions.php";
?>
<?php
  $query = "SELECT * FROM groups WHERE id='{$_GET['group_id']}' AND domain_id='{$_SESSION['domain_id']}'";
  $result = $db->query($query);
  $row = $result->fetchRow();
  $grouplocalpart = $row['name'];
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('Edit group'); ?></title>
        <meta charset="utf-8" />
        <link rel="stylesheet" href="css/bootstrap.min.css" />
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
        <script src="js/scripts.js"></script>
        <!--[if lt IE 9]>
        <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body onLoad="document.groupchange.realname.focus()">
        <div class="container">
            <?php include dirname(__FILE__) . '/config/header.php'; ?>
            
            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="admingroup.php"><?php echo _('Manage Groups'); ?></a></li>
                        <li><a href="admingroupadd.php"><?php echo _('Add Group'); ?></a></li>
                        <li><a href="admin.php"><?php echo _('Main Menu'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>
    
            <?php 
            # ensure this page can only be used to view/edit aliases that already exist for the domain of the admin account
            if (!$result->numRows()) {			
                echo '<table align="center"><tr><td>';
                echo "Invalid groupid '" . htmlentities($_GET['group_id']) . "' for domain '" . htmlentities($_SESSION['domain']). "'";			
                echo '</td></tr></table>';
            }
            else{	
            ?>	
            <form name="groupchange" method="post" action="admingroupchangesubmit.php">
                <table align="center">
                    <tr>
                        <td><?php echo _('Group Address'); ?>:</td>
                        <td colspan="3">
                            <div class="input-append">
                                <input name="localpart" type="text" value="<?php echo $row['name']; ?>" /><span class="add-on">@<?php echo $_SESSION['domain']; ?></span>
                            </div>
                            <input name="group_id" type="hidden" value="<?php echo $_GET['group_id']; ?>" />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Is Public'); ?></td>
                        <td colspan="3"><input name="is_public" type="checkbox" <?php echo $row['is_public'] == 'Y' ? 'checked' : ''; ?> /></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Enabled'); ?></td>
                        <td colspan="3"><input name="enabled" type="checkbox" <?php echo $row['enabled']=='1' ? 'checked' : ''; ?> /></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3"><input class="btn" name="editgroup" type="submit" value="Submit" /></td>
                    </tr>
                </table>
            </form>

            <table>
                <?php
                $query = "select u.realname, u.username, u.enabled, c.member_id
                from users u, group_contents c
                where u.user_id = c.member_id and c.group_id = '{$_GET['group_id']}'
                order by u.enabled desc, u.realname asc";
                $result = $db->query($query);
                if ($result->numRows()) {
                ?>
                        <tr>
                            <th></th>
                            <th><?php echo _('Real name'); ?></th>
                            <th><?php echo _('Email Address'); ?></th>
                            <th><?php echo _('Enabled'); ?></th>
                        </tr>
                        <?php
                        while ($row = $result->fetchRow()) {
                        ?>
                        <tr>
                            <td>
                                <a href="admingroupcontentdeletesubmit.php?group_id=<?php echo $_GET['group_id'];?>&member_id=<?php echo $row['member_id']; ?>&localpart=<?php echo $grouplocalpart;?>">
                                    <img title="Remove member <?php echo $row['realname']. ' from group ' . $grouplocalpart; ?>" src="images/trashcan.gif" alt="trashcan" />
                                </a>
                            </td>
                            <td><?php echo $row['realname']; ?></td>
                            <td><?php echo $row['username']; ?></td>
                            <td>
                                <?php
                                if($row['enabled']='1') {
                                ?>
                                <img src="images/check.gif" />
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                        }#while
                        ?>
                <?php
                } else {
                echo '<tr><td>';
                print _('There are no members in this group');
                }
                ?>
                </td>
            </tr>
            </table>

                    <form method="post" action="admingroupcontentaddsubmit.php" name="groupcontentadd">
                    <table>
                    <tr>
                        <td><?php echo _('Add Member'); ?></td>
                        <td>
                            <input name="group_id" type="hidden" value="<?php echo $_GET['group_id']; ?>" />
                            <input name="localpart" type="hidden" value="<?php echo $grouplocalpart; ?>" />
                            <select name="usertoadd">
                                <option selected value=""></option>
                                <?php
                                $query = "select realname, username, user_id from users
                                where enabled = '1' and domain_id = '{$_SESSION['domain_id']}'
                                and (type = 'local'
                                or (type = 'alias' and smtp like '%@{$_SESSION['domain']}'))
                                order by realname, username, type desc";
                                $result = $db->query($query);
                                while ($row = $result->fetchRow()) {
                                ?>
                                <option value="<?php echo $row['user_id']; 
                                ?>"><?php echo $row['realname']; 
                                ?>(<?php echo $row['username']; ?>)</option>
                                <?php 
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input class="btn" name="addmember" type="submit" value="<?php echo _('Submit'); ?>" />
                        </td>
                    </tr>
                </table>
            </form>
            <?php 		
                # end of the block editing a group within the domain
                }  
            ?>		  
        </div>
    </body>
</html>