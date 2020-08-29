<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2020, Niirrty
 * @package        Niirrty\DB\Driver\Attribute
 * @since          2017-11-01
 * @version        0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\DB\Driver\Attribute;


/**
 * Defines a class that …
 *
 * @since v0.1.0
 */
class Support implements \Countable, \IteratorAggregate, IValueMissedSupport
{


    // <editor-fold desc="// – – –   P R I V A T E   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – –">


    /**
     * The supported attributes contained in the collection.
     *
     * @type Descriptor[]
     */
    private $_attributes;

    /**
     * The DSN separator char between attribute keys and values
     *
     * @type string
     */
    private $_dsnKeyValueSeparator;

    /**
     * The DSN separator char between attributes.
     *
     * @type string
     */
    private $_dsnAttributeSeparator;

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

    /**
     * Support constructor.
     *
     * @param Descriptor[] $attributes
     */
    public function __construct( array $attributes = [] )
    {

        $this->_attributes = $this->getAttributes( $attributes );
        $this->_dsnKeyValueSeparator = '=';
        $this->_dsnAttributeSeparator = ' ';

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P R O T E C T E D   M E T H O D S   – – – – – – – – – – – – – – – – – – – – –">

    /**
     * Results array of items.
     *
     * @param array $attributes
     *
     * @return Descriptor[]
     */
    protected function getAttributes( array $attributes ): array
    {

        $result = [];

        foreach ( $attributes as $attribute )
        {
            if ( !( $attribute instanceof Descriptor ) )
            {
                continue;
            }
            $result[ $attribute->getName() ] = $attribute;
        }

        return $result;

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">


    /**
     * Retrieve an external iterator
     *
     * @return \Traversable
     */
    public function getIterator()
    {

        return new \ArrayIterator( $this->_attributes );

    }

    /**
     * Gets whether a attribute with defined name is described.
     *
     * @param string $argName
     *
     * @return bool
     */
    public function has( string $argName ): bool
    {

        return \array_key_exists( $argName, $this->_attributes );

    }

    /**
     * Gets the DSN argument descriptor with defined name, or NULL if it not exists.
     *
     * @param string $argName
     *
     * @return Descriptor|null
     */
    public function get( string $argName ): ?Descriptor
    {

        return $this->_attributes[ $argName ] ?? null;

    }

    /**
     * Adds or overwrites a argument descriptor.
     *
     * @param Descriptor $argDescriptor
     *
     * @return Support
     */
    public function add( Descriptor $argDescriptor ): Support
    {

        $this->_attributes[ $argDescriptor->getName() ] = $argDescriptor;

        return $this;

    }

    /**
     * Removes the DSN argument descriptor with defined name
     *
     * @param string $argName
     *
     * @return Support
     */
    public function remove( string $argName ): Support
    {

        if ( !$this->has( $argName ) )
        {
            return $this;
        }

        unset( $this->_attributes[ $argName ] );

        return $this;

    }

    /**
     * Count all described attributes.
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int
     */
    public function count()
    {

        return \count( $this->_attributes );

    }

    /**
     * Gets all current declared descriptors as a associative array. Keys are the argument names, values the
     * Descriptor instances.
     *
     * @return Descriptor[]
     */
    public function all(): array
    {

        return $this->_attributes;

    }

    /**
     * Gets the argument names of all current declared argument descriptors as a numeric indicated array.
     *
     * @return string[]
     */
    public function names(): array
    {

        return \array_keys( $this->_attributes );

    }

    /**
     * Gets if an attribute with defined name is defined
     *
     * @param string $attributeName
     *
     * @return bool
     */
    public function hasAttribute( string $attributeName ): bool
    {

        return $this->has( $attributeName );

    }

    /**
     * Gets if the declared $attributes define all required attributes.
     *
     * @param array $attributes The attributes to check (associative array)
     *
     * @return bool
     */
    public function haveAllRequiredAttributes( array $attributes ): bool
    {

        foreach ( $this->_attributes as $attribute )
        {
            if ( !$attribute->isRequired() )
            {
                continue;
            }
            if ( !isset( $attributes[ $attribute->getName() ] ) )
            {
                if ( $attribute->hasValueMissedLink() && $attribute->getValueMissedLink()->isValid() )
                {
                    continue;
                }

                return false;
            }
        }

        return true;

    }

    /**
     * Gets the separator char between attribute keys and values
     *
     * @return string
     */
    public function getDSNKeyValueSeparator(): string
    {

        return $this->_dsnKeyValueSeparator;

    }

    /**
     * Gets the separator char between attributes
     *
     * @return string
     */
    public function getDSNAttributeSeparator(): string
    {

        return $this->_dsnAttributeSeparator;

    }

    /**
     * Sets the DSN separator char between attribute keys and values
     *
     * @param string $keyValueSeparator
     *
     * @return Support
     */
    public function setDSNKeyValueSeparator( string $keyValueSeparator ): Support
    {

        $this->_dsnKeyValueSeparator = $keyValueSeparator;

        return $this;

    }

    /**
     * Sets the separator char between attribute keys and values
     *
     * @param string $attributeSeparator
     *
     * @return Support
     */
    public function setAttributeSeparator( string $attributeSeparator ): Support
    {

        $this->_dsnAttributeSeparator = $attributeSeparator;

        return $this;

    }

    /**
     * Gets the name of the parameter that should be used as DBMS auth login user name parameter (No DSN!).
     *
     * @return string|null
     */
    public function getAuthUserParamName(): ?string
    {

        foreach ( $this->_attributes as $attribute )
        {
            if ( Type::USERNAME_PARAM === $attribute->getType() )
            {
                return $attribute->getName();
            }
        }

        return null;

    }

    /**
     * Gets the name of the parameter that should be used as DBMS auth login password parameter (No DSN!).
     *
     * @return string|null
     */
    public function getAuthPasswordParamName(): ?string
    {

        foreach ( $this->_attributes as $attribute )
        {
            if ( Type::PASSWORD_PARAM === $attribute->getType() )
            {
                return $attribute->getName();
            }
        }

        return null;

    }

    /**
     * Gets the names of the parameters that should be used as PDO options (No DSN!).
     *
     * @return array
     */
    public function getOptionParamNames(): array
    {

        $out = [];

        foreach ( $this->_attributes as $attributeName => $attribute )
        {
            if ( Type::OPTION_PARAM === $attribute->getType() )
            {
                $out[] = $attributeName;
            }
        }

        return $out;

    }

    /**
     * Gets the names of the parameters that should be used as SQL queries, called after connection open.
     *
     * @return array
     */
    public function getSQLParamNames(): array
    {

        $out = [];

        foreach ( $this->_attributes as $attributeName => $attribute )
        {
            if ( Type::INIT_SQL === $attribute->getType() )
            {
                $out[] = $attributeName;
            }
        }

        return $out;

    }

    /**
     * Validates a Driver argument with defined name and value. At least it checks if the Driver knows the
     * argument and if the value is valid.
     *
     * @param string $attributeName
     * @param        $attributeValue
     *
     * @return ValidationResult
     */
    public function validate( string $attributeName, $attributeValue ): ValidationResult
    {

        $result = new ValidationResult();

        if ( false === $this->has( $attributeName ) )
        {
            // Unknown argument name
            return $result
                ->setErrorMessage( 'Unknown Driver attribute name "' . $attributeName . '"!' );
        }

        if ( $this->_attributes[ $attributeName ]->validateValue( $attributeValue ) )
        {
            // The value is valid we are done here
            return $result
                ->setIsKnown( true )
                ->setIsValidValue( true )
                ->setValue( $attributeValue );
        }

        if ( $this->_attributes[ $attributeName ]->hasDefaultValue() )
        {
            // The defined value is not valid, but a default value is defined, that is used now
            return $result
                ->setIsKnown( true )
                ->setValue( $this->_attributes[ $attributeName ]->getDefaultValue() );
        }

        return $result
            ->setIsKnown( true )
            ->setErrorMessage( 'Unknown Driver argument "' . $attributeName . '" value "' . $attributeValue . '"!' );

    }


    // </editor-fold>


}

