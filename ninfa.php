<?php
require_once __DIR__ . '/app/bootstrap.php';
$config = require __DIR__ . '/config/app.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('default_charset', 'UTF-8');
if (!ini_get('register_globals')) {
  $superglobals = array(
    $_SERVER,
    $_ENV,
    $_FILES,
    $_COOKIE,
    $_POST,
    $_GET
  );
  if (isset($_SESSION)) {
    array_unshift($superglobals, $_SESSION);
  }
  foreach ($superglobals as $superglobal) {
    extract($superglobal, EXTR_SKIP);
  }
}

SessionManager::start();

$loginError = '';
$loginValues = ['name' => ''];

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
  SessionManager::destroy();
  header('Location: ninfa.php?action=login');
  exit;
}

if (isset($_GET['token'])) {
  $secret = env('HMAC_SECRET');

  $payload = json_decode(base64_decode($_GET['token']), true);

  if (!$payload) {
    header('Location: ninfa.php?action=login');
    exit;
  }

  $check = hash_hmac(
    'sha256',
    json_encode($payload['user']) . '|' . $payload['exp'],
    $secret
  );

  if (!hash_equals($check, $payload['sig'])) {
    header('Location: ninfa.php?action=login');
    exit;
  }

  if ($payload['exp'] < time()) {
    header('Location: ninfa.php?action=login');
    exit;
  }

  SessionManager::set('usuario', $payload['user']);
}

$action = $_GET['action'] ?? null;
if ($action === 'login') {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::validate($_POST['csrf_token'] ?? '')) {
      $loginError = 'Token inválido. Recarregue a página e tente novamente.';
    } else {
      $name = $_POST['name'] ?? '';
      $password = $_POST['password'] ?? '';

      $loginValues = ['name' => $name];

      if ($name === '' || $password === '') {
        $loginError = 'Nome e senha são obrigatórios.';
      } else {
        $connection = Database::getConnection();
        $stmt = $connection->prepare(
          'SELECT NomeUsu, UserName, SenhaUsu, VinculoUsu, PriviUsu, EmailUsu, PPG, DEPTO, priviDepto, UFRGS, chavePrimaria
           FROM programa
           WHERE BINARY UserName = ?
             AND UserName <> "0"
             AND SenhaUsu <> "0"
             AND VinculoUsu IN ("Funcionário", "Professor")
           ORDER BY PriviUsu DESC'
        );

        if ($stmt === false) {
          $loginError = 'Erro ao processar a autenticação.';
        } else {
          $stmt->bind_param('s', $name);
          $stmt->execute();
          $stmt->store_result();
          $stmt->bind_result($dbNome, $dbUserName, $dbSenha, $dbVinculo, $dbPrivi, $dbEmail, $dbPPG, $dbDepto, $dbPriviDepto, $dbUFRGS, $dbId);

          $authenticated = false;
          while ($stmt->fetch()) {
            $sha1Password = sha1($dbUserName . $password);
            if (hash_equals((string) $dbSenha, $password) || hash_equals((string) $dbSenha, $sha1Password)) {
              $authenticated = true;
              break;
            }
          }

          if (!$authenticated) {
            $loginError = 'Nome ou senha inválidos.';
          } else {
            SessionManager::set('usuario', [
              $dbNome,
              $dbPrivi ?? '',
              $dbEmail ?? '',
              $dbVinculo ?? '',
              $dbPPG ?? '',
              $dbDepto ?? '',
              $dbPriviDepto ?? '',
              $dbUserName ?? '',
              $dbUFRGS ?? '',
              $dbId ?? ''
            ]);
            $stmt->close();
            header('Location: ninfa.php?page=home');
            exit;
          }

          $stmt->close();
        }
      }
    }
  }

  require __DIR__ . '/views/login.php';
  exit;
}

$usuario = SessionManager::get('usuario');
if (!is_array($usuario) || empty($usuario[0])) {
  header('Location: ninfa.php?action=login');
  exit;
}

$user = CurrentUserDTO::fromSession($usuario);
$connection = Database::getConnection();
if (isset($multimidia)) {
  $name = $user->name;
  $atualizou = $name . " em " . timestamp_db_php1(date("Ymd"));
  echo "<font face='arial' size='3'>";
  echo "<font color=#009900><h3>Relatorio Multimídia - salas de aula</h3></font>";
  if (isset($atualiza)) {
    $sql = mysqli_query($connection, "UPDATE multimidia set sala='$sala', projMarca='$projmarca', projHoras='$projhoras', projContr='$projcontr', projData='$projdata', compMarca='$compmarca', compData='$compdata', amplifica='$amplifica', observa='$observa', atualizou='$atualizou' where id = '$id'");
    echo "<script language=\"Javascript\">";
    echo "alert (\"$sala atualizado com sucesso!\")";
    echo "</script>";
  }
  if (isset($atu)) {
    echo "<p align='center'><input type=\"button\" value=\"Voltar\" onclick=\"javascript:window.location='ninfa.php?multimidia=ok';\"></p>";
    $resultado = mysqli_query($connection, "SELECT * FROM multimidia where sala = '$atu'");
    while ($tupla = mysqli_fetch_row($resultado)) {
      $sala = $tupla[0];
      $projmarca = $tupla[1];
      $projhoras = $tupla[2];
      $projcontr = $tupla[3];
      $projdata = $tupla[4];
      $compmarca = $tupla[5];
      $compdata = $tupla[6];
      $amplifica = $tupla[7];
      $observa = $tupla[8];
      $id = $tupla[10];
    }
    echo "<form STYLE='margin: 0px; padding: 0px;' ENCTYPE=\"multipart/form-data\" method=\"POST\" action=\"ninfa.php?multimidia=ok\">";
    echo "<input type='hidden' name='id' value='$id'>";
    echo "<b>Sala / Prédio: </b>";
    echo "<SELECT name=\"sala\">";
    echo "<OPTION value='$sala'>$sala";
    echo "</SELECT>";
    echo "<br>Projetor - marca: <input type='text' name='projmarca' size='75' value='$projmarca' />";
    echo "<br>Projetor - horas lâmpada: <input type='text' name='projhoras' size='10' value='$projhoras' />";
    switch ($projcontr) {
      case "Sim":
        echo "<br>Projetor - controle: <input type='radio' name='projcontr' value='Sim' checked='checked'/>Sim&nbsp;&nbsp;<input type='radio' name='projcontr' value='Nao' />Não";
        break;
      case "Nao":
        echo "<br>Projetor - controle: <input type='radio' name='projcontr' value='Sim' />Sim&nbsp;&nbsp;<input type='radio' name='projcontr' value='Nao' checked='checked'/>Não";
        break;
      default:
        echo "<br>Projetor - controle: <input type='radio' name='projcontr' value='Sim' />Sim&nbsp;&nbsp;<input type='radio' name='projcontr' value='Nao' />Não";
        //   		endswitch;
    }
    echo "<br>Projetor - data da revisão: <input type='text' name='projdata' size='15' value='$projdata' />";
    echo "<br>Computador - marca: <input type='text' name='compmarca' size='50' value='$compmarca' />";
    echo "<br>Computador - data da revisão: <input type='text' name='compdata' size='15' value='$compdata' />";
    switch ($amplifica) {
      case "Sim":
        echo "<br>Amplificador: <input type='radio' name='amplifica' value='Sim' checked='checked'/>Sim&nbsp;&nbsp;<input type='radio' name='amplifica' value='Nao' />Não";
        break;
      case "Nao":
        echo "<br>Amplificador: <input type='radio' name='amplifica' value='Sim' />Sim&nbsp;&nbsp;<input type='radio' name='amplifica' value='Nao' checked='checked'/>Não";
        break;
      default:
        echo "<br>Amplificador: <input type='radio' name='amplifica' value='Sim' />Sim&nbsp;&nbsp;<input type='radio' name='amplifica' value='Nao' />Não";
        //   		endswitch;
    }
    echo "<br><table border='0'><td valign='middle'>Observação:";
    echo "<td><textarea rows='5' cols='75' name='observa'>$observa</textarea></table>";
    echo "<input type=\"submit\" name=\"atualiza\" value=\"ATUALIZAR\"></form>";
  } else {
    echo "<p align='center'><input type=\"button\" value=\"Voltar\" onclick=\"javascript:window.location='ninfa.php';\"></p>";
    echo "<center><table border='1'>";
    $sql = mysqli_query($connection, "SELECT * from multimidia order by sala asc");
    while ($tupla = mysqli_fetch_row($sql))
      echo "<tr><td>Sala / Prédio: <a href='ninfa.php?multimidia=ok&atu=$tupla[0]'>$tupla[0]</a><br>Projetor - marca: $tupla[1]<br>Projetor - horas lâmpada: $tupla[2]<br>Projetor - controle: $tupla[3]<br>Projetor - data da revisão: $tupla[4]<br>Computador - marca: $tupla[5]<br>Computador - data da revisão: $tupla[6]<br>Amplificador: $tupla[7]<br>Observação: $tupla[8]<br>Atualizado por $tupla[9]</td>";
    echo "</table>";
  }
} else if (isset($atendimento) || isset($_GET['page']) || isset($_GET['module'])) {
  $routePage = $_GET['page'] ?? $_GET['module'] ?? null;
  $routeAction = $_GET['action'] ?? null;

  if ($routePage === null) {
    if ($atendimento === 'ok') {
      $routePage = 'atendimentos';
      $routeAction = 'menu';
    } elseif ($atendimento === '1') {
      $routePage = 'atendimentos';
      $routeAction = 'create';
    } elseif ($atendimento === '2') {
      $routePage = 'atendimentos';
      $routeAction = 'index';
    } elseif ($atendimento === '3') {
      $routePage = 'atendimentos';
      $routeAction = 'getById';
    } elseif ($atendimento === '4') {
      $routePage = 'atendimentos';
      $routeAction = 'tickets';
    }
  }

  // Exibir a página home quando solicitado
  if ($routePage === 'home') {
    require __DIR__ . '/views/home.php';
    exit;
  }

  if ($routePage === 'atendimentos') {
    $router = new TicketsRouter(
      $connection,
      $user,
      $config['mail']
    );

    if ($routeAction === 'create' || $routeAction === 'tickets.create') {
      $router->handle('create');
      exit;
    }

    if ($routeAction === 'store' || $routeAction === 'tickets.store') {
      $router->handle('store');
      exit;
    }

    if ($routeAction === 'show' || $routeAction === 'tickets.show') {
      $router->handle('show');
      exit;
    }

    if ($routeAction === 'start' || $routeAction === 'tickets.start') {
      $router->handle('start');
      exit;
    }

    if ($routeAction === 'request_information' || $routeAction === 'tickets.request_information') {
      $router->handle('request_information');
      exit;
    }

    if ($routeAction === 'return_to_technician' || $routeAction === 'tickets.return_to_technician') {
      $router->handle('return_to_technician');
      exit;
    }

    if ($routeAction === 'finalize' || $routeAction === 'tickets.finalize') {
      $router->handle('finalize');
      exit;
    }

    if ($routeAction === 'close' || $routeAction === 'tickets.close') {
      $router->handle('close');
      exit;
    }

    if ($routeAction === 'index' || $routeAction === 'tickets.index') {
      if (isset($lancouservico)) {
        echo "<script language=\"Javascript\">";
        echo "alert (\"O SERVIÇO FOI LANÇADO COM SUCESSO!\")";
        echo "</script>";
      }
      $router->handle('index');
      exit;
    }

    echo "<font color=#009900><h3>Chamados</h3></font>";
    echo "<a href='ninfa.php?page=atendimentos&action=tickets.create' target='_blank' rel='noopener noreferrer'>- Lançar novo chamado</a><br><br>";
    echo "<a href='ninfa.php?page=atendimentos&action=tickets.index' target='_blank' rel='noopener noreferrer'>- Ver chamados</a><br><br>";
    echo "<p align=center><input type=\"button\" value=\"Voltar\" onclick=\"javascript:window.location='ninfa.php';\"></p>";
    exit;
  }
} else {
  echo "<font color=#009900><h3>NINFA</h3></font>";
  echo "<p><strong>Usuário:</strong> " . htmlspecialchars($user->name, ENT_QUOTES, 'UTF-8') . "</p>";
  if ($user->name === "NINFA - Estagiários" || isset($_SESSION['usuario'][1]) && $_SESSION['usuario'][1] === 'x')
    echo "<a href='ninfa.php?multimidia=ok'>- Relatório Multimídia - salas de aula</a><br><br>";
  echo "<a href='ninfa.php?page=atendimentos'>- Chamados</a><br><br>";
  echo "<p align=center><input type=\"button\" value=\"Voltar\" onclick=\"javascript:window.location='administrativo.php';\"></p>";
}

function timestamp_db_php1($date)
{
  $year   = substr($date, 0, 4);
  $month  = substr($date, 4, 2);
  $day    = substr($date, 6, 2);
  $datetime = $day . "/" . $month . "/" . $year;
  return $datetime;
}
function timestamp_db_php($date)
{
  $year   = substr($date, 0, 4);
  $month  = substr($date, 4, 2);
  $day    = substr($date, 6, 2);
  $hour   = substr($date, 8, 2);
  $minute = substr($date, 10, 2);
  $datetime = $day . "/" . $month . "/" . $year . "(" . $hour . ":" . $minute . "h)";
  return $datetime;
}
function transform($txt)
{
  $beta = array(
    "a",
    "a",
    "a",
    "a",
    "a",
    "e",
    "e",
    "e",
    "e",
    "i",
    "i",
    "i",
    "i",
    "o",
    "o",
    "o",
    "o",
    "o",
    "u",
    "u",
    "u",
    "u",
    "c",
    "A",
    "A",
    "A",
    "A",
    "A",
    "E",
    "E",
    "E",
    "E",
    "I",
    "I",
    "I",
    "I",
    "O",
    "O",
    "O",
    "O",
    "O",
    "U",
    "U",
    "U",
    "U",
    "C",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_",
    "_"
  );
  $alfa = array(
    "á",
    "à",
    "ã",
    "â",
    "ä",
    "é",
    "è",
    "ê",
    "ë",
    "í",
    "ì",
    "î",
    "ï",
    "ó",
    "ò",
    "õ",
    "ô",
    "ö",
    "ú",
    "ù",
    "û",
    "ü",
    "ç",
    "Á",
    "À",
    "Ã",
    "Â",
    "Ä",
    "É",
    "È",
    "Ê",
    "Ë",
    "Í",
    "Ì",
    "Î",
    "Ï",
    "Ó",
    "Ò",
    "Õ",
    "Ô",
    "Ö",
    "Ú",
    "Ù",
    "Û",
    "Ü",
    "Ç",
    "\"",
    "'",
    "!",
    "@",
    "#",
    "$",
    "%",
    "&",
    "*",
    "(",
    ")",
    "+",
    "}",
    "]",
    "=",
    "º",
    "§",
    "{",
    "[",
    "ª",
    "?",
    "/",
    "°",
    "<",
    ">",
    "\\",
    "|",
    ",",
    ".",
    ";",
    ":",
    "~",
    "^",
    "´",
    "`"
  );
  $gama = str_replace($alfa, $beta, $txt);
  $omega = strtoupper($gama);
  $omega = strip_tags($omega);
  $omega = trim($omega);
  return print_r($omega, true);
}
mysqli_close($connection);
