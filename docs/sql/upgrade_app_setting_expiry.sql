-- -------------------------------------------------------
-- Application Framework SQL update script
-- -------------------------------------------------------
--
-- Adds the field to track expiry dates of application settings
--
-- Usable for applications: Maileditor
--
-- Modifies existing tables: YES (No possible conflicts)
--

ALTER TABLE `app_settings` ADD `expiry_date` datetime null;

-- Allow email aliases for the same InsideNet ID
ALTER TABLE `known_users` DROP INDEX `foreign_id_2`;