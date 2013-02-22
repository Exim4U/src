<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';

  $query = "SELECT * FROM users WHERE user_id='{$_GET['user_id']}'
		AND domain_id='{$_SESSION['domain_id']}'
		AND (type='local' OR type='piped')";
  $result = $db->query($query);
  if ($result->numRows()) { $row = $result->fetchRow(); }
  
  $username = $row[username];
  $domquery = "SELECT spamassassin,quotas,pipe FROM domains
    WHERE domain_id='{$_SESSION['domain_id']}'";
  $domresult = $db->query($domquery);
  if ($domresult->numRows()) {
    $domrow = $domresult->fetchRow();
  }
  $blockquery = "SELECT blockhdr,blockval,block_id FROM blocklists
    WHERE blocklists.user_id='{$_GET['user_id']}'";
  $blockresult = $db->query($blockquery);
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
    <body onLoad="document.userchange.realname.focus()">
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

            <?php 
            	# ensure this page can only be used to view/edit user accounts that already exist for the domain of the admin account
            	if (!$result->numRows()) {			
            		echo '<table><tr><td>';
            		echo "Invalid userid '" . htmlentities($_GET['user_id']) . "' for domain '" . htmlentities($_SESSION['domain']). "'";			
            		echo '</td></tr></table>';
            	}else{	
            ?>

            <form name="userchange" method="post" action="adminuserchangesubmit.php">	
                <table>
                    <tr>
                        <td><?php echo _('Name'); ?>:</td>
                        <td><input type="text" size="25" name="realname" value="<?php print $row['realname']; ?>" /></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Email Address'); ?>:</td>
                        <td>
                            <?php print $row['username']; ?>
                            <input name="user_id" type="hidden" value="<?php print $_GET['user_id']; ?>" />
                        </td>
                    </tr>
                    
                    <tr>
                        <td><?php echo _('Password'); ?>:</td>
                        <td>
                        <input type="password" size="25" name="clear" />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Verify Password'); ?>:</td>
                        <td>
                        <input type="password" size="25" name="vclear" />
                        </td>
                    </tr>
                    <?php
                    if ($postmasteruidgid == "yes") { ?>
                    <tr>
                        <td><?php echo _('UID'); ?>:</td>
                        <td>
                        <input type="text" size="25" name="uid" value="<?php echo $row['uid']; ?>" /></td>
                    </tr>
                    <tr>
                        <td><?php echo _('GID'); ?>:</td>
                        <td><input type="text" size="25" name="gid" value="<?php echo $row['gid']; ?>" /></td>
                    </tr> 
                    <tr>
                        <td colspan="2">
                            <?php echo _('When you update the UID or GID, please make sure your MTA still has permission to create the required user directories!'); ?>
                        </td>
                    </tr>
                    <?php
                    }
                    if ($domrow['quotas'] > "0") {
                    ?>
                    <tr>
                        <td>
                        <?php printf (_('Mailbox quota (%s Mb max)'),
                        $domrow['quotas']); ?>
                        :</td>
                        <td>
                            <div class="input-append">
                                <input type="text" size="5" name="quota" value="<?php echo $row['quota']; ?>"><span class="add-on">Mb</span>
                            </div>
                        </td>
                    </tr>
                    <?php
                        }
                        if ((function_exists(imap_get_quotaroot))
                        && ($imap_to_check_quota == "yes")) {
                            $mbox = imap_open(
                            $imapquotaserver, $row['username'], $row['clear'], OP_HALFOPEN
                            );
                            $quota = imap_get_quotaroot($mbox, "INBOX");
                            if (is_array($quota)) {
                                printf ("<tr><td>"
                                . _('Space used:')
                                . "</td><td>%.2f MB</td></tr>",
                                $quota['STORAGE']['usage'] / 1024);
                            }
                            imap_close($mbox);
                            print "</tr>";
                        }
                        if ($domrow['pipe'] == "1") {
                    ?>
                    <tr>
                        <td><?php echo _('Pipe To Command Or Alternative Maildir'); ?>:</td>
                        <td><input type="text" size="25" name="smtp" value="<?php echo $row['smtp']; ?>" /></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php echo _('Optional'); ?>: 
                            <?php echo _('Pipe all mail to a command (e.g. procmail).'); ?> 
                            <?php echo _('Check box below to enable'); ?>:
                        </td>
                    </tr>
                    <tr>
                        <td><?php _('Enable piped command or alternative Maildir?'); ?></td>
                        <td><input type="checkbox" name="on_piped"
                        <?php
                            if ($row['on_piped'] == "1") {
                                print " checked ";
                            } 
                        ?> />
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td><?php echo _('Admin'); ?>:</td>
                        <td>
                            <input name="admin" type="checkbox"
                            <?php 
                            if ($row['admin'] == 1) { 
                                print " checked";
                            } 
                            ?> />
                        </td>
                    </tr>
                    <?php
                    if ($domrow['spamassassin'] == "1") {
                    ?>
                    <tr>
                        <td><?php echo _('Spamassassin'); ?>:</td>
                        <td><input name="on_spamassassin" type="checkbox"
                            <?php 
                            if ($row['on_spamassassin'] == "1") {
                                print " checked";
                            }
                            ?> />
                        </td>
                    </tr>
                    <tr>
                    <tr>
                        <td><?php echo _('Enable Spam Box'); ?>:</td>
                        <td><input name="on_spambox" type="checkbox"
                            <?php
                            if ($row['on_spambox'] == "1") {
                                print " checked";
                            }
                            ?> />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Enable Spam Box Report'); ?>:</td>
                        <td><input name="on_spamboxreport" type="checkbox"
                            <?php 
                            if ($row['on_spamboxreport'] == "1") {
                            print " checked";
                            }
                            ?> />
                        </td>      
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin Tag Score'); ?>:</td>
                        <td><input type="text" size="5" name="sa_tag" value="<?php echo $row['sa_tag']; ?>" /></td>
                    </tr>
                    <tr>
                        <td><?php echo _('Spamassassin Discard Score'); ?>:</td>
                        <td><input type="text" size="5" name="sa_refuse" value="<?php echo $row['sa_refuse']; ?>" /></td> 
                    </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <td><?php echo _('Maximum Message Size'); ?>:</td>
                        <td>
                            <div class="input-append">
                                <input type="text" size="5" name="maxmsgsize" value="<?php echo $row['maxmsgsize']; ?>" /><span class="add-on">Kb</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Enabled'); ?>:</td>
                        <td><input name="enabled" type="checkbox" <?php
                        if ($row['enabled'] == 1) {
                        print "checked";
                        } ?> />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Vacation On'); ?>:</td>
                        <td><input name="on_vacation" type="checkbox" <?php
                        if ($row['on_vacation'] == "1") {
                        print " checked ";
                        } ?> />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Vacation Message'); ?>:</td>
                        <td>
                            <textarea name="vacation" cols="40" rows="5"><?php print $row['vacation']; ?></textarea>
                        </td>
                    <tr>
                        <td><?php echo _('Forwarding Enabled'); ?>:</td>
                        <td><input name="on_forward" type="checkbox" <?php
                        if ($row['on_forward'] == "1") {
                        print " checked";
                        } ?> />
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Forward Mail To'); ?>:</td>
                        <td>
                            <input type="text" size="25" name="forward" value="<?php print $row['forward']; ?>" /><br>
                            <? echo _('Must be a full e-mail address'); ?>!<br>
                            <? echo _('OR') .":<br>\n"; ?>
                            <select name="forwardmenu">
                                <option selected value=""></option>
                                <?php
                                $queryuserlist = "SELECT realname, username, user_id, unseen
                                FROM users
                                WHERE enabled = '1' AND domain_id = '{$_SESSION['domain_id']}'
                                AND type != 'fail' ORDER BY realname, username, type desc";
                                $resultuserlist = $db->query($queryuserlist);
                                while ($rowuserlist = $resultuserlist->fetchRow()) {
                                ?>
                                <option value="<?php echo $rowuserlist['username']; ?>">
                                <?php echo $rowuserlist['realname']; ?>
                                (<?php echo $rowuserlist['username']; ?>)
                                </option>
                                <?php 
                                }
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Store Forwarded Mail Locally'); ?>:</td>
                        <td>
                            <input name="unseen" type="checkbox" <?php
                            if ($row['unseen'] == "1") {
                            print " checked ";
                            } ?> />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input name="user_id" type="hidden" value="<?php print $_GET['user_id']; ?>" />
                            <input name="localpart" type="hidden" value="<?php print $row['localpart']; ?>" />
                            <input class="btn" name="submit" type="submit" value="Submit">
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <?php echo _('Aliases To This Account'); ?>:<br>
                            <?php
                            # Print the aliases associated with this account
                            $query = "SELECT user_id,localpart,domain,realname FROM users,domains
                            WHERE smtp='{$row['localpart']}@{$_SESSION['domain']}'
                            AND users.domain_id=domains.domain_id ORDER BY realname";
                            $result = $db->query($query);
                            if ($result->numRows()) {
                            while ($row = $result->fetchRow()) {
                            if (($row['domain'] == $_SESSION['domain'])
                            && ($row['localpart'] != "*")) {
                            print '<a href="adminaliaschange.php?user_id='
                            . $row['user_id']
                            . '">'
                            . $row['localpart']. '@' . $row['domain']
                            . '</a>';
                            } else if (($row['domain'] == $_SESSION['domain'])
                            && ($row['localpart'] == "*")) {
                            print '<a href="admincatchall.php?user_id='
                            . $row['user_id']
                            . '">'
                            . $row['localpart'] . '@' . $row['domain']
                            . '</a>';
                            } else {
                            print $row['localpart'] . '@' . $row['domain'];
                            }
                            if ($row['realname'] == "Catchall") {
                            print $row[realname];
                            }
                            print '<br>';
                            }
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </form>
            
            <form name="blocklist" method="post" action="adminuserblocksubmit.php">            
                <table>
                    <tr>
                        <td colspan="4">
                            <?php
                            echo _('Add A New Header Blocking Filter For This User');
                            ?>:
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo _('Header'); ?>:</td>
                        <td>
                            <select name="blockhdr">
                            <option value="From"><?php echo _('From'); ?>:</option>
                            <option value="To"><?php echo _('To'); ?>:</option>
                            <option value="Subject"><?php echo _('Subject'); ?>:</option>
                            <option value="X-Mailer"><?php echo _('X-Mailer'); ?>:</option>
                            </select>
                        </td>
                        <td>
                            <input name="blockval" type="text" size="25">
                            <input name="user_id" type="hidden"
                            value="<?php print $_GET['user_id']; ?>">
                            <input name="localpart" type="hidden"
                            value="<?php print $_GET['localpart']; ?>">
                            <input name="color" type="hidden" value="black">
                        </td>
                        <td>
                            <input class="btn" name="submit" type="submit" value="<?php echo _('Submit'); ?>">
                        </td>
                    </tr>
                </table>
            </form>
            
            <table>
                <tr>
                    <td><?php echo _('Blocked'); ?></td>
                    <td><?php echo _('Headers Listed Below'); ?></td>
                    <td><?php echo _('(mail will be deleted):'); ?></td>
                </tr>
                <?php
                if ($blockresult->numRows()) {
                    while ($blockrow = $blockresult->fetchRow()) {
                ?>
                        <tr>
                            <td>
                                <a href="adminuserblocksubmit.php?action=delete&user_id=<?php print $_GET['user_id']. '&block_id='. $blockrow['block_id'].'&localpart='. $_GET['localpart'];?>">
                                    <img title="Delete" src="images/trashcan.gif" alt="trashcan">
                                </a>
                            </td>
                            <td><?php echo $blockrow['blockhdr']; ?></td>
                            <td><?php echo $blockrow['blockval']; ?></td>
                        </tr>
                <?php
                    }
                }
                ?>
            </table>
            <?php 		
            # end of the block editing an alias within the domain
            }  
            ?>
	   </div>
    </body>
</html>