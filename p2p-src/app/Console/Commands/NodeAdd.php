<?php

namespace App\Console\Commands;

use App\Models\NodeList;
use Illuminate\Console\Command;
use GuzzleHttp\Client;

class NodeAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:node-add {node}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初期ノード追加';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $node = trim($this->argument('node'));
            list($host, $port) = explode(':', $node);

            if (!$this->checkHost($host, (int) $port)) {
                throw new \Exception('ノードへ接続できません');
            }

            NodeList::updateOrcreate([
                'host' => $host,
                'port' => $port,
            ]);

            $this->addNodes($host, (int) $port);
        } catch (\Exception $e) {
            $this->error("ノード追加に失敗しました: {$e->getMessage()}");
        }
    }

    private function checkHost(string $host, int $port): bool
    {
        $client = new Client();
        $response = $client->get("http://{$host}:{$port}/status", [
            "query" => [
                "port" => env('APP_PORT'),
            ],
        ]);

        $json = json_decode($response->getBody(), true);
        if (empty($json)) {
            return false;
        }
        if ($json['status'] !== 'OK') {
            return false;
        }
        return true;
    }

    private function addNodes(string $host, int $port): void
    {
        $client = new Client();
        $response = $client->get("http://{$host}:{$port}/node-list");

        $json = json_decode($response->getBody(), true);
        $this->info($response->getBody());
        if (empty($json)) {
            return;
        }

        foreach ($json as $node) {
            NodeList::updateOrCreate([
                'host' => $node['host'],
                'port' => $node['port'],
            ]);
        }
    }
}
