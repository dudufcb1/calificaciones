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
            $table->enum('status', ['active', 'pending', 'inactive'])->default('pending')->after('password');
            $table->enum('role', ['admin', 'user'])->default('user')->after('status');
            $table->text('deactivation_reason')->nullable()->after('role');
            $table->boolean('is_confirmed')->default(false)->after('deactivation_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('role');
            $table->dropColumn('deactivation_reason');
            $table->dropColumn('is_confirmed');
        });
    }
};
