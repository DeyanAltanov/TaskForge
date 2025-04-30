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
        Schema::table('updates', function (Blueprint $table) {
            $table->foreign(['user_id'], 'updates_ibfk_1')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['updated_by'], 'updates_ibfk_2')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('updates', function (Blueprint $table) {
            $table->dropForeign('updates_ibfk_1');
            $table->dropForeign('updates_ibfk_2');
        });
    }
};
