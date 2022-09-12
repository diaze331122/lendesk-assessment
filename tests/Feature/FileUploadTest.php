<?php

class FileUploadTest extends Test {

    public function testFileUpload () {
        $response = $this->post();

        $response->assertStatus(200);
    }
}