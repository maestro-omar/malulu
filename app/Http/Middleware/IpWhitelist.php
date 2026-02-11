<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Read allowed IPs from .env directly (since config:clear can't be run)
        $allowedIps = env('ADMIN_TOOLS_ALLOWED_IPS', '');
        
        // If no IPs are configured, deny access
        if (empty($allowedIps)) {
            abort(403, 'Access denied. No IP addresses configured.');
        }

        // Parse comma-separated IPs
        $allowedIpArray = array_map('trim', explode(',', $allowedIps));
        
        // Get the client IP - use server variables directly to avoid timeout issues
        // Try multiple methods to get IP, with fallbacks
        $clientIp = $_SERVER['REMOTE_ADDR'] ?? null;
        
        // Check proxy headers if REMOTE_ADDR doesn't work
        if (!$clientIp || $clientIp === '127.0.0.1' || $clientIp === '::1') {
            $headers = [
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_REAL_IP',
                'HTTP_CLIENT_IP',
            ];
            
            foreach ($headers as $header) {
                if (!empty($_SERVER[$header])) {
                    $ips = explode(',', $_SERVER[$header]);
                    $clientIp = trim($ips[0]);
                    break;
                }
            }
        }
        
        // Fallback to request->ip() only if we still don't have an IP
        if (!$clientIp) {
            try {
                $clientIp = $request->ip();
            } catch (\Exception $e) {
                // If request->ip() fails, use REMOTE_ADDR as last resort
                $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            }
        }
        
        // Check if client IP is in the whitelist
        if (!in_array($clientIp, $allowedIpArray)) {
            abort(403, 'Access denied. Your IP address (' . $clientIp . ') is not authorized.');
        }

        return $next($request);
    }
}
