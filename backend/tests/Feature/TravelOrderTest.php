<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TravelOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\Facades\JWTAuth;

class TravelOrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Retorna a requisição autenticada via JWT.
     */
    private function actingAsJwt(User $user)
    {
        $token = JWTAuth::fromUser($user);
        return $this->withHeader('Authorization', 'Bearer ' . $token);
    }

    // Testes para Li
    public function test_user_can_list_travel_orders()
    {
        $user = User::factory()->create();
        TravelOrder::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($user)
            ->getJson('/api/orders');
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'current_page',
                    'data' => [
                        '*' => [
                            'id',
                            'user_id',
                            'requester_name',
                            'destination',
                            'departure_date',
                            'return_date',
                            'status',
                            'created_at',
                            'updated_at'
                        ]
                    ],
                    'first_page_url',
                    'from',
                    'last_page',
                    'last_page_url',
                    'links',
                    'next_page_url',
                    'path',
                    'per_page',
                    'prev_page_url',
                    'to',
                    'total'
                ]
            ]);
            
    }

    public function test_user_can_create_travel_order()
    {
        $user = User::factory()->create();
        $payload = [
            'requester_name'  => 'Matheus Castro',
            'destination'     => 'São Paulo',
            'departure_date'  => '2025-09-01',
            'return_date'     => '2025-09-10',
        ];

        $response = $this->actingAsJwt($user)
            ->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id', 
                    'requester_name', 
                    'destination', 
                    'departure_date', 
                    'return_date', 
                    'status', 
                    'created_at', 
                    'updated_at'
                ]
            ]);
    }

    public function test_validation_error_on_create_travel_order()
    {
        $user = User::factory()->create();
        $payload = []; // vazio para forçar erro

        $response = $this->actingAsJwt($user)
            ->postJson('/api/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'requester_name',
                    'destination',
                    'departure_date',
                    'return_date',
                ]
            ]);
    }

    public function test_user_can_view_travel_order()
    {
        $user = User::factory()->create();
        $order = TravelOrder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($user)
            ->getJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id', 
                    'requester_name', 
                    'destination', 
                    'departure_date', 
                    'return_date', 
                    'status', 
                    'created_at', 
                    'updated_at'
                ]
            ]);
    }

    public function test_user_can_delete_travel_order()
    {
        $user = User::factory()->create();
        $order = TravelOrder::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsJwt($user)
            ->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => true
            ]);
    }

    public function test_admin_can_approve_travel_order()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = TravelOrder::factory()->create(['user_id' => $user->id, 'status' => 'requested']);

        $response = $this->actingAsJwt($admin)
            ->putJson("/api/orders/{$order->id}/approve", ['status' => 'approved']);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');
    }

    public function test_admin_can_cancel_travel_order()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        $order = TravelOrder::factory()->create(['user_id' => $user->id, 'status' => 'requested']);

        $response = $this->actingAsJwt($admin)
            ->putJson("/api/orders/{$order->id}/cancel", ['status' => 'canceled']);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'canceled');
    }
}
