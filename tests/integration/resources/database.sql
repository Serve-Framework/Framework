------------------------------------------------------------
-- GROUPS
------------------------------------------------------------

DROP TABLE IF EXISTS "serve_groups";
CREATE TABLE "serve_groups" (
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "name" text NOT NULL
);

INSERT INTO "serve_groups" ("id", "name") VALUES (1, 'admin');
INSERT INTO "serve_groups" ("id", "name") VALUES (2, 'user');
INSERT INTO "serve_groups" ("id", "name") VALUES (3, 'moderator');
INSERT INTO "serve_groups" ("id", "name") VALUES (4, 'manager');

------------------------------------------------------------
-- USERS
------------------------------------------------------------

DROP TABLE IF EXISTS "serve_users";
CREATE TABLE serve_users(
  "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
  "group_id" integer,
  "created_at" text NOT NULL,
  "username" text NOT NULL,
  "email" text NOT NULL,
  FOREIGN KEY(group_id) REFERENCES serve_groups(id)
);

INSERT INTO "serve_users" ("id", "group_id", "created_at", "username", "email") VALUES (1, 1, '2014-04-30 14:40:01', 'foo', 'foo@example.org');
INSERT INTO "serve_users" ("id", "group_id", "created_at", "username", "email") VALUES (2, 1, '2014-04-30 14:02:43', 'bar', 'bar@example.org');
INSERT INTO "serve_users" ("id", "group_id", "created_at", "username", "email") VALUES (3, 2, '2014-04-30 14:12:43', 'baz', 'baz@example.org');

