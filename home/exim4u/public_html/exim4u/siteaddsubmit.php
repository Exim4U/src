<?php
  include_once dirname(__FILE__) . "/config/variables.php";
  include_once dirname(__FILE__) . "/config/authsite.php";
  include_once dirname(__FILE__) . "/config/functions.php";
  include_once dirname(__FILE__) . "/config/httpheaders.php";

  if (isset($_POST['spamassassin'])) {
    $_POST['spamassassin'] = 1;
  } else {
    $_POST['spamassassin'] = 0;
  }
  if (isset($_POST['enabled'])) {
    $_POST['enabled'] = 1;
  } else {
    $_POST['enabled'] = 0;
  }
  if (isset($_POST['pipe'])) {
    $_POST['pipe'] = 1;
  } else {
    $_POST['pipe'] = 0;
  }
  if ($_POST['type'] == "relay") {
    $_POST['clear'] = $_POST['vclear'] = "BLANK";
  }
  if ($_POST['type'] == "alias") {
    $_POST['clear'] = $_POST['vclear'] = "BLANK";
  }
  if (!isset($_POST['max_accounts']) || $_POST['max_accounts'] == '') {
    $_POST['max_accounts'] = '0';
  }

  if ($_POST['type'] == "local") {
    $_POST['relay_address'] = "";
  }
  if ($_POST['type'] == "relay" && ($multi_ip == "yes")) {
    $_POST['outgoing_ip'] = "";
  }

  if (preg_match("/^\s*$/",$_POST['domain'])) {
    header("Location: siteadd.php?type={$_POST['type']}&blankdomname=yes");
    die;
  }
  if ((preg_match("/^\s*$/",$_POST['relay_address'])) && ($_POST['type'] == "relay")) {
    header("Location: siteadd.php?type={$_POST['type']}&blankrelayadd=yes");
    die;
  }

// User can specify either UID, or username, the former being preferred.
// Using posix_getpwuid/posix_getgrgid even when we have an UID is so we
// are sure the UID exists.
  if (isset ($_POST['uid'])) {
    $uid = $_POST['uid'];
  }
  if (isset ($_POST['gid'])) {
    $gid = $_POST['gid'];
  }

  if ($userinfo = @posix_getpwuid ($uid)) {
    $uid = $userinfo['uid'];
  } elseif ($userinfo = @posix_getpwnam ($uid)) {
    $uid = $userinfo['uid'];
  } else {
    header ("Location: siteadd.php?type={$_POST['type']}&failuidguid={$_POST['domain']}");
    die;
  }
  
  if ($groupinfo = @posix_getgrgid ($gid)) {
    $gid = $groupinfo['gid'];
  } elseif ($groupinfo = @posix_getgrnam ($gid)) {
    $gid = $groupinfo['gid'];
  } else {
    header ("Location: siteadd.php?type={$_POST['type']}&failuidguid={$_POST['domain']}");
    die;
  }
        
  if(isset($_POST['maildir']) && isset($_POST['localpart'])) {
     if (substr($_POST['maildir'], 0, 1) !== '/') {
       header ("Location: siteadd.php?type={$_POST['type']}&failmaildirnonabsolute={$_POST['maildir']}");
 	  die();
     }
     if ($testmailroot && is_dir(realpath($_POST['maildir'])) === false) {
       header ("Location: siteadd.php?type={$_POST['type']}&failmaildirmissing={$_POST['maildir']}");
       die();
 	}
     $domainpath = $_POST['maildir'];
     if (substr($domainpath, -1) !== '/') {
       $domainpath .= '/';
     }
     $domainpath .= $_POST['domain'];
     $smtphomepath = $domainpath . "/" . $_POST['localpart'] . "/Maildir";
     $pophomepath = $domainpath . "/" . $_POST['localpart'];
    }
//Gah. Transactions!! -- GCBirzan
if ((validate_password($_POST['clear'], $_POST['vclear'])) &&
    ($_POST['type'] != "alias")) {

  if ($multi_ip == "yes") {
    $query = "INSERT INTO domains 
              (domain, spamassassin, sa_tag, sa_refuse,
              max_accounts, quotas, maildir, pipe, enabled,
              uid, gid, type, maxmsgsize, relay_address, outgoing_ip)
              VALUES (:domain, :spamassassin, :sa_tag, :sa_refuse,
              :max_accounts, :quotas, :maildir, :pipe, :enabled,
              :uid, :gid, :type, :maxmsgsize, :relay_address, :outgoing_ip)";
    $sth = $dbh->prepare($query);
    $success = $sth->execute(array(':domain'=>$_POST['domain'],
              ':spamassassin'=>$_POST['spamassassin'],
              ':sa_tag'=>((isset($_POST['sa_tag'])) ? $_POST['sa_tag'] : 0),
              ':sa_refuse'=>((isset($_POST['sa_refuse'])) ? $_POST['sa_refuse'] : 0),
              ':max_accounts'=>$_POST['max_accounts'],
              ':quotas'=>((isset($_POST['quotas'])) ? $_POST['quotas'] : 0),
              ':maildir'=>((isset($_POST['maildir'])) ? $domainpath : ''),
              ':pipe'=>$_POST['pipe'], ':enabled'=>$_POST['enabled'],
              ':uid'=>$uid, ':gid'=>$gid, ':type'=>$_POST['type'],
              ':maxmsgsize'=>((isset($_POST['maxmsgsize'])) ? $_POST['maxmsgsize'] : 0),
              ':relay_address'=>$_POST['relay_address'],
              ':outgoing_ip'=>$_POST['outgoing_ip']
              ));
  } else {
    $query = "INSERT INTO domains 
              (domain, spamassassin, sa_tag, sa_refuse,
              max_accounts, quotas, maildir, pipe, enabled,
              uid, gid, type, maxmsgsize, relay_address)
              VALUES (:domain, :spamassassin, :sa_tag, :sa_refuse,
              :max_accounts, :quotas, :maildir, :pipe, :enabled,
              :uid, :gid, :type, :maxmsgsize, :relay_address)";
    $sth = $dbh->prepare($query);
    $success = $sth->execute(array(':domain'=>$_POST['domain'],
              ':spamassassin'=>$_POST['spamassassin'],
              ':sa_tag'=>((isset($_POST['sa_tag'])) ? $_POST['sa_tag'] : 0),
              ':sa_refuse'=>((isset($_POST['sa_refuse'])) ? $_POST['sa_refuse'] : 0),
              ':max_accounts'=>$_POST['max_accounts'],
              ':quotas'=>((isset($_POST['quotas'])) ? $_POST['quotas'] : 0),
              ':maildir'=>((isset($_POST['maildir'])) ? $domainpath : ''),
              ':pipe'=>$_POST['pipe'], ':enabled'=>$_POST['enabled'],
              ':uid'=>$uid, ':gid'=>$gid, ':type'=>$_POST['type'],
              ':maxmsgsize'=>((isset($_POST['maxmsgsize'])) ? $_POST['maxmsgsize'] : 0),
              ':relay_address'=>$_POST['relay_address']
              ));
  }
    if ($success) {
      if ($_POST['type'] == "local") {
        $query = "INSERT INTO users
             (domain_id, localpart, username, crypt, uid, gid,
             on_spamassassin, sa_tag, sa_refuse, quota, maxmsgsize,
             smtp, pop, realname, type, admin)
             SELECT domain_id, :localpart, :username, :crypt,:uid, :gid,
             :on_spamassassin, :sa_tag, :sa_refuse, :quota, :maxmsgsize,
             :smtp, :pop, 'Domain Admin', 'local', 1
             FROM domains
             WHERE domains.domain=:domain";
             $sth = $dbh->prepare($query);
             $success = $sth->execute(array(':localpart'=>$_POST['localpart'],
             ':username'=>$_POST['localpart'].'@'.$_POST['domain'],
             ':crypt'=>crypt_password($_POST['clear']),
             ':uid'=>$uid, ':gid'=>$gid,
             ':on_spamassassin'=>$_POST['spamassassin'],
             ':sa_tag'=>((isset($_POST['sa_tag'])) ? $_POST['sa_tag'] : 0),
             ':sa_refuse'=>((isset($_POST['sa_refuse'])) ? $_POST['sa_refuse'] : 0),
             ':quota'=>((isset($_POST['quotas'])) ? $_POST['quotas'] : 0),
             ':maxmsgsize'=>((isset($_POST['maxmsgsize'])) ? $_POST['maxmsgsize'] : 0),
             ':smtp'=>$smtphomepath,
             ':pop'=>$pophomepath,
             ':domain'=>$_POST['domain'],
             ));

// Is using indexes worth setting the domain_id by hand? -- GCBirzan
          if (!$success) {
          header ("Location: siteadd.php?type={$_POST['type']}&failaddedusrerr={$_POST['domain']}");
          die;
        } else {
          header ("Location: site.php?added={$_POST['domain']}" .
                "&type={$_POST['type']}");
/* GLD fix for bug in welcome message to blank local part. email to: postmaster@<relay-to-domain>
   Welcome message only sent for new Local domains and not for alias or Relay domains.  */
/*      mail("{$_POST['localpart']}@{$_POST['domain']}",  GLD removed this */
        mail("postmaster@{$_POST['domain']}",
              vexim_encode_header(_("Welcome Domain Admin!")),
              "$welcome_newdomain",
              "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: 8bit\r\n");
/*            "From: {$_POST['localpart']}@{$_POST['domain']}\r\n");  GLD removed this */
/* GLD fix: email from:  apache@<relay from domain>  */
        die;
        }
      } else {
        header ("Location: site.php?added={$_POST['domain']}" .
                "&type={$_POST['type']}");
        die;
      }
    } else {
      header ("Location: siteadd.php?type={$_POST['type']}&failaddeddomerr={$_POST['domain']}");
      die;
    }
} else if ($_POST['type'] == "alias") {
    $query = "INSERT INTO domainalias (domain_id, alias)
               VALUES (:domain_id, :alias)";
    $sth = $dbh->prepare($query);
    $success = $sth->execute(array(':domain_id'=>$_POST['aliasdest'], ':alias'=>$_POST['domain']));
    if (!$success) {
      header ("Location: siteadd.php?type={$_POST['type']}&failaddeddomerr={$_POST['domain']}");
      die;
    } else {
      header ("Location: site.php?added={$_POST['domain']}" .
              "&type={$_POST['type']}");
      die;
    }
} else {
    header ("Location: siteadd.php?type={$_POST['type']}&failaddedpassmismatch={$_POST['domain']}");
}

?>
<!-- Layout and CSS tricks obtained from http://www.bluerobot.com/web/layouts/ -->
<!-- This module was modified extensively by GLD to accomodate relay server address and relay spamassassin parameters  -->
