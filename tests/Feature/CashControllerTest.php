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

    private function getCash(): string {
        // Get what is in the DB first
        $response = $this->get('/api/cash');
        $response->assertStatus(200);
        $content = $response->getContent();
        if (is_numeric($content)) {
            return $content;
        }
        
        return '0';
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
        $this->assertEquals(0, bccomp(bcadd($saved, $addition, 2), $addedValue));

        // Get & test
        $savedAddedValue = $this->getCash();
        $this->assertEquals(0, bccomp($addedValue, $savedAddedValue));

        // Withdraw 10.10
        $url = '/api/addCash?value=-' . ($addition);
        Log::debug('Url: ' . $url);
        $response = $this->post($url);
        $response->assertStatus(200);
        $substractedValue = $response->getContent();
        $this->assertEquals(0, bccomp($saved, $substractedValue));

        // Get & test
        $savedSubstractedValue = $this->getCash();
        $this->assertEquals(0, bccomp($substractedValue, $savedSubstractedValue));
    }
}
