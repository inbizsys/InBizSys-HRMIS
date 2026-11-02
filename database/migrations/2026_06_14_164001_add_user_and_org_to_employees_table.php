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
        Schema::table('employees', function (Blueprint $table) {
            
            if (!Schema::hasColumn('employees', 'organization_id')) {
                $table->unsignedBigInteger('organization_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('employees', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('organization_id');
            }

            if (Schema::hasColumn('employees', 'country')) {
                $table->renameColumn('country', 'work_location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'work_location')) {
                $table->renameColumn('work_location', 'country');
            }
            $table->dropColumn(['organization_id', 'user_id']);
        });
    }
};
