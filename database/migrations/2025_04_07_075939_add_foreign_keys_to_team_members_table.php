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
        Schema::table('team_members', function (Blueprint $table) {
            $table->foreign(['team_id'], 'team_members_ibfk_1')->references(['id'])->on('teams')->onUpdate('restrict')->onDelete('cascade');
            $table->foreign(['user_id'], 'team_members_ibfk_2')->references(['id'])->on('users')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropForeign('team_members_ibfk_1');
            $table->dropForeign('team_members_ibfk_2');
        });
    }
};
