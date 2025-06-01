<?php

namespace Tests\Feature;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_deposit()
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance' => 0.00]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/deposit', ['amount' => 100.00]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('wallets', ['user_id' => $user->id, 'balance' => 100.00]);
    }

    public function test_user_can_transfer()
    {
        $sender = User::factory()->create();
        $sender->wallet()->create(['balance' => 100.00]);
        $receiver = User::factory()->create();
        $receiver->wallet()->create(['balance' => 0.00]);
        $token = $sender->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/transfer', [
                'amount' => 50.00,
                'receiver_email' => $receiver->email,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('wallets', ['user_id' => $sender->id, 'balance' => 50.00]);
        $this->assertDatabaseHas('wallets', ['user_id' => $receiver->id, 'balance' => 50.00]);
    }
}