#!/bin/bash
#
#  IMMEDIATELY FOLLOWING THE EXECUTION OF THIS SCRIPT, LOGIN TO THE EXIM4U USER INTERFACE WITH THE FOLLOWING CREDENTIALS:
# 
#  Username = 'siteadmin', Password = 'PASSWD'
# 
#  THEN, IMMEDIATELY CHANGE THE SITE PASSWORD FROM 'PASSWD' TO SOMETHING VERY SECURE.
#
clear;
printf "\nEXIM4U MYSQL SETUP\n\n";
printf "This script creates the exim4u database.\n\n";
# Specify values for '@UID', '@GID', @DB_PASSWD and @CRYPT.
while ! [[ "$uid" =~ ^[0-9]+$ ]]
do
	read -e -p "Please enter the numeric UID for the exim4u user: " uid;
	if ! [[ "$uid" =~ ^[0-9]+$ ]]
	then
		echo -en "\033[1A\033[2K"
	fi
done;
while ! [[ "$gid" =~ ^[0-9]+$ ]]
do
	read -e -p "Please enter the numeric GID for the exim4u user: " gid;
	if ! [[ "$gid" =~ ^[0-9]+$ ]]
	then
		echo -en "\033[1A\033[2K"
	fi
done;
printf "Please enter the mysql password to be used with the exim4u database and exim4u user: ";
	read db_passwd;
#
# Create a working copy of mysql_setup.sql named mysql-final.sql
cp ./mysql.sh ./mysql_final.sql;
sed -i 1,100d ././mysql_final.sql;
# Substitute all occurrences of @UID with the numeric uid for the exim4u user.
sed -i s/@UID/$uid/ ./mysql_final.sql;
# Substitute all occurrences of @GID with the numeric gid for the exim4u user.
sed -i s/@GID/$gid/ ./mysql_final.sql;
# Substitute the value of @DB_PASSWD with the exim4u database password.
sed -i s/@DB_PASSWD/$db_passwd/ ./mysql_final.sql;
# 
printf "\nPlease specify the password encryption method for Exim4U as follows:
1 - SHA512 (Recommended)
2 - MD5
3 - DES
4 - Bcrypt (BSD only)
5 - Clear-text (Not Recommended) \n\n";
#
while [[ $crypt -lt 1 || $crypt -gt 5 ]]
do
#   	read -e -p "Enter encryption method (1 - 5): " -i "1" crypt;
	read -e -p "Enter encryption method (1 - 5): " crypt;
	if ! [[ "$crypt" =~ ^[0-5]+$ ]]
	then
		echo -en "\033[1A\033[2K"
		crypt=0
	fi
done;
if [ $crypt == 1 ]
then
	sed -i 3,10d ./mysql_final.sql
fi
if [ $crypt == 2 ]
then
	sed -i 5,10d ./mysql_final.sql
	sed -i 1,2d ./mysql_final.sql
fi
if [ $crypt == 3 ]
then
	sed -i 7,10d ./mysql_final.sql
	sed -i 1,4d ./mysql_final.sql
fi
if [ $crypt == 4 ]
then
	sed -i 9,10d ./mysql_final.sql
	sed -i 1,6d ./mysql_final.sql
fi
if [ $crypt == 5 ]
then
	sed -i 1,8d ./mysql_final.sql
fi
# printf "\nThe MySQL script for Exim4U is complete.  Execute the script, which will install the Exim4u database, with the following command: \n\nmysql -u root -p < ./mysql_final.sql\n\n";
printf "\n\n\nExecuting MySQL to install the Exim4u database and user with the following command:\n\n";
printf "     \'mysql -u root -p < ./mysql_final.sql\'\n\n";
printf "Enter the MySQL root user's password....\n\n";
mysql -u root -p < ./mysql_final.sql;
rm ./mysql_final.sql;
printf "\n\nScript complete.....\n\n";
printf "Immediately following the successful execution of this script,\n";
printf "login to the Exim4U user interface with the following credentials:\n\n";
printf "     Username = 'siteadmin', Password = 'PASSWD'\n\n";
printf "and immediately change the site password from 'PASSWD' to something very secure.\n\n";
exit
#
# End Of Bash Script
#
# ---------------------------------
#
# Start Of Mysql Script
#
# SHA512 encryption of 'PASSWD' for crypt field:
set @PW_CRYPT='$6$4HTy8Ts3TvC1$FFAVbY1N3nKiuYi7eV3DQ0clbGS9MYrVEOjerUUQgc0sdYWfqceYbfLyPnBUK92soHAS15j.w7H05eDQn3erL/';
# MD5 encryption of 'PASSWD' for crypt field:
set @PW_CRYPT='$1$12345678$JCW6RgxAyYiRf00lURaOE.';
# DES encryption of 'PASSWD' for crypt field:
set @PW_CRYPT='0A/4rVI7XZP6Y';
# Bcrypt encryption of 'PASSWD' for crypt field:
set @PW_CRYPT='$2y$10$kUX1xxuCbSCQ5v8VE5uZ1ez7BoRJsmJACyYWPh0OJqOBwlym1IKsy';
# Clear-text password:
set @PW_CRYPT='PASSWD';
--
-- Database: `exim4u`
--
CREATE DATABASE IF NOT EXISTS `exim4u` DEFAULT CHARACTER SET utf8;
--
-- Table: `domains`
--
DROP TABLE IF EXISTS `exim4u`.`domains`;
CREATE TABLE IF NOT EXISTS `exim4u`.`domains`
(
    	domain_id        mediumint(10)  unsigned  NOT NULL  auto_increment,
	domain           varchar(255)            NOT NULL  default '',
	maildir          varchar(4096)           NOT NULL  default '',
	uid              smallint(5)   unsigned  NOT NULL  default '@UID',
	gid              smallint(5)   unsigned  NOT NULL  default '@GID',
	max_accounts     int(10)       unsigned  NOT NULL  default '0',
	quotas           int(10)       unsigned  NOT NULL  default '0',
	type             varchar(5)                        default NULL,
	avscan           tinyint(1)              NOT NULL  default '0',
	blocklists       tinyint(1)              NOT NULL  default '0',
	enabled          tinyint(1)              NOT NULL  default '1',
	mailinglists     tinyint(1)              NOT NULL  default '0',
	maxmsgsize       mediumint(8)  unsigned  NOT NULL  default '0',
	pipe             tinyint(1)              NOT NULL  default '0',
	spamassassin     tinyint(1)              NOT NULL  default '0',
	sa_tag           smallint(5)   unsigned  NOT NULL  default '0',
	sa_refuse        smallint(5)   unsigned  NOT NULL  default '0',
	relay_address    varchar(255)            NOT NULL  default '',
	outgoing_ip      varchar(15)             NOT NULL  default '',
	PRIMARY KEY (domain_id),
	UNIQUE KEY domain (domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table: `users`
--
DROP TABLE IF EXISTS `exim4u`.`users`;
CREATE TABLE IF NOT EXISTS `exim4u`.`users` 
(
	user_id          int(10)       unsigned  NOT NULL  auto_increment,
	domain_id        mediumint(10) unsigned  NOT NULL,
	localpart        varchar(64)             NOT NULL  default '',
	username         varchar(255)            NOT NULL  default '',
-- Optionally, uncomment the clear field,
--	clear            varchar(255)                      default NULL,
	crypt            varchar(255)                      default NULL,
	uid              smallint(5)   unsigned  NOT NULL  default '@UID',
	gid              smallint(5)   unsigned  NOT NULL  default '@GID',
	smtp             varchar(4096)                     default NULL,
	pop              varchar(4096)                     default NULL,
	type             enum('local', 'alias', 
                          'catch', 'fail', 
                          'piped', 'admin', 
                          'site')                NOT NULL  default 'local',
	admin            tinyint(1)              NOT NULL  default '0',
	on_avscan        tinyint(1)              NOT NULL  default '0',
	on_blocklist     tinyint(1)              NOT NULL  default '0',
	on_forward       tinyint(1)              NOT NULL  default '0',
	on_piped         tinyint(1)              NOT NULL  default '0',
	on_spamassassin  tinyint(1)              NOT NULL  default '0',
	on_vacation      tinyint(1)              NOT NULL  default '0',
	enabled          tinyint(1)              NOT NULL  default '1',
	flags            varchar(16)                       default NULL,
	forward          varchar(4096)                     default NULL,
        unseen           tinyint(1)              NOT NULL  default '0',
	maxmsgsize       mediumint(8)  unsigned  NOT NULL  default '0',
	quota            int(10)       unsigned  NOT NULL  default '0',
	realname         varchar(255)                      default NULL,
	sa_tag           smallint(5)   unsigned  NOT NULL  default '0',
	sa_refuse        smallint(5)   unsigned  NOT NULL  default '0',
	tagline          varchar(255)                      default NULL,
	vacation         text                              default NULL,
	on_spambox       tinyint(1)              NOT NULL  default '0',
	on_spamboxreport tinyint(1)	         NOT NULL  default '0',
	PRIMARY KEY (user_id),
	UNIQUE KEY username (localpart, domain_id),
	KEY local (localpart)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table: `blocklists`
--
DROP TABLE IF EXISTS `exim4u`.`blocklists`;
CREATE TABLE IF NOT EXISTS `exim4u`.`blocklists`
(
	block_id         int(10)       unsigned  NOT NULL  auto_increment,
	domain_id        mediumint(10)  unsigned  NOT NULL,
	user_id          int(10)       unsigned            default NULL,
	blockhdr         varchar(192)            NOT NULL  default '',
	blockval         varchar(255)            NOT NULL  default '',
	color            varchar(8)              NOT NULL  default '',
	PRIMARY KEY (block_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Table: `domainalias`
--
CREATE TABLE IF NOT EXISTS `exim4u`.`domainalias` 
(
	domain_id        mediumint(10)  unsigned  NOT NULL,
	alias            varchar(255)       NOT NULL,
	PRIMARY KEY (alias)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table: `groups`
--
DROP TABLE IF EXISTS `exim4u`.`groups`;
CREATE TABLE IF NOT EXISTS `exim4u`.`groups`
(
	id               int(10)        NOT NULL  auto_increment,
	domain_id        mediumint(10)        unsigned  NOT NULL,
	name             varchar(64)              NOT NULL,
	is_public        char(1)                  NOT NULL  default 'Y',
	enabled          tinyint(1)               NOT NULL  default '1',
	PRIMARY KEY (id),
	UNIQUE KEY group_name(domain_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Table: `group_contents`
--
DROP TABLE IF EXISTS `exim4u`.`group_contents`;
CREATE TABLE IF NOT EXISTS `exim4u`.`group_contents` 
(
	group_id         int(10)   unsigned  NOT NULL,
	member_id        int(10)   unsigned  NOT NULL,
	PRIMARY KEY (group_id, member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--
-- Create table for simple mailing list:
--
DROP TABLE IF EXISTS `exim4u`.`ml`;
CREATE TABLE IF NOT EXISTS `exim4u`.`ml`
(
        domain_id           mediumint(10) unsigned   NOT NULL,
        name                varchar(64)             NOT NULL,
        email               varchar(128)            NOT NULL,
        enabled             bool                    NOT NULL default '1',
        -- m for member, h for head member
        type                char(1)                 NOT NULL default 'm',
        memberCount         int                     NULL,
        -- s for sender, m for mailing list
        replyTo             char(1)                 NOT NULL default 's',
        -- there are 3 head members that hold info for the group : memberCount and enabled
        fullName            varchar(256)            NULL,
        PRIMARY KEY (domain_id, type, name, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--
--
-- Privileges and database password:
--
GRANT SELECT,INSERT,DELETE,UPDATE ON `exim4u`.* to "exim4u"@"localhost" 
IDENTIFIED BY '@DB_PASSWD';
FLUSH PRIVILEGES;
--
-- add initial domain: admin
--
INSERT INTO `exim4u`.`domains` (domain_id, domain) VALUES ('1', 'admin');
--
-- add initial user, "siteadmin" and password, "PASSWD" (as encrypted)
--
INSERT INTO `exim4u`.`users`
(
	crypt,
	domain_id, localpart, username, uid, gid, smtp, pop, realname, type, admin
)
VALUES 
(
@PW_CRYPT,'1', 'siteadmin', 'siteadmin', '@UID', '@GID', '', '', 'SiteAdmin', 'site', '1'
);
