<?php


namespace Niirrty\DB\SQL;


use Niirrty\DB\DBException;
use Niirrty\DB\DbType;


class WhereCondition
{


    #region // PUBLIC CONSTANTS


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

    #endregion


    #region // PRIVATE CONSTANTS

    private const OP_TYPE_EQ    = 'eq';

    private const OP_TYPE_NEQ   = 'neq';

    private const OP_TYPE_LT    = 'lt';

    private const OP_TYPE_GT    = 'gt';

    private const OP_TYPE_GL_EQ = 'lteq';

    private const OP_TYPE_GT_EQ = 'gteq';

    private const OP_TYPE_IS    = 'is';

    private const OP_TYPE_ISN   = 'isn';

    private const OP_TYPE_IN    = 'in';

    #endregion


    #region // PRIVATE FIELDS

    /**
     * @internal The column name part of the condition
     * @var string
     */
    private $_col = null;

    /**
     * @internal The type of the column definition.
     * @var string
     */
    private $_colType = null;

    /**
     * @internal The operator type, or '' if no Operator should be used
     * @var string
     */
    private $_op = '';

    /**
     * @internal The condition value part
     * @var string
     */
    private $_val = null;

    /**
     * @internal The condition value part type
     * @var string
     */
    private $_valType = null;

    /**
     * @internal The parent WhereSQL instance
     * @var WhereSQL
     */
    private $_parent;

    #endregion


    #region // The constructor

    /**
     * WhereCondition constructor.
     *
     * @param WhereSQL $parent The parent WhereSQL instance
     */
    public function __construct( WhereSQL $parent )
    {

        $this->_parent = $parent;
    }

    #endregion


    #region // PUBLIC METHODS

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

    #region // ALL OPERATORS

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

        return null !== $this->_col && null !== $this->_op && null !== $this->_val &&
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

        if ( !$this->isValid() )
        {
            throw new DBException( 'Can not end this where condition because not all required parts are defined!' );
        }

        return $this->_parent;
    }

    public function __toString()
    {

        if ( !$this->isValid() )
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

        switch ( $this->_op )
        {
            case self::OP_TYPE_EQ:
                $sql .= ' =';
                break;
            case self::OP_TYPE_NEQ:
                $sql .= ' !=';
                break;
            case self::OP_TYPE_GT:
                $sql .= ' >';
                break;
            case self::OP_TYPE_LT:
                $sql .= ' <';
                break;
            case self::OP_TYPE_IS:
                $sql .= ' IS';
                break;
            case self::OP_TYPE_ISN:
                $sql .= ' IS NOT';
                break;
            case self::OP_TYPE_GL_EQ:
                $sql .= ' <=';
                break;
            case self::OP_TYPE_GT_EQ:
                $sql .= ' >=';
                break;
            case self::OP_TYPE_IN:
                $sql .= ' IN';
                break;
            default:
                $sql .= ' ';
                break;
        }

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


    #region // PRIVATE METHODS

    private function getFieldNameEnclosures(): array
    {

        switch ( $this->_parent->getDbType() )
        {
            case DbType::PGSQL:
            case DbType::SQLITE:
                return [ '"', '"' ];
            default:
                return [ '`', '`' ];
        }
    }

    private function getStringEnclosures(): string
    {

        switch ( $this->_parent->getDbType() )
        {
            case DbType::PGSQL:
            case DbType::SQLITE:
            case DbType::MYSQL:
                return "'";
            default:
                return '"';
        }
    }


    #endregion

}

