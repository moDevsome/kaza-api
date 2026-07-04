<?php

namespace Api\Controller;

use Api\Service\Business\LodgingLoader;
use Api\Service\Technical\ResponseBuffer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class LodgingController extends AbstractController
{
    #[Route('/lodging', name: 'api_lodging')]
    public function index(): JsonResponse
    {

        return $this->responseBuffer->buildResponse($this->loader->loadList());
    }

    public function __construct(private readonly ResponseBuffer $responseBuffer, private readonly LodgingLoader $loader) {}
}
