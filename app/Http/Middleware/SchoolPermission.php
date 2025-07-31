<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
            } else {
                $schoolId = $school;
            }

            if (!$user || !$this->hasPermissionToSchool($user, $permission, $schoolId)) {
                return Inertia::render('Errors/403')
                    ->toResponse($request)
                    ->setStatusCode(403);
                // abort(403, 'No tienes permiso para esta acceder a esta escuela');
            }
        }

        return $next($request);
    }

    protected function hasPermissionToSchool($user, $permission, $schoolId): bool
    {
        $matrix = $user->permissionBySchoolDirect();
        return isset($matrix[$permission]) && in_array($schoolId, $matrix[$permission]);
    }
}
