<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quote_email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_request_id')->constrained()->cascadeOnDelete();
            $table->uuid('batch_id');
            $table->string('type', 20);
            $table->string('stage', 20);
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('status', 20)->default('queued');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['batch_id', 'recipient_email']);
            $table->index(['quote_request_id', 'stage', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_email_deliveries');
    }
};
