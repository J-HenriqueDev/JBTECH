<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Add unidade_comercial column if it doesn't exist
        if (!Schema::hasColumn('produtos', 'unidade_comercial')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->string('unidade_comercial')->default('UN')->after('ncm');
            });
        }

        // 2. Update 'Serviço' product data
        DB::table('produtos')
            ->where('nome', 'Serviço')
            ->update([
                'ncm' => '00000000',
                'unidade_comercial' => 'UN'
            ]);

        // 3. Ensure all products have a valid unidade_comercial
        DB::table('produtos')
            ->whereNull('unidade_comercial')
            ->orWhere('unidade_comercial', '')
            ->update(['unidade_comercial' => 'UN']);
    }

    public function down()
    {
        // We generally don't remove the column in down() if it might have been added by the original migration in a fresh install,
        // but for an update migration, we might want to reverse it.
        // However, since we modified the original migration file too, this column is now "standard".
        // So leaving it is safer, or checking if we should drop it.
        // For simplicity and safety (data preservation), we won't drop it here, or we can.
        // Let's drop it only if we added it? Hard to track.
        // Typically we drop it if we added it.
        
        /*
        if (Schema::hasColumn('produtos', 'unidade_comercial')) {
            Schema::table('produtos', function (Blueprint $table) {
                $table->dropColumn('unidade_comercial');
            });
        }
        */
    }
};
