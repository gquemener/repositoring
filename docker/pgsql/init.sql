CREATE TABLE "public"."pdo_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    "version" INT NOT NULL,
    CONSTRAINT "pdo_todo_id" PRIMARY KEY ("id")
);
CREATE VIEW pdo_opened_todo (id, description) AS SELECT id, description FROM pdo_todo WHERE status = 'opened';

CREATE TABLE "public"."doctrine_dbal_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    "version" INT NOT NULL,
    CONSTRAINT "doctrine_dbal_todo_id" PRIMARY KEY ("id")
);

CREATE TABLE doctrine_orm_todo (
    id UUID NOT NULL,
    no INT DEFAULT 1 NOT NULL,
    description_value TEXT NOT NULL,
    status_value TEXT NOT NULL,
    PRIMARY KEY(id)
);

CREATE TABLE "public"."pomm_foundation_todo" (
    "id" uuid NOT NULL,
    "description" text NOT NULL,
    "status" text NOT NULL,
    "version" INT NOT NULL,
    CONSTRAINT "pomm_foundation_todo_id" PRIMARY KEY ("id")
);

CREATE TABLE event_streams (
  no BIGSERIAL,
  real_stream_name VARCHAR(150) NOT NULL,
  stream_name CHAR(41) NOT NULL,
  metadata JSONB,
  category VARCHAR(150),
  PRIMARY KEY (no),
  UNIQUE (stream_name)
);
CREATE INDEX on event_streams (category);
