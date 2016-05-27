  ALTER TABLE users
  ADD COLUMN show_users TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN show_summary TINYINT(1) NOT NULL DEFAULT 0;

  UPDATE users SET show_users = 1 WHERE id in (1,69, 102);
  UPDATE users SET show_summary = 1 WHERE id in (1,69, 102);