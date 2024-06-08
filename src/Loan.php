<?php

namespace Homeful\Loan;

use Homeful\Loan\Exceptions\LoanExceedsLoanableValueException;
use Homeful\Borrower\Borrower;
use Homeful\Property\Property;
use Illuminate\Support\Carbon;
use Jarouche\Financial\PMT;
use Whitecube\Price\Price;
use Brick\Money\Money;

class Loan
{
    const MAXIMUM_AGE_AT_LOAN_MATURITY = 70;

    const MAXIMUM_MONTHS_TO_PAY_LOAN = 360;

    protected Borrower $borrower;

    protected Property $property;

    protected Price $loan_amount;

    protected Price $equity;

    /**
     * @return $this
     */
    public function setBorrower(Borrower $borrower): self
    {
        $this->borrower = $borrower;

        return $this;
    }

    public function getBorrower(): Borrower
    {
        return $this->borrower;
    }

    /**
     * @return $this
     */
    public function setProperty(Property $property): self
    {
        $this->property = $property;

        return $this;
    }

    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * @return $this
     * @throws LoanExceedsLoanableValueException
     */
    public function setLoanAmount(Price $value): self
    {
        if ($value->compareTo($this->getProperty()->getLoanableValue()) > 0)
            throw new LoanExceedsLoanableValueException;

        $this->loan_amount = $value;

        return $this;
    }

    public function getLoanAmount(): Price
    {
        return $this->loan_amount;
    }

    /**
     * @param Price|float $value
     * @return $this
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function setEquity(Price|float $value): self
    {
        $this->equity = $value instanceof Price
            ? $value
            : new Price(Money::of($value, 'PHP'));

        return $this;
    }

    /**
     * @return Price
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function getEquity(): Price
    {
        return $this->equity ?? new Price(Money::of(0, 'PHP'));
    }

    /**
     * @return int
     */
    public function getMaximumMonthsToPay(): int
    {
        $date_at_maximum_loan_maturity = $this->borrower->getOldestAmongst()->getBirthdate()->copy()
            ->addYears(self::MAXIMUM_AGE_AT_LOAN_MATURITY);

        return min(abs($date_at_maximum_loan_maturity->diffInMonths(Carbon::today())), self::MAXIMUM_MONTHS_TO_PAY_LOAN);
    }

    /**
     * Manually encoded matrix
     *
     * @throws \Brick\Math\Exception\MathException
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function getAnnualInterestRate(): float
    {
        return match (true) {
            $this->getProperty()->getTotalContractPrice()->inclusive()->compareTo(750000) <= 0 => ($this->getBorrower()->getRegional()
                ? ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(12000) <= 0 ? 3 / 100 : 6.25 / 100)
                : ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(14500) <= 0 ? 3 / 100 : 6.25 / 100)),
            $this->getProperty()->getTotalContractPrice()->inclusive()->compareTo(800000) <= 0 => ($this->getBorrower()->getRegional()
                ? ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(13000) <= 0 ? 3 / 100 : 6.25 / 100)
                : ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(15500) <= 0 ? 3 / 100 : 6.25 / 100)),
            $this->getProperty()->getTotalContractPrice()->inclusive()->compareTo(850000) <= 0 => ($this->getBorrower()->getRegional()
                ? ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(15000) <= 0 ? 3 / 100 : 6.25 / 100)
                : ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(16500) <= 0 ? 3 / 100 : 6.25 / 100)),
            default => 6.25 / 100,
        };
    }

    /**
     * @throws \Brick\Math\Exception\MathException
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function getMonthlyInterestRate(): float
    {
        return $this->getAnnualInterestRate() / 12;
    }

    /**
     * @throws \Brick\Math\Exception\MathException
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\MoneyMismatchException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     * @throws \Exception
     */
    public function getMonthlyAmortizationAmount(): Price
    {
        $obj = new PMT($this->getMonthlyInterestRate(), $this->getMaximumMonthsToPay(), $this->getLoanAmount()->inclusive()->getAmount()->toFloat());
        $float = round($obj->evaluate());

        return new Price(Money::of((int) $float, 'PHP'));
    }

    /**
     * @return Price
     * @throws \Brick\Math\Exception\NumberFormatException
     * @throws \Brick\Math\Exception\RoundingNecessaryException
     * @throws \Brick\Money\Exception\UnknownCurrencyException
     */
    public function getEquityRequirementAmount(): Price
    {
        $equity = $this->property->getTotalContractPrice()->inclusive()
            ->minus($this->getLoanAmount()->inclusive())
            ->minus($this->getEquity()->inclusive())
        ;

        return new Price($equity);
    }
}
