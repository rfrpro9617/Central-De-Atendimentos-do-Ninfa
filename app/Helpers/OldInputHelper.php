<?php
class OldInputHelper
{
  public static function set(array $data): void
  {
    $_SESSION['old'] = $data;
  }

  public static function get(string $key, mixed $default = null): mixed
  {
    return $_SESSION['old'][$key] ?? $default;
  }

  public static function clear(): void
  {
    unset($_SESSION['old']);
  }
}
