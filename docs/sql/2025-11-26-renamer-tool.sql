-- ------------------------------------------------------------
-- DB RENAMER TOOL
-- ------------------------------------------------------------
--
-- This adds a new table for the DB renamer tool to index
-- search results.
--
-- Modifies existing tables...: NO
-- Adds new tables............: YES
-- Deletes existing tables....: NO
-- Affects the application....: NO
-- Possible conflicts.........: NO
--

--
-- Table structure for table `renamer_index`
--

CREATE TABLE `renamer_index` (
    `index_id` bigint(11) UNSIGNED NOT NULL,
    `column_id` varchar(80) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `hash` varchar(32) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
    `primary_values` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `renamer_index`
--
ALTER TABLE `renamer_index`
    ADD PRIMARY KEY (`index_id`),
    ADD KEY `column_id` (`column_id`),
    ADD KEY `hash` (`hash`);

--
-- AUTO_INCREMENT for table `renamer_index`
--
ALTER TABLE `renamer_index`
    MODIFY `index_id` bigint(11) UNSIGNED NOT NULL AUTO_INCREMENT;
