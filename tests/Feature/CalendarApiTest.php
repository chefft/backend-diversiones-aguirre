<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_returns_available_occupied_and_unavailable_blocks(): void
    {
        config([
            'calendar.slot_minutes' => 60,
            'calendar.availability_start' => '10:00',
            'calendar.availability_end' => '18:00',
            'calendar.day_window_start' => '08:00',
            'calendar.day_window_end' => '20:00',
        ]);

        $user = User::factory()->create();
        $game = Game::create([
            'name' => 'Montana VR',
            'slug' => 'montana-vr',
            'description' => 'Prueba',
            'price_per_hour' => 200,
            'status' => 'active',
        ]);

        Reservation::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'start_date' => '2026-04-20 11:00:00',
            'end_date' => '2026-04-20 12:00:00',
            'total_price' => 200,
            'status' => 'confirmed',
        ]);

        $response = $this->getJson('/api/calendario?inicio=2026-04-20&fin=2026-04-20&game_id=' . $game->id . '&intervalo=60');

        $response->assertOk();
        $response->assertJsonPath('0.fecha', '2026-04-20');

        $blocks = collect($response->json('0.bloques'))->keyBy(fn (array $block) => $block['inicio'] . '-' . $block['fin']);

        $this->assertSame('no_disponible', $blocks['09:00-10:00']['estado']);
        $this->assertSame('disponible', $blocks['10:00-11:00']['estado']);
        $this->assertSame('ocupado', $blocks['11:00-12:00']['estado']);
        $this->assertSame('disponible', $blocks['12:00-13:00']['estado']);
        $this->assertSame('no_disponible', $blocks['18:00-19:00']['estado']);
    }

    public function test_calendar_requires_date_range(): void
    {
        $response = $this->getJson('/api/calendario');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['inicio', 'fin']);
    }
}
