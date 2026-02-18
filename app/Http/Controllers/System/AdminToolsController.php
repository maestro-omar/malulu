<?php

namespace App\Http\Controllers\System;

use App\Http\Controllers\System\SystemBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class AdminToolsController extends SystemBaseController
{
    /**
     * Display the admin tools page.
     */
    public function index(): Response
    {
        return Inertia::render('AdminTools/Index');
    }

    /**
     * Execute an Artisan command (like migrations).
     */
    public function executeCommand(Request $request)
    {
        $request->validate([
            'command' => 'required|string|in:migrate,migrate:fresh,migrate:refresh,migrate:rollback,migrate:status,db:seed,db:drop-all-tables,cache:clear,config:clear,route:clear,view:clear,optimize:clear',
            'options' => 'nullable|array',
        ]);

        try {
            $command = $request->input('command');
            $optionsInput = $request->input('options', []);
            
            // Parse options if it's a JSON string (from streaming)
            if (is_string($optionsInput)) {
                $options = json_decode($optionsInput, true) ?: [];
            } else {
                $options = $optionsInput;
            }

            // Increase execution time and memory limit for long-running commands
            // Especially important for migrations and seeders
            $longRunningCommands = ['migrate', 'migrate:fresh', 'migrate:refresh', 'migrate:rollback', 'db:seed'];
            if (in_array($command, $longRunningCommands) || str_starts_with($command, 'migrate')) {
                set_time_limit(600); // 10 minutes
                ini_set('max_execution_time', '600');
                ini_set('memory_limit', '512M');
            } else {
                set_time_limit(120); // 2 minutes for other commands
                ini_set('max_execution_time', '120');
            }

            // Handle special command: drop all tables
            if ($command === 'db:drop-all-tables') {
                return $this->dropAllTables($request);
            }

            // Streaming disabled for now - show output after completion
            // Uncomment below to enable streaming (may not work in all environments)
            // $stream = $request->input('stream', false);
            // if ($stream) {
            //     return $this->executeCommandStreaming($command, $options, $request);
            // }

            // Build the command with options
            // For db:seed, allow specifying a specific seeder class via options
            if ($command === 'db:seed' && isset($options['--class'])) {
                // --class option is already in $options, Artisan::call will handle it
            }
            
            $exitCode = Artisan::call($command, $options);
            $output = Artisan::output();

            // Determine success based on exit code (0 = success, non-zero = error)
            // Note: Laravel's Artisan::call() returns 0 on success, but some commands
            // might return non-zero even on partial success (e.g., seeders with warnings)
            $success = $exitCode === 0;
            
            // For seeders, even if exit code is 0, check if output indicates real errors
            // Seeders might output "Error creating user..." via echo but still complete successfully
            $outputLower = strtolower($output);
            $hasRealErrors = false;
            
            if ($command === 'db:seed') {
                // For seeders, check if there are fatal errors vs warnings
                // If output contains "Error" but exit code is 0, it's likely just warnings
                // We'll still mark as success if exit code is 0, but log it
                if ($success && str_contains($outputLower, 'error')) {
                    // Check if it's just warnings (echo statements) vs real exceptions
                    // Real exceptions would cause non-zero exit code
                    Log::warning('Admin Tools - Seeder completed with warnings', [
                        'command' => $command,
                        'options' => $options,
                        'output_preview' => substr($output, 0, 500),
                    ]);
                }
            }

            // Log the command execution
            Log::info('Admin Tools - Artisan command executed', [
                'command' => $command,
                'options' => $options,
                'exit_code' => $exitCode,
                'success' => $success,
                'output_length' => strlen($output),
                'has_warnings' => $success && str_contains($outputLower, 'error'),
                'ip' => $request->ip(),
                'user' => Auth::id(),
            ]);

            // Handle large outputs - truncate if too large to avoid JSON encoding issues
            $maxOutputLength = 500000; // 500KB max
            $outputLength = strlen($output);
            $truncated = false;
            
            if ($outputLength > $maxOutputLength) {
                $truncated = true;
                $output = substr($output, 0, $maxOutputLength) . "\n\n... [Output truncated - " . number_format($outputLength) . " bytes total, showing first " . number_format($maxOutputLength) . " bytes]";
                Log::warning('Admin Tools - Output truncated due to size', [
                    'command' => $command,
                    'original_length' => $outputLength,
                    'truncated_to' => $maxOutputLength,
                ]);
            }
            
            // Ensure success is explicitly a boolean (not null/undefined)
            $responseData = [
                'success' => (bool) $success,
                'exit_code' => (int) $exitCode,
                'output' => $output,
                'output_length' => $outputLength,
                'truncated' => $truncated,
                'message' => $success 
                    ? 'Command executed successfully.' 
                    : 'Command completed with exit code ' . $exitCode . '.',
            ];
            
            // Log the response data being sent for debugging (but truncate output in log)
            Log::debug('Admin Tools - Sending response', [
                'success' => $responseData['success'],
                'success_type' => gettype($responseData['success']),
                'exit_code' => $responseData['exit_code'],
                'output_length' => $responseData['output_length'],
                'truncated' => $truncated,
                'output_preview' => substr($output, 0, 200) . ($outputLength > 200 ? '...' : ''),
            ]);
            
            // Ensure JSON encoding works - catch any encoding errors
            try {
                $jsonResponse = response()->json($responseData);
                
                // Verify the response can be encoded
                $testEncode = json_encode($responseData);
                if ($testEncode === false) {
                    $jsonError = json_last_error_msg();
                    Log::error('Admin Tools - JSON encoding failed', [
                        'error' => $jsonError,
                        'output_length' => $outputLength,
                    ]);
                    
                    // Fallback: return truncated/simplified response
                    return response()->json([
                        'success' => (bool) $success,
                        'exit_code' => (int) $exitCode,
                        'output' => substr($output, 0, 10000) . "\n\n... [Output too large for JSON encoding, truncated]",
                        'message' => $success ? 'Command executed successfully (output truncated).' : 'Command completed with errors (output truncated).',
                        'error' => 'Output too large to encode in JSON response',
                    ]);
                }
                
                return $jsonResponse;
            } catch (\Exception $e) {
                Log::error('Admin Tools - Response creation failed', [
                    'error' => $e->getMessage(),
                    'output_length' => $outputLength,
                ]);
                
                // Fallback response
                return response()->json([
                    'success' => (bool) $success,
                    'exit_code' => (int) $exitCode,
                    'output' => substr($output, 0, 10000) . "\n\n... [Error creating response, output truncated]",
                    'message' => $success ? 'Command executed successfully (response error).' : 'Command completed with errors (response error).',
                ], 200);
            }
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            $errorDetails = '';
            
            // Provide helpful hints for common errors
            if (str_contains($errorMessage, 'not found') && str_contains($errorMessage, 'UserFaker')) {
                $errorDetails = "\n\n⚠️ IMPORTANT: The UserFaker class file must be in the 'Faker' directory (capital F), not 'faker' (lowercase).\n";
                $errorDetails .= "On Linux servers, directory names are case-sensitive.\n";
                $errorDetails .= "Expected path: database/seeders/Faker/UserFaker.php\n";
                $errorDetails .= "If the file is in database/seeders/faker/UserFaker.php, you need to rename the directory to 'Faker'.\n";
            }
            
            // Handle timeout errors
            if (str_contains($errorMessage, 'Maximum execution time') || str_contains($errorMessage, 'timeout')) {
                $errorDetails = "\n\n⚠️ TIMEOUT: The command is taking too long to execute.\n";
                $errorDetails .= "The execution time limit has been increased to 10 minutes for migrations/seeders.\n";
                $errorDetails .= "If this persists, you may need to:\n";
                $errorDetails .= "1. Increase PHP max_execution_time in php.ini\n";
                $errorDetails .= "2. Increase web server timeout (Apache/Nginx)\n";
                $errorDetails .= "3. Run the command via command line if possible\n";
                $errorDetails .= "4. Consider running seeders separately instead of all at once\n";
            }
            
            Log::error('Admin Tools - Artisan command failed', [
                'command' => $request->input('command'),
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'ip' => $request->ip(),
                'user' => Auth::id(),
            ]);

            $fullErrorMessage = config('app.debug') 
                ? $errorMessage . "\n\nFile: " . $e->getFile() . "\nLine: " . $e->getLine() . $errorDetails
                : $errorMessage . $errorDetails;

            return response()->json([
                'success' => false,
                'message' => 'Error executing command: ' . $errorMessage,
                'output' => $fullErrorMessage,
            ], 500);
        }
    }

    /**
     * Execute a SQL query.
     */
    public function executeQuery(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'type' => 'required|string|in:select,insert,update,delete,other',
        ]);

        try {
            $query = $request->input('query');
            $type = $request->input('type');

            // Log the query execution
            Log::info('Admin Tools - SQL query executed', [
                'query_type' => $type,
                'query_preview' => substr($query, 0, 200),
                'ip' => $request->ip(),
                'user' => Auth::id(),
            ]);

            // Execute the query based on type
            if ($type === 'select') {
                // For SELECT queries, return the results
                $results = DB::select($query);
                return response()->json([
                    'success' => true,
                    'type' => 'select',
                    'results' => $results,
                    'count' => count($results),
                    'message' => 'Query executed successfully. ' . count($results) . ' row(s) returned.',
                ]);
            } else {
                // For INSERT, UPDATE, DELETE, or other queries
                $rowCount = DB::affectingStatement($query);
                
                return response()->json([
                    'success' => true,
                    'type' => $type,
                    'affected_rows' => $rowCount,
                    'message' => 'Query executed successfully. ' . $rowCount . ' row(s) affected.',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Admin Tools - SQL query failed', [
                'query_preview' => substr($request->input('query'), 0, 200),
                'error' => $e->getMessage(),
                'ip' => $request->ip(),
                'user' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error executing query: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Drop all tables from the database.
     */
    private function dropAllTables(Request $request)
    {
        try {
            $connection = DB::connection();
            $driverName = $connection->getDriverName();
            
            // Only support MySQL/MariaDB for now
            if ($driverName !== 'mysql') {
                return response()->json([
                    'success' => false,
                    'message' => 'Drop all tables is only supported for MySQL/MariaDB databases.',
                    'output' => "Current driver: {$driverName}",
                ], 400);
            }

            $databaseName = $connection->getDatabaseName();
            $tables = [];

            // Get all table names
            $tableNames = DB::select("SHOW TABLES");
            
            // Handle dynamic property name for "Tables_in_{database}" key
            // Convert result to array to access properties safely
            foreach ($tableNames as $table) {
                $tableArray = (array) $table;
                // The key format is "Tables_in_{database_name}"
                $key = array_key_first($tableArray);
                if ($key) {
                    $tables[] = $tableArray[$key];
                }
            }

            if (empty($tables)) {
                return response()->json([
                    'success' => true,
                    'exit_code' => 0,
                    'output' => 'No tables found in the database.',
                    'message' => 'No tables to drop.',
                ]);
            }

            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Drop all tables
            $droppedTables = [];
            foreach ($tables as $table) {
                DB::statement("DROP TABLE IF EXISTS `{$table}`");
                $droppedTables[] = $table;
            }

            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $output = "Dropped " . count($droppedTables) . " table(s):\n" . implode("\n", $droppedTables);

            // Log the operation
            Log::warning('Admin Tools - All tables dropped', [
                'tables_dropped' => count($droppedTables),
                'table_names' => $droppedTables,
                'ip' => $request->ip(),
                'user' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'exit_code' => 0,
                'output' => $output,
                'message' => 'All tables dropped successfully. ' . count($droppedTables) . ' table(s) removed.',
            ]);
        } catch (\Exception $e) {
            // Re-enable foreign key checks in case of error
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Exception $e2) {
                // Ignore
            }

            Log::error('Admin Tools - Drop all tables failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'ip' => $request->ip(),
                'user' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error dropping tables: ' . $e->getMessage(),
                'output' => $e->getMessage() . "\n\nFile: " . $e->getFile() . "\nLine: " . $e->getLine(),
            ], 500);
        }
    }

    /**
     * Execute command with streaming output using Symfony Process
     */
    private function executeCommandStreaming(string $command, array $options, Request $request)
    {
        try {
            // Get PHP binary - try multiple methods for compatibility
            $phpBinary = defined('PHP_BINARY') ? PHP_BINARY : 'php';
            if (empty($phpBinary) || !file_exists($phpBinary)) {
                // Try common PHP locations
                $phpBinary = 'php';
            }
            
            // Build artisan command
            $artisanPath = base_path('artisan');
            $cmd = [$phpBinary, $artisanPath, $command];
            
            // Add options
            foreach ($options as $key => $value) {
                if (is_bool($value) && $value) {
                    $cmd[] = $key;
                } elseif (!is_bool($value)) {
                    $cmd[] = $key;
                    if (is_string($value)) {
                        $cmd[] = $value;
                    }
                }
            }

            return response()->stream(function () use ($cmd, $command, $options, $request) {
                try {
                    // Create process with proper constructor signature
                    $process = Process::fromShellCommandline(
                        implode(' ', array_map('escapeshellarg', $cmd)),
                        base_path(),
                        null,
                        null,
                        600 // 10 minutes timeout
                    );
                    
                    // Start process and stream output
                    $process->start(function ($type, $buffer) {
                        $data = [
                            'type' => $type === Process::ERR ? 'error' : 'output',
                            'data' => $buffer,
                        ];
                        echo "data: " . json_encode($data) . "\n\n";
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    });

                    // Wait for completion
                    $process->wait();
                    
                    $exitCode = $process->getExitCode();
                    $finalOutput = $process->getOutput();
                    $errorOutput = $process->getErrorOutput();
                    
                    // Send any remaining output
                    if (!empty($finalOutput)) {
                        $data = ['type' => 'output', 'data' => $finalOutput];
                        echo "data: " . json_encode($data) . "\n\n";
                    }
                    if (!empty($errorOutput)) {
                        $data = ['type' => 'error', 'data' => $errorOutput];
                        echo "data: " . json_encode($data) . "\n\n";
                    }
                    
                    // Log the command execution
                    Log::info('Admin Tools - Artisan command executed (streaming)', [
                        'command' => $command,
                        'options' => $options,
                        'exit_code' => $exitCode,
                        'ip' => $request->ip(),
                        'user' => Auth::id(),
                    ]);

                    // Send completion message
                    $completionData = [
                        'type' => 'complete',
                        'success' => $exitCode === 0,
                        'exit_code' => $exitCode,
                        'message' => $exitCode === 0 ? 'Command executed successfully.' : 'Command completed with errors.',
                    ];
                    echo "data: " . json_encode($completionData) . "\n\n";
                    
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                    
                } catch (\Exception $e) {
                    $errorData = [
                        'type' => 'error',
                        'data' => 'Error: ' . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine(),
                    ];
                    echo "data: " . json_encode($errorData) . "\n\n";
                    
                    $completionData = [
                        'type' => 'complete',
                        'success' => false,
                        'exit_code' => 1,
                        'message' => 'Error executing command: ' . $e->getMessage(),
                    ];
                    echo "data: " . json_encode($completionData) . "\n\n";
                    
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                    
                    Log::error('Admin Tools - Streaming command failed', [
                        'command' => $command,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                        'ip' => $request->ip(),
                        'user' => Auth::id(),
                    ]);
                }
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no', // Disable nginx buffering
                'Connection' => 'keep-alive',
            ]);
            
        } catch (\Exception $e) {
            // If streaming setup fails, fall back to regular execution
            Log::warning('Admin Tools - Streaming setup failed, falling back to regular execution', [
                'command' => $command,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Fall back to regular Artisan::call
            $exitCode = Artisan::call($command, $options);
            $output = Artisan::output();
            
            return response()->json([
                'success' => $exitCode === 0,
                'exit_code' => $exitCode,
                'output' => $output,
                'message' => $exitCode === 0 ? 'Command executed successfully.' : 'Command completed with errors.',
                'note' => 'Streaming not available, output shown after completion.',
            ]);
        }
    }
}
