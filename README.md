# My test task

The requirements/specifications are described on the following page https://gist.github.com/naymkazp/87112812d3e273083979f3e36035e1e9

Steps to set up the project

1. `git clone https://github.com/antonhub/Test.git`

2. `composer install`

3. change the DB connection parameters in `.env` file by editing the following line:
   `DATABASE_URL="mysql://root@127.0.0.1:3306/test_task?serverVersion=mariadb-10.4.32"`

4. execute DB migrations `php bin/console doctrine:migrations:migrate`

Now you can run the console command to calculate the commissions:
`php bin/console commissions:calculate input.txt`