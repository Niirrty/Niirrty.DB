<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Niirrty
 * @package        Niirrty\DB\SQL
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\DB\SQL;


use \Niirrty\DB\DBException;
use \Niirrty\DB\DbType;


/**
 * A class, for easy define a SQL WHERE clause by code.
 *
 * Example-Code
 *
 * <code>
 * $where = WhereSQL::Create( DbType::MYSQL )
 *     ->group()
 *         ->cond()->col( 'u_password' )->eq()->val( ':pwd' )->end()
 *       ->op( 'AND' )
 *         ->cond()->col( 'u_mail' )->eq()->val( ':mail' )->end()
 *     ->end()
 *     ->op( 'OR' )
 *       ->cond()->col( 'u_guid' )->eq()->val( 'NULL' )->end()
 *     ->op( 'OR' )
 *       ->cond()->col( 'u_guid' )->eq()->val( 'XYZ', WhereCondition::VAL_TYPE_STRING )->end()
 *   ->end();
 *
 * echo $where;
 * </code>
 *
 * Outputs the following SQL:
 *
 * <code>
 *  WHERE ( `u_password` = :pwd AND `u_mail` = :mail ) OR `u_guid` = NULL OR `u_guid` = 'XYZ'
 * </code>
 *
 * @package Niirrty\DB\SQL\Where
 */
class WhereSQL
{


    #region // - - -   P R I V A T E   F I E L D S   - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @internal All single parts of the WHERE Statement
     * @var array
     */
    private array $_parts;

    #endregion


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * WhereSQL constructor.
     *
     * @param WhereSQL|null $parent Optional Parent WhereSQL instance. If defined, it means this is a group.
     * @param string|null   $dbType The DB Driver Type. Known types are defined by DbType::... Constants
     */
    public function __construct( private readonly ?WhereSQL $parent = null, private ?string $dbType = null )
    {

        $this->_parts = [];
        if ( null === $dbType )
        {
            $this->dbType = ( null === $parent )
                          ? DbType::MYSQL
                          : $parent->getDbType();
        }

    }

    #endregion


    #region // - - -   P U B L I C   M E T H O D S   - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Returns how many parts are currently registered inside the instance
     *
     * @return int
     */
    public function countParts(): int
    {

        return \count( $this->_parts );

    }

    /**
     * Init a new where condition and return it.
     *
     * @return WhereCondition
     */
    public function cond(): WhereCondition
    {

        $this->_parts[] = new WhereCondition( $this );

        return $this->_parts[ \count( $this->_parts ) - 1 ];

    }

    /**
     * Init a new condition group and return it
     *
     * @return WhereSQL
     */
    public function group(): WhereSQL
    {

        $this->_parts[] = new WhereSQL( $this, $this->dbType );

        return $this->_parts[ \count( $this->_parts ) - 1 ];

    }

    /**
     * Adds the 'AND' or 'OR' operator
     *
     * @param string $name 'AND' or 'OR'
     *
     * @return WhereSQL
     * @throws DBException
     */
    public function op( string $name = 'AND' ): WhereSQL
    {

        $name = \strtoupper( $name );

        if ( $name !== 'AND' and $name !== 'OR' )
        {
            throw new DBException( 'A condition can only be "AND" or "OR"!' );
        }

        $lastIndex = $this->countParts() - 1;

        if ( $lastIndex < 0 )
        {
            throw new DBException( 'A condition (' . $name . ') can not be placed at this point!' );
        }

        if ( \is_string( $this->_parts[ $lastIndex ] ) )
        {
            throw new DBException( 'A condition (' . $name . ') can not be placed in direct follow of an other condition!' );
        }

        $this->_parts[] = $name;

        return $this;

    }

    /**
     * Gets the type of the DB driver, defined by the DbType::... constants
     *
     * @return string
     */
    public function getDbType(): string
    {

        return $this->dbType;

    }

    /**
     * Add the AND operator.
     *
     * @return WhereSQL
     * @throws DBException If the operator should be placed at a wrong position
     */
    public function opAnd(): WhereSQL
    {

        return $this->op();

    }

    /**
     * Add the OR operator.
     *
     * @return WhereSQL
     * @throws DBException If the operator should be placed at a wrong position
     */
    public function opOR(): WhereSQL
    {

        return $this->op( 'OR' );

    }

    /**
     * Gets the parent WHERE SQL part, or NULL if no parent exists.
     *
     * @return WhereSQL|null
     */
    public function getParent(): ?WhereSQL
    {

        return $this->parent;

    }

    /**
     * Gets if a parent WhereSQL is defined. If so, this is handled as group
     *
     * @return bool
     */
    public function hasParent(): bool
    {

        return null !== $this->parent;

    }

    /**
     * Finishes the current IWhereSQL and returns the parent (or current, if no parent exists).
     *
     * @return WhereSQL
     */
    public function end(): WhereSQL
    {

        if ( null !== $this->parent )
        {
            return $this->parent;
        }

        return $this;

    }

    public function __toString(): string
    {

        if ( $this->countParts() < 1 )
        {
            return '';
        }

        $sql = $this->hasParent() ? ' (' : ' WHERE';

        foreach ( $this->_parts as $part )
        {
            $sql .= ' ' . \trim( (string) $part );
        }

        $sql .= ( $this->hasParent() ? ' )' : '' );

        return $sql;

    }

    #endregion


    #region // - - -   P U B L I C   S T A T I C   M E T H O D S   - - - - - - - - - - - - - - - - - -

    /**
     * @param string $dbType
     *
     * @return WhereSQL
     */
    public static function Create( string $dbType ): WhereSQL
    {

        return new WhereSQL( null, $dbType );

    }

    #endregion


}

