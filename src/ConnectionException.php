<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2016-2021, Niirrty
 * @package        Niirrty\DB
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\DB;


use \Niirrty\DB\Driver\IDriver;


/**
 * Class ConnectionException.
 *
 * @since v0.1.0
 */
class ConnectionException extends DBException
{


    #region = = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =

    /**
     * ConnectionException constructor.
     *
     * @param IDriver         $driver
     * @param string|null     $message
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        private IDriver $driver, ?string $message = null, $code = 256, \Throwable $previous = null )
    {

        parent::__construct(
            \ucfirst( $driver->getType() )
            . ' connection error: '
            . $driver->getInfoString()
            . static::appendMessage( $message ),
            $code,
            $previous
        );

    }

    #endregion


    public final function getDriver(): IDriver
    {

        return $this->driver;

    }


}

