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
            $options = $request->input('options', []);

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

            // Build the command with options
            // For db:seed, allow specifying a specific seeder class via options
            if ($command === 'db:seed' && isset($options['--class'])) {
                // --class option is already in $options, Artisan::call will handle it
            }
            
            $exitCode = Artisan::call($command, $options);
            $output = Artisan::output();

            // Log the command execution
            Log::info('Admin Tools - Artisan command executed', [
                'command' => $command,
                'options' => $options,
                'exit_code' => $exitCode,
                'ip' => $request->ip(),
                'user' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'exit_code' => $exitCode,
                'output' => $output,
                'message' => 'Command executed successfully.',
            ]);
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
}
