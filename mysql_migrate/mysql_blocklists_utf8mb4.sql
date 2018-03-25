# Script to convert blocklists table to utf8mb4. This fixes the "max key length
# is 767 bytes" problem for headers with embeded emojis (such as Subjects).
#
# Usage:
# mysql -u <username> -p exim4u < mysql_blocklists_utf8mb4.sql
#     where: <username> = Your MYSQL root username
#     The script will then prompt you for your MYSQL username's password.
#            For example, for username=MYNAME then the command would be:
#            mysql -u MYNAME -p exim4u < mysql_migrate/mysql_blocklists_utf8mb4.sql
#
ALTER TABLE blocklists CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
