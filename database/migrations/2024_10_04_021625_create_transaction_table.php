<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaction', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('from_card_id');
            $table->foreign('from_card_id')->references('id')->on('card');
            $table->unsignedBigInteger('to_card_id')->nullable();
            $table->foreign('to_card_id')->references('id')->on('card');
            $table->double('amount', 16, 2);
            $table->enum('type', ['withdraw', 'deposit', 'transfer']);
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction');
    }
};
