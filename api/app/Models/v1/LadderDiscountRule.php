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
namespace App\Models\v1;

use App\Traits\CommonTrait;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 阶梯满减规则模型
 *
 * @property int $id
 * @property string $name 规则名称
 * @property string $description 规则描述
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property int $status 状态：0禁用 1启用
 * @property int $scope_type 适用范围类型：0全场 1指定商品/品牌/分类 2排除商品/品牌/分类
 * @property \\App\\Models\\v1\\LadderDiscountLevel[] $levels 阶梯档位
 * @property \\App\\Models\\v1\\LadderDiscountProduct[] $products 关联商品
 * @property \\App\\Models\\v1\\LadderDiscountBrand[] $brands 关联品牌
 * @property \\App\\Models\\v1\\LadderDiscountCategory[] $categories 关联分类
 */
class LadderDiscountRule extends Model
{
    use SoftDeletes;
    use CommonTrait;

    const STATUS_DISABLE = 0; // 状态：禁用
    const STATUS_ENABLE = 1; // 状态：启用
    
    const SCOPE_TYPE_ALL = 0; // 适用范围类型：全场
    const SCOPE_TYPE_INCLUDE = 1; // 适用范围类型：指定商品/品牌/分类
    const SCOPE_TYPE_EXCLUDE = 2; // 适用范围类型：排除商品/品牌/分类

    protected $fillable = ['name', 'description', 'start_time', 'end_time', 'status', 'scope_type'];
    protected $appends = ['status_show', 'scope_type_show'];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * 关联阶梯档位
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function levels()
    {
        return $this->hasMany('App\Models\v1\LadderDiscountLevel', 'rule_id', 'id')->orderBy('sort', 'asc');
    }

    /**
     * 关联商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany('App\Models\v1\LadderDiscountProduct', 'rule_id', 'id');
    }

    /**
     * 关联品牌
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function brands()
    {
        return $this->hasMany('App\Models\v1\LadderDiscountBrand', 'rule_id', 'id');
    }

    /**
     * 关联分类
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function categories()
    {
        return $this->hasMany('App\Models\v1\LadderDiscountCategory', 'rule_id', 'id');
    }

    /**
     * 状态显示
     *
     * @return string
     */
    public function getStatusShowAttribute()
    {
        $status = [
            self::STATUS_DISABLE => '禁用',
            self::STATUS_ENABLE => '启用'
        ];
        return isset($status[$this->status]) ? $status[$this->status] : '未知';
    }

    /**
     * 适用范围类型显示
     *
     * @return string
     */
    public function getScopeTypeShowAttribute()
    {
        $scopeType = [
            self::SCOPE_TYPE_ALL => '全场',
            self::SCOPE_TYPE_INCLUDE => '指定商品/品牌/分类',
            self::SCOPE_TYPE_EXCLUDE => '排除商品/品牌/分类'
        ];
        return isset($scopeType[$this->scope_type]) ? $scopeType[$this->scope_type] : '未知';
    }
}