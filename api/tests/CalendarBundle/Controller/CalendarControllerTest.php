<?php

namespace CalendarBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class CalendarControllerTest
 * @package CalendarBundle\Tests\Controller
 * @author Callum Jones <cj@icj.me>
 */
class CalendarControllerTest extends WebTestCase
{
    public function testOptions()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/calendar/options');

        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

        $options = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($options["MondayFirst"]);
    }
}
