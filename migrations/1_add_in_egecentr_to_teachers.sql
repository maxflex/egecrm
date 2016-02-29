ALTER TABLE  `teachers` ADD `in_egecentr` BOOLEAN NOT NULL DEFAULT FALSE;
update teachers set in_egecentr=1 where id <=133;