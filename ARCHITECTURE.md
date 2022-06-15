# Architecture

## Component diagram

```
   client app
       │  POST { payload: <JWE compact serialization> }
       ▼
   EncryptionService.decrypt()
       │  config/encryption.php: APP_ENV → kid
       │  config/keystore.php:  kid  → symmetric key (A128CBC-HS256)
       ▼
   TokenHandler (auth)  →  Http/Controllers
       │
       ▼
   TransactionHandler
       │
       ├──▶ EWLimitConfigService.resolve(user, activityType, frequency)
       │        │
       │        ├─ EWUserActivityLimit   (this user, this activity)   ← most specific
       │        ├─ EWUserLimitConfig     (this user, any activity)
       │        └─ EWLimitConfig         (system default per activity) ← fallback
       │        scoped by EWFrequency (daily · monthly · yearly)
       │
       ├─ over limit? → reject, nothing written
       │
       ├──▶ EWalletHandler / CoreHandler   (balance movement)
       ├──▶ EWTrxHistory                    (typed by EWTrxActivityType)
       └──▶ EWSendTransactionNotification   (queued)

   OTP-guarded actions:
       EWOTPHistory (issued)  →  EWOTPUsageLog (consumed)  →  SMSHandler

   external rail:
       EWBankTransferRequest / EWBankTrxRequest  (async settlement, own states)

   scheduled:
       reset:daily-limit    ──┐
       reset:monthly-limit  ──┼──▶ zero the counters for that frequency band
       reset:yearly-limit   ──┘

   audit (tables, not log files):
       EWLimitConfigLog · EWUserLimitConfigLog · EWUserActivityLimitLog
       EWUserConfigLog · EWConfigLog · EWTrxActivityTypeLog · HttpLog
```

## Why it's shaped this way

**Limits resolve most-specific-first, across three levels.** A regulated
wallet needs a system default per activity type, a per-customer override (risk
tier, enhanced KYC), and sometimes a cap on one specific activity for one
specific customer. `EWLimitConfigService` walks
`EWUserActivityLimit → EWUserLimitConfig → EWLimitConfig` and takes the first
match. Collapsing this into one table forces a row per user per activity even
when almost everyone is on the default, and makes "raise the default for
everyone" a mass update instead of a single edit.

**Frequency is a dimension, not three separate columns.** `EWFrequency`
(daily / monthly / yearly) applies at every level, because regulators specify
limits in exactly those bands and a customer typically has all three at once.

**Counters are maintained and reset on a schedule.** The obvious alternative —
computing "spent today" by summing transaction history on every authorisation
— is correct but puts an aggregate query on the hot path of every payment. A
maintained counter reset by `reset:daily-limit` / `reset:monthly-limit` /
`reset:yearly-limit` keeps authorisation cheap. Three separate commands rather
than one with a switch means a failure in the monthly reset cannot delay the
daily one, and each can be scheduled at the boundary that actually applies to
it.

**Every configuration change is logged to a table.** `EWLimitConfigLog`,
`EWUserLimitConfigLog`, `EWUserActivityLimitLog`, `EWConfigLog` and the rest
exist because "who raised this customer's daily limit to X, and when" is a
question asked by auditors and fraud investigators, not by developers. A log
file rotates away and cannot be joined to the transaction it explains.

**Payloads are encrypted, with rotation designed in.** `EncryptionService`
resolves a key by `kid`, and the keystore holds several keys at once. Rotation
is therefore: add the new key, repoint `config/encryption.php`, retire the old
one — no downtime and no window where in-flight payloads become undecryptable.
Each environment maps to a different kid, so a staging key can never decrypt
production traffic. These are symmetric keys (`kty: oct`), which is why the
keystore is gitignored and templated rather than committed.

**OTP issuance and OTP consumption are separate tables.** `EWOTPHistory`
records what was sent; `EWOTPUsageLog` records what was used. One table with a
`used` flag answers "was this consumed" but not "how many times was it
attempted, from where, over what period" — which is the shape of both a replay
attack and a brute-force attempt.

**Registration is a sequence of known steps.** `EWUserRegistrationStep` plus a
pending-detail review path means a half-onboarded customer sits at an explicit
position that support can see and act on. Representing partial KYC as nulls
scattered across the user record makes "what is this customer waiting for"
unanswerable.

**Bank transfers are modelled apart from wallet transfers.** An in-wallet
transfer is immediate and either succeeds or does not. An external bank rail
settles asynchronously, can be pending for hours, and fails in ways that need
their own states and reconciliation — hence `EWBankTransferRequest` /
`EWBankTrxRequest` rather than a flag on the transaction table.

**Activities are enumerated.** `EWTrxActivityType` is what limits, reporting
and notification all key off. A free-text description would make the limit
engine string-match, which is where silent gaps in enforcement come from.

## Transaction lifecycle

```
client → POST (JWE payload)
  ▼
EncryptionService.decrypt()      kid → key → plaintext request
  ▼
TokenHandler                     authenticate
  ▼
TransactionHandler
  │  activityType = EWTrxActivityType for this operation
  │
  │  EWLimitConfigService.resolve(user, activityType, frequency)
  │     EWUserActivityLimit ?  → use it
  │     else EWUserLimitConfig ? → use it
  │     else EWLimitConfig       → system default
  │
  │  current counter + amount > limit ?
  │     yes → reject (nothing written, nothing moved)
  │
  │  OTP required for this activity ?
  │     issue  → EWOTPHistory  → SMSHandler
  │     verify → EWOTPUsageLog
  │
  │  EWalletHandler / CoreHandler → move balance
  │  EWTrxHistory                  → record, typed by activity
  │  increment the frequency counters
  │  EWSendTransactionNotification → queued
  ▼
response encrypted with the same kid
```

## Scheduled resets

```
00:00 daily     reset:daily-limit    → zero every daily counter
month boundary  reset:monthly-limit  → zero every monthly counter
year boundary   reset:yearly-limit   → zero every yearly counter

each command targets one EWFrequency band only, so a failure in one
cannot hold up another, and each can be retried independently
```

## Structure walkthrough

`app/Services/EWLimitConfigService.php` and
`app/Repositories/EWalletLimitConfig.php` are the files to read first — the
three-level resolution they implement is the core of the product.
`app/Services/EncryptionService.php` is the transport security layer and the
only place keys are touched. `app/Console/Commands/*LimitReset.php` are what
make the maintained counters correct over time. `app/Models/EW*Log.php` are
the audit trail — note that there is a log model for every configuration model,
which is deliberate rather than incidental. `app/Admin/Controllers/EW*` is the
operator surface, including a view for each log so an auditor never needs
database access.
