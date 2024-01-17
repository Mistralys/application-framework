--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
`tag_id` int(11) UNSIGNED NOT NULL,
`label` varchar(160) NOT NULL,
`parent_tag_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
ADD PRIMARY KEY (`tag_id`),
ADD KEY `label` (`label`),
ADD KEY `parent_tag_id` (`parent_tag_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
MODIFY `tag_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT2;