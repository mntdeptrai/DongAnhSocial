<?php

namespace App\Contracts;

interface YouTubeAuthServiceInterface
{
    /**
     * Build Google YouTube OAuth authorization URL.
     *
     * @param string $redirectUri
     * @return string
     */
    public function getAuthUrl(string $redirectUri): string;

    /**
     * Exchange authorization code for tokens and persist them.
     *
     * @param string $code
     * @param string $redirectUri
     * @return array
     */
    public function handleCallback(string $code, string $redirectUri): array;
}
