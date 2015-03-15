# Script to convert existing Exim4U data base to utf8
#
# mysql -u<user_name> -p<password> exim4u < mysql_utf8-conversion.sql
#     where: <username> = Your MYSQL root username
#            <password> = Your MYSQL root username's password
#            For example, for user_name=MYNAME and password=SECRET then the command would be:
#            mysql -uMYNAME -pSECRET exim4u < mysql_migrate/mysql_expand_crypt.sql
#
alter database exim4u charset=utf8;
ALTER TABLE blocklists CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE domainalias CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE domains CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE groups CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE group_contents CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE ml CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
