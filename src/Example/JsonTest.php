<?php
/**
 * @Author:      Jan
 * @Description:
 * @Date:        Created in 2022/6/13 11:16 上午
 * @Modifid By:
 */

namespace test\Example;

use test\DataProvider\BaseTestAbstract;

class JsonTest extends BaseTestAbstract
{

    /**
     * @dataProvider autoProvider
     * @param $expect
     */
    public function testDemo1($expect)
    {
        $this->assertEquals(["name1"=>"class-json1","name2"=>"class-json2"], $expect);
    }


    public function testMethod($expect)
    {
        $this->assertEquals('method-json', $expect);
    }
}