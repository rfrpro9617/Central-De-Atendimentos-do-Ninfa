<?php

class TicketsRouter
{
  private TicketsController $controller;

  private array $routes = [
    // Utiliza apenas GET e POST, pois as outras verbas HTTP não são amplamente suportadas por formulários HTML e não estamos adoptando uma API RESTful completa. 
    'GET' => [
      'index' => 'index',
      'create' => 'create',
      'show' => 'show',
    ],
    'POST' => [
      'store' => 'store',
      'start' => 'start',
      'request_information' => 'requestInformation',
      'return_to_technician' => 'returnToTechnician',
      'finalize' => 'finalize',
      'close' => 'close',
    ]
  ];

  public function __construct(
    mysqli $db,
    CurrentUserDTO $user,
    array $mailConfig
  ) {
    $this->controller = new TicketsController(
      $db,
      $user,
      $mailConfig
    );
  }

  public function handle(string $action): void
  {
    $method = $_SERVER['REQUEST_METHOD'];

    $controllerMethod =
      $this->routes[$method][$action]
      ?? 'notFound';

    $this->controller->$controllerMethod();
  }
}
