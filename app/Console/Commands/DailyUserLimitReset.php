<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EWUserLimitConfig;

class DailyUserLimitReset extends Command
{
    private $_frequency_id = 1;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:daily-limit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset Daily User Limit';

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
        if (EWUserLimitConfig::where('frequency_id', $this->_frequency_id)
                             ->update(['total_trx_amount' => 0, 'total_trx_count' => 0])) {
            $this->info("Daily User Limit Config Reset Finished.");
        }
    }
}
