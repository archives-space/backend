<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
//    public function testRegister()
//    {
//        return false;
//        $client = static::createClient();
//        $tests  = [
//            [
//                "username"   => "username" . uniqid(),
//                "password"   => "ACompleXe!p@55w0rd!",
//                "email"      => uniqid() . "email@free.fr",
//                "publicName" => "publicName",
//                "location"   => "location",
//                "biography"  => "biography",
//                "roles"      => [
//                    "ROLE_ADMIN",
//                    "ROLE_MODERATOR",
//                ],
//                "code"       => 200,
//            ],
//            [
//                "username" => "username" . uniqid(),
//                "password" => "ACompleXe!p@55w0rd!",
//                "email"    => uniqid() . "email@free.fr",
//                "code"     => 200,
//            ],
////            [
////                "username" => "username",
////                "password" => "ACompleXe!p@55w0rd!",
////                "email"    => "email@free.fr",
////                "code"     => 400,
////            ],
//            [
//                "password"   => "ACompleXe!p@55w0rd!",
//                "email"      => "email@free.fr",
//                "publicName" => "publicName",
//                "location"   => "location",
//                "biography"  => "biography",
//                "roles"      => [
//                    "ROLE_ADMIN",
//                    "ROLE_MODERATOR",
//                ],
//                "code"       => 400,
//            ],
//            [
//                "password" => "ACompleXe!p@55w0rd!",
//                "email"    => "email@free.fr",
//                "code"     => 400,
//            ],
//        ];
//        foreach ($tests as $test) {
//            $client->request('POST', '/register', [], [], [], json_encode($test));
//            $this->assertResponseStatusCodeSame($test["code"]);
//        }
//    }

    public function testListing()
    {
        $client = static::createClient();
        $tests  = [
            [
                "code" => 200,
            ],
            [
                "nbPerPage" => 10,
                "page"      => 5,
                "code"      => 200,
            ],
        ];

        foreach ($tests as $test) {
            $client->request('GET', '/users', $test);
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

//    public function testEdit()
//    {
//        return false;
//        $client = static::createClient();
//        $id     = $this->getId($client);
//        $tests  = [
//            [
//                "username"   => "username" . uniqid(),
//                "password"   => "ACompleXe!p@55w0rd!",
//                "email"      => uniqid() . "email@free.fr",
//                "publicName" => "publicName",
//                "location"   => "location",
//                "biography"  => "biography",
//                "roles"      => [
//                    "ROLE_ADMIN",
//                    "ROLE_MODERATOR",
//                ],
//                "id"         => $id,
//                "code"       => 200,
//            ],
//            [
//                "username" => "username" . uniqid(),
//                "password" => "ACompleXe!p@55w0rd!",
//                "email"    => uniqid() . "email@free.fr",
//                "id"       => $id,
//                "code"     => 200,
//            ],
//            [
//                "username" => "username",
//                "password" => "ACompleXe!p@55w0rd!",
//                "email"    => "email@free.fr",
//                "id"       => $id,
//                "code"     => 400,
//            ],
////            [
////                "password"   => "ACompleXe!p@55w0rd!",
////                "email"      => "email@free.fr",
////                "publicName" => "publicName",
////                "location"   => "location",
////                "biography"  => "biography",
////                "roles"      => [
////                    "ROLE_ADMIN",
////                    "ROLE_MODERATOR",
////                ],
////                "id"         => $id,
////                "code"       => 400,
////            ],
//            [
//                "password" => "ACompleXe!p@55w0rd!",
//                "email"    => uniqid() . "email@free.fr",
//                "id"       => $id,
//                "code"     => 200,
//            ],
//            [
//                "password" => "ACompleXe!p@55w0rd!",
//                "email"    => uniqid() . "email@free.fr",
//                "id"       => 'toto',
//                "code"     => 400,
//            ],
//        ];
//        foreach ($tests as $test) {
//            $client->request('PUT', '/users/' . $test['id'], [], [], [], json_encode($test));
//            $this->assertResponseStatusCodeSame($test["code"]);
//        }
//    }

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
            $client->request('GET', '/users/' . $test['id']);
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

    public function testDelete()
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
            $client->request('DELETE', '/users/' . $test['id']);
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

    public function testeditPassword()
    {
        $client = static::createClient();
        $id     = $this->getId($client);
        $tests  = [
            [
                "id"   => $id,
                "code" => 200,
            ],
            [
                "id"       => $id,
                "password" => 'toto',
                "code"     => 200,
            ],
            [
                "id"   => 'toto',
                "code" => 400,
            ],
        ];
        foreach ($tests as $test) {
            $client->request('POST', '/users/' . $test['id'] . '/password');
            $this->assertResponseStatusCodeSame($test["code"]);
        }
    }

    private function getId($client)
    {
        $client->request('GET', '/users', [
            "nbPerPage" => 1,
            "page"      => 1,
        ]);
        return json_decode($client->getResponse()->getContent(), true)['data'][0]['id'];
    }
}