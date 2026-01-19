<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            if (!Schema::hasColumn('produtos', 'cest')) {
                $table->string('cest', 7)->nullable()->after('ncm');
            }
            if (!Schema::hasColumn('produtos', 'cfop_interno')) {
                $table->string('cfop_interno', 4)->nullable()->after('cest');
            }
            if (!Schema::hasColumn('produtos', 'cfop_externo')) {
                $table->string('cfop_externo', 4)->nullable()->after('cfop_interno');
            }
            if (!Schema::hasColumn('produtos', 'unidade_comercial')) {
                $table->string('unidade_comercial', 6)->nullable()->after('cfop_externo');
            }
            if (!Schema::hasColumn('produtos', 'unidade_tributavel')) {
                $table->string('unidade_tributavel', 6)->nullable()->after('unidade_comercial');
            }
            if (!Schema::hasColumn('produtos', 'origem')) {
                $table->integer('origem')->default(0)->after('unidade_tributavel');
            }
            if (!Schema::hasColumn('produtos', 'csosn_icms')) {
                $table->string('csosn_icms', 4)->nullable()->after('origem');
            }
            if (!Schema::hasColumn('produtos', 'cst_icms')) {
                $table->string('cst_icms', 3)->nullable()->after('csosn_icms');
            }
            if (!Schema::hasColumn('produtos', 'cst_pis')) {
                $table->string('cst_pis', 3)->nullable()->after('cst_icms');
            }
            if (!Schema::hasColumn('produtos', 'cst_cofins')) {
                $table->string('cst_cofins', 3)->nullable()->after('cst_pis');
            }
            if (!Schema::hasColumn('produtos', 'aliquota_icms')) {
                $table->decimal('aliquota_icms', 5, 2)->nullable()->after('cst_cofins');
            }
            if (!Schema::hasColumn('produtos', 'aliquota_pis')) {
                $table->decimal('aliquota_pis', 5, 2)->nullable()->after('aliquota_icms');
            }
            if (!Schema::hasColumn('produtos', 'aliquota_cofins')) {
                $table->decimal('aliquota_cofins', 5, 2)->nullable()->after('aliquota_pis');
            }
            if (!Schema::hasColumn('produtos', 'perc_icms_fcp')) {
                $table->decimal('perc_icms_fcp', 5, 2)->nullable()->after('aliquota_cofins');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn([
                'cest',
                'cfop_interno',
                'cfop_externo',
                'unidade_comercial',
                'unidade_tributavel',
                'origem',
                'csosn_icms',
                'cst_icms',
                'cst_pis',
                'cst_cofins',
                'aliquota_icms',
                'aliquota_pis',
                'aliquota_cofins',
                'perc_icms_fcp'
            ]);
        });
    }
};
