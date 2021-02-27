<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CatalogControllerTest extends WebTestCase
{
    public function testCreate()
    {
        $client = static::createClient();
        $tests  = [
            [
                "name"        => "name",
                "description" => "description",
                "code"        => 200,
            ],
            [
                "name" => "name",
                "code" => 200,
            ],
            [
                "name"        => "name",
                "description" => "description",
                "parent"      => [
                    "id" => "parentId",
                ],
                "code"        => 400,
            ],
            [
                "description" => "description",
                "code"        => 400,
            ],
        ];
        foreach ($tests as $test) {
            $client->request('POST', '/catalogs', [], [], [], json_encode($test));
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

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
            $client->request('GET', '/catalogs', $test);
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

    public function testEdit()
    {
        $client = static::createClient();
        $id     = $this->getId($client);
        $tests  = [
            [
                "name"        => "name",
                "description" => "description",
                "id"          => $id,
                "code"        => 200,
            ],
            [
                "name"        => "name",
                "description" => "description",
                "id"          => "toto",
                "code"        => 400,
            ],
            [
                "name"        => "name",
                "description" => "description",
                "parent"    => [
                    "id" => "parentId",
                ],
                "id"          => $id,
                "code"        => 400,
            ],
        ];
        foreach ($tests as $test) {
            $client->request('PUT', '/catalogs/' . $test['id'], [], [], [], json_encode($test));
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
            $client->request('GET', '/catalogs/' . $test['id']);
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

//    public function testDelete()
//    {
//        $client = static::createClient();
//        $id     = $this->getId($client);
//        $tests  = [
//            [
//                "id"   => $id,
//                "code" => 200,
//            ],
//            [
//                "id"   => 'toto',
//                "code" => 400,
//            ],
//        ];
//        foreach ($tests as $test) {
//            $client->request('DELETE', '/catalogs/' . $test['id']);
//            $this->assertResponseStatusCodeSame($test["code"]);
//        }
//    }

    private function getId($client)
    {
        $client->request('GET', '/catalogs', [
            "nbPerPage" => 1,
            "page"      => 1,
        ]);
        return json_decode($client->getResponse()->getContent(), true)['data'][0]['id'];
    }
}