
ALTER TABLE `app_messagelog` CHANGE `type` `type` VARCHAR(60) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `app_news` CHANGE `news_type` `news_type` VARCHAR(60) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `app_news` CHANGE `locale` `locale` VARCHAR(5) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `app_news` CHANGE `status` `status` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'draft';

ALTER TABLE `app_news_related` CHANGE `relation_type` `relation_type` VARCHAR(160) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `app_ratings_screens` CHANGE `hash` `hash` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `app_settings` CHANGE `data_key` `data_key` VARCHAR(80) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `countries` CHANGE `iso` `iso` VARCHAR(2) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `custom_properties_data` CHANGE `owner_type` `owner_type` VARCHAR(250) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `custom_properties_data` CHANGE `owner_key` `owner_key` VARCHAR(250) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `custom_properties_data` CHANGE `name` `name` VARCHAR(180) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `custom_properties_presets` CHANGE `owner_type` `owner_type` VARCHAR(250) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `feedback` CHANGE `feedback_scope` `feedback_scope` VARCHAR(40) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL DEFAULT 'application';
ALTER TABLE `feedback` CHANGE `feedback_type` `feedback_type` VARCHAR(40) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `known_users` DROP INDEX `email`;
ALTER TABLE `known_users` CHANGE `email` `email` VARCHAR(500) NOT NULL;

ALTER TABLE `media` CHANGE `media_type` `media_type` VARCHAR(100) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `media` CHANGE `media_extension` `media_extension` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `media_configurations` CHANGE `type_id` `type_id` VARCHAR(60) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `media_configurations` CHANGE `config_key` `config_key` VARCHAR(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `revisionables_changelog` CHANGE `changelog_type` `changelog_type` VARCHAR(160) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `revisionables_revisions` CHANGE `alias` `alias` VARCHAR(160) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `revisionables_revisions_data` CHANGE `data_key` `data_key` VARCHAR(300) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `tags` CHANGE `sort_type` `sort_type` VARCHAR(60) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;
ALTER TABLE `tags_registry` CHANGE `registry_key` `registry_key` VARCHAR(180) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `test_records` CHANGE `alias` `alias` VARCHAR(160) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

ALTER TABLE `user_settings` CHANGE `setting_name` `setting_name` VARCHAR(180) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL;

DROP TABLE IF EXISTS `user_emails`;
