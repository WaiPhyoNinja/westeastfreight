<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table): void {
            $table->longText('company_header_html')->nullable()->after('website');
            $table->longText('terms_and_conditions_html')->nullable()->after('terms_and_conditions');
        });

        Schema::table('invoices', function (Blueprint $table): void {
            $table->string('bl_no')->nullable()->after('terms_of_payment');
            $table->string('cargo_type')->nullable()->after('bl_no');
        });

        Schema::table('invoice_items', function (Blueprint $table): void {
            $table->string('unit')->nullable()->after('quantity');
        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', fn (Blueprint $table) => $table->dropColumn('unit'));
        Schema::table('invoices', fn (Blueprint $table) => $table->dropColumn(['bl_no', 'cargo_type']));
        Schema::table('invoice_settings', fn (Blueprint $table) => $table->dropColumn(['company_header_html', 'terms_and_conditions_html']));
    }
};
