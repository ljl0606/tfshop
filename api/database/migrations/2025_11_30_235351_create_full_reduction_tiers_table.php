<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFullReductionTiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('full_reduction_tiers', function (Blueprint $table) {
            $table->id();
            $table->integer('full_reduction_id')->comment('所属满减活动ID');
            $table->integer('full_amount')->comment('满减金额（单位：分）');
            $table->integer('reduce_amount')->comment('减免金额（单位：分）');
            $table->integer('sort')->default(0)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('full_reduction_id')->references('id')->on('full_reductions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('full_reduction_tiers');
    }
}
