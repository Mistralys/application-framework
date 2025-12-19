
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE `app_sets` (
    `app_set_id` int(11) UNSIGNED NOT NULL,
    `alias` varchar(160) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `is_active` enum('yes','no') NOT NULL DEFAULT 'no',
    `label` varchar(180) NOT NULL,
    `description` text NOT NULL DEFAULT '',
    `default_url_name` varchar(80) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `enabled_url_names` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `app_sets`
    ADD PRIMARY KEY (`app_set_id`),
    ADD KEY `alias` (`alias`),
    ADD KEY `label` (`label`),
    ADD KEY `is_active` (`is_active`),
    ADD KEY `default_url_name` (`default_url_name`);

ALTER TABLE `app_sets`
    MODIFY `app_set_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;
