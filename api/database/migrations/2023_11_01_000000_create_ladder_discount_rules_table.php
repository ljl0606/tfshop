<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLadderDiscountRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 阶梯满减规则主表
        Schema::create('ladder_discount_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('规则名称');
            $table->text('description')->nullable()->comment('规则描述');
            $table->dateTime('start_time')->comment('开始时间');
            $table->dateTime('end_time')->comment('结束时间');
            $table->tinyInteger('status')->default(0)->comment('状态：0禁用 1启用');
            $table->tinyInteger('scope_type')->default(0)->comment('适用范围类型：0全场 1指定商品/品牌/分类 2排除商品/品牌/分类');
            $table->timestamps();
            $table->softDeletes();
        });

        // 阶梯满减档位表
        Schema::create('ladder_discount_levels', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id')->comment('规则ID');
            $table->integer('min_amount')->comment('满减门槛金额');
            $table->integer('discount_amount')->comment('减额');
            $table->tinyInteger('sort')->default(0)->comment('排序');
            $table->timestamps();

            $table->index('rule_id');
        });

        // 规则关联商品表
        Schema::create('ladder_discount_products', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id')->comment('规则ID');
            $table->integer('product_id')->comment('商品ID');
            $table->timestamps();

            $table->index('rule_id');
            $table->index('product_id');
        });

        // 规则关联品牌表
        Schema::create('ladder_discount_brands', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id')->comment('规则ID');
            $table->integer('brand_id')->comment('品牌ID');
            $table->timestamps();

            $table->index('rule_id');
            $table->index('brand_id');
        });

        // 规则关联分类表
        Schema::create('ladder_discount_categories', function (Blueprint $table) {
            $table->id();
            $table->integer('rule_id')->comment('规则ID');
            $table->integer('category_id')->comment('分类ID');
            $table->timestamps();

            $table->index('rule_id');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ladder_discount_categories');
        Schema::dropIfExists('ladder_discount_brands');
        Schema::dropIfExists('ladder_discount_products');
        Schema::dropIfExists('ladder_discount_levels');
        Schema::dropIfExists('ladder_discount_rules');
    }
}