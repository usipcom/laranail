<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Laravel\Http\Middleware;

use Closure;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailObfuscatorMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Apply logic differently based on the nature of $response.
        if ($response instanceof Renderable) {
            $response = pheg()->email()->obfuscate($response->render());
        } elseif ($response instanceof Response) {
            $content  = pheg()->email()->obfuscate($response->getContent());
            $response->setContent($content);
        }

        return $response;
    }

}