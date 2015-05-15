# Script to remove the "clear" column from the users table.
#
# mysql -u <username> -p exim4u < mysql_remove-clear-column.sql
#     where: <username> = Your MYSQL root username
#     The script will then prompt you for your MYSQL username's password.
#            For example, for username=MYNAME then the command would be:
#            mysql -u MYNAME -p exim4u < mysql_migrate/mysql_remove-clear-column.sql
#
ALTER TABLE users DROP COLUMN clear;
