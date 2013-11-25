<?php
namespace Phactory\Sql\DbUtil;

/**
 * @author Konstantin G Romanov
 */
class AbstractDbUtilTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractDbUtil */
    private $cut;

    protected function setUp()
    {
        $phactory = $this->getMock('Phactory\Sql\Phactory', array('getConnection'), array(), '', false);
        $phactory->expects($this->once())->method('getConnection')->will($this->returnValue(null));

        /** @var AbstractDbUtil $cut */
        $this->cut = $this->getMockForAbstractClass('Phactory\Sql\DbUtil\AbstractDbUtil', array($phactory));
    }

    public function testGetQuoteChar_default_returnsBacktick()
    {
        $this->assertEquals('`', $this->cut->getQuoteChar());
    }

    /**
     * @depends testGetQuoteChar_default_returnsBacktick
     */
    public function testQuoteIdentifier_notQuoteInIdentifier_backtickQuoted()
    {
        $identifier = "test_id";
        $this->assertEquals("`{$identifier}`", $this->cut->quoteIdentifier($identifier));
    }

    /**
     * @depends testGetQuoteChar_default_returnsBacktick
     */
    public function testQuoteIdentifier_backtickQuotedInIdentifier_backtickQuotedOnce()
    {
        $identifier = "`test_id`";
        $this->assertEquals($identifier, $this->cut->quoteIdentifier($identifier));
    }
}
