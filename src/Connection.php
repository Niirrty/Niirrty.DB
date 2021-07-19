<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Niirrty
 * @package        Niirrty\DB
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\DB;


use \Niirrty\DB\Driver\Attribute\Type;
use \Niirrty\DB\Driver\IDriver;


/**
 * This class defines the database connection. It always requires the definition of a DB Driver.
 *
 * @since v0.1.0
 */
class Connection
{


    #region // – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –

    /**
     * The usable PDO connection, or null if no connection is open.
     *
     * @type \PDO|null
     */
    private ?\PDO $_pdo;

    #endregion


    #region // – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –

    /**
     * Connection constructor.
     *
     * @param IDriver $driver The DBMS depending driver implementation
     */
    public function __construct( private IDriver $driver ) { }

    #endregion


    #region // – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –


    #region // – – –   G E T T E R   – – – – – – – – – – – – –

    /**
     * Gets the DBMS depending driver implementation
     *
     * @return IDriver
     */
    public function getDriver(): IDriver
    {

        return $this->driver;

    }

    /**
     * Gets the used PDO instance, or null if none is defined.
     *
     * @return \PDO|null
     */
    public function getPDO(): ?\PDO
    {

        return $this->_pdo;

    }

    #endregion


    #region // – – –   S E T T E R   – – – – – – – – – – – – –

    /**
     * Sets the DBMS Driver
     *
     * @param IDriver $driver
     *
     * @return Connection
     */
    public function setDriver( IDriver $driver ): Connection
    {

        $this->driver = $driver;
        $this->_pdo = null;

        return $this;

    }

    #endregion


    #region // – – –   O T H E R   – – – – – – – – – – – – – -

    /**
     * Gets if the connection is valid configured.
     *
     * @return bool
     */
    public function hasValidConfig(): bool
    {

        return $this->driver->getAttributeSupport()
                            ->haveAllRequiredAttributes( $this->driver->getDefinedAttributes() );

    }

    /**
     * Gets if a usable connection is open
     *
     * @return bool
     */
    public final function isOpen(): bool
    {

        return null !== $this->_pdo;

    }

    /**
     * Opens a connection if none is open.
     *
     * @return Connection
     * @throws ConnectionException
     */
    public final function open(): Connection
    {

        if ( $this->isOpen() )
        {
            return $this;
        }

        $attrSupport = $this->driver->getAttributeSupport();
        $definedAttributes = $this->driver->getDefinedAttributes();

        $dsn = $this->driver->getType() . ':';
        $dsnC = 0;
        $user = null;
        $pass = null;
        $sql = [];
        $opts = [];

        foreach ( $definedAttributes as $attrName => $attrValue )
        {

            $attrDesc = $attrSupport->get( $attrName );

            if ( null === $attrDesc )
            {
                continue;
            }

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
            if ( $this->driver->getType() === 'sqlite' || ( null === $user && null === $pass ) )
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
            throw new ConnectionException( $this->driver, 'Connection init fails!', 256, $ex );
        }

        if ( 0 < \count( $sql ) )
        {

            foreach ( $sql as $sqlString )
            {
                try
                {
                    $this->_pdo->exec( $sqlString );
                }
                catch ( \Throwable $ex )
                {
                    throw new QueryException(
                        $this->driver, $sqlString, [], 'Can not call the initial query!', 256, $ex
                    );
                }
            }

        }

        return $this;

    }

    /**
     * Closes the current connection.
     *
     * @return Connection
     */
    public final function close(): Connection
    {

        $this->_pdo = null;

        return $this;

    }

    /**
     * Fetches all records from defined SQL query string and returns all as a array.
     *
     * @param string $sql        The SQL query string
     * @param array  $bindParams Optional bind params for prepared statements
     * @param int    $fetchStyle See \PDO::FETCH_* constants
     *
     * @return array
     * @throws ConnectionException If no connection exists an creation fails.
     * @throws QueryException      If the query execution fails
     */
    public final function fetchAll( string $sql, array $bindParams = [], int $fetchStyle = \PDO::FETCH_ASSOC ): array
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
            throw new QueryException(
                $this->driver,
                $sql,
                $bindParams,
                'Can not fetch all data from query! ' . $ex->getMessage() );
        }

    }

    /**
     * Fetches all records from defined SQL query string and returns all as a Generator.
     *
     * @param string $sql        The SQL query string
     * @param array  $bindParams Optional bind params for prepared statements
     * @param int    $fetchStyle See \PDO::FETCH_* constants
     *
     * @return \Generator
     * @throws ConnectionException If no connection exists an creation fails.
     * @throws QueryException      If the query execution fails
     */
    public final function fetchIterateAll(
        string $sql, array $bindParams = [], int $fetchStyle = \PDO::FETCH_ASSOC ): \Generator
    {

        $this->open();

        try
        {
            if ( 1 > \count( $bindParams ) )
            {
                $stmt = $this->_pdo->query( $sql );
            }
            else
            {
                $stmt = $this->_pdo->prepare( $sql );
                $stmt->execute( $bindParams );
            }
            while ( $record = $stmt->fetch( $fetchStyle ) )
            {
                yield $record;
            }
        }
        catch ( \Throwable $ex )
        {
            throw new QueryException(
                $this->driver,
                $sql,
                $bindParams,
                'Can not fetch all data from query! ' . $ex->getMessage() );
        }

    }

    /**
     * Fetches the first found record from defined SQL query string and returns it as a associative array.
     *
     * @param string $sql        The SQL query string
     * @param array  $bindParams Optional bind params for prepared statements
     * @param int    $fetchStyle See \PDO::FETCH_* constants
     *
     * @return array|null
     * @throws ConnectionException If no connection exists an creation fails.
     * @throws QueryException      If the query execution fails
     */
    public final function fetchRecord( string $sql, array $bindParams = [], int $fetchStyle = \PDO::FETCH_ASSOC ): ?array
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
            throw new QueryException(
                $this->driver,
                $sql,
                $bindParams,
                'Can not fetch a single record from query! ' . $ex->getMessage() );
        }

    }

    /**
     * Fetches the first found scalar value from defined SQL query string and returns it.
     *
     * @param string     $sql          The SQL query string
     * @param array      $bindParams   Optional bind params for prepared statements
     * @param mixed|null $defaultValue Is returned if no value could be found.
     *
     * @return mixed
     * @throws ConnectionException If no connection exists an creation fails.
     * @throws QueryException      If the query execution fails
     */
    public final function fetchScalar( string $sql, array $bindParams = [], mixed $defaultValue = null ) : mixed
    {

        $this->open();

        try
        {
            if ( 1 > \count( $bindParams ) )
            {
                $stmt = $this->_pdo->query( $sql );
            }
            else
            {
                $stmt = $this->_pdo->prepare( $sql );
                $stmt->execute( $bindParams );
            }
            $record = $stmt->fetch( \PDO::FETCH_NUM );
            if ( \is_array( $record ) && isset( $record[ 0 ] ) )
            {
                return $record[ 0 ];
            }

            return $defaultValue;
        }
        catch ( \Throwable $ex )
        {
            throw new QueryException(
                $this->driver,
                $sql,
                $bindParams,
                'Can not fetch a scalar value from query! ' . $ex->getMessage() );
        }

    }

    /**
     * Fetches the first found scalar value from defined SQL query string and returns it.
     *
     * @param string     $sql               The SQL query string
     * @param array      $bindParams        Optional bind params for prepared statements
     * @param int|string $columnIndexOrName The index or name of the required column
     *
     * @return mixed
     * @throws ConnectionException If no connection exists an creation fails.
     * @throws QueryException      If the query execution fails
     */
    public final function fetchColumn( int|string $columnIndexOrName, string $sql, array $bindParams = [] ): array
    {

        $fetchStyle = \is_int( $columnIndexOrName ) ? \PDO::FETCH_NUM : \PDO::FETCH_ASSOC;

        $out = [];

        foreach ( $this->fetchIterateAll( $sql, $bindParams, $fetchStyle ) as $record )
        {
            if ( ! \is_array( $record ) || ! isset( $record[ $columnIndexOrName ] ) )
            {
                continue;
            }
            $out[] = $record[ $columnIndexOrName ];
        }

        return $out;

    }

    /**
     * Fetches the first found scalar value from defined SQL query string and returns it.
     *
     * @param string $sql        The SQL query string
     * @param array  $bindParams Optional bind params for prepared statements
     *
     * @return mixed
     * @throws ConnectionException If no connection exists an creation fails.
     * @throws QueryException      If the query execution fails
     */
    public final function exec( string $sql, array $bindParams = [] ): Connection
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
            throw new QueryException(
                $this->driver,
                $sql,
                $bindParams,
                'Can not execute the query! ' . $ex->getMessage() );
        }

        return $this;

    }

    #endregion


    #endregion


}

