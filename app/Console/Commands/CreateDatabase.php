<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('db:create')]
#[Description('Command description')]
class CreateDatabase extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $config = config('database.connections.mysql');

        $pdo = new \PDO(
            "mysql:host={$config['host']};port={$config['port']}",
            $config['username'],
            $config['password']
        );

        $database = $config['database'];

        $pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `$database`"
        );

        $this->info("Database $database created successfully!");
    }
}
