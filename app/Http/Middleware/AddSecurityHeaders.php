<?php

namespace App\Http\Middleware;

use Closure;

class AddSecurityHeaders
{
    // Enumerate headers which you do not want in your application's responses.
    // Great starting point would be to go check out @Scott_Helme's:
    // https://securityheaders.com/
    private $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];
    public function handle($request, Closure $next)
    {
        $this->removeUnwantedHeaders($this->unwantedHeaderList);
        $response = $next($request);
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Feature-Policy', "camera 'none'");
        $response->headers->set('fullscreen', 'self');
        $response->headers->set('X-Forwarded-Proto', 'https');
        $response->headers->set('X-Forwarded-For', '13.235.22.62');
        $response->headers->set('microphone', 'self');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Permissions-Policy', "geolocation 'self'");
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Expect-CT', 'max-age=31536000;  report-uri="https://your.report-uri.com/r/d/ct/report"');
        $response->headers->set('Content-Security-Policy', "connect-src 'self'");
        return $response;
    }
    private function removeUnwantedHeaders($headerList)
    {
        foreach ($headerList as $header) {
            header_remove($header);
        }
    }
}
