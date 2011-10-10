<?php
  require_once dirname(__FILE__) . '/config/variables.php';
  require_once dirname(__FILE__) . '/config/functions.php';
  require_once dirname(__FILE__) . '/config/httpheaders.php';
?>
<html>
  <head>
    <title><?php echo _('Exim4U'); ?></title>
    <link rel="stylesheet" href="style.css" type="text/css">
  </head>
  <body onLoad="document.login.localpart.focus()">
    <?php include dirname(__FILE__) . '/config/header.php'; ?>
    <div id="Centered">
      <form style="margin-top:3em;" name="login" method="post" action="login.php">
        <table align="center">
          <tr>
            <td><?php echo _('Username'); ?>:<td><input name="localpart" type="text" class="textfield">&nbsp;@&nbsp;</td>
            <td>
              <?php
                $domain = preg_replace ("/^mail\./", "", $_SERVER["SERVER_NAME"]);
                if ($domaininput == 'dropdown') {
                  $query = "SELECT domain FROM domains WHERE type='local' AND domain!='admin' ORDER BY domain";
                  $result = $db->query($query);
              ?>
                  <select name="domain" class="textfield">
                  <option value=''>
              <?php
                    if ($result->numRows()) {
                      while ($row = $result->fetchRow()) {
                        print "<option value='{$row['domain']}'>{$row['domain']}"
                        . '</option>';
                      }
                    }
                  print '</select>';
                } else if ($domaininput == 'textbox') {
                  print '<input type="text" name="domain" class="textfield"> (Domain)';
                } else if ($domaininput == 'static') {
                  print $domain
                    . '<input type="hidden" name="domain" value='
                    . $domain
                    . '>';
                }
              ?>
            </td>
          </tr>
          <tr>
            <td><?php echo _("Password"); ?>:</td>
            <td><input name="crypt" type="password" class="textfield"></td>
          </tr>
          <tr>
            <td colspan="3" style="text-align:center;padding-top:1em">
              <input name="submit" type="submit"
                value="<?php echo _("Submit"); ?>" class="longbutton">
            </td>
          </tr>
        </table>
      </form>
    </div>
  </body>


<body style="margin: 0px;">
 <div align="center">
  <table border="0" cellspacing="0" cellpadding="0">
   <tr>
    <td><TABLE cellSpacing=0 cellPadding=0 width=800 align=center>
<TBODY>
<TR>
<TD style="BORDER-RIGHT: black 0px solid; BORDER-TOP: black 0px solid; BORDER-LEFT: black 0px
 solid; BORDER-BOTTOM: black 0px solid">
</TD></TR></TBODY></TABLE>
     <table border="0" cellspacing="0" cellpadding="0" width="446">
      <tr valign="top" align="left">
       <td width="357" height="255"><img src="./images/clearpixel.gif" width="357" height="1" border="0" alt=""></td>
       <td></td>
      </tr>
      <tr valign="top" align="left">
       <td height="52"></td>
       <td width="88"><img id="Exim4U" height="52" width="88" src="./images/logo.gif" border="0" alt="Exim4U" title="Exim4U"></td>
      </tr>
     </table>
    </td>
   </tr>
  </table>
 </div>
</body>

</html>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
