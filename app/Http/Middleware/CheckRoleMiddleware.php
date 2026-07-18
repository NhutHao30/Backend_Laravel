<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRoleMiddleware
{
    /**
     * Check access permissions (Admin: 0, Staff: 1, Customer: 2)
     * You can pass multiple roles using commas: middleware('role:0,1')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::guard('api')->check()) {
            return response()->json(['error' => 'Bạn chưa đăng nhập.'], 401);
        }

        $userRole = Auth::guard('api')->user()->MAROLE;

        if (!in_array($userRole, $roles)) {
            return response()->json(['error' => 'Bạn không có quyền truy cập vào chức năng này.'], 403);
        }

        return $next($request);
    }
}
