<?php

namespace Homeful\Loan\Data;

use Homeful\Borrower\Data\BorrowerData;
use Homeful\Loan\Loan;
use Homeful\Property\Data\PropertyData;
use Spatie\LaravelData\Data;

class LoanData extends Data
{
    public function __construct(
        public float $loan_amount,
        public int $months_to_pay,
        public float $annual_interest,
        public float $monthly_amortization,
        public BorrowerData $borrower,
        public PropertyData $property,
    ) {
    }

    public static function fromObject(Loan $loan): self
    {
        return new self(
            loan_amount: $loan->getLoanAmount()->inclusive()->getAmount()->toFloat(),
            months_to_pay: $loan->getMaximumMonthsToPay(),
            annual_interest: $loan->getAnnualInterestRate(),
            monthly_amortization: $loan->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat(),
            borrower: BorrowerData::fromObject($loan->getBorrower()),
            property: PropertyData::fromObject($loan->getProperty())
        );
    }
}
