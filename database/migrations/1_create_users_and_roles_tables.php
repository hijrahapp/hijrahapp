<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->enum('name', ['SuperAdmin', 'Admin', 'Expert', 'Customer'])->unique();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->boolean('active')->default(false);
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('birthDate')->nullable();
            $table->unsignedBigInteger('roleId');
            $table->timestamps();
            $table->foreign('roleId')->references('id')->on('roles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
