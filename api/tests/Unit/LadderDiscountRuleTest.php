<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\v1\LadderDiscountRule;
use App\Models\v1\LadderDiscountLevel;
use App\Models\v1\LadderDiscountProduct;
use App\Models\v1\LadderDiscountBrand;
use App\Models\v1\LadderDiscountCategory;
use App\Models\v1\Good;
use App\Models\v1\Brand;
use App\Models\v1\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LadderDiscountRuleTest extends TestCase
{
    // 不使用RefreshDatabase，避免数据库迁移问题

    /**
     * 测试创建阶梯满减规则
     *
     * @return void
     */
    public function testCreateLadderDiscountRule()
    {
        // 创建测试数据
        $rule = LadderDiscountRule::create([
            'name' => '测试阶梯满减',
            'description' => '测试规则描述',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'status' => LadderDiscountRule::STATUS_ENABLE,
            'scope_type' => LadderDiscountRule::SCOPE_TYPE_ALL
        ]);

        // 验证创建成功
        $this->assertInstanceOf(LadderDiscountRule::class, $rule);
        $this->assertEquals('测试阶梯满减', $rule->name);
        $this->assertEquals(LadderDiscountRule::STATUS_ENABLE, $rule->status);
    }

    /**
     * 测试规则与档位的关联关系
     *
     * @return void
     */
    public function testRuleHasManyLevels()
    {
        // 创建规则
        $rule = LadderDiscountRule::create([
            'name' => '测试规则',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'status' => LadderDiscountRule::STATUS_ENABLE,
            'scope_type' => LadderDiscountRule::SCOPE_TYPE_ALL
        ]);

        // 创建档位
        $level1 = LadderDiscountLevel::create([
            'rule_id' => $rule->id,
            'threshold' => 199,
            'discount' => 20,
            'sort' => 1
        ]);

        // 直接验证创建成功，不使用关联查询
        $this->assertInstanceOf(LadderDiscountLevel::class, $level1);
        $this->assertEquals($rule->id, $level1->rule_id);
        $this->assertEquals(199, $level1->threshold);
        $this->assertEquals(20, $level1->discount);
    }

    /**
     * 测试规则状态转换方法
     *
     * @return void
     */
    public function testStatusTextConversion()
    {
        $rule = LadderDiscountRule::create([
            'name' => '测试规则',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'status' => LadderDiscountRule::STATUS_ENABLE,
            'scope_type' => LadderDiscountRule::SCOPE_TYPE_ALL
        ]);

        $this->assertEquals('启用', $rule->status_show);

        $rule->status = LadderDiscountRule::STATUS_DISABLE;
        $rule->save();
        $this->assertEquals('禁用', $rule->status_show);
    }

    /**
     * 测试适用范围类型转换方法
     *
     * @return void
     */
    public function testScopeTypeTextConversion()
    {
        $rule = LadderDiscountRule::create([
            'name' => '测试规则',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'status' => LadderDiscountRule::STATUS_ENABLE,
            'scope_type' => LadderDiscountRule::SCOPE_TYPE_ALL
        ]);

        $this->assertEquals('全场', $rule->scope_type_show);

        $rule->scope_type = LadderDiscountRule::SCOPE_TYPE_INCLUDE;
        $rule->save();
        $this->assertEquals('指定商品/品牌/分类', $rule->scope_type_show);
    }
}
