<?php

namespace Homeful\Loan\Data;

use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\UnknownCurrencyException;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\MathException;
use Homeful\Borrower\Data\BorrowerData;
use Homeful\Property\Data\PropertyData;
use Homeful\Equity\Data\EquityData;
use Spatie\LaravelData\Data;
use Homeful\Loan\Loan;

class LoanData extends Data
{
    public function __construct(
        public float $loan_amount,
        public int $months_to_pay,
        public float $annual_interest,
        public float $joint_disposable_monthly_income,
        public float $monthly_amortization,
        public float $equity_requirement_amount,
        public bool $is_income_sufficient,
        public BorrowerData $borrower,
        public PropertyData $property,
        public EquityData $down_payment
    ) {}

    /**
     * @throws MathException
     * @throws MoneyMismatchException
     * @throws NumberFormatException
     * @throws RoundingNecessaryException
     * @throws UnknownCurrencyException
     */
    public static function fromObject(Loan $loan): self
    {
        return new self(
            loan_amount: $loan->getLoanAmount()->inclusive()->getAmount()->toFloat(),
            months_to_pay: $loan->getMaximumMonthsToPay(),
            annual_interest: $loan->getAnnualInterestRate(),
            joint_disposable_monthly_income: $loan->getJointDisposableMonthlyIncome()->inclusive()->getAmount()->toFloat(),
            monthly_amortization: $loan->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat(),
            equity_requirement_amount: $loan->getEquityRequirementAmount()->inclusive()->getAmount()->toFloat(),
            is_income_sufficient: $loan->getIsIncomeSufficient(),
            borrower: BorrowerData::fromObject($loan->getBorrower()),
            property: PropertyData::fromObject($loan->getProperty()),
            down_payment: EquityData::fromObject($loan->getDownPayment()),
        );
    }
}
