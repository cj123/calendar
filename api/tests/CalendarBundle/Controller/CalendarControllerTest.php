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

        $client->request('GET', '/calendar/options');

        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

        $options = json_decode($client->getResponse()->getContent(), true);

        $this->assertTrue($options["MondayFirst"]);
    }

    public function testMonthView()
    {
        $this->markTestSkipped("slow");
        $client = static::createClient();

        // testing with december 2016, since it's caused problems before
        $client->request('GET', '/calendar/month-view?month=12&year=2016');

        $this->assertEquals($client->getResponse()->getStatusCode(), 200);

        $data = json_decode($client->getResponse()->getContent(), true);

        foreach ($data["days"] as $day) {
            $actual = $day["events"];
            $date = sprintf("2016-12-%02d", $day["day"]);

            $client->request("GET", "/calendar/day-view?date=${date}");

            $this->assertEquals($client->getResponse()->getStatusCode(), 200);
            $data = json_decode($client->getResponse()->getContent(), true);

            $expected = count($data) > 0;

            $this->assertEquals($expected, $actual, "${date} should " . ($expected ? "" : "NOT") . "have appointments");
        }
    }
}
