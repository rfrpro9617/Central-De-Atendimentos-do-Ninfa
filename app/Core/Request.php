<?php

class Request
{
  private array $get;
  private array $post;
  private array $server;

  public function __construct(array $get = [], array $post = [], array $server = [])
  {
    $this->get = $this->sanitize($get);
    $this->post = $this->sanitize($post);
    $this->server = $server;
  }

  // Captura os dados da requisição atual (GET, POST e SERVER) e retorna uma instância de Request
  public static function capture(): self
  {
    return new self($_GET, $_POST, $_SERVER);
  }

  public function all(): array
  {
    return array_merge($this->get, $this->post);
  }

  public function input(string $key, mixed $default = null): mixed
  {
    $data = $this->all();
    return $data[$key] ?? $default;
  }

  public function only(array $keys): array
  {
    return array_filter($this->all(), fn($key) => in_array($key, $keys, true), ARRAY_FILTER_USE_KEY);
  }

  public function has(string $key): bool
  {
    return array_key_exists($key, $this->all());
  }

  public function isPost(): bool
  {
    return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET') === 'POST';
  }

  private function sanitize(array $data): array
  {
    return array_map(function ($value) {
      if (is_array($value)) {
        return $this->sanitize($value);
      }
      if (is_string($value)) {
        return trim(strip_tags($value));
      }
      return $value;
    }, $data);
  }
}
