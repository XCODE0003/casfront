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
        Schema::table('users', function (Blueprint $table) {
            $table->string('bnb_bep20_dep_address')->nullable()->after('eth_dep_address');
            $table->string('ton_dep_address')->nullable()->after('bnb_bep20_dep_address');
            $table->string('sol_dep_address')->nullable()->after('ton_dep_address');
            $table->string('usdt_bep20_dep_address')->nullable()->after('sol_dep_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bnb_bep20_dep_address',
                'ton_dep_address',
                'sol_dep_address',
                'usdt_bep20_dep_address'
            ]);
        });
    }
}; 