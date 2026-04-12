<?php

namespace App\Http\Middleware;

use App\Models\PhotoboothSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $sessionId = $request->input('session_id')
            ?? $request->query('sessionId')
            ?? $request->route('sessionId');

        if (!$sessionId) {
            return response()->json([
                'success' => false,
                'message' => 'Session ID is required',
            ], 400);
        }

        // Check if session exists
        $session = PhotoboothSession::where('session_id', $sessionId)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session ID',
            ], 404);
        }

        // Update last activity
        $session->touch('last_activity');

        // Add session to request
        $request->merge(['photobooth_session' => $session]);

        return $next($request);
    }
}
