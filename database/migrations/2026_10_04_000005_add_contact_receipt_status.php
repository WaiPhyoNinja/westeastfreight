<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_submissions', function (Blueprint $table): void {
            $table->string('receipt_mail_status')->nullable();
            $table->timestamp('receipt_mail_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('website_submissions', fn (Blueprint $table) => $table->dropColumn([
            'receipt_mail_status', 'receipt_mail_sent_at',
        ]));
    }
};
