<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\RespondsWithJson;
use App\Http\Resources\BookingResource;
use App\Models\Beautician;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    use RespondsWithJson;

    public function store(Request $request)
    {
        $validated = $request->validate([
            'beautician_id' => ['required', 'exists:beauticians,id'],
            'service_type' => ['required', 'string', 'max:255'],
            'booking_date' => ['required', 'date', 'after:today'],
            'booking_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ]);

        $beautician = Beautician::findOrFail($validated['beautician_id']);

        if (! $beautician->is_available) {
            return $this->error('This beautician is not available for bookings.', null, 422);
        }

        $allowed = $beautician->allowedServiceTypes();
        if (! in_array($validated['service_type'], $allowed, true)) {
            return $this->error(
                'That service is not offered by this salon. Choose one of: '.implode(', ', $allowed),
                null,
                422
            );
        }

        $booking = Booking::create([
            'user_id' => $request->user()->id,
            'beautician_id' => $beautician->id,
            'service_type' => $validated['service_type'],
            'booking_date' => $validated['booking_date'],
            'booking_time' => $validated['booking_time'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'price' => $beautician->hourly_rate,
        ]);

        $booking->load('beautician');

        return $this->success(
            ['booking' => new BookingResource($booking)],
            'Booking created successfully.',
            201
        );
    }

    public function index(Request $request)
    {
        $bookings = $request->user()
            ->bookings()
            ->with('beautician')
            ->latest()
            ->get();

        return $this->success([
            'bookings' => BookingResource::collection($bookings),
        ]);
    }

    public function cancel(Request $request, int $id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->user_id !== $request->user()->id) {
            return $this->error('Forbidden.', null, 403);
        }

        if ($booking->status !== 'pending') {
            return $this->error('Only pending bookings can be cancelled.', null, 422);
        }

        $booking->update(['status' => 'cancelled']);
        $booking->load('beautician');

        return $this->success(
            ['booking' => new BookingResource($booking)],
            'Booking cancelled successfully.'
        );
    }
}
