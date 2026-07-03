<?php

/**
 * Live API E2E QA script — run: php qa_e2e.php
 * Requires: php artisan serve on http://127.0.0.1:8000
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;

$base = 'http://localhost:8000';
$email = 'qa-'.bin2hex(random_bytes(4)).'@example.com';
$cookieFile = sys_get_temp_dir().'/qa_cookies.txt';
@unlink($cookieFile);

function req(string $method, string $url, string $cookieFile, ?string $body = null, array $headers = []): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_FOLLOWLOCATION => false,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $resp = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, $resp];
}

$results = [];

function refreshCsrf(string $base, string $cookieFile): string
{
    req('GET', $base.'/sanctum/csrf-cookie', $cookieFile, null, [
        'Origin: http://localhost:5173',
        'Accept: application/json',
    ]);
    [, $csrfResp] = req('GET', $base.'/sanctum/csrf-token', $cookieFile, null, [
        'Origin: http://localhost:5173',
        'Accept: application/json',
    ]);

    return json_decode($csrfResp, true)['csrf_token'] ?? '';
}

[$c] = req('GET', $base.'/up', $cookieFile);
$results[] = ['GET /up', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

$csrf = refreshCsrf($base, $cookieFile);
$results[] = ['CSRF token fetch', $csrf ? 'PASS' : 'FAIL', ''];

$regBody = json_encode([
    'name' => 'QA User',
    'email' => $email,
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'city' => 'Lahore',
]);
[$c, $regResp] = req('POST', $base.'/api/auth/register', $cookieFile, $regBody, [
    'Origin: http://localhost:5173',
    'Accept: application/json',
    'Content-Type: application/json',
    'X-CSRF-TOKEN: '.$csrf,
]);
$reg = json_decode($regResp, true);
$token = $reg['data']['token'] ?? '';
$verified = $reg['data']['user']['email_verified'] ?? null;
$results[] = ['POST /api/auth/register', ($c === 201 && $token && $verified === false) ? 'PASS' : 'FAIL', (string) $c];

[$c, $wResp] = req('GET', $base.'/api/wardrobe', $cookieFile, null, [
    'Authorization: Bearer '.$token,
    'Accept: application/json',
]);
$w = json_decode($wResp, true);
$results[] = ['GET /api/wardrobe (unverified)', ($c === 403 && ($w['code'] ?? '') === 'unverified') ? 'PASS' : 'FAIL', (string) $c];

$user = User::where('email', $email)->first();
if (! $user) {
    echo "FAIL: Registration did not create user. Last register response:\n{$regResp}\n";
    exit(1);
}
$verifyUrl = URL::temporarySignedRoute(
    'verification.verify',
    now()->addHour(),
    ['id' => $user->id, 'hash' => sha1($user->email)]
);
[$c] = req('GET', $verifyUrl, $cookieFile, null, ['Accept: application/json']);
$user->refresh();
$results[] = ['GET verify link (redirect)', ($c >= 300 && $c < 400) ? 'PASS' : 'FAIL', (string) $c];
$results[] = ['Email verified in DB', $user->hasVerifiedEmail() ? 'PASS' : 'FAIL', ''];

[$c] = req('GET', $base.'/api/wardrobe', $cookieFile, null, [
    'Authorization: Bearer '.$token,
    'Accept: application/json',
]);
$results[] = ['GET /api/wardrobe (verified)', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

$csrf = refreshCsrf($base, $cookieFile);
[$c] = req('POST', $base.'/api/auth/logout', $cookieFile, '{}', [
    'Origin: http://localhost:5173',
    'Accept: application/json',
    'Content-Type: application/json',
    'X-CSRF-TOKEN: '.$csrf,
    'Authorization: Bearer '.$token,
]);
$results[] = ['POST /api/auth/logout', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

req('GET', $base.'/sanctum/csrf-cookie', $cookieFile, null, ['Origin: http://localhost:5173']);
$csrf2 = refreshCsrf($base, $cookieFile);
[$c, $loginResp] = req('POST', $base.'/api/auth/login', $cookieFile, json_encode([
    'email' => $email,
    'password' => 'password123',
]), [
    'Origin: http://localhost:5173',
    'Accept: application/json',
    'Content-Type: application/json',
    'X-CSRF-TOKEN: '.$csrf2,
]);
$login = json_decode($loginResp, true);
$results[] = ['POST /api/auth/login', ($c === 200 && ($login['data']['user']['email_verified'] ?? false) === true) ? 'PASS' : 'FAIL', (string) $c];

$csrf2 = refreshCsrf($base, $cookieFile);
[$c] = req('POST', $base.'/api/auth/forgot-password', $cookieFile, json_encode(['email' => $email]), [
    'Origin: http://localhost:5173',
    'Accept: application/json',
    'Content-Type: application/json',
    'X-CSRF-TOKEN: '.$csrf2,
]);
$results[] = ['POST /api/auth/forgot-password', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

$resetToken = Password::createToken($user);
$csrf2 = refreshCsrf($base, $cookieFile);
[$c] = req('POST', $base.'/api/auth/reset-password', $cookieFile, json_encode([
    'email' => $email,
    'token' => $resetToken,
    'password' => 'newpass12345',
    'password_confirmation' => 'newpass12345',
]), [
    'Origin: http://localhost:5173',
    'Accept: application/json',
    'Content-Type: application/json',
    'X-CSRF-TOKEN: '.$csrf2,
]);
$results[] = ['POST /api/auth/reset-password', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

[$c] = req('GET', $base.'/api/wardrobe', $cookieFile, null, [
    'Authorization: Bearer '.$token,
    'Accept: application/json',
]);
$results[] = ['Old token invalidated after reset', $c === 401 ? 'PASS' : 'FAIL', (string) $c];

$csrf3 = refreshCsrf($base, $cookieFile);
[$c] = req('POST', $base.'/api/auth/login', $cookieFile, json_encode([
    'email' => $email,
    'password' => 'newpass12345',
]), [
    'Origin: http://localhost:5173',
    'Accept: application/json',
    'Content-Type: application/json',
    'X-CSRF-TOKEN: '.$csrf3,
]);
$results[] = ['Login with new password', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

$fail = 0;
echo "QA E2E API Results (email: {$email})\n";
echo str_repeat('-', 60)."\n";
foreach ($results as $r) {
    printf("%-40s %-8s %s\n", $r[0], $r[1], $r[2]);
    if ($r[1] === 'FAIL') {
        $fail++;
    }
}
echo str_repeat('-', 60)."\n";
echo 'Total: '.count($results).", Failed: {$fail}\n";

exit($fail > 0 ? 1 : 0);
