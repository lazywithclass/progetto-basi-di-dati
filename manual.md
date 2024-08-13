# Manual

## Dependencies

The project has been tested with these versions:

 * PHP 8.2.19
 * PostgreSQL 15.6
 
## Run 
 
Run the following commands in the order they're presented to start the project the first time

```shell
$ ./scripts/postgresql-setup.sh
$ ./scripts/postgresql-reset.sh
$ ./scripts/postgresql-start.sh
$ ./scripts/php-start.sh
```
 
After the first run only the following are needed
 
```shell
$ ./scripts/postgresql-start.sh
$ ./scripts/php-start.sh
```

## Credentials

Librarian
Username: brooks
Password: brooks
