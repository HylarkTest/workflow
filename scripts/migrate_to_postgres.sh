#! /bin/bash

sudo apt install -y sbcl unzip libsqlite3-dev gawk curl make freetds-dev libzip-dev
cd /tmp
curl -fsSLO https://github.com/dimitri/pgloader/archive/v3.6.2.tar.gz
tar xvf v3.6.2.tar.gz
cd pgloader-3.6.2/
make pgloader
sudo mv ./build/bin/pgloader /usr/local/bin/
sudo rm -rf /tmp/pgloader-3.6.2

block="LOAD DATABASE
     FROM      mysql://homestead:secret@localhost:3306/hylark
     INTO postgresql://homestead:secret@localhost:5432/hylark

 WITH include drop, create tables, create indexes, reset sequences,
      workers = 8, concurrency = 1,
      multiple readers per thread, rows per range = 50000

  SET PostgreSQL PARAMETERS
      maintenance_work_mem to '128MB',
      work_mem to '12MB',
      search_path to 'public'

  SET MySQL PARAMETERS
      net_read_timeout  = '120',
      net_write_timeout = '120'

 CAST type bigint when (= precision 20) to bigserial drop typemod,
      type date drop not null drop default using zero-dates-to-null,
      -- type tinyint to boolean using tinyint-to-boolean,
      type year to integer,
      type int when unsigned with extra auto_increment to bigserial drop typemod,
      type int when unsigned to bigint drop typemod;

"

echo "$block" > "/tmp/psql_migration_script.lisp"

/usr/local/bin/pgloader /tmp/psql_migration_script.lisp
