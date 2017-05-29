#
# MySQL Update Script for Exim4U 3.2 Migration From Exim4U 3.1
# ------------------------------------------------------------
# Updates the mysql database for migrating from Exim4U 3.1 to 3.2.
#
# IMPORTANT
# ---------
# You should backup your mysql database prior to running this script.
# This script is optional and not required for your Exim4U installation to
# function properly.
#
# USAGE SYNTAX
# ------------
# mysql -u <username> -p exim4u < mysql_migrate/mysql_exim4u-3.2.sql
#     where: <username> = Your MYSQL root username
#     The script will then prompt you for your MYSQL username's password.
#	     For example, for username=MYNAME then the command would be:
#	     mysql -u MYNAME -p exim4u < mysql_migrate/mysql_exim4u-3.2.sql
#

ALTER TABLE domains MODIFY COLUMN avscan tinyint(1);
ALTER TABLE domains MODIFY COLUMN blocklists tinyint(1);
ALTER TABLE domains DROP COLUMN complexpass;
ALTER TABLE domains MODIFY COLUMN enabled tinyint(1);
ALTER TABLE domains MODIFY COLUMN mailinglists tinyint(1);
ALTER TABLE domains MODIFY COLUMN pipe tinyint(1);
ALTER TABLE domains MODIFY COLUMN spamassassin tinyint(1);
ALTER TABLE domains MODIFY COLUMN relay_address varchar(255);

ALTER TABLE users MODIFY COLUMN domain_id mediumint(10);
ALTER TABLE users MODIFY COLUMN admin tinyint(1);
ALTER TABLE users MODIFY COLUMN on_avscan tinyint(1);
ALTER TABLE users MODIFY COLUMN on_blocklist tinyint(1);
ALTER TABLE users DROP COLUMN on_complexpass;
ALTER TABLE users MODIFY COLUMN on_forward tinyint(1);
ALTER TABLE users MODIFY COLUMN on_piped tinyint(1);
ALTER TABLE users MODIFY COLUMN on_spamassassin tinyint(1);
ALTER TABLE users MODIFY COLUMN enabled tinyint(1);
ALTER TABLE users MODIFY COLUMN forward varchar(4096);
ALTER TABLE users MODIFY COLUMN unseen tinyint(1);
ALTER TABLE users MODIFY COLUMN vacation text;

ALTER TABLE blocklists MODIFY COLUMN domain_id mediumint(10);

ALTER TABLE domainalias MODIFY COLUMN domain_id mediumint(10);
ALTER TABLE domainalias ADD PRIMARY KEY(alias);

ALTER TABLE groups MODIFY COLUMN id int(10) NOT NULL auto_increment;
ALTER TABLE groups MODIFY COLUMN domain_id mediumint(10);
ALTER TABLE groups MODIFY COLUMN enabled tinyint(1);

ALTER TABLE group_contents MODIFY COLUMN group_id int(10) unsigned NOT NULL;
ALTER TABLE group_contents MODIFY COLUMN member_id int(10) unsigned NOT NULL;

ALTER TABLE ml MODIFY COLUMN domain_id mediumint(10);
