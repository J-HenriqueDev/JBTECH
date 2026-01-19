<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--path= : Caminho relativo em storage/app para salvar o backup}';

    protected $description = 'Gera um backup completo do banco de dados em JSON';

    public function handle()
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        if ($driver !== 'mysql') {
            $this->error("Driver de banco de dados '{$driver}' não suportado por este comando.");
            return Command::FAILURE;
        }

        $tablesResult = $connection->select('SHOW TABLES');

        if (empty($tablesResult)) {
            $this->error('Nenhuma tabela encontrada para backup.');
            return Command::FAILURE;
        }

        $tables = [];
        foreach ($tablesResult as $row) {
            $values = array_values((array) $row);
            if (!empty($values[0])) {
                $tables[] = $values[0];
            }
        }

        if (empty($tables)) {
            $this->error('Não foi possível identificar os nomes das tabelas.');
            return Command::FAILURE;
        }

        $backupData = [
            'connection' => $connection->getName(),
            'driver' => $driver,
            'database' => $connection->getDatabaseName(),
            'generated_at' => now()->toIso8601String(),
            'tables' => [],
        ];

        foreach ($tables as $table) {
            $this->info("Exportando tabela: {$table}");

            $rows = DB::table($table)->get();
            $backupData['tables'][$table] = $rows->map(function ($row) {
                return (array) $row;
            })->toArray();
        }

        $relativePath = $this->option('path') ?: 'backups';
        $relativePath = trim($relativePath, '/');

        if (!Storage::exists($relativePath)) {
            Storage::makeDirectory($relativePath);
        }

        $fileName = 'backup_' . now()->format('Ymd_His') . '.json';
        $fullPath = $relativePath . '/' . $fileName;

        Storage::put($fullPath, json_encode($backupData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info('Backup gerado com sucesso.');
        $this->info('Arquivo: ' . storage_path('app/' . $fullPath));

        return Command::SUCCESS;
    }
}

