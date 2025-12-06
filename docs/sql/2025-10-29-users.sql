-- ------------------------------------------------------------
-- USER MANAGEMENT UPDATES
-- ------------------------------------------------------------
--
-- This will add several new fields to the known_users table
-- to support the new user management features.
--
-- NOTE: The email_md5 field will be populated separately,
-- and will later be modified to not have a default value.
-- For now, it has a default to avoid issues during the update.
--
-- Modifies existing tables...: YES
-- Adds new tables............: NO
-- Deletes existing tables....: NO
-- Affects the application....: NO
-- Possible conflicts.........: NO
--

START TRANSACTION;

ALTER TABLE `known_users`
    ADD `foreign_nickname` VARCHAR(180) NULL DEFAULT NULL AFTER `foreign_id`, ADD INDEX (`foreign_nickname`);

ALTER TABLE `known_users`
    ADD `nickname` VARCHAR(180) NULL DEFAULT NULL AFTER `lastname`, ADD INDEX (`nickname`);

ALTER TABLE `known_users`
    ADD `email_md5` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT '' AFTER `email`, ADD INDEX (`email_md5`);

ALTER TABLE `known_users`
    ADD `date_registered` DATETIME NOT NULL DEFAULT current_timestamp() AFTER `email_md5`, ADD INDEX (`date_registered`);

COMMIT;
