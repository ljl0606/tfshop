<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFullReductionRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('full_reduction_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('规则名称');
            $table->enum('type', ['include', 'exclude'])->default('include')->comment('规则类型：include-包含，exclude-排除');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('规则状态：active-启用，inactive-禁用');
            $table->dateTime('start_time')->nullable()->comment('开始时间');
            $table->dateTime('end_time')->nullable()->comment('结束时间');
            $table->text('description')->nullable()->comment('规则描述');
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
        Schema::dropIfExists('full_reduction_rules');
    }
}
