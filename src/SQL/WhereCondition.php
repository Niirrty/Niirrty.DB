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


class WhereCondition
{


    #region // = = =   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * The column name value is a regular column name
     */
    public const COL_TYPE_NAME = 'name';

    /**
     * The column name value is some SQL
     */
    public const COL_TYPE_SQL = 'sql';

    /**
     * The value is a string that must be escaped
     */
    public const VAL_TYPE_STRING = 'string';

    /**
     * The value is some SQL
     */
    public const VAL_TYPE_SQL = 'sql';

    private const OP_TYPE_EQ    = 'eq';

    private const OP_TYPE_NEQ   = 'neq';

    private const OP_TYPE_LT    = 'lt';

    private const OP_TYPE_GT    = 'gt';

    private const OP_TYPE_GL_EQ = 'lteq';

    private const OP_TYPE_GT_EQ = 'gteq';

    private const OP_TYPE_IS    = 'is';

    private const OP_TYPE_ISN   = 'isn';

    private const OP_TYPE_IN    = 'in';

    private const OP_TYPE_NIN   = 'nin';

    #endregion


    #region // - - -   P R I V A T E   F I E L D S   - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * @internal The column name part of the condition
     * @var string|null
     */
    private ?string $_col = null;

    /**
     * @internal The type of the column definition.
     * @var string|null
     */
    private ?string $_colType = null;

    /**
     * @internal The operator type, or '' if no Operator should be used
     * @var string
     */
    private string $_op = '';

    /**
     * @internal The condition value part
     * @var string|null
     */
    private ?string $_val = null;

    /**
     * @internal The condition value part type
     * @var string|null
     */
    private ?string $_valType = null;

    #endregion


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * WhereCondition constructor.
     *
     * @param WhereSQL $parent The parent WhereSQL instance
     */
    public function __construct( private readonly WhereSQL $parent ) { }

    #endregion


    #region // - - -   P U B L I C   M E T H O D S   - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Sets the column condition part.
     *
     * @param string $column     THe column definition
     * @param string $columnType The type of the column definition WhereCondition::COL_TYPE_NAME or
     *                           WhereCondition::COL_TYPE_SQL
     *
     * @return WhereCondition
     */
    public function col( string $column, string $columnType = self::COL_TYPE_NAME ): WhereCondition
    {

        $this->_col = $column;
        $this->_colType = $columnType;

        return $this;

    }

    #region // - - - - ALL OPERATORS   - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Use the equal (=) operator.
     *
     * @return WhereCondition
     */
    public function eq(): WhereCondition
    {

        $this->_op = self::OP_TYPE_EQ;

        return $this;

    }

    /**
     * Use the not equal (!=) operator.
     *
     * @return WhereCondition
     */
    public function neq(): WhereCondition
    {

        $this->_op = self::OP_TYPE_NEQ;

        return $this;

    }

    /**
     * Use the "greater than or equal" (>=) operator.
     *
     * @return WhereCondition
     */
    public function gteq(): WhereCondition
    {

        $this->_op = self::OP_TYPE_GT_EQ;

        return $this;

    }

    /**
     * Use the "lower than or equal" (<=) operator.
     *
     * @return WhereCondition
     */
    public function lteq(): WhereCondition
    {

        $this->_op = self::OP_TYPE_GL_EQ;

        return $this;

    }

    /**
     * Use the "lower than" (<) operator.
     *
     * @return WhereCondition
     */
    public function lt(): WhereCondition
    {

        $this->_op = self::OP_TYPE_LT;

        return $this;

    }

    /**
     * Use the "greater than" (>) operator.
     *
     * @return WhereCondition
     */
    public function gt(): WhereCondition
    {

        $this->_op = self::OP_TYPE_GT;

        return $this;

    }

    /**
     * Use the IS operator.
     *
     * @return WhereCondition
     */
    public function isValue(): WhereCondition
    {

        $this->_op = self::OP_TYPE_IS;

        return $this;

    }

    /**
     * Use the IS NOT operator.
     *
     * @return WhereCondition
     */
    public function isNot(): WhereCondition
    {

        $this->_op = self::OP_TYPE_ISN;

        return $this;

    }

    /**
     * Use the IN operator.
     *
     * @return WhereCondition
     */
    public function inValue(): WhereCondition
    {

        $this->_op = self::OP_TYPE_IN;

        return $this;

    }

    /**
     * Use the NOT IN operator.
     *
     * @return WhereCondition
     */
    public function notInValue(): WhereCondition
    {

        $this->_op = self::OP_TYPE_NIN;

        return $this;

    }

    #endregion

    /**
     * Sets the value condition part.
     *
     * @param string $value
     * @param string $valueType
     *
     * @return $this
     */
    public function val( string $value, string $valueType = self::VAL_TYPE_SQL ): WhereCondition
    {

        $this->_val = $value;
        $this->_valType = $valueType;

        return $this;

    }

    /**
     * Return if the condition defines all required elements.
     *
     * @return bool
     */
    public function isValid(): bool
    {

        return null !== $this->_col && null !== $this->_val &&
               null !== $this->_colType && null !== $this->_valType;

    }

    /**
     * Finish the condition definition and return the owning WhereSQL.
     *
     * @return WhereSQL
     * @throws DBException If the condition is not valid.
     */
    public function end(): WhereSQL
    {

        if ( ! $this->isValid() )
        {
            throw new DBException( 'Can not end this where condition because not all required parts are defined!' );
        }

        return $this->parent;
    }

    public function __toString()
    {

        if ( ! $this->isValid() )
        {
            return '';
        }

        $enc = $this->getFieldNameEnclosures();

        $sql = '';

        if ( self::COL_TYPE_SQL === $this->_colType )
        {
            $sql .= ' ' . $this->_col;
        }
        else
        {
            $sql .= ' ' . $enc[ 0 ] . $this->_col . $enc[ 1 ];
        }

        $sql .= match ( $this->_op )
        {
            self::OP_TYPE_EQ    => ' =',
            self::OP_TYPE_NEQ   => ' !=',
            self::OP_TYPE_GT    => ' >',
            self::OP_TYPE_LT    => ' <',
            self::OP_TYPE_IS    => ' IS',
            self::OP_TYPE_ISN   => ' IS NOT',
            self::OP_TYPE_GL_EQ => ' <=',
            self::OP_TYPE_GT_EQ => ' >=',
            self::OP_TYPE_IN    => ' IN',
            self::OP_TYPE_NIN   => ' NOT IN',
            default             => ' ',
        };

        $quote = $this->getStringEnclosures();

        if ( self::VAL_TYPE_SQL === $this->_valType )
        {
            $sql .= ' ' . $this->_val;
        }
        else
        {
            $sql .= ' ' . $quote . $this->_val . $quote;
        }

        return $sql;

    }

    #endregion


    #region // - - -   P R I V A T E   M E T H O D S   - - - - - - - - - - - - - - - - - - - - - - - -

    private function getFieldNameEnclosures(): array
    {

        return match ( $this->parent->getDbType() )
        {
            DbType::PGSQL, DbType::SQLITE => [ '"', '"' ],
            default                       => [ '`', '`' ],
        };

    }

    private function getStringEnclosures(): string
    {

        return match ( $this->parent->getDbType() )
        {
            DbType::PGSQL, DbType::SQLITE, DbType::MYSQL => "'",
            default                                      => '"',
        };

    }

    #endregion

}

