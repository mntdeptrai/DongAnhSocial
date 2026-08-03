<?php

namespace App\Helpers;

class TextHelper
{
    /**
     * Auto linkify plain text (convert URLs, emails, and phone numbers into clickable HTML <a> links)
     *
     * @param string|null $text
     * @return string
     */
    public static function linkify(?string $text): string
    {
        if (!$text) {
            return '';
        }

        // 1. Escape HTML special chars to prevent XSS injection
        $escaped = e($text);

        // 2. Auto linkify URLs starting with http://, https://, or www.
        $patternUrl = '/\b(?:https?:\/\/|www\.)[^\s<]+[^\s<.,:;"\')\]]/i';
        $escaped = preg_replace_callback($patternUrl, function ($matches) {
            $url = $matches[0];
            $href = preg_match('/^https?:\/\//i', $url) ? $url : 'http://' . $url;
            return '<a href="' . $href . '" target="_blank" rel="noopener noreferrer" class="post-auto-link" style="color: #2563eb; text-decoration: underline; word-break: break-all; font-weight: 500;">' . $url . '</a>';
        }, $escaped);

        // 3. Auto linkify Email addresses
        $patternEmail = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}\b/';
        $escaped = preg_replace_callback($patternEmail, function ($matches) {
            $email = $matches[0];
            return '<a href="mailto:' . $email . '" class="post-auto-link" style="color: #2563eb; text-decoration: underline; font-weight: 500;">' . $email . '</a>';
        }, $escaped);

        // 4. Auto linkify Vietnamese Phone numbers (e.g. 038 4441646, 038.444.1646, 0912345678)
        $patternPhone = '/(?<=^|\s|:)(0[35789][0-9]{1,2}[\s.-]?[0-9]{3,4}[\s.-]?[0-9]{3,4})(?=$|\s|<|\.|,)/';
        $escaped = preg_replace_callback($patternPhone, function ($matches) {
            $phoneRaw = $matches[0];
            $phoneClean = preg_replace('/[^\d+]/', '', $phoneRaw);
            if (strlen($phoneClean) >= 9 && strlen($phoneClean) <= 11) {
                return '<a href="tel:' . $phoneClean . '" class="post-auto-link" style="color: #2563eb; text-decoration: underline; font-weight: 500;">' . $phoneRaw . '</a>';
            }
            return $phoneRaw;
        }, $escaped);

        return $escaped;
    }
}
