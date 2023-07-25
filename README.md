## This is a technical test project. Part of the recruitment process for my earlier role at Glide Utilities.

The brief for the test was to create a front and back end for a viewer for Calorific values, as retrieved from a remote source via feed fetch/scrape. The data was to be stored in a MySQL database and rendered in a web front-end.

I've left this on my public GitHub profile in case you want to see what I can do using PHP.

The test implements a very basic MVC system, without using an off the shelf framework. This was to demonstrate my understanding of the concepts of PHP frameworks, the MVC pattern and OOP in general.

# glide-test

Calorie Data Viewer App.

## Installation:

1. Install the code.
2. Create a MySQL user and database.
3. Run the queries in create_tables.sql to create the tables.
4. Create a file called application.ini in the project root. Copy/paste the following into the file:

    ```ini
    [general]    
    installation_path = YOUR_PROJECT_ROOT_PATH    
    site_url = YOUR_SITE_ROOT_URL
    
    [database]    
    db_host = YOUR_DB_HOST    
    db_database = YOUR_DB_DATABASE    
    db_user = YOUR_DB_USER
    db_pass = 'YOUR_DB_PASS'
    ```
    Swap the YOUR_* parts with your own settings.

    installation_path must be the full path to the project root dir, e.g.
    "/home/glidetest.example.com/"

    site_url must be the full URL to the root of the site, e.g. "http://glidetest.example.com/"

5. Open the app in a web browser.
6. Use the Update Now button to load the remote CSV data.
