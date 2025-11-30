<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\v1\FullReductionRule;
use App\Models\v1\FullReductionTier;
use App\Models\v1\FullReductionRelation;
use App\Models\v1\Admin;
use Laravel\Passport\Passport;

class FullReductionTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // 创建管理员用户
        $this->admin = Admin::factory()->create();

        // 登录管理员
        Passport::actingAs($this->admin);
    }

    /**
     * 测试阶梯满减规则列表接口
     *
     * @return void
     */
    public function testFullReductionList()
    {
        // 创建两个阶梯满减规则
        FullReductionRule::factory()->count(2)->create();

        $response = $this->getJson('/api/admin/fullReduction');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'message',
                     'data' => [
                         'list' => [
                             '*' => [
                                 'id',
                                 'name',
                                 'type',
                                 'status',
                                 'start_time',
                                 'end_time',
                                 'description',
                                 'created_at',
                                 'updated_at'
                             ]
                         ],
                         'total'
                     ]
                 ]);

        // 验证返回了两个规则
        $this->assertCount(2, $response->json('data.list'));
    }

    /**
     * 测试阶梯满减规则详情接口
     *
     * @return void
     */
    public function testFullReductionDetail()
    {
        // 创建一个阶梯满减规则
        $rule = FullReductionRule::factory()->create();

        // 创建两个档位
        FullReductionTier::factory()->count(2)->create(['rule_id' => $rule->id]);

        // 创建两个关联
        FullReductionRelation::factory()->count(2)->create(['rule_id' => $rule->id]);

        $response = $this->getJson('/api/admin/fullReduction/' . $rule->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'type',
                         'status',
                         'start_time',
                         'end_time',
                         'description',
                         'tiers' => [
                             '*' => [
                                 'id',
                                 'rule_id',
                                 'min_amount',
                                 'discount_amount',
                                 'sort',
                                 'created_at',
                                 'updated_at'
                             ]
                         ],
                         'relations' => [
                             '*' => [
                                 'id',
                                 'rule_id',
                                 'relation_type',
                                 'relation_id',
                                 'created_at',
                                 'updated_at'
                             ]
                         ],
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        // 验证返回了正确的规则ID
        $this->assertEquals($rule->id, $response->json('data.id'));

        // 验证返回了两个档位
        $this->assertCount(2, $response->json('data.tiers'));

        // 验证返回了两个关联
        $this->assertCount(2, $response->json('data.relations'));
    }

    /**
     * 测试创建阶梯满减规则接口
     *
     * @return void
     */
    public function testCreateFullReduction()
    {
        $data = [
            'name' => '满减活动',
            'type' => 1,
            'status' => 1,
            'start_time' => '2025-01-01 00:00:00',
            'end_time' => '2025-01-31 23:59:59',
            'description' => '满减活动描述',
            'tiers' => [
                [
                    'min_amount' => 199.00,
                    'discount_amount' => 20.00,
                    'sort' => 1
                ],
                [
                    'min_amount' => 399.00,
                    'discount_amount' => 50.00,
                    'sort' => 2
                ]
            ],
            'relations' => [
                [
                    'relation_type' => 'good',
                    'relation_id' => 1
                ],
                [
                    'relation_type' => 'brand',
                    'relation_id' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/admin/fullReduction', $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'type',
                         'status',
                         'start_time',
                         'end_time',
                         'description',
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        // 验证规则已创建
        $this->assertDatabaseHas('full_reduction_rules', [
            'name' => '满减活动',
            'type' => 1,
            'status' => 1,
            'start_time' => '2025-01-01 00:00:00',
            'end_time' => '2025-01-31 23:59:59',
            'description' => '满减活动描述'
        ]);

        // 验证档位已创建
        $this->assertDatabaseHas('full_reduction_tiers', [
            'min_amount' => 199.00,
            'discount_amount' => 20.00,
            'sort' => 1
        ]);

        $this->assertDatabaseHas('full_reduction_tiers', [
            'min_amount' => 399.00,
            'discount_amount' => 50.00,
            'sort' => 2
        ]);

        // 验证关联已创建
        $this->assertDatabaseHas('full_reduction_relations', [
            'relation_type' => 'good',
            'relation_id' => 1
        ]);

        $this->assertDatabaseHas('full_reduction_relations', [
            'relation_type' => 'brand',
            'relation_id' => 1
        ]);
    }

    /**
     * 测试编辑阶梯满减规则接口
     *
     * @return void
     */
    public function testEditFullReduction()
    {
        // 创建一个阶梯满减规则
        $rule = FullReductionRule::factory()->create();

        // 创建两个档位
        FullReductionTier::factory()->count(2)->create(['rule_id' => $rule->id]);

        // 创建两个关联
        FullReductionRelation::factory()->count(2)->create(['rule_id' => $rule->id]);

        $data = [
            'name' => '修改后的满减活动',
            'type' => 2,
            'status' => 0,
            'start_time' => '2025-02-01 00:00:00',
            'end_time' => '2025-02-28 23:59:59',
            'description' => '修改后的满减活动描述',
            'tiers' => [
                [
                    'min_amount' => 299.00,
                    'discount_amount' => 30.00,
                    'sort' => 1
                ],
                [
                    'min_amount' => 499.00,
                    'discount_amount' => 60.00,
                    'sort' => 2
                ]
            ],
            'relations' => [
                [
                    'relation_type' => 'category',
                    'relation_id' => 1
                ]
            ]
        ];

        $response = $this->postJson('/api/admin/fullReduction/' . $rule->id, $data);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'message',
                     'data' => [
                         'id',
                         'name',
                         'type',
                         'status',
                         'start_time',
                         'end_time',
                         'description',
                         'created_at',
                         'updated_at'
                     ]
                 ]);

        // 验证规则已更新
        $this->assertDatabaseHas('full_reduction_rules', [
            'id' => $rule->id,
            'name' => '修改后的满减活动',
            'type' => 2,
            'status' => 0,
            'start_time' => '2025-02-01 00:00:00',
            'end_time' => '2025-02-28 23:59:59',
            'description' => '修改后的满减活动描述'
        ]);

        // 验证旧档位已删除，新档位已创建
        $this->assertDatabaseMissing('full_reduction_tiers', [
            'rule_id' => $rule->id,
            'min_amount' => 199.00
        ]);

        $this->assertDatabaseHas('full_reduction_tiers', [
            'rule_id' => $rule->id,
            'min_amount' => 299.00,
            'discount_amount' => 30.00,
            'sort' => 1
        ]);

        $this->assertDatabaseHas('full_reduction_tiers', [
            'rule_id' => $rule->id,
            'min_amount' => 499.00,
            'discount_amount' => 60.00,
            'sort' => 2
        ]);

        // 验证旧关联已删除，新关联已创建
        $this->assertDatabaseMissing('full_reduction_relations', [
            'rule_id' => $rule->id,
            'relation_type' => 'good'
        ]);

        $this->assertDatabaseHas('full_reduction_relations', [
            'rule_id' => $rule->id,
            'relation_type' => 'category',
            'relation_id' => 1
        ]);
    }

    /**
     * 测试删除阶梯满减规则接口
     *
     * @return void
     */
    public function testDestroyFullReduction()
    {
        // 创建一个阶梯满减规则
        $rule = FullReductionRule::factory()->create();

        // 创建两个档位
        FullReductionTier::factory()->count(2)->create(['rule_id' => $rule->id]);

        // 创建两个关联
        FullReductionRelation::factory()->count(2)->create(['rule_id' => $rule->id]);

        $response = $this->postJson('/api/admin/fullReduction/destroy/' . $rule->id);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'code',
                     'message',
                     'data'
                 ]);

        // 验证规则已删除（软删除）
        $this->assertDatabaseHas('full_reduction_rules', [
            'id' => $rule->id,
            'deleted_at' => $rule->fresh()->deleted_at
        ]);

        // 验证档位已删除（软删除）
        $tiers = FullReductionTier::where('rule_id', $rule->id)->get();
        foreach ($tiers as $tier) {
            $this->assertNotNull($tier->deleted_at);
        }

        // 验证关联已删除（软删除）
        $relations = FullReductionRelation::where('rule_id', $rule->id)->get();
        foreach ($relations as $relation) {
            $this->assertNotNull($relation->deleted_at);
        }
    }
}
