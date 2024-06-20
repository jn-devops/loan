<?php

namespace Homeful\Loan\Data;

use Brick\Math\Exception\MathException;
use Brick\Math\Exception\NumberFormatException;
use Brick\Math\Exception\RoundingNecessaryException;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Exception\UnknownCurrencyException;
use Homeful\Borrower\Data\BorrowerData;
use Homeful\Equity\Data\EquityData;
use Homeful\Loan\Loan;
use Homeful\Property\Data\PropertyData;
use Spatie\LaravelData\Data;

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
        public float $total_contract_price,
        public float $percent_miscellaneous_fees,
        public float $miscellaneous_fees,
        public float $net_total_contract_price,
        public float $percent_down_payment,
        public float $total_contract_price_down_payment,
        public float $miscellaneous_fees_down_payment,
        public float $holding_fee,
        public BorrowerData $borrower,
        public PropertyData $property,
        public EquityData $balance_down_payment,
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
            total_contract_price: $loan->getTotalContractPrice()->inclusive()->getAmount()->toFloat(),
            percent_miscellaneous_fees: $loan->getPercentMiscellaneousFees(),
            miscellaneous_fees: $loan->getMiscellaneousFees()->inclusive()->getAmount()->toFloat(),
            net_total_contract_price: $loan->getNetTotalContractPrice()->inclusive()->getAmount()->toFloat(),
            percent_down_payment: $loan->getPercentDownPayment(),
            total_contract_price_down_payment: $loan->getTotalContractPriceDownPayment()->inclusive()->getAmount()->toFloat(),
            miscellaneous_fees_down_payment: $loan->getMiscellaneousFeesDownPayment()->inclusive()->getAmount()->toFloat(),
            holding_fee: $loan->getHoldingFee()->inclusive()->getAmount()->toFloat(),
            borrower: BorrowerData::fromObject($loan->getBorrower()),
            property: PropertyData::fromObject($loan->getProperty()),
            balance_down_payment: EquityData::fromObject($loan->getBalanceDownPayment()),
            down_payment: EquityData::fromObject($loan->getDownPayment()),
        );
    }
}
