<?php
/**
 * -------------------------------------------------------------------------
 * Pengamanan CSRF & Sanitasi Input - POS Resto Java Turtle
 * -------------------------------------------------------------------------
 * File helper ini menyediakan proteksi keamanan terhadap serangan CSRF (Cross-Site
 * Request Forgery) serta fungsi-fungsi utilitas untuk menyaring (sanitasi) input
 * pengguna dari parameter POST dan GET agar bersih dari karakter berbahaya.
 */

if (!function_exists('csrf_get_token')) {
    /**
     * Mengambil atau membuat token CSRF baru di dalam sesi.
     *
     * @return string Token CSRF dalam bentuk heksadesimal 64 karakter.
     */
    function csrf_get_token(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Membuat token acak baru jika belum terdefinisi dalam sesi
        if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Menghasilkan elemen input tersembunyi (hidden input) berisi token CSRF untuk formulir HTML.
     *
     * @return string String tag HTML input.
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('csrf_validate_token')) {
    /**
     * Memvalidasi kecocokan token CSRF yang dikirimkan dengan yang tersimpan di sesi menggunakan hash_equals.
     *
     * @param mixed $token Token yang dikirim dari form/request.
     * @return bool True jika valid, False jika tidak valid.
     */
    function csrf_validate_token($token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (!is_string($sessionToken) || $sessionToken === '' || !is_string($token)) {
            return false;
        }
        // Membandingkan dengan algoritma konstan-waktu untuk menghindari serangan side-channel
        return hash_equals($sessionToken, $token);
    }
}

if (!function_exists('csrf_guard')) {
    /**
     * Melakukan intersepsi/penjagaan pada request POST. Jika token CSRF tidak valid, request digagalkan.
     */
    function csrf_guard(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $token = $_POST['csrf_token'] ?? '';
        if (!csrf_validate_token($token)) {
            http_response_code(400);
            exit('Invalid CSRF token');
        }
    }
}

if (!function_exists('csrf_require_token')) {
    /**
     * Memastikan parameter token yang diberikan valid.
     *
     * @param mixed $token
     */
    function csrf_require_token($token): void
    {
        if (!csrf_validate_token($token)) {
            http_response_code(400);
            exit('Invalid CSRF token');
        }
    }
}

if (!function_exists('sanitize_scalar_input')) {
    /**
     * Melakukan penyaringan string dari karakter-karakter kontrol ASCII non-printable yang berbahaya.
     *
     * @param mixed $value
     * @param bool $allowNewlines Apakah baris baru (\n) diperbolehkan dalam string.
     * @return string String hasil pembersihan.
     */
     function sanitize_scalar_input($value, bool $allowNewlines = false): string
     {
         $stringValue = trim((string) $value);
         // Pola ekspresi reguler untuk mendeteksi karakter kontrol ASCII
         $pattern = $allowNewlines ? '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u' : '/[\x00-\x1F\x7F]/u';
         $cleaned = preg_replace($pattern, '', $stringValue);
 
         return $cleaned === null ? '' : trim($cleaned);
     }
}

if (!function_exists('get_post_string')) {
    /**
     * Mengambil nilai parameter POST berbentuk string secara aman.
     *
     * @param string $key Kunci input post
     * @param bool $allowNewlines Apakah boleh mengandung baris baru
     * @return string
     */
    function get_post_string(string $key, bool $allowNewlines = false): string
    {
        $value = filter_input(
            INPUT_POST,
            $key,
            FILTER_UNSAFE_RAW,
            ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]
        );

        if ($value === null) {
            return '';
        }

        return sanitize_scalar_input($value, $allowNewlines);
    }
}

if (!function_exists('get_post_float')) {
    /**
     * Mengambil nilai parameter POST berbentuk desimal (float) secara aman.
     *
     * @param string $key
     * @return float
     */
    function get_post_float(string $key): float
    {
        $value = filter_input(
            INPUT_POST,
            $key,
            FILTER_SANITIZE_NUMBER_FLOAT,
            ['flags' => FILTER_FLAG_ALLOW_FRACTION]
        );

        if ($value === null || $value === false || $value === '') {
            return 0.0;
        }

        return (float) $value;
    }
}

if (!function_exists('get_post_int')) {
    /**
     * Mengambil nilai parameter POST berbentuk integer (angka bulat) secara aman.
     *
     * @param string $key
     * @return int
     */
    function get_post_int(string $key): int
    {
        $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT);
        if ($value === null || $value === false) {
            return 0;
        }

        return (int) $value;
    }
}

if (!function_exists('get_get_param')) {
    /**
     * Mengambil nilai parameter GET berbentuk string secara aman.
     *
     * @param string $key
     * @return string
     */
    function get_get_param(string $key): string
    {
        $value = filter_input(
            INPUT_GET,
            $key,
            FILTER_UNSAFE_RAW,
            ['flags' => FILTER_FLAG_NO_ENCODE_QUOTES]
        );

        if ($value === null) {
            return '';
        }

        return sanitize_scalar_input($value);
    }
}
?>
