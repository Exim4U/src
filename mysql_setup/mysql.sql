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
    domain_id      mediumint(8)  unsigned  NOT NULL  auto_increment,
	domain           varchar(64)             NOT NULL  default '',
	maildir          varchar(128)            NOT NULL  default '',
	uid              smallint(5)   unsigned  NOT NULL  default 'CHANGE',
	gid              smallint(5)   unsigned  NOT NULL  default 'CHANGE',
	max_accounts     int(10)       unsigned  NOT NULL  default '0', 
	quotas           int(10)       unsigned  NOT NULL  default '0',
	type             varchar(5)                        default NULL,
	avscan           bool                    NOT NULL  default '0',
	blocklists       bool                    NOT NULL  default '0',
	complexpass      bool                    NOT NULL  default '0',
	enabled          bool                    NOT NULL  default '1',
	mailinglists     bool                    NOT NULL  default '0',
	maxmsgsize       mediumint(8)  unsigned  NOT NULL  default '0',
	pipe             bool                    NOT NULL  default '0',
	spamassassin     bool                    NOT NULL  default '0',
	sa_tag           smallint(5)   unsigned  NOT NULL  default '0',
	sa_refuse        smallint(5)   unsigned  NOT NULL  default '0',
	relay_address    varchar(64)             NOT NULL  default '',
	outgoing_ip      varchar(15)             NOT NULL  default '',
	PRIMARY KEY (domain_id),
	UNIQUE KEY domain (domain)
);

--
-- Table: `users`
--
DROP TABLE IF EXISTS `exim4u`.`users`;
CREATE TABLE IF NOT EXISTS `exim4u`.`users` 
(
    user_id          int(10)       unsigned  NOT NULL  auto_increment,
	domain_id        mediumint(8)  unsigned  NOT NULL,
	localpart        varchar(192)            NOT NULL  default '',
	username         varchar(255)            NOT NULL  default '',
-- Optionally, uncomment the clear field,
--	clear            varchar(255)                      default NULL,
	crypt            varchar(255)                      default NULL,
	uid              smallint(5)   unsigned  NOT NULL  default '65534',
	gid              smallint(5)   unsigned  NOT NULL  default '65534',
	smtp             varchar(255)                      default NULL,
	pop              varchar(255)                      default NULL,
	type             enum('local', 'alias', 
                          'catch', 'fail', 
                          'piped', 'admin', 
                          'site')            NOT NULL  default 'local',
	admin            bool                    NOT NULL  default '0',
	on_avscan        bool                    NOT NULL  default '0',
	on_blocklist     bool                    NOT NULL  default '0',
	on_complexpass   bool                    NOT NULL  default '0',
	on_forward       bool                    NOT NULL  default '0',
	on_piped         bool                    NOT NULL  default '0',
	on_spamassassin  bool                    NOT NULL  default '0',
	on_vacation      bool                    NOT NULL  default '0',
	enabled          bool                    NOT NULL  default '1',
	flags            varchar(16)                       default NULL,
	forward          varchar(255)                      default NULL,
        unseen           bool                              default '0',
	maxmsgsize       mediumint(8)  unsigned  NOT NULL  default '0',
	quota            int(10)       unsigned  NOT NULL  default '0',
	realname         varchar(255)                      default NULL,
	sa_tag           smallint(5)   unsigned  NOT NULL  default '0',
	sa_refuse        smallint(5)   unsigned  NOT NULL  default '0',
	tagline          varchar(255)                      default NULL,
	vacation         varchar(4096)                     default NULL,
	on_spambox       tinyint(1)              NOT NULL  default '0',
	on_spamboxreport tinyint(1)	         NOT NULL  default '0',
	PRIMARY KEY (user_id),
	UNIQUE KEY username (localpart, domain_id),
	KEY local (localpart)
);

--
-- Table: `blocklists`
--
DROP TABLE IF EXISTS `exim4u`.`blocklists`;
CREATE TABLE IF NOT EXISTS `exim4u`.`blocklists`
(
    block_id         int(10)       unsigned  NOT NULL  auto_increment,
  	domain_id        mediumint(8)  unsigned  NOT NULL,
	user_id          int(10)       unsigned            default NULL,
	blockhdr         varchar(192)            NOT NULL  default '',
	blockval         varchar(192)            NOT NULL  default '',
	color            varchar(8)              NOT NULL  default '',
	PRIMARY KEY (block_id)
);


--
-- Table: `domainalias`
--
CREATE TABLE IF NOT EXISTS `exim4u`.`domainalias` 
(
    domain_id        mediumint(8)  unsigned  NOT NULL,
	alias varchar(64)
);

--
-- Table: `groups`
--
DROP TABLE IF EXISTS `exim4u`.`groups`;
CREATE TABLE IF NOT EXISTS `exim4u`.`groups`
(
    id               int(10)                           auto_increment,
    domain_id        mediumint(8)  unsigned  NOT NULL,
    name             varchar(64)             NOT NULL,
    is_public        char(1)                 NOT NULL  default 'Y',
    enabled          bool                    NOT NULL  default '1',
    PRIMARY KEY (id),
    UNIQUE KEY group_name(domain_id, name)
);

--
-- Table: `group_contents`
--
DROP TABLE IF EXISTS `exim4u`.`group_contents`;
CREATE TABLE IF NOT EXISTS `exim4u`.`group_contents` 
(
    group_id         int(10)                 NOT NULL,
    member_id        int(10)                 NOT NULL,
    PRIMARY KEY (group_id, member_id)
);

--
-- Privileges (database password):
--
GRANT SELECT,INSERT,DELETE,UPDATE ON `exim4u`.* to "exim4u"@"localhost" 
    IDENTIFIED BY 'CHANGE';
FLUSH PRIVILEGES;

--
-- add initial domain: admin
--
INSERT INTO `exim4u`.`domains` (domain_id, domain) VALUES ('1', 'admin');

--
-- add initial user; postmaster
--
INSERT INTO `exim4u`.`users`
(
    crypt,
    domain_id, localpart, username, uid, gid, smtp, pop, realname, type, admin
)
VALUES 
(
-- Specify the default password, "PASSWD", as encrypted in SHA512, MD5 or DES
-- or as a clear-text password. Select and uncomment one of the following
-- crypt values. The default encryption method is SHA512.
--
-- SHA512 encryption of 'PASSWD' for crypt field:
'$6$4HTy8Ts3TvC1$FFAVbY1N3nKiuYi7eV3DQ0clbGS9MYrVEOjerUUQgc0sdYWfqceYbfLyPnBUK92soHAS15j.w7H05eDQn3erL/',
--
-- MD5 encryption of 'PASSWD' for crypt field:
-- '$1$12345678$JCW6RgxAyYiRf00lURaOE.',
--
-- DES encryption of 'PASSWD' for crypt field:
-- '0A/4rVI7XZP6Y',
--
-- Clear-text password:
-- 'PASSWD',
--
-- Remainder of values for the users table:
'1', 'siteadmin', 'siteadmin', '65535', '65535', '', '', 'SiteAdmin', 'site', '1'
--
);

--
-- Create table for simple mailing list:
--
DROP TABLE IF EXISTS `exim4u`.`ml`;
CREATE TABLE IF NOT EXISTS `exim4u`.`ml`
(
    domain_id           mediumint(8) unsigned   NOT NULL,
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
);
-- adapter l'insert pour mettre 3 head
-- alter table ml add column type char(1) NOT NULL default 'm';
-- alter table ml add column memberCount int NULL;
-- alter table ml add column fullName varchar(256) NULL;
-- alter table ml add column replyTo char(1) NOT NULL default 's'
