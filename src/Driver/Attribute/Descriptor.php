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
 * Describes a Driver specific DSN argument
 */
class Descriptor
{


   // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * The name of the described attribute.
    *
    * @type string
    */
   private $_name;

   /**
    * The described attribute is required or not?
    *
    * @type bool
    */
   private $_required;

   /**
    * The default value of the described attribute.
    *
    * @type string|null
    */
   private $_defaultValue;

   /**
    * The argument value validator closure/callback or NULL if no validation should performed.
    *
    * @type \Closure|null
    */
   private $_validator;

   /**
    * Should the attribute use a name if its a part of the DSN?
    *
    * @type bool
    */
   private $_useNameInDSN;

   /**
    * The attribute type. Use one of the `TYPE_*` constants defined by the `Type` class
    *
    * @type string
    */
   private $_type;

   /** @type ValueMissedLink|null */
   private $_valueMissedLink;

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

   /**
    * Descriptor constructor.
    *
    * @param string $name      The name of the described attribute.
    * @param string $type      The attribute type. Use one of the `TYPE_*` constants defined by the `Type` class
    * @param bool   $required  Defines if the described attribute is required or not?
    */
   public function __construct( string $name, string $type, bool $required = false )
   {

      $this->_name            = $name;
      $this->_required        = $required;
      $this->_defaultValue    = null;
      $this->_validator       = null;
      $this->_useNameInDSN    = true;
      $this->_type            = $type;
      $this->_valueMissedLink = null;

   }

   // </editor-fold>


   // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

   /**
    * Gets the name of the described attribute.
    *
    * @return string
    */
   public function getName() : string
   {

      return $this->_name;

   }

   /**
    * Gets if the described attribute is required or not?
    *
    * @return bool
    */
   public function isRequired() : bool
   {

      return $this->_required;

   }

   /**
    * Gets the attribute type. Use one of the `TYPE_*` constants defined by the `Type` class
    *
    * @return string
    */
   public function getType() : string
   {

      return $this->_type;

   }

   /**
    * Gets if the attribute is a part of the DSN.
    *
    * @return boolean
    */
   public function isDSNPart() : bool
   {

      return $this->_type === Type::DSN_PART;

   }

   /**
    * Gets if the attribute should be user as DBMS auth user name.
    *
    * @return boolean
    */
   public function isUserNameParam() : bool
   {

      return $this->_type === Type::DSN_PART;

   }

   /**
    * Gets if the attribute should be the DBMS auth password.
    *
    * @return boolean
    */
   public function isPasswordParam() : bool
   {

      return $this->_type === Type::DSN_PART;

   }

   /**
    * Gets the default value of the described attribute.
    *
    * @return null|string
    */
   public function getDefaultValue() : ?string
   {

      return $this->_defaultValue;

   }

   /**
    * Gets if an alternative attribute is defined, that must exists if this attribute is not defined.
    *
    * @return bool
    */
   public function hasValueMissedLink() : bool
   {

      return null === $this->_valueMissedLink;

   }

   /**
    * Gets the alternative attribute if defined, that must exists if this attribute is not defined.
    *
    * @return \Niirrty\DB\Driver\Attribute\ValueMissedLink|null
    */
   public function getValueMissedLink() : ?ValueMissedLink
   {

      return $this->_valueMissedLink;

   }

   /**
    * Gets if a default value is defined.
    *
    * @return bool
    */
   public function hasDefaultValue() : bool
   {

      return null !== $this->_defaultValue && '' !== $this->_defaultValue;

   }

   /**
    * Gets if a usable value validator is defined.
    *
    * @return bool
    */
   public function hasValidator() : bool
   {

      return null !== $this->_validator;

   }

   /**
    * Gets, if the attribute name should be written into DSN if its a DSN part.
    *
    * This is helpful for example, if you will use **SQLite**, the DSN is `sqlite:%dbfile%` or `sqlite::memory:`
    *
    * @return bool
    */
   public function useNameInDSN() : bool
   {

      return $this->_useNameInDSN;

   }


   /**
    * Sets the alternative attribute if defined, that must exists if this attribute is not defined.
    *
    * @param  null|\Niirrty\DB\Driver\Attribute\ValueMissedLink $link
    * @return \Niirrty\DB\Driver\Attribute\Descriptor
    */
   public function setValueMissedLink( ?ValueMissedLink $link ) : Descriptor
   {

      $this->_valueMissedLink = $link;

      return $this;

   }

   /**
    * Sets if the described attribute is required or not?
    *
    * @param  bool $required
    * @return Descriptor
    */
   public function setIsRequired( bool $required ) : Descriptor
   {

      $this->_required = $required;

      return $this;

   }

   /**
    * Sets the default value of the described attribute.
    *
    * @param  null|string $defaultValue
    * @return Descriptor
    */
   public function setDefaultValue( ?string $defaultValue ) : Descriptor
   {

      $this->_defaultValue = ( null === $defaultValue ) ? $defaultValue : \trim( $defaultValue );

      return $this;

   }

   /**
    * Sets the argument value validator closure/callback or NULL if no validation should performed.
    * It must accept a single parameter (the value to check) and must return a boolean value
    * (TRUE on success, FALSE otherwise)
    *
    * @param \Closure|null $validator
    * @return Descriptor
    */
   public function setValidator( ?\Closure $validator ) : Descriptor
   {

      $this->_validator = $validator;

      return $this;

   }

   /**
    * Sets, if the attribute name should be written into DSN if its a DSN part.
    *
    * This is helpful for example, if you will use **SQLite**, the DSN is `sqlite:%dbfile%` or `sqlite::memory:`
    *
    * @param bool $useNameInDSN
    * @return Descriptor
    */
   public function setUseNameInDSN( bool $useNameInDSN ) : Descriptor
   {

      $this->_useNameInDSN = $useNameInDSN;

      return $this;

   }


   /**
    * Checks if the value is a valid value of this DSN argument.
    *
    * @param $value
    * @return bool
    */
   public function validateValue( $value ) : bool
   {

      if ( false === $this->hasValidator() )
      {
         return true;
      }

      return (bool) ( $this->_validator )( $value );

   }


   // </editor-fold>


}

