<?php

namespace App\Http\Middleware;

use App\Helper\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BanStatusMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userStatus = $request->user()->status;

        if($userStatus == 0){
            $msg =  __('thisAccHasBeenBanned') .  __('mailTo') . env('ADMIN_EMAIL', 'admin@picshare.com').   __('protestBan') ; 
            return ResponseHelper::error(message: $msg, statusCode: 403);
        }

        return $next($request);
    }
}
