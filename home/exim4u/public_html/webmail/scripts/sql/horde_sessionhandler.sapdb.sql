-- $Horde: horde/scripts/sql/horde_sessionhandler.sapdb.sql,v 1.1.10.1 2009/02/14 04:43:47 chuck Exp $

CREATE TABLE horde_sessionhandler (
    session_id             VARCHAR(32) NOT NULL,
    session_lastmodified   INT NOT NULL,
    session_data           LONG BYTE,
    PRIMARY KEY (session_id)
)

CREATE INDEX session_lastmodified_idx ON horde_sessionhandler (session_lastmodified);
