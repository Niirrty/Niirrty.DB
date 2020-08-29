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
 * Each DB driver must implement the methods, defined here :-)
 *
 * @since v0.1.0
 */
interface IDriver
{


    /**
     * Gets the driver type. (e.g.: 'mysql', 'pgsql', etc)
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Gets the driver specific attribute support.
     *
     * @return Support
     */
    public function getAttributeSupport(): Support;

    /**
     * Sets the value of a driver specific Attribute.
     *
     * @param string $name  The name of the attribute
     * @param mixed  $value The attribute value
     *
     * @return IDriver
     * @throws ArgumentException If a unknown attribute should be defined
     */
    public function setAttribute( string $name, $value );

    /**
     * Gets the value of a driver specific Attribute.
     *
     * @param string $name
     * @param mixed  $defaultValue Is returned if the attribute is not defined
     *
     * @return mixed
     */
    public function getAttribute( string $name, $defaultValue = false );

    /**
     * Gets if a attribute exists.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute( string $name ): bool;

    /**
     * Gets the names of all currently defined attributes.
     *
     * @return array
     */
    public function getNamesOfDefinedAttributes(): array;

    /**
     * Gets all currently defined attributes.
     *
     * @return array
     */
    public function getDefinedAttributes(): array;

    /**
     * Gets the attribute with defined name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get( $name );

    /**
     * Sets the value of a driver specific Attribute.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function __set( $name, $value );

    /**
     * Checks if a value of the attribute with defined name is defined.
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset( $name );

    /**
     * Gets all connection info as string
     *
     * @return string
     */
    public function getInfoString(): string;

    /**
     * Gets if the table with defined name exists in selected database of current connection.
     *
     * @param \PDO   $pdo
     * @param string $tableName
     *
     * @return bool
     */
    public function tableExists( \PDO $pdo, string $tableName ): bool;


}

