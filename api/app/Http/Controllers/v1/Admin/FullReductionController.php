<?php

namespace App\Http\Controllers\v1\Admin;

use App\Http\Controllers\Controller;
use App\Models\v1\FullReductionRule;
use App\Models\v1\FullReductionTier;
use App\Models\v1\FullReductionRelation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FullReductionController extends Controller
{
    /**
     * 阶梯满减规则列表
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);
        $name = $request->input('name', '');
        $status = $request->input('status', '');

        $query = FullReductionRule::with(['tiers' => function ($q) {
            $q->orderBy('min_amount', 'asc');
        }]);

        if (!empty($name)) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $rules = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $rules
        ]);
    }

    /**
     * 阶梯满减规则详情
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        $rule = FullReductionRule::with(['tiers' => function ($q) {
            $q->orderBy('min_amount', 'asc');
        }, 'relations'])->find($id);

        if (!$rule) {
            return response()->json([
                'code' => 1,
                'message' => '规则不存在',
                'data' => []
            ]);
        }

        return response()->json([
            'code' => 0,
            'message' => 'success',
            'data' => $rule
        ]);
    }

    /**
     * 创建阶梯满减规则
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'type' => ['required', Rule::in(['include', 'exclude'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:start_time',
            'description' => 'nullable|string',
            'tiers' => 'required|array|min:1',
            'tiers.*.min_amount' => 'required|numeric|min:0',
            'tiers.*.discount_amount' => 'required|numeric|min:0',
            'tiers.*.sort' => 'nullable|integer|min:0',
            'relations' => 'nullable|array',
            'relations.*.relation_type' => ['required', Rule::in(['good', 'brand', 'category'])],
            'relations.*.relation_id' => 'required|integer|min:1',
        ]);

        \\DB::beginTransaction();

        try {
            // 创建规则
            $rule = FullReductionRule::create([
                'name' => $validatedData['name'],
                'type' => $validatedData['type'],
                'status' => $validatedData['status'],
                'start_time' => $validatedData['start_time'] ?? null,
                'end_time' => $validatedData['end_time'] ?? null,
                'description' => $validatedData['description'] ?? '',
            ]);

            // 创建档位
            foreach ($validatedData['tiers'] as $tier) {
                FullReductionTier::create([
                    'rule_id' => $rule->id,
                    'min_amount' => $tier['min_amount'],
                    'discount_amount' => $tier['discount_amount'],
                    'sort' => $tier['sort'] ?? 0,
                ]);
            }

            // 创建关联关系
            if (isset($validatedData['relations']) && !empty($validatedData['relations'])) {
                foreach ($validatedData['relations'] as $relation) {
                    FullReductionRelation::create([
                        'rule_id' => $rule->id,
                        'relation_type' => $relation['relation_type'],
                        'relation_id' => $relation['relation_id'],
                    ]);
                }
            }

            \\DB::commit();

            return response()->json([
                'code' => 0,
                'message' => '创建成功',
                'data' => $rule
            ]);
        } catch (\Exception $e) {
            \\DB::rollBack();

            return response()->json([
                'code' => 1,
                'message' => '创建失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 编辑阶梯满减规则
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $rule = FullReductionRule::find($id);

        if (!$rule) {
            return response()->json([
                'code' => 1,
                'message' => '规则不存在',
                'data' => []
            ]);
        }

        $validatedData = $request->validate([
            'name' => 'required|string|max:100',
            'type' => ['required', Rule::in(['include', 'exclude'])],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'start_time' => 'nullable|date_format:Y-m-d H:i:s',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:start_time',
            'description' => 'nullable|string',
            'tiers' => 'required|array|min:1',
            'tiers.*.id' => 'nullable|integer|min:1',
            'tiers.*.min_amount' => 'required|numeric|min:0',
            'tiers.*.discount_amount' => 'required|numeric|min:0',
            'tiers.*.sort' => 'nullable|integer|min:0',
            'relations' => 'nullable|array',
            'relations.*.id' => 'nullable|integer|min:1',
            'relations.*.relation_type' => ['required', Rule::in(['good', 'brand', 'category'])],
            'relations.*.relation_id' => 'required|integer|min:1',
        ]);

        \\DB::beginTransaction();

        try {
            // 更新规则
            $rule->update([
                'name' => $validatedData['name'],
                'type' => $validatedData['type'],
                'status' => $validatedData['status'],
                'start_time' => $validatedData['start_time'] ?? null,
                'end_time' => $validatedData['end_time'] ?? null,
                'description' => $validatedData['description'] ?? '',
            ]);

            // 处理档位
            $existingTierIds = $rule->tiers->pluck('id')->toArray();
            $newTierIds = [];

            foreach ($validatedData['tiers'] as $tier) {
                if (isset($tier['id']) && in_array($tier['id'], $existingTierIds)) {
                    // 更新现有档位
                    FullReductionTier::find($tier['id'])->update([
                        'min_amount' => $tier['min_amount'],
                        'discount_amount' => $tier['discount_amount'],
                        'sort' => $tier['sort'] ?? 0,
                    ]);
                    $newTierIds[] = $tier['id'];
                } else {
                    // 创建新档位
                    $newTier = FullReductionTier::create([
                        'rule_id' => $rule->id,
                        'min_amount' => $tier['min_amount'],
                        'discount_amount' => $tier['discount_amount'],
                        'sort' => $tier['sort'] ?? 0,
                    ]);
                    $newTierIds[] = $newTier->id;
                }
            }

            // 删除未在更新列表中的档位
            $tiersToDelete = array_diff($existingTierIds, $newTierIds);
            if (!empty($tiersToDelete)) {
                FullReductionTier::whereIn('id', $tiersToDelete)->delete();
            }

            // 处理关联关系
            $existingRelationIds = $rule->relations->pluck('id')->toArray();
            $newRelationIds = [];

            if (isset($validatedData['relations']) && !empty($validatedData['relations'])) {
                foreach ($validatedData['relations'] as $relation) {
                    if (isset($relation['id']) && in_array($relation['id'], $existingRelationIds)) {
                        // 更新现有关联关系
                        FullReductionRelation::find($relation['id'])->update([
                            'relation_type' => $relation['relation_type'],
                            'relation_id' => $relation['relation_id'],
                        ]);
                        $newRelationIds[] = $relation['id'];
                    } else {
                        // 创建新关联关系
                        $newRelation = FullReductionRelation::create([
                            'rule_id' => $rule->id,
                            'relation_type' => $relation['relation_type'],
                            'relation_id' => $relation['relation_id'],
                        ]);
                        $newRelationIds[] = $newRelation->id;
                    }
                }
            }

            // 删除未在更新列表中的关联关系
            $relationsToDelete = array_diff($existingRelationIds, $newRelationIds);
            if (!empty($relationsToDelete)) {
                FullReductionRelation::whereIn('id', $relationsToDelete)->delete();
            }

            \\DB::commit();

            return response()->json([
                'code' => 0,
                'message' => '编辑成功',
                'data' => $rule
            ]);
        } catch (\Exception $e) {
            \\DB::rollBack();

            return response()->json([
                'code' => 1,
                'message' => '编辑失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }

    /**
     * 删除阶梯满减规则
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $rule = FullReductionRule::find($id);

        if (!$rule) {
            return response()->json([
                'code' => 1,
                'message' => '规则不存在',
                'data' => []
            ]);
        }

        \\DB::beginTransaction();

        try {
            // 删除关联的档位和关系
            $rule->tiers()->delete();
            $rule->relations()->delete();

            // 删除规则
            $rule->delete();

            \\DB::commit();

            return response()->json([
                'code' => 0,
                'message' => '删除成功',
                'data' => []
            ]);
        } catch (\Exception $e) {
            \\DB::rollBack();

            return response()->json([
                'code' => 1,
                'message' => '删除失败：' . $e->getMessage(),
                'data' => []
            ]);
        }
    }
}
