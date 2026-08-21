<?php

class CurrentUserDTO
{
  public string $name;
  public string $email;
  public string $department;

  public function __construct(string $name, string $email, string $department)
  {
    $this->name = $name;
    $this->email = $email;
    $this->department = $department;
  }

  public static function fromSession(array $session): self
  {
    $name = trim($session[0] ?? '');
    $email = trim($session[2] ?? '');
    $department = trim($session[5] ?? '');

    if ($name === '') {
      throw new InvalidArgumentException('Usuário não autenticado.');
    }

    return new self($name, $email, $department);
  }

  public function canViewDetails(): bool
  {
    return $this->department === 'Faculdade de Agronomia - NINFA'
      || $this->name === 'NINFA - Estagiários';
  }
}
