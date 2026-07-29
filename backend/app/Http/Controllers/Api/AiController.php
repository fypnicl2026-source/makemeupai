<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Controllers\Controller;
use App\Services\FaceAnalysisService;
use App\Services\LookRecommendationService;
use App\Support\PublicStorageUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AiController extends Controller
{
    use RespondsWithJson;

    public function __construct(
        private readonly FaceAnalysisService $faceAnalysisService,
        private readonly LookRecommendationService $lookRecommendationService
    ) {}

    public function faceProfile(Request $request)
    {
        $user = $request->user();

        return $this->success([
            'profile_photo_url' => $this->profilePhotoUrl($user->profile_photo),
            'face_traits' => $user->face_traits,
        ]);
    }

    public function faceAnalysis(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        $file = $request->file('image');
        $contents = file_get_contents($file->getRealPath());
        $traits = $this->faceAnalysisService->analyze($user->id, $contents);

        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $path = $file->storeAs(
            'selfies',
            "{$user->id}.{$extension}",
            'public'
        );

        $user->update([
            'profile_photo' => $path,
            'face_traits' => $traits,
        ]);

        return $this->success([
            'faceShape' => $traits['faceShape'],
            'skinTone' => $traits['skinTone'],
            'hairLength' => $traits['hairLength'],
            'profile_photo_url' => $this->profilePhotoUrl($path),
            'analyzed_at' => $traits['analyzed_at'],
        ], 'Face analysis complete.');
    }

    public function lookRecommendations(Request $request)
    {
        $validated = $request->validate([
            'eventType' => ['required', Rule::in(['wedding', 'party', 'casual', 'work', 'formal'])],
            'styleMood' => ['required', Rule::in(['elegant', 'natural', 'bold', 'soft'])],
            'gender' => ['nullable', Rule::in(['male', 'female'])],
        ]);

        $user = $request->user();
        $traits = $user->face_traits;

        if (! is_array($traits) || empty($traits['faceShape'])) {
            return $this->error(
                'Upload a selfie for face analysis before generating look recommendations.',
                null,
                422
            );
        }

        $gender = $validated['gender'] ?? $user->gender;
        if (! in_array($gender, ['male', 'female'], true)) {
            return $this->error(
                'Select your gender to get personalized look recommendations.',
                null,
                422
            );
        }

        if ($user->gender !== $gender) {
            $user->update(['gender' => $gender]);
        }

        $recommendations = $this->lookRecommendationService->recommend(
            $traits,
            $validated['eventType'],
            $validated['styleMood'],
            $gender
        );

        return $this->success($recommendations);
    }

    private function profilePhotoUrl(?string $path): ?string
    {
        return PublicStorageUrl::fromPath($path);
    }
}
