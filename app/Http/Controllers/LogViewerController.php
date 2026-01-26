<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
        // Logs do Laravel
        $logFile = storage_path('logs/laravel.log');
        $logs = [];

        if (File::exists($logFile)) {
            // Read the last 1000 lines
            $fileContent = File::get($logFile);
            $lines = explode("\n", $fileContent);
            $lines = array_reverse($lines); // Show newest first
            $logs = array_slice($lines, 0, 1000);
        } else {
            $logs[] = "Log file not found at $logFile";
        }

        // Logs do Console
        $consoleLogFile = storage_path('logs/console-output.log');
        $consoleLogs = [];

        if (File::exists($consoleLogFile)) {
             // Read the last 1000 lines
             $fileContent = File::get($consoleLogFile);
             $lines = explode("\n", $fileContent);
             $lines = array_reverse($lines); // Show newest first
             $consoleLogs = array_slice($lines, 0, 1000);
        } else {
            $consoleLogs[] = "Nenhum log de console encontrado.";
        }

        return view('content.logs.index', compact('logs', 'consoleLogs'));
    }

    public function clear()
    {
        $logFile = storage_path('logs/laravel.log');
        if (File::exists($logFile)) {
            File::put($logFile, '');
        }

        $consoleLogFile = storage_path('logs/console-output.log');
        if (File::exists($consoleLogFile)) {
            File::put($consoleLogFile, '');
        }

        return redirect()->route('logs.index')->with('success', 'Logs limpos com sucesso!');
    }
}
