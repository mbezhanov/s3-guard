<?php

namespace App\Console\Commands;

use App\Models\Host;
use Illuminate\Console\Command;

class RemoveHost extends Command
{
    protected $signature = 'host:remove {name : The name of the host that you want to remove}';
    protected $description = 'Remove a host definition from the S3 Guard';

    public function handle()
    {
        $name = $this->argument('name');
        $host = Host::where('name', $name)->first();

        if (!$host) {
            $this->error(sprintf('Cannot find a host named: "%s"', $name));
            return;
        }

        if ($host->delete()) {
            $this->info(sprintf('Host "%s" has been removed successfully', $name));
            return;
        }
        $this->error(sprintf('Could not delete host "%s"', $name));
    }
}
