<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
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
        // Create some test data
        Holding::create([
            'instrument' => 'AAPL-US',
            'purchase_quantity' => 100,
            'purchase_price' => 150,
            'sold_quantity' => 50,
            'sold_price' => 160,
        ]);
        Holding::create([
            'instrument' => 'GOOG-US',
            'purchase_quantity' => 200,
            'purchase_price' => 1000,
            'sold_quantity' => 100,
            'sold_price' => 1200,
        ]);

        // Test filtering
        $response = $this->getJson('/api/holdings?instrument=AAPL');
        $response->assertStatus(200);
        $response->assertJsonCount(1); // Ensure only 1 result for AAPL

        // Test sorting by instrument
        $response = $this->getJson('/api/holdings?sort_by_instrument=instrument|desc');
        $response->assertStatus(200);
        $response->assertJsonFragment(['instrument' => 'GOOG-US']);
        $response->assertJsonFragment(['instrument' => 'AAPL-US']);
    }

    /** @test */
    public function it_handles_empty_filter_gracefully()
    {
        $response = $this->getJson('/api/holdings');
        $response->assertStatus(200);
        $response->assertJsonCount(2); // All records returned
    }

    /** @test */
    public function it_creates_a_new_holding_when_valid_data_is_provided()
    {
        $data = [
            'instrument' => 'AAPL-US',
            'purchase_quantity' => 50,
            'purchase_price' => 150,
        ];

        $response = $this->postJson('/api/holdings/bought', $data);

        $response->assertStatus(201); // Expecting created status
        $response->assertJson(['message' => 'Holding created successfully']);

        $this->assertDatabaseHas('holdings', [
            'instrument' => 'AAPL-US',
            'purchase_quantity' => 50,
            'purchase_price' => 150,
        ]);
    }

    /** @test */
    public function it_validates_the_request_when_data_is_missing()
    {
        $data = [
            'instrument' => 'AAPL-US',
            'purchase_quantity' => 50,
        ];

        $response = $this->postJson('/api/holdings/bought', $data);
        $response->assertStatus(422); // Unprocessable Entity due to validation failure
        $response->assertJsonValidationErrors(['purchase_price']);
    }

    /** @test */
    public function it_updates_the_holding_when_sold_data_is_provided()
    {
        $holding = Holding::create([
            'instrument' => 'AAPL-US',
            'purchase_quantity' => 100,
            'purchase_price' => 150,
            'sold_quantity' => 0,
            'sold_price' => 0,
        ]);

        $data = [
            'instrument' => 'AAPL-US',
            'sell_quantity' => 50,
            'selling_price' => 160,
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
