<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $schoolId = $request->route('school');

        // If the route model binding provides a School model, get its id
        if (is_object($schoolId) && method_exists($schoolId, 'getKey')) {
            $schoolId = $schoolId->getKey();
        }

        if (!$user || !$this->hasPermissionToSchool($user, $permission, $schoolId)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }

    protected function hasPermissionToSchool($user, $permission, $schoolId): bool
    {
        $matrix = $user->permissionBySchoolDirect();
        return isset($matrix[$permission]) && in_array($schoolId, $matrix[$permission]);
    }
}