<?php

if (!function_exists('session_start_safe')) {
    function session_start_safe() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('mb_strlen')) {
    function mb_strlen($string, $encoding = 'UTF-8') {
        return strlen($string);
    }
}

if (!function_exists('mb_substr')) {
    function mb_substr($string, $start, $length = null, $encoding = 'UTF-8') {
        if ($length === null) {
            return substr($string, $start);
        }
        return substr($string, $start, $length);
    }
}

if (!function_exists('str_limit')) {
    function str_limit($text, $limit = 100, $suffix = '…') {
        $text = (string)$text;
        $limit = (int)$limit;

        if ($limit <= 0 || $text === '') {
            return '';
        }

        if (function_exists('mb_strimwidth')) {
            return mb_strimwidth($text, 0, $limit, $suffix, 'UTF-8');
        }

        if (strlen($text) <= $limit) {
            return $text;
        }

        $suffix = (string)$suffix;
        $suffixLength = strlen($suffix);
        $sliceLength = $limit - $suffixLength;

        if ($sliceLength < 0) {
            $sliceLength = 0;
        }

        return substr($text, 0, $sliceLength) . $suffix;
    }
}
