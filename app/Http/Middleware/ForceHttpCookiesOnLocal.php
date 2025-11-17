<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttpCookiesOnLocal
{
    public function handle(Request $request, Closure $next): Response
    {
        // Получаем ответ от приложения
        $response = $next($request);

        // Проверяем, что мы в локальном окружении
        if (app()->environment('local')) {
            // Проходимся по всем заголовкам ответа
            foreach ($response->headers->getCookies() as $cookie) {
                // For cross-site cookies, we need to maintain the SameSite=None setting
                // and handle secure flag appropriately for local development
                $sameSite = $cookie->getSameSite();
                $secure = $cookie->isSecure();
                $name = $cookie->getName();
                
                // Special handling for local development with cross-site cookies
                // We need to allow secure cookies for cross-site functionality
                // but make exceptions for certain cookies if needed
                if ($sameSite === 'none' || $sameSite === 'None') {
                    // For SameSite=None cookies, we need to be more careful
                    // In local development, we might need to adjust this based on the setup
                    // But for ngrok/https local testing, we should keep secure=true
                    $newCookie = new \Symfony\Component\HttpFoundation\Cookie(
                        $name,
                        $cookie->getValue(),
                        $cookie->getExpiresTime(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        true, // Keep secure=true for cross-site cookies
                        $cookie->isHttpOnly(),
                        $cookie->isRaw(),
                        $sameSite // Keep SameSite=None
                    );
                } else {
                    // For regular cookies in local development, set secure=false
                    $newCookie = new \Symfony\Component\HttpFoundation\Cookie(
                        $name,
                        $cookie->getValue(),
                        $cookie->getExpiresTime(),
                        $cookie->getPath(),
                        $cookie->getDomain(),
                        false, // secure = false for local development
                        $cookie->isHttpOnly(),
                        $cookie->isRaw(),
                        $cookie->getSameSite()
                    );
                }
                // Заменяем старый cookie на новый
                $response->headers->setCookie($newCookie);
            }
        }

        return $response;
    }
}