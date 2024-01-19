<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NodeList as NodeListModel;

class NodeList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:node-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (NodeListModel::all() as $node) {
            $this->info("{$node->host}:{$node->port}");
        }
    }
}
