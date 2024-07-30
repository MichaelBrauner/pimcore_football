# My first Pimcore project

A small project on the subject of soccer.

## Prerequisites

* You need Docker and docker-compose installed.
* You need GNU-Make installed.

## Getting started

### Download

```bash
git clone git@github.com:MichaelBrauner/pimcore_football.git mb-football
cd ./mb-football
```

### Start the docker containers and install Pimcore

```bash
make start-install
make pimcore-install
```
> For some reason you have to run the last command twice.  
> And ignore the error messages. 
> If there occurs other errors, send me an email or call me ;).

### Import the data

```bash
make import-data
```

You can see the website right away at [http://127.0.0.1](http://127.0.0.1).  
Now you can access the Pimcore backend at [http://127.0.0.1/admin](http://127.0.0.1/admin) with the credentials `Admin` / `admin123`.