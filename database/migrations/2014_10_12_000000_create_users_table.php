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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('usdt_dep_address')->nullable();
            $table->string('btc_dep_address')->nullable();
            $table->string('eth_dep_address')->nullable();
            $table->string('bnb_bep20_dep_address')->nullable();
            $table->string('ton_dep_address')->nullable();
            $table->string('sol_dep_address')->nullable();
            $table->string('usdt_bep20_dep_address')->nullable();
            $table->string('avatar')->nullable();
            $table->tinyInteger('role_id')->default(3);
            $table->integer('win_chance')->default(75);
            $table->boolean('is_verification')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
