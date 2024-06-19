<?php

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Homeful\Borrower\Borrower;
use Homeful\Equity\Equity;
use Homeful\Loan\Data\LoanData;
use Homeful\Loan\Exceptions\LoanExceedsLoanableValueException;
use Homeful\Loan\Loan;
use Homeful\Property\Property;
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;

dataset('borrower', function () {
    return [
        fn () => (new Borrower)->setRegional(false)->addWages(14500)->setBirthdate(Carbon::parse('1999-03-17')),
    ];
});

dataset('property', function () {
    return [
        fn () => (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP'))),
    ];
});

it('has borrower with different maximum months to pay depending on the co-borrowers age', function (Borrower $borrower, Property $property) {
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getMaximumMonthsToPay())->toBe(Loan::MAXIMUM_MONTHS_TO_PAY_LOAN); //360
    $co_borrower1 = (new Borrower)->setRegional(false)->addWages(14000)->setBirthdate(Carbon::parse('1966-03-17'));
    $borrower->addCoBorrower($co_borrower1);
    expect($loan->getMaximumMonthsToPay())->toBe(140);
})->with('borrower', 'property');

it('has default interest rate', function () {
    $loan = new Loan;

    /** NCR, TCP <= 750k, GMI <= 14,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(14500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3 / 100);

    /** NCR, TCP <= 750k, GMI > 14,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(14501);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    /** NCR, 750k < TCP <= 800k GMI <= 15,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(15500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(800000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3 / 100);

    /** NCR, 750k < TCP <= 800k GMI > 14,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(15501);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(800000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    /** NCR, 800k < TCP <= 850k GMI <= 16,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(16500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3 / 100);

    /** NCR, 800k < TCP <= 850k GMI > 16,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(16501);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    /** NCR, 850k < TCP */
    $borrower = (new Borrower)->setRegional(false)->addWages(14500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    $borrower = (new Borrower)->setRegional(false)->addWages(15500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    $borrower = (new Borrower)->setRegional(false)->addWages(16500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    /** Regional, TCP <= 750k, GMI <= 12,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(12000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3 / 100);

    /** Regional, TCP <= 750k, GMI > 12,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(12001);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    /** Regional, 750k < TCP <= 800k GMI <= 13,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(13000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(800000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3 / 100);

    /** Regional, 750k < TCP <= 800k GMI > 13,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(13001);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(800000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    /** Regional, 800k < TCP <= 850k GMI <= 15,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(15000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3 / 100);

    /** Regional, 800k < TCP <= 850k GMI > 15,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(15001);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    /** Regional, 850k < TCP */
    $borrower = (new Borrower)->setRegional(true)->addWages(12000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    $borrower = (new Borrower)->setRegional(true)->addWages(13000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);

    $borrower = (new Borrower)->setRegional(true)->addWages(15000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25 / 100);
});

it('has default monthly amortization payment depending on loan amount', function (Borrower $borrower, Property $property) {
    $loan = new Loan;
    $property->setAppraisedValue(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    $loanable_value = $property->getLoanableValue()->inclusive()->getAmount()->toFloat();
    expect($loanable_value)->toBe(807500.95);
    expect($loan->setLoanAmount(new Price(Money::of($loanable_value, 'PHP')))->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe(4972.0);
    expect($loan->setLoanAmount(new Price(Money::of($loanable_value - 10000, 'PHP')))->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe(4910.0);
    expect($loan->setLoanAmount(new Price(Money::of($loanable_value - 20000, 'PHP')))->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe(4849.0);
    expect($loan->setLoanAmount(new Price(Money::of($loanable_value - 30000, 'PHP')))->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe(4787.0);
})->with('borrower', 'property');

it('has loan data', function (Borrower $borrower, Property $property) {
    $loan = new Loan;
    $property->setAppraisedValue(new Price(Money::of(850000, 'PHP')));
    $loanable_value = $property->getLoanableValue()->inclusive()->getAmount()->toFloat();
    expect($loanable_value)->toBe(807500.0);
    $loan->setBorrower($borrower)->setProperty($property)
        ->setLoanAmount(new Price(Money::of($loanable_value, 'PHP')));
    $data = LoanData::fromObject($loan);
    expect($data->loan_amount)->toBe($loan->getLoanAmount()->inclusive()->getAmount()->toFloat());
    expect($data->months_to_pay)->toBe($loan->getMaximumMonthsToPay());
    expect($data->annual_interest)->toBe($loan->getAnnualInterestRate());
    expect($data->joint_disposable_monthly_income)->toBe($loan->getJointDisposableMonthlyIncome()->inclusive()->getAmount()->toFloat());
//    expect($data->equity)->toBe($loan->getEquity()->inclusive()->getAmount()->toFloat());
    expect($data->equity_requirement_amount)->toBe($loan->getEquityRequirementAmount()->inclusive()->getAmount()->toFloat());
    expect($data->is_income_sufficient)->toBe($loan->getIsIncomeSufficient());
    expect($data->monthly_amortization)->toBe($loan->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat());
    expect($data->borrower->gross_monthly_income)->toBe($loan->getBorrower()->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat());
    expect($data->borrower->regional)->toBe($loan->getBorrower()->getRegional());
    expect($data->borrower->birthdate)->toBe($loan->getBorrower()->getBirthdate()->format('Y-m-d'));
    expect($data->property->market_segment)->toBe($loan->getProperty()->getMarketSegment()->getName());
    expect($data->property->total_contract_price)->toBe($property->getTotalContractPrice()->inclusive()->getAmount()->toFloat());
    expect($data->property->appraised_value)->toBe($property->getAppraisedValue()->inclusive()->getAmount()->toFloat());
    expect($data->property->default_loanable_value_multiplier)->toBe($property->getDefaultLoanableValueMultiplier());
    expect($data->property->loanable_value_multiplier)->toBe($property->getLoanableValueMultiplier());
    expect($data->property->loanable_value)->toBe($property->getLoanableValue()->inclusive()->getAmount()->toFloat());
    expect($data->property->disposable_income_requirement_multiplier)->toBe($property->getDefaultDisposableIncomeRequirementMultiplier());
    expect($data->property->default_disposable_income_requirement_multiplier)->toBe($property->getDefaultDisposableIncomeRequirementMultiplier());
    $amount = $loan->getEquityRequirementAmount()->inclusive()->getAmount()->toFloat();
    $loan->setDownPayment($amount);
    $data = LoanData::fromObject($loan);
    expect($data->down_payment->amount)->toBe($amount);
    expect($data->down_payment->interest_rate)->toBe(0.0);
    expect($data->down_payment->months_to_pay)->toBe(12);
    expect($data->down_payment->monthly_amortization)->toBe($amount / 12);
})->with('borrower', 'property');

it('has loan amount that should be less than the loanable amount', function (Borrower $borrower) {
    $property = (new Property)
        ->setTotalContractPrice(new Price(Money::of(850000, 'PHP')))
        ->setAppraisedValue(new Price(Money::of(800000, 'PHP')));
    $loanable_value = $property->getLoanableValue()->inclusive()->getAmount()->toFloat();
    expect($loanable_value)->toBe(800000.0);
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property);
    $loan->setLoanAmount(new Price(Money::of($loanable_value + 1, 'PHP')));

})->with('borrower')->expectException(LoanExceedsLoanableValueException::class);

it('has a default equity monthly amortization', function () {
    $loan = new Loan;

    /** NCR, TCP <= 750k, GMI <= 14,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(14500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')))->setAppraisedValue(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property)->setLoanAmount(new Price(Money::of(750000, 'PHP')));

    expect($loan->getEquity()->inclusive()->compareTo(0))->toBe(0);
    expect($loan->getEquityMonthlyAmortizationAmount()->inclusive()->compareTo(0))->toBe(0);
    expect($loan->getEquityMonthsToPay())->toBe(0);
});

it('has computed equity monthly amortization', function () {
    $borrower = (new Borrower)
        ->setRegional(false)
        ->setBirthdate(Carbon::now()->addYears(-40))
        ->addWages(9000);
    $property = (new Property)
        ->setTotalContractPrice(new Price(Money::of(850000, 'PHP')))
        ->setAppraisedValue(new Price(Money::of(800000, 'PHP')));
    $loanable_value = $property->getLoanableValue()->inclusive()->getAmount()->toFloat();
    expect($loanable_value)->toBe(800000.0);
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property)->setLoanAmount(new Price(Money::of($loanable_value, 'PHP')));
    expect($loan->getEquityRequirementAmount()->inclusive()->compareTo(50000))->toBe(0);
    expect($loan->getEquityMonthsToPay())->toBe(12);
    expect($loan->getEquityMonthlyAmortizationAmount()->inclusive()->compareTo(BigDecimal::of(50000.0)->dividedBy(12, 2, RoundingMode::CEILING)))->toBe(0);
});

it('may add equity', function () {
    $borrower = (new Borrower)
        ->setRegional(false)
        ->setBirthdate(Carbon::now()->addYears(-40))
        ->addWages(9000);
    $property = (new Property)
        ->setTotalContractPrice(new Price(Money::of(850000, 'PHP')))
        ->setAppraisedValue(new Price(Money::of(800000, 'PHP')));
    expect($property->getLoanableValueMultiplier())->toBe(1.0);
    $loanable_value = $property->getLoanableValue()->inclusive()->getAmount()->toFloat();
    expect($loanable_value)->toBe(800000.0);
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property)->setLoanAmount(new Price(Money::of($loanable_value, 'PHP')));
    expect($loan->getEquityRequirementAmount()->inclusive()->getAmount()->toFloat())->toBe(50000.0);
    //    $loan->setEquity(new Price(Money::of(50000.0, 'PHP')));
    //    expect($loan->getEquityRequirementAmount()->inclusive()->getAmount()->toFloat())->toBe(0.0);
});

it('can have income insufficiency', function () {
    $borrower = (new Borrower)
        ->setRegional(false)
        ->setBirthdate(Carbon::now()->addYears(-40))
        ->addWages(70000);
    $property = (new Property)
        ->setTotalContractPrice(new Price(Money::of(850000, 'PHP')))
        ->setAppraisedValue(new Price(Money::of(800000, 'PHP')));
    $loanable_value = $property->getLoanableValue()->inclusive()->getAmount()->toFloat();
    expect($loanable_value)->toBe(800000.0);
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property)->setLoanAmount(new Price(Money::of($loanable_value, 'PHP')));
    expect($borrower->getJointDisposableMonthlyIncome($property)->inclusive()->getAmount()->toFloat())->toBe(24500.0);
    expect($loan->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe(4926.0);

    $borrower = (new Borrower)
        ->setRegional(false)
        ->setBirthdate(Carbon::now()->addYears(-40))
        ->addWages(9000);
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property)->setLoanAmount(new Price(Money::of($loanable_value, 'PHP')));
    $loanable_value = $property->getLoanableValue()->inclusive()->getAmount()->toFloat();
    expect($loanable_value)->toBe(800000.0);
    expect($borrower->getJointDisposableMonthlyIncome($property)->inclusive()->getAmount()->toFloat())->toBe(3150.0);
    expect($loan->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe(3373.0);
    expect($loan->getIsIncomeSufficient())->toBe(false);

    expect($loan->getEquityRequirementAmount()->inclusive()->compareTo(50000.0))->toBe(0);
    expect($loan->getEquityMonthsToPay())->toBe(12);
    expect($loan->getEquityMonthlyAmortizationAmount()->inclusive()->compareTo(BigDecimal::of(50000.0)->dividedBy(12, 2, RoundingMode::CEILING)))->toBe(0);
    $loan->setEquityMonthsToPay(24);
    expect($loan->getEquityMonthlyAmortizationAmount()->inclusive()->compareTo(BigDecimal::of(50000.0)->dividedBy(24, 2, RoundingMode::CEILING)))->toBe(0);
});

it('has configurable down payment', function () {
    $borrower = (new Borrower)
        ->setRegional(false)
        ->setBirthdate(Carbon::now()->addYears(-40))
        ->addWages(9000);
    $property = (new Property)
        ->setTotalContractPrice(new Price(Money::of(850000, 'PHP')))
        ->setAppraisedValue(new Price(Money::of(790000, 'PHP')));
    expect($property->getLoanableValueMultiplier())->toBe(1.0);
    $loanable_value = $property->getLoanableValue()->inclusive()->getAmount()->toFloat();
    expect($loanable_value)->toBe(790000.0);
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property)->setLoanAmount(new Price(Money::of($loanable_value, 'PHP')));
    expect($loan->getEquityRequirementAmount()->inclusive()->getAmount()->toFloat())->toBe(60000.0);

    $loan->setDownPayment((new Equity)->setAmount($loan->getEquityRequirementAmount()));
    expect($loan->getDownPayment()->getAmount()->inclusive()->getAmount()->compareTo(60000))->toBe(0);
    expect($loan->getDownPayment()->getAnnualInterestRate())->toBe(0.0);
    expect($loan->getDownPayment()->getMonthsToPay())->toBe(12);
    expect($loan->getDownPayment()->getMonthlyAmortization()->inclusive()->compareTo(5000))->toBe(0);
    $loan->getDownPayment()->setMonthsToPay(24);
    expect($loan->getDownPayment()->getAmount()->inclusive()->getAmount()->compareTo(60000))->toBe(0);
    expect($loan->getDownPayment()->getAnnualInterestRate())->toBe(0.0);
    expect($loan->getDownPayment()->getMonthsToPay())->toBe(24);
    expect($loan->getDownPayment()->getMonthlyAmortization()->inclusive()->compareTo(2500))->toBe(0);
});
