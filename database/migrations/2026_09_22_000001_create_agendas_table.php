<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgendasTable extends Migration
{
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('data');
            $table->time('hora');
            $table->text('comentario');
            $table->string('google_event_id')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'user_id', 'data']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
}
