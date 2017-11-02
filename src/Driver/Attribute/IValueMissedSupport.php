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
 * …
 *
 * @since v0.1.0
 */
interface IValueMissedSupport
{

   /**
    * Gets if an attribute with defined name is defined
    *
    * @param string $attributeName
    * @return bool
    */
   public function hasAttribute( string $attributeName ) : bool;

}

