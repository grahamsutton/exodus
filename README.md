# exodus
A migrations framework for raw SQL written in PHP.

**Still in Development**

---

If you want to test it out, you need to clone the project to your local machine and run:

```sh
$ composer install
```

## Configuration

To see the help menu, just type:

```sh
$ php exodus
```

To run a sample of how Exodus works, first, create a database with Postgres. For this example, I'll call mine `example_db`:

```sh
$ createdb example_db
```

The configuration file, `exodus.yml`, will be created if it does not yet exist when you run the `php exodus` or any Exodus command:

```sh
$ php exodus
Created exodus.yml file.
...
```

You should now see the `exodus.yml` file in your project root directory.

Edit your `exodus.yml` file with your credentials:

```yml
migration_dir: database/migrations/     # location you want to have your migrations folder
migration_table: migrations             # the database table that will hold your run migrations
db:
  adapter: postgresql                   # must be postgresql, do not change
  host: localhost                       # or whatever host you want
  username: graham                      # replace with your username to the db
  password:                             # replace with your password to the db
  port: 5432                            # or whatever port you have configured
  name: example_db                      # or whatever the name of your database is
```

## Creating a Migration

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

Open the `1506960399_create_users_table.sql` file and you should see two Postgres functions:

* `exodus_tmp.UP()`: Executes when you run `php exodus migrate`
* `exodus_tmp.DOWN()`: Executes when you run `php exodus rollback`

Essentially, `UP` are the positive, new changes you want to make against your database, whereas `DOWN` reverses those new changes. So, `DOWN` is the reverse operation of `UP`. Exodus creates a temporary schema called `exodus_tmp` to execute the `UP` and `DOWN` functions, and is dropped after the migrations have finished running.

Here is an example of creating a new table and adding some records in the `UP` function, and you can see how `DOWN` reverses this change by dropping the table.

```sql
CREATE OR REPLACE FUNCTION exodus_tmp.UP()
RETURNS void AS
$BODY$
  BEGIN

    CREATE TABLE users (
      name VARCHAR,
      age INT
    );

    INSERT INTO users (name, age) VALUES ('Mike', 28);
    INSERT INTO users (name, age) VALUES ('Steve', 32);

  END;
$BODY$
LANGUAGE 'plpgsql';

CREATE OR REPLACE FUNCTION exodus_tmp.DOWN()
RETURNS void AS
$BODY$
  BEGIN

    DROP TABLE users;

  END;
$BODY$
LANGUAGE 'plpgsql';
```

Now, save the file and in your terminal, run your migrations:

```sh
$ php exodus migrate
Migrated: 1506960399_create_users_table.sql
```

You should then be able to see in your database:

```sh
$ psql example_db

exodus_dev_db=> SELECT * FROM users;
   name   |   age
----------+---------
 Mike     |      28
 Steve    |      32
(2 rows)

exodus_dev_db=> SELECT * FROM migrations;
                 file                 |           ran_at           
--------------------------------------+----------------------------
 1506960399_create_accounts_table.sql | 2017-10-02 21:10:33.638886
(1 row)
```

Now that the migration file has been run and is recorded in the database, you can try running the migrate command again:

```sh
php exodus migrate
No migrations to run.
```

Since the migrations have been run and no new migrations are pending, the app has no migrations to run.

## Rolling Back Migrations

If you wish to rollback the last batch of executed migrations, simply run:

```sh
$ php exodus rollback
Rolled back: 1506960399_create_users_table.sql
```

This will iterate through the last batch of migration files that were executed and will run the `DOWN` function on each one. Migrations that have *not* been migrated yet will be ignored. If you would like to rollback the execution before that (and so on), just keep running this command over and over again as desired.

## In The Works

Future updates that are coming soon include:

* Create or replace procedures by referencing them from their own files (instead of having to write the entire procedure inside a migration file).

## Contributing

**Fork and Clone the Project**

First fork. Then clone your version:

```sh
$ git clone https://github.com/YOURUSERNAME/exodus.git
```

I like pull requests.
