<?php

/**
 * Maps APP_ENV to the key id (`kid`) used for payload encryption.
 *
 * EncryptionService reads this, then selects the matching key from
 * config/keystore.php. Rotation is therefore a two-step, zero-downtime
 * operation: add the new key to the keystore first, then point this mapping
 * at its kid. The previous key stays in the keystore until anything encrypted
 * with it has been consumed.
 *
 * Each environment gets its own kid so a staging key can never decrypt
 * production traffic.
 */

return [
    'local'      => env('JWE_KID_LOCAL', '00000000-0000-0000-0000-000000000001'),
    'staging'    => env('JWE_KID_STAGING', '00000000-0000-0000-0000-000000000002'),
    'production' => env('JWE_KID_PRODUCTION', '00000000-0000-0000-0000-000000000003'),
];
