<?php

namespace App\Console\Commands;

use App\Models\Host;
use Illuminate\Console\Command;

class AddHost extends Command
{
    protected $signature = 'host:add';
    protected $description = 'Add a host definition to the S3 Guard';

    public function handle()
    {
        $host = new Host();
        $host->name = $this->ask('Hostname (e.g. test.example.com)');
        $host->username = $this->ask('HTTP Auth Username');
        $host->password = password_hash($this->secret('HTTP Auth Password'), PASSWORD_DEFAULT);
        $host->bucket_name = $this->ask('S3 Bucket Name (e.g. my.s3.bucket)');
        $host->access_key = $this->ask('AWS Key (20 symbols)');
        $host->secret_key = $this->ask('AWS Secret Key (40 symbols)');
        $host->region_name = $this->ask('AWS Region Name (e.g. us-west-1)');

        if ($host->save()) {
            $this->info('Host was added successfully');
        } else {
            $this->error('Could not add host');
        }
    }
}
