<?php

namespace Homeful\Loan;

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

    /**
     * @param Borrower $borrower
     * @return $this
     */
    public function setBorrower(Borrower $borrower): self
    {
        $this->borrower = $borrower;

        return $this;
    }

    /**
     * @return Borrower
     */
    public function getBorrower(): Borrower
    {
        return $this->borrower;
    }

    /**
     * @param Property $property
     * @return $this
     */
    public function setProperty(Property $property): self
    {
        $this->property = $property;

        return $this;
    }

    /**
     * @return Property
     */
    public function getProperty(): Property
    {
        return $this->property;
    }

    /**
     * @param Price $value
     * @return $this
     */
    public function setLoanAmount(Price $value): self
    {
        $this->loan_amount = $value;

        return $this;
    }

    /**
     * @return Price
     */
    public function getLoanAmount(): Price
    {
        return $this->loan_amount;
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
     * @return float
     * @throws \Brick\Math\Exception\MathException
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function getAnnualInterestRate(): float
    {
        return match(true) {
            $this->getProperty()->getTotalContractPrice()->inclusive()->compareTo(750000) <= 0 => ($this->getBorrower()->getRegional()
                ? ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(12000) <= 0 ? 3/100 : 6.25/100)
                : ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(14500) <= 0 ? 3/100 : 6.25/100)),
            $this->getProperty()->getTotalContractPrice()->inclusive()->compareTo(800000) <= 0 => ($this->getBorrower()->getRegional()
                ? ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(13000) <= 0 ? 3/100 : 6.25/100)
                : ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(15500) <= 0 ? 3/100 : 6.25/100)),
            $this->getProperty()->getTotalContractPrice()->inclusive()->compareTo(850000) <= 0 => ($this->getBorrower()->getRegional()
                ? ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(15000) <= 0 ? 3/100 : 6.25/100)
                : ($this->getBorrower()->getGrossMonthlyIncome()->inclusive()->compareTo(16500) <= 0 ? 3/100 : 6.25/100)),
            default => 6.25/100,
        };
    }

    /**
     * @return float
     * @throws \Brick\Math\Exception\MathException
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public function getMonthlyInterestRate(): float
    {
        return $this->getAnnualInterestRate()/12;
    }

    /**
     * @return Price
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
}
