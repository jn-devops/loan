<?php

namespace Homeful\Loan;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Homeful\Loan\Commands\LoanCommand;

class LoanServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('loan')
            ->hasConfigFile(['loan', 'property'])
            ->hasViews()
            ->hasMigration('create_loan_table')
            ->hasCommand(LoanCommand::class);
    }
}
