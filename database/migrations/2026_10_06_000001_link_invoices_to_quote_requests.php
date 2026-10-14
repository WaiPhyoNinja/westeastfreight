<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->foreignId('quote_request_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('currency', 3)->nullable();
            $table->string('name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('etd_pol')->nullable();
            $table->string('eta_dest')->nullable();
            $table->string('volume')->nullable();
            $table->string('weight')->nullable();
            $table->string('commodity')->nullable();
            $table->string('terms_of_payment')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table): void {
            $table->dropForeign(['quote_request_id']);
            $table->dropUnique(['quote_request_id']);
            $table->dropColumn('quote_request_id');
            $table->dropColumn([
                'currency', 'name', 'company_name', 'email', 'phone', 'address',
                'etd_pol', 'eta_dest', 'volume', 'weight', 'commodity', 'terms_of_payment',
            ]);
        });
    }
};
