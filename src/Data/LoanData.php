<?php

namespace Homeful\Loan\Data;

use Homeful\Borrower\Data\BorrowerData;
use Homeful\Property\Data\PropertyData;
use Spatie\LaravelData\Data;
use Homeful\Loan\Loan;

class LoanData extends Data
{
    public function __construct(
        public float $loan_amount,
        public int $months_to_pay,
        public float $annual_interest,
        public float $monthly_amortization,
        public float $equity,
        public float $equity_requirement_amount,
        public BorrowerData $borrower,
        public PropertyData $property,
    ) {}

    public static function fromObject(Loan $loan): self
    {
        return new self(
            loan_amount: $loan->getLoanAmount()->inclusive()->getAmount()->toFloat(),
            months_to_pay: $loan->getMaximumMonthsToPay(),
            annual_interest: $loan->getAnnualInterestRate(),
            monthly_amortization: $loan->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat(),
            equity: $loan->getEquity()->inclusive()->getAmount()->toFloat(),
            equity_requirement_amount: $loan->getEquityRequirementAmount()->inclusive()->getAmount()->toFloat(),
            borrower: BorrowerData::fromObject($loan->getBorrower()),
            property: PropertyData::fromObject($loan->getProperty())
        );
    }
}
