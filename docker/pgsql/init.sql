DROP TABLE IF EXISTS "pdo_todo";
CREATE TABLE "public"."pdo_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    CONSTRAINT "pdo_todo_id" PRIMARY KEY ("id")
);
