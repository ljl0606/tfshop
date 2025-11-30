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

use App\Http\Controllers\Controller;
use App\Models\v1\LadderDiscountBrand;
use App\Models\v1\LadderDiscountCategory;
use App\Models\v1\LadderDiscountLevel;
use App\Models\v1\LadderDiscountProduct;
use App\Models\v1\LadderDiscountRule;
use App\Models\v1\Brand;
use App\Models\v1\Category;
use App\Models\v1\Good;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 阶梯满减规则控制器
 *
 * @package App\Http\Controllers\v1\Admin
 */
class LadderDiscountController extends Controller
{
    /**
     * 阶梯满减规则列表
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 10);
        $name = $request->input('name', '');
        $status = $request->input('status', '');

        $query = LadderDiscountRule::query();

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        $count = $query->count();
        $list = $query->orderBy('created_at', 'desc')
            ->with('levels')
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get();

        return $this->success([
            'list' => $list,
            'count' => $count
        ]);
    }

    /**
     * 阶梯满减规则详情
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail($id)
    {
        $rule = LadderDiscountRule::find($id);
        if (!$rule) {
            return $this->error(404, '规则不存在');
        }

        // 加载关联数据
        $rule->load('levels');
        $rule->load('products.product');
        $rule->load('brands.brand');
        $rule->load('categories.category');

        return $this->success($rule);
    }

    /**
     * 创建阶梯满减规则
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        // 验证请求参数
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'status' => 'required|in:0,1',
            'scope_type' => 'required|in:0,1,2',
            'levels' => 'required|array|min:1',
            'levels.*.min_amount' => 'required|integer|min:1',
            'levels.*.discount_amount' => 'required|integer|min:1',
            'product_ids' => 'nullable|array',
            'brand_ids' => 'nullable|array',
            'category_ids' => 'nullable|array'
        ]);

        DB::beginTransaction();
        try {
            // 创建规则主表
            $rule = new LadderDiscountRule();
            $rule->name = $request->input('name');
            $rule->description = $request->input('description', '');
            $rule->start_time = $request->input('start_time');
            $rule->end_time = $request->input('end_time');
            $rule->status = $request->input('status');
            $rule->scope_type = $request->input('scope_type');
            $rule->save();

            // 创建阶梯档位
            $levels = $request->input('levels');
            foreach ($levels as $index => $level) {
                $ladderLevel = new LadderDiscountLevel();
                $ladderLevel->rule_id = $rule->id;
                $ladderLevel->min_amount = $level['min_amount'];
                $ladderLevel->discount_amount = $level['discount_amount'];
                $ladderLevel->sort = $index;
                $ladderLevel->save();
            }

            // 保存关联商品
            $productIds = $request->input('product_ids', []);
            foreach ($productIds as $productId) {
                $product = new LadderDiscountProduct();
                $product->rule_id = $rule->id;
                $product->product_id = $productId;
                $product->save();
            }

            // 保存关联品牌
            $brandIds = $request->input('brand_ids', []);
            foreach ($brandIds as $brandId) {
                $brand = new LadderDiscountBrand();
                $brand->rule_id = $rule->id;
                $brand->brand_id = $brandId;
                $brand->save();
            }

            // 保存关联分类
            $categoryIds = $request->input('category_ids', []);
            foreach ($categoryIds as $categoryId) {
                $category = new LadderDiscountCategory();
                $category->rule_id = $rule->id;
                $category->category_id = $categoryId;
                $category->save();
            }

            DB::commit();
            return $this->success($rule);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(500, '创建失败：' . $e->getMessage());
        }
    }

    /**
     * 更新阶梯满减规则
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, $id)
    {
        // 验证请求参数
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after_or_equal:start_time',
            'status' => 'required|in:0,1',
            'scope_type' => 'required|in:0,1,2',
            'levels' => 'required|array|min:1',
            'levels.*.min_amount' => 'required|integer|min:1',
            'levels.*.discount_amount' => 'required|integer|min:1',
            'product_ids' => 'nullable|array',
            'brand_ids' => 'nullable|array',
            'category_ids' => 'nullable|array'
        ]);

        DB::beginTransaction();
        try {
            // 查找规则
            $rule = LadderDiscountRule::find($id);
            if (!$rule) {
                return $this->error(404, '规则不存在');
            }

            // 更新规则主表
            $rule->name = $request->input('name');
            $rule->description = $request->input('description', '');
            $rule->start_time = $request->input('start_time');
            $rule->end_time = $request->input('end_time');
            $rule->status = $request->input('status');
            $rule->scope_type = $request->input('scope_type');
            $rule->save();

            // 删除原有阶梯档位
            LadderDiscountLevel::where('rule_id', $id)->delete();

            // 创建新的阶梯档位
            $levels = $request->input('levels');
            foreach ($levels as $index => $level) {
                $ladderLevel = new LadderDiscountLevel();
                $ladderLevel->rule_id = $rule->id;
                $ladderLevel->min_amount = $level['min_amount'];
                $ladderLevel->discount_amount = $level['discount_amount'];
                $ladderLevel->sort = $index;
                $ladderLevel->save();
            }

            // 删除原有关联商品
            LadderDiscountProduct::where('rule_id', $id)->delete();

            // 保存新的关联商品
            $productIds = $request->input('product_ids', []);
            foreach ($productIds as $productId) {
                $product = new LadderDiscountProduct();
                $product->rule_id = $rule->id;
                $product->product_id = $productId;
                $product->save();
            }

            // 删除原有关联品牌
            LadderDiscountBrand::where('rule_id', $id)->delete();

            // 保存新的关联品牌
            $brandIds = $request->input('brand_ids', []);
            foreach ($brandIds as $brandId) {
                $brand = new LadderDiscountBrand();
                $brand->rule_id = $rule->id;
                $brand->brand_id = $brandId;
                $brand->save();
            }

            // 删除原有关联分类
            LadderDiscountCategory::where('rule_id', $id)->delete();

            // 保存新的关联分类
            $categoryIds = $request->input('category_ids', []);
            foreach ($categoryIds as $categoryId) {
                $category = new LadderDiscountCategory();
                $category->rule_id = $rule->id;
                $category->category_id = $categoryId;
                $category->save();
            }

            DB::commit();
            return $this->success($rule);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(500, '更新失败：' . $e->getMessage());
        }
    }

    /**
     * 删除阶梯满减规则
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            // 查找规则
            $rule = LadderDiscountRule::find($id);
            if (!$rule) {
                return $this->error(404, '规则不存在');
            }

            // 删除关联数据
            LadderDiscountLevel::where('rule_id', $id)->delete();
            LadderDiscountProduct::where('rule_id', $id)->delete();
            LadderDiscountBrand::where('rule_id', $id)->delete();
            LadderDiscountCategory::where('rule_id', $id)->delete();

            // 删除规则
            $rule->delete();

            DB::commit();
            return $this->success();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(500, '删除失败：' . $e->getMessage());
        }
    }

    /**
     * 获取可用商品列表（用于选择器）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableProducts(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 20);

        $query = Good::query()->where('is_delete', 0);

        if ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%')
                ->orWhere('number', 'like', '%' . $keyword . '%');
        }

        $count = $query->count();
        $list = $query->orderBy('id', 'desc')
            ->select('id', 'name', 'number', 'price')
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get();

        return $this->success([
            'list' => $list,
            'count' => $count
        ]);
    }

    /**
     * 获取可用品牌列表（用于选择器）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableBrands(Request $request)
    {
        $keyword = $request->input('keyword', '');
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 20);

        $query = Brand::query();

        if ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        $count = $query->count();
        $list = $query->orderBy('id', 'desc')
            ->select('id', 'name')
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get();

        return $this->success([
            'list' => $list,
            'count' => $count
        ]);
    }

    /**
     * 获取可用分类列表（用于选择器）
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableCategories(Request $request)
    {
        $keyword = $request->input('keyword', '');

        $query = Category::query();

        if ($keyword) {
            $query->where('name', 'like', '%' . $keyword . '%');
        }

        $list = $query->orderBy('sort', 'asc')
            ->select('id', 'name', 'pid')
            ->get();

        return $this->success([
            'list' => $list
        ]);
    }
}