<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * When an admin is viewing the teacher panel via "Login as", block all write
 * requests (POST, PUT, PATCH, DELETE) so the admin can only view, not change
 * anything.
 */
class BlockWritesWhenImpersonatingTeacher
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('impersonate.admin_id')) {
            return $next($request);
        }

        $method = strtoupper($request->method());
        $readOnly = in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);

        if ($readOnly) {
            return $next($request);
        }

        $message = __('app.view only mode no changes allowed');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['status' => false, 'message' => $message], 403);
        }

        return redirect()->back()->with('error', $message);
    }
}
