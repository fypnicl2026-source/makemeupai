<?php

/**
 * Manual checklist spot-check via API (flows 1–5 style).
 * Requires: php artisan serve on http://localhost:8000
 * Run: php qa_manual_spotcheck.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Beautician;
use App\Models\User;
use Illuminate\Support\Facades\URL;

$base = 'http://localhost:8000';
$email = 'manual-'.bin2hex(random_bytes(3)).'@example.com';
$cookieFile = sys_get_temp_dir().'/qa_manual_cookies.txt';
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

$results = [];
$csrf = refreshCsrf($base, $cookieFile);

[$c, $regResp] = req('POST', $base.'/api/auth/register', $cookieFile, json_encode([
    'name' => 'Manual QA',
    'email' => $email,
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'city' => 'Lahore',
]), [
    'Origin: http://localhost:5173',
    'Accept: application/json',
    'Content-Type: application/json',
    'X-CSRF-TOKEN: '.$csrf,
]);
$reg = json_decode($regResp, true);
$token = $reg['data']['token'] ?? '';
$results[] = ['FLOW1 Register', ($c === 201 && $token) ? 'PASS' : 'FAIL', (string) $c];

$user = User::where('email', $email)->first();
$verifyUrl = URL::temporarySignedRoute(
    'verification.verify',
    now()->addHour(),
    ['id' => $user->id, 'hash' => sha1($user->email)]
);
req('GET', $verifyUrl, $cookieFile, null, ['Accept: application/json']);
$user->refresh();
$results[] = ['FLOW1 Email verified', $user->hasVerifiedEmail() ? 'PASS' : 'FAIL', ''];

[$c] = req('GET', $base.'/api/auth/me', $cookieFile, null, [
    'Authorization: Bearer '.$token,
    'Accept: application/json',
    'Origin: http://localhost:5173',
]);
$results[] = ['FLOW2 Session/me', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

$authHeaders = function (string $csrf) use ($token): array {
    return [
        'Origin: http://localhost:5173',
        'Accept: application/json',
        'Content-Type: application/json',
        'X-CSRF-TOKEN: '.$csrf,
        'Authorization: Bearer '.$token,
    ];
};

$csrf = refreshCsrf($base, $cookieFile);
$items = [
    ['name' => 'Blue Kurta', 'category' => 'tops', 'colors' => ['blue'], 'season' => ['summer'], 'occasion' => ['casual']],
    ['name' => 'Black Pants', 'category' => 'bottoms', 'colors' => ['black'], 'season' => ['summer'], 'occasion' => ['casual']],
    ['name' => 'White Sneakers', 'category' => 'shoes', 'colors' => ['white'], 'season' => ['summer'], 'occasion' => ['casual']],
];
$createdIds = [];
foreach ($items as $item) {
    [$c, $resp] = req('POST', $base.'/api/wardrobe', $cookieFile, json_encode($item), $authHeaders($csrf));
    $csrf = refreshCsrf($base, $cookieFile);
    $data = json_decode($resp, true);
    if (($data['data']['item']['id'] ?? null)) {
        $createdIds[] = $data['data']['item']['id'];
    }
}
$results[] = ['FLOW3 Wardrobe create x3', (count($createdIds) === 3) ? 'PASS' : 'FAIL', (string) count($createdIds)];

[$c, $listResp] = req('GET', $base.'/api/wardrobe', $cookieFile, null, [
    'Authorization: Bearer '.$token,
    'Accept: application/json',
]);
$list = json_decode($listResp, true);
$count = count($list['data']['items'] ?? $list['data'] ?? []);
$results[] = ['FLOW3 Wardrobe list', ($c === 200 && $count >= 3) ? 'PASS' : 'FAIL', (string) $c.'/'.$count];

[$c, $recResp] = req('GET', $base.'/api/recommendations?occasion=casual', $cookieFile, null, [
    'Authorization: Bearer '.$token,
    'Accept: application/json',
]);
$rec = json_decode($recResp, true);
$outfits = $rec['data']['outfits'] ?? $rec['data'] ?? [];
$results[] = ['FLOW4 Recommendations', ($c === 200 && is_array($outfits)) ? 'PASS' : 'FAIL', (string) $c];

// Minimal 1x1 PNG
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
$tmpImg = sys_get_temp_dir().'/qa_face.png';
file_put_contents($tmpImg, $png);
$csrf = refreshCsrf($base, $cookieFile);
$ch = curl_init($base.'/api/ai/face-analysis');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_COOKIEJAR => $cookieFile,
    CURLOPT_COOKIEFILE => $cookieFile,
    CURLOPT_HTTPHEADER => [
        'Origin: http://localhost:5173',
        'Accept: application/json',
        'X-CSRF-TOKEN: '.$csrf,
        'Authorization: Bearer '.$token,
    ],
    CURLOPT_POSTFIELDS => [
        'image' => new CURLFile($tmpImg, 'image/png', 'selfie.png'),
    ],
]);
$faceResp = curl_exec($ch);
$faceCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
@unlink($tmpImg);
$face = json_decode($faceResp, true);
$results[] = ['FLOW4b Face analysis', ($faceCode === 200 && ! empty($face['data'])) ? 'PASS' : 'FAIL', (string) $faceCode];

$csrf = refreshCsrf($base, $cookieFile);
[$c] = req('POST', $base.'/api/ai/look-recommendations', $cookieFile, json_encode([
    'eventType' => 'wedding',
    'styleMood' => 'elegant',
]), $authHeaders($csrf));
$results[] = ['FLOW4b Look recommendations', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

if (Beautician::count() === 0) {
    Beautician::create([
        'name' => 'QA Beautician',
        'bio' => 'QA seeded beautician',
        'city' => 'Lahore',
        'specializations' => ['makeup'],
        'hourly_rate' => 2000,
        'skill_badge' => 'expert',
        'profile_photo' => null,
        'is_available' => true,
        'avg_rating' => 4.8,
    ]);
}
[$c, $bResp] = req('GET', $base.'/api/beauticians', $cookieFile, null, ['Accept: application/json']);
$beauticians = json_decode($bResp, true);
$bList = $beauticians['data']['beauticians'] ?? [];
$firstId = $bList[0]['id'] ?? Beautician::query()->value('id');
$results[] = ['FLOW5 Beauticians list', ($c === 200 && $firstId) ? 'PASS' : 'FAIL', (string) $c];

$csrf = refreshCsrf($base, $cookieFile);
$date = now()->addDays(2)->format('Y-m-d');
[$c, $bookResp] = req('POST', $base.'/api/bookings', $cookieFile, json_encode([
    'beautician_id' => $firstId,
    'service_type' => 'Makeup Session',
    'booking_date' => $date,
    'booking_time' => '14:00',
    'notes' => 'QA booking',
]), $authHeaders($csrf));
$book = json_decode($bookResp, true);
$bookingId = $book['data']['booking']['id'] ?? null;
$results[] = ['FLOW5 Create booking', ($c === 201 && $bookingId) ? 'PASS' : 'FAIL', (string) $c];

$csrf = refreshCsrf($base, $cookieFile);
[$c] = req('PATCH', $base.'/api/bookings/'.$bookingId.'/cancel', $cookieFile, '{}', $authHeaders($csrf));
$results[] = ['FLOW5 Cancel booking', $c === 200 ? 'PASS' : 'FAIL', (string) $c];

[$c] = req('GET', $base.'/api/wardrobe', $cookieFile, null, ['Accept: application/json']);
$results[] = ['FLOW7 Guard without auth', $c === 401 ? 'PASS' : 'FAIL', (string) $c];

$fail = 0;
echo "Manual checklist API spot-check\n";
echo str_repeat('-', 60)."\n";
foreach ($results as $r) {
    printf("%-36s %-8s %s\n", $r[0], $r[1], $r[2]);
    if ($r[1] === 'FAIL') {
        $fail++;
    }
}
echo str_repeat('-', 60)."\n";
echo 'Total: '.count($results).", Failed: {$fail}\n";
exit($fail > 0 ? 1 : 0);
