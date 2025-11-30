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

class FullReduction extends Model
{
    use SoftDeletes;
    use CommonTrait;

    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
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
    ];

    const PARTICIPATION_TYPE_ALL = 0; //参与类型：全部商品参与
    const PARTICIPATION_TYPE_INCLUDE = 1; //参与类型：指定商品/品牌参与
    const PARTICIPATION_TYPE_EXCLUDE = 2; //参与类型：排除商品/品类参与
    const STATUS_DISABLED = 0; //状态：禁用
    const STATUS_ENABLED = 1; //状态：启用

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
     * 获取满减档位
     */
    public function tiers()
    {
        return $this->hasMany('App\Models\v1\FullReductionTier', 'full_reduction_id', 'id');
    }
}
