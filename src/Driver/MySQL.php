<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2016, Niirrty
 * @package        Niirrty\DB\Driver\Attribute
 * @since          2017-11-01
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\DB\Driver;


use Niirrty\ArgumentException;
use Niirrty\DB\DbType;
use \Niirrty\DB\Driver\Attribute\{ Descriptor, Support, Type, ValueMissedLink };
use Niirrty\DB\QueryException;
use Niirrty\TypeTool;


/**
 * Defines the MySQL database driver.
 *
 * Usable Driver connection attributes are:
 *
 * - host: If no host is defined the unix_socket must be specified
 * - port
 * - user: Not used inside the DSN. Must be passed as 2nd PDO constructor param.
 * - password: Not used inside the DSN. Must be passed as 3rd PDO constructor param.
 * - dbname
 * - unix_socket
 * - charset
 */
final class MySQL extends AbstractDriver
{


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * MySQL driver constructor.
    */
   public function __construct()
   {

      $support = ( new Support() )

         // MySQL DSN attributes are separated by a space
         ->setAttributeSeparator( ';' )

         // MySQL DSN attribute names and values are separated by a equal sign
         ->setDSNKeyValueSeparator( '=' );


      $support

         // dbname as DSN part, not required
         ->add(
            ( new Descriptor( 'dbname', Type::DSN_PART, false ) )
               ->setValidator( function( $value )
                  {
                     if ( null === $value || ! \is_string( $value ) ||  '' === \trim( $value ) )
                     {
                        return false;
                     }
                     return (bool) \preg_match( '~^[a-z]([a-z0-9_]+)?$~', $value );
                  }
               )
         )

         // host as DSN part, or if host is not defined unix_socket must be defined
         ->add(
            ( new Descriptor( 'host', Type::DSN_PART, true ) )
               ->setValueMissedLink( new ValueMissedLink( $support, 'host', 'unix_socket' ) )
               ->setValidator( function( $value )
                  {
                     if ( null === $value || ! \is_string( $value ) || '' === \trim( $value ) )
                     {
                        return false;
                     }
                     return (bool) \preg_match(
                     // Host name                  | IP address
                        '~^([a-zA-Z]([a-zA-Z0-9_.-]+)?|[12]\d{0,2}\.[012]\d{0,2}\.[012]\d{0,2}\.[012]\d{0,2})$~',
                        $value
                     );
                  }
               )
         )

         // Optional port number 100-65554
         ->add(
            ( new Descriptor( 'port', Type::DSN_PART, false ) )
               ->setValidator( function( $value )
               {
                  if ( null === $value || ! \is_string( $value ) || ! \is_int( $value ) )
                  {
                     return false;
                  }
                  $port = (int) $value;
                  return $port > 99 && $port < 65555;
               }
               )
         )

         // Optional charset as DSN part. If not defined it uses a utf-8 fallback
         ->add(
            ( new Descriptor( 'charset', Type::DSN_PART, false ) )
               ->setDefaultValue( 'utf-8' )
               ->setValidator( function( $value )
                  {
                     if ( null === $value || ! \is_string( $value ) || '' === \trim( $value ) )
                     {
                        return false;
                     }
                     $len = \mb_strlen( $value );
                     return $len > 0 && $len < 17;
                  }
               )
         )

         // Optional unix socket as DSN part
         ->add(
            ( new Descriptor( 'unix_socket', Type::DSN_PART, false ) )
               ->setValidator( function( $value )
                  {
                     if ( null === $value || ! \is_string( $value ) || '' === \trim( $value ) )
                     {
                        return false;
                     }
                     $len = \mb_strlen( $value );
                     return $len > 0 && $len < 256;
                  }
               )
         )

         // the user name parameter
         ->add(
            ( new Descriptor( 'user', Type::USERNAME_PARAM, true ) )

               ->setValidator( function( $value )
                  {
                     if ( null === $value || ! \is_string( $value ) || '' === \trim( $value ) )
                     {
                        return false;
                     }
                     $len = \mb_strlen( $value );
                     return $len > 0 && $len < 51;
                  }
               )
         )

         // the password parameter
         ->add(
            ( new Descriptor( 'password', Type::PASSWORD_PARAM, false ) )
               ->setDefaultValue( '' )
               ->setValidator( function( $value )
                  {
                     if ( null === $value || ! \is_string( $value ) || '' === \trim( $value ) )
                     {
                        return false;
                     }
                     $len = \mb_strlen( $value );
                     return $len > 0 && $len < 129;
                  }
               )
         );

      parent::__construct( DbType::MYSQL, $support );

   }

   // </editor-fold>

   /**
    * Gets all connection info as string
    *
    * @return string
    */
   public function getInfoString() : string
   {

      $out = '';
      $haveData = false;

      if ( isset( $this->_attributes[ 'host' ] ) )
      {
         $out = 'host="' . $this->_attributes[ 'host' ] . '"';
         $haveData = true;
      }
      if ( isset( $this->_attributes[ 'port' ] ) )
      {
         $out .= ( $haveData ? '; ' : '' ) . 'port=' . $this->_attributes[ 'port' ];
         $haveData = true;
      }
      if ( isset( $this->_attributes[ 'dbname' ] ) )
      {
         $out .= ( $haveData ? '; ' : '' ) . 'dbname="' . $this->_attributes[ 'dbname' ] . '"';
         $haveData = true;
      }
      if ( isset( $this->_attributes[ 'unix_socket' ] ) )
      {
         $out .= ( $haveData ? '; ' : '' ) . 'unix_socket="' . $this->_attributes[ 'unix_socket' ] . '"';
         $haveData = true;
      }
      if ( isset( $this->_attributes[ 'charset' ] ) )
      {
         $out .= ( $haveData ? '; ' : '' ) . 'charset="' . $this->_attributes[ 'charset' ] . '"';
         $haveData = true;
      }
      $out .= ( $haveData ? '; ' : '' ) . 'user=[' .
              ( empty( $this->_attributes[ 'user' ] ) ? 'un' : '' ) . 'defined]"';
      $out .= ( $haveData ? '; ' : '' ) . 'password=[' .
              ( empty( $this->_attributes[ 'password' ] ) ? 'un' : '' ) . 'defined]"';

      return $out;

   }

   /**
    * Gets the host name or ip address of the PgSQL server.
    *
    * If you want to use a socket leave host empty/undefined
    *
    * @return null|string
    */
   public function getHost() : ?string
   {

      return $this->_attributes[ 'host' ] ?? null;

   }

   /**
    * Gets the optional port number of the PgSQL server.
    *
    * @return int|null
    */
   public function getPort() : ?int
   {

      return $this->_attributes[ 'port' ] ?? null;

   }

   /**
    * Gets the optional name of the initial connected database.
    *
    * @return null|string
    */
   public function getDbName() : ?string
   {

      return $this->_attributes[ 'dbname' ] ?? null;

   }

   /**
    * Gets the connection charset.
    *
    * @return string|null
    */
   public function getCharset() : ?string
   {

      return $this->_attributes[ 'charset' ] ?? null;

   }

   /**
    * Gets the unix socket. If you want to use it, do not define a host!
    *
    * @return null|string
    */
   public function getUnixSocket() : ?string
   {

      return $this->_attributes[ 'unix_socket' ] ?? null;

   }


   /**
    * Sets the host name or ip address of the MySQL server.
    *
    * If you want to use a socket call {@see \Niirrty\DB\Driver\MySQL::setUnixSocket()} and not this method.
    *
    * @param  null|string $host
    * @return \Niirrty\DB\Driver\MySQL
    * @throws \Niirrty\ArgumentException
    */
   public function setHost( ?string $host ) : MySQL
   {

      if ( ! $this->_supportedAttributes->get( 'host' )->validateValue( $host ) )
      {
         throw new ArgumentException( 'host', $host, 'Invalid host!' );
      }

      $this->_attributes[ 'host' ] = $host;

      return $this;

   }

   /**
    * Sets the optional port number of the MySQL server.
    *
    * @param $port
    * @return \Niirrty\DB\Driver\MySQL
    * @throws \Niirrty\ArgumentException
    */
   public function setPort( $port ) : MySQL
   {

      if ( null === $port )
      {
         $this->_attributes[ 'port' ] = $port;
         return $this;
      }

      $port = false;
      if ( \is_string( $port ) )
      {
         $port = (int) $port;
      }
      else if ( TypeTool::IsInteger( $port ) )
      {
         $port = (int) $port;
      }
      else if ( TypeTool::IsStringConvertible( $port, $portStr ) )
      {
         $port = (int) $portStr;
      }

      if ( ! \is_int( $port ) || ! $this->_supportedAttributes->get( 'port' )->validateValue( $port ) )
      {
         throw new ArgumentException( 'port', $port, 'Invalid port!' );
      }

      $this->_attributes[ 'port' ] = $port;

      return $this;

   }

   /**
    * Sets the optional name of the initial connected database.
    *
    * @param null|string $dbName
    * @return \Niirrty\DB\Driver\MySQL
    * @throws \Niirrty\ArgumentException
    */
   public function setDbName( ?string $dbName ) : MySQL
   {

      if ( ! $this->_supportedAttributes->get( 'dbname' )->validateValue( $dbName ) )
      {
         throw new ArgumentException( 'dbname', $dbName, 'Invalid database name!' );
      }

      $this->_attributes[ 'dbname' ] = $dbName;

      return $this;

   }

   /**
    * Sets the unix socket. If you want to use it, do not define a host!
    *
    * @param null|string $socket
    * @return \Niirrty\DB\Driver\MySQL
    * @throws \Niirrty\ArgumentException
    */
   public function setUnixSocket( ?string $socket ) : MySQL
   {

      if ( ! $this->_supportedAttributes->get( 'unix_socket' )->validateValue( $socket ) )
      {
         throw new ArgumentException( 'socket', $socket, 'Invalid unix socket!' );
      }

      $this->_attributes[ 'unix_socket' ] = $socket;

      return $this;

   }

   /**
    * Sets the connection charset.
    *
    * @param string $charset
    * @return \Niirrty\DB\Driver\MySQL
    * @throws \Niirrty\ArgumentException
    */
   public function setCharset( string $charset = 'UTF8' ) : MySQL
   {

      if ( ! $this->_supportedAttributes->get( 'charset' )->validateValue( $charset ) )
      {
         throw new ArgumentException( 'charset', $charset, 'Invalid charset!' );
      }

      $this->_attributes[ 'charset' ] = $charset;

      return $this;

   }

   /**
    * Sets the auth DBMS login user name.
    *
    * @param null|string $user
    * @return \Niirrty\DB\Driver\MySQL
    * @throws \Niirrty\ArgumentException
    */
   public function setAuthUserName( ?string $user ) : MySQL
   {

      if ( ! $this->_supportedAttributes->get( 'user' )->validateValue( $user ) )
      {
         throw new ArgumentException(
            'user', \str_repeat( '*', min( 64, \mb_strlen( $user, 'utf-8' ) ) ), 'Invalid auth user!' );
      }

      $this->_attributes[ 'user' ] = $user;

      return $this;

   }

   /**
    * Sets the auth DBMS login password.
    *
    * @param null|string $password
    * @return \Niirrty\DB\Driver\MySQL
    * @throws \Niirrty\ArgumentException
    */
   public function setAuthPassword( ?string $password ) : MySQL
   {

      if ( ! $this->_supportedAttributes->get( 'password' )->validateValue( $password ) )
      {
         throw new ArgumentException(
            'password', \str_repeat( '*', min( 48, \mb_strlen( $password, 'utf-8' ) ) ), 'Invalid auth password!' );
      }

      $this->_attributes[ 'password' ] = $password;

      return $this;

   }


   /**
    * @internal Gets if the table with defined name exists in selected database of current connection.
    * @param  \PDO   $pdo
    * @param  string $tableName
    * @return bool
    * @throws QueryException
    */
   public function tableExists( \PDO $pdo, string $tableName ) : bool
   {

      $tmp = \explode( '.', $tableName, 2 );

      $schema = null;

      if ( 2 === \count( $tmp ) )
      {
         $schema    = $tmp[ 0 ];
         $tableName = $tmp[ 1 ];
      }

      $sql        = 'SHOW TABLES LIKE ?';
      $bindParams = [ $tableName ];

      if ( null === $schema )
      {
         try
         {
            $stmt   = $pdo->prepare( $sql );
            $stmt->execute( $bindParams );
            $record = $stmt->fetch( \PDO::FETCH_NUM );
            $stmt   = null;
            if ( ! \is_array( $record ) )
            {
               return false;
            }
            return isset( $record[ 0 ] ) && ( $tableName === $record[ 0 ] );
         }
         catch ( \Throwable $ex )
         {
            throw new QueryException( $this, $sql, $bindParams, 'The SQL query execution fails.', 256, $ex );
         }
      }

      $sql        = 'SELECT count(*) FROM information_schema.TABLES WHERE (TABLE_SCHEMA = ?) AND (TABLE_NAME = ?)';
      $bindParams = [ $schema, $tableName ];

      try
      {
         $stmt   = $pdo->prepare( $sql );
         $stmt->execute( $bindParams );
         $record = $stmt->fetch( \PDO::FETCH_NUM );
         $stmt   = null;
         if ( ! \is_array( $record ) || ! isset( $record[ 0 ] ) )
         {
            return false;
         }
         return ( (int) $record[ 0 ] ) > 0;
      }
      catch ( \Throwable $ex )
      {
         throw new QueryException( $this, $sql, $bindParams, 'Can not check if a table exists!', 256, $ex );
      }

   }


}

