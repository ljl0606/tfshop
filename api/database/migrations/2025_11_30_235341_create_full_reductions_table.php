<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFullReductionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('full_reductions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('活动名称');
            $table->timestamp('start_time')->comment('开始时间');
            $table->timestamp('end_time')->comment('结束时间');
            $table->tinyInteger('participation_type')->default(0)->comment('参与类型：0-全部商品参与，1-指定商品/品牌参与，2-排除商品/品类参与');
            $table->json('include_goods_ids')->nullable()->comment('包含的商品ID列表');
            $table->json('include_brands_ids')->nullable()->comment('包含的品牌ID列表');
            $table->json('exclude_goods_ids')->nullable()->comment('排除的商品ID列表');
            $table->json('exclude_categories_ids')->nullable()->comment('排除的品类ID列表');
            $table->json('include_categories_ids')->nullable()->comment('包含的品类ID列表');
            $table->json('exclude_brands_ids')->nullable()->comment('排除的品牌ID列表');
            $table->tinyInteger('status')->default(1)->comment('状态：0-禁用，1-启用');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('full_reductions');
    }
}
