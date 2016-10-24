<?php

namespace CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CalendarController
 * @package CalendarBundle\Controller
 */
class CalendarController extends Controller
{
    /**
     * Index Action
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        return $this->render("CalendarBundle:Calendar:index.html.twig");
    }
}
