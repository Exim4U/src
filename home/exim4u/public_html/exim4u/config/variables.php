<?php
  /* SQL Database login information */
  include_once dirname(__FILE__) . "/i18n.php";

  $sqlserver = "localhost";
  $sqltype = "mysql";
  $sqldb = "exim4u";
  $sqluser = "exim4u";
  $sqlpass = "CHANGE";

  $dsn = "$sqltype:host=$sqlserver;dbname=$sqldb";
  $dboptions = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8');
 
  try {
    $dbh = new PDO($dsn, $sqluser, $sqlpass, $dboptions);
    $dbh->setAttribute($dbh::ATTR_DEFAULT_FETCH_MODE, $dbh::FETCH_ASSOC);
  } catch (PDOException $e) {
    die($e->getMessage());
  }

  /* We use this IMAP server to check user quotas */
  $imapquotaserver = "{mail.CHANGE.com:143/imap/notls}";
  $imap_to_check_quota = "no";

  /* Setting this to 0 if only admins should be allowed to login */
  $AllowUserLogin = 1;

  /* Choose whether to break up domain and user lists alphabetically */
  $alphadomains = 1;
  $alphausers = 1;

/* Set to either "sha", "sha512", "des" or "md5" depending on your
   crypt() libraries or "clear" for clear-text passwords. Encryption
   is recommended and the "clear" option is discouraged.
   Examples: Debian 7 - use sha512, FreeBSD 10.1 - use sha, Older OSs - use md5 */
  $cryptscheme = "sha512";

  /* Choose the type of domain name input for the index page. It should
     either be 'static', 'dropdown' or 'textbox'. Static causes the
     domain name part of the URL to be used automatically, and the user
     cannot change it. Dropdown uses a dropdown style menu with <select>
     and <option>. Textbox presents a blank line for the user to type
     their domain name one. Textbox might be prefered if you have a
     large number of domains, or don't want to reveal the names of sites
     which you host */
  $domaininput = "dropdown";

  /* Multi IP config:
     If $multi_ip = "yes" then this is a multi IP installation.  MULTI_IP must
     also be set to "YES" in the exim configuration file exim4u_local.conf.inc.
     Also reverse dns (PTR) records must be advertised for all IP addresses for
     all domains used in the multi IP config.  Each IP's "PTR" record should be
     setup exactly the same (in reverse) as the coinciding domain's "A" record.
     The outgoing_IP value designates the default IP address for sending mail for
     all domains. This value can be changed for each domain in the web interface.
     The MX host's domain name must be defined in DNS for reverse lookups to 
     work properly.
     $multi_ip = "yes";
     $outgoing_IP = "111.222.333.444"; */
  $multi_ip = "no";

  /* The UID's and GID's control the default UID and GID for new domains
     and if postmasters can define their own.
     THE UID AND GID MUST BE NUMERIC! 
        $uid = "511";
        $gid = "511";
     If you use exim4u as the user and group for the default UID and GID then
     use:
        $uid = shell_exec('id -u exim4u');
        $gid = shell_exec('id -g exim4u');  */
  $uid = shell_exec('id -u exim4u');
  $gid = shell_exec('id -g exim4u');

  $postmasteruidgid = "no";

  /* The location of your mailstore for new domains.
     Make sure the directory belongs to the configured $uid/$gid!
        $mailroot = "/home/USER/mail/";
     For most Linux, $mailroot = "/home/exim4u/mail/";
     For most FreeBSD, $mailroot = "/usr/home/exim4u/mail/"; */
        
        $mailroot = "/home/exim4u/mail/";

  /* Mailman
     exim4u: mod to allow multiple mailman domains.
     If mailman is  installed, this mod now gets the domain based on the domain
     currently chosen and properly inserts that domain name into 
     the <a href> link to call mailman correctly. This mod assumes that, for all
     resident domains, mailman is installed in <domain.tld>/mailmandir so
     that new lists will be added at <domain.tld>/mailmandir/create and existing
     domains will be viewed and modified at <domain.tld>/mailmandir/admin/<listname>.

     Set to "yes" if mailman is installed or "no" if mailman is not installed.
     */
  $mailmaninstalled = "no";

  /* exim4u: Specify secure or insecure protocol for mailman acccess as follows:
     "https" or "http" for mailman.   */
  $mailmanprotocol = "https";

  /* If web hosting for mail domains are not on the same server as Exim4U then
     you need to specify a domain for mailman to use on the Exim4u server.
     Specify mailman domain name to be used as "domain.tld" or use "default"
     to use the local mail domains.   
  $mailmandomain = "default";
     or
  $mailmandomain = "domain.tld";
  */
  $mailmandomain = "default";
  
  /* exim4u: Specify the path to mailman from the domain document roots. That is, if on your installation
     for each domain, mailman is located at https://<domain.tld>/mailmandir then specify "mailmandir".
     */
  $mailmanpath = "mailman";


  /* sa_tag is the default value to offer when we create new domains for SpamAssassin tagging
     sa_refuse is the default value to offer when we create new domains for SpamAssassin dropping */
  $sa_tag = "5";
  $sa_refuse = "10";

  /* max size of a vacation message */
  $max_vacation_length = 4095;

  /* Welcome message, sent to new POP/IMAP accounts */
  @$welcome_message = "Welcome, {$_POST['realname']} !\n\nYour new E-mail account is all ready for you.\n\n"
                   . "Here are some settings you might find useful:\n\n"
               . "Username: {$_POST['localpart']}@{$_SESSION['domain']}\n"
/* exim4u Change   . "POP3 server: mail.{$_SESSION['domain']}\n"
                   . "SMTP server: mail.{$_SESSION['domain']}\n"; */
                   . "Incoming POP3 or IMAP server: mail.{$_SESSION['domain']}\n"
                   . "Outgoing SMTP server: mail.{$_SESSION['domain']}\n";

  /* Welcome message, sent to new domains */
  @$welcome_newdomain = "Welcome, and thank you for registering your e-mail domain\n"
                   . "{$_POST['domain']} with us.\n\nIf you have any questions, please\n"
                 . "don't hesitate to ask your account representitive.\n";
?>
