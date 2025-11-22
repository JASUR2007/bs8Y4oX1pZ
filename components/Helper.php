<?php

    namespace app\components;

    class Helper
    {
        public static function maskIp($ip)
        {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $parts = explode('.', $ip);
                $parts[2] = '**';
                $parts[3] = '**';
                return implode('.', $parts);
            }

            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                $parts = explode(':', $ip);
                for ($i = 4; $i < 8; $i++) {
                    $parts[$i] = '**';
                }
                return implode(':', $parts);
            }

            return $ip;
        }
    }
