<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use App\Database\PortfolioDB;
use App\Models\Holding;
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
        $data = [
            'instrument' => 'AAPL-US',
            'quantity' => 50,
            'price' => 150,
        ];

        $response = $this->postJson('/api/holdings/bought', $data);

        $response->assertStatus(201); // Expecting created status
        $response->assertJson(['message' => 'Holding created successfully']);

        $this->assertDatabaseHas('holdings', [
            'instrument' => 'AAPL-US',
            'quantity' => 50,
            'price' => 150,
        ]);
    }

    /** @test */
    public function it_validates_the_request_when_data_is_missing()
    {
        $data = [
            'instrument' => 'AAPL-US',
            'quantity' => 50,
        ];

        $response = $this->postJson('/api/holdings/bought', $data);
        $response->assertStatus(422); // Unprocessable Entity due to validation failure
        $response->assertJsonValidationErrors(['price']);
    }

    /** @test */
    public function it_updates_the_holding_when_new_purchase_data_is_provided()
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

        $data = [
            'instrument' => 'AAPL-US',
            'quantity' => 30,
            'price' => 100,
        ];

        // (100*150+30*100)/ 150 => (15000 + 3000)/150 => 18000/150 => 120

        // When
        $response = $this->postJson('/api/holdings/bought', $data);
        
        // Then
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Holding updated successfully']);

        $holding->refresh();
        $this->assertEquals(300, $holding->quantity); 
        $this->assertEquals(120, $holding->price); 
    }

    /** @test */
    public function it_updates_the_holding_when_sold_data_is_provided()
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

        $data = [
            'instrument' => 'AAPL-US',
            'sold_quantity' => 50,
            'sold_price' => 160,
        ];

        $response = $this->postJson('/api/holdings/sold', $data);
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Holding updated successfully']);

        $holding->refresh();
        $this->assertEquals(50, $holding->sold_quantity); // Verify the sold quantity has been updated
        $this->assertEquals(160, $holding->selling_price); // Verify the selling price is set
    }

    /** @test */
    public function it_validates_the_request_when_sold_data_is_missing()
    {
        $data = [
            'instrument' => 'AAPL-US',
            'sell_quantity' => 50,
        ];

        $response = $this->postJson('/api/holdings/sold', $data);
        $response->assertStatus(422); // Expecting validation error for missing selling price
        $response->assertJsonValidationErrors(['selling_price']);
    }
}
