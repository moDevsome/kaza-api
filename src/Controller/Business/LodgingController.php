<?php

namespace Api\Controller\Business;

use Api\Exception\BusinessException;
use Api\Service\Business\LodgingLoader;
use Api\Service\Technical\ResponseBuffer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

final class LodgingController extends AbstractController
{
    #[Route('/lodging', name: 'api_lodging_list')]
    public function index(): JsonResponse
    {

        return $this->responseBuffer->buildResponse($this->loader->loadList());
    }

    #[Route('/lodging/{guid}', name: 'api_lodging')]
    public function lodging(string $guid): JsonResponse
    {
        $errorMessage = 'Lodging (' . $guid . ') not found';

        if (!ctype_alnum($guid))
            throw new BusinessException(404,  $errorMessage);

        $lodging = $this->loader->loadOne($guid);

        if ($lodging === null)
            throw new BusinessException(404,  $errorMessage);

        return $this->responseBuffer->buildResponse($lodging);
    }

    public function __construct(private readonly ResponseBuffer $responseBuffer, private readonly LodgingLoader $loader) {}
}
