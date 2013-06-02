<?php
/**
 * DateHelper Test file
 *
 * Test the DateHelper class
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package    ResqueBoard
 * @subpackage ResqueBoard.Test.Lib
 * @author     Wan Qi Chen <kami@kamisama.me>
 * @copyright  2012-2013 Wan Qi Chen
 * @link       http://resqueBoard.kamisama.me
 * @since      1.5.0
 * @license    MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

namespace ResqueBoard\Test\Lib;

use ResqueBoard\Lib\DateHelper;

/**
 * DateHelperTest Class
 *
 * Test the DateHelper class
 *
 * @author Wan Qi Chen <kami@kamisama.me>
 */
class DateHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ResqueBoard\Lib\DateHelper::explodeMilliseconds
     */
    public function testExplodeMillisecondsWithOnlyMilliseconds()
    {
        $ms = 1;

        $return = DateHelper::explodeMilliseconds($ms);

        $this->assertEquals(1, $return['ms']);
        $this->assertEquals(0, $return['sec']);
        $this->assertEquals(0, $return['min']);
        $this->assertEquals(0, $return['hour']);
        $this->assertEquals(0, $return['day']);
        $this->assertCount(5, $return);
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::explodeMilliseconds
     */
    public function testExplodeMillisecondsWithEmptyValue()
    {
        $ms = 0;

        $return = DateHelper::explodeMilliseconds($ms);

        $this->assertEquals(0, $return['ms']);
        $this->assertEquals(0, $return['sec']);
        $this->assertEquals(0, $return['min']);
        $this->assertEquals(0, $return['hour']);
        $this->assertEquals(0, $return['day']);
        $this->assertCount(5, $return);
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::explodeMilliseconds
     */
    public function testExplodeMillisecondsWithSeconds()
    {
        $ms = 1500; // 1.5 seconds

        $return = DateHelper::explodeMilliseconds($ms);

        $this->assertEquals(500, $return['ms']);
        $this->assertEquals(1, $return['sec']);
        $this->assertEquals(0, $return['min']);
        $this->assertEquals(0, $return['hour']);
        $this->assertEquals(0, $return['day']);
        $this->assertCount(5, $return);
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::explodeMilliseconds
     */
    public function testExplodeMillisecondsWithMinutes()
    {
        $ms = 425 + (60*3.5) * 1000;

        $return = DateHelper::explodeMilliseconds($ms);

        $this->assertEquals(425, $return['ms']);
        $this->assertEquals(30, $return['sec']);
        $this->assertEquals(3, $return['min']);
        $this->assertEquals(0, $return['hour']);
        $this->assertEquals(0, $return['day']);
        $this->assertCount(5, $return);
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::explodeMilliseconds
     */
    public function testExplodeMillisecondsWithHours()
    {
        $ms = 425 + (60*3.5 + 3600*6) * 1000;

        $return = DateHelper::explodeMilliseconds($ms);

        $this->assertEquals(425, $return['ms']);
        $this->assertEquals(30, $return['sec']);
        $this->assertEquals(3, $return['min']);
        $this->assertEquals(6, $return['hour']);
        $this->assertEquals(0, $return['day']);
        $this->assertCount(5, $return);
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::explodeMilliseconds
     */
    public function testExplodeMillisecondsWithDays()
    {
        $ms = 425 + (60*3.5 + 3600*6 + 3600*24*8) * 1000;

        $return = DateHelper::explodeMilliseconds($ms);

        $this->assertEquals(425, $return['ms']);
        $this->assertEquals(30, $return['sec']);
        $this->assertEquals(3, $return['min']);
        $this->assertEquals(6, $return['hour']);
        $this->assertEquals(8, $return['day']);
        $this->assertCount(5, $return);
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithDays()
    {
        $ms = 425 + (60*3.5 + 3600*6 + 3600*24*8) * 1000;
        $this->assertEquals('8 days 6h03', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithHours()
    {
        $ms = 425 + (60*3.5 + 3600*6) * 1000;
        $this->assertEquals('6:03 hours', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithMinutes()
    {
        $ms = 425 + (60*3.5) * 1000;
        $this->assertEquals('3:30 min', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithSeconds()
    {
        $ms = 3425;
        $this->assertEquals('3:425 sec', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithSecondsAndNoMilliseconds()
    {
        $ms = 3000;
        $this->assertEquals('3:000 sec', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithMinutesAndNoSeconds()
    {
        $ms = 425 + (60*3) * 1000;
        $this->assertEquals('3:00 min', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithHoursAndNoMinutes()
    {
        $ms = 425 + (3600*6) * 1000;
        $this->assertEquals('6:00 hours', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithDaysAndMinutesButNoHours()
    {
        $ms = 425 + (3600*24*8 + 60*3) * 1000;
        $this->assertEquals('8 days 3 min', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithDaysAndHoursButNotMinutes()
    {
        $ms = 425 + (3600*6 + 3600*24*8) * 1000;
        $this->assertEquals('8 days 6 hours', DateHelper::humanize($ms));
    }

    /**
     * @covers ResqueBoard\Lib\DateHelper::humanize
     */
    public function testHumanizeWithDaysButNoHoursNorMinutes()
    {
        $ms = 425 + (3600*24*8) * 1000;
        $this->assertEquals('8 days', DateHelper::humanize($ms));
    }

    public function testGetStartHour()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetEndHour()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetStartDay()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetEndDay()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetStartWeek()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetEndWeek()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetStartMonth()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetEndMonth()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testGetAgo()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
