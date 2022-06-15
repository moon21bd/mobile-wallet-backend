<?php
namespace App\Http\Middleware;

use Platform\Admin\Middleware\XSS as XSSMiddleware;

class XSS extends XSSMiddleware
{
    /**
     * The URIs that should be excluded from XSS verification.
     *
     * @var array
     */
    protected $except_urls = [
    ];
}
