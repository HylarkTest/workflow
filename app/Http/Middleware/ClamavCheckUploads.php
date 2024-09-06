<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Clamav;
use Illuminate\Http\Request;

class ClamavCheckUploads
{
    protected Clamav $clamav;

    public function __construct(Clamav $clamav)
    {
        $this->clamav = $clamav;
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     *
     * @throws \App\Exceptions\ClamavException
     */
    public function handle(Request $request, \Closure $next)
    {
        if (empty($request->allFiles())) {
            return $next($request);
        }

        foreach ($request->allFiles() as $file) {
            $this->clamav->check($file);
        }

        return $next($request);
    }
}
