-- $Horde: nag/scripts/sql/nag.oci8.sql,v 1.1.2.8 2008/11/28 20:07:52 chuck Exp $

CREATE TABLE nag_tasks (
    task_id              VARCHAR2(32) NOT NULL,
    task_owner           VARCHAR2(255) NOT NULL,
    task_creator         VARCHAR2(255) NOT NULL,
    task_parent          VARCHAR2(255) NOT NULL,
    task_assignee        VARCHAR2(255),
    task_name            VARCHAR2(255) NOT NULL,
    task_uid             VARCHAR2(255) NOT NULL,
    task_desc            CLOB,
    task_start           INT,
    task_due             INT,
    task_priority        INT DEFAULT 0 NOT NULL,
    task_estimate        FLOAT,
    task_category        VARCHAR2(80),
    task_completed       SMALLINT DEFAULT 0 NOT NULL,
    task_completed_date  INT,
    task_alarm           INT DEFAULT 0 NOT NULL,
    task_private         SMALLINT DEFAULT 0 NOT NULL,
--
    PRIMARY KEY (task_id)
);

CREATE INDEX nag_tasklist_idx ON nag_tasks (task_owner);
CREATE INDEX nag_uid_idx ON nag_tasks (task_uid);
CREATE INDEX nag_start_idx ON nag_tasks (task_start);

CREATE TABLE nag_shares (
    share_id INT NOT NULL,
    share_name VARCHAR2(255) NOT NULL,
    share_owner VARCHAR2(25) NOT NULL,
    share_flags SMALLINT NOT NULL DEFAULT 0,
    perm_creator SMALLINT NOT NULL DEFAULT 0,
    perm_default SMALLINT NOT NULL DEFAULT 0,
    perm_guest SMALLINT NOT NULL DEFAULT 0,
    attribute_name VARCHAR2(255) NOT NULL,
    attribute_desc VARCHAR2(255),
    PRIMARY KEY (share_id)
);

CREATE INDEX nag_shares_share_name_idx ON nag_shares (share_name);
CREATE INDEX nag_shares_share_owner_idx ON nag_shares (share_owner);
CREATE INDEX nag_shares_perm_creator_idx ON nag_shares (perm_creator);
CREATE INDEX nag_shares_perm_default_idx ON nag_shares (perm_default);
CREATE INDEX nag_shares_perm_guest_idx ON nag_shares (perm_guest);

CREATE TABLE nag_shares_groups (
    share_id INT NOT NULL,
    group_uid VARCHAR(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX nag_shares_groups_share_id_idx ON nag_shares_groups (share_id);
CREATE INDEX nag_shares_groups_group_uid_idx ON nag_shares_groups (group_uid);
CREATE INDEX nag_shares_groups_perm_idx ON nag_shares_groups (perm);

CREATE TABLE nag_shares_users (
    share_id INT NOT NULL,
    user_uid VARCHAR2(255) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX nag_shares_users_share_id_idx ON nag_shares_users (share_id);
CREATE INDEX nag_shares_users_user_uid_idx ON nag_shares_users (user_uid);
CREATE INDEX nag_shares_users_perm_idx ON nag_shares_users (perm);
