<?php

namespace App\Foundation;

final class Route
{
    public array $methods = [];
    public string $pattern;
    public $handler;
    public $params = [];

    public function __construct($methods, $pattern, $handler)
    {
        $this->methods = (array)$methods;
        $this->pattern = $pattern;
        $this->handler = $handler;
    }

    public static function get(string $pattern, $handler): Route
    {
        return new Route("GET", $pattern, $handler);
    }

    public static function post(string $pattern, $handler): Route
    {
        return new Route("POST", $pattern, $handler);
    }

    public static function put(string $pattern, $handler): Route
    {
        return new Route("PUT", $pattern, $handler);
    }

    public static function patch(string $pattern, $handler): Route
    {
        return new Route("PATCH", $pattern, $handler);
    }

    public static function delete(string $pattern, $handler): Route
    {
        return new Route("DELETE", $pattern, $handler);
    }

    public function patternLength(): int
    {
        return strlen($this->pattern);
    }

    public function matches(string $method, string $path): bool
    {
        if (!in_array($method, $this->methods)) {
            return false;
        }
        $params = $this->matchPattern($this->pattern, $path);
        if ($params === null) {
            return false;
        }
        $this->params = $params;
        return true;
    }

    private function matchPattern($pattern, $path)
    {
        $matches = [];
        $tags = [];
        preg_match_all("#{(.*?)}#", $pattern, $matches);

        foreach ($matches[1] as $tag) {
            $parts = explode(":", $tag);
            $tags[] = (object)[
                'tag' => $tag,
                'name' => $parts[0],
                'pattern' => $parts[1] ?? ".*",
            ];
        }

        foreach ($tags as $tag) {
            $pattern = str_replace("{{$tag->tag}}", "(?P<{$tag->name}>{$tag->pattern})", $pattern);
        }

        $matches = [];
        if (preg_match_all("#^{$pattern}$#i", $path, $matches)) {
            $params = [];
            foreach ($tags as $tag) {
                $params[$tag->name] = $matches[$tag->name][0] ?? null;
            }
            return $params;
        }
        return null;
    }
}
