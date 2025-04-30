<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 20)->change();
            $table->string('last_name', 20)->change();
            $table->string('email', 30)->change();
            $table->string('phone', 20)->change();
            $table->string('gender', 20)->change();
            $table->string('password', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name', 255)->change();
            $table->string('last_name', 255)->change();
            $table->string('email', 255)->change();
            $table->string('phone', 255)->change();
            $table->string('gender', 255)->change();
            $table->string('password', 255)->change();
        });
    }
};