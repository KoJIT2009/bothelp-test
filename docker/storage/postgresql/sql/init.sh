#!/bin/bash
set -e

psql -v ON_ERROR_STOP=1 --username "$POSTGRES_USER" --dbname "$POSTGRES_DB" <<-EOSQL
CREATE TABLE IF NOT EXISTS tasks_order
(
    id         bigserial primary key not null,
    account_id integer               NOT NULL,
    event_id   bigint                NOT NULL
);


create index tasks_order_account_id on tasks_order (account_id);
create unique index tasks_order_event_id on tasks_order (event_id);


CREATE TABLE IF NOT EXISTS events_executed
(
    id         bigserial primary key not null,
    event_id   bigint                NOT NULL references tasks_order(event_id) ON UPDATE CASCADE ON DELETE CASCADE
);

create unique index events_executed_event_id on events_executed (event_id);
EOSQL
