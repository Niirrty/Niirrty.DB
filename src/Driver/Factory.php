<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2016-2021, Niirrty
 * @package        Niirrty\DB\Driver
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\DB\Driver;


use \Niirrty\ArgumentException;
use \Niirrty\DB\DbType;
use \Niirrty\IO\File;
use \Niirrty\IO\FileFormatException;
use \Niirrty\IO\Vfs\VfsManager;
use \Niirrty\NiirrtyException;
use \Symfony\Component\Yaml\Yaml;


class Factory
{


    #region // – – –   P U B L I C   S T A T I C   M E T H O D S   – – – – – – – – – – – – – – – – –

    /**
     * @param string       $configFile
     * @param VfsManager|null $vfsManager Optional VFS manager
     *
     * @return IDriver
     * @throws FileFormatException|NiirrtyException
     */
    public static function FromConfigFile( string $configFile, ?VfsManager $vfsManager = null ): IDriver
    {

        if ( null !== $vfsManager )
        {
            $configFile = $vfsManager->parsePath( $configFile );
        }
        $ext = \strtolower( File::GetExtensionName( $configFile ) );
        $data = [];

        switch ( $ext )
        {

            case 'php':
                try
                {
                    /** @noinspection PhpIncludeInspection */
                    $data = include $configFile;
                }
                catch ( \Throwable $ex )
                {
                    throw new FileFormatException(
                        $configFile,
                        'Unable to parse the DB Driver config file as PHP format.',
                        254,
                        $ex
                    );
                }
                if ( ! \is_array( $data ) || \count( $data ) < 1 )
                {
                    throw new FileFormatException(
                        $configFile,
                        'Unable to parse the DB Driver config file as PHP format. It contains no valid data!' );
                }
                break;

            case 'json':
                try
                {
                    $data = \json_decode( file_get_contents( $configFile ), true );
                }
                catch ( \Throwable $ex )
                {
                    throw new FileFormatException(
                        $configFile,
                        'Unable to parse the DB Driver config file as JSON format.',
                        254,
                        $ex );
                }
                if ( ! \is_array( $data ) || \count( $data ) < 1 )
                {
                    throw new FileFormatException(
                        $configFile,
                        'Unable to parse the DB Driver config file as JSON format. It contains no valid data!' );
                }
                break;

            case 'yml':
            case 'yaml':
                try
                {
                    $data = Yaml::parse( file_get_contents( $configFile ) );
                }
                catch ( \Throwable $ex )
                {
                    throw new FileFormatException(
                        $configFile,
                        'Unable to parse the DB Driver config file as YAML format.',
                        254,
                        $ex );
                }
                break;

            default:
                break;

        }

        return self::FromArray( $data );

    }

    /**
     * @param array $configData
     *
     * @return IDriver|null
     * @throws NiirrtyException
     * @throws ArgumentException
     */
    public static function FromArray( array $configData ): ?IDriver
    {

        if ( !isset( $configData[ 'type' ] ) && !isset( $configData[ 'platform' ] ) )
        {
            throw new NiirrtyException( 'Invalid driver config data. Missing a type or platform declaration!' );
        }

        $type = $configData[ 'type' ] ?? $configData[ 'platform' ];

        if ( ! \in_array( $type, DbType::KNOWN_TYPES ) )
        {
            throw new NiirrtyException( "Invalid driver config data. The type '{$type}' is unknown!" );
        }

        switch ( $type )
        {

            case DbType::PGSQL:
                $drv = ( new PgSQL() )
                    ->setHost( $configData[ 'host' ] ?? null )
                    ->setCharset( $configData[ 'charset' ] ?? 'UTF8' );
                if ( isset( $configData[ 'db' ] ) )
                {
                    $drv->setDbName( $configData[ 'db' ] );
                }
                else if ( isset( $configData[ 'database' ] ) )
                {
                    $drv->setDbName( $configData[ 'database' ] );
                }
                else if ( isset( $configData[ 'dbname' ] ) )
                {
                    $drv->setDbName( $configData[ 'dbname' ] );
                }
                if ( isset( $configData[ 'port' ] ) && 0 < (int) $configData[ 'port' ] )
                {
                    $drv->setPort( (int) $configData[ 'port' ] );
                }
                if ( isset( $configData[ 'password' ] ) )
                {
                    $drv->setAuthPassword( $configData[ 'password' ] );
                }
                else if ( isset( $configData[ 'pwd' ] ) )
                {
                    $drv->setAuthPassword( $configData[ 'pwd' ] );
                }
                else if ( isset( $configData[ 'passwd' ] ) )
                {
                    $drv->setAuthPassword( $configData[ 'passwd' ] );
                }
                if ( isset( $configData[ 'user' ] ) )
                {
                    $drv->setAuthUserName( $configData[ 'user' ] );
                }
                else if ( isset( $configData[ 'username' ] ) )
                {
                    $drv->setAuthUserName( $configData[ 'username' ] );
                }
                break;

            case DbType::MYSQL:
                $drv = ( new MySQL() )
                    ->setHost( $configData[ 'host' ] ?? null )
                    ->setCharset( $configData[ 'charset' ] ?? 'UTF8' );
                if ( isset( $configData[ 'port' ] ) && 0 < (int) $configData[ 'port' ] )
                {
                    $drv->setPort( (int) $configData[ 'port' ] );
                }
                if ( isset( $configData[ 'db' ] ) )
                {
                    $drv->setDbName( $configData[ 'db' ] );
                }
                else if ( isset( $configData[ 'database' ] ) )
                {
                    $drv->setDbName( $configData[ 'database' ] );
                }
                else if ( isset( $configData[ 'dbname' ] ) )
                {
                    $drv->setDbName( $configData[ 'dbname' ] );
                }
                if ( isset( $configData[ 'user' ] ) )
                {
                    $drv->setAuthUserName( $configData[ 'user' ] );
                }
                else if ( isset( $configData[ 'username' ] ) )
                {
                    $drv->setAuthUserName( $configData[ 'username' ] );
                }
                if ( isset( $configData[ 'password' ] ) )
                {
                    $drv->setAuthPassword( $configData[ 'password' ] );
                }
                else if ( isset( $configData[ 'pwd' ] ) )
                {
                    $drv->setAuthPassword( $configData[ 'pwd' ] );
                }
                else if ( isset( $configData[ 'passwd' ] ) )
                {
                    $drv->setAuthPassword( $configData[ 'passwd' ] );
                }
                if ( isset( $configData[ 'socket' ] ) )
                {
                    $drv->setUnixSocket( $configData[ 'socket' ] ?? null );
                }
                else if ( isset( $configData[ 'unixsocket' ] ) )
                {
                    $drv->setUnixSocket( $configData[ 'unixsocket' ] ?? null );
                }
                else if ( isset( $configData[ 'unix_socket' ] ) )
                {
                    $drv->setUnixSocket( $configData[ 'unix_socket' ] ?? null );
                }
                else if ( isset( $configData[ 'unixSocket' ] ) )
                {
                    $drv->setUnixSocket( $configData[ 'unixSocket' ] ?? null );
                }
                break;

            #case DbType::SQLITE:
            default:
                $drv = new SQLite();
                if ( isset( $configData[ 'db' ] ) )
                {
                    $drv->setDb( $configData[ 'db' ] );
                }
                else if ( isset( $configData[ 'database' ] ) )
                {
                    $drv->setDb( $configData[ 'database' ] );
                }
                else if ( isset( $configData[ 'dbname' ] ) )
                {
                    $drv->setDb( $configData[ 'dbname' ] );
                }
                else if ( isset( $configData[ 'dbfile' ] ) )
                {
                    $drv->setDb( $configData[ 'dbfile' ] );
                }
                break;

        }

        return $drv;

    }

    #endregion


}

