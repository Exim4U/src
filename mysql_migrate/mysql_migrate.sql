--
-- Copyright (c) 2009 MailHub4U.com, LLC
--
-- Script to upgrade mysql database from vexim to exim4u
--
--
-- Create exim4u database.
--
CREATE DATABASE IF NOT EXISTS `exim4u`;
--
--
-- Create tables and copy the contents of existing fields
-- from the vexim database to the exim4u database.
--
CREATE TABLE `exim4u`.`domains` LIKE `vexim`.`domains`;
INSERT INTO `exim4u`.`domains` SELECT * FROM `vexim`.`domains`;

CREATE TABLE `exim4u`.`users` LIKE `vexim`.`users`;
INSERT INTO `exim4u`.`users` SELECT * FROM `vexim`.`users`;

CREATE TABLE `exim4u`.`blocklists` LIKE `vexim`.`blocklists`;
INSERT INTO `exim4u`.`blocklists` SELECT * FROM `vexim`.`blocklists`;

CREATE TABLE `exim4u`.`domainalias` LIKE `vexim`.`domainalias`;
INSERT INTO `exim4u`.`domainalias` SELECT * FROM `vexim`.`domainalias`;

CREATE TABLE `exim4u`.`groups` LIKE `vexim`.`groups`;
INSERT INTO `exim4u`.`groups` SELECT * FROM `vexim`.`groups`;

CREATE TABLE `exim4u`.`group_contents` LIKE `vexim`.`group_contents`;
INSERT INTO `exim4u`.`group_contents` SELECT * FROM `vexim`.`group_contents`;
--
--
-- Add extra fields to exim4u database:
--
-- AddColumnUnlessExists procedure is Copyrighted (c) 2009 www.cryer.co.uk
-- 
drop procedure if exists exim4u.AddColumnUnlessExists;
delimiter '//'

create procedure exim4u.AddColumnUnlessExists(
	IN dbName tinytext,
	IN tableName tinytext,
	IN fieldName tinytext,
	IN fieldDef text)
begin
	IF NOT EXISTS (
		SELECT * FROM information_schema.COLUMNS
		WHERE column_name=fieldName
		and table_name=tableName
		and table_schema=dbName
		)
	THEN
		set @ddl=CONCAT('ALTER TABLE ',dbName,'.',tableName,
			' ADD COLUMN ',fieldName,' ',fieldDef);
		prepare stmt from @ddl;
		execute stmt;
	END IF;
end;
//

delimiter ';'

call exim4u.AddColumnUnlessExists('exim4u', 'domains', 'relay_address', 'varchar(64) NOT NULL'); 
call exim4u.AddColumnUnlessExists('exim4u', 'domains', 'outgoing_IP', 'varchar(15) NOT NULL'); 
call exim4u.AddColumnUnlessExists('exim4u', 'users', 'on_spambox', 'tinyint(1) unsigned NOT NULL default 0'); 
call exim4u.AddColumnUnlessExists('exim4u', 'users', 'on_spamboxreport', 'tinyint(1) unsigned NOT NULL default 0'); 

drop procedure exim4u.AddColumnUnlessExists;
