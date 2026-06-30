<?php

namespace Illuminate\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Backport of Laravel 8's TrustProxies middleware for Laravel 7.
 * Laravel 7 removed fideloper/proxy in later skeletons but did not ship this class until Laravel 8.
 */
class TrustProxies
{
    /**
     * @var array|string|null
     */
    protected $proxies;

    /**
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_AWS_ELB;

    public function handle(Request $request, Closure $next)
    {
        $request::setTrustedProxies([], $this->getTrustedHeaderNames());

        $this->setTrustedProxyIpAddresses($request);

        return $next($request);
    }

    protected function setTrustedProxyIpAddresses(Request $request)
    {
        $trustedIps = $this->proxies() ?: config('trustedproxy.proxies');

        if ($trustedIps === '*' || $trustedIps === '**') {
            return $this->setTrustedProxyIpAddressesToTheCallingIp($request);
        }

        $trustedIps = is_string($trustedIps)
                ? array_map('trim', explode(',', $trustedIps))
                : $trustedIps;

        if (is_array($trustedIps)) {
            return $this->setTrustedProxyIpAddressesToSpecificIps($request, $trustedIps);
        }
    }

    protected function setTrustedProxyIpAddressesToSpecificIps(Request $request, array $trustedIps)
    {
        $request->setTrustedProxies($trustedIps, $this->getTrustedHeaderNames());
    }

    protected function setTrustedProxyIpAddressesToTheCallingIp(Request $request)
    {
        $request->setTrustedProxies([$request->server->get('REMOTE_ADDR')], $this->getTrustedHeaderNames());
    }

    protected function getTrustedHeaderNames()
    {
        switch ($this->headers) {
            case 'HEADER_X_FORWARDED_AWS_ELB':
            case Request::HEADER_X_FORWARDED_AWS_ELB:
                return Request::HEADER_X_FORWARDED_AWS_ELB;

            case 'HEADER_FORWARDED':
            case Request::HEADER_FORWARDED:
                return Request::HEADER_FORWARDED;

            case 'HEADER_X_FORWARDED_FOR':
            case Request::HEADER_X_FORWARDED_FOR:
                return Request::HEADER_X_FORWARDED_FOR;

            case 'HEADER_X_FORWARDED_HOST':
            case Request::HEADER_X_FORWARDED_HOST:
                return Request::HEADER_X_FORWARDED_HOST;

            case 'HEADER_X_FORWARDED_PORT':
            case Request::HEADER_X_FORWARDED_PORT:
                return Request::HEADER_X_FORWARDED_PORT;

            case 'HEADER_X_FORWARDED_PROTO':
            case Request::HEADER_X_FORWARDED_PROTO:
                return Request::HEADER_X_FORWARDED_PROTO;

            default:
                return Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_AWS_ELB;
        }

        return $this->headers;
    }

    protected function proxies()
    {
        return $this->proxies;
    }
}
