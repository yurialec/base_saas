<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->string('tenant_id', 36)->change();
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->foreign('tenant_id', 'fk_mhr_tenant')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
        });
    }
};
