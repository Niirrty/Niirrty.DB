<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Niirrty
 * @package        Niirrty\DB\Driver
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\DB\Driver;


use \Niirrty\ArgumentException;
use \Niirrty\DB\{DbType, QueryException};
use \Niirrty\DB\Driver\Attribute\{Descriptor, Support, Type};
use \Niirrty\TypeTool;


/**
 * Defines the PostGreSQL database driver.
 *
 * Usable Driver connection attributes are:
 *
 * - host
 * - port
 * - user
 * - password
 * - dbname
 * - charset
 */
final class PgSQL extends AbstractDriver
{


    #region // – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –


    /**
     * PgSqlDriver constructor.
     */
    public function __construct()
    {

        $support = ( new Support() )
            ->setAttributeSeparator( ';' )
            ->setDSNKeyValueSeparator( '=' )

            // Database name
            ->add(
                ( new Descriptor( 'dbname', Type::DSN_PART, false ) )
                    ->setValidator( function ( $value )
                        {

                            if ( ! \is_string( $value ) || '' === \trim( $value ) )
                            {
                                return false;
                            }

                            return (bool) \preg_match( '~^[a-z]([a-z0-9_]+)?$~', $value );
                        }
                    )
            )

            // Host name (optionally, if not defined a socket will been opened if available.)
            ->add(
                ( new Descriptor( 'host', Type::DSN_PART, false ) )
                    ->setValidator( function ( $value )
                        {

                            if ( ! \is_string( $value ) || '' === \trim( $value ) )
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

            // The optional auth user
            ->add(
                ( new Descriptor( 'user', Type::DSN_PART, false ) )
                    ->setValidator( function ( $value )
                        {

                            if ( ! \is_string( $value ) || '' === \trim( $value ) )
                            {
                                return false;
                            }
                            $len = \mb_strlen( $value );

                            return $len >= 1 && $len < 51;
                        }
                    )
            )

            // The optional auth password
            ->add(
                ( new Descriptor( 'password', Type::DSN_PART, false ) )
                    ->setValidator( function ( $value )
                        {

                            if ( ! \is_string( $value ) || '' === \trim( $value ) )
                            {
                                return false;
                            }
                            $len = \mb_strlen( $value );

                            return $len >= 1 && $len < 129;
                        }
                    )
            )

            // Optional port number 100-65554
            ->add(
                ( new Descriptor( 'port', Type::DSN_PART, false ) )
                    ->setValidator( function ( $value )
                        {

                            if ( ! \is_string( $value ) && !\is_int( $value ) )
                            {
                                return false;
                            }
                            $port = \intval( $value );

                            return $port > 99 && $port < 65555;
                        }
                    )
            )

            // Optional charset as SQL part called after init.
            ->add(
                ( new Descriptor( 'charset', Type::INIT_SQL, false ) )
                    ->setValidator( function ( $value )
                        {

                            if ( ! \is_string( $value ) || '' === \trim( $value ) )
                            {
                                return false;
                            }
                            $len = \mb_strlen( $value );

                            return $len > 0 && $len < 17;
                        }
                    )
            );

        parent::__construct( DbType::PGSQL, $support );

    }

    #endregion

    /**
     * Gets all connection info as string
     *
     * @return string
     */
    public function getInfoString(): string
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
    public function getHost(): ?string
    {

        return $this->_attributes[ 'host' ] ?? null;

    }

    /**
     * Gets the optional port number of the PgSQL server.
     *
     * @return int|null
     */
    public function getPort(): ?int
    {

        return $this->_attributes[ 'port' ] ?? null;

    }

    /**
     * Gets the optional name of the initial connected database.
     *
     * @return null|string
     */
    public function getDbName(): ?string
    {

        return $this->_attributes[ 'dbname' ] ?? null;

    }

    /**
     * Gets the connection charset.
     *
     * @return string|null
     */
    public function getCharset(): ?string
    {

        return $this->_attributes[ 'charset' ] ?? null;

    }


    /**
     * Sets the host name or ip address of the PgSQL server.
     *
     * If you want to use a socket not call this method. (leave host empty/undefined)
     *
     * @param null|string $host
     *
     * @return PgSQL
     * @throws ArgumentException
     */
    public function setHost( ?string $host ): PgSQL
    {

        if ( !$this->supportedAttributes->get( 'host' )->validateValue( $host ) )
        {
            throw new ArgumentException( 'host', $host, 'Invalid host!' );
        }

        $this->_attributes[ 'host' ] = $host;

        return $this;

    }

    /**
     * Sets the optional port number of the PgSQL server.
     *
     * @param $port
     *
     * @return PgSQL
     * @throws ArgumentException
     */
    public function setPort( $port ): PgSQL
    {

        if ( null === $port )
        {
            $this->_attributes[ 'port' ] = $port;

            return $this;
        }

        $p = false;
        if ( \is_string( $port ) )
        {
            $p = (int) $port;
        }
        else if ( TypeTool::IsInteger( $port ) )
        {
            $p = (int) $port;
        }
        else if ( TypeTool::IsStringConvertible( $port, $portStr ) )
        {
            $p = (int) $portStr;
        }

        if ( ! \is_int( $p ) || !$this->supportedAttributes->get( 'port' )->validateValue( $port ) )
        {
            throw new ArgumentException( 'port', $port, 'Invalid port!' );
        }

        $this->_attributes[ 'port' ] = $p;

        return $this;

    }

    /**
     * Sets the optional name of the initial connected database.
     *
     * @param null|string $dbName
     *
     * @return PgSQL
     * @throws ArgumentException
     */
    public function setDbName( ?string $dbName ): PgSQL
    {

        if ( !$this->supportedAttributes->get( 'dbname' )->validateValue( $dbName ) )
        {
            throw new ArgumentException( 'dbname', $dbName, 'Invalid database name!' );
        }

        $this->_attributes[ 'dbname' ] = $dbName;

        return $this;

    }

    /**
     * Sets the connection charset.
     *
     * @param string $charset
     *
     * @return PgSQL
     * @throws ArgumentException
     */
    public function setCharset( string $charset = 'UTF8' ): PgSQL
    {

        if ( !$this->supportedAttributes->get( 'charset' )->validateValue( $charset ) )
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
     *
     * @return PgSQL
     * @throws ArgumentException
     */
    public function setAuthUserName( ?string $user ): PgSQL
    {

        if ( !$this->supportedAttributes->get( 'user' )->validateValue( $user ) )
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
     *
     * @return PgSQL
     * @throws ArgumentException
     */
    public function setAuthPassword( ?string $password ): PgSQL
    {

        if ( !$this->supportedAttributes->get( 'password' )->validateValue( $password ) )
        {
            throw new ArgumentException(
                'password', \str_repeat( '*', min( 48, \mb_strlen( $password, 'utf-8' ) ) ), 'Invalid auth password!' );
        }

        $this->_attributes[ 'password' ] = $password;

        return $this;

    }


    /**
     * @param \PDO   $pdo
     * @param string $tableName
     *
     * @return bool
     * @throws QueryException
     * @internal Gets if the table with defined name exists in selected database of current connection.
     */
    public function tableExists( \PDO $pdo, string $tableName ): bool
    {

        $tmp = \explode( '.', $tableName, 2 );

        $schema = 'public';

        if ( 2 === \count( $tmp ) )
        {
            $schema = $tmp[ 0 ];
            $tableName = $tmp[ 1 ];
        }

        $sql = 'SELECT COUNT(*) FROM pg_catalog.pg_class c
            JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
            WHERE  n.nspname = ? AND c.relname = ? AND c.relkind = \'r\'';
        $bindParams = [ $schema, $tableName ];

        try
        {
            $stmt = $pdo->prepare( $sql );
            $stmt->execute( $bindParams );
            $record = $stmt->fetch( \PDO::FETCH_NUM );
            $stmt = null;
            if ( !\is_array( $record ) || !isset( $record[ 0 ] ) )
            {
                return false;
            }

            return ( (int) $record[ 0 ] ) > 0;
        }
        catch ( \Throwable $ex )
        {
            throw new QueryException( $this, $sql, $bindParams, 'Can not check if a table exists.', 256, $ex );
        }

    }


}

