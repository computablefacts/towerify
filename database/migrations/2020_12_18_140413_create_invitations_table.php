<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvitationsTable extends Migration
{
    public function up()
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->intOrBigIntBasedOnRelated('user_id', Schema::connection(null), 'users.id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('hash');
            $table->string('type');
            $table->json('options');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('utilized_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                  ->references('id')
                  ->on('users');
        });
    }

    public function down()
    {
        Schema::drop('invitations');
    }
}
