-- -------------------------------------------------------------
-- TablePlus 6.8.2(656)
--
-- https://tableplus.com/
--
-- Database: olo_admin
-- Generation Time: 2026-03-24 20:50:19.0960
-- -------------------------------------------------------------


DROP TABLE IF EXISTS "public"."migrations";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS migrations_id_seq;

-- Table Definition
CREATE TABLE "public"."migrations" (
    "id" int4 NOT NULL DEFAULT nextval('migrations_id_seq'::regclass),
    "migration" varchar(255) NOT NULL,
    "batch" int4 NOT NULL,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."cache";
-- Table Definition
CREATE TABLE "public"."cache" (
    "key" varchar(255) NOT NULL,
    "value" text NOT NULL,
    "expiration" int4 NOT NULL,
    PRIMARY KEY ("key")
);

DROP TABLE IF EXISTS "public"."cache_locks";
-- Table Definition
CREATE TABLE "public"."cache_locks" (
    "key" varchar(255) NOT NULL,
    "owner" varchar(255) NOT NULL,
    "expiration" int4 NOT NULL,
    PRIMARY KEY ("key")
);

DROP TABLE IF EXISTS "public"."jobs";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS jobs_id_seq;

-- Table Definition
CREATE TABLE "public"."jobs" (
    "id" int8 NOT NULL DEFAULT nextval('jobs_id_seq'::regclass),
    "queue" varchar(255) NOT NULL,
    "payload" text NOT NULL,
    "attempts" int2 NOT NULL,
    "reserved_at" int4,
    "available_at" int4 NOT NULL,
    "created_at" int4 NOT NULL,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."job_batches";
-- Table Definition
CREATE TABLE "public"."job_batches" (
    "id" varchar(255) NOT NULL,
    "name" varchar(255) NOT NULL,
    "total_jobs" int4 NOT NULL,
    "pending_jobs" int4 NOT NULL,
    "failed_jobs" int4 NOT NULL,
    "failed_job_ids" text NOT NULL,
    "options" text,
    "cancelled_at" int4,
    "created_at" int4 NOT NULL,
    "finished_at" int4,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."failed_jobs";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS failed_jobs_id_seq;

-- Table Definition
CREATE TABLE "public"."failed_jobs" (
    "id" int8 NOT NULL DEFAULT nextval('failed_jobs_id_seq'::regclass),
    "uuid" varchar(255) NOT NULL,
    "connection" text NOT NULL,
    "queue" text NOT NULL,
    "payload" text NOT NULL,
    "exception" text NOT NULL,
    "failed_at" timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."password_reset_tokens";
-- Table Definition
CREATE TABLE "public"."password_reset_tokens" (
    "email" varchar(255) NOT NULL,
    "token" varchar(255) NOT NULL,
    "created_at" timestamp(0),
    PRIMARY KEY ("email")
);

DROP TABLE IF EXISTS "public"."purchase_order_comments";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS purchase_order_comments_id_seq;

-- Table Definition
CREATE TABLE "public"."purchase_order_comments" (
    "id" int8 NOT NULL DEFAULT nextval('purchase_order_comments_id_seq'::regclass),
    "purchase_order_id" int8 NOT NULL,
    "user_id" int8 NOT NULL,
    "comment" text NOT NULL,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "operacion" varchar(255),
    "deleted_at" timestamp(0),
    "action_type" varchar(255) NOT NULL DEFAULT 'comment'::character varying,
    "old_values" json,
    "new_values" json,
    "ip_address" varchar(255),
    "user_agent" text,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."company_user";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS company_user_id_seq;

-- Table Definition
CREATE TABLE "public"."company_user" (
    "id" int8 NOT NULL DEFAULT nextval('company_user_id_seq'::regclass),
    "company_id" int8 NOT NULL,
    "user_id" int8 NOT NULL,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."user_auth_events";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS user_auth_events_id_seq;

-- Table Definition
CREATE TABLE "public"."user_auth_events" (
    "id" int8 NOT NULL DEFAULT nextval('user_auth_events_id_seq'::regclass),
    "user_id" int8,
    "event_type" varchar(255) NOT NULL,
    "ip_address" varchar(45),
    "user_agent" text,
    "email" varchar(255),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."porth_cargos";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS porth_cargos_id_seq;

-- Table Definition
CREATE TABLE "public"."porth_cargos" (
    "id" int8 NOT NULL DEFAULT nextval('porth_cargos_id_seq'::regclass),
    "shipping_document_id" int8 NOT NULL,
    "porth_cargo_id" varchar(255),
    "type" varchar(255),
    "number" varchar(255),
    "seal" varchar(255),
    "amount" int4,
    "width" numeric(10,2),
    "height" numeric(10,2),
    "depth" numeric(10,2),
    "weight" numeric(12,3),
    "notes" text,
    "phase" varchar(255),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."porth_phases";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS porth_phases_id_seq;

-- Table Definition
CREATE TABLE "public"."porth_phases" (
    "id" int8 NOT NULL DEFAULT nextval('porth_phases_id_seq'::regclass),
    "shipping_document_id" int8 NOT NULL,
    "porth_phase_id" varchar(255),
    "name" varchar(255),
    "estimated_dates" json,
    "actual_date" timestamp(0),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."media";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS media_id_seq;

-- Table Definition
CREATE TABLE "public"."media" (
    "id" int8 NOT NULL DEFAULT nextval('media_id_seq'::regclass),
    "model_type" varchar(255) NOT NULL,
    "model_id" int8 NOT NULL,
    "uuid" uuid,
    "collection_name" varchar(255) NOT NULL,
    "name" varchar(255) NOT NULL,
    "file_name" varchar(255) NOT NULL,
    "mime_type" varchar(255),
    "disk" varchar(255) NOT NULL,
    "conversions_disk" varchar(255),
    "size" int8 NOT NULL,
    "manipulations" json NOT NULL,
    "custom_properties" json NOT NULL,
    "generated_conversions" json NOT NULL,
    "responsive_images" json NOT NULL,
    "order_column" int4,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."products";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS products_id_seq;

-- Table Definition
CREATE TABLE "public"."products" (
    "id" int8 NOT NULL DEFAULT nextval('products_id_seq'::regclass),
    "material_id" varchar(255),
    "short_text" varchar(255),
    "supplying_plant" varchar(255),
    "unit_of_measure" varchar(255),
    "plant" varchar(255),
    "vendor_name" varchar(255),
    "vendo_code" varchar(255),
    "price_per_unit" numeric(10,2),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."sessions";
-- Table Definition
CREATE TABLE "public"."sessions" (
    "id" varchar(255) NOT NULL,
    "user_id" int8,
    "ip_address" varchar(45),
    "user_agent" text,
    "payload" text NOT NULL,
    "last_activity" int4 NOT NULL,
    "device_type" varchar(255),
    "country" varchar(255),
    "is_active" bool NOT NULL DEFAULT true,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."webhook_events";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS webhook_events_id_seq;

-- Table Definition
CREATE TABLE "public"."webhook_events" (
    "id" int8 NOT NULL DEFAULT nextval('webhook_events_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "description" text,
    "is_active" bool NOT NULL DEFAULT true,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."webhook_endpoints";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS webhook_endpoints_id_seq;

-- Table Definition
CREATE TABLE "public"."webhook_endpoints" (
    "id" int8 NOT NULL DEFAULT nextval('webhook_endpoints_id_seq'::regclass),
    "event_name" varchar(255) NOT NULL,
    "url" varchar(255) NOT NULL,
    "secret" varchar(255),
    "method" varchar(255) NOT NULL DEFAULT 'POST'::character varying,
    "headers" json,
    "is_active" bool NOT NULL DEFAULT true,
    "timeout" int4 NOT NULL DEFAULT 30,
    "retry_attempts" int4 NOT NULL DEFAULT 3,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."webhook_logs";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS webhook_logs_id_seq;

-- Table Definition
CREATE TABLE "public"."webhook_logs" (
    "id" int8 NOT NULL DEFAULT nextval('webhook_logs_id_seq'::regclass),
    "webhook_endpoint_id" int8 NOT NULL,
    "event_name" varchar(255) NOT NULL,
    "payload" json NOT NULL,
    "response_status" int4,
    "response_body" text,
    "attempt" int4 NOT NULL DEFAULT 1,
    "status" varchar(255) NOT NULL DEFAULT 'pending'::character varying CHECK ((status)::text = ANY ((ARRAY['pending'::character varying, 'success'::character varying, 'failed'::character varying])::text[])),
    "error_message" text,
    "sent_at" timestamp(0),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."forecasts";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS forecasts_id_seq;

-- Table Definition
CREATE TABLE "public"."forecasts" (
    "id" int8 NOT NULL DEFAULT nextval('forecasts_id_seq'::regclass),
    "release_date" date,
    "material" varchar(255),
    "short_text" varchar(255),
    "purchase_requisition" varchar(255),
    "supplying_plant" varchar(255),
    "qty_real" numeric(20,3),
    "uom_real" varchar(255),
    "quantity_requested" numeric(20,3),
    "delivery_date" date,
    "unit_of_measure" varchar(255),
    "plant" varchar(255),
    "planned_delivery_time" int4,
    "mrp_controller" varchar(255),
    "vendor_name" varchar(255),
    "vendor_code" varchar(255),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

-- Column Comment
COMMENT ON COLUMN "public"."forecasts"."qty_real" IS 'Qty Real';
COMMENT ON COLUMN "public"."forecasts"."uom_real" IS 'UOM Real';
COMMENT ON COLUMN "public"."forecasts"."planned_delivery_time" IS 'Planned Deliv. Time in days';
COMMENT ON COLUMN "public"."forecasts"."mrp_controller" IS 'MRP Controller';

DROP TABLE IF EXISTS "public"."personal_access_tokens";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS personal_access_tokens_id_seq;

-- Table Definition
CREATE TABLE "public"."personal_access_tokens" (
    "id" int8 NOT NULL DEFAULT nextval('personal_access_tokens_id_seq'::regclass),
    "tokenable_type" varchar(255) NOT NULL,
    "tokenable_id" int8 NOT NULL,
    "name" varchar(255) NOT NULL,
    "token" varchar(64) NOT NULL,
    "abilities" text,
    "last_used_at" timestamp(0),
    "expires_at" timestamp(0),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."companies";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS companies_id_seq;

-- Table Definition
CREATE TABLE "public"."companies" (
    "id" int8 NOT NULL DEFAULT nextval('companies_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "address" varchar(255),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "country" varchar(255),
    "city" varchar(255),
    "zip" varchar(255),
    "phone" varchar(255),
    "description" text,
    "website" varchar(255),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."kanban_boards";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS kanban_boards_id_seq;

-- Table Definition
CREATE TABLE "public"."kanban_boards" (
    "id" int8 NOT NULL DEFAULT nextval('kanban_boards_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "description" text,
    "company_id" int8,
    "type" varchar(255) NOT NULL DEFAULT 'purchase_orders'::character varying,
    "is_active" bool NOT NULL DEFAULT true,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."bill_tos";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS bill_tos_id_seq;

-- Table Definition
CREATE TABLE "public"."bill_tos" (
    "id" int8 NOT NULL DEFAULT nextval('bill_tos_id_seq'::regclass),
    "company_id" int8 NOT NULL,
    "name" varchar(255) NOT NULL,
    "email" varchar(255),
    "contact_person" varchar(255),
    "address" varchar(255),
    "postal_code" varchar(255),
    "country" varchar(255),
    "state" varchar(255),
    "phone" varchar(255),
    "status" varchar(255) NOT NULL DEFAULT 'active'::character varying CHECK ((status)::text = ANY (ARRAY[('active'::character varying)::text, ('inactive'::character varying)::text])),
    "notes" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."vendors";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS vendors_id_seq;

-- Table Definition
CREATE TABLE "public"."vendors" (
    "id" int8 NOT NULL DEFAULT nextval('vendors_id_seq'::regclass),
    "company_id" int8 NOT NULL,
    "name" varchar(255) NOT NULL,
    "vendo_code" varchar(255),
    "email" varchar(255),
    "contact_person" varchar(255),
    "address" varchar(255),
    "postal_code" varchar(255),
    "country" varchar(255),
    "state" varchar(255),
    "phone" varchar(255),
    "status" varchar(255) NOT NULL DEFAULT 'active'::character varying CHECK ((status)::text = ANY (ARRAY[('active'::character varying)::text, ('inactive'::character varying)::text])),
    "notes" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."ship_tos";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS ship_tos_id_seq;

-- Table Definition
CREATE TABLE "public"."ship_tos" (
    "id" int8 NOT NULL DEFAULT nextval('ship_tos_id_seq'::regclass),
    "company_id" int8 NOT NULL,
    "name" varchar(255) NOT NULL,
    "email" varchar(255),
    "contact_person" varchar(255),
    "address" varchar(255),
    "postal_code" varchar(255),
    "country" varchar(255),
    "state" varchar(255),
    "phone" varchar(255),
    "status" varchar(255) NOT NULL DEFAULT 'active'::character varying CHECK ((status)::text = ANY (ARRAY[('active'::character varying)::text, ('inactive'::character varying)::text])),
    "notes" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."users";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS users_id_seq;

-- Table Definition
CREATE TABLE "public"."users" (
    "id" int8 NOT NULL DEFAULT nextval('users_id_seq'::regclass),
    "company_id" int8,
    "name" varchar(255) NOT NULL,
    "email" varchar(255) NOT NULL,
    "email_verified_at" timestamp(0),
    "password" varchar(255) NOT NULL,
    "remember_token" varchar(100),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "language" varchar(255) NOT NULL DEFAULT 'es_CL'::character varying,
    "time_zone" varchar(255) NOT NULL DEFAULT 'America/Santiago'::character varying,
    "date_format" varchar(255) NOT NULL DEFAULT 'DD/MM/YYYY'::character varying,
    "time_format" varchar(255) NOT NULL DEFAULT '24hrs'::character varying,
    "description" text,
    "phone" varchar(255),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."permissions";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS permissions_id_seq;

-- Table Definition
CREATE TABLE "public"."permissions" (
    "id" int8 NOT NULL DEFAULT nextval('permissions_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "guard_name" varchar(255) NOT NULL,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."model_has_permissions";
-- Table Definition
CREATE TABLE "public"."model_has_permissions" (
    "permission_id" int8 NOT NULL,
    "model_type" varchar(255) NOT NULL,
    "model_id" int8 NOT NULL,
    PRIMARY KEY ("permission_id","model_id","model_type")
);

DROP TABLE IF EXISTS "public"."roles";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS roles_id_seq;

-- Table Definition
CREATE TABLE "public"."roles" (
    "id" int8 NOT NULL DEFAULT nextval('roles_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "guard_name" varchar(255) NOT NULL,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."model_has_roles";
-- Table Definition
CREATE TABLE "public"."model_has_roles" (
    "role_id" int8 NOT NULL,
    "model_type" varchar(255) NOT NULL,
    "model_id" int8 NOT NULL,
    PRIMARY KEY ("role_id","model_id","model_type")
);

DROP TABLE IF EXISTS "public"."role_has_permissions";
-- Table Definition
CREATE TABLE "public"."role_has_permissions" (
    "permission_id" int8 NOT NULL,
    "role_id" int8 NOT NULL,
    PRIMARY KEY ("permission_id","role_id")
);

DROP TABLE IF EXISTS "public"."tracking_data_pos";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS tracking_data_pos_id_seq;

-- Table Definition
CREATE TABLE "public"."tracking_data_pos" (
    "id" int8 NOT NULL DEFAULT nextval('tracking_data_pos_id_seq'::regclass),
    "purchase_order_id" int8 NOT NULL,
    "status" varchar(255) NOT NULL CHECK ((status)::text = ANY (ARRAY[('in_transit'::character varying)::text, ('delivered'::character varying)::text, ('delayed'::character varying)::text, ('lost'::character varying)::text])),
    "location" varchar(255),
    "carrier" varchar(255),
    "tracking_number" varchar(255),
    "estimated_delivery" timestamp(0),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "deleted_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."boarding_documents";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS boarding_documents_id_seq;

-- Table Definition
CREATE TABLE "public"."boarding_documents" (
    "id" int8 NOT NULL DEFAULT nextval('boarding_documents_id_seq'::regclass),
    "purchase_order_id" int8 NOT NULL,
    "document_path" varchar(255) NOT NULL,
    "document_type" varchar(255) NOT NULL CHECK ((document_type)::text = ANY (ARRAY[('invoice'::character varying)::text, ('packing_list'::character varying)::text, ('bill_of_lading'::character varying)::text, ('other'::character varying)::text])),
    "status" varchar(255) NOT NULL DEFAULT 'pending'::character varying CHECK ((status)::text = ANY (ARRAY[('pending'::character varying)::text, ('approved'::character varying)::text, ('rejected'::character varying)::text])),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "deleted_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."kanban_statuses";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS kanban_statuses_id_seq;

-- Table Definition
CREATE TABLE "public"."kanban_statuses" (
    "id" int8 NOT NULL DEFAULT nextval('kanban_statuses_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "slug" varchar(255) NOT NULL,
    "description" text,
    "kanban_board_id" int8 NOT NULL,
    "position" int4 NOT NULL DEFAULT 0,
    "color" varchar(255) NOT NULL DEFAULT '#3490dc'::character varying,
    "is_default" bool NOT NULL DEFAULT false,
    "is_final" bool NOT NULL DEFAULT false,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "is_hidden" bool NOT NULL DEFAULT false,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."notification_preferences";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS notification_preferences_id_seq;

-- Table Definition
CREATE TABLE "public"."notification_preferences" (
    "id" int8 NOT NULL DEFAULT nextval('notification_preferences_id_seq'::regclass),
    "user_id" int8 NOT NULL,
    "notification_type_id" int8 NOT NULL,
    "enabled" bool NOT NULL DEFAULT false,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."notification_types";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS notification_types_id_seq;

-- Table Definition
CREATE TABLE "public"."notification_types" (
    "id" int8 NOT NULL DEFAULT nextval('notification_types_id_seq'::regclass),
    "key" varchar(255) NOT NULL,
    "name" varchar(255) NOT NULL,
    "category" varchar(255) NOT NULL,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "description" text,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."user_frequencies";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS user_frequencies_id_seq;

-- Table Definition
CREATE TABLE "public"."user_frequencies" (
    "id" int8 NOT NULL DEFAULT nextval('user_frequencies_id_seq'::regclass),
    "user_id" int8 NOT NULL,
    "frequency" varchar(255) NOT NULL,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."notifications";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS notifications_id_seq;

-- Table Definition
CREATE TABLE "public"."notifications" (
    "id" int8 NOT NULL DEFAULT nextval('notifications_id_seq'::regclass),
    "type" varchar(255) NOT NULL,
    "user_id" int8 NOT NULL,
    "title" varchar(255) NOT NULL,
    "message" text NOT NULL,
    "data" json,
    "read_at" timestamp(0),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."hubs";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS hubs_id_seq;

-- Table Definition
CREATE TABLE "public"."hubs" (
    "id" int8 NOT NULL DEFAULT nextval('hubs_id_seq'::regclass),
    "name" varchar(255) NOT NULL,
    "code" varchar(255) NOT NULL,
    "country" varchar(255) NOT NULL,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "documentary_cut" varchar(255),
    "zarpe" varchar(255),
    "operation_days" int4 NOT NULL DEFAULT 0,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."authorizations";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS authorizations_id_seq;

-- Table Definition
CREATE TABLE "public"."authorizations" (
    "id" int8 NOT NULL DEFAULT nextval('authorizations_id_seq'::regclass),
    "operation_id" varchar(255) NOT NULL,
    "authorizable_type" varchar(255) NOT NULL,
    "authorizable_id" int8 NOT NULL,
    "requester_id" int8 NOT NULL,
    "operation_type" varchar(255) NOT NULL,
    "status" varchar(255) NOT NULL DEFAULT 'pending'::character varying CHECK ((status)::text = ANY (ARRAY[('pending'::character varying)::text, ('approved'::character varying)::text, ('rejected'::character varying)::text])),
    "data" json,
    "authorizer_id" int8,
    "authorized_at" timestamp(0),
    "notes" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."shipping_document_comments";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS shipping_document_comments_id_seq;

-- Table Definition
CREATE TABLE "public"."shipping_document_comments" (
    "id" int8 NOT NULL DEFAULT nextval('shipping_document_comments_id_seq'::regclass),
    "shipping_document_id" int8 NOT NULL,
    "user_id" int8 NOT NULL,
    "comment" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "stage" varchar(255) DEFAULT 'shipping_document'::character varying,
    "action_type" varchar(255) NOT NULL DEFAULT 'comment'::character varying,
    "old_values" json,
    "new_values" json,
    "ip_address" varchar(255),
    "user_agent" text,
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."po_confirmation_settings";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS po_confirmation_settings_id_seq;

-- Table Definition
CREATE TABLE "public"."po_confirmation_settings" (
    "id" int8 NOT NULL DEFAULT nextval('po_confirmation_settings_id_seq'::regclass),
    "key" varchar(255) NOT NULL,
    "value" text NOT NULL,
    "type" varchar(255) NOT NULL DEFAULT 'string'::character varying,
    "group" varchar(255) NOT NULL DEFAULT 'general'::character varying,
    "description" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."purchase_order_shipping_document";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS purchase_order_shipping_document_id_seq;

-- Table Definition
CREATE TABLE "public"."purchase_order_shipping_document" (
    "id" int8 NOT NULL DEFAULT nextval('purchase_order_shipping_document_id_seq'::regclass),
    "purchase_order_id" int8 NOT NULL,
    "shipping_document_id" int8 NOT NULL,
    "notes" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "deleted_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."support_requests";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS support_requests_id_seq;

-- Table Definition
CREATE TABLE "public"."support_requests" (
    "id" int8 NOT NULL DEFAULT nextval('support_requests_id_seq'::regclass),
    "user_id" int8 NOT NULL,
    "subject" varchar(255) NOT NULL,
    "message" text NOT NULL,
    "status" varchar(255) NOT NULL DEFAULT 'pending'::character varying CHECK ((status)::text = ANY ((ARRAY['pending'::character varying, 'in_progress'::character varying, 'resolved'::character varying, 'closed'::character varying])::text[])),
    "response" text,
    "responded_by" int8,
    "responded_at" timestamp(0),
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."historical_purchase_orders";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS historical_purchase_orders_id_seq;

-- Table Definition
CREATE TABLE "public"."historical_purchase_orders" (
    "id" int8 NOT NULL DEFAULT nextval('historical_purchase_orders_id_seq'::regclass),
    "order_number" varchar(255) NOT NULL,
    "vendor_id" varchar(255),
    "vendor_name" varchar(255),
    "retail_group" varchar(255),
    "route_label" varchar(255),
    "net_total" numeric(12,2),
    "currency" varchar(3),
    "emision_date_po" date,
    "category" varchar(255),
    "mode" varchar(255),
    "mbl_number" varchar(255),
    "container_type" varchar(255),
    "container_number" varchar(255),
    "incoterms" varchar(255),
    "logistics_incoterm" varchar(255),
    "price_incoterm" varchar(255),
    "date_booking_request" date,
    "date_booking_authorized" date,
    "date_carga_po" date,
    "date_theorical_load" date,
    "carga_lista_validada" bool NOT NULL DEFAULT false,
    "dif_load_date" int4,
    "etd_initial_validated" bool NOT NULL DEFAULT false,
    "date_etd_initial" date,
    "date_etd_updated" date,
    "date_etd" date,
    "etd_dates_difference" int4,
    "eta_inicial" date,
    "date_eta_updated" date,
    "date_eta" date,
    "eta_dates_difference" int4,
    "case_number_file" varchar(255),
    "consolidator_name" varchar(255),
    "departure_port" varchar(255),
    "port_of_loading_validated" bool NOT NULL DEFAULT false,
    "arrival_port" varchar(255),
    "customs_dua" varchar(255),
    "receipt_note" text,
    "receipt_note_date" date,
    "factory_proforma_number" varchar(255),
    "cargo_invoice_number" varchar(255),
    "freight_amount" numeric(12,2),
    "service_provider" varchar(255),
    "cbm" numeric(10,3),
    "invoice" varchar(255),
    "invoice_amount" numeric(12,2),
    "factura_merca" varchar(255),
    "has_facture_merca" bool NOT NULL DEFAULT false,
    "visibility_notes" text,
    "applies_tlc" bool NOT NULL DEFAULT false,
    "comments" text,
    "apply_technical_note" bool NOT NULL DEFAULT false,
    "applies_af" bool NOT NULL DEFAULT false,
    "bonded_warehouse_enter" date,
    "bonded_warehouse_exit" date,
    "reason" varchar(255),
    "shipping_line" varchar(255),
    "forwader_date" date,
    "container_free_days" int4,
    "tariff_type" varchar(255),
    "customer_type" varchar(255),
    "trading_company" varchar(255),
    "company_id" int8,
    "status" varchar(255),
    "total_amount" numeric(12,2),
    "ensurence_type" varchar(255),
    "bill_to_id" int8,
    "kanban_status_id" int8,
    "ship_to_id" int8,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."porth_itineraries";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS porth_itineraries_id_seq;

-- Table Definition
CREATE TABLE "public"."porth_itineraries" (
    "id" int8 NOT NULL DEFAULT nextval('porth_itineraries_id_seq'::regclass),
    "shipping_document_id" int8 NOT NULL,
    "porth_id" varchar(255),
    "porth_itinerary_id" varchar(255),
    "porth_cargo_id" varchar(255),
    "phase" varchar(255),
    "name" varchar(255),
    "place" varchar(255),
    "vessel_voyage" varchar(255),
    "date" timestamp(0),
    "created_at_porth" timestamp(0),
    "updated_at_porth" timestamp(0),
    "done" bool NOT NULL DEFAULT false,
    "raw" json,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."porth_sync_runs";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS porth_sync_runs_id_seq;

-- Table Definition
CREATE TABLE "public"."porth_sync_runs" (
    "id" int8 NOT NULL DEFAULT nextval('porth_sync_runs_id_seq'::regclass),
    "trigger" varchar(50) NOT NULL DEFAULT 'manual'::character varying,
    "scope" varchar(255),
    "status" varchar(50) NOT NULL DEFAULT 'running'::character varying,
    "started_at" timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    "finished_at" timestamp(0),
    "duration_ms" int4,
    "total" int4 NOT NULL DEFAULT 0,
    "processed" int4 NOT NULL DEFAULT 0,
    "updated" int4 NOT NULL DEFAULT 0,
    "no_change" int4 NOT NULL DEFAULT 0,
    "skipped" int4 NOT NULL DEFAULT 0,
    "failed" int4 NOT NULL DEFAULT 0,
    "dry_run" int4 NOT NULL DEFAULT 0,
    "error_summary" text,
    "meta" json,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."purchase_order_product";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS purchase_order_product_id_seq;

-- Table Definition
CREATE TABLE "public"."purchase_order_product" (
    "id" int8 NOT NULL DEFAULT nextval('purchase_order_product_id_seq'::regclass),
    "purchase_order_id" int8 NOT NULL,
    "product_id" int8 NOT NULL,
    "quantity" int4 NOT NULL DEFAULT 1,
    "unit_price" numeric(10,2) NOT NULL,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "deleted_at" timestamp(0),
    PRIMARY KEY ("id")
);

DROP TABLE IF EXISTS "public"."purchase_orders";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS purchase_orders_id_seq;

-- Table Definition
CREATE TABLE "public"."purchase_orders" (
    "id" int8 NOT NULL DEFAULT nextval('purchase_orders_id_seq'::regclass),
    "company_id" int8 NOT NULL,
    "order_number" varchar(255) NOT NULL,
    "status" varchar(255) NOT NULL CHECK ((status)::text = ANY (ARRAY[('draft'::character varying)::text, ('pending'::character varying)::text, ('approved'::character varying)::text, ('shipped'::character varying)::text, ('delivered'::character varying)::text, ('cancelled'::character varying)::text])),
    "total_amount" numeric(10,2) NOT NULL DEFAULT '0'::numeric,
    "ensurence_type" varchar(255) NOT NULL CHECK ((ensurence_type)::text = ANY (ARRAY[('pending'::character varying)::text, ('applied'::character varying)::text])),
    "order_date" date,
    "currency" varchar(255),
    "incoterms" varchar(255),
    "mode" varchar(255),
    "payment_terms" varchar(255),
    "email_agent" varchar(255),
    "tracking_id" varchar(255),
    "net_total" numeric(12,2),
    "additional_cost" numeric(12,2),
    "total" numeric(12,2),
    "length" numeric(8,2),
    "width" numeric(8,2),
    "height" numeric(8,2),
    "volume" numeric(10,3),
    "weight_kg" numeric(8,2),
    "weight_lb" numeric(8,2),
    "pallet_quantity" int4,
    "pallet_quantity_real" int4,
    "bill_of_lading" varchar(255),
    "date_required_in_destination" timestamp(0),
    "date_planned_pickup" timestamp(0),
    "date_actual_pickup" timestamp(0),
    "date_estimated_hub_arrival" timestamp(0),
    "date_actual_hub_arrival" timestamp(0),
    "date_etd" timestamp(0),
    "date_atd" timestamp(0),
    "date_eta" timestamp(0),
    "date_ata" timestamp(0),
    "date_consolidation" timestamp(0),
    "release_date" timestamp(0),
    "insurance_cost" numeric(10,2),
    "ground_transport_cost_1" numeric(10,2),
    "ground_transport_cost_2" numeric(10,2),
    "cost_nationalization" numeric(10,2),
    "cost_ofr_estimated" numeric(10,2),
    "cost_ofr_real" numeric(10,2),
    "estimated_pallet_cost" numeric(10,2),
    "real_cost_estimated_po" numeric(10,2),
    "real_cost_real_po" numeric(10,2),
    "other_costs" numeric(10,2),
    "other_expenses" numeric(10,2),
    "variable_calculare_weight" numeric(10,2),
    "savings_ofr_fcl" numeric(10,2),
    "saving_pickup" numeric(10,2),
    "saving_executed" numeric(10,2),
    "saving_not_executed" numeric(10,2),
    "notes" text,
    "comments" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "kanban_status_id" int8,
    "ship_to_id" int8,
    "vendor_id" int8,
    "planned_hub_id" int8,
    "actual_hub_id" int8,
    "bill_to_id" int8,
    "material_type" json,
    "length_cm" numeric(10,2),
    "width_cm" numeric(10,2),
    "height_cm" numeric(10,2),
    "confirmation_hash" varchar(64),
    "hash_expires_at" timestamp(0),
    "confirmation_email_sent" bool NOT NULL DEFAULT false,
    "confirmation_email_sent_at" timestamp(0),
    "update_date_po" date,
    "confirm_update_date_po" bool NOT NULL DEFAULT false,
    "rejection_reason" text,
    "factory_proforma_number" varchar(255),
    "mbl_number" varchar(255),
    "container_type" varchar(255),
    "container_number" varchar(255),
    "shipping_line" varchar(255),
    "port_of_loading_validated" bool NOT NULL DEFAULT false,
    "is_dropship" bool NOT NULL DEFAULT false,
    "applies_tlc" bool NOT NULL DEFAULT false,
    "applies_af" bool NOT NULL DEFAULT false,
    "has_facture_merca" bool NOT NULL DEFAULT false,
    "used_rate_ok" bool NOT NULL DEFAULT false,
    "uses_bonded_warehouse" bool NOT NULL DEFAULT false,
    "apply_technical_note" bool NOT NULL DEFAULT false,
    "etd_initial_validated" bool NOT NULL DEFAULT false,
    "date_booking_request" timestamp(0),
    "date_booking_authorized" timestamp(0),
    "date_theorical_load" timestamp(0),
    "date_variable_date" timestamp(0),
    "date_received" timestamp(0),
    "date_etd_initial" timestamp(0),
    "inspection_date" timestamp(0),
    "vgm_cut_date" timestamp(0),
    "balance_payment_date" timestamp(0),
    "local_charges_payment_date" timestamp(0),
    "bonded_warehouse_enter" timestamp(0),
    "bonded_warehouse_exit" timestamp(0),
    "receipt_note_date" timestamp(0),
    "estimated_dc_availability_date" timestamp(0),
    "logistics_incoterm" varchar(255),
    "price_incoterm" varchar(255),
    "reason" varchar(255),
    "category" varchar(255),
    "forwarder_name" varchar(255),
    "cargo_invoice_number" varchar(255),
    "tariff_type" varchar(255),
    "route_label" varchar(255),
    "retail_group" varchar(255),
    "customer_type" varchar(255),
    "trading_company" varchar(255),
    "service_provider" varchar(255),
    "customs_dua" varchar(255),
    "invoice" varchar(255),
    "factura_merca" varchar(255),
    "case_number_file" varchar(255),
    "receipt_note" text,
    "visibility_notes" text,
    "departure_port" varchar(255),
    "arrival_port" varchar(255),
    "Invoice_amount" numeric(12,2),
    "freight_amount" numeric(12,2),
    "arrival_status" varchar(255),
    "delay_days" int4,
    "container_free_days" int4,
    "etd_dates_difference" int4,
    "eta_dates_difference" int4,
    "date_eta_initial" timestamp(0),
    "date_invoice_received" timestamp(0),
    "date_vendor_document_received" timestamp(0),
    "deleted_at" timestamp(0),
    "cbm" numeric(10,2),
    "dif_load_date" timestamp(0),
    "consolidator_name" varchar(255),
    "vendor_number" varchar(255),
    "emision_date_po" timestamp(0),
    "forwader_date" timestamp(0),
    "last_email_type_sent" int4,
    "last_email_sent_at" timestamp(0),
    "email_sent_history" json,
    "insurance_type" varchar(255),
    "carga_lista_validada" bool NOT NULL DEFAULT false,
    "porth_pol" varchar(255),
    "porth_pol_name" varchar(255),
    "porth_pod" varchar(255),
    "porth_pod_name" varchar(255),
    "porth_origin" varchar(255),
    "porth_final_destination" varchar(255),
    "porth_carrier_code" varchar(255),
    "porth_vessel_voyage" json,
    "porth_shipment_number" varchar(255),
    "porth_modality" varchar(255),
    "freight_type" varchar(255),
    "porth_first_eta" timestamp(0),
    "porth_first_etd" timestamp(0),
    "porth_ready" timestamp(0),
    "porth_to_origin_port" timestamp(0),
    "porth_at_origin_port" timestamp(0),
    "porth_in_transit" timestamp(0),
    "porth_at_destination_port" timestamp(0),
    "porth_to_final_destination" timestamp(0),
    "porth_delivered" timestamp(0),
    "porth_phase" varchar(255),
    "porth_priority" varchar(255),
    "porth_manual_tracking" bool NOT NULL DEFAULT false,
    "porth_free_time_at_destination" int4,
    "porth_id" varchar(255),
    "last_porth_sync_at" timestamp(0),
    "porth_itinerary" json,
    PRIMARY KEY ("id")
);

-- Column Comment
COMMENT ON COLUMN "public"."purchase_orders"."last_email_type_sent" IS 'Tipo del último email enviado (1, 2, 3, 4)';
COMMENT ON COLUMN "public"."purchase_orders"."last_email_sent_at" IS 'Fecha del último email enviado';
COMMENT ON COLUMN "public"."purchase_orders"."email_sent_history" IS 'Historial de emails enviados';

DROP TABLE IF EXISTS "public"."shipping_documents";
-- Sequence and defined type
CREATE SEQUENCE IF NOT EXISTS shipping_documents_id_seq;

-- Table Definition
CREATE TABLE "public"."shipping_documents" (
    "id" int8 NOT NULL DEFAULT nextval('shipping_documents_id_seq'::regclass),
    "company_id" int8 NOT NULL,
    "document_number" varchar(255) NOT NULL,
    "status" varchar(255) NOT NULL DEFAULT 'draft'::character varying CHECK ((status)::text = ANY (ARRAY[('draft'::character varying)::text, ('pending'::character varying)::text, ('approved'::character varying)::text, ('in_transit'::character varying)::text, ('delivered'::character varying)::text])),
    "creation_date" date NOT NULL,
    "estimated_departure_date" date,
    "estimated_arrival_date" date,
    "actual_departure_date" date,
    "actual_arrival_date" date,
    "hub_location" varchar(255),
    "total_weight_kg" int4 NOT NULL DEFAULT 0,
    "notes" text,
    "created_at" timestamp(0),
    "updated_at" timestamp(0),
    "tracking_id" varchar(255),
    "instruction_date" date,
    "kanban_status_id" int8,
    "release_date" date,
    "booking_code" varchar(255),
    "container_number" varchar(255),
    "mbl_number" varchar(255),
    "hbl_number" varchar(255),
    "date_theorical_load" timestamp(0),
    "date_variable_date" timestamp(0),
    "service_provider" varchar(255),
    "forwarder_name" varchar(255),
    "date_booking_request" timestamp(0),
    "date_booking_authorized" timestamp(0),
    "date_etd_updated" timestamp(0),
    "container_type" varchar(255),
    "mode" varchar(255),
    "date_eta_updated" timestamp(0),
    "shipping_line" varchar(255),
    "arrival_status" varchar(255),
    "factura_merca" varchar(255),
    "departure_port" varchar(255),
    "arrival_port" varchar(255),
    "bill_of_lading" varchar(255),
    "Invoice_amount" numeric(12,2),
    "bonded_warehouse_enter" timestamp(0),
    "bonded_warehouse_exit" timestamp(0),
    "receipt_note" varchar(255),
    "porth_shipment_id" varchar(255),
    "porth_id" varchar(255),
    "porth_shipment_number" int8,
    "porth_carrier_code" varchar(255),
    "porth_pol" varchar(255),
    "porth_pod" varchar(255),
    "porth_pol_name" varchar(255),
    "porth_pod_name" varchar(255),
    "porth_phase" varchar(255),
    "porth_priority" varchar(255),
    "porth_modality" varchar(255),
    "freight_type" varchar(255),
    "porth_vessel_voyage" json,
    "porth_name" varchar(255),
    "porth_organization_id" varchar(255),
    "porth_origin" varchar(255),
    "porth_final_destination" varchar(255),
    "porth_first_eta" timestamp(0),
    "porth_first_etd" timestamp(0),
    "porth_free_time_at_destination" int4,
    "porth_manual_tracking" bool NOT NULL DEFAULT false,
    "porth_tags" json,
    "porth_ready" timestamp(0),
    "porth_to_origin_port" timestamp(0),
    "porth_at_origin_port" timestamp(0),
    "porth_in_transit" timestamp(0),
    "porth_at_destination_port" timestamp(0),
    "porth_to_final_destination" timestamp(0),
    "porth_delivered" timestamp(0),
    "last_porth_sync_at" timestamp(0),
    "porth_raw" json,
    PRIMARY KEY ("id")
);

