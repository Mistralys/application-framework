-- --------------------------------------------------------

--
-- Media table adjustments
--

ALTER TABLE `media`
ADD `file_size` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `media_extension`,
ADD `keywords` VARCHAR(500) NOT NULL DEFAULT '' AFTER `file_size`,
ADD `description` VARCHAR(1200) NOT NULL DEFAULT '' AFTER `keywords`,
ADD INDEX (`file_size`);

-- --------------------------------------------------------

--
-- Table structure for table `app_news`
--

CREATE TABLE `app_news` (
`news_id` int(11) UNSIGNED NOT NULL,
`parent_news_id` int(11) UNSIGNED DEFAULT NULL,
`news_type` varchar(60) NOT NULL,
`label` varchar(120) NOT NULL,
`author` int(11) UNSIGNED NOT NULL,
`locale` varchar(5) NOT NULL,
`status` varchar(20) NOT NULL DEFAULT 'draft',
`synopsis` text NOT NULL DEFAULT '',
`article` mediumtext NOT NULL DEFAULT '',
`date_created` datetime NOT NULL,
`date_modified` datetime NOT NULL,
`criticality` varchar(60) DEFAULT NULL COMMENT 'Used for alerts.',
`scheduled_from_date` datetime DEFAULT NULL,
`scheduled_to_date` datetime DEFAULT NULL,
`requires_receipt` enum('yes','no') NOT NULL DEFAULT 'no' COMMENT 'Used for alerts.',
`views` int(11) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_categories`
--

CREATE TABLE `app_news_categories` (
`news_category_id` int(11) UNSIGNED NOT NULL,
`label` varchar(160) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_entry_categories`
--

CREATE TABLE `app_news_entry_categories` (
`news_id` int(11) UNSIGNED NOT NULL,
`news_category_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_reactions`
--

CREATE TABLE `app_news_reactions` (
`reaction_id` int(11) UNSIGNED NOT NULL,
`label` varchar(60) NOT NULL,
`emoji` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_related`
--

CREATE TABLE `app_news_related` (
`news_id` int(11) UNSIGNED NOT NULL,
`related_news_id` int(11) UNSIGNED NOT NULL,
`relation_type` varchar(160) NOT NULL,
`relation_params` text NOT NULL COMMENT 'JSON configuration.'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_user_reactions`
--

CREATE TABLE `app_news_user_reactions` (
`news_id` int(11) UNSIGNED NOT NULL,
`user_id` int(11) UNSIGNED NOT NULL,
`reaction_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `app_news_user_receipts`
--

CREATE TABLE `app_news_user_receipts` (
`news_id` int(11) UNSIGNED NOT NULL,
`user_id` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_news`
--
ALTER TABLE `app_news`
ADD PRIMARY KEY (`news_id`),
ADD KEY `label` (`label`),
ADD KEY `date_created` (`date_created`),
ADD KEY `criticality` (`criticality`),
ADD KEY `visible_from_date` (`scheduled_from_date`),
ADD KEY `visible_to_date` (`scheduled_to_date`),
ADD KEY `dismissable` (`requires_receipt`),
ADD KEY `author` (`author`),
ADD KEY `date_modified` (`date_modified`),
ADD KEY `parent_news_id` (`parent_news_id`),
ADD KEY `views` (`views`),
ADD KEY `status` (`status`),
ADD KEY `news_type` (`news_type`),
ADD KEY `locale` (`locale`);

--
-- Indexes for table `app_news_categories`
--
ALTER TABLE `app_news_categories`
ADD PRIMARY KEY (`news_category_id`),
ADD KEY `label` (`label`);

--
-- Indexes for table `app_news_entry_categories`
--
ALTER TABLE `app_news_entry_categories`
ADD PRIMARY KEY (`news_id`,`news_category_id`),
ADD KEY `news_id` (`news_id`),
ADD KEY `news_category_id` (`news_category_id`);

--
-- Indexes for table `app_news_reactions`
--
ALTER TABLE `app_news_reactions`
ADD PRIMARY KEY (`reaction_id`),
ADD KEY `label` (`label`),
ADD KEY `emoji` (`emoji`);

--
-- Indexes for table `app_news_related`
--
ALTER TABLE `app_news_related`
ADD PRIMARY KEY (`news_id`,`related_news_id`,`relation_type`),
ADD KEY `news_id` (`news_id`),
ADD KEY `related_news_id` (`related_news_id`),
ADD KEY `relation_type` (`relation_type`);

--
-- Indexes for table `app_news_user_reactions`
--
ALTER TABLE `app_news_user_reactions`
ADD PRIMARY KEY (`news_id`,`user_id`,`reaction_id`),
ADD KEY `user_id` (`user_id`),
ADD KEY `reaction_id` (`reaction_id`);

--
-- Indexes for table `app_news_user_receipts`
--
ALTER TABLE `app_news_user_receipts`
ADD PRIMARY KEY (`news_id`,`user_id`),
ADD KEY `news_id` (`news_id`),
ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_news`
--
ALTER TABLE `app_news`
MODIFY `news_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_news_categories`
--
ALTER TABLE `app_news_categories`
MODIFY `news_category_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `app_news_reactions`
--
ALTER TABLE `app_news_reactions`
MODIFY `reaction_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `app_news`
--
ALTER TABLE `app_news`
ADD CONSTRAINT `app_news_ibfk_1` FOREIGN KEY (`author`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `app_news_ibfk_2` FOREIGN KEY (`parent_news_id`) REFERENCES `app_news` (`news_id`) ON DELETE SET NULL ON UPDATE SET NULL;

--
-- Constraints for table `app_news_entry_categories`
--
ALTER TABLE `app_news_entry_categories`
ADD CONSTRAINT `app_news_entry_categories_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `app_news_entry_categories_ibfk_2` FOREIGN KEY (`news_category_id`) REFERENCES `app_news_categories` (`news_category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_news_related`
--
ALTER TABLE `app_news_related`
ADD CONSTRAINT `app_news_related_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `app_news_related_ibfk_2` FOREIGN KEY (`related_news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_news_user_reactions`
--
ALTER TABLE `app_news_user_reactions`
ADD CONSTRAINT `app_news_user_reactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `app_news_user_reactions_ibfk_2` FOREIGN KEY (`news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `app_news_user_reactions_ibfk_3` FOREIGN KEY (`reaction_id`) REFERENCES `app_news_reactions` (`reaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `app_news_user_receipts`
--
ALTER TABLE `app_news_user_receipts`
ADD CONSTRAINT `app_news_user_receipts_ibfk_1` FOREIGN KEY (`news_id`) REFERENCES `app_news` (`news_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `app_news_user_receipts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `known_users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
