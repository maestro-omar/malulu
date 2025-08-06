<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Entities\School;

class SchoolPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = Auth::user();
        $route = $request->route();
        $routeName = $route->getName();
        $publicRoutes = ['schools.show'];
        if ($user->isSuperadmin() || ($routeName && in_array($routeName, $publicRoutes))) {
            //ok, go on
        } else {


            $school = $request->route('school');

            // If the route model binding provides a School model, get its id
            if (is_object($school) && method_exists($school, 'getKey')) {
                $schoolId = $school->getKey();
            } elseif (is_numeric($school)) {
                $schoolId = $school;
            } else {
                $school = School::where('slug', $school)->first();
                $schoolId = $school ? $school->id : null;
            }

            if (!$user || !$user->hasPermissionToSchool($permission, $schoolId)) {
                return Inertia::render('Errors/403', [
                    'description' => 'No tienes permiso para esta acceder a esta información en esta escuela',
                ])
                    ->toResponse($request)
                    ->setStatusCode(403);
            }
        }

        return $next($request);
    }
}
