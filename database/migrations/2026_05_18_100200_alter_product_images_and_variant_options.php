<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            if (!Schema::hasColumn('product_images', 'attribute_option_id')) {
                $table->foreignId('attribute_option_id')
                    ->nullable()
                    ->after('sort_order')
                    ->constrained('attribute_options')
                    ->nullOnDelete();
            }
        });

        Schema::table('product_variant_options', function (Blueprint $table) {
            if (!$this->indexExists('product_variant_options', 'product_variant_options_variant_id_attribute_option_id_unique')) {
                $table->unique(['variant_id', 'attribute_option_id']);
            }
        });

        Schema::table('product_images', function (Blueprint $table) {
            if (!$this->indexExists('product_images', 'product_images_product_id_attribute_option_id_index')) {
                $table->index(['product_id', 'attribute_option_id']);
            }
        });
    }

    public function down(): void
    {
        // Intentionally conservative to avoid destructive rollback on
        // production data where image tagging has already been populated.
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $result = DB::selectOne(
            'SELECT COUNT(1) AS aggregate
             FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [DB::getDatabaseName(), $table, $indexName]
        );

        return (int) ($result->aggregate ?? 0) > 0;
    }
};
