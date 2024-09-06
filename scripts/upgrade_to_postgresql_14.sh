#! /bin/bash

sudo apt-get update -y

sudo service postgresql stop

sudo apt-get remove -y postgresql-13
sudo apt-get autoremove -y

sudo apt-get install -y postgresql-14

sudo service postgresql start
sudo pg_dropcluster 14 main
sudo service postgresql stop
sudo pg_upgradecluster -m upgrade 13 main
sudo service postgresql start
sudo pg_dropcluster 13 main --stop

sudo -u postgres psql -c "CREATE ROLE homestead LOGIN PASSWORD 'secret' SUPERUSER INHERIT NOCREATEDB NOCREATEROLE NOREPLICATION;"

sudo sed -i "s/#listen_addresses = 'localhost'/listen_addresses = '*'/g" /etc/postgresql/14/main/postgresql.conf
sudo sed -i "s/port = 5433/port = 5432/g" /etc/postgresql/14/main/postgresql.conf
echo "host    all             all             0.0.0.0/0               md5" | sudo tee -a /etc/postgresql/14/main/pg_hba.conf

sudo service postgresql restart

sudo -u postgres /usr/bin/createdb --echo --owner=homestead homestead
sudo -u postgres /usr/bin/createdb --echo --owner=homestead hylark
