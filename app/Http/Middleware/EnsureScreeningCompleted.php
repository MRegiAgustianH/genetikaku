<?php

namespace App\Http\Middleware;

use App\Models\ScreeningResult;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureScreeningCompleted
{
    /**
     * The session key holding the completed screening result identifier.
     */
    public const SESSION_KEY = 'screening_result_id';

    /**
     * Handle an incoming request.
     *
     * Guards the prediction flow: requests are only allowed through when the
     * session holds a `screening_result_id` that maps to an existing
     * {@see ScreeningResult}. Otherwise the user is redirected back to the
     * screening page so they can complete screening first (Req 2.4).
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $screeningResultId = $request->session()->get(self::SESSION_KEY);

        if ($screeningResultId === null || ScreeningResult::find($screeningResultId) === null) {
            return $this->redirectToScreening();
        }

        return $next($request);
    }

    /**
     * Redirect to the screening page, preferring the named route when present.
     */
    private function redirectToScreening(): Response
    {
        if (Route::has('skrining.show')) {
            return redirect()->route('skrining.show');
        }

        return redirect('/skrining');
    }
}
