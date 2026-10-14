<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->string('page_type')->default('page')->after('slug')->index();
        });

        DB::table('pages')
            ->whereIn('slug', ['air-freight', 'sea-freight', 'land-transport'])
            ->update(['page_type' => 'service']);
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table): void {
            $table->dropIndex(['page_type']);
            $table->dropColumn('page_type');
        });
    }
};
