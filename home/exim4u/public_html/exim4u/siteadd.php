<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authsite.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
?>
<html>
  <head>
    <title><?php echo _('Exim4U') . ': ' . _('Manage Domains'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body onLoad="document.siteadd.domain.focus()">
    <?php include dirname(__FILE__) . '/config/header_domain.php'; ?>
    <div id='menu'>
      <a href="site.php"><?php echo _('Manage Domains'); ?></a><br>
      <a href="sitepassword.php"><?php echo _('Site Password'); ?></a><br>
      <br><a href="logout.php"><?php echo _('Logout'); ?></a><br>
    </div>
    <div id="forms">
      <form name="siteadd" method="post" action="siteaddsubmit.php">
        <table align="center">
          <tr>
    <td><?php echo _('New Domain'); ?>:</td>
          <td><input name="domain" type="text" class="textfield"></td>
    <td>
      <?php echo _('Domain that you are adding.'); ?>
            </td>
          </tr>
          <?php
            if ($_GET['type'] == "local") {
          ?>
          <tr>
            <td><?php echo _('Domain Admin'); ?>:</td>
            <td>
              <input name="localpart" type="text" value="postmaster"
                class="textfield">
            </td>
            <td>
              <?php
                echo _('Username of domain\'s administrator');
              ?>
            </td>
          </tr>
          <tr>
            <td><?php echo _('Password'); ?>:</td>
            <td>
              <input name="clear" type="password" class="textfield">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Verify Password'); ?>:</td>
            <td>
              <input name="vclear" type="password" class="textfield">
            </td>
          </tr>
      <?php if ($multi_ip == "yes") {      ?>
        <tr>
            <td><?php echo _('Outgoing IP'); ?>:</td>
            <td>
              <input name="outgoing_ip" size="12" type="text" class="textfield"
                value="<?php echo $outgoing_IP; ?>">
            </td>
          </tr>
      <?php } ?>
          <tr>
            <td><?php echo _('System UID'); ?>:</td>
            <td>
              <input name="uid" type="text" class="textfield"
                value="<?php echo $uid; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo _('System GID'); ?>:</td>
            <td>
              <input name="gid" type="text" class="textfield"
                value="<?php echo $gid; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Domain Mail Directory'); ?>:</td>
            <td>
              <input name="maildir" type="text" class="textfield"
                value="<?php echo $mailroot; ?>">
            </td>
            <td>
              <?php
                echo _('Create the domain directory below this top-level
                  mailstore');
              ?>
            </td>
          </tr>
          <tr>
            <td>
              <?php echo _('Maximum Accounts'); ?><br>
              (<?php echo _("0 for unlimited"); ?>):
            </td>
            <td>
              <input type="text" size="5" name="max_accounts" value="0"
                class="textfield">
            </td>
          </tr>
          <tr>
            <td>
              <?php echo _('Max Mailbox Quota'); ?>
              (<?php echo _('0 for disabled'); ?>):
            </td>
            <td>
              <input name="quotas" size="5" type="text" class="textfield"
                value="0"><?php echo _('Mb'); ?>
            </td>
          </tr>
          <tr>
            <td><?php echo _('Maximum Message Size'); ?>:</td>
            <td>
              <input name="maxmsgsize" size="5" type="text" class="textfield"
                value="0"><?php echo _('Kb'); ?>
            </td>
            <td>
              <?php echo _('The maximum size for incoming mail (user
                tunable)'); ?>
            </td>
          </tr>
          <tr>
            <td><?php echo _('Spamassassin Tag Score'); ?>:</td>
            <td>
              <input name="sa_tag" size="5" type="text" class="textfield"
                value="<?php echo $sa_tag; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Spamassassin Discard Score'); ?>:</td>
            <td>
              <input name="sa_refuse" size="5" type="text" class="textfield"
              value="<?php echo $sa_refuse; ?>">
          </tr>
          <tr>
            <td><?php echo _('Spamassassin enabled?'); ?></td>
            <td>
              <input name="spamassassin" type="checkbox" checked class="textfield">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Enable piping mail to command?'); ?></td>
            <td>
              <input name="pipe" type="checkbox" class="textfield">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Domain enabled?'); ?></td>
            <td>
              <input name="enabled" type="checkbox" class="textfield" checked>
            </td>
          </tr>
          <tr><td></td></tr>
        <?php
           } else if ($_GET['type'] == "alias") {
        ?>
          <tr>
            <td><?php echo _('Redirect Messages To Domain'); ?>:</td>
            <td>
              <input name="aliasdest" type="text" class="textfield">
            </td>
          </tr>
        <?php
/* GLD MOD to get relay server address along with spamassassin options */
           } else if ($_GET['type'] == "relay") {
        ?>
          <tr>
            <td><?php echo _('Relay Server'); ?>:</td>
            <td>
              <input name="relay_address" type="text" size="22" class="textfield">
            </td>
            <td>
            <?php echo _('Destination mail server name.'); ?>
                </td>
          </tr>
          <tr>
            <td><?php echo _('Spamassassin Tag Score'); ?>:</td>
            <td>
              <input name="sa_tag" size="5" type="text" class="textfield"
                value="<?php echo $sa_tag; ?>">
            </td>
          </tr>
          <tr>
            <td><?php echo _('Spamassassin Discard Score'); ?>:</td>
            <td>
              <input name="sa_refuse" size="5" type="text" class="textfield"
              value="<?php echo $sa_refuse; ?>">
        </tr>
            <tr><td><?php echo _('Spamassassin enabled?'); ?></td>
            <td>
              <input name="spamassassin" type="checkbox" checked class="textfield">
            </td></tr>
          </tr>
        <?php
           }
/* GLD MOD end */
        ?>
          <tr>
            <td>
            </td>
            <td>
              <input name="type" type="hidden"
                value="<?php print $_GET['type']; ?>">
              <input name="admin" type="hidden" value="1">
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
<!-- This module was modified extensively by GLD to accomodate relay server address and relay spamassassin parameters -->
