<?php

namespace Api\Controller\Technical;

use Api\Helper\CorsHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class CorsController extends AbstractController
{
    /**
     * Handle browser preflight request
     * hhttps://fetch.spec.whatwg.org/#http-requests
     */
    public function preflight(Request $request): Response
    {

        return new Response(status: 204, headers: CorsHelper::getHeaders());
    }
}
