<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use App\Database\PortfolioDB;
use App\Models\Holding;
use App\Models\Pnl;
use Tests\TestCase;

class HoldingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        PortfolioDB::ensureDB();
    }

    /** @test */
    public function it_returns_holdings_with_optional_filters_and_sorting()
    {
        // Given
        Holding::create([
            'instrument' => 'AAPL-US',
            'instrument_first' => 'AAPL',
            'instrument_second' => 'US',
            'quantity' => 100,
            'price' => 150,
        ]);
        Holding::create([
            'instrument' => 'GOOG-US',
            'instrument_first' => 'GOOG',
            'instrument_second' => 'US',
            'quantity' => 200,
            'price' => 1000,
        ]);

        // When-Then
        $response = $this->getJson('/api/holdings');
        $response->assertStatus(200);
        $response->assertJsonCount(2); // All records returned

        // When filtering-Then
        $response = $this->getJson('/api/holdings?instrument=AAPL');
        $response->assertStatus(200);
        $response->assertJsonCount(1); // Ensure only 1 result for AAPL

        // When sorting by instrument-Then
        $response = $this->getJson('/api/holdings?sort_by_instrument=instrument|desc');
        $response->assertStatus(200);
        $response->assertJsonFragment(['instrument' => 'GOOG-US']);
        $response->assertJsonFragment(['instrument' => 'AAPL-US']);
    }

    /** @test */
    public function it_creates_a_new_holding_when_valid_data_is_provided()
    {
        // Given
        $data = [
            'instrument' => 'AAPL-US',
            'quantity' => 50,
            'price' => 150,
        ];

        // When
        $response = $this->putJson('/api/holdings/bought', $data);

        // Then
        $response->assertStatus(200); 

        $this->assertDatabaseHas('holdings', [
            'instrument' => 'AAPL-US',
            'quantity' => 50,
            'price' => 150,
        ]);
    }

    /** @test */
    public function it_validates_the_request_when_price_is_missing()
    {
        $data = [
            'instrument' => 'AAPL-US',
            'quantity' => 50,
        ];

        $response = $this->putJson('/api/holdings/bought', $data);
        $response->assertStatus(422); 
        $response->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_updates_the_holding_when_new_purchase_data_is_provided()
    {
        // Given
        $holding = Holding::create([
            'instrument' => 'AAPL-US',
            'instrument_first' => 'AAPL',
            'instrument_second' => 'US',
            'quantity' => 100,
            'price' => 150,
        ]);
        Holding::create([
            'instrument' => 'GOOG-US',
            'instrument_first' => 'GOOG',
            'instrument_second' => 'US',
            'quantity' => 200,
            'price' => 1000,
        ]);

        $data = [
            'instrument' => 'AAPL-US',
            'quantity' => 300,
            'price' => 110,
        ];

        // (100*150+300*110)/ 400 => (15000 + 33000)/400 => 48000/400 => 120

        // When
        $response = $this->putJson('/api/holdings/bought', $data);
        
        // Then
        $response->assertStatus(200);

        $holding->refresh();
        $this->assertEquals(0, bccomp(400, $holding->quantity, 9), 0, $holding->quantity . ' is not 400'); 
        $this->assertEquals(0, bccomp(120, $holding->price, 9), 0, $holding->price . ' is not 120'); 
    }

    /** @test */
    public function it_updates_the_holding_when_sold_data_is_provided()
    {
        // Given
        $pnl = Pnl::query()->first();
        $old_pnl = is_null($pnl) ? '0' : $pnl->value; 
        $expected_pnl = bcadd($old_pnl, 500); // 50 * (160 - 150) = 500

        $holding = Holding::create([
            'instrument' => 'AAPL-US',
            'instrument_first' => 'AAPL',
            'instrument_second' => 'US',
            'quantity' => 100,
            'price' => 150,
        ]);
        Holding::create([
            'instrument' => 'GOOG-US',
            'instrument_first' => 'GOOG',
            'instrument_second' => 'US',
            'quantity' => 200,
            'price' => 1000,
        ]);

        $data = [
            'instrument' => 'AAPL-US',
            'quantity' => 50,
            'price' => 160,
        ];

        // When
        $response = $this->putJson('/api/holdings/sold', $data);

        // Then
        $response->assertStatus(200);

        $holding->refresh();
        $this->assertEquals(0, bccomp(50, $holding->quantity, 9), $holding->quantity . ' is not 50'); 
        $this->assertEquals(0, bccomp(150, $holding->price, 9), $holding->price . ' is not 150'); 

        // bonus: checking PnL updated
        $pnl = Pnl::query()->first();
        $this->assertEquals(0, bccomp($expected_pnl, $pnl->value, 9), 'Expected pnl is ' . $expected_pnl . ' but it is ' . $pnl->value); 
    }

    /** @test */
    public function it_validates_the_request_when_sold_data_is_missing()
    {
        $data = [
            'instrument' => 'AAPL-US',
            'quantity' => 50,
        ];

        $response = $this->putJson('/api/holdings/sold', $data);
        $response->assertStatus(422); 
        $response->assertJsonValidationErrors(['price']);
    }
}
