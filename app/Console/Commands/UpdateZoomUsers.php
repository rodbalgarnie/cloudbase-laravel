<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ZoomController;


class UpdateZoomUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zoomusers:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Hello Test');
		$zoom = new ZoomController();
   		$test = $zoom->getusersimeetings();
		
		$this->info($test);
    }
}
