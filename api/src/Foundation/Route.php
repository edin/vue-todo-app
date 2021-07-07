<?php

namespace App\Foundation;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
final class Route
{
    public array $methods = [];
    public string $pattern;
    public $handler;
    public $params = [];

    public function __construct($pattern, $methods = "GET", $handler = null)
    {
        $this->methods = (array)$methods;
        $this->pattern = $pattern;
        $this->handler = $handler;
    }

    public function setHandler($handler): void {
        $this->handler = $handler;
    }

    public static function get(string $pattern, $handler): Route
    {
        return new Route($pattern, "GET", $handler);
    }

    public static function post(string $pattern, $handler): Route
    {
        return new Route($pattern, "POST", $handler);
    }

    public static function put(string $pattern, $handler): Route
    {
        return new Route($pattern, "PUT", $handler);
    }

    public static function patch(string $pattern, $handler): Route
    {
        return new Route($pattern, "PATCH", $handler);
    }

    public static function delete(string $pattern, $handler): Route
    {
        return new Route($pattern, "DELETE", $handler);
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
        if (preg_match_all("#^{$pattern}$#i", $path, $matches) ||
            preg_match_all("#^/{$pattern}$#i", $path, $matches)) {
            $params = [];
            foreach ($tags as $tag) {
                $params[$tag->name] = $matches[$tag->name][0] ?? null;
            }
            return $params;
        }
        return null;
    }
}
