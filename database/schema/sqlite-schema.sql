CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "name" varchar not null,
  "email" varchar not null,
  "email_verified_at" datetime,
  "password" varchar not null,
  "remember_token" varchar,
  "created_at" datetime,
  "updated_at" datetime,
  "status" varchar check("status" in('active', 'pending', 'inactive')) not null default 'pending',
  "role" varchar check("role" in('admin', 'user')) not null default 'user',
  "deactivation_reason" text,
  "is_confirmed" tinyint(1) not null default '0'
);
CREATE UNIQUE INDEX "users_email_unique" on "users"("email");
CREATE TABLE IF NOT EXISTS "password_reset_tokens"(
  "email" varchar not null,
  "token" varchar not null,
  "created_at" datetime,
  primary key("email")
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "evaluacion_criterio"(
  "id" integer primary key autoincrement not null,
  "evaluacion_id" integer not null,
  "criterio_id" integer not null,
  "calificacion" numeric not null,
  "calificacion_ponderada" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("evaluacion_id") references "evaluaciones"("id") on delete cascade,
  foreign key("criterio_id") references "criterios"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "evaluacion_detalles"(
  "id" integer primary key autoincrement not null,
  "evaluacion_id" integer not null,
  "alumno_id" integer not null,
  "promedio_final" numeric not null default '0',
  "observaciones" text,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("evaluacion_id") references "evaluaciones"("id") on delete cascade,
  foreign key("alumno_id") references "alumnos"("id") on delete cascade
);
CREATE UNIQUE INDEX "evaluacion_detalles_evaluacion_id_alumno_id_unique" on "evaluacion_detalles"(
  "evaluacion_id",
  "alumno_id"
);
CREATE TABLE IF NOT EXISTS "evaluacion_detalle_criterio"(
  "id" integer primary key autoincrement not null,
  "evaluacion_detalle_id" integer not null,
  "criterio_id" integer not null,
  "calificacion" numeric not null,
  "calificacion_ponderada" numeric not null,
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("evaluacion_detalle_id") references "evaluacion_detalles"("id") on delete cascade,
  foreign key("criterio_id") references "criterios"("id") on delete cascade
);
CREATE UNIQUE INDEX "evaluacion_detalle_criterio_evaluacion_detalle_id_criterio_id_unique" on "evaluacion_detalle_criterio"(
  "evaluacion_detalle_id",
  "criterio_id"
);
CREATE TABLE IF NOT EXISTS "grupos"(
  "id" integer primary key autoincrement not null,
  "nombre" varchar not null,
  "descripcion" text,
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "user_id" integer,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "campo_formativos"(
  "id" integer primary key autoincrement not null,
  "nombre" varchar not null,
  "descripcion" text,
  "created_at" datetime,
  "updated_at" datetime,
  "user_id" integer,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "evaluaciones"(
  "id" integer primary key autoincrement not null,
  "campo_formativo_id" integer not null,
  "titulo" varchar not null,
  "descripcion" text,
  "fecha_evaluacion" date,
  "is_draft" tinyint(1) not null default('1'),
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "user_id" integer,
  foreign key("campo_formativo_id") references campo_formativos("id") on delete cascade on update no action,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "criterios"(
  "id" integer primary key autoincrement not null,
  "nombre" varchar not null,
  "porcentaje" numeric not null,
  "descripcion" text,
  "campo_formativo_id" integer not null,
  "created_at" datetime,
  "updated_at" datetime,
  "orden" integer not null default('0'),
  "user_id" integer,
  foreign key("campo_formativo_id") references campo_formativos("id") on delete cascade on update no action,
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "alumnos"(
  "id" integer primary key autoincrement not null,
  "nombre" varchar not null,
  "apellido_paterno" varchar not null,
  "apellido_materno" varchar not null,
  "grupo_id" integer,
  "curp" varchar,
  "fecha_nacimiento" date,
  "genero" varchar check("genero" in('masculino', 'femenino', 'otro')),
  "estado" varchar not null default('activo'),
  "created_at" datetime,
  "updated_at" datetime,
  "deleted_at" datetime,
  "user_id" integer,
  "tutor_nombre" varchar,
  "tutor_telefono" varchar,
  "tutor_email" varchar,
  "direccion" text,
  "telefono_emergencia" varchar,
  "alergias" text,
  "observaciones" text,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("grupo_id") references grupos("id") on delete set null on update no action
);
CREATE UNIQUE INDEX "alumnos_curp_unique" on "alumnos"("curp");

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2025_03_19_072934_create_campo_formativos_table',1);
INSERT INTO migrations VALUES(5,'2025_03_19_073314_create_criterios_table',1);
INSERT INTO migrations VALUES(6,'2025_03_19_082324_create_alumnos_table',1);
INSERT INTO migrations VALUES(7,'2025_03_19_083118_create_evaluacion_criterio_table',1);
INSERT INTO migrations VALUES(8,'2025_03_19_083118_create_evaluaciones_table',1);
INSERT INTO migrations VALUES(9,'2025_03_19_085049_create_campos_formativos_table',1);
INSERT INTO migrations VALUES(10,'2025_03_19_091135_create_grupos_table',1);
INSERT INTO migrations VALUES(11,'2025_03_19_221820_update_alumnos_table_make_curp_nullable',1);
INSERT INTO migrations VALUES(12,'2025_03_19_222451_add_orden_to_criterios_table',1);
INSERT INTO migrations VALUES(13,'2025_03_19_222916_drop_duplicate_campos_formativos_table',1);
INSERT INTO migrations VALUES(14,'2025_03_19_223333_fix_evaluaciones_foreign_key',1);
INSERT INTO migrations VALUES(15,'2025_03_19_223439_recreate_evaluaciones_table',1);
INSERT INTO migrations VALUES(16,'2025_03_19_223501_recreate_evaluacion_criterio_table',1);
INSERT INTO migrations VALUES(17,'2025_03_19_225524_update_evaluaciones_table',1);
INSERT INTO migrations VALUES(18,'2025_03_19_225530_create_evaluacion_detalles_table',1);
INSERT INTO migrations VALUES(19,'2025_03_19_225536_create_evaluacion_detalle_criterio_table',1);
INSERT INTO migrations VALUES(20,'2025_03_20_013955_add_user_id_to_tables',1);
INSERT INTO migrations VALUES(22,'2025_03_20_022708_add_status_and_role_to_users_table',2);
