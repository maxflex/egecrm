ALTER TABLE `users`
ADD COLUMN `banned_egerep` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN `approve_tutor` TINYINT(1) NULL DEFAULT 1,
ADD COLUMN `dev` TINYINT(1) NULL DEFAULT 0;

update users set dev = 1 where id in (69,  102);