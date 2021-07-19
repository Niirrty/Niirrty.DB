<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Niirrty
 * @package        Niirrty\DB\Driver
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\DB\Driver;


use \Niirrty\ArgumentException;
use \Niirrty\DB\Driver\Attribute\Support;


/**
 * The abstract DB driver base class.
 *
 * Overwriting classes have only to implement the constructor by calling parent constructor.
 */
abstract class AbstractDriver implements IDriver
{


    #region // – – –   P R O T E C T E D   F I E L D S   – – – – – – – – – – – – – – – – – – – – – –

    /**
     * All driver specific attribute values.
     *
     * @type array
     */
    protected array $_attributes;

    #endregion


    #region // – – –   P R O T E C T E D   C O N S T R U C T O R   – – – – – – – – – – – – – – – – –

    /**
     * AbstractDriver constructor.
     *
     * @param string  $type The driver type name. Must be defined by a constant of class {@see \Niirrty\DB\DbType}.
     * @param Support $supportedAttributes Support of driver specific attributes.
     */
    protected function __construct( protected string $type, protected Support $supportedAttributes )
    {

        $this->_attributes         = [];

    }

    #endregion


    #region // – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –

    /**
     * Gets the driver type name. Must be defined by a constant of {@see \Niirrty\DB\DbType}.
     *
     * @return string
     */
    public function getType(): string
    {

        return $this->type;

    }

    /**
     * Gets the support of driver specific attributes.
     *
     * @return Support
     */
    public function getAttributeSupport(): Support
    {

        return $this->supportedAttributes;

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
    public function setAttribute( string $name, mixed $value ) : AbstractDriver
    {

        if ( ! $this->supportedAttributes->has( $name ) )
        {
            // Unknown attribute
            throw new ArgumentException(
                'name', $name,
                'Can not set the DB driver "' . $this->type . '" not supports a attribute with this name!'
            );
        }

        if ( ! $this->supportedAttributes->get( $name )->validateValue( $value ) )
        {
            // Invalid value
            throw new ArgumentException(
                'value', $value, 'Can not set the DB driver "' . $this->type . '" attribute "' . $name .
                                 '" value, because the value is invalid!'
            );
        }

        $this->_attributes[ $name ] = $value;

        return $this;

    }

    /**
     * Gets the value of a driver specific Attribute.
     *
     * @param string     $name
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function getAttribute( string $name, mixed $defaultValue = false ) : mixed
    {

        if ( ! $this->hasAttribute( $name ) )
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
    public function __get( string $name )
    {

        return $this->getAttribute( $name );

    }

    /**
     * Sets the value of a driver specific Attribute.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set( string $name, mixed $value )
    {

        if ( ! $this->supportedAttributes->has( $name ) )
        {
            // Unknown attribute
            return;
        }

        if ( ! $this->supportedAttributes->get( $name )->validateValue( $value ) )
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
    public function __isset( string $name )
    {

        return $this->hasAttribute( $name );

    }

    #endregion


}

