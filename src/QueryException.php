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


use \Niirrty\DB\Driver\IDriver;


/**
 * Defines a class that …
 *
 * @since v0.1.0
 */
class QueryException extends ConnectionException
{


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * QueryException constructor.
    *
    * @param \Niirrty\DB\Driver\IDriver  $driver
    * @param string          $query
    * @param array           $params
    * @param string|null     $message
    * @param int             $code
    * @param \Throwable|null $previous
    */
   public function __construct(
      IDriver $driver, string $query, array $params = [], ?string $message = null,
      $code = 256, \Throwable $previous = null )
   {

      $msg = "Bad query was:\n" . $query;
      if ( 0 < \count( $params ) )
      {
         \ob_start();
         \var_dump( $params );
         $paramsStr = \ob_get_contents();
         \ob_end_clean();
         $msg .= "\n\n with parameters:\n" . $paramsStr;
      }
      $msg .= static::appendMessage( $message );

      parent::__construct( $driver, $msg, $code, $previous );

   }

   // </editor-fold>


}

