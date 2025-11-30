<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\v1\FullReduction;
use App\Models\v1\FullReductionTier;
use Illuminate\Support\Facades\DB;

class FullReductionTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * 测试阶梯满减列表接口
     *
     * @return void
     */
    public function testFullReductionList()
    {
        // 创建测试数据
        $fullReduction = factory(FullReduction::class)->create();
        $tiers = [];
        for ($i = 0; $i < 3; $i++) {
            $tiers[] = factory(FullReductionTier::class)->make()->toArray();
        }
        $fullReduction->tiers()->createMany($tiers);

        // 发送请求
        $response = $this->get('/api/v1/admin/fullReduction');

        // 打印响应内容
        dd($response->getContent());

        // 验证响应
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'start_time',
                        'end_time',
                        'participation_type',
                        'include_goods_ids',
                        'exclude_goods_ids',
                        'include_brands_ids',
                        'exclude_brands_ids',
                        'include_categories_ids',
                        'exclude_categories_ids',
                        'status',
                        'sort',
                        'created_at',
                        'updated_at',
                        'tiers' => [
                            '*' => [
                                'id',
                                'full_reduction_id',
                                'full_amount',
                                'reduce_amount',
                                'sort',
                                'created_at',
                                'updated_at',
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /**
     * 测试创建阶梯满减接口
     *
     * @return void
     */
    public function testCreateFullReduction()
    {
        // 准备测试数据
        $data = [
            'name' => $this->faker->name,
            'start_time' => now()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(7)->format('Y-m-d H:i:s'),
            'participation_type' => 1,
            'include_goods_ids' => [],
            'exclude_goods_ids' => [],
            'include_brands_ids' => [],
            'exclude_brands_ids' => [],
            'include_categories_ids' => [],
            'exclude_categories_ids' => [],
            'status' => 1,
            'sort' => 100,
            'tiers' => [
                [
                    'full_amount' => 10000,
                    'reduce_amount' => 1000,
                    'sort' => 100
                ],
                [
                    'full_amount' => 20000,
                    'reduce_amount' => 2500,
                    'sort' => 90
                ]
            ]
        ];

        // 发送请求
        $response = $this->post('/api/v1/admin/fullReduction', $data);

        // 验证响应
        $response->assertStatus(200);
        $response->assertJson([
            'code' => 200,
            'message' => '创建成功'
        ]);

        // 验证数据是否保存到数据库
        $this->assertDatabaseHas('full_reductions', [
            'name' => $data['name'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'participation_type' => $data['participation_type'],
            'include_goods_ids' => json_encode($data['include_goods_ids']),
            'exclude_goods_ids' => json_encode($data['exclude_goods_ids']),
            'include_brands_ids' => json_encode($data['include_brands_ids']),
            'exclude_brands_ids' => json_encode($data['exclude_brands_ids']),
            'include_categories_ids' => json_encode($data['include_categories_ids']),
            'exclude_categories_ids' => json_encode($data['exclude_categories_ids']),
            'status' => $data['status'],
            'sort' => $data['sort'],
        ]);

        $fullReduction = FullReduction::where('name', $data['name'])->first();
        $this->assertCount(2, $fullReduction->tiers);
    }

    /**
     * 测试更新阶梯满减接口
     *
     * @return void
     */
    public function testUpdateFullReduction()
    {
        // 创建测试数据
        $fullReduction = factory(FullReduction::class)->create();
        $tiers = [];
        for ($i = 0; $i < 2; $i++) {
            $tiers[] = factory(FullReductionTier::class)->make()->toArray();
        }
        $fullReduction->tiers()->createMany($tiers);

        // 准备更新数据
        $updateData = [
            'name' => '更新后的满减活动',
            'start_time' => now()->format('Y-m-d H:i:s'),
            'end_time' => now()->addDays(14)->format('Y-m-d H:i:s'),
            'participation_type' => 2,
            'include_goods_ids' => [],
            'exclude_goods_ids' => [],
            'include_brands_ids' => [],
            'exclude_brands_ids' => [],
            'include_categories_ids' => [],
            'exclude_categories_ids' => [],
            'status' => 0,
            'sort' => 90,
            'tiers' => [
                [
                    'id' => $fullReduction->tiers[0]->id,
                    'full_amount' => 15000,
                    'reduce_amount' => 1800,
                    'sort' => 100
                ],
                [
                    'full_amount' => 25000,
                    'reduce_amount' => 3000,
                    'sort' => 90
                ]
            ]
        ];

        // 发送请求
        $response = $this->post('/api/v1/admin/fullReduction/' . $fullReduction->id, $updateData);

        // 验证响应
        $response->assertStatus(200);
        $response->assertJson([
            'code' => 200,
            'message' => '更新成功'
        ]);

        // 验证数据是否更新到数据库
        $this->assertDatabaseHas('full_reductions', [
            'id' => $fullReduction->id,
            'name' => $updateData['name'],
            'start_time' => $updateData['start_time'],
            'end_time' => $updateData['end_time'],
            'participation_type' => $updateData['participation_type'],
            'include_goods_ids' => json_encode($updateData['include_goods_ids']),
            'exclude_goods_ids' => json_encode($updateData['exclude_goods_ids']),
            'include_brands_ids' => json_encode($updateData['include_brands_ids']),
            'exclude_brands_ids' => json_encode($updateData['exclude_brands_ids']),
            'include_categories_ids' => json_encode($updateData['include_categories_ids']),
            'exclude_categories_ids' => json_encode($updateData['exclude_categories_ids']),
            'status' => $updateData['status'],
            'sort' => $updateData['sort'],
        ]);

        $updatedFullReduction = FullReduction::find($fullReduction->id);
        $this->assertCount(2, $updatedFullReduction->tiers);
        $this->assertEquals(15000, $updatedFullReduction->tiers[0]->full_amount);
    }

    /**
     * 测试查看阶梯满减详情接口
     *
     * @return void
     */
    public function testShowFullReduction()
    {
        // 创建测试数据
        $fullReduction = factory(FullReduction::class)->create();
        $tiers = [];
        for ($i = 0; $i < 3; $i++) {
            $tiers[] = factory(FullReductionTier::class)->make()->toArray();
        }
        $fullReduction->tiers()->createMany($tiers);

        // 发送请求
        $response = $this->get('/api/v1/admin/fullReduction/' . $fullReduction->id);

        // 验证响应
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'code',
            'message',
            'data' => [
                'id',
                'name',
                'start_time',
                'end_time',
                'participation_type',
                'include_goods_ids',
                'exclude_goods_ids',
                'include_brands_ids',
                'exclude_brands_ids',
                'include_categories_ids',
                'exclude_categories_ids',
                'status',
                'sort',
                'created_at',
                'updated_at',
                'tiers' => [
                    '*' => [
                        'id',
                        'full_reduction_id',
                        'full_amount',
                        'reduce_amount',
                        'sort',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]
        ]);
        $response->assertJson([
            'data' => [
                'id' => $fullReduction->id,
                'name' => $fullReduction->name,
            ]
        ]);
    }

    /**
     * 测试删除阶梯满减接口
     *
     * @return void
     */
    public function testDeleteFullReduction()
    {
        // 创建测试数据
        $fullReduction = factory(FullReduction::class)->create();
        $tiers = [];
        for ($i = 0; $i < 2; $i++) {
            $tiers[] = factory(FullReductionTier::class)->make()->toArray();
        }
        $fullReduction->tiers()->createMany($tiers);

        // 发送请求
        $response = $this->post('/api/v1/admin/fullReduction/destroy/' . $fullReduction->id);

        // 验证响应
        $response->assertStatus(200);
        $response->assertJson([
            'code' => 200,
            'message' => '删除成功'
        ]);

        // 验证数据是否被软删除
        $this->assertSoftDeleted('full_reductions', ['id' => $fullReduction->id]);
        $this->assertSoftDeleted('full_reduction_tiers', ['full_reduction_id' => $fullReduction->id]);
    }
}
