DROP TABLE IF EXISTS "pdo_todo";
CREATE TABLE "public"."pdo_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    CONSTRAINT "pdo_todo_id" PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "doctrine_dbal_todo";
CREATE TABLE "public"."doctrine_dbal_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    CONSTRAINT "doctrine_dbal_todo_id" PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "doctrine_orm_todo";
CREATE TABLE "public"."doctrine_orm_todo" (
    "id" uuid NOT NULL,
    "description_value" text NOT NULL,
    "status_value" text NOT NULL,
    PRIMARY KEY ("id")
);
