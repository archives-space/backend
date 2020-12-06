<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PictureControllerTest extends WebTestCase
{
    public function testListing()
    {
        $client = static::createClient();
        $tests  = [
            [
                "code" => 200,
            ],
            [
                "nbPerPage" => 10,
                "page"      => 1,
                "code"      => 200,
            ],
        ];

        foreach ($tests as $test) {
            $client->request('GET', '/pictures', $test);
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

    public function testDetail()
    {
        $client = static::createClient();
        $id     = $this->getId($client);
        $tests  = [
            [
                "id"   => $id,
                "code" => 200,
            ],
            [
                "id"   => 'toto',
                "code" => 400,
            ],
        ];
        foreach ($tests as $test) {
            $client->request('GET', '/pictures/' . $test['id']);
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

    private function getId($client)
    {
        $client->request('GET', '/pictures', [
            "nbPerPage" => 1,
            "page"      => 1,
        ]);
        return json_decode($client->getResponse()->getContent(), true)['data'][0]['id'];
    }
}