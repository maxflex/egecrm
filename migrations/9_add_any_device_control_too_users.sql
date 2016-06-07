ALTER TABLE `users` ADD COLUMN `any_device_access` TINYINT(1) NOT NULL DEFAULT '0';

update users set any_device_access = 1 where id in (1, 69, 102);