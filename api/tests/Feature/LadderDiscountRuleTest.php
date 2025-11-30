<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\v1\LadderDiscountRule;
use App\Models\v1\LadderDiscountLevel;
use App\Models\v1\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class LadderDiscountRuleTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $token;

    /**
     * 测试前的准备工作
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // 创建管理员用户并获取访问令牌
        $this->admin = Admin::create([
            'username' => 'testadmin',
            'password' => bcrypt('password'),
            'status' => 1
        ]);
        $this->token = $this->admin->createToken('Test Token')->accessToken;
    }

    /**
     * 测试获取阶梯满减规则列表
     *
     * @return void
     */
    public function testListLadderDiscountRules()
    {
        // 创建测试数据
        for ($i = 0; $i < 3; $i++) {
            LadderDiscountRule::create([
                'name' => '测试规则' . $i,
                'start_time' => now(),
                'end_time' => now()->addDays(7),
                'status' => 1,
                'scope_type' => 1
            ]);
        }

        // 发送请求
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/admin/ladderDiscount/list');

        // 验证响应
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'message',
            'data' => [
                'list',
                'total',
                'page',
                'pageSize'
            ]
        ]);
    }

    /**
     * 测试获取阶梯满减规则详情
     *
     * @return void
     */
    public function testDetailLadderDiscountRule()
    {
        // 创建测试数据
        $rule = LadderDiscountRule::create([
            'name' => '测试详情规则',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'status' => 1,
            'scope_type' => 1
        ]);
        LadderDiscountLevel::create(['rule_id' => $rule->id, 'threshold' => 199, 'discount' => 20, 'sort' => 1]);
        LadderDiscountLevel::create(['rule_id' => $rule->id, 'threshold' => 399, 'discount' => 50, 'sort' => 2]);

        // 发送请求
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get('/api/admin/ladderDiscount/detail/' . $rule->id);

        // 验证响应
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'start_time',
                'end_time',
                'status',
                'scope_type',
                'levels'
            ]
        ]);
    }

    /**
     * 测试创建阶梯满减规则
     *
     * @return void
     */
    public function testCreateLadderDiscountRule()
    {
        // 准备请求数据
        $data = [
            'name' => '新的阶梯满减活动',
            'description' => '测试创建阶梯满减规则',
            'start_time' => now()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'status' => 1,
            'scope_type' => 1,
            'levels' => [
                ['threshold' => 199, 'discount' => 20, 'sort' => 1],
                ['threshold' => 399, 'discount' => 50, 'sort' => 2]
            ]
        ];

        // 发送请求
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post('/api/admin/ladderDiscount/create', $data);

        // 验证响应
        $response->assertStatus(200);
        $response->assertJson(['code' => 0]);
        
        // 验证数据是否正确保存
        $this->assertDatabaseHas('ladder_discount_rules', ['name' => '新的阶梯满减活动']);
        $this->assertDatabaseCount('ladder_discount_levels', 2);
    }

    /**
     * 测试更新阶梯满减规则
     *
     * @return void
     */
    public function testUpdateLadderDiscountRule()
    {
        // 创建测试数据
        $rule = LadderDiscountRule::create([
            'name' => '测试更新规则',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'status' => 1,
            'scope_type' => 1
        ]);
        LadderDiscountLevel::create(['rule_id' => $rule->id, 'threshold' => 199, 'discount' => 20, 'sort' => 1]);

        // 准备请求数据
        $data = [
            'name' => '更新后的满减活动',
            'description' => '更新规则描述',
            'start_time' => now()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'status' => 0,
            'scope_type' => 2,
            'levels' => [
                ['threshold' => 299, 'discount' => 30, 'sort' => 1],
                ['threshold' => 499, 'discount' => 60, 'sort' => 2],
                ['threshold' => 999, 'discount' => 120, 'sort' => 3]
            ]
        ];

        // 发送请求
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->put('/api/admin/ladderDiscount/edit/' . $rule->id, $data);

        // 验证响应
        $response->assertStatus(200);
        $response->assertJson(['code' => 0]);
        
        // 验证数据是否正确更新
        $this->assertDatabaseHas('ladder_discount_rules', [
            'id' => $rule->id,
            'name' => '更新后的满减活动',
            'status' => 0
        ]);
        $this->assertDatabaseCount('ladder_discount_levels', 3);
    }

    /**
     * 测试删除阶梯满减规则
     *
     * @return void
     */
    public function testDeleteLadderDiscountRule()
    {
        // 创建测试数据
        $rule = LadderDiscountRule::create([
            'name' => '测试删除规则',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'status' => 1,
            'scope_type' => 1
        ]);
        LadderDiscountLevel::create(['rule_id' => $rule->id, 'threshold' => 199, 'discount' => 20, 'sort' => 1]);

        // 发送请求
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->delete('/api/admin/ladderDiscount/destroy/' . $rule->id);

        // 验证响应
        $response->assertStatus(200);
        $response->assertJson(['code' => 0]);
        
        // 验证数据是否正确删除
        $this->assertDatabaseMissing('ladder_discount_rules', ['id' => $rule->id]);
        $this->assertDatabaseMissing('ladder_discount_levels', ['rule_id' => $rule->id]);
    }
}
