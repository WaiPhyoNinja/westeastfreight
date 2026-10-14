<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('brand_name')->default('WEST EAST FREIGHT');
            $table->string('brand_mark')->default('WE');
            $table->json('header_menu')->nullable();
            $table->json('footer_menu')->nullable();
            $table->text('footer_description_en')->nullable();
            $table->text('footer_description_my')->nullable();
            $table->text('footer_description_th')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->json('quotation_countries')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
