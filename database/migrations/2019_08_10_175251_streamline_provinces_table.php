<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Konekt\Address\Models\ProvinceTypeProxy;

class StreamlineProvincesTable extends Migration
{
    public function up()
    {
        /** based on ISO 3166-2 https://en.wikipedia.org/wiki/ISO_3166-2 */
        Schema::table('provinces', function (Blueprint $table) {
            // Get rid of enum field, they're fkcn painful with Laravel
            $table->string('type', 16)->default(ProvinceTypeProxy::defaultValue())->change();
        });

        Schema::table('provinces', function (Blueprint $table) {
            $table->integer('parent_id')->unsigned()->nullable();

            $table->index('code', 'provinces_code_index');
            $table->unique(['country_id', 'code'], 'provinces_country_id_code_index');
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('provinces')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('provinces', function (Blueprint $table) {
            // This index has to be added back manually, because
            // creating a composite index with a field that's
            // an fkey causes original index to be dropped
            $table->index('country_id', 'provinces_country_id_foreign');

            if (!$this->isSqlite()) {
                $table->dropIndex('provinces_code_index');
                $table->dropUnique('provinces_country_id_code_index');
                $table->dropForeign('provinces_parent_id_foreign');
            }

            $table->dropColumn('parent_id');
        });
    }

    private function isSqlite(): bool
    {
        return 'sqlite' === Schema::connection($this->getConnection())
                ->getConnection()
                ->getPdo()
                ->getAttribute(PDO::ATTR_DRIVER_NAME)
        ;
    }
}
