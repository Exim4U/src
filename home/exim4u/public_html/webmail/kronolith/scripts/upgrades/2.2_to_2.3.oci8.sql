ALTER TABLE kronolith_shares CHANGE share_owner share_owner VARCHAR2(255);
ALTER TABLE kronolith_shares_users CHANGE user_uid user_uid VARCHAR2(255);
ALTER TABLE kronolith_shares_groups CHANGE group_uid group_uid VARCHAR2(255);
