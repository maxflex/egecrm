ALTER TABLE `users`	ADD COLUMN `show_phone_calls` TINYINT(1) NULL DEFAULT '0' AFTER `banned`;
UPDATE `users` SET `show_phone_call` = 1 WHERE `type` = 'USER'