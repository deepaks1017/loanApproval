<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoanTest extends TestCase
{
    /**
     * A apply loan test.
     *
     * @return void
     */
    public function testApplyLoanDemoUser()
    {
        $response = $this->json('POST', '/api/applyloan', [
            'username' => 'demouser', 'secret' => 'Z82fG73OQ4', 
            'amount' => 50000, 'loan_tenure' => 3, 
        ]);

        $response->assertStatus(200)
                ->assertJson([
                'status' => true,
            ]);
    }

    /**
     * A approve loan test.
     *
     * @return void
     */
    public function testApproveLoan()
    {
        $response = $this->json('POST', '/api/approveloan', [
            'username' => 'admin', 'secret' => 'cuaS9D0HRe', 
            'loan_id' => 1, 
        ]);

        $response->assertStatus(200)
                ->assertJson([
                'status' => true,
            ]);
    }

    /**
     * A pay loan emi test.
     *
     * @return void
     */
    public function testPayEmi()
    {
        $response = $this->json('POST', '/api/payemi', [
            'username' => 'demouser', 'secret' => 'Z82fG73OQ4', 
            'schedule_id' => 1, 'amount' => 16667,
        ]);

        $response->assertStatus(200)
                ->assertJson([
                'status' => true,
            ]);
    }
}
