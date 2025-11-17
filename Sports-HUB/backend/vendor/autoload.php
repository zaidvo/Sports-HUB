<?php
// Simple autoloader for our application
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = dirname(__DIR__) . '/src/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Simple JWT implementation
class JWT {
    public static function encode($payload, $key, $alg = 'HS256') {
        $header = ['typ' => 'JWT', 'alg' => $alg];
        
        $headerEncoded = self::urlsafeB64Encode(json_encode($header));
        $payloadEncoded = self::urlsafeB64Encode(json_encode($payload));
        
        $signature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $key, true);
        $signatureEncoded = self::urlsafeB64Encode($signature);
        
        return $headerEncoded . "." . $payloadEncoded . "." . $signatureEncoded;
    }
    
    public static function decode($jwt, $key) {
        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new Exception('Invalid JWT');
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        $header = json_decode(self::urlsafeB64Decode($headerEncoded), true);
        $payload = json_decode(self::urlsafeB64Decode($payloadEncoded), true);
        
        $signature = self::urlsafeB64Decode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', $headerEncoded . "." . $payloadEncoded, $key, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            throw new Exception('Invalid JWT signature');
        }
        
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('JWT expired');
        }
        
        return (object) $payload;
    }
    
    private static function urlsafeB64Encode($input) {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
    
    private static function urlsafeB64Decode($input) {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }
}

// Simple FastRoute implementation
class SimpleRouter {
    private $routes = [];
    
    public function addRoute($method, $pattern, $handler) {
        $this->routes[] = ['method' => $method, 'pattern' => $pattern, 'handler' => $handler];
    }
    
    public function dispatch($method, $uri) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchRoute($route['pattern'], $uri, $matches)) {
                return [1, $route['handler'], $matches]; // FOUND
            }
        }
        return [0]; // NOT_FOUND
    }
    
    private function matchRoute($pattern, $uri, &$matches) {
        $matches = [];
        $pattern = preg_replace('/\{(\w+):\\\d\+\}/', '(\d+)', $pattern);
        $pattern = preg_replace('/\{(\w+)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $rawMatches)) {
            array_shift($rawMatches);
            $matches = ['id' => $rawMatches[0] ?? null];
            return true;
        }
        return false;
    }
}