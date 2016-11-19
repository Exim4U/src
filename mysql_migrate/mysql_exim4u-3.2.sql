#
# MySQL Update Script for Exim4U 3.2 Migration From Exim4U 3.1
# ------------------------------------------------------------
# Updates the mysql database for migrating from Exim4U 3.1 to 3.2.
# - Adds primary key (alias) in domainalias table.
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
ALTER TABLE domainalias ADD PRIMARY KEY(alias);
