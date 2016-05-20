ALTER TABLE `users`
ADD COLUMN `banned_egerep` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN `can_approve_tutor` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN `is_dev` TINYINT(1) not NULL DEFAULT 0,
ADD COLUMN `show_tasks` TINYINT(1) not NULL DEFAULT 0,
ADD COLUMN `is_seo` TINYINT(1) not NULL DEFAULT 0;

update users set is_dev = 1 where id in (69,  102);
update users set is_dev = 1 where id = 99;
update users set can_approve_tutor = 1 where id in (56, 61, 79, 1368);
update users set show_tasks = 1 where id in (1, 69, 93, 104, 102);
