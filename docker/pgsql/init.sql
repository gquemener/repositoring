CREATE TABLE "public"."pdo_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    CONSTRAINT "pdo_todo_id" PRIMARY KEY ("id")
);

CREATE TABLE "public"."doctrine_dbal_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    CONSTRAINT "doctrine_dbal_todo_id" PRIMARY KEY ("id")
);

CREATE TABLE doctrine_orm_todo (
    id UUID NOT NULL,
    version INT DEFAULT 1 NOT NULL,
    description_value TEXT NOT NULL,
    status_value TEXT NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE "public"."pomm_foundation_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    CONSTRAINT "pomm_foundation_todo_id" PRIMARY KEY ("id")
);

