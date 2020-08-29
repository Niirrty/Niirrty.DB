<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Niirrty
 * @package        Niirrty\DB\Driver\Attribute
 * @since          2017-11-01
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\DB\Driver;


use Niirrty\ArgumentException;
use Niirrty\DB\Driver\Attribute\Support;


/**
 * The abstract DB driver base class.
 *
 * Overwriting classes have only to implement the constructor by calling parent constructor.
 */
abstract class AbstractDriver implements IDriver
{


    // <editor-fold desc="// – – –   P R O T E C T E D   F I E L D S   – – – – – – – – – – – – – – – – – – – – – –">


    /**
     * Support of driver specific attributes.
     *
     * @type Support
     */
    protected $_supportedAttributes;

    /**
     * The driver type name. Must be defined by a constant of class {@see \Niirrty\DB\DbType}.
     *
     * @type string
     */
    protected $_type;

    /**
     * All driver specific attribute values.
     *
     * @type array
     */
    protected $_attributes;

    // </editor-fold>


    // <editor-fold desc="// – – –   P R O T E C T E D   C O N S T R U C T O R   – – – – – – – – – – – – – – – – –">

    protected function __construct( string $type, Support $supportedAttributes )
    {

        $this->_type = $type;
        $this->_supportedAttributes = $supportedAttributes;
        $this->_attributes = [];

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

    /**
     * Gets the driver type name. Must be defined by a constant of {@see \Niirrty\DB\DbType}.
     *
     * @return string
     */
    public function getType(): string
    {

        return $this->_type;

    }

    /**
     * Gets the support of driver specific attributes.
     *
     * @return Support
     */
    public function getAttributeSupport(): Support
    {

        return $this->_supportedAttributes;

    }

    /**
     * Sets the value of a driver specific Attribute.
     *
     * @param string $name  The name of the attribute
     * @param mixed  $value The attribute value
     *
     * @return AbstractDriver
     * @throws ArgumentException If a unknown attribute should be defined
     */
    public function setAttribute( string $name, $value )
    {

        if ( !$this->_supportedAttributes->has( $name ) )
        {
            // Unknown attribute
            throw new ArgumentException(
                'name', $name,
                'Can not set the DB driver "' . $this->_type . '" not supports a attribute with this name!'
            );
        }

        if ( !$this->_supportedAttributes->get( $name )->validateValue( $value ) )
        {
            // Invalid value
            throw new ArgumentException(
                'value', $value, 'Can not set the DB driver "' . $this->_type . '" attribute "' . $name .
                                 '" value, because the value is invalid!'
            );
        }

        $this->_attributes[ $name ] = $value;

        return $this;

    }

    /**
     * Gets the value of a driver specific Attribute.
     *
     * @param string $name
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public function getAttribute( string $name, $defaultValue = false )
    {

        if ( !$this->hasAttribute( $name ) )
        {
            return $defaultValue;
        }

        return $this->_attributes[ $name ];

    }

    /**
     * Gets if a attribute exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute( string $name ): bool
    {

        return \array_key_exists( $name, $this->_attributes );

    }

    /**
     * Gets the names of all currently defined attributes.
     *
     * @return array
     */
    public function getNamesOfDefinedAttributes(): array
    {

        return \array_keys( $this->_attributes );

    }

    /**
     * Gets all currently defined attributes.
     *
     * @return array
     */
    public function getDefinedAttributes(): array
    {

        return $this->_attributes;
    }

    /**
     * Gets the attribute with defined name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get( $name )
    {

        return $this->getAttribute( $name );

    }

    /**
     * Sets the value of a driver specific Attribute.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set( $name, $value )
    {

        if ( !$this->_supportedAttributes->has( $name ) )
        {
            // Unknown attribute
            return;
        }

        if ( !$this->_supportedAttributes->get( $name )->validateValue( $value ) )
        {
            // Invalid value
            return;
        }

        $this->_attributes[ $name ] = $value;

    }

    /**
     * Checks if a value of the attribute with defined name is defined.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset( $name )
    {

        return $this->hasAttribute( $name );

    }


    // </editor-fold>


}

