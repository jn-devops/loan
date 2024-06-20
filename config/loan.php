<?php

// config for Homeful/Loan2
return [
    'percent_miscellaneous_fees' => env('PERCENT_MISCELLANEOUS_FEES', 8.5/100),
    'percent_down_payment' => env('PERCENT_DOWN_PAYMENT', 5/100),
    'down_payment_term' => env('DOWN_TERM', 12),
];
