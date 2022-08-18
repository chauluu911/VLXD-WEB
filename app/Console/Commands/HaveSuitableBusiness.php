<?php

namespace App\Console\Commands;

use App\Jobs\SendWhenHaveSuitableBusiness;
use Illuminate\Console\Command;

class HaveSuitableBusiness extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'haveSuitableBusiness:enqueue-send-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enqueue a job sending notification to user when there are suitable business';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        SendWhenHaveSuitableBusiness::dispatch()->onQueue('sequentialTask');
        return 0;
    }
}
