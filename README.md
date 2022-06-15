# Mobile Wallet Backend

A Laravel backend for a mobile money / e-wallet product: staged KYC
registration, OTP-verified actions, wallet-to-wallet and bank transfers, and a
multi-level transaction limit engine (per activity type, per user, per
frequency) with automatic daily/monthly/yearly resets — all over JWE-encrypted
request payloads.

This is a **generalised reference implementation** of a production system. The
real folder structure, limit engine, KYC flow, encryption layer and audit
design are preserved; employer, brand and market names are replaced with
neutral ones, and the database dump, encryption keys and every credential are
removed.

## What it demonstrates

- **A layered transaction limit engine** — limits exist at three levels:
  system defaults per activity type (`EWLimitConfig`), per-user overrides
  (`EWUserLimitConfig`), and per-user per-activity caps
  (`EWUserActivityLimit`), each scoped by an `EWFrequency` (daily / monthly /
  yearly). `EWLimitConfigService` resolves the effective limit for a given
  user and activity. Regulated wallets are required to enforce exactly this
  shape, and hardcoding a single global cap makes both compliance and
  per-customer risk tiers impossible.
- **Limit resets are scheduled commands, not computed windows** —
  `DailyUserLimitReset`, `MonthlyUserLimitReset` and `YearlyUserLimitReset`
  each reset their own frequency band. Deriving "how much has this user spent
  today" from a rolling query over transaction history is correct but
  expensive on every single transaction; a maintained counter with a scheduled
  reset keeps the authorisation path fast.
- **Every limit change is logged** — `EWLimitConfigLog`,
  `EWUserLimitConfigLog`, `EWUserConfigLog`, `EWUserActivityLimitLog`,
  `EWConfigLog`, `EWTrxActivityTypeLog`. In a financial product "who raised
  this customer's daily limit, when, and to what" is an audit question, not a
  debugging one, so the log is a table rather than a log file.
- **JWE-encrypted payloads with rotatable keys** — `EncryptionService` uses
  `web-token/jwt-*` with `A128CBC-HS256`. `config/encryption.php` maps
  `APP_ENV` to a key id and `config/keystore.php` holds the keys, so rotation
  is: add the new key, repoint the kid, retire the old one — with no downtime
  and no environment able to decrypt another's traffic.
- **OTP with usage accounting** — `EWOTPHistory` records issuance,
  `EWOTPUsageLog` records consumption. Separating the two is what makes replay
  and brute-force attempts visible rather than merely blocked.
- **KYC as explicit steps** — `EWUserRegistrationStep` plus
  `EWUserDetail` / `EWUserDetailChild` and a pending-detail review path, so a
  partially onboarded user is a real state with a known position, not a row
  with nulls in it.
- **Bank transfer as its own request lifecycle** —
  `EWBankTransferRequest` / `EWBankTrxRequest`, kept apart from in-wallet
  transfers because an external rail settles asynchronously and fails
  differently.
- **Transaction history with typed activities** — `EWTrxHistory` +
  `EWTrxActivityType`, so limits, reporting and fees all key off the same
  enumerated activity rather than a free-text description.
- **Queued transaction notifications** — `EWSendTransactionNotification`,
  `EWTransactionCount`: no customer-facing message sits on the transaction
  path.
- **Admin back office** — `app/Admin/Controllers/EW*` for limits, activity
  types, OTP history, user details, registration steps, countries and cities,
  each with a matching log view.

## Structure

```
app/
  Services/
    EncryptionService.php        JWE encrypt/decrypt, key selection by kid
    EWLimitConfigService.php     resolves the effective limit for user+activity
    TransactionApiService.php    outbound transaction calls
  Repositories/
    EWalletHandler.php           wallet operations
    EWalletLimitConfig.php       limit lookup/enforcement
    TransactionHandler.php       transaction lifecycle
    TokenHandler.php             auth tokens
    SMSHandler.php · SierraSMSGateway.php · NotificationHandler.php
    CoreHandler.php              balance service client
  Console/Commands/
    DailyUserLimitReset.php · MonthlyUserLimitReset.php · YearlyUserLimitReset.php
  Jobs/
    EWSendTransactionNotification.php · EWTransactionCount.php
  Models/
    EWTrxHistory · EWTrxActivityType(+Log)
    EWLimitConfig(+Log) · EWUserLimitConfig(+Log) · EWUserActivityLimit(+Log)
    EWUserConfig(+Log) · EWConfigLog · EWFrequency
    EWUserDetail(+Child) · EWUserRegistrationStep
    EWOTPHistory · EWOTPUsageLog · EWSMSHistory
    EWBankTransferRequest · EWBankTrxRequest
    EWCountry · EWCity
    OAuth2Client · CustomPermission(+Detail) · HttpLog
  Http/Controllers/              API + WebhookController
  Admin/Controllers/EW*          back office
config/
  encryption.php                 APP_ENV -> key id
  keystore.php.example           key material template (real file gitignored)
database/migrations|seeders
```

## Running

```bash
cp .env.example .env
cp config/keystore.php.example config/keystore.php   # then generate real keys
composer install
php artisan migrate
php artisan schedule:work        # drives the limit resets
php artisan queue:work
```

Ships without `vendor/`, without any database dump, without encryption keys,
and with every credential read from the environment.
