DROP TABLE IF EXISTS `exim4u`.`ml`;
CREATE TABLE IF NOT EXISTS `exim4u`.`ml`
(
    domain_id           mediumint(8) unsigned   NOT NULL,
    name                varchar(64)             NOT NULL,
    email               varchar(128)            NOT NULL,
    enabled             bool                    NOT NULL default '1',
    -- m for member, h for head member
    type                char(1)                 NOT NULL default 'm',
    memberCount         int                     NULL,
    -- s for sender, m for mailing list
    replyTo             char(1)                 NOT NULL default 's',
    -- there are 3 head members that hold info for the group : memberCount and enabled
    fullName            varchar(256)            NULL,
    PRIMARY KEY (domain_id, type, name, email)
);
-- adapter l'insert pour mettre 3 head
-- alter table ml add column type char(1) NOT NULL default 'm';
-- alter table ml add column memberCount int NULL;
-- alter table ml add column fullName varchar(256) NULL;
-- alter table ml add column replyTo char(1) NOT NULL default 's'
