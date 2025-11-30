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
            $table->unsignedBigInteger('rule_id')->comment('规则ID');
            $table->decimal('min_amount', 10, 2)->comment('最低消费金额');
            $table->decimal('discount_amount', 10, 2)->comment('减免金额');
            $table->integer('sort')->default(0)->comment('排序');
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
        Schema::dropIfExists('full_reduction_tiers');
    }
}
