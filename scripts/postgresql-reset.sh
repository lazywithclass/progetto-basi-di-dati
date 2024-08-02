#!/usr/bin/env sh

DB_NAME=quibreria
DB_USER=pagemaster

psql -U $DB_USER -d $DB_NAME -f ./db/dump.sql -h $PWD/pgsql/data
