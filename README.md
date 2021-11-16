This is a small app to fetch list of repositories of an organization. It can collect a list of repositories, save to database and display as a list on a website.

Requirements:
------------------
This app requires docker and docker-compose

Quick Installation
------------------

Just type in your IDE the following command:
```
make install
```
It will automatically install everything you need to run this project, it may take a few minutes.

After completing the installation run:

```make run```

And type the following command to run the app:

```php bin/console app:fetch-repository "organization name" "provider"```

If you interested in the building process checkout the Makefile


If it succeeds a message will be returned with the number of elements that have been saved to the database.

To access the fetched repositories, open your browser and go to:

```localhost:8080/repo```