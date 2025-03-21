<?php

namespace App\Http\Middleware;

use App\Helper\JWT;
use Illuminate\Http\Request;

class Authenticate
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @return string|null
     */
    public function handle(Request $request, \Closure $next)
    {
        $jwt = new JWT();
        $result = $jwt->authorize($request->header('Authorization'));
        if ($result->code != $result::STATUS_SUCCESS) {
            return response()->json($result, $result->status);
        }

        $result->data->allowed_roles = ((array) ((array) $result->data)['https://hasura.io/jwt/claims'])['x-hasura-allowed-roles'];

        $request->merge(['user' => (array) $result->data]);

        return $next($request);
    }
}
