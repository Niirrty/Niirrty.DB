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


/**
 * Defines the SQLite database driver.
 *
 * Usable Driver connection attributes are:
 *
 * - 'db' The SQLite database file path, or ':memory:' for using a temporary in memory db.
 */
final class SQLite extends AbstractDriver
{


    #region // – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –


    /**
     * SQLite driver constructor.
     */
    public function __construct()
    {

        $support = ( new Support() )
            ->setAttributeSeparator( ';' )
            ->setDSNKeyValueSeparator( '' )

            // Database name
            ->add(
                ( new Descriptor( 'db', Type::DSN_PART, true ) )
                    ->setUseNameInDSN( false )
                    ->setValidator( function ( $value )
                    {

                        if ( null === $value || !\is_string( $value ) || '' === \trim( $value ) )
                        {
                            return false;
                        }
                        if ( ':memory:' === $value )
                        {
                            return true;
                        }
                        if ( '\\' === \DIRECTORY_SEPARATOR )
                        {
                            return (bool) \preg_match( '~^[a-zA-Z0-9_.:\\\\!/$ -]+?$~', $value );
                        }

                        return (bool) \preg_match( '~^[a-zA-Z0-9_./!$ -]+?$~', $value );
                    }
                    )
            );

        parent::__construct( DbType::SQLITE, $support );

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

        if ( isset( $this->_attributes[ 'db' ] ) )
        {
            $out = 'db="' . $this->_attributes[ 'db' ] . '"';
        }

        return $out;

    }

    /**
     * Gets the database (file path or ':memory:').
     *
     * @return null|string
     */
    public function getDb(): ?string
    {

        return $this->_attributes[ 'db' ] ?? null;

    }

    /**
     * Sets the database (file path or ':memory:').
     *
     * @param string $db
     *
     * @return SQLite
     * @throws ArgumentException
     */
    public function setDb( string $db ): SQLite
    {

        if ( !$this->supportedAttributes->get( 'db' )->validateValue( $db ) )
        {
            throw new ArgumentException( 'db', $db, 'Invalid database!' );
        }

        $this->_attributes[ 'db' ] = $db;

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

        $sql = 'SELECT COUNT(*) AS cnt FROM sqlite_master WHERE type=\'table\' AND name=?';
        $bindParams = [ $tableName ];

        try
        {
            $stmt = $pdo->prepare( $sql );
            $stmt->execute( $bindParams );
            $record = $stmt->fetch( \PDO::FETCH_ASSOC );
            if ( !\is_array( $record ) || !isset( $record[ 'cnt' ] ) )
            {
                return false;
            }

            return ( (int) $record[ 'cnt' ] ) > 0;
        }
        catch ( \Throwable $ex )
        {
            throw new QueryException( $this, $sql, $bindParams, 'The SQL query execution fails.', 256, $ex );
        }

    }


}

