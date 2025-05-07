<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOrOwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is an admin or the owner of the resource
        $user = Auth::user();
        $userId = $request->route('user');

        return response()->json([
            "user" => $user,
            "userId" => $userId,
        ]);

        // if (Auth::user()->role == 'admin' || Auth::user()->id == $request->route('user')) {
        //     return $next($request);
        // }

        // return response()->json([
        //     'status' => false,
        //     'message' => 'Unauthorized',
        // ])->setStatusCode(403, 'Forbidden');
    }
}
