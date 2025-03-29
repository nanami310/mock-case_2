<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('status'); // 'off', 'working', 'checked_out' など
            $table->timestamp('check_in')->nullable(); // 出勤時間
            $table->timestamp('check_out')->nullable(); // 退勤時間
            $table->timestamp('break_start')->nullable(); // 休憩開始時刻
            $table->timestamp('break_end')->nullable(); // 休憩終了時刻
            $table->integer('break_time')->default(0); // 休憩時間（分単位）
            $table->decimal('total_hours', 5, 2)->default(0); // 合計時間（小数点以下2桁）
            $table->string('remarks')->nullable(); // 備考カラムを追加
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}