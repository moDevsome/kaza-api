<?php

namespace Api\Controller\Business;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;
use Api\Exception\BusinessException;
use Api\Service\Technical\ResponseBuffer;
use Api\Service\Business\LocationAreaObjectHandler;

/**
 * CREATE, UPDATE and DELETE operations on LocationArea are not allowed for front-office users
 */
final class LocationAreaController extends AbstractController
{

    public function __construct(
        private readonly ResponseBuffer $responseBuffer,
        private readonly LocationAreaObjectHandler $handler,
        protected readonly RequestStack $requestStack,
    ) {}

    /**
     * Return a list of location area
     */
    #[Route('/location-area', name: 'api_location_area_list', methods: ['GET'])]
    public function index(): JsonResponse
    {

        return $this->responseBuffer->buildResponse($this->handler->loadList());
    }

    /**
     * Return one location area
     * @param string $id
     * @throws BusinessException
     * @return JsonResponse
     */
    #[Route('/location-area/{id}', name: 'api_location_area')]
    public function location(string $id): JsonResponse
    {
        $errorMessage = 'LocationArea (' . $id . ') not found';

        $location = $this->handler->loadOne($id);

        if ($location === null)
            throw new BusinessException(404,  $errorMessage);

        return $this->responseBuffer->buildResponse($location);
    }
}
