<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendance_break_approvals', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_approval_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('attendance_break_approvals', function (Blueprint $table) {
            $table->dropColumn('attendance_approval_id');
        });
    }
};
