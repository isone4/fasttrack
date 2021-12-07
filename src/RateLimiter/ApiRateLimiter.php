<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class ApiRateLimiter
{
    public function index(Request $request, RateLimiterFactory $anonymousApiLimiter)
    {
        $limiter = $anonymousApiLimiter->create($request->getClientIp());
        $limit = $limiter->consume();
        $headers = [
            'x-ratelimit-remaining' => $limit->getRemainingTokens(),
            'date' => $limit->getRetryAfter()->getTimestamp(),
            'x-ratelimit-limit' => $limit->getLimit()
        ];

        if (false === $limit->isAccepted()) {
            return new Response(null, Response::HTTP_TOO_MANY_REQUESTS, $headers);
        }

        $response = new Response('Too many requests');
        $response->headers->add($headers);

        return $response;
    }
}