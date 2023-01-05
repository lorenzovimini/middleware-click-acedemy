<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('source')->nullable()->default(null);
            $table->string('referer')->nullable()->default(null);
            $table->string('type', 50)->nullable()->default(null);
            $table->string('name', 100);
            $table->string('surname'. 100);
            $table->string('region', 100)->nullable()->default(null);
            $table->string('state', 100)->nullable()->default(null);
            $table->string('country', 100)->nullable()->default(null);
            $table->string('phone', 100)->nullable()->default(null);
            $table->string('email', 255);
            $table->string('course', 100)->nullable()->default(null);
            $table->timestamp('accept970_at')->nullable()->default(null);
            $table->timestamp('make_processed_at')->nullable()->default(null);
            $table->timestamp('crm_processed_at')->nullable()->default(null);
            $table->text('notes')->nullable()->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
};
