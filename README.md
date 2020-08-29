# Niirrty.DB

The Niirrty database library

For usage see the examples.


## Examples

First you must define the configuration.

Currently it can be done by 2 different ways

### 1. By PHP config file

#### 1.1. The config file

In this case you have to define a php file with a content like this

```php
<?php

return [
   'type'     => 'pgsql',
   'host'     => '127.0.0.1',
   #'port'    => 5432,
   'db'       => 'db-name',
   'charset'  => 'UTF8',
   'user'     => 'db-username',
   'password' => 'db-password'
];
```

* **type**:     Defines the driver type `('pgsql' | 'mysql' | 'sqlite')`
* **host**:     The DB host or IP address
* **port**:     The DB Port
* **db**:       The name of the database that should be selected
* **charset**:  The db connection charset
* **user**:     The db login user name
* **password**: The db login password

#### 1.2. Usage

```php
<?php

include '../vendor/autoload.php';

use Niirrty\DB\Driver\Factory as DriverFactory;
use Niirrty\DB\Connection as DbConnection;

try
{

    // Init the driver from above defined php config file
    $driver = DriverFactory::FromConfigFile( __DIR__ . '/driver-config.php' );

    // Open a database connection with the defined driver
    $conn = new DbConnection( $driver );

    // The example table is a simple users table

    // Check if the table is empty 
    $sql = 'SELECT COUNT(*) FROM users';
    $cnt = (int) $conn->fetchScalar( $sql, [], '0' );

    if ( $cnt < 1 )
    {
        // OK the table is empty => insert the first user
        $sql = "INSERT INTO `users`
            ( u_name, u_display_name, u_mail, u_pass, u_created, u_last_login, u_deleted )
            VALUES( ?, ?, ?, ?, DEFAULT, NULL, NULL )";
        $conn->exec( $sql, [ 'Administrator', 'John Who (Admin)', 'j.who@example.com', \hash( 'sha512', 'secret' ) ] );
    }

    // We have a first user => select all but only use the first found record :-D
    $sql = '
      SELECT u_id, u_name, u_display_name, u_mail, u_pass, u_created, u_last_login, u_deleted
         FROM users
         ORDER BY u_display_name';
    $record = $conn->fetchRecord( $sql );
    var_dump( $record );

}
catch ( \Throwable $ex )
{
   echo $ex;
}
```


### 2. By YAML file format

#### 2.1. The config file

Create a file with contents like follow and save it with `.yml` file name extension

```yaml
type:     pgsql
host:     127.0.0.1
#port:    5432
db:       db-name
charset:  utf8
user:     db-username
password: db-password
```

* **type**:     Defines the driver type `('pgsql' | 'mysql' | 'sqlite')`
* **host**:     The DB host or IP address
* **port**:     The DB Port
* **db**:       The name of the database that should be selected
* **charset**:  The db connection charset
* **user**:     The db login user name
* **password**: The db login password

#### 2.2. Usage

```php
<?php

include '../vendor/autoload.php';

use Niirrty\DB\Driver\Factory as DriverFactory;
use Niirrty\DB\Connection as DbConnection;

try
{

    // Init the driver from above defined php config file
    $driver = DriverFactory::FromConfigFile( __DIR__ . '/driver-config.php' );

    // Open a database connection with the defined driver
    $conn = new DbConnection( $driver );

    // Working with the connection…

}
catch ( \Throwable $ex )
{
   echo $ex;
}
```

### 3. By pure PHP code

#### 3.1. Postgre Example

```php
<?php

include dirname( __DIR__ ) . '/vendor/autoload.php';

use \Niirrty\DB\Driver\PgSQL as PgSQLDriver;
use Niirrty\DB\Connection as DbConnection;

try
{
    $driver = ( new PgSQLDriver() )
        ->setHost( '127.0.0.1' )
        ->setDbName( 'db-name' )
        ->setAuthUserName( 'db-username' )
        ->setAuthPassword( 'db-password' )
        ->setCharset( 'UTF8' );
    $conn = new DbConnection( $driver );

    // Working with the connection…

}
catch ( \Throwable $ex )
{
   echo $ex;
}
```

#### 3.2. MySQL example

```php
<?php

include dirname( __DIR__ ) . '/vendor/autoload.php';

use \Niirrty\DB\Driver\MySQL as MySQLDriver;
use Niirrty\DB\Connection as DbConnection;

try
{
    $driver = ( new MySQLDriver() )
        ->setHost( '127.0.0.1' )
        ->setDbName( 'db-name' )
        ->setAuthUserName( 'db-username' )
        ->setAuthPassword( 'db-password' )
        ->setCharset( 'UTF8' );
    $conn = new DbConnection( $driver );

    // Working with the connection…

}
catch ( \Throwable $ex )
{
   echo $ex;
}
```

#### 3.2. SQLite example

```php
<?php

include dirname( __DIR__ ) . '/vendor/autoload.php';

use \Niirrty\DB\Driver\SQLite as SQLiteDriver;
use Niirrty\DB\Connection as DbConnection;

try
{
    $driver = ( new SQLiteDriver() ) ->setDb( __DIR__ . '/example.sqlite3' );
    $conn = new DbConnection( $driver );

    // Working with the connection…

}
catch ( \Throwable $ex )
{
   echo $ex;
}
```