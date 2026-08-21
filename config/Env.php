<?php

/**
 * Carrega variáveis de ambiente a partir de um arquivo .env
 *
 * Exemplo de conteúdo do arquivo:
 * APP_NAME=MeuProjeto
 * DB_HOST=localhost
 */
function loadEnv(string $path): void
{
  // Verifica se o arquivo existe.
  // Caso não exista, encerra a função sem gerar erro.
  if (!is_file($path)) {
    return;
  }

  // Lê todas as linhas do arquivo.
  // FILE_IGNORE_NEW_LINES remove as quebras de linha.
  // FILE_SKIP_EMPTY_LINES ignora linhas vazias.
  $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

  // Percorre cada linha do arquivo.
  foreach ($lines as $line) {

    // Remove espaços extras no início e fim da linha.
    $line = trim($line);

    // Ignora linhas vazias ou comentários iniciados por "#".
    if ($line === '' || str_starts_with($line, '#')) {
      continue;
    }

    // Divide a linha em nome e valor usando "=" como separador.
    // O limite 2 garante que apenas o primeiro "=" seja usado.
    // Exemplo:
    // DB_HOST=localhost
    // Resultado:
    // $name = "DB_HOST"
    // $value = "localhost"
    [$name, $value] = array_map(
      'trim',
      explode('=', $line, 2) + ['', '']
    );

    // Se o nome da variável estiver vazio, ignora a linha.
    if ($name === '') {
      continue;
    }

    // Remove espaços e aspas simples ou duplas ao redor do valor.
    // Exemplo:
    // APP_NAME="Meu Sistema"
    // Resultado:
    // Meu Sistema
    $value = trim($value, " \t\n\r\0\x0B\"'");

    // Define a variável no ambiente do PHP.
    putenv("{$name}={$value}");

    // Também disponibiliza nos arrays globais.
    $_ENV[$name] = $value;
    $_SERVER[$name] = $value;
  }
}

/**
 * Recupera uma variável de ambiente.
 *
 * Exemplo:
 * env('DB_HOST');
 * env('DB_PORT', 3306);
 */
function env(string $key, $default = null)
{
  // Busca a variável no ambiente.
  $value = getenv($key);

  // Se não existir, retorna o valor padrão informado.
  if ($value === false) {
    return $default;
  }

  // Caso exista, retorna o valor encontrado.
  return $value;
}
