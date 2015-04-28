# Script to remove the "clear" column from the users table.
#
# mysql -u<user_name> -p<password> exim4u < mysql_remove-clear-column.sql
#     where: <username> = Your MYSQL root username
#            <password> = Your MYSQL root username's password
#            For example, for user_name=MYNAME and password=SECRET then the command would be:
#            mysql -uMYNAME -pSECRET exim4u < mysql_migrate/mysql_remove-clear-column.sql
#
ALTER TABLE users DROP COLUMN clear;
