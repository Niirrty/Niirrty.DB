<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright      © 2017-2021, Niirrty
 * @package        Niirrty\DB
 * @since          2017-11-01
 * @version        0.4.0
 */


declare( strict_types=1 );


namespace Niirrty\DB;


/**
 * The supported main database (DBMS) types.
 *
 * @since v0.1.0
 */
interface DbType
{


    public final const MYSQL       = 'mysql';

    public final const PGSQL       = 'pgsql';

    public final const SQLITE      = 'sqlite';

    public final const KNOWN_TYPES = [ self::MYSQL, self::PGSQL, self::SQLITE ];


}

