<?php
  include_once dirname(__FILE__) . '/config/variables.php';
  include_once dirname(__FILE__) . '/config/authsite.php';
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
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title><?php echo _('Exim4U') . ': ' . _('Manage Sites'); ?></title>
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
            <?php include dirname(__FILE__) . '/config/header_domain.php'; ?>
    
            <div class="navbar">
                <div class="navbar-inner">
                    <ul id="menu" class="nav">
                        <li><a href="siteadd.php?type=alias"><?php echo _('Add Alias Domain'); ?></a></li>
                        <li><a href="siteadd.php?type=local"><?php echo _('Add Local Domain'); ?></a></li>
                        <li><a href="siteadd.php?type=relay"><?php echo _('Add Relay Domain'); ?></a></li>
                        <li><a href='sitepassword.php'><?php echo _('Site Password'); ?></a></li>
                        <li><a href="logout.php"><?php echo _('Logout'); ?></a></li>
                    </ul>
                </div>
            </div>
            
            <ul class="nav nav-tabs">
              <li class="active"><a href="#local" data-toggle="tab"><?php echo _('Local Domains'); ?></a></li>
              <li><a href="#relay" data-toggle="tab"><?php echo _('Relay Domains'); ?></a></li>
              <li><a href="#alias" data-toggle="tab"><?php echo _('Alias Domains'); ?></a></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="local">

                    <?php
                      alpha_menu($alphadomains);
                    ?>
            
                    <form name="search" method="post" action="site.php">
                        <div class="input-prepend input-append">
                        <span class="add-on"><?php
                        echo _('Search');
                        ?></span>
                            <input type="text" size="20" name="searchfor" value="<?php echo $_POST['searchfor']; ?>" />
                            <input class="btn" type="submit" name="search" value="<?php echo _('search'); ?>" />
                        </div>
                    </form>

                    <table>
                    <tr>
                        <th></th>
                        <th><?php echo _('Local Domains'); ?></th>
                        <th><?php echo _('Admin Account'); ?></th>
                        <th><?php echo _('Total Admins'); ?></th>
                    </tr>
                    <?php
                        $query = "SELECT MIN(localpart) AS localpart, domain, domains.domain_id, count(*) AS count FROM   users, domains WHERE  users.domain_id = domains.domain_id AND domain !='admin' AND admin=1";
                        if ($alphadomains AND $letter != '') 
                            $query .= " AND lower(domain) LIKE lower('$letter%')";
                        elseif ($_POST['searchfor'] != '')
                            $query .= " AND domain LIKE '%" . $_POST['searchfor'] . "%' ";
                            $query .= " GROUP BY domains.domain, domains.domain_id ORDER BY domain";
                            $result = $db->query($query);
                        if ($result->numRows()) {
                            while ($row = $result->fetchRow()) {
                                ?>
                                    <tr>
                                      <td>
                                        <a href="sitedelete.php?domain_id=<?php
                                          echo $row['domain_id']; ?>&domain=<?php
                                          echo $row['domain']; ?>">
                                          <img class="trash" title="Delete <?php $row['domain']; ?>"
                                            src="images/trashcan.gif" alt="trashcan">
                                        </a>
                                      </td>
                                      <td>
                                        <a href="sitechange.php?domain_id=<?php
                                          echo $row['domain_id']; ?>&domain=<?php
                                          echo $row['domain']; ?>"><?php echo $row['domain']; ?></a>
                                      </td>
                                  <?php
                                if ($row['count'] == 1) {
                              ?>
                                  <td><?php echo $row['localpart'] . '@' . $row['domain']; ?></td>
                              <?php
                                } else {
                              ?>
                                  <td><?php echo _('Multiple admins'); ?></td>
                              <?php
                                }
                              ?>
                        <td><?php echo $row['count']; ?></td>
                       </tr>
                      <?php
                      }
                    }
                  ?>
                  </table>

                  <div class="text-warning">
                      <?php echo _('WARNING') ?>: 
                      <?php
                        echo _('Deleting a domain will delete all user accounts in that
                          domain permanently!');
                      ?>
                  </div>

              </div><!-- #local -->

              <div class="tab-pane" id="relay">    
                  <table>
                    <tr>
                        <th></th>
                        <th><?php echo _('Relay Domains'); ?></th>
                    </tr>
                  <?php
                    $query = "SELECT domain,domain_id FROM domains
                    WHERE domain !='admin'
                    AND type='relay' ORDER BY domain";
                    $result = $db->query($query);
                    if ($result->numRows()) {
                      while ($row = $result->fetchRow()) {
                  ?>
                        <tr>
                          <td>
                            <a href="sitedelete.php?domain_id=<?php
                              echo $row['domain_id']; ?>&domain=<?php
                              echo $row['domain']; ?>&type=relay">
                              <img class="trash" title="<?php echo _('Delete') .
                                $row['domain']; ?>" src="images/trashcan.gif" alt="trashcan">
                            </a>
                          </td>
                          <td>
                        <a href="sitechangerelay.php?domain_id=<?php
                          echo $row['domain_id']; ?>&domain=<?php
                              echo $row['domain']; ?>"><?php echo $row['domain']; ?></a>
                       </td>
                        </tr>
                  <?php
                      }
                    }
                  ?>
                  </table>
              </div><!-- #relay -->

              <div class="tab-pane" id="alias">
                  <table>
                  <tr>
                    <th></th>
                    <th><?php echo _('Aliased Domains'); ?></th>
                  </tr>
                  <?php
                    $query = "SELECT alias,domain FROM domainalias,domains
                      WHERE domainalias.domain_id = domains.domain_id";
                    $result = $db->query($query);
                    if ($result->numRows()) {
                      while ($row = $result->fetchRow()) {
                  ?>
                        <tr>
                          <td>
                            <a href="sitedelete.php?domain_id=<?php
                              echo $row['domain_id']; ?>&domain=<?php
                              echo $row['alias']; ?>&type=alias">
                              <img class="trash" title="<?php echo _('Delete')
                                . $row['alias']; ?>" src="images/trashcan.gif" alt="trashcan">
                            </a>
                          </td>
                        <td>
                          <?php echo $row['alias'] . ' &raquo; ' . $row['domain']; ?>
                        </td>
                      </tr>
                  <?php
                      }
                    }
            		
            		echo '</table>';
        		echo '</div><!-- #alias -->';		

    		# display status of $AllowUserLogin
    		if($AllowUserLogin){
    		    echo '<div class="text-success">Standard user accounts are currently able to login and change their own personal details.</div>';
    		}else{
    			echo '<div class="text-warning">The system is currently configured to prevent standard users logging in to change their own personal details.</div>';
    		}
          ?>
        </div>
  </body>
</html>