<?php

declare(strict_types=1);

namespace TynkaControlCenter\Infrastructure\Http;

use Psr\Http\Message\ResponseInterface;

/**
 * Schrijft een PSR-7 {@see ResponseInterface} weg naar de SAPI-output: statuscode,
 * headers en body. Dit is precies wat een framework's "emitter" doet — door het
 * één keer zelf te schrijven zie je dat `header()`/`http_response_code()`/`echo`
 * geen magie zijn, maar de laatste, geïsoleerde stap van de request-afhandeling.
 */
final class ResponseEmitter
{
    public function emit(ResponseInterface $response): void
    {
        http_response_code($response->getStatusCode());

        foreach ($response->getHeaders() as $name => $values) {
            // Eerste waarde vervangt een bestaande header met die naam; volgende
            // waarden worden toegevoegd (bv. meerdere Set-Cookie-headers).
            $replace = true;
            foreach ($values as $value) {
                header(sprintf('%s: %s', $name, $value), $replace);
                $replace = false;
            }
        }

        echo $response->getBody();
    }
}
