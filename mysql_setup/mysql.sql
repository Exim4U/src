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
	UNIQUE KEY domain (domain),
	KEY domain_id (domain_id),
	KEY domains (domain)
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
	clear            varchar(255)                      default NULL,
	crypt            varchar(255)                       default NULL,
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
-- Priviledges:
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
    domain_id, localpart, username, clear, crypt, uid, gid, 
    smtp, pop, realname, type, admin
)
VALUES 
(   '1', 'siteadmin', 'siteadmin', 'CHANGE', 
    '$1$12345678$2lQK5REWxaFyGz.p/dos3/', '65535', '65535', '', '', 
    'SiteAdmin', 'site', '1'
);

-- Fix password when using DES encrypted password:
-- UPDATE `exim4u`.`users` SET `crypt` = '0Apup3ZbF9RPg'
--   WHERE `user_id` = '1' LIMIT 1 ;


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
