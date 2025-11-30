<?php

namespace App\Models\v1;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FullReductionRule extends Model
{
    use SoftDeletes;

    protected $table = 'full_reduction_rules';

    protected $fillable = [
        'name', 'type', 'status', 'start_time', 'end_time', 'description'
    ];

    protected $dates = ['deleted_at'];

    /**
     * 与阶梯满减档位的关联
     */
    public function tiers()
    {
        return $this->hasMany(FullReductionTier::class, 'rule_id');
    }

    /**
     * 与关联关系的关联
     */
    public function relations()
    {
        return $this->hasMany(FullReductionRelation::class, 'rule_id');
    }
}
