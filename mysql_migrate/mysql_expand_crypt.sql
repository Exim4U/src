# Script to widen the crypt field.
#
# mysql -u<user_name> -p<password> exim4u < mysql_migrate/mysql_expand_vacation.sql
#     where: <username> = Your MYSQL root username
#            <password> = Your MYSQL root username's password
#	     For example, for user_name=MYNAME and password=SECRET then the command would be:
#	     mysql -uMYNAME -pSECRET exim4u < mysql_migrate/mysql_expand_vacation.sql
#
alter table users modify crypt VARCHAR(256);
