<?php

namespace App\Controller;

use App\Model\ApiResponse\ApiResponse;
use App\Utils\Response\Errors;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ErrorsController
 * @package App\Controller
 * @Route(defaults={"_format"="json"})
 */
class ErrorsController extends AbstractController
{

    /**
     * @Route("/errors", methods={"GET"}, name="LIST_ERRORS")
     */
    public function errors(): Response
    {
        $errors = Errors::parseConstants();
        $response = (new ApiResponse())->setData($errors);
        return $response->getResponse();
    }
}