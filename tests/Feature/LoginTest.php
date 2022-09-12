<?php

class LoginTest extends Test {

    public function testLogin () {
        $response = $this->post();

        $response->assertStatus(200);
    }
}