<?php

namespace Tests\Feature;

use App\Models\Beautician;
use App\Models\Booking;
use App\Models\ClothingItem;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(array $overrides = []): User
    {
        return User::factory()->create($overrides);
    }

    private function actingAsUser(?User $user = null): User
    {
        $user = $user ?? $this->makeUser();
        $this->actingAs($user);

        return $user;
    }

    private function validRegisterPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }

    private function validWardrobePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Blue Shirt',
            'category' => 'tops',
            'colors' => ['blue'],
            'season' => ['summer'],
            'occasion' => ['casual'],
        ], $overrides);
    }

    private function seedBeautician(array $overrides = []): Beautician
    {
        return Beautician::create(array_merge([
            'name' => 'Ayesha Noor',
            'salon_name' => 'Noor Bridal Studio',
            'bio' => 'Makeup specialist.',
            'city' => 'Lahore',
            'area' => 'Gulberg',
            'gender_focus' => 'female',
            'specializations' => ['makeup', 'bridal'],
            'hourly_rate' => 3500.00,
            'skill_badge' => 'expert',
            'profile_photo' => null,
            'is_available' => true,
            'avg_rating' => 4.85,
        ], $overrides));
    }

    private function seedWardrobeItem(User $user, array $overrides = []): ClothingItem
    {
        return ClothingItem::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Casual Top',
            'category' => 'tops',
            'colors' => ['blue'],
            'season' => ['summer'],
            'occasion' => ['casual'],
            'notes' => null,
            'image_path' => null,
        ], $overrides));
    }

    private function withStatefulFrontend(): static
    {
        return $this
            ->withHeader('Origin', 'http://localhost:5173')
            ->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function validBookingPayload(int $beauticianId, array $overrides = []): array
    {
        return array_merge([
            'beautician_id' => $beauticianId,
            'service_type' => 'Makeup Session',
            'booking_date' => now()->addDays(3)->format('Y-m-d'),
            'booking_time' => '14:00',
            'notes' => 'Please call on arrival.',
        ], $overrides);
    }

    // Auth

    public function test_register_with_valid_data_returns_201(): void
    {
        $response = $this->withStatefulFrontend()->postJson('/api/auth/register', $this->validRegisterPayload([
            'email' => 'newuser@example.com',
        ]));

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.email', 'newuser@example.com')
            ->assertJsonPath('data.user.name', 'Test User')
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_register_with_duplicate_email_returns_422(): void
    {
        $this->makeUser(['email' => 'duplicate@example.com']);

        $response = $this->withStatefulFrontend()->postJson('/api/auth/register', $this->validRegisterPayload([
            'email' => 'duplicate@example.com',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_register_with_missing_fields_returns_422(): void
    {
        $response = $this->withStatefulFrontend()->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_login_with_correct_credentials_returns_200(): void
    {
        $user = $this->makeUser([
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response = $this->withStatefulFrontend()->postJson('/api/auth/login', [
            'email' => 'login@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', 'login@example.com')
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_login_with_wrong_password_returns_401(): void
    {
        $this->makeUser([
            'email' => 'wrongpass@example.com',
            'password' => 'password123',
        ]);

        $response = $this->withStatefulFrontend()->postJson('/api/auth/login', [
            'email' => 'wrongpass@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false, 'message' => 'Invalid credentials.']);
    }

    public function test_get_auth_me_without_auth_returns_401(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertUnauthorized();
    }

    public function test_get_auth_me_with_auth_returns_200(): void
    {
        $user = $this->actingAsUser();

        $response = $this->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.id', $user->id);
    }

    // Wardrobe

    public function test_get_wardrobe_without_auth_returns_401(): void
    {
        $response = $this->getJson('/api/wardrobe');

        $response->assertUnauthorized();
    }

    public function test_get_wardrobe_with_bearer_token_returns_200(): void
    {
        $user = $this->makeUser();
        $this->seedWardrobeItem($user);
        $token = $user->createToken('spa')->plainTextToken;

        $response = $this->getJson('/api/wardrobe', [
            'Authorization' => 'Bearer '.$token,
        ]);

        $response->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_get_wardrobe_with_auth_returns_200(): void
    {
        $user = $this->actingAsUser();
        $this->seedWardrobeItem($user);

        $response = $this->getJson('/api/wardrobe');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['items']]);

        $this->assertIsArray($response->json('data.items'));
    }

    public function test_post_wardrobe_with_valid_data_returns_201(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/wardrobe', $this->validWardrobePayload());

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.item.name', 'Blue Shirt')
            ->assertJsonPath('data.item.category', 'tops');
    }

    public function test_post_wardrobe_with_missing_category_returns_422(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/wardrobe', [
            'name' => 'No Category Item',
            'colors' => ['red'],
            'season' => ['summer'],
            'occasion' => ['casual'],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['category']);
    }

    public function test_delete_wardrobe_own_item_returns_200(): void
    {
        $user = $this->actingAsUser();
        $item = $this->seedWardrobeItem($user);

        $response = $this->deleteJson("/api/wardrobe/{$item->id}");

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('clothing_items', ['id' => $item->id]);
    }

    public function test_delete_wardrobe_another_users_item_returns_403(): void
    {
        $owner = $this->makeUser();
        $other = $this->actingAsUser();
        $item = $this->seedWardrobeItem($owner);

        $response = $this->deleteJson("/api/wardrobe/{$item->id}");

        $response->assertForbidden()
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('clothing_items', ['id' => $item->id]);
    }

    // Recommendations

    public function test_get_recommendations_with_auth_returns_200(): void
    {
        $user = $this->actingAsUser();
        $this->seedWardrobeItem($user, ['category' => 'tops', 'occasion' => ['casual']]);
        $this->seedWardrobeItem($user, ['name' => 'Jeans', 'category' => 'bottoms', 'occasion' => ['casual']]);
        $this->seedWardrobeItem($user, ['name' => 'Sneakers', 'category' => 'shoes', 'occasion' => ['casual']]);

        $response = $this->getJson('/api/recommendations?occasion=casual');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['combinations']]);

        $combinations = $response->json('data.combinations');
        $this->assertIsArray($combinations);

        if (count($combinations) > 0) {
            $this->assertArrayHasKey('items', $combinations[0]);
        }
    }

    public function test_get_recommendations_without_auth_returns_401(): void
    {
        $response = $this->getJson('/api/recommendations?occasion=casual');

        $response->assertUnauthorized();
    }

    // Beauticians

    public function test_get_beauticians_returns_200(): void
    {
        $this->seedBeautician();

        $response = $this->getJson('/api/beauticians');

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['beauticians']]);

        $this->assertNotEmpty($response->json('data.beauticians'));
    }

    public function test_get_beauticians_filtered_by_city_returns_200(): void
    {
        $this->seedBeautician(['name' => 'Lahore Pro', 'city' => 'Lahore']);
        $this->seedBeautician(['name' => 'Karachi Pro', 'city' => 'Karachi']);

        $response = $this->getJson('/api/beauticians?city=Lahore');

        $response->assertOk()
            ->assertJson(['success' => true]);

        $cities = collect($response->json('data.beauticians'))->pluck('city')->unique()->values()->all();
        $this->assertSame(['Lahore'], $cities);
    }

    public function test_get_beauticians_filtered_by_gender_focus_returns_200(): void
    {
        $this->seedBeautician([
            'name' => 'Women Salon',
            'salon_name' => 'Blush Lane',
            'gender_focus' => 'female',
        ]);
        $this->seedBeautician([
            'name' => 'Men Salon',
            'salon_name' => 'Barber Co',
            'gender_focus' => 'male',
            'specializations' => ['haircut', 'beard'],
        ]);

        $response = $this->getJson('/api/beauticians?city=Lahore&gender_focus=male');

        $response->assertOk()
            ->assertJson(['success' => true]);

        $focuses = collect($response->json('data.beauticians'))->pluck('gender_focus')->unique()->values()->all();
        $this->assertSame(['male'], $focuses);
        $this->assertArrayHasKey('allowed_services', $response->json('data.beauticians.0'));
        $this->assertArrayHasKey('salon_name', $response->json('data.beauticians.0'));
    }

    public function test_get_beautician_by_valid_id_returns_200(): void
    {
        $beautician = $this->seedBeautician();

        $response = $this->getJson("/api/beauticians/{$beautician->id}");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.beautician.id', $beautician->id);
    }

    public function test_get_beautician_by_invalid_id_returns_404(): void
    {
        $response = $this->getJson('/api/beauticians/99999');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Not found',
            ]);
    }

    // Bookings

    public function test_post_bookings_without_auth_returns_401(): void
    {
        $beautician = $this->seedBeautician();

        $response = $this->postJson('/api/bookings', $this->validBookingPayload($beautician->id));

        $response->assertUnauthorized();
    }

    public function test_post_bookings_with_valid_future_date_returns_201(): void
    {
        $this->actingAsUser();
        $beautician = $this->seedBeautician();

        $response = $this->postJson('/api/bookings', $this->validBookingPayload($beautician->id));

        $response->assertCreated()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.booking.status', 'pending');
    }

    public function test_post_bookings_with_mismatched_service_returns_422(): void
    {
        $this->actingAsUser();
        $beautician = $this->seedBeautician(['gender_focus' => 'male', 'specializations' => ['haircut', 'beard']]);

        $response = $this->postJson('/api/bookings', $this->validBookingPayload($beautician->id, [
            'service_type' => 'Bridal Package',
        ]));

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_post_bookings_male_salon_with_valid_service_returns_201(): void
    {
        $this->actingAsUser();
        $beautician = $this->seedBeautician([
            'name' => 'Ali Barber',
            'gender_focus' => 'male',
            'specializations' => ['haircut', 'beard'],
        ]);

        $response = $this->postJson('/api/bookings', $this->validBookingPayload($beautician->id, [
            'service_type' => 'Beard Grooming',
        ]));

        $response->assertCreated()
            ->assertJsonPath('data.booking.service_type', 'Beard Grooming');
    }

    public function test_post_bookings_with_past_date_returns_422(): void
    {
        $this->actingAsUser();
        $beautician = $this->seedBeautician();

        $response = $this->postJson('/api/bookings', $this->validBookingPayload($beautician->id, [
            'booking_date' => now()->subDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['booking_date']);
    }

    public function test_get_bookings_with_auth_returns_200(): void
    {
        $userA = $this->makeUser();
        $userB = $this->makeUser();
        $beautician = $this->seedBeautician();

        Booking::create([
            'user_id' => $userA->id,
            'beautician_id' => $beautician->id,
            'service_type' => 'Makeup',
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'pending',
            'notes' => null,
            'price' => 3500.00,
        ]);

        Booking::create([
            'user_id' => $userB->id,
            'beautician_id' => $beautician->id,
            'service_type' => 'Hair',
            'booking_date' => now()->addDays(3)->format('Y-m-d'),
            'booking_time' => '11:00',
            'status' => 'pending',
            'notes' => null,
            'price' => 3500.00,
        ]);

        $this->actingAs($userA);
        $response = $this->getJson('/api/bookings');

        $response->assertOk()
            ->assertJson(['success' => true]);

        $bookings = $response->json('data.bookings');
        $this->assertCount(1, $bookings);
        $this->assertSame('Makeup', $bookings[0]['service_type']);
    }

    public function test_patch_cancel_own_pending_booking_returns_200(): void
    {
        $user = $this->actingAsUser();
        $beautician = $this->seedBeautician();

        $booking = Booking::create([
            'user_id' => $user->id,
            'beautician_id' => $beautician->id,
            'service_type' => 'Makeup',
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'pending',
            'notes' => null,
            'price' => 3500.00,
        ]);

        $response = $this->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.booking.status', 'cancelled');
    }

    public function test_patch_cancel_another_users_booking_returns_403(): void
    {
        $owner = $this->makeUser();
        $this->actingAsUser();
        $beautician = $this->seedBeautician();

        $booking = Booking::create([
            'user_id' => $owner->id,
            'beautician_id' => $beautician->id,
            'service_type' => 'Makeup',
            'booking_date' => now()->addDays(2)->format('Y-m-d'),
            'booking_time' => '10:00',
            'status' => 'pending',
            'notes' => null,
            'price' => 3500.00,
        ]);

        $response = $this->patchJson("/api/bookings/{$booking->id}/cancel");

        $response->assertForbidden()
            ->assertJson(['success' => false]);

        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'pending',
        ]);
    }

    // AI face insights

    public function test_post_look_recommendations_without_face_analysis_returns_422(): void
    {
        $this->actingAsUser();

        $response = $this->postJson('/api/ai/look-recommendations', [
            'eventType' => 'party',
            'styleMood' => 'elegant',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    private function fakeSelfieUpload(): UploadedFile
    {
        return UploadedFile::fake()->create('selfie.jpg', 64, 'image/jpeg');
    }

    public function test_post_face_analysis_returns_traits_and_stores_profile(): void
    {
        Storage::fake('public');
        $user = $this->actingAsUser();

        $response = $this->post('/api/ai/face-analysis', [
            'image' => $this->fakeSelfieUpload(),
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'data' => ['faceShape', 'skinTone', 'hairLength', 'profile_photo_url', 'analyzed_at'],
            ]);

        $user->refresh();
        $this->assertNotNull($user->profile_photo);
        $this->assertIsArray($user->face_traits);
        $this->assertNotEmpty($user->face_traits['faceShape']);
        Storage::disk('public')->assertExists($user->profile_photo);
    }

    public function test_post_look_recommendations_after_face_analysis_returns_200(): void
    {
        Storage::fake('public');
        $user = $this->actingAsUser();

        $this->post('/api/ai/face-analysis', [
            'image' => $this->fakeSelfieUpload(),
        ])->assertOk();

        $response = $this->postJson('/api/ai/look-recommendations', [
            'eventType' => 'wedding',
            'styleMood' => 'elegant',
            'gender' => 'female',
        ]);

        $response->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('data.gender', 'female')
            ->assertJsonStructure([
                'data' => ['makeup', 'hairstyle', 'mehndi', 'gender'],
            ]);

        $makeup = $response->json('data.makeup');
        $this->assertIsArray($makeup);
        $this->assertNotEmpty($makeup);

        $user->refresh();
        $this->assertSame('female', $user->gender);
    }

    public function test_post_look_recommendations_for_male_returns_styling_categories(): void
    {
        Storage::fake('public');
        $this->actingAsUser();

        $this->post('/api/ai/face-analysis', [
            'image' => $this->fakeSelfieUpload(),
        ])->assertOk();

        $response = $this->postJson('/api/ai/look-recommendations', [
            'eventType' => 'party',
            'styleMood' => 'bold',
            'gender' => 'male',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.gender', 'male')
            ->assertJsonStructure([
                'data' => ['hairstyle', 'beard_grooming', 'styling', 'gender'],
            ])
            ->assertJsonMissingPath('data.makeup')
            ->assertJsonMissingPath('data.mehndi');
    }

    public function test_get_face_profile_returns_photo_and_traits(): void
    {
        $user = $this->actingAsUser();
        $user->update([
            'face_traits' => [
                'faceShape' => 'oval',
                'skinTone' => 'warm-medium',
                'hairLength' => 'medium',
            ],
        ]);

        $response = $this->getJson('/api/ai/face-profile');

        $response->assertOk()
            ->assertJsonPath('data.face_traits.faceShape', 'oval');
    }
}
