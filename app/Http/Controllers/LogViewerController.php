<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function index(Request $request)
    {
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

        return view('content.logs.index', compact('logs'));
    }

    public function clear()
    {
        $logFile = storage_path('logs/laravel.log');
        if (File::exists($logFile)) {
            File::put($logFile, '');
        }
        return redirect()->route('logs.index')->with('success', 'Logs limpos com sucesso!');
    }
}
