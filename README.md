# User upload (PHP, MySql)
PHP script, that is executed from the command line, accepts a CSV file as input and processes the CSV file.

## Prerequisites
- Ubuntu 18.04 instance
- PHP 7.2.x
- MySQL 5.7 or higher
- The script requires register_argc_argv enabled in php.ini

## Assumptions
Database (schema) parameter is missing, please create a database called **dbguillermo**

```MySql
CREATE DATABASE IF NOT EXISTS dbguillermo DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Usage
The PHP script should include these command line options (directives):

- --file [csv file name] – this is the name of the CSV to be parsed
- --create_table – this will cause the MySQL users table to be built (and no further action will be taken)
- --dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered
- -u – MySQL username
- -p – MySQL password
- -h – MySQL host
- --help – which will output the above list of directives with details.
For example:
```
php user_upload.php  --file users.csv --create_table -u root -p pass -h localhost
```
```php
// The parameters File, Username and Host are required.
// Select --create_table or --dry_run not both.
```
# Authors
**Guillermo Gomez Arias** - [guillogo](https://github.com/guillogo/) - [LinkedIn](https://www.linkedin.com/in/guillogo/)
