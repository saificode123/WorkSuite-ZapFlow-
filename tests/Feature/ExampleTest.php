<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The root URL redirects unauthenticated users (internal CRM with no public homepage).
     * The correct assertion is assertRedirect(), not assertStatus(200).
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        // '/' redirects unauthenticated users to login — that is correct CRM behavior.
        $response->assertRedirect();
    }

}
