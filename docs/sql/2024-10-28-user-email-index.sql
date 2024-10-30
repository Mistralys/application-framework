
ALTER TABLE `known_users` DROP INDEX `email`;
ALTER TABLE `known_users` CHANGE `email` `email` VARCHAR(500) NOT NULL;

DROP TABLE IF EXISTS `user_emails`;
