<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\v1\LadderDiscountRule;

class LadderDiscountRuleSimplifiedTest extends TestCase
{
    /**
     * 测试1：仅测试模型类是否能正确加载
     */
    public function testModelExists()
    {
        $this->assertTrue(class_exists(LadderDiscountRule::class));
    }
    
    /**
     * 测试2：测试模型常量是否正确定义
     */
    public function testConstantsDefined()
    {
        $this->assertEquals(1, LadderDiscountRule::STATUS_ENABLE);
        $this->assertEquals(0, LadderDiscountRule::STATUS_DISABLE);
        $this->assertEquals(0, LadderDiscountRule::SCOPE_TYPE_ALL);
        $this->assertEquals(1, LadderDiscountRule::SCOPE_TYPE_INCLUDE);
        $this->assertEquals(2, LadderDiscountRule::SCOPE_TYPE_EXCLUDE);
    }
}
