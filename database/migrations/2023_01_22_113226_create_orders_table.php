<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->nullable();
            $table->unsignedDecimal('price')->default(0);
            $table->unsignedDecimal('fee_price')->default(0);
            $table->string('notify_url')->nullable();
            $table->string('notify_id')->nullable();
            $table->unsignedInteger('status')->default(0);
            $table->unsignedInteger('notify_status')->default(0);
            $table->text('notify_return')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
