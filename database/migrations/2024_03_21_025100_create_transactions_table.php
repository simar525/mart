<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->bigInteger('user_id')->unsigned();
            $table->double('amount', 18, 2);
            $table->double('fees', 18, 2)->default(0);
            $table->text('tax')->nullable();
            $table->double('total', 18, 2);
            $table->bigInteger('payment_gateway_id')->unsigned()->nullable();
            $table->string('payment_id')->nullable();
            $table->string('payer_id')->nullable();
            $table->string('payer_email')->nullable();
            $table->string('payment_proof')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0:Unpaid 1:Pending 2:Paid 3:Cancelled');
            $table->string('cancellation_reason')->nullable();
            $table->foreign("user_id")->references("id")->on('users')->onDelete('cascade');
            $table->foreign("payment_gateway_id")->references("id")->on('payment_gateways');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};