<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FullReductionRelation extends Model
{
    use SoftDeletes;

    protected $table = 'full_reduction_relations';

    protected $fillable = [
        'rule_id', 'relation_type', 'relation_id'
    ];

    protected $dates = ['deleted_at'];

    /**
     * 与阶梯满减规则的关联
     */
    public function rule()
    {
        return $this->belongsTo(FullReductionRule::class, 'rule_id');
    }

    /**
     * 与商品的关联
     */
    public function good()
    {
        return $this->belongsTo(Good::class, 'relation_id')->where('relation_type', 'good');
    }

    /**
     * 与品牌的关联
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'relation_id')->where('relation_type', 'brand');
    }

    /**
     * 与品类的关联
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'relation_id')->where('relation_type', 'category');
    }
}
