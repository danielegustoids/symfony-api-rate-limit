<?php 

// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class LuckyController extends AbstractController
{
    /**
     * This route has a greedy pattern and is defined first.
     *
     * @Route("/blog/{slug}", name="blog_show")
     */
    //public function show(string $slug)
    //{
        // ...
   // }

    /**
     * This route could not be matched without defining a higher priority than 0.
     *
     * @Route("/blog/list", name="blog_list", priority=2)
     */
    //public function list()
    //{
        // ...
    //}
    public function limit(Request $request, RateLimiterFactory $anonymousApiLimiter)
    {
        // create a limiter based on a unique identifier of the client
        // (e.g. the client's IP address, a username/email, an API key, etc.)
        $limiter = $anonymousApiLimiter->create($request->getClientIp());

        // the argument of consume() is the number of tokens to consume
        // and returns an object of type Limit
        if (false === $limiter->consume(1)->isAccepted()) {
           throw new TooManyRequestsHttpException();
        }

        // you can also use the ensureAccepted() method - which throws a
        // RateLimitExceededException if the limit has been reached
        // $limiter->consume(1)->ensureAccepted();

        // ...
    }

    function __invoke(Request $request,  RateLimiterFactory $anonymousApiLimiter): Response
    {   
        $this->limit($request, $anonymousApiLimiter);

        $routeName = $request->attributes->get('_route');
        $routeParameters = $request->attributes->get('_route_params');
        $min = $request->attributes->get('min');
        $max = $request->attributes->get('max');

        // use this to get all the available attributes (not only routing ones):
       $allAttributes = $request->attributes->all();

       $response = new Response(
        json_encode(['content' => rand($min,$max), 'attr' => $allAttributes]),
        Response::HTTP_OK,
        ['content-type' => 'application/json']
        );

        return $response;
    }
}


/// https://needlify.com/post/how-to-implement-the-rate-limiter-component-on-a-symfony-5-project-ac6b0982