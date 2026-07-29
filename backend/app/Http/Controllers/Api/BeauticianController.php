<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Resources\BeauticianResource;
use App\Models\Beautician;
use Illuminate\Http\Request;

class BeauticianController extends Controller
{
    use RespondsWithJson;

    public function index(Request $request)
    {
        $query = Beautician::where('is_available', true);

        if ($request->filled('city')) {
            $request->validate([
                'city' => ['required', 'string', 'max:100'],
            ]);

            $query->where('city', $request->city);
        }

        if ($request->filled('specialization')) {
            $request->validate([
                'specialization' => ['required', 'string', 'max:100'],
            ]);

            $query->whereJsonContains('specializations', $request->specialization);
        }

        if ($request->filled('gender_focus')) {
            $request->validate([
                'gender_focus' => ['required', 'in:male,female,unisex'],
            ]);

            $focus = $request->gender_focus;
            $query->where(function ($q) use ($focus) {
                $q->where('gender_focus', $focus)->orWhere('gender_focus', 'unisex');
            });
        }

        $beauticians = $query->orderByDesc('avg_rating')->get();

        return $this->success([
            'beauticians' => BeauticianResource::collection($beauticians),
        ]);
    }

    public function show(int $id)
    {
        $beautician = Beautician::where('is_available', true)->findOrFail($id);

        return $this->success([
            'beautician' => new BeauticianResource($beautician),
        ]);
    }
}
