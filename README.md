# ToDo-List (ToDo & Co)

[![SymfonyInsight](https://insight.symfony.com/projects/5745b6bc-f698-4abe-9618-404d1da53406/big.svg)](https://insight.symfony.com/projects/5745b6bc-f698-4abe-9618-404d1da53406)

This project is a todo-list exercise for the PHP course at OpenClassrooms.
It is not meant to be used in production nor is it meant to be a showcase.

This repository is primarily a way of sharing code with the tutor.

## Installation

This project uses [Composer](https://getcomposer.org) with PHP `>=8.3`.

Configure your database in a `.env.local` file at the root of the project.  
Copy a `DATABASE_URL` line from the `.env` file and modify it to fit your configuration.

Clone and install the project.

```shell
# Clone the repository
$ git clone https://github.com/scoopandrun/ocp8
$ cd ocp8

# Install the project (with fixtures, default)
$ make install
# Without fixtures
$ make install FIXTURES=0
```

Alternatively, you can decompose the steps as follows

```shell
# Install the dependencies
$ composer install

# Create your database
$ php bin/console doctrine:database:create

# Execute the migrations
$ php bin/console doctrine:migrations:migrate

# (Recommended) Load the fixtures to get a starting data set.
$ php bin/console doctrine:fixtures:load
```

## Tests and code coverage

The tests can be run with the following command:

```shell
$ make test
# or
$ bin/phpunit
```

The `make test` command resets the test database and loads the fixtures before each run.

The latest code coverage is available at `/tests/index.html` (already included in the repo).

## Default users

By default, the fixtures create 3 permanent users :

- Admin
  - username: Admin
  - email: admin<span>@</span>example.com
  - password: "admin"
- User 1
  - username: User1
  - email: user1<span>@</span>example.com
  - password: "pass"
- User 2
  - username: User2
  - email: user2<span>@</span>example.com
  - password: "pass"

You can update the initial users information in the data fixtures (`src/DataFixtures/AppFixtures.php`).
