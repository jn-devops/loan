<?php

use Homeful\Loan\Data\LoanData;
use Homeful\Property\Property;
use Homeful\Borrower\Borrower;
use Illuminate\Support\Carbon;
use Whitecube\Price\Price;
use Brick\Money\Money;
use Homeful\Loan\Loan;

dataset('borrower', function() {
    return [
        fn () => (new Borrower)->setRegional(false)->addWages(14500)->setBirthdate(Carbon::parse('1999-03-17'))
    ];
});

dataset('property', function() {
    return [
        fn () => (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')))
    ];
});

it('has borrower with different maximum months to pay depending on the co-borrowers age', function (Borrower $borrower, Property $property) {
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getMaximumMonthsToPay())->toBe(Loan::MAXIMUM_MONTHS_TO_PAY_LOAN); //360
    $co_borrower1 = (new Borrower)->setRegional(false)->addWages(14000)->setBirthdate(Carbon::parse('1966-03-17'));
    $borrower->addCoBorrower($co_borrower1);
    expect($loan->getMaximumMonthsToPay())->toBe(141);
})->with('borrower', 'property');

it('has default interest rate', function () {
    $loan = new Loan;

    /** NCR, TCP <= 750k, GMI <= 14,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(14500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3/100);

    /** NCR, TCP <= 750k, GMI > 14,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(14501);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    /** NCR, 750k < TCP <= 800k GMI <= 15,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(15500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(800000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3/100);

    /** NCR, 750k < TCP <= 800k GMI > 14,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(15501);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(800000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    /** NCR, 800k < TCP <= 850k GMI <= 16,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(16500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3/100);

    /** NCR, 800k < TCP <= 850k GMI > 16,500 */
    $borrower = (new Borrower)->setRegional(false)->addWages(16501);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    /** NCR, 850k < TCP */
    $borrower = (new Borrower)->setRegional(false)->addWages(14500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    $borrower = (new Borrower)->setRegional(false)->addWages(15500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    $borrower = (new Borrower)->setRegional(false)->addWages(16500);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    /** Regional, TCP <= 750k, GMI <= 12,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(12000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3/100);

    /** Regional, TCP <= 750k, GMI > 12,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(12001);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(750000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    /** Regional, 750k < TCP <= 800k GMI <= 13,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(13000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(800000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3/100);

    /** Regional, 750k < TCP <= 800k GMI > 13,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(13001);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(800000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    /** Regional, 800k < TCP <= 850k GMI <= 15,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(15000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(3/100);

    /** Regional, 800k < TCP <= 850k GMI > 15,000 */
    $borrower = (new Borrower)->setRegional(true)->addWages(15001);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    /** Regional, 850k < TCP */
    $borrower = (new Borrower)->setRegional(true)->addWages(12000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    $borrower = (new Borrower)->setRegional(true)->addWages(13000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);

    $borrower = (new Borrower)->setRegional(true)->addWages(15000);
    $property = (new Property)->setTotalContractPrice(new Price(Money::of(850001, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->getAnnualInterestRate())->toBe(6.25/100);
});

it('has default monthly amortization payment depending on loan amount', function (Borrower $borrower, Property $property) {
    $loan = new Loan;
    $loan->setBorrower($borrower)->setProperty($property);
    expect($loan->setLoanAmount(new Price(Money::of(850001, 'PHP')))->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe(5234.0);
    expect($loan->setLoanAmount(new Price(Money::of(850000, 'PHP')))->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe(5234.0);
    expect($loan->setLoanAmount(new Price(Money::of(849000, 'PHP')))->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe( 5227.0);
    expect($loan->setLoanAmount(new Price(Money::of(840000, 'PHP')))->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat())->toBe( 5172.0);
})->with('borrower', 'property');

it('has loan data', function (Borrower $borrower, Property $property) {
    $loan = new Loan;
    $property->setAppraisedValue(new Price(Money::of(850000, 'PHP')));
    $loan->setBorrower($borrower)->setProperty($property)->setLoanAmount(new Price(Money::of(850001, 'PHP')));
    $data = LoanData::fromObject($loan);
    expect($data->loan_amount)->toBe($loan->getLoanAmount()->inclusive()->getAmount()->toFloat());
    expect($data->months_to_pay)->toBe($loan->getMaximumMonthsToPay());
    expect($data->annual_interest)->toBe($loan->getAnnualInterestRate());
    expect($data->monthly_amortization)->toBe($loan->getMonthlyAmortizationAmount()->inclusive()->getAmount()->toFloat());
    expect($data->borrower->gross_monthly_income)->toBe($loan->getBorrower()->getGrossMonthlyIncome()->inclusive()->getAmount()->toFloat());
    expect($data->borrower->regional)->toBe($loan->getBorrower()->getRegional());
    expect($data->borrower->birthdate)->toBe($loan->getBorrower()->getBirthdate()->format('Y-m-d'));
    expect($data->property->market_segment)->toBe($loan->getProperty()->getMarketSegment()->value);
    expect($data->property->total_contract_price)->toBe($property->getTotalContractPrice()->inclusive()->getAmount()->toFloat());
    expect($data->property->appraised_value)->toBe($property->getAppraisedValue()->inclusive()->getAmount()->toFloat());
    expect($data->property->default_loanable_value_multiplier)->toBe($property->getDefaultLoanableValueMultiplier());
    expect($data->property->loanable_value_multiplier)->toBe($property->getLoanableValueMultiplier());
    expect($data->property->loanable_value)->toBe($property->getLoanableValue()->inclusive()->getAmount()->toFloat());
    expect($data->property->disposable_income_requirement_multiplier)->toBe($property->getDefaultDisposableIncomeRequirementMultiplier());
    expect($data->property->default_disposable_income_requirement_multiplier)->toBe($property->getDefaultDisposableIncomeRequirementMultiplier());
})->with('borrower', 'property');


