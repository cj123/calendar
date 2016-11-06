<?php

namespace CalendarBundle\Controller;

use CalendarBundle\Defaults\OptionMap as DefaultOptionMap;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package CalendarBundle\Controller
 * @author Callum Jones <cj@icj.me>
 */
class UserController
{
    /**
     * @var DefaultOptionMap
     */
    private $defaultOptionMap;

    /**
     * UserController constructor.
     * @param DefaultOptionMap $defaultOptionMap
     */
    public function __construct(DefaultOptionMap $defaultOptionMap)
    {
        $this->defaultOptionMap = $defaultOptionMap;
    }

    /**
     * Returns user defined options
     *
     * @return Response
     */
    public function optionsAction()
    {
        // @TODO eventually this will be merged with user's settings
        $defaultOptionMap = $this->defaultOptionMap->getDefaults();

        return new JsonResponse($defaultOptionMap);
    }
}
