<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2016, Niirrty
 * @package        Niirrty\DB\Driver\Attribute
 * @since          2017-11-01
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\DB;


use Niirrty\DB\Driver\Attribute\Type;
use Niirrty\DB\Driver\IDriver;


/**
 * Defines a class that …
 *
 * @since v0.1.0
 */
class Connection
{


   // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The DBMS depending driver implementation
    *
    * @type \Niirrty\DB\Driver\IDriver
    */
   private $_driver;

   /**
    * The usable PDO connection, or null if no connection is open.
    *
    * @type \PDO|null
    */
   private $_pdo;

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * Connection constructor.
    *
    * @param \Niirrty\DB\Driver\IDriver $driver
    */
   public function __construct( IDriver $driver )
   {

      $this->_driver = $driver;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">


   // <editor-fold desc="// – – –   G E T T E R   – – – – – – – – – – – – –">

   /**
    * Gets the DBMS depending driver implementation
    *
    * @return \Niirrty\DB\Driver\IDriver
    */
   public function getDriver() : IDriver
   {

      return $this->_driver;

   }

   /**
    * Gets the used PDO instance, or null if none is defined.
    *
    * @return \PDO|null
    */
   public function getPDO() : ?\PDO
   {

      return $this->_pdo;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   S E T T E R   – – – – – – – – – – – – –">

   /**
    * Sets the DBMS Driver
    *
    * @param \Niirrty\DB\Driver\IDriver $driver
    * @return \Niirrty\DB\Connection
    */
   public function setDriver( IDriver $driver ) : Connection
   {

      $this->_driver = $driver;
      $this->_pdo    = null;

      return $this;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   O T H E R   – – – – – – – – – – – – – -">

   /**
    * Gets if the connection is valid configured.
    *
    * @return bool
    */
   public function hasValidConfig() : bool
   {

      return $this->_driver->getAttributeSupport()->haveAllRequiredAttributes( $this->_driver->getDefinedAttributes() );

   }

   /**
    * Gets if a usable connection is open
    *
    * @return bool
    */
   public final function isOpen() : bool
   {

      return null !== $this->_pdo;

   }

   /**
    * Opens a connection if none is open.
    *
    * @return \Niirrty\DB\Connection
    * @throws \Niirrty\DB\ConnectionException
    */
   public final function open() : Connection
   {

      if ( $this->isOpen() )
      {
         return $this;
      }

      $attrSupport       = $this->_driver->getAttributeSupport();
      $definedAttributes = $this->_driver->getDefinedAttributes();


      $dsn  = $this->_driver->getType() . ':';
      $dsnC = 0;
      $user = null;
      $pass = null;
      $sql  = [];
      $opts = [];

      foreach( $definedAttributes as $attrName => $attrValue )
      {

         $attrDesc = $attrSupport->get( $attrName );

         if ( null === $attrDesc ) { continue; }

         switch ( $attrDesc->getType() )
         {

            case Type::DSN_PART:
               if ( $dsnC < 1 )
               {
                  $dsnC++;
               }
               else
               {
                  $dsn .= $attrSupport->getDSNAttributeSeparator();
               }
               if ( $attrDesc->useNameInDSN() )
               {
                  $dsn .= $attrName . $attrSupport->getDSNKeyValueSeparator();
               }
               $dsn .= $attrValue;
               break;

            case Type::USERNAME_PARAM:
               $user = $attrValue;
               break;

            case Type::PASSWORD_PARAM:
               $pass = $attrValue;
               break;

            case Type::OPTION_PARAM:
               $opts[ $attrName ] = $attrValue;
               break;

            #case Type::INIT_SQL:
            default:
               if ( 'charset' === $attrName )
               {
                  $sql[] = 'SET NAMES ' . $attrValue;
                  break;
               }
               $sql[] = $attrValue;
               break;

         }

      }

      try
      {
         if ( ! isset( $opts[ \PDO::ATTR_ERRMODE ] ) )
         {
            $opts[ \PDO::ATTR_ERRMODE ] = \PDO::ERRMODE_EXCEPTION;
         }
         if ( $this->_driver->getType() === 'sqlite' || ( null === $user && null === $pass ) )
         {
            $this->_pdo = new \PDO( $dsn, null, null, $opts );
         }
         else
         {
            $this->_pdo = new \PDO( $dsn, $user, $pass, $opts );
         }
      }
      catch ( \Throwable $ex )
      {
         throw new ConnectionException( $this->_driver, 'Connection init fails!', 256, $ex );
      }

      if ( 0 < \count( $sql ) )
      {

         foreach ( $sql as $sqlString )
         {
            try { $this->_pdo->exec( $sqlString ); }
            catch ( \Throwable $ex )
            {
               throw new QueryException(
                  $this->_driver, $sqlString, [], 'Can not call the initial query!', 256, $ex
               );
            }
         }

      }

      return $this;

   }

   /**
    * Closes the current connection.
    *
    * @return \Niirrty\DB\Connection
    */
   public final function close() : Connection
   {

      $this->_pdo = null;

      return $this;

   }

   /**
    * Fetches all records from defined SQL query string and returns all as a array.
    *
    * @param  string $sql        The SQL query string
    * @param  array  $bindParams Optional bind params for prepared statements
    * @param  int    $fetchStyle See \PDO::FETCH_* constants
    * @return array
    * @throws \Niirrty\DB\ConnectionException If no connection exists an creation fails.
    * @throws \Niirrty\DB\QueryException      If the query execution fails
    */
   public final function fetchAll( string $sql, array $bindParams = [], $fetchStyle = \PDO::FETCH_ASSOC ) : array
   {

      $this->open();

      try
      {
         if ( 1 > \count( $bindParams ) )
         {
            $stmt = $this->_pdo->query( $sql );

            return $stmt->fetchAll( $fetchStyle );
         }

         $stmt = $this->_pdo->prepare( $sql );
         $stmt->execute( $bindParams );

         return $stmt->fetchAll( $fetchStyle );
      }
      catch ( \Throwable $ex )
      {
         throw new QueryException( $this->_driver, $sql, $bindParams, 'Can not fetch all data from query!' );
      }

   }

   /**
    * Fetches all records from defined SQL query string and returns all as a Generator.
    *
    * @param  string $sql        The SQL query string
    * @param  array  $bindParams Optional bind params for prepared statements
    * @param  int    $fetchStyle See \PDO::FETCH_* constants
    * @return \Generator
    * @throws \Niirrty\DB\ConnectionException If no connection exists an creation fails.
    * @throws \Niirrty\DB\QueryException      If the query execution fails
    */
   public final function fetchIterateAll( string $sql, array $bindParams = [], $fetchStyle = \PDO::FETCH_ASSOC )
      : \Generator
   {

      $this->open();

      try
      {
         if ( 1 > \count( $bindParams ) )
         {
            $stmt = $this->_pdo->query( $sql );
            while ( $record = $stmt->fetch( $fetchStyle, \PDO::FETCH_ORI_NEXT ) )
            {
               yield $record;
            }
         }
         else
         {
            $stmt = $this->_pdo->prepare( $sql );
            $stmt->execute( $bindParams );

            while ( $record = $stmt->fetch( $fetchStyle, \PDO::FETCH_ORI_NEXT ) )
            {
               yield $record;
            }
         }
      }
      catch ( \Throwable $ex )
      {
         throw new QueryException( $this->_driver, $sql, $bindParams, 'Can not fetch all data from query!' );
      }

   }

   /**
    * Fetches the first found record from defined SQL query string and returns it as a associative array.
    *
    * @param  string $sql        The SQL query string
    * @param  array  $bindParams Optional bind params for prepared statements
    * @param  int    $fetchStyle See \PDO::FETCH_* constants
    * @return array|null
    * @throws \Niirrty\DB\ConnectionException If no connection exists an creation fails.
    * @throws \Niirrty\DB\QueryException      If the query execution fails
    */
   public final function fetchRecord( string $sql, array $bindParams = [], $fetchStyle = \PDO::FETCH_ASSOC ) : ?array
   {

      $this->open();

      try
      {
         if ( 1 > \count( $bindParams ) )
         {
            $stmt = $this->_pdo->query( $sql );
            return $stmt->fetch( $fetchStyle );
         }
         $stmt = $this->_pdo->prepare( $sql );
         $stmt->execute( $bindParams );
         $record = $stmt->fetch( $fetchStyle );
         if ( \is_array( $record ) )
         {
            return $record;
         }
         return null;
      }
      catch ( \Throwable $ex )
      {
         throw new QueryException( $this->_driver, $sql, $bindParams, 'Can not fetch a single record from query!' );
      }

   }

   /**
    * Fetches the first found scalar value from defined SQL query string and returns it.
    *
    * @param  string $sql          The SQL query string
    * @param  array  $bindParams   Optional bind params for prepared statements
    * @param  mixed  $defaultValue Is returned if no value could be found.
    * @return mixed
    * @throws \Niirrty\DB\ConnectionException If no connection exists an creation fails.
    * @throws \Niirrty\DB\QueryException      If the query execution fails
    */
   public final function fetchScalar( string $sql, array $bindParams = [], $defaultValue = null )
   {

      $this->open();

      $record = null;
      try
      {
         if ( 1 > \count( $bindParams ) )
         {
            $stmt = $this->_pdo->query( $sql );
            $record = $stmt->fetch( \PDO::FETCH_NUM );
         }
         else
         {
            $stmt = $this->_pdo->prepare( $sql );
            $stmt->execute( $bindParams );
            $record = $stmt->fetch( \PDO::FETCH_NUM );
         }
         if ( \is_array( $record ) && isset( $record[ 0 ] ) )
         {
            return $record[ 0 ];
         }
         return $defaultValue;
      }
      catch ( \Throwable $ex )
      {
         throw new QueryException( $this->_driver, $sql, $bindParams, 'Can not fetch a scalar value from query!' );
      }

   }

   /**
    * Fetches the first found scalar value from defined SQL query string and returns it.
    *
    * @param  string $sql          The SQL query string
    * @param  array  $bindParams   Optional bind params for prepared statements
    * @param  int|string $columnIndexOrName The index or name of the required column
    * @return mixed
    * @throws \Niirrty\DB\ConnectionException If no connection exists an creation fails.
    * @throws \Niirrty\DB\QueryException      If the query execution fails
    */
   public final function fetchColumn( $columnIndexOrName, string $sql, array $bindParams = [] ) : array
   {

      $fetchStyle = \is_int( $columnIndexOrName ) ? \PDO::FETCH_NUM : \PDO::FETCH_ASSOC;

      $out = [];

      foreach ( $this->fetchIterateAll( $sql, $bindParams, $fetchStyle ) as $record )
      {
         if ( ! \is_array( $record ) || ! isset( $record[ $columnIndexOrName ] ) ) { continue; }
         $out[] = $record[ $columnIndexOrName ];
      }

      return $out;

   }

   /**
    * Fetches the first found scalar value from defined SQL query string and returns it.
    *
    * @param  string $sql          The SQL query string
    * @param  array  $bindParams   Optional bind params for prepared statements
    * @return mixed
    * @throws \Niirrty\DB\ConnectionException If no connection exists an creation fails.
    * @throws \Niirrty\DB\QueryException      If the query execution fails
    */
   public final function exec( string $sql, array $bindParams = [] ) : Connection
   {

      $this->open();

      try
      {

         if ( 1 > \count( $bindParams ) )
         {
            $this->_pdo->exec( $sql );
         }
         else
         {
            $stmt = $this->_pdo->prepare( $sql );
            $stmt->execute( $bindParams );
         }

      }
      catch ( \Throwable $ex )
      {
         throw new QueryException( $this->_driver, $sql, $bindParams, 'Can not execute the query!' );
      }

      return $this;

   }

   // </editor-fold>


   // </editor-fold>


}

