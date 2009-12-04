-- $Horde: turba/scripts/upgrades/2.1_to_2.2_add_sql_share_tables.pgsql.sql,v 1.1.2.2 2008/04/29 19:33:23 chuck Exp $

CREATE TABLE turba_shares (
    share_id SMALLINT NOT NULL,
    share_name VARCHAR(255) NOT NULL,
    share_owner VARCHAR(32) NOT NULL,
    share_flags SMALLINT NOT NULL DEFAULT 0,
    perm_creator SMALLINT NOT NULL DEFAULT 0,
    perm_default SMALLINT NOT NULL DEFAULT 0,
    perm_guest SMALLINT NOT NULL DEFAULT 0,
    attribute_name VARCHAR(255) NOT NULL,
    attribute_desc VARCHAR(255),
    attribute_params TEXT,
    PRIMARY KEY (share_id)
);

CREATE INDEX turba_shares_share_name_idx ON turba_shares (share_name);
CREATE INDEX turba_shares_share_owner_idx ON turba_shares (share_owner);
CREATE INDEX turba_shares_perm_creator_idx ON turba_shares (perm_creator);
CREATE INDEX turba_shares_perm_default_idx ON turba_shares (perm_default);
CREATE INDEX turba_shares_perm_guest_idx ON turba_shares (perm_guest);

CREATE TABLE turba_shares_groups (
    share_id SMALLINT NOT NULL,
    group_uid SMALLINT NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX turba_shares_groups_share_id_idx ON turba_shares_groups (share_id);
CREATE INDEX turba_shares_groups_group_uid_idx ON turba_shares_groups (group_uid);
CREATE INDEX turba_shares_groups_perm_idx ON turba_shares_groups (perm);

CREATE TABLE turba_shares_users (
    share_id SMALLINT NOT NULL,
    user_uid VARCHAR(32) NOT NULL,
    perm SMALLINT NOT NULL
);

CREATE INDEX turba_shares_users_share_id_idx ON turba_shares_users (share_id);
CREATE INDEX turba_shares_users_user_uid_idx ON turba_shares_users (user_uid);
CREATE INDEX turba_shares_users_perm_idx ON turba_shares_users (perm);