<?php

namespace App\Console\Commands;

use App\Jobs\FeedNotifyUserJobQueue;
use Illuminate\Console\Command;

class EnqueueSendNotificationJob extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:enqueue-feed-job';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enqueue a job sending notification to user';

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
        FeedNotifyUserJobQueue::dispatch()->onQueue('sequentialTask');
        return 0;
    }
}
