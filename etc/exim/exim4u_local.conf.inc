# exim4u_local.conf.inc
# exim4u - Host Specific Configuration Settings
#
# Specify your host's canonical name here. This should normally be the fully
# qualified "official" name of your host. If this option is not set, the
# uname() function is called to obtain the name. In many cases this does
# the right thing and you need not set anything explicitly.
#
primary_hostname = hostname.tld
#
# exim4u: The following lines are concerned with ident callbacks, as defined by
# RFC 1413 (hence their names). These settings cause Exim to make ident callbacks
# for all incoming SMTP calls. You can limit the hosts to which these calls are
# made, or change the timeout that is used. If you set the timeout to zero, all
# ident calls are disabled. Although they are cheap and can provide useful
# information for tracing problem messages, some hosts and firewalls have
# problems with ident calls. This can result in a timeout instead of an immediate
# refused connection, leading to delays on starting up an incoming SMTP session.
# rfc1413_hosts = * : ! 192.168.0.0/16
#
rfc1413_hosts = *
rfc1413_query_timeout = 5s
#
# exim4u: It appears that more and more DNS zone administrators are breaking the rules
# and putting domain names that look like IP addresses on the right hand side of MX
# records. Exim follows the rules and rejects this, giving an error message that
# explains the mis-configuration. However, some other MTAs support this practice,
# so to avoid “Why can’t Exim do this?” complaints, allow_mx_to_ip exists, in order
# to enable this heinous activity. It is not recommended, except when you have
# no other choice.
#
allow_mx_to_ip = true
#
# exim4u: If using Domain Keys for outgoing mail then set DOMAINKEYS = YES here.
# You must also generate and save SSL keys and place the Domain Key TXT record
# entries in the DNS zone files for Bind/named. Complete instructions for 
# configuring Exim4U's Domain Keys implementation are included at the bottom of
# this file.
#
DOMAINKEYS = YES
#
# exim4u: Exim4u's multiple IP option is set here. This option allows the mail server
# to masquarade as multiple domains each with their own tls certificate as specified.
# If MULTI_IP = YES, then the IP address that is designated for each local domain
# within the exim4u's web interface is advertised during outgoing SMTP sessions
# instead of the default main IP address. For incoming mail, the IP address
# is designated by $received_ip_address. The domain name that is advertised
# is the PTR record for each IP. Therefore, reverse DNS records must be
# published for each domain. If MULTI_IP =NO, then the primary IP address
# and primary_hostname is used instead. If you are using a router such that the 
# machine's IP address is not public (192.168.*.*) then we recommend that you set
# MULTI_IP = NO. Complete instructions for configuring Exim4U's multi IP option
# are included at the bottom of this file.
#
MULTI_IP = YES
#
# If using tls then you must store your certificates as described at the bottom
# of this file according to whether MULTI_IP is set to YES or NO. Include the
# following line if using tls certificates (If not, then comment the following line out):
#
tls_advertise_hosts = *
#
# exim4u: Setup MYSQL
# Specify our mysql information: socket location, database name, user name  and password.
# The data is hidden from all but the admin user with "hide".
# hide mysql_servers =  <hostname>::<port>(<socket name>)/<database>/<user>/<password>
# hide mysql_servers = localhost::(/tmp/mysql.sock)/exim4u/exim4u/CHANGE
#
hide mysql_servers = localhost::(/var/lib/mysql/mysql.sock)/exim4u/exim4u/CHANGE
#
#
# exim4u: Added extra_local_interfaces to prevent loopback problems with relay domains. This is
# needed only when a network router is used such that the exim computer is located within a subnet.
# That is, this should be used whenever exim's transport interface address is 192.168.x.y instead
# of the public IP address.
# extra_local_interface = public IP address
#
# extra_local_interfaces = 111.222.333.444
#
#
# exim4u: Add SMTP ports to listen for here. Port 25 is required. RFC 2476, requires port 587 
# for email user clients and 2525 is simply an extra port.
daemon_smtp_ports = 25 : 2525 : 587
#
# exim4u: Put your mailman domains here (separated by : )
#
# domainlist mm_domains = mailman_domain_name_1.tld :  mailman_domain_name_2.tld : mailman_domain_name_3.tld
#
#
# exim4u: Mailman Configuration Location
#
MAILMAN_HOME=/var/lib/mailman
MAILMAN_WRAP=/usr/lib/mailman/mail/mailman
MAILMAN_USER=mailnull
MAILMAN_GROUP=mail
#
#
# END Of Host Specific Configuration Settings
#
#
#
# DOMAIN KEYS CONFIGURATION INSTRUCTIONS
# --------------------------------------
#
# 1) Generate private and public SSL keys and save the keys in /etc/pki/tls/dk as follows.
#	cd /etc/pki/tls
#	mkdir dk
#	cd dk
#	openssl genrsa -out rsa.private 1024
#	openssl rsa -in rsa.private -out rsa.public -pubout -outform PEM
#	chown -R exim.exim /etc/pki/tls/dk
#	chmod go-r -R /etc/pki/tls/dk
#    
#    The private key is named rsa.private and the public key is named rsa.public. Both are
#    stored in the /etc/pki/tls directory.
#
#    Save whats between ---BEGIN PUBLIC KEY--- and ---END PUBLIC KEY--- from the public key
#    for use in step 2 below, however, get rid of the line feeds so that the key is one long line.
#
# 2) Configure Bind (named) zone files.
#
#    Edit the zone files for all hosted mail domains, and add the following TXT records:
#
#	_domainkey.domain.tld.       IN      TXT     "t=y; o=-"
#	exim4u._domainkey.domain.tld.  IN      TXT     "k=rsa; t=y; p= <public key>"
#	
#    In the above two lines, domain.tld is the FQDN for the hosted domain. Also, In the second
#    TXT record above, <public key> is the public key saved as one line without line
#    feeds as described above in step 1.
#
# 3) Modify /etc/named.conf (or var/named/chroot/etc/named.conf if running chrooted Bind/named).
#
#    In both your primary and secondary name servers add the following to the options section
#    of named.conf which allows the use of _(underscore):
#
#	check-names master ignore; 
#
#
#
# MULTI IP CONFIGURATION INSTRUCTIONS
# -----------------------------------
#
# With MULTI_IP = YES, exim4u allows an infinite number of domains to masquarade as the host
# mail server and each of these domains may have their own IP address and SSL certificate/key. With
# MULTI_IP= NO, exim4u uses only one server host name, IP address and SSL certificate/key for all
# hosted domains.
#
# For MULTI_IP = NO, the tls certificate and key for your primary_hostname is stored here:
# /etc/pki/tls/exim_tls/exim.cert
# /etc/pki/tls/exim_tls/exim.key
#
# For MULTI_IP = YES, tls certificates and keys for each domain/IP are stored here:
# /etc/pki/tls/exim_tls/exim.MY_IP.cert
# /etc/pki/tls/exim_tls/exim.MY_IP.key
# where MY_IP_address = Transport's interface IP address. As an example, if a domain's IP address is
# 111.222.333.444 then the certificate is stored here: /etc/pki/tls/exim_tls/exim.111.222.333.444.cert
#
# To accomplish this, a unique IP address must be assigned to each domain and the mail server should
# be configured as follows:
#
# For each IP/domain that is added, the following must be perfomed:
# 1. Modify the DNS zone file in /var/named.  Add "A" and "MX" records for each IP/domain. Also,
#    for each IP/domain, modify the spf record to include the new IP and mx address.
# 2. For each domain, generate a self-signed SSL certificate or purchase an SSL certificate.
#    Add each domain's SSL certificate and key files to /etc/pki/tls/exim-tls. The file names
#    should be: exim.MY_IP.cert and exim.MY_IP_key where MY_IP is the ip address for the
#    certificate's domain. eg: MY_IP = 111.222.333.444.  Make sure that file ownerships for the
#    certificates and keys are all set to exim.  That is;
#    chown exim.exim -R /etc/pki/tls/exim-tls
# 3. Add all domains and IPs to the local host's file named: /etc/exim/exim4u_hostnames+hostIPs
# 4. If using relays then, on the local host, add the domain of each relay server to either of
#    the following files as per your config:
#       /etc/exim/exim4u_backup_mx_host_names or;
#       /etc/exim/exim4u_backup_mx_rl_host_names
# 5. Add a new Dovecot configuration file for each IP address and put it in /etc/dovecot. The 
#    Dovecot configuration file names should be dovecot.MY_IP.conf where MY_IP is the IP
#    address for the domain.  Then, modify the /etc/init.d/dovecot service to include all dovecot
#    configuration files (for each IP). That is, a daemon should be configured to start for each
#    configuration file in /etc/init.d/dovecot.
# 6. Using exim4u's web interface, set the "Outgoing IP" to the "A" DNS record for each local
#    domain. The local domain's "Outgoing IP" must be an IP address and not a FQDN.
# 7. For relay hosts do the following: Using exim4u's web interface, set the "Relay Server Address"
#    for each domain.  The "Relay Server Address" may be either the IP address or the FQDN.