#! /bin/bash

sudo apt-get upgrade postgresql

# Add Citus repository for package manager
curl https://install.citusdata.com/community/deb.sh | sudo bash

# install the server and initialize db
sudo apt-get -y install postgresql-15-citus-10.2

echo "shared_preload_libraries = 'citus'" | sudo tee -a citus/postgresql.conf

sudo /usr/lib/postgresql/15/bin/psql -c "CREATE EXTENSION citus;"

sudo service postgresql restart
