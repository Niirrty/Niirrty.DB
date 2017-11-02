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


use Niirrty\NiirrtyException;


/**
 * The base database exception
 *
 * @since v0.1
 */
class DBException extends NiirrtyException
{


   # <editor-fold desc="= = =   P U B L I C   C O N S T U C T O R   = = = = = = = = = = = = = = = = = = = = = =">

   /**
    * Exception constructor.
    *
    * @param string          $message
    * @param int             $code
    * @param \Throwable|null $previous
    */
   public function __construct( string $message, $code = \E_USER_WARNING, \Throwable $previous = null )
   {

      parent::__construct( $message, $code, $previous );

   }

   # </editor-fold>


}

