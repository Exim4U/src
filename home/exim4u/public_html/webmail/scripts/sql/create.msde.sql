-- $Horde: horde/scripts/sql/create.msde.sql,v 1.1.2.20 2009/06/10 16:45:28 jan Exp $

USE master
GO

CREATE DATABASE horde
GO

EXEC sp_addlogin 'horde', 'horde_mgr', 'horde'
GO

USE horde
GO

EXEC sp_grantdbaccess 'horde'
GO

CREATE TABLE horde_users (
    user_uid VARCHAR(255) NOT NULL,
    user_pass VARCHAR(255) NOT NULL,
    user_soft_expiration_date INT,
    user_hard_expiration_date INT,

    PRIMARY KEY (user_uid)
)
GO

CREATE TABLE horde_signups (
    user_name VARCHAR(255) NOT NULL,
    signup_date INT NOT NULL,
    signup_host VARCHAR(255) NOT NULL,
    signup_data TEXT NOT NULL,
    PRIMARY KEY (user_name)
)
GO

CREATE TABLE horde_groups (
    group_uid INT UNSIGNED NOT NULL,
    group_name VARCHAR(255) NOT NULL,
    group_parents VARCHAR(255) NOT NULL,
    group_email VARCHAR(255),
    PRIMARY KEY (group_uid)
)
GO

CREATE TABLE horde_groups_members (
    group_uid INT UNSIGNED NOT NULL,
    user_uid VARCHAR(255) NOT NULL
)
GO

CREATE INDEX group_uid_idx ON horde_groups_members (group_uid)
CREATE INDEX user_uid_idx ON horde_groups_members (user_uid)
GO

CREATE TABLE horde_perms (
    perm_id INT UNSIGNED NOT NULL,
    perm_name VARCHAR(255) NOT NULL,
    perm_parents VARCHAR(255) NOT NULL,
    perm_data TEXT,
    PRIMARY KEY (perm_id)
)
GO

CREATE TABLE horde_prefs (
    pref_uid VARCHAR(200) NOT NULL,
    pref_scope VARCHAR(16) DEFAULT '' NOT NULL,
    pref_name VARCHAR(32) NOT NULL,
    pref_value TEXT NULL,

    PRIMARY KEY (pref_uid, pref_scope, pref_name)
)
GO

CREATE INDEX pref_uid_idx ON horde_prefs (pref_uid)
CREATE INDEX pref_scope_idx ON horde_prefs (pref_scope)
GO

CREATE TABLE horde_datatree (
    datatree_id INT UNSIGNED NOT NULL,
    group_uid VARCHAR(255) NOT NULL,
    user_uid VARCHAR(255) NOT NULL,
    datatree_name VARCHAR(255) NOT NULL,
    datatree_parents VARCHAR(255) NOT NULL,
    datatree_order INT,
    datatree_data TEXT,
    datatree_serialized SMALLINT DEFAULT 0 NOT NULL,

    PRIMARY KEY (datatree_id)
)
GO

CREATE INDEX datatree_datatree_name_idx ON horde_datatree (datatree_name)
CREATE INDEX datatree_group_idx ON horde_datatree (group_uid)
CREATE INDEX datatree_user_idx ON horde_datatree (user_uid)
CREATE INDEX datatree_serialized_idx ON horde_datatree (datatree_serialized)
CREATE INDEX datatree_parents_idx ON horde_datatree (datatree_parents)
GO

CREATE TABLE horde_datatree_attributes (
    datatree_id INT UNSIGNED NOT NULL,
    attribute_name VARCHAR(255) NOT NULL,
    attribute_key VARCHAR(255) DEFAULT '' NOT NULL,
    attribute_value VARCHAR(MAX)
)
GO

CREATE INDEX datatree_attribute_idx ON horde_datatree_attributes (datatree_id)
CREATE INDEX datatree_attribute_name_idx ON horde_datatree_attributes (attribute_name)
CREATE INDEX datatree_attribute_key_idx ON horde_datatree_attributes (attribute_key)
CREATE INDEX datatree_attribute_value_idx ON horde_datatree_attributes (attribute_value)
GO

CREATE TABLE horde_tokens (
    token_address VARCHAR(100) NOT NULL,
    token_id VARCHAR(32) NOT NULL,
    token_timestamp BIGINT NOT NULL,

    PRIMARY KEY (token_address, token_id)
)
GO

CREATE TABLE horde_vfs (
    vfs_id INT UNSIGNED NOT NULL,
    vfs_type SMALLINT UNSIGNED NOT NULL,
    vfs_path VARCHAR(255) NOT NULL,
    vfs_name VARCHAR(255) NOT NULL,
    vfs_modified BIGINT NOT NULL,
    vfs_owner VARCHAR(255) NOT NULL,
    vfs_data TEXT,

    PRIMARY KEY   (vfs_id)
)
GO

CREATE INDEX vfs_path_idx ON horde_vfs (vfs_path)
CREATE INDEX vfs_name_idx ON horde_vfs (vfs_name)
GO

CREATE TABLE horde_histories (
    history_id       INT UNSIGNED NOT NULL,
    object_uid       VARCHAR(255) NOT NULL,
    history_action   VARCHAR(32) NOT NULL,
    history_ts       BIGINT NOT NULL,
    history_desc     TEXT,
    history_who      VARCHAR(255),
    history_extra    TEXT,

    PRIMARY KEY (history_id)
)
GO

CREATE INDEX history_action_idx ON horde_histories (history_action)
CREATE INDEX history_ts_idx ON horde_histories (history_ts)
CREATE INDEX history_uid_idx ON horde_histories (object_uid)
GO

CREATE TABLE horde_syncml_map (
    syncml_syncpartner VARCHAR(255) NOT NULL,
    syncml_db          VARCHAR(255) NOT NULL,
    syncml_uid         VARCHAR(255) NOT NULL,
    syncml_cuid        VARCHAR(255),
    syncml_suid        VARCHAR(255),
    syncml_timestamp   BIGINT
)
GO

CREATE INDEX syncml_syncpartner_idx ON horde_syncml_map (syncml_syncpartner);
CREATE INDEX syncml_db_idx ON horde_syncml_map (syncml_db);
CREATE INDEX syncml_uid_idx ON horde_syncml_map (syncml_uid);
CREATE INDEX syncml_cuid_idx ON horde_syncml_map (syncml_cuid);
CREATE INDEX syncml_suid_idx ON horde_syncml_map (syncml_suid);
GO

CREATE TABLE horde_syncml_anchors(
    syncml_syncpartner  VARCHAR(255) NOT NULL,
    syncml_db           VARCHAR(255) NOT NULL,
    syncml_uid          VARCHAR(255) NOT NULL,
    syncml_clientanchor VARCHAR(255),
    syncml_serveranchor VARCHAR(255)
);
GO

CREATE INDEX syncml_anchors_syncpartner_idx ON horde_syncml_anchors (syncml_syncpartner);
CREATE INDEX syncml_anchors_db_idx ON horde_syncml_anchors (syncml_db);
CREATE INDEX syncml_anchors_uid_idx ON horde_syncml_anchors (syncml_uid);
GO

CREATE TABLE horde_alarms (
    alarm_id        VARCHAR(255) NOT NULL,
    alarm_uid       VARCHAR(255),
    alarm_start     DATETIME NOT NULL,
    alarm_end       DATETIME,
    alarm_methods   VARCHAR(255),
    alarm_params    TEXT,
    alarm_title     VARCHAR(255) NOT NULL,
    alarm_text      TEXT,
    alarm_snooze    DATETIME,
    alarm_dismissed SMALLINT DEFAULT 0 NOT NULL,
    alarm_internal  TEXT
);
GO

CREATE INDEX alarm_id_idx ON horde_alarms (alarm_id);
CREATE INDEX alarm_user_idx ON horde_alarms (alarm_uid);
CREATE INDEX alarm_start_idx ON horde_alarms (alarm_start);
CREATE INDEX alarm_end_idx ON horde_alarms (alarm_end);
CREATE INDEX alarm_snooze_idx ON horde_alarms (alarm_snooze);
CREATE INDEX alarm_dismissed_idx ON horde_alarms (alarm_dismissed);
GO

CREATE TABLE horde_cache (
    cache_id          VARCHAR(32) NOT NULL,
    cache_timestamp   BIGINT NOT NULL,
    cache_expiration  BIGINT NOT NULL,
    cache_data        TEXT,

    PRIMARY KEY  (cache_id)
);
GO

CREATE TABLE horde_locks (
    lock_id                  VARCHAR(36) NOT NULL,
    lock_owner               VARCHAR(32) NOT NULL,
    lock_scope               VARCHAR(32) NOT NULL,
    lock_principal           VARCHAR(255) NOT NULL,
    lock_origin_timestamp    BIGINT NOT NULL,
    lock_update_timestamp    BIGINT NOT NULL,
    lock_expiry_timestamp    BIGINT NOT NULL,
    lock_type                SMALLINT UNSIGNED NOT NULL,

    PRIMARY KEY (lock_id)
);
GO
