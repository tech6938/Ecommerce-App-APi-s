<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'price')) {
                $table->decimal('price', 10, 2)->nullable()->after('status');
            }

            if (!Schema::hasColumn('products', 'discount_price')) {
                $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            }

            if (!Schema::hasColumn('products', 'currency')) {
                $table->string('currency', 10)->nullable()->after('discount_price');
            }

            if (!Schema::hasColumn('products', 'specifications')) {
                $table->json('specifications')->nullable()->after('currency');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (!Schema::hasColumn('product_variants', 'discount_price')) {
                $table->decimal('discount_price', 10, 2)->nullable()->after('price');
            }

            if (!Schema::hasColumn('product_variants', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('image');
            }
        });

        Schema::table('product_variants', function (Blueprint $table) {
            if (!$this->indexExists('product_variants', 'product_variants_product_id_is_default_index')) {
                $table->index(['product_id', 'is_default']);
            }
        });
    }

    public function down(): void
    {
        // Intentionally conservative.
        // This migration fills schema drift in environments that may already
        // have some of these columns, so rollback is left as a no-op.
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
