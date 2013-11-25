<?php

namespace Phactory\Sql\DbUtil;

use Phactory\Sql\Phactory;

abstract class AbstractDbUtil
{
    protected $_pdo;
    /**
     * @var string quote character for table and column names. Override in
     *      DB-specific descendant classes
     */
    protected $_quoteChar = '`'; // MySQL and SqlLite uses that

    public function __construct(Phactory $phactory) {
        $this->_pdo = $phactory->getConnection();
    }

    abstract public function getPrimaryKey($table);
    abstract public function getColumns($table);

    // Not available in all RDBMS - default - do nothing
    public function disableForeignKeys() {}
    public function enableForeignKeys() {}

    /**
     * Quotes identifier with _quoteChar if it is not quoted (no _quoteChar's in $identifier).
     *
     * @param string $identifier name of table, column, etc
     * @return string quoted identifier
     */
    public function quoteIdentifier($identifier)
    {
        $quote = $this->getQuoteChar();
        if (false !== strpos($identifier, $this->getQuoteChar())) {
            $quote = '';
        }

        return $quote.$identifier.$quote;
    }

    public function getQuoteChar()
    {
        return $this->_quoteChar;
    }
}
