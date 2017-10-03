# exodus
A migrations framework for raw SQL written in PHP.

**Still in Development**

---

If you want to test it out, you need to clone the project to your local machine and run:

```sh
$ composer install
```

## How to Use

To see the help menu, just type:

```sh
$ php exodus
```

To run a sample of how Exodus works, first, create a database with Postgres. For this example, I'll call mine `example_db`:

```sh
$ createdb example_db
```

Now run the bootstrap command to get the `exodus.yml` configuration file:

```sh
$ php exodus bootstrap
Created exodus.yml file.
```

You should now see the `exodus.yml` file in your project root directory.

Edit your `exodus.yml` file with your credentials:

```yml
migration_dir: database/migrations/     # location you want to have your migrations folder
migration_table: migrations             # the database table that will hold your run migrations
db:
  adapter: postgresql   # must be postgresql, do not change
  host: localhost       # or whatever host you want
  username: graham      # replace with your username to the db
  password:             # replace with your password to the db
  port: 5432            # or whatever port you have configured
  name: example_db      # or whatever the name of your database is
```

Now that you have your configuration file ready to go, it's time to make a migration:

```sh
$ php exodus make:migration create_users_table
Created migration file.
```

This will create the migration file under the specified directory from `migration_dir` in `exodus.yml`.
In this case, my file was created under:

```
database/migrations/1506960399_create_users_table.sql
```

Notice that the *time in milliseconds* is prepended to the migration file name. That is used to help sort the order the migration files should be run, so that in case you create another migration file and don't yet run it, it will still be run after the first one.

Open the `1506960399_create_users_table.sql` file and add a simple command to execute like creating a table:

```sql
DO
$$
BEGIN

CREATE TABLE users (
    id INT PRIMARY KEY NOT NULL,
    name VARCHAR NOT NULL
);

INSERT INTO users (id, name) VALUES (1, 'Graham');
INSERT INTO users (id, name) VALUES (2, 'Jonathan');

END;
$$
```

Now, save the file and in your terminal, run the migrations and you should see the corresponding output:

```sh
$ php exodus migrate
Your files have made it to the Promised Land.
 -------------------------------------- 
  Files Migrated                        
 -------------------------------------- 
  1506960399_create_accounts_table.sql  
 --------------------------------------
```

If you see this message and no warnings or errors, then congrats, your migrations have successfully run.

You should then be able to see in your database:

```sh
$ psql example_db
psql (9.5.8)
Type "help" for help.

exodus_dev_db=> SELECT * FROM users;
 id |   name 
----+----------
 1  | Graham
 2  | Jonathan
(2 rows)

exodus_dev_db=> SELECT * FROM migrations;
                 file                 |           ran_at           
--------------------------------------+----------------------------
 1506960399_create_accounts_table.sql | 2017-10-02 21:10:33.638886
(1 row)

exodus_dev_db=> \q
```

Now that the migration file has been run and is recorded in the database, you can try running the migrate command again:

```sh
php exodus migrate
No migrations to run.
```

Since the migrations have been run and no new migrations are pending, the app has no migrations to run.

## Contributing

**Fork and Clone the Project**

First fork. Then clone your version:

```sh
$ git clone https://github.com/YOURUSERNAME/exodus.git
```

I like pull requests.
