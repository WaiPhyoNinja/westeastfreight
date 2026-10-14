<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table): void {
            $table->json('item_descriptions')->nullable()->after('terms_and_conditions_html');
            $table->json('item_units')->nullable()->after('item_descriptions');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_settings', fn (Blueprint $table) => $table->dropColumn(['item_descriptions', 'item_units']));
    }
};
