# Script to convert existing Exim4U data base to utf8
#
# mysql -u <username> -p exim4u < mysql_utf8-conversion.sql
#     where: <username> = Your MYSQL root username
#     The script will then prompt you for your MYSQL username's password.
#            For example, for username=MYNAME then the command would be:
#            mysql -u MYNAME -p SECRET exim4u < mysql_migrate/mysql_utf8-conversion.sql
#
alter database exim4u charset=utf8;
ALTER TABLE blocklists CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE domainalias CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE domains CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE groups CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE group_contents CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE ml CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
