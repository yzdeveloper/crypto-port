<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Exception;

class CashControllerTest extends TestCase
{
    public function test_Get(): void
    {
        $response = $this->get('/api/cash');
        $response->assertStatus(200);
        $content = $response->getContent();
        $contentValue = floatval($content);
        $this->assertTrue(is_numeric($contentValue), 'Should return numeric value');
    }

    private function getCash() {
        // Get what is in the DB first
        $response = $this->get('/api/cash');
        $response->assertStatus(200);
        $content = $response->getContent();
        $cashValue = floatval($content);
        return $cashValue;
    }

    public function test_Add(): void
    {
        $saved = $this->getCash();
        $addition = 10.10;

        // Add 10.10
        $response = $this->post('/api/addCash?value=' . $addition);
        $response->assertStatus(200);
        $content = $response->getContent();
        $addedValue = floatval($content);
        $this->assertEquals($saved + $addition, $addedValue);

        // Get & test
        $savedAddedValue = $this->getCash();
        $this->assertEquals($addedValue, $savedAddedValue);

        // Withdraw 10.10
        $url = '/api/addCash?vaue=-' . (-$addition);
        Log::debug('Url: ' . $url);
        $response = $this->post($url);
        $response->assertStatus(200);
        $content = $response->getContent();
        $substractedValue = floatval($content);
        $this->assertEquals($saved, substractedValue);

        // Get & test
        $savedSubstractedValue = $this->getCash();
        $this->assertEquals($substractedValue, $savedSubstractedValue);
    }
}
