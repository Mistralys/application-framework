-- -------------------------------------------------------
-- Application Framework SQL update script
-- -------------------------------------------------------
--
-- Adds the field to track uploads connected to media files
--
-- Usable for applications: SPIN, Maileditor
--
-- Modifies existing tables: YES (No possible conflicts)
--

ALTER TABLE `uploads` ADD `media_id` INT(11) UNSIGNED NULL DEFAULT NULL AFTER `upload_extension`, ADD INDEX (`media_id`);