<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quote_requests', function (Blueprint $table): void {
            $table->text('quotation_message')->nullable();
            $table->string('quotation_attachment')->nullable();
            $table->timestamp('quotation_sent_at')->nullable();
        });

        Schema::table('website_submissions', function (Blueprint $table): void {
            $table->text('reply_message')->nullable();
            $table->timestamp('replied_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('quote_requests', fn (Blueprint $table) => $table->dropColumn([
            'quotation_message', 'quotation_attachment', 'quotation_sent_at',
        ]));
        Schema::table('website_submissions', fn (Blueprint $table) => $table->dropColumn([
            'reply_message', 'replied_at',
        ]));
    }
};
