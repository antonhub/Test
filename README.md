# My test task

The requirements/specifications are described on the following page https://gist.github.com/naymkazp/87112812d3e273083979f3e36035e1e9

Steps to set up the project

1. `git clone https://github.com/antonhub/Test.git`

2. `composer install`

3. change the DB connection parameters in `.env` file by editing the following line:
   `DATABASE_URL="mysql://root@127.0.0.1:3306/test_task?serverVersion=mariadb-10.4.32"`  
   or better add `.env.local` file for local environment variables

4. execute DB migrations `php bin/console doctrine:migrations:migrate`

Now you can run the console command to calculate the commissions:
`php bin/console commissions:calculate input.txt`

## Unit tests
Run unit tests by `php bin/phpunit tests`  
I've added just 2 simple unit test so far.  
The fixtures are configured and added.  
To create you test environment DB, create tables and populate it with fixtures:  
`php bin/console --env=test doctrine:database:create`  
`php bin/console --env=test doctrine:schema:create`  
`php bin/console make:fixtures`  
`php bin/console --env=test doctrine:fixtures:load`  