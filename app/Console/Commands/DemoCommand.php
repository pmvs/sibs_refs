<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DemoCommand extends Command
{
    protected $signature = 'demo:command';

    protected $description = 'Your demo command description.';

    public function handle()
    {
        // Your command logic here
    }
}