#
# MySQL Update Script for Exim4U 3.1 Migration
# --------------------------------------------
# Updates the mysql database for migrating from Exim4U 3.0 to 3.1.
# - Updates several field lengths.
# - Sets the enabled flag to enabled (1) for relay domains since relay
#   domains did not previously utilize the enabled field.
#
# IMPORTANT
# ---------
# You should backup your mysql database prior to running this script.
# This script is optional and not required for your Exim4U installation to
# function properly. However, if you do not run the script then you must
# manually enable all relay domains using the Exim4U web interface after
# upgrading your server from Exim4U 3.0 to Exim4U 3.1.
#
# USAGE SYNTAX
# ------------
# mysql -u <username> -p exim4u < mysql_migrate/mysql_exim4u-3.1.sql
#     where: <username> = Your MYSQL root username
#     The script will then prompt you for your MYSQL username password.
#	     For example, for username=MYNAME then the command would be:
#	     mysql -u MYNAME -p exim4u < mysql_migrate/mysql_exim4u-3.1.sql
#
alter table domains modify domain VARCHAR(255);
alter table domains modify maildir VARCHAR(4096);
alter table users modify localpart VARCHAR(64);
alter table users modify smtp VARCHAR(4096);
alter table users modify pop VARCHAR(4096);
alter table blocklists modify blockval VARCHAR(255);
alter table domainalias modify alias VARCHAR(255);
update domains set enabled = '1' where type = 'relay';
