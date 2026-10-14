<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('website_submissions', fn (Blueprint $table) => $table->string('reply_mail_status')->nullable());
        Schema::table('quote_requests', fn (Blueprint $table) => $table->string('quotation_mail_status')->nullable());
    }

    public function down(): void
    {
        Schema::table('website_submissions', fn (Blueprint $table) => $table->dropColumn('reply_mail_status'));
        Schema::table('quote_requests', fn (Blueprint $table) => $table->dropColumn('quotation_mail_status'));
    }
};
