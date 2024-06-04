<?php

namespace Homeful\Loan\Commands;

use Illuminate\Console\Command;

class LoanCommand extends Command
{
    public $signature = 'loan';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
