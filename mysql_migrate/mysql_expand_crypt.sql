# Script to increase the crypt field length.
#
# mysql -u <username> -p exim4u < mysql_migrate/mysql_expand_crypt.sql
#     where: <username> = Your MYSQL root username
#     The script will then prompt you for your MYSQL username's password.
#	     For example, for username=MYNAME then the command would be:
#	     mysql -u MYNAME -p exim4u < mysql_migrate/mysql_expand_crypt.sql
#
alter table users modify crypt VARCHAR(256);
