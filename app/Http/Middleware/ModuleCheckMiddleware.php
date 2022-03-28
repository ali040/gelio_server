<?php

namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Config;

class ModuleCheckMiddleware
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
        // Check header request and determine localizaton
        if(!$request->hasHeader('moduleId'))
        {
            $errors = [];
            array_push($errors, ['code' => 'moduleId', 'message' => trans('messages.module_id_required')]);
            return response()->json([
                'errors' => $errors
            ], 403);
        }
        Config::set('module.current_module_id', $request->header('moduleId'));
        return $next($request);
    }
}
