<?php
/** +----------------------------------------------------------------------
 * | TFSHOP [ 轻量级易扩展低代码开源商城系统 ]
 * +----------------------------------------------------------------------
 * | Copyright (c) 2020~2023 https://www.dswjcms.com All rights reserved.
 * +----------------------------------------------------------------------
 * | Licensed 未经许可不能去掉TFSHOP相关版权
 * +----------------------------------------------------------------------
 * | Author: Purl <383354826@qq.com>
 * +----------------------------------------------------------------------
 */

namespace App\Http\Controllers\v1\Admin;

use App\Code;
use App\Http\Controllers\Controller;
use App\Models\v1\FullReduction;
use App\Models\v1\FullReductionTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group [ADMIN]FullReduction(阶梯满减管理)
 * Class FullReductionController
 * @package App\Http\Controllers\v1\Admin
 */
class FullReductionController extends Controller
{
    /**
     * FullReductionList
     * 阶梯满减列表
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @queryParam  name string 活动名称
     * @queryParam  limit int 每页显示条数
     * @queryParam  sort string 排序
     * @queryParam  page string 页码
     */
    public function list(Request $request)
    {
        $q = FullReduction::query();
        $limit = $request->limit ?? 10;
        
        if ($request->name) {
            $q->where('name', 'like', '%' . $request->name . '%');
        }
        
        if ($request->has('sort')) {
            $sortFormatConversion = sortFormatConversion($request->sort);
            $q->orderBy($sortFormatConversion[0], $sortFormatConversion[1]);
        } else {
            $q->orderBy('sort', 'asc')->orderBy('id', 'desc');
        }
        
        $paginate = $q->with('tiers')->paginate($limit);
        
        return resReturn(1, $paginate);
    }

    /**
     * FullReductionCreate
     * 创建阶梯满减活动
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @queryParam  name string 活动名称
     * @queryParam  start_time string 开始时间
     * @queryParam  end_time string 结束时间
     * @queryParam  participation_type int 参与类型：0-全部商品参与，1-指定商品/品牌参与，2-排除商品/品类参与
     * @queryParam  include_goods array 包含的商品ID列表
     * @queryParam  include_brands array 包含的品牌ID列表
     * @queryParam  exclude_goods array 排除的商品ID列表
     * @queryParam  exclude_categories array 排除的品类ID列表
     * @queryParam  status int 状态：0-禁用，1-启用
     * @queryParam  sort int 排序
     * @queryParam  tiers array 满减档位列表
     */
    public function create(Request $request)
    {
        // 验证参数
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'participation_type' => 'required|in:0,1,2',
            'include_goods_ids' => 'nullable|array',
            'include_brands_ids' => 'nullable|array',
            'exclude_goods_ids' => 'nullable|array',
            'exclude_brands_ids' => 'nullable|array',
            'include_categories_ids' => 'nullable|array',
            'exclude_categories_ids' => 'nullable|array',
            'status' => 'required|in:0,1',
            'sort' => 'nullable|integer|min:0',
            'tiers' => 'required|array|min:1',
            'tiers.*.full_amount' => 'required|integer|min:1',
            'tiers.*.reduce_amount' => 'required|integer|min:1',
            'tiers.*.sort' => 'nullable|integer|min:0',
        ]);
        
        $return = DB::transaction(function () use ($request) {
            // 创建满减活动
            $fullReduction = new FullReduction();
            $fullReduction->name = $request->name;
            $fullReduction->start_time = $request->start_time;
            $fullReduction->end_time = $request->end_time;
            $fullReduction->participation_type = $request->participation_type;
            $fullReduction->include_goods_ids = $request->include_goods_ids ? json_encode($request->include_goods_ids) : null;
            $fullReduction->include_brands_ids = $request->include_brands_ids ? json_encode($request->include_brands_ids) : null;
            $fullReduction->exclude_goods_ids = $request->exclude_goods_ids ? json_encode($request->exclude_goods_ids) : null;
            $fullReduction->exclude_brands_ids = $request->exclude_brands_ids ? json_encode($request->exclude_brands_ids) : null;
            $fullReduction->include_categories_ids = $request->include_categories_ids ? json_encode($request->include_categories_ids) : null;
            $fullReduction->exclude_categories_ids = $request->exclude_categories_ids ? json_encode($request->exclude_categories_ids) : null;
            $fullReduction->status = $request->status;
            $fullReduction->sort = $request->sort ?? 0;
            $fullReduction->save();
            
            // 创建满减档位
            foreach ($request->tiers as $tierData) {
                $tier = new FullReductionTier();
                $tier->full_reduction_id = $fullReduction->id;
                $tier->full_amount = $tierData['full_amount'];
                $tier->reduce_amount = $tierData['reduce_amount'];
                $tier->sort = $tierData['sort'] ?? 0;
                $tier->save();
            }
            
            return resReturn(1, $fullReduction->load('tiers'));
        });
        
        return $return;
    }

    /**
     * FullReductionUpdate
     * 更新阶梯满减活动
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     * @queryParam  id int 活动ID
     * @queryParam  name string 活动名称
     * @queryParam  start_time string 开始时间
     * @queryParam  end_time string 结束时间
     * @queryParam  participation_type int 参与类型：0-全部商品参与，1-指定商品/品牌参与，2-排除商品/品类参与
     * @queryParam  include_goods array 包含的商品ID列表
     * @queryParam  include_brands array 包含的品牌ID列表
     * @queryParam  exclude_goods array 排除的商品ID列表
     * @queryParam  exclude_categories array 排除的品类ID列表
     * @queryParam  status int 状态：0-禁用，1-启用
     * @queryParam  sort int 排序
     * @queryParam  tiers array 满减档位列表
     */
    public function update(Request $request, $id)
    {
        // 验证参数
        $request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'participation_type' => 'required|in:0,1,2',
            'include_goods_ids' => 'nullable|array',
            'include_brands_ids' => 'nullable|array',
            'exclude_goods_ids' => 'nullable|array',
            'exclude_brands_ids' => 'nullable|array',
            'include_categories_ids' => 'nullable|array',
            'exclude_categories_ids' => 'nullable|array',
            'status' => 'required|in:0,1',
            'sort' => 'nullable|integer|min:0',
            'tiers' => 'required|array|min:1',
            'tiers.*.id' => 'nullable|integer',
            'tiers.*.full_amount' => 'required|integer|min:1',
            'tiers.*.reduce_amount' => 'required|integer|min:1',
            'tiers.*.sort' => 'nullable|integer|min:0',
        ]);
        
        // 查找满减活动
        $fullReduction = FullReduction::find($id);
        if (!$fullReduction) {
            return resReturn(0, '活动不存在', Code::CODE_PARAMETER_WRONG);
        }
        
        $return = DB::transaction(function () use ($request, $fullReduction) {
            // 更新满减活动
            $fullReduction->name = $request->name;
            $fullReduction->start_time = $request->start_time;
            $fullReduction->end_time = $request->end_time;
            $fullReduction->participation_type = $request->participation_type;
            $fullReduction->include_goods_ids = $request->include_goods_ids ? json_encode($request->include_goods_ids) : null;
            $fullReduction->include_brands_ids = $request->include_brands_ids ? json_encode($request->include_brands_ids) : null;
            $fullReduction->exclude_goods_ids = $request->exclude_goods_ids ? json_encode($request->exclude_goods_ids) : null;
            $fullReduction->exclude_brands_ids = $request->exclude_brands_ids ? json_encode($request->exclude_brands_ids) : null;
            $fullReduction->include_categories_ids = $request->include_categories_ids ? json_encode($request->include_categories_ids) : null;
            $fullReduction->exclude_categories_ids = $request->exclude_categories_ids ? json_encode($request->exclude_categories_ids) : null;
            $fullReduction->status = $request->status;
            $fullReduction->sort = $request->sort ?? 0;
            $fullReduction->save();
            
            // 更新满减档位
            $existingTierIds = $fullReduction->tiers->pluck('id')->toArray();
            $newTierIds = [];
            
            foreach ($request->tiers as $tierData) {
                if (isset($tierData['id']) && $tierData['id']) {
                    // 更新现有档位
                    $tier = FullReductionTier::find($tierData['id']);
                    if ($tier && $tier->full_reduction_id == $fullReduction->id) {
                        $tier->full_amount = $tierData['full_amount'];
                        $tier->reduce_amount = $tierData['reduce_amount'];
                        $tier->sort = $tierData['sort'] ?? 0;
                        $tier->save();
                        $newTierIds[] = $tier->id;
                    }
                } else {
                    // 创建新档位
                    $tier = new FullReductionTier();
                    $tier->full_reduction_id = $fullReduction->id;
                    $tier->full_amount = $tierData['full_amount'];
                    $tier->reduce_amount = $tierData['reduce_amount'];
                    $tier->sort = $tierData['sort'] ?? 0;
                    $tier->save();
                    $newTierIds[] = $tier->id;
                }
            }
            
            // 删除未包含在更新列表中的档位
            $tierIdsToDelete = array_diff($existingTierIds, $newTierIds);
            if (!empty($tierIdsToDelete)) {
                FullReductionTier::whereIn('id', $tierIdsToDelete)->delete();
            }
            
            return resReturn(1, $fullReduction->load('tiers'));
        });
        
        return $return;
    }

    /**
     * FullReductionShow
     * 获取阶梯满减活动详情
     * @param int $id
     * @return \Illuminate\Http\Response
     * @queryParam  id int 活动ID
     */
    public function show($id)
    {
        $fullReduction = FullReduction::with('tiers')->find($id);
        if (!$fullReduction) {
            return resReturn(0, '活动不存在', Code::CODE_PARAMETER_WRONG);
        }
        
        return resReturn(1, $fullReduction);
    }

    /**
     * FullReductionDelete
     * 删除阶梯满减活动
     * @param int $id
     * @return \Illuminate\Http\Response
     * @queryParam  id int 活动ID
     */
    public function delete($id)
    {
        $fullReduction = FullReduction::find($id);
        if (!$fullReduction) {
            return resReturn(0, '活动不存在', Code::CODE_PARAMETER_WRONG);
        }
        
        $fullReduction->delete();
        
        return resReturn(1, '删除成功');
    }
}
