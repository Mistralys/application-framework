-- Add missing indexes in the users table.
ALTER TABLE `known_users` ADD INDEX(`firstname`);
ALTER TABLE `known_users` ADD INDEX(`lastname`);

-- Ensure that the two system users have unique email addresses,
-- and foreign IDs. They are currently the only ones with duplicate data.
UPDATE `known_users` SET `email` = 'system@appframework.system' WHERE `known_users`.`user_id` = 1;
UPDATE `known_users` SET `email` = 'dummy@appframework.system' WHERE `known_users`.`user_id` = 2;
UPDATE `known_users` SET `foreign_id` = '__system' WHERE `known_users`.`user_id` = 1;
UPDATE `known_users` SET `foreign_id` = '__dummy' WHERE `known_users`.`user_id` = 2;

-- Now we can add the missing unique keys.
ALTER TABLE `known_users` ADD UNIQUE(`email`);
ALTER TABLE `known_users` ADD UNIQUE(`foreign_id`);


--
-- Table structure for table `user_emails`
--

CREATE TABLE `user_emails` (
   `user_id` int(11) UNSIGNED NOT NULL,
   `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


--
-- Indexes for table `user_emails`
--
ALTER TABLE `user_emails`
    ADD PRIMARY KEY (`user_id`,`email`),
    ADD UNIQUE KEY `email_2` (`email`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `email` (`email`);


--
-- Constraints for table `user_emails`
--
ALTER TABLE `user_emails`
    ADD CONSTRAINT `user_emails_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
