<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetupRequest
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, \Closure $next)
    {
        $lang = $request->header('accept-language');
        if (!in_array($lang, ['en', 'id'])) {
            $lang = 'en';
        }

        App::setLocale($lang);

        $parameter = $request->all();

        if (count($parameter) === 0) {
            return $next($request);
        }
        $request->validate([
            'data' => 'required',
        ]);

        // $data = $request->data;
        // $data['data'] = null;

        // $request->merge($data);

        return $next($request);
    }
}
