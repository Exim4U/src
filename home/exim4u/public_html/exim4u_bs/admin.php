<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authpostmaster.php';
  include_once dirname(__FILE__) . '/config/functions.php';
  include_once dirname(__FILE__) . '/config/httpheaders.php';
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U'); ?></title>
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
                        <li><a href="adminuser.php"><?php echo _('POP/IMAP Accounts');?></a></li>
                        <li><a href="adminalias.php"><?php echo _('Aliases, Forwards And Catchalls');?></a></li>
                        <li><a href="adminfail.php"><?php echo _('Addresses To Fail'); ?></a></li>
                        <li class="dropdown"><a href="#" id="groups" role="button" class="dropdown-toggle" data-toggle="dropdown"><?php echo _('Groups'); ?> <b class="caret"></b></a>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="groups">
                                <li><a href="admingroup.php"><?php echo _('Groups'); ?></a></li>
                                <li><a href="admingroupnew.php"><?php echo _('Simple Mailing Lists'); ?></a></li>
                                <?php
                                    if ($mailmaninstalled != "no") {
                                        print '<li><a href="adminlists.php">' . _('Mailman Mailing Lists') . '</a></li>';
                                    }
                                ?>
                            </ul>
                        </li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>
    <?php
        $query = "SELECT alias,domain FROM domainalias,domains 
        WHERE domainalias.domain_id = {$_SESSION['domain_id']}
        AND domains.domain_id = domainalias.domain_id";
        $result = $db->query($query);
        if ($result->numRows()) {
            print '<table><tr><th>Domain data:</th></tr>';
            while ($row = $result->fetchRow()) {
                print '<tr><td>';
                print "{$row['alias']} is an alias of {$_SESSION['domain']}";
                print '</td></tr>';
            }
        print '</table>';
        }
    ?>

        </div>
    </body>
</html>