To use the webapp export the sql dump to a database tool of your choose. Then create a config.php in the php folder make sure it has the credentials of your database in the following format:
define('DB_HOST', 'localhost');
define('DB_PORT', 'port_number');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_user_name');
define('DB_PASSWORD', 'your_database_password');