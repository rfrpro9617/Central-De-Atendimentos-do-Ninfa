<?php
// Os nomes dos métodos do controller devem corresponder aos nomes das rotas definidas no TicketsRouter. 
// Por exemplo, a rota 'index' chama o método index() do controller, 'create' chama create(), e assim por diante. Se uma rota for chamada que não exista no controller, o método notFound() será executado, retornando um erro 404.
class TicketsController
{
  private TicketsService $service;

  public function __construct(mysqli $db, CurrentUserDTO $user, array $mailConfig)
  {
    // TODO: adicionar injeção numa V2
    $this->service = new TicketsService($db, $user, $mailConfig);
  }

  // ##### Helpers #####
  private function redirect(string $url, int $code = 303): void
  {
    header("Location: {$url}", true, $code);
    exit;
  }

  // o método flash é usado para mostrar alertas de sucesso ou erro para o usuário.
  // Ele armazena a mensagem na sessão, que pode ser recuperada e exibida na próxima requisição.
  private function flash(string $key, mixed $value): void
  {
    SessionManager::flash($key, $value);
  }

  private function getUploadDir(): string
  {
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'atendimentos';
  }

  private function uploadFile(array $file = null): ?string
  {
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
      return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
      throw new ValidationExceptionHelper(['arquivo' => 'Falha no upload do arquivo.']);
    }

    if (($file['size'] ?? 0) > 10 * 1024 * 1024) {
      throw new ValidationExceptionHelper(['arquivo' => 'O arquivo deve ter no máximo 10MB.']);
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
      finfo_close($finfo);
    }

    $allowedMimeTypes = [
      'image/png',
      'image/jpeg',
      'application/pdf',
      'application/zip',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
      throw new ValidationExceptionHelper(['arquivo' => 'Tipo de arquivo não permitido.']);
    }

    $uploadDir = $this->getUploadDir();
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
      throw new RuntimeException('Não foi possível criar a pasta de upload.');
    }

    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $fileName = sprintf('%s_%s', bin2hex(random_bytes(8)), $safeName);
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
      throw new RuntimeException('Falha ao salvar o arquivo enviado.');
    }

    return 'uploads/atendimentos/' . $fileName;
  }

  private function uploadFiles(array $files = null): array
  {
    $uploaded = [];
    if (empty($files)) {
      return $uploaded;
    }

    // multiple files from input name="arquivo[]"
    if (is_array($files['name'])) {
      $count = count($files['name']);
      for ($i = 0; $i < $count; $i++) {
        $file = [
          'name' => $files['name'][$i] ?? null,
          'type' => $files['type'][$i] ?? null,
          'tmp_name' => $files['tmp_name'][$i] ?? null,
          'error' => $files['error'][$i] ?? UPLOAD_ERR_NO_FILE,
          'size' => $files['size'][$i] ?? 0,
        ];

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
          continue;
        }

        $uploadedPath = $this->uploadFile($file);
        if ($uploadedPath !== null) {
          $uploaded[] = $uploadedPath;
        }
      }
    } else {
      $path = $this->uploadFile($files);
      if ($path !== null) {
        $uploaded[] = $path;
      }
    }

    return $uploaded;
  }
  // ##### End Helpers #####

  public function index(): void
  {
    $request = Request::capture();
    $filters = [
      'responsavel' => $request->input('responsavel', ''),
      'status' => $request->input('status', ''),
      'prioridade' => $request->input('prioridade', ''),
    ];

    $tickets = $this->service->getRequests($filters);
    $responsibles = $this->service->getResponsibles();
    $statuses = $this->service->getStatuses();
    $priorities = $this->service->getPriorities();
    $selectedFilters = $filters;

    require __DIR__ . '/../../views/administrativo/atendimentoAoUsuarioNinfa/atendimentos/solicitacoesEmAndamento.php';
  }

  public function create(): void
  {
    $responsibles = $this->service->getResponsibles();
    $priorities = $this->service->getPriorities();
    $csrfToken = Csrf::token();

    require __DIR__ . '/../../views/administrativo/atendimentoAoUsuarioNinfa/atendimentos/lancarNovaSolicitacao.php';
  }

  public function store(): void
  {
    $request = Request::capture();

    if (!$request->isPost() || !Csrf::validate((string) $request->input('csrf_token', ''))) {
      $this->flash('error', 'Requisição inválida.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.create');
    }

    try {
      $validator = new StoreSolicitacaoValidator();
      $validated = $validator->validate($request->all());
      $uploaded = $this->uploadFiles($_FILES['arquivo'] ?? null);
      $validated['arquivo'] = empty($uploaded) ? null : implode(';', $uploaded);
      $payload = StoreAtendimentoDTO::fromArray($validated);

      $this->service->createRequest($payload);
      OldInputHelper::clear();

      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    } catch (ValidationExceptionHelper $e) {
      FlashHelper::set('errors', $e->errors);
      OldInputHelper::set($request->all());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.create');
    } catch (Throwable $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Erro ao criar solicitação.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.create');
    }
  }

  private function buildOldData(array $ticket, array $old): array
  {
    if (!empty($old) || $ticket['id_status'] !== StatusConstants::FINALIZADO) {
      return $old;
    }

    return [
      'executor' => $ticket['executor'] ?? '',
      'observacao' => $ticket['obs'] ?? '',
      'procedimento' => array_filter(
        array_map('trim', explode(';', $ticket['procedimento'] ?? '')),
        fn($item) => $item !== ''
      ),
    ];
  }

  public function show(): void
  {

    $request = Request::capture();
    $id = (int) $request->input('id', 0);

    try {
      $ticketModel = $this->service->getTicketById($id);
      $ticket = $ticketModel->toArray();
      $isTicketOwner = $this->service->canReturnToTechnician($ticketModel);
      $canCancel = $this->service->canCancelTicket($ticketModel);
      $timeline = $this->service->getTicketTimeline($ticketModel->codigo);
    } catch (TicketNotFoundException $e) {
      $this->flash('error', $e->getMessage());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    } catch (DomainException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Erro ao carregar solicitação.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    }

    $executors = $this->service->getExecutors();
    $procediments = $this->service->getProcedures();
    $canStart = $this->service->isExecutor();
    $csrfToken = Csrf::token();

    $old = $this->buildOldData($ticket, $old ?? []);

    require __DIR__ . '/../../views/administrativo/atendimentoAoUsuarioNinfa/atendimentos/executar.php';
  }

  private function validateFinalizeRequest(Request $request): void
  {
    if (!$request->isPost() || !Csrf::validate((string) $request->input('csrf_token', ''))) {
      $this->flash('error', 'Requisição inválida.');
      $this->redirect(
        'ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0)
      );
    }
  }

  public function finalize(): void
  {
    $request = Request::capture();

    $this->validateFinalizeRequest($request);

    try {
      $validator = new FinalizarAtendimentoValidator();
      $validated = $validator->validate($request->all());
      $payload = FinalizeAtendimentoDTO::fromArray($validated);

      $this->service->finalizeTicket($payload);
      OldInputHelper::clear();

      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    } catch (ValidationExceptionHelper $e) {
      FlashHelper::set('errors', $e->errors);
      OldInputHelper::set($request->all());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    } catch (TicketNotFoundException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Solicitação não encontrada.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    } catch (TicketPermissionException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', $e->getMessage());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    } catch (DomainException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', $e->getMessage());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    } catch (Throwable $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Erro ao finalizar atendimento.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    }
  }

  public function start(): void
  {
    $request = Request::capture();

    if (!$request->isPost() || !Csrf::validate((string) $request->input('csrf_token', ''))) {
      $this->flash('error', 'Requisição inválida.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    }

    try {
      $id = (int) $request->input('cod', 0);
      $this->service->start($id);
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . $id);
    } catch (TicketNotFoundException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Solicitação não encontrada.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    } catch (TicketPermissionException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', $e->getMessage());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    } catch (DomainException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Erro ao iniciar atendimento.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    }
  }

  public function requestInformation(): void
  {
    $request = Request::capture();

    if (!$request->isPost() || !Csrf::validate((string) $request->input('csrf_token', ''))) {
      $this->flash('error', 'Requisição inválida.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    }

    $this->service->requestUserInformation(
      (int) $request->input('cod', 0),
      (string) $request->input('mensagem', '')
    );

    $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
  }

  public function close(): void
  {
    $request = Request::capture();

    if (!$request->isPost() || !Csrf::validate((string) $request->input('csrf_token', ''))) {
      $this->flash('error', 'Requisição inválida.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    }

    try {
      $validator = new CancelarAtendimentoValidator();
      $validator->validate($request->all());

      $this->service->close(
        (int) $request->input('cod', 0),
        (string) $request->input('mensagem', '')
      );

      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    } catch (ValidationExceptionHelper $e) {
      FlashHelper::set('errors', $e->errors);
      OldInputHelper::set($request->all());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    } catch (TicketNotFoundException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Solicitação não encontrada.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.index');
    } catch (TicketPermissionException | InvalidTicketOperationException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', $e->getMessage());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    } catch (DomainException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', $e->getMessage());
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    } catch (Throwable $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Erro ao cancelar atendimento.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    }
  }

  public function returnToTechnician(): void
  {
    $request = Request::capture();

    if (!$request->isPost() || !Csrf::validate((string) $request->input('csrf_token', ''))) {
      $this->flash('error', 'Requisição inválida.');
      $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
    }

    try {
      $this->service->returnToTechnician(
        (int) $request->input('cod', 0),
        (string) $request->input('mensagem', '')
      );
    } catch (TicketNotFoundException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Solicitação não encontrada.');
    } catch (TicketPermissionException | InvalidTicketOperationException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', $e->getMessage());
    } catch (DomainException $e) {
      Logger::error($e->getMessage());
      $this->flash('error', $e->getMessage());
    } catch (Throwable $e) {
      Logger::error($e->getMessage());
      $this->flash('error', 'Erro ao retornar o chamado para o técnico.');
    }

    $this->redirect('ninfa.php?page=atendimentos&action=tickets.show&id=' . (int) $request->input('cod', 0));
  }

  public function notFound(): void
  {
    http_response_code(404);
    echo 'Ação não encontrada.';
  }
}
