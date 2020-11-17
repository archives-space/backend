<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{
    /**
     * @dataProvider provideUrls
     */
    public function testSomething($url, $method = 'GET', $code = 200)
    {
        $client  = static::createClient();
        $crawler = $client->request($method, $url);
        $this->assertResponseStatusCodeSame($code);
    }

    public function provideUrls()
    {
        return [
            ['/register', 'POST', 400],
            ['/users', 'GET', 200],
            ['/users/monsuperid', 'PUT', 400],
            ['/users/monsuperid', 'GET', 400],
            ['/users/monsuperid', 'DELETE', 400],
            ['/users/monsuperid/password', 'POST', 400],
        ];
    }
}