<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Niirrty
 * @package        Niirrty\DB\Driver\Attribute
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types=1 );


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


    #region // – – –   P U B L I C   C O N S T R U C T O R   – – – – – – – – – – – – – – – – – – – –

    /**
     * ValueMissedLink constructor.
     *
     * @param IValueMissedSupport $support      The basic collection of supported attributes
     * @param string              $requiredName The name of the required attribute.
     * @param string              $altName      The name of the other attribute that should be defined if the required not exists.
     */
    public function __construct(
        private IValueMissedSupport $support, protected string $requiredName, protected string $altName ) { }

    #endregion


    #region // – – –   P U B L I C   M E T H O D S   – – – – – – – – – – – – – – – – – – – – – – – –

    public function isValid(): bool
    {

        if ( $this->support->hasAttribute( $this->requiredName ) )
        {
            return true;
        }

        return $this->support->hasAttribute( $this->altName );

    }

    #endregion


}

