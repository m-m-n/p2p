<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Laravel\Prompts\Output\ConsoleOutput;

class ClientStart extends Command
{
    const COMMANDS = [
        "/^(help)$/",
        "/^(refresh-share)$/",
        "/^(search)\s+(.*$)/",
        "/^(download)\s+([0-9a-fA-F]{160})$/",
        "/^(node-add)\s+(.*:[1-9][0-9]*)$/",
        "/^(node-list)$/",
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:client-start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'P2P Client Application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->initializeDatabase();

        readline_completion_function(function ($input, $index) {
            return [];
        });

        while (true) {
            $command = readline("p2p> ");

            if ($command === false) {
                break;
            }

            $command = trim($command);

            $parsed_items = $this->parseCommand($command);
            if (!isset($parsed_items[1])) {
                continue;
            }

            $this->addCommandHistory($command);

            switch ($parsed_items[1]) {
                case "help":
                    $usage = <<<EOT
                    Usage:
                        help
                        refresh-share
                        search <keyword>
                        download <hash>
                        node-add <host:port>
                        node-list
                    EOT;
                    $this->info($usage);
                    break;
                case "refresh-share":
                    Artisan::call("app:refresh-share", [], new ConsoleOutput());
                    break;
                case "search":
                    Artisan::call("app:search", [
                        "keyword" => $parsed_items[2],
                    ], new ConsoleOutput());
                    break;
                case "download":
                    Artisan::call("app:download", [
                        "hash" => $parsed_items[2],
                    ], new ConsoleOutput());
                    break;
                case "node-add":
                    Artisan::call("app:node-add", [
                        "node" => $parsed_items[2],
                    ], new ConsoleOutput());
                    break;
                case "node-list":
                    Artisan::call("app:node-list", [], new ConsoleOutput());
                    break;
                default:
                    $this->error("Invalid command");
            }
        }
    }

    private function initializeDatabase()
    {
        // 初回起動時にデータベースを作成する
        $databasePath = config('database.connections.sqlite.database');

        if (!File::exists($databasePath)) {
            File::put($databasePath, "");
            Artisan::call('migrate', ['--force' => true]);
        }
    }

    private function parseCommand(string $command): array
    {
        foreach (self::COMMANDS as $pattern) {
            if (preg_match($pattern, $command, $matches)) {
                return $matches;
            }
        }

        return [];
    }

    private function addCommandHistory(string $command)
    {
        $history = readline_list_history();

        $index = array_search($command, $history);
        if ($index !== false) {
            unset($history[$index]);
        }

        readline_clear_history();

        foreach ($history as $item) {
            readline_add_history($item);
        }
        readline_add_history($command);
    }
}
