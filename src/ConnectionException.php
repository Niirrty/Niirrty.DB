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


use Niirrty\DB\Driver\IDriver;


/**
 * Class ConnectionException.
 *
 * @since v0.1.0
 */
class ConnectionException extends DBException
{


   private $_driver;


   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * ConnectionException constructor.
    *
    * @param \Niirrty\DB\Driver\IDriver $driver
    * @param string|null           $message
    * @param int                   $code
    * @param \Throwable|null       $previous
    */
   public function __construct(
      IDriver $driver, ?string $message = null, $code = 256, \Throwable $previous = null )
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

   # </editor-fold>

   public final function getDriver() : IDriver
   {

      return $this->_driver;

   }


}

