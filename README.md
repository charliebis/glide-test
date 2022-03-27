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
