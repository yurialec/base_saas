<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneAndCpfCnpjToTenantsTable extends Migration
{
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Empresas existentes permanecem válidas sem os novos dados.
            $table->string('phone', 15)->nullable();
            $table->string('cpf_cnpj', 14)->nullable();
        });
    }

    public function down()
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['phone', 'cpf_cnpj']);
        });
    }
}
