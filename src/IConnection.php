<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @since          2018-06-12
 * @version        0.2.0
 */


declare( strict_types = 1 );


namespace Niirrty\DB;


use Niirrty\DB\Driver\IDriver;


/**
 * A Niirrty DB connection must implement this interface.
 *
 * @package Niirrty\DB
 */
interface IConnection
{

   /**
    * Gets the DBMS depending driver implementation
    *
    * @return \Niirrty\DB\Driver\IDriver
    */
   public function getDriver() : IDriver;

   /**
    * Gets the used PDO instance, or null if none is defined.
    *
    * @return \PDO|null
    */
   public function getPDO() : ?\PDO;

   /**
    * Gets if the connection is valid configured.
    *
    * @return bool
    */
   public function hasValidConfig() : bool;

   /**
    * Gets if a usable connection is open
    *
    * @return bool
    */
   public function isOpen() : bool;

   /**
    * Opens a connection if none is open.
    *
    * @return \Niirrty\DB\IDbConnection
    * @throws \Niirrty\DB\ConnectionException
    */
   public function open();

   /**
    * Closes the current connection.
    *
    * @return \Niirrty\DB\IDbConnection
    */
   public function close();

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
   public function fetchAll( string $sql, array $bindParams = [], $fetchStyle = \PDO::FETCH_ASSOC ) : array;

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
   public function fetchIterateAll( string $sql, array $bindParams = [], $fetchStyle = \PDO::FETCH_ASSOC ) : \Generator;

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
   public function fetchRecord( string $sql, array $bindParams = [], $fetchStyle = \PDO::FETCH_ASSOC ) : ?array;

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
   public function fetchScalar( string $sql, array $bindParams = [], $defaultValue = null );

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
   public function fetchColumn( $columnIndexOrName, string $sql, array $bindParams = [] ) : array;

   /**
    * Execute a query.
    *
    * @param  string $sql          The SQL query string
    * @param  array  $bindParams   Optional bind params for prepared statements
    * @return IDbConnection
    * @throws \Niirrty\DB\ConnectionException If no connection exists an creation fails.
    * @throws \Niirrty\DB\QueryException      If the query execution fails
    */
   public function exec( string $sql, array $bindParams = [] );


}

