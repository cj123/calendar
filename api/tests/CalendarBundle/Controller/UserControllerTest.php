<?php

namespace CalendarBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class UserControllerTest
 * @package CalendarBundle\Tests\Controller
 * @author Callum Jones <cj@icj.me>
 */
class UserControllerTest extends WebTestCase
{
    public function testOptions()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/user/options');

        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

        $options = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($options["MondayFirst"]);
    }
}
