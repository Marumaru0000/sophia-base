<?php

namespace Revolution\Ordering\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TableMiddleware
{
    /**
     * @param  Request  $request
     * @param  Closure  $next
     *
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (empty($request->table)) {
            return redirect()->route('table');
        }

        return $next($request);
    }
}
