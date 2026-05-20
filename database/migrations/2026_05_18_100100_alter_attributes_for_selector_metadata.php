<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attributes', function (Blueprint $table) {
            if (!Schema::hasColumn('attributes', 'display_type')) {
                $table->string('display_type', 20)->nullable()->default('chip')->after('name');
            }
        });

        Schema::table('attribute_options', function (Blueprint $table) {
            if (!Schema::hasColumn('attribute_options', 'hex_code')) {
                $table->string('hex_code', 20)->nullable()->after('value');
            }
        });

        Schema::table('category_attribute', function (Blueprint $table) {
            if (!$this->indexExists('category_attribute', 'category_attribute_category_id_attribute_id_unique')) {
                $table->unique(['category_id', 'attribute_id']);
            }
        });

        Schema::table('attribute_options', function (Blueprint $table) {
            if (!$this->indexExists('attribute_options', 'attribute_options_attribute_id_value_unique')) {
                $table->unique(['attribute_id', 'value']);
            }
        });
    }

    public function down(): void
    {
        // Intentionally conservative to avoid removing metadata columns
        // that may already exist on a live catalog database.
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
