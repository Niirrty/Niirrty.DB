<?php


include dirname( __DIR__ ) . '/vendor/autoload.php';


use Niirrty\DB\Driver\Factory as DriverFactory;


try
{
   $driver = DriverFactory::FromConfigFile( __DIR__ . '/driver-config.php' );
   $conn = new \Niirrty\DB\Connection( $driver );
   $sql = 'SELECT COUNT(*) FROM users';
   $cnt = (int) $conn->fetchScalar( $sql, [], '0' );
   if ( $cnt < 1 )
   {
      $sql = "
         INSERT
            INTO
               users
            (
               u_name, u_display_name, u_mail, u_pass, u_created,
               u_last_login, u_deleted
            )
            VALUES(
               ?, ?, ?,
               '75de72b9b25502b1a98fea0de5e3bade635340d60cc04522f018690ffbf81744acf108f7fbcc19367133f5e48ea2d7de8485b3ac2c1208ee1486efe57a7be26e',
               DEFAULT, NULL, NULL
            )";
      $conn->exec( $sql, [ 'Messier 1001', 'Messier', 'messier@localhost' ] );
   }
   $sql = '
      SELECT
            u_id, u_name, u_display_name, u_mail, u_pass, u_created,
            u_last_login, u_deleted
         FROM
            users
         ORDER BY
            u_display_name';
   $record = $conn->fetchRecord( $sql );
   var_dump( $record );
}
catch ( \Throwable $ex )
{

   echo $ex;

}

