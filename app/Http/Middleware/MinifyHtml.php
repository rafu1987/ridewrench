<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use voku\helper\HtmlMin;

class MinifyHtml
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);
        $contentType = (string) $response->headers->get('Content-Type');

        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if (!is_string($content) || $content === '') {
            return $response;
        }

        $htmlMin = new HtmlMin();

        $htmlMin->doOptimizeViaHtmlDomParser(true);
        $htmlMin->doRemoveComments(true);
        $htmlMin->doSumUpWhitespace(true);
        $htmlMin->doRemoveWhitespaceAroundTags(true);
        $htmlMin->doOptimizeAttributes(true);
        $htmlMin->doRemoveHttpPrefixFromAttributes(false);
        $htmlMin->doRemoveHttpsPrefixFromAttributes(false);

        $response->setContent($htmlMin->minify($content));

        return $response;
    }
}
