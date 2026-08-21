<?php

class Router
{
  private array $routes = [];

  public function add(string $action, string $handler): void
  {
    $this->routes[$action] = $handler;
  }

  public function dispatch(string $action): void
  {
    $handler = $this->routes[$action] ?? null;

    if (!$handler || !is_callable($handler)) {
      http_response_code(404);
      echo 'Página não encontrada.';
      return;
    }

    call_user_func($handler);
  }
}
