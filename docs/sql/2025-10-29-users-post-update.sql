-- ------------------------------------------------------------
-- USER MANAGEMENT UPDATES
-- ------------------------------------------------------------
--
-- This removes the default value for the email_md5 field
-- once the field has been populated.
--
-- Modifies existing tables...: YES
-- Adds new tables............: NO
-- Deletes existing tables....: NO
-- Affects the application....: NO
-- Possible conflicts.........: NO
--

START TRANSACTION;

ALTER TABLE `known_users`
    CHANGE `email_md5` `email_md5` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

COMMIT;
