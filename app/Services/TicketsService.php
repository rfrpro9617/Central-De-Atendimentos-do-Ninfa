<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class TicketsService
{
  private TicketsRepository $repository;
  private AtendimentoMailerAberto $mailerAberto;
  private AtendimentoMailerEmAndamento $mailerEmAndamento;
  private AtendimentoMailerAguardandoUsuario $mailerAguardandoUsuario;
  private AtendimentoMailerFinalizado $mailerFinalizado;
  private AtendimentoMailerCancelado $mailerCancelado;
  private CurrentUserDTO $user;

  public function __construct(mysqli $db, CurrentUserDTO $user, array $mailConfig)
  {
    $this->repository = new TicketsRepository($db);
    $mailer = new Mailer($mailConfig);

    $this->mailerAberto = new AtendimentoMailerAberto($mailer);
    $this->mailerEmAndamento = new AtendimentoMailerEmAndamento($mailer);
    $this->mailerAguardandoUsuario = new AtendimentoMailerAguardandoUsuario($mailer);
    $this->mailerFinalizado = new AtendimentoMailerFinalizado($mailer);
    $this->mailerCancelado = new AtendimentoMailerCancelado($mailer);
    $this->user = $user;
  }

  // ##### Permissões #####
  public function canViewDetails(): bool
  {
    return $this->user->canViewDetails();
  }
  // ##### End Permissões #####

  private function resolvePriorityName(StoreAtendimentoDTO $request): ?string
  {
    return $request->prioridadeId ? $this->repository->findPriorityName($request->prioridadeId) : null;
  }

  public function createRequest(StoreAtendimentoDTO $request): int
  {
    $priorityName = $this->resolvePriorityName($request);
    $payload = $request->toRepositoryPayload($this->user, $priorityName);
    $id = $this->repository->createRequest($payload);
    // Pega sempre o responsável, pois os técnicos podem abrir chamados para outros responsáveis.
    $responsable = $payload['responsavel'];
    $payload['codigo'] = $id;

    try {
      $this->addTimelineEntry($id, $responsable, 'ABERTURA', 'Chamado aberto pelo usuário.');
    } catch (Throwable $e) {
      Logger::error('Erro ao adicionar entrada na timeline: ' . $e->getMessage());
    }

    try {
      $this->mailerAberto->enviarSolicitacaoCriada(NinfaConstants::EMAIL_NINFA, $payload);
    } catch (Throwable $e) {
      Logger::error('Erro ao enviar email de solicitação para o NINFA: ' . $e->getMessage());
    }

    try {
      $responsibleEmail = $this->repository->findUserEmailByName($responsable);
      if ($responsibleEmail) {
        $this->mailerAberto->enviarSolicitacaoCriada($responsibleEmail, $payload);
      }
    } catch (Throwable $e) {
      Logger::error('Erro ao enviar email de solicitação para o responsável: ' . $e->getMessage());
    }

    return $id;
  }

  public function getTicketById(int $id): Atendimento
  {
    $data = $this->repository->findTicketById($id);

    if (empty($data)) {
      throw new TicketNotFoundException($id);
    }

    return Atendimento::fromArray($data);
  }

  public function getTicketTimeline(int $ticketId): array
  {
    return $this->repository->findTimelineEntriesByTicketId($ticketId);
  }

  private function addTimelineEntry(int $ticketId, string $author, string $type, string $message): void
  {
    $this->repository->createTimelineEntry([
      'atendimento_id' => $ticketId,
      'autor' => $author,
      'tipo' => $type,
      'mensagem' => AtendimentoHelper::transform($message),
      'created_at' => date('YmdHi'),
    ]);
  }

  public function getExecutors(): array
  {
    return $this->repository->getExecutors();
  }

  public function isExecutor(): bool
  {
    return $this->repository->userIsExecutor($this->user->name);
  }

  public function canReturnToTechnician(Atendimento $ticket): bool
  {
    return $ticket->id_status === StatusConstants::AGUARDANDO_USUARIO
      && $ticket->lancaSolicita === $this->user->name;
  }

  public function canCancelTicket(Atendimento $ticket): bool
  {
    return $ticket->id_status !== StatusConstants::FINALIZADO
      && $ticket->id_status !== StatusConstants::CANCELADO
      && $ticket->lancaSolicita === $this->user->name;
  }

  public function getProcedures(): array
  {
    return AtendimentoHelper::procedimentos();
  }

  public function getRequests(array $filters = []): array
  {
    return $this->repository->findRequests($this->user, $filters);
  }

  public function getStatuses(): array
  {
    return $this->repository->findStatuses();
  }

  public function getClosedRequests(string $order): array
  {
    return $this->repository->findClosedRequests($order, $this->user);
  }

  public function getResponsibles(): array
  {
    if ($this->canViewDetails()) {
      return $this->repository->findStaffUsers();
    }

    return $this->repository->findUserByName($this->user->name);
  }

  public function getPriorities(): array
  {
    return $this->repository->findPriorities();
  }

  public function finalizeTicket(FinalizeAtendimentoDTO $request): void
  {
    if (!$this->isExecutor()) {
      throw new TicketPermissionException('Apenas executores podem finalizar atendimentos.');
    }

    $ticket = $this->getTicketById($request->codigo);
    $this->repository->finalizeRequest($request->toRepositoryPayload($this->user));
    $this->repository->updateStatus($request->codigo, StatusConstants::FINALIZADO);
    $this->addTimelineEntry($request->codigo, $this->user->name, 'FINALIZACAO', 'Atendimento finalizado. Observações: ' . $request->observacao);

    try {
      $this->mailerFinalizado->enviarAtendimentoFinalizado(
        NinfaConstants::EMAIL_NINFA,
        $this->user->name,
        $ticket->toArray(),
        $request->observacao
      );
      $this->mailerFinalizado->enviarAtendimentoFinalizado(
        $this->user->email,
        $this->user->name,
        $ticket->toArray(),
        $request->observacao
      );
      $responsable = $ticket->responsavel;
      $responsibleEmail = $this->repository->findUserEmailByName($responsable);
      if ($responsibleEmail) {
        $this->mailerFinalizado->enviarAtendimentoFinalizado(
          $responsibleEmail,
          $this->user->name,
          $ticket->toArray(),
          $request->observacao
        );
      }
    } catch (Throwable $e) {
      Logger::error('Erro ao enviar email de finalização: ' . $e->getMessage());
    }
  }

  public function start(int $id): void
  {
    if (!$this->isExecutor()) {
      throw new TicketPermissionException('Apenas executores podem iniciar atendimentos.');
    }

    $ticket = $this->getTicketById($id);

    $this->repository->setTechnicianStarted($id, $this->user->name);

    $this->repository->updateStatus($id, StatusConstants::EM_ANDAMENTO);
    $this->addTimelineEntry($id, $this->user->name, 'INICIO', 'Atendimento iniciado pelo técnico.');

    try {
      $responsable = $ticket->responsavel;
      $responsibleEmail = $this->repository->findUserEmailByName($responsable);
      if ($responsibleEmail) {
        $this->mailerEmAndamento->enviarAtendimentoEmAndamento(
          $responsibleEmail,
          $this->user->name,
          $ticket->toArray(),
          'aberto'
        );
      }
    } catch (Throwable $e) {
      Logger::error('Erro ao enviar email de início de atendimento: ' . $e->getMessage());
    }
  }

  public function requestUserInformation(int $id, string $message): bool
  {
    $ticket = $this->getTicketById($id);
    $this->repository->updateStatus($id, StatusConstants::AGUARDANDO_USUARIO);
    // Ao solicitar informações do usuário, registramos o técnico, pois pode ter mudado desde o início do atendimento.
    $this->repository->setTechnicianStarted($id, $this->user->name);
    $this->addTimelineEntry($id, $this->user->name, 'MENSAGEM_TECNICO', $message ?: 'Solicitação de informação enviada ao usuário.');

    try {
      $responsable = $ticket->responsavel;
      $responsibleEmail = $this->repository->findUserEmailByName($responsable);
      if ($responsibleEmail) {
        $this->mailerAguardandoUsuario->enviarSolicitacaoUsuario(
          $responsibleEmail,
          $ticket->toArray(),
          $message
        );
      }

      return true;
    } catch (Throwable $e) {
      Logger::error('Erro ao enviar email aguardando usuário: ' . $e->getMessage());
      return false;
    }
  }

  public function close(int $id, string $message): void
  {
    $ticket = $this->getTicketById($id);

    if ($ticket->lancaSolicita !== $this->user->name) {
      throw new TicketPermissionException('Apenas quem abriu o chamado pode cancelar o atendimento.');
    }

    if ($ticket->id_status === StatusConstants::FINALIZADO || $ticket->id_status === StatusConstants::CANCELADO) {
      throw new InvalidTicketOperationException('Não é possível cancelar um chamado já finalizado ou cancelado.');
    }

    $this->repository->updateStatus($id, StatusConstants::CANCELADO);
    $this->addTimelineEntry($id, $this->user->name, 'ENCERRAMENTO', $message ?: 'Chamado cancelado pelo usuário.');

    try {
      $this->mailerCancelado->enviarAtendimentoCancelado(
        NinfaConstants::EMAIL_NINFA,
        $ticket->toArray(),
        $message
      );
      $technician = $ticket->tecnico_iniciou_atendimento;
      if ($technician) {
        $technicianEmail = $this->repository->findUserEmailByName($technician);
        if ($technicianEmail) {
          $this->mailerCancelado->enviarAtendimentoCancelado(
            $technicianEmail,
            $ticket->toArray(),
            $message
          );
        }
      }
      $responsable = $ticket->responsavel;
      $responsibleEmail = $this->repository->findUserEmailByName($responsable);
      if ($responsibleEmail) {
        $this->mailerCancelado->enviarAtendimentoCancelado(
          $responsibleEmail,
          $ticket->toArray(),
          $message
        );
      }
    } catch (Throwable $e) {
      Logger::error('Erro ao enviar email de cancelamento: ' . $e->getMessage());
    }
  }

  public function returnToTechnician(int $id, string $message): void
  {
    $ticket = $this->getTicketById($id);

    if (!$this->canReturnToTechnician($ticket)) {
      throw new TicketPermissionException('Apenas o usuário que abriu o chamado pode retornar para o técnico quando o atendimento estiver aguardando usuário.');
    }

    if ($ticket->id_status !== StatusConstants::AGUARDANDO_USUARIO) {
      throw new InvalidTicketOperationException('O chamado só pode ser retornado para o técnico quando estiver aguardando informações do usuário.');
    }

    $this->repository->updateStatus($id, StatusConstants::EM_ANDAMENTO);
    $this->addTimelineEntry($id, $this->user->name, 'MENSAGEM_USUARIO', $message ?: 'Usuário retornou ao técnico.');

    try {
      $technician = $ticket->tecnico_iniciou_atendimento;
      if ($technician) {
        $technicianEmail = $this->repository->findUserEmailByName($technician);
        if ($technicianEmail) {
          $this->mailerEmAndamento->enviarAtendimentoEmAndamento(
            $technicianEmail,
            $this->user->name,
            $ticket->toArray(),
            'retorno_usuario',
            $message
          );
        }
      }
    } catch (Throwable $e) {
      Logger::error('Erro ao enviar email de retorno ao técnico: ' . $e->getMessage());
    }
  }
}
