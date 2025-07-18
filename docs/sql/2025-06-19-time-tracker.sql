-- ------------------------------------------------------------
-- TIME SPAN MANAGEMENT TABLE
-- ------------------------------------------------------------
--

START TRANSACTION;

ALTER TABLE `time_tracker_entries` ADD `ticket_url` TEXT NOT NULL DEFAULT '' AFTER `ticket`;

CREATE TABLE `time_tracker_time_spans` (
    `time_span_id` int(11) UNSIGNED NOT NULL,
    `user_id` int(11) UNSIGNED NOT NULL,
    `type` varchar(40) NOT NULL,
    `date_start` date NOT NULL,
    `date_end` date NOT NULL,
    `days` int(11) UNSIGNED NOT NULL,
    `processed` enum('yes','no') NOT NULL DEFAULT 'no',
    `comments` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `time_tracker_time_spans`
    ADD PRIMARY KEY (`time_span_id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `date_end` (`date_end`),
    ADD KEY `date_start` (`date_start`),
    ADD KEY `processed` (`processed`),
    ADD KEY `type` (`type`),
    ADD KEY `days` (`days`);

ALTER TABLE `time_tracker_time_spans`
    MODIFY `time_span_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

COMMIT;
