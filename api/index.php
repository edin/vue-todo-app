<?php

final class TodoItem 
{
    public ?int $id = null;
    public string $title = "";
    public bool $completed = false;

    public function __construct(
        ?int $id,
        string $title,
        bool $completed = false)
    {
        $this->id = $id;
        $this->title = $title;
        $this->completed = $completed;
    }

    public static function fromArray(array $data): TodoItem {
        return new TodoItem(
            $data['id'] ?? null,
            $data['title'],
            (bool)$data['completed']
        );
    }
}

class DatabaseConnection 
{
    private ?PDO $db = null;

    public function __construct()
    {
    }

    private function getDb(): PDO 
    {
        if ($this->db == null) {
            $this->db = new PDO('mysql:host=localhost;dbname=todo', "root", "root", [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]); 
        }
        return $this->db;
    }

    public function execute(string $query, array $params = []) 
    {
        $command = $this->getDb()->prepare($query);
        foreach($params as $key => $value) {
            $command->bindValue($key, $value);
        }
        $command->execute();
    }

    public function lastInsertId() {
        return $this->getDb()->lastInsertId();
    }

    public function queryAll(string $query, array $params = []) 
    {
        $command = $this->getDb()->prepare($query);
        foreach($params as $key => $value) {
            $command->bindValue($key, $value);
        }
        $command->execute();
        return $command->fetchAll(\PDO::FETCH_ASSOC);
    }
}

final class TodoDatabase 
{
    private DatabaseConnection $db;

    public function __construct(DatabaseConnection $db)
    {
        $this->db = $db;
    }

    public function save(TodoItem $item): TodoItem {
        if ($item->id) {
            return $this->update($item);
        } else {
            return $this->insert($item);
        }
    }

    public function update(TodoItem $item): TodoItem {
        $this->db->execute(
            "UPDATE todos SET title = :title, completed = :completed WHERE id = :id",
            [
                "id" => $item->id,
                "title" => $item->title,
                "completed" => (int)$item->completed
            ]
        );
        return $item;
    }

    public function insert(TodoItem $item): TodoItem {
        $this->db->execute(
            "INSERT INTO todos(title, completed) VALUES(:title, :completed)",
            [
                "title" => $item->title,
                "completed" => (int)$item->completed
            ]
        );
        $item->id = $this->db->lastInsertId();
        return $item;
    }

    public function removeById(int $id): ?TodoItem {
        $item = $this->findById($id);
        if ($item) {
            $this->db->execute("DELETE FROM todos WHERE ID = :id", ["id" => $item->id]);
        }
        return $item;
    }

    public function findById(int $id): ?TodoItem {
        $items = $this->db->queryAll("SELECT * FROM todos WHERE ID = :id", ["id" => $id]);
        if (count($items) > 0) {
            return TodoItem::fromArray($items[0]);
        }
        return null;
    }

    public function findAll(): array {
        $models = [];
        $items = $this->db->queryAll("SELECT * FROM todos ORDER BY id ASC");
        
        foreach($items as $item) 
        {
            $models[] = TodoItem::fromArray($item);
        }

        return $models;
    }
}


class TodoController 
{
    private TodoDatabase $todos;
    
    public function __construct(TodoDatabase $todos)
    {
        $this->todos = $todos;
    }

    private function getModel(): ?TodoItem {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);
        return TodoItem::fromArray($data);
    }

    public function findAll() {
        return $this->todos->findAll();
    }   

    public function create() {
        $model = $this->getModel();
        return $this->todos->save($model);
    }

    public function update(?int $id) {
        $model = $this->getModel();
        $model->id = (int)$id;
        return $this->todos->save($model);
    }

    public function remove(?int $id) {
        return $this->todos->removeById($id);
    }
}

$method = $_SERVER["REQUEST_METHOD"];
$path = $_SERVER["PATH_INFO"] ?? "";
$queryString = $_SERVER["QUERY_STRING"] ?? "";

$connection = new DatabaseConnection();
$todos = new TodoDatabase($connection);
$controller = new TodoController($todos);

$items = $controller->findAll();

class Route {
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

    public static function get(string $pattern, $handler): Route {
        return new Route("GET", $pattern, $handler);
    }

    public static function post(string $pattern, $handler): Route {
        return new Route("POST", $pattern, $handler);
    }

    public static function put(string $pattern, $handler): Route {
        return new Route("PUT", $pattern, $handler);
    }

    public static function patch(string $pattern, $handler): Route {
        return new Route("PATCH", $pattern, $handler);
    }

    public static function delete(string $pattern, $handler): Route {
        return new Route("DELETE", $pattern, $handler);
    }

    public function patternLength(): int {
        return strlen($this->pattern);
    }

    public function matches(string $method, string $path): bool {
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

    private function matchPattern($pattern, $path) {
        $matches = [];
        $tags = [];
        preg_match_all("#{(.*?)}#", $pattern, $matches);
    
        foreach($matches[1] as $tag) {
            $parts = explode(":", $tag);
            $tags[] = (object)[
                'tag' => $tag,
                'name' => $parts[0],
                'pattern' => $parts[1] ?? ".*",
            ];
        }
    
        foreach($tags as $tag) {
            $pattern = str_replace("{{$tag->tag}}", "(?P<{$tag->name}>{$tag->pattern})", $pattern);
        }
    
        $matches = [];
        if (preg_match_all("#^{$pattern}$#i", $path, $matches)) {
            $params = [];
            foreach($tags as $tag) {
                $params[$tag->name] = $matches[$tag->name][0] ?? null;
            }
            return $params;
        }
        return null;
    }
}

class Router {
    public array $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
        usort($this->routes, function(Route $a, Route $b) {
            return $b->patternLength() - $a->patternLength();
        });
    }

    public function matches(string $method, string $path): ?Route {
        foreach($this->routes as $route) {
            if ($route->matches($method, $path)) {
                return $route;
            }
        }
        return null;
    }
}


$router = new Router([
    Route::get("/todos", [$controller, "findAll"]),
    Route::post("/todos", [$controller, "create"]),
    Route::put("/todos/{id:\d+}", [$controller, "update"]),
    Route::delete("/todos/{id:\d+}", [$controller, "remove"]),
]);

$route = $router->matches($method, $path);

if ($route) {
    $result = call_user_func_array(
            $route->handler, 
            $route->params
    );
    header("Content-Type: application/json");
    echo json_encode($result);
} else {
    header("Content-Type: application/json");
    echo json_encode($route);       
}

