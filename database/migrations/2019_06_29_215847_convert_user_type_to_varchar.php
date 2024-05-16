<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Konekt\User\Models\UserProxy;
use Konekt\User\Models\UserTypeProxy;

class ConvertUserTypeToVarchar extends Migration
{
    public function up()
    {
        $typeFieldName = $this->escape('type');

        Schema::table('users', function (Blueprint $table) {
            $table->string('type_new')->after('type')->default('client');
        });

        DB::update("UPDATE users set type_new = $typeFieldName");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('type')->after('type_new')->default('client');
        });

        DB::update("UPDATE users set $typeFieldName = type_new");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type_new');
        });
    }

    public function down()
    {
        $typeFieldName = $this->escape('type');

        Schema::table('users', function (Blueprint $table) {
            $table->string('type_new')->after('type')->default('client');
        });

        DB::update("update users set type_new = $typeFieldName");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->enum('type', ['client', 'admin', 'api'])->default('client');
        });

        /** @var \Konekt\User\Contracts\User $user */
        foreach (UserProxy::all() as $user) {
            // Must handle this, otherwise it might fail with:
            // MySQL:    Data truncated for column 'type'
            // Postgres: Check violation: 7 ERROR: new row for relation "users"
            //           violates check constraint "users_type_check"
            $user->type = UserTypeProxy::has($user->type_new) ? $user->type_new : UserTypeProxy::defaultValue();
            $user->save();
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type_new');
        });
    }

    private function escape(string $word): string
    {
        return DB::table('users')->getGrammar()->wrap($word);
    }
}
