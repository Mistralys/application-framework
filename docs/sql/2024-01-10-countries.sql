-- Clean up the duplicate indexes on the countries table,
-- and keep only the unique index.

ALTER TABLE `countries` DROP INDEX `iso`;
ALTER TABLE `countries` ADD UNIQUE(`iso`);
