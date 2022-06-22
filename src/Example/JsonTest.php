<?php
/**
 * @Author:      Jan
 * @Description:
 * @Date:        Created in 2022/6/13 11:16 上午
 * @Modifid By:
 */

namespace Example;

use DataProvider\BaseTestAbstract;

class JsonTest extends BaseTestAbstract
{
    // 去掉命名空间影响，子类必须继续
    protected $testNamespace = __NAMESPACE__;

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