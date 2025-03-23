<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

     public function handle($request, Closure $next)
{
    // 認証されているユーザーであれば通過
    if (!auth()->check()) {
        abort(403); // 未認証の場合は403エラー
    }

    return $next($request);
}
}
