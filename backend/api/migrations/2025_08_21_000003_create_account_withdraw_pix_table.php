<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateAccountWithdrawPixTable extends Migration
{
    public function up(): void
    {
        Schema::create('account_withdraw_pix', function (Blueprint $table) {
            $table->string('account_withdraw_id', 36);
            $table->string('type');
            $table->string('key');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('account_withdraw_id');
            $table->foreign('account_withdraw_id')->references('id')->on('account_withdraw')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_withdraw_pix');
    }
}
