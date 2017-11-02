<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2016, Niirrty
 * @package        Niirrty\DB\Driver\Attribute
 * @since          2017-11-01
 * @version        0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\DB\Driver\Attribute;


/**
 * Defines all usable driver attribute types.
 *
 * @since v0.1.0
 */
interface Type
{

   /**
    * The Attribute should be used as a part of the PDO DSN parameter
    */
   public const DSN_PART       = 'DSN';

   /**
    * The Attribute should be used as 2nd PDO $username parameter.
    */
   public const USERNAME_PARAM = 'USER';

   /**
    * The Attribute should be used as 3rd PDO $password parameter.
    */
   public const PASSWORD_PARAM = 'PASS';

   /**
    * The Attribute should be used as 4rd PDO parameter part.
    */
   public const OPTION_PARAM   = 'OPT';

   /**
    * THe attribute value is a SQL statement that should be called after connection creation.
    */
   public const INIT_SQL       = 'SQL';

   public const KNOWN_TYPES    = [
      self::DSN_PART, self::USERNAME_PARAM, self::PASSWORD_PARAM, self::OPTION_PARAM, self::INIT_SQL
   ];


}

