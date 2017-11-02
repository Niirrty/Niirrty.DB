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
 * This class allow you to link a required but not defined attribute to an other attribute the should be defined
 * as a replacement or alternative.
 *
 * Worry? :-)
 *
 * For example: If the attribute 'host' is normally required but if its not defined, there is an other attribute
 * 'unix_socket' that must only be defined if the 'host' attribute is not declared. So the 'host' attribute is only
 * required if the 'unix_socket' attribute is not defined.
 *
 * Better? :-/
 *
 * @since v0.1.0
 */
class ValueMissedLink
{


   // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The basic collection of supported attributes
    *
    * @type IValueMissedSupport
    */
   private $_support;

   // </editor-fold>


   // <editor-fold desc="// – – –   P R O T E C T E D   F I E L D S   – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The name of the required attribute.
    *
    * @type string
    */
   protected $_requiredAttributeName;

   /**
    * The name of the other attribute that should be defined if the required not exists.
    *
    * @type string
    */
   protected $_altAttributeName;

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * ValueMissedLink constructor.
    *
    * @param \Niirrty\DB\Driver\Attribute\IValueMissedSupport $support
    * @param string                                      $required
    * @param string                                      $alt
    */
   public function __construct( IValueMissedSupport $support, string $required, string $alt )
   {

      $this->_support = $support;
      $this->_requiredAttributeName = $required;
      $this->_altAttributeName = $alt;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   public function isValid() : bool
   {

      if ( $this->_support->hasAttribute( $this->_requiredAttributeName ) )
      {
         return true;
      }

      return $this->_support->hasAttribute( $this->_altAttributeName );

   }

   // </editor-fold>


}

