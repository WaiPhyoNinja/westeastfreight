<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->string('phone_en')->nullable()->after('phone');
            $table->string('phone_my')->nullable()->after('phone_en');
            $table->string('phone_th')->nullable()->after('phone_my');
            $table->text('address_en')->nullable()->after('address');
            $table->text('address_my')->nullable()->after('address_en');
            $table->text('address_th')->nullable()->after('address_my');
            $table->text('open_hours_en')->nullable()->after('address_th');
            $table->text('open_hours_my')->nullable()->after('open_hours_en');
            $table->text('open_hours_th')->nullable()->after('open_hours_my');
        });

        DB::table('site_settings')->update([
            'phone_en' => DB::raw('phone'),
            'address_en' => DB::raw('address'),
        ]);
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'phone_en', 'phone_my', 'phone_th',
                'address_en', 'address_my', 'address_th',
                'open_hours_en', 'open_hours_my', 'open_hours_th',
            ]);
        });
    }
};
