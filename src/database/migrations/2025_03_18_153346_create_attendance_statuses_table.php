<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendanceStatusesTable extends Migration
{
    public function up()
    {
        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // 申請者のユーザーID
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // 'pending', 'approved', 'rejected'
            $table->time('check_in')->nullable(); // 勤怠のチェックイン時間
            $table->time('check_out')->nullable(); // 勤怠のチェックアウト時間
            $table->time('break_start')->nullable(); // 休憩開始時間
            $table->time('break_end')->nullable(); // 休憩終了時間
            $table->text('remarks')->nullable(); // 備考
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_statuses');
    }
}