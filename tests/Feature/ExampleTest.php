<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get(route('order'));

        $response->assertStatus(302)
                 ->assertRedirect(route('table'));
    }
}
