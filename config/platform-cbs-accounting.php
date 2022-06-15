<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Report Builder Controllers
    |--------------------------------------------------------------------------
    |
    | Following controllers from Report Builder can be overridden
    |
    */
    'controllers' => [
        'account'                   => App\Admin\Controllers\CbsAccounting\CBSAccountController::class,                 // config('platform-cbs-accounting.controllers.account')
        'accounting_transaction'    => App\Admin\Controllers\CbsAccounting\CBSAccountingTransactionController::class,   // config('platform-cbs-accounting.controllers.accounting_transaction')
        'general_ledger_account'    => App\Admin\Controllers\CbsAccounting\CBSGeneralLedgerAccountController::class,    // config('platform-cbs-accounting.controllers.general_ledger_account')
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Builder Models
    |--------------------------------------------------------------------------
    |
    | Following models from Report Builder can be overridden
    |
    */
    'models' => [
        'account'                       => App\Models\CbsAccounting\CBSAccount::class,                      // config('platform-cbs-accounting.models.account')
        'accounting_transaction'        => App\Models\CbsAccounting\CBSAccountingTransaction::class,        // config('platform-cbs-accounting.models.accounting_transaction')
        'accounting_transaction_detail' => App\Models\CbsAccounting\CBSAccountingTransactionDetail::class,  // config('platform-cbs-accounting.models.accounting_transaction_detail')
        'general_ledger_account'        => App\Models\CbsAccounting\CBSGeneralLedgerAccount::class,         // config('platform-cbs-accounting.models.general_ledger_account')
    ]
];
