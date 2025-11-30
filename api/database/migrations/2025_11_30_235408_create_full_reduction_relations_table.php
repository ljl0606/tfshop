<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFullReductionRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('full_reduction_relations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rule_id')->comment('规则ID');
            $table->enum('relation_type', ['good', 'brand', 'category'])->comment('关联类型：good-商品，brand-品牌，category-品类');
            $table->unsignedBigInteger('relation_id')->comment('关联ID');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('rule_id')->references('id')->on('full_reduction_rules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('full_reduction_relations');
    }
}
