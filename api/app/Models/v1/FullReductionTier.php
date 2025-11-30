<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FullReductionTier extends Model
{
    use SoftDeletes;

    protected $table = 'full_reduction_tiers';

    protected $fillable = [
        'rule_id', 'min_amount', 'discount_amount', 'sort'
    ];

    protected $dates = ['deleted_at'];

    /**
     * 与阶梯满减规则的关联
     */
    public function rule()
    {
        return $this->belongsTo(FullReductionRule::class, 'rule_id');
    }
}
