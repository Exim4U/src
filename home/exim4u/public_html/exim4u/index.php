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
          <td><?php echo _('Username'); ?>:</td>
          <td><input name="username" type="text" class="loginfield">
            <?php
            if($domainguess===1) echo '@'.preg_replace ("/^mail\./", "", $_SERVER["SERVER_NAME"]);
            ?>
            </td>
          </tr>
          <tr>
            <td><?php echo _("Password"); ?>:</td>
            <td><input name="crypt" type="password" class="loginfield"></td>
          </tr>
          <tr>
            <td colspan="2" style="text-align:center;padding-top:1em">
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
