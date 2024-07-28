#!/usr/bin/env sh

DB_NAME=quibreria
DB_USER=pagemaster

psql -U nixos -c "CREATE DATABASE $DB_NAME;" -h $PWD/pgsql/data
psql -U nixos -c "CREATE USER $DB_USER WITH PASSWORD 'pagemaster';" -h $PWD/pgsql/data
psql -U nixos -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;" -h $PWD/pgsql/data
