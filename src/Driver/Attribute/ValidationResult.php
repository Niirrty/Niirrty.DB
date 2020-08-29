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
 * A result of a DSN argument validation
 */
class ValidationResult
{


    // <editor-fold desc="// – – –   P U B L I C   F I E L D S   – – – – – – – – – – – – – – – – – – – – – – – – –">


    /**
     * Is the validated argument a known argument?
     *
     * @type bool
     */
    public $isKnownArgument;

    /**
     * Is the validated argument value a valid value?
     *
     * @type bool
     */
    public $isValidValue;

    /**
     * Error message, depending to $isKnownArgument and/or $isValidValue
     *
     * @type string|null
     */
    public $errorMessage;

    /**
     * The final value if no error message is defined.
     *
     * @type string|null
     */
    public $value;

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –">

    /**
     * DSNArgValidationResult constructor.
     */
    public function __construct()
    {

        $this->isKnownArgument = false;
        $this->isValidValue = false;
        $this->errorMessage = null;
        $this->value = null;

    }

    // </editor-fold>


    // <editor-fold desc="// – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –">

    /**
     * Gets if an error message is defined. If it returns true, no usable argument value is defined.
     *
     * @return bool
     */
    public function hasError(): bool
    {

        return null !== $this->errorMessage;

    }

    /**
     * Sets if the validated argument is a known argument?
     *
     * @param bool $isKnown
     *
     * @return ValidationResult
     */
    public function setIsKnown( bool $isKnown ): ValidationResult
    {

        $this->isKnownArgument = $isKnown;

        return $this;

    }

    /**
     * Sets if the validated argument is a valid argument value.
     *
     * @param bool $isValidValue
     *
     * @return ValidationResult
     */
    public function setIsValidValue( bool $isValidValue ): ValidationResult
    {

        $this->isValidValue = $isValidValue;

        return $this;

    }

    /**
     * Sets a error message of NULL if no error message should be defined.
     *
     * @param null|string $message
     *
     * @return ValidationResult
     */
    public function setErrorMessage( ?string $message ): ValidationResult
    {

        $this->errorMessage = $message;

        return $this;

    }

    /**
     * Sets the value.
     *
     * @param null|string $value
     *
     * @return ValidationResult
     */
    public function setValue( ?string $value ): ValidationResult
    {

        $this->value = $value;

        return $this;

    }


    // </editor-fold>


}

