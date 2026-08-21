<?php

class TicketsRepository
{
  private mysqli $db;

  public function __construct(mysqli $db)
  {
    $this->db = $db;
  }

  public function createRequest(array $data): int
  {
    $stmt = $this->db->prepare(
      'INSERT INTO atendimentos (responsavel, demanda, patrimonio, descricao, dataSolicita, lancaSolicita, prioridade_id, id_status, arquivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $arquivo = $data['arquivo'] ?? null;

    $stmt->bind_param(
      'ssssssiss',
      $data['responsavel'],
      $data['demanda'],
      $data['patrimonio'],
      $data['descricao'],
      $data['dataSolicita'],
      // Responsável, pois técnicos podem abrir chamados para outros responsáveis, e isso impacta na lógica de permissões para retornar ao técnico ou cancelar o chamado.
      $data['responsavel'],
      $data['prioridade_id'],
      $data['id_status'],
      $arquivo
    );

    if (!$stmt->execute()) {
      throw new RuntimeException('Falha ao criar solicitação: ' . $stmt->error);
    }

    return $stmt->insert_id;
  }

  public function findStaffUsers(): array
  {
    $result = $this->db->query(
      "SELECT NomeUsu FROM programa WHERE VinculoUsu IN ('Professor', 'Funcionário') ORDER BY NomeUsu ASC"
    );

    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function findUserByName(string $nome): array
  {
    $stmt = $this->db->prepare(
      'SELECT NomeUsu 
       FROM programa 
       WHERE NomeUsu = ?
         AND SenhaUsu <> "0"
         AND UserName <> "0"
       ORDER BY NomeUsu ASC'
    );

    $stmt->bind_param('s', $nome);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }

  public function findUserEmailByName(string $nome): ?string
  {
    $stmt = $this->db->prepare(
      'SELECT EmailUsu FROM programa WHERE NomeUsu = ? LIMIT 1'
    );

    $stmt->bind_param('s', $nome);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    return $result['EmailUsu'] ?? null;
  }

  public function findTicketById(int $id): array
  {
    $stmt = $this->db->prepare(
      'SELECT * FROM atendimentos WHERE codigo = ?'
    );

    $stmt->bind_param('i', $id);
    $stmt->execute();

    return $stmt->get_result()->fetch_assoc() ?: [];
  }

  public function getExecutors(): array
  {
    $result = $this->db->query(
      "SELECT NomeUsu FROM programa WHERE UserName <> '0' AND SenhaUsu <> '0' AND VinculoUsu = 'Funcionário' AND DEPTO = 'Faculdade de Agronomia - NINFA' ORDER BY NomeUsu"
    );

    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function userIsExecutor(string $name): bool
  {
    $stmt = $this->db->prepare(
      "SELECT 1 FROM programa WHERE UserName <> '0' AND SenhaUsu <> '0' AND VinculoUsu = 'Funcionário' AND DEPTO = 'Faculdade de Agronomia - NINFA' AND NomeUsu = ? LIMIT 1"
    );

    $stmt->bind_param('s', $name);
    $stmt->execute();

    return (bool) $stmt->get_result()->fetch_assoc();
  }

  public function finalizeRequest(array $data): void
  {
    $stmt = $this->db->prepare(
      'UPDATE atendimentos SET executor = ?, procedimento = ?, obs = ?, dataExecuta = ?, lancaExecuta = ?, id_status = ? WHERE codigo = ?'
    );

    $statusFinalizado = StatusConstants::FINALIZADO;

    $stmt->bind_param(
      'sssssii',
      $data['executor'],
      $data['procedimento'],
      $data['obs'],
      $data['dataExecuta'],
      $data['lancaExecuta'],
      $statusFinalizado,
      $data['codigo']
    );

    if (!$stmt->execute()) {
      throw new RuntimeException('Falha ao finalizar solicitação: ' . $stmt->error);
    }
  }

  public function findRequests(CurrentUserDTO $user, array $filters = []): array
  {
    $sql = "
      SELECT
        atendimento.*,
        prioridade.nome AS prioridade_nome,
        status.nome AS status,
        status.nome_programatico AS status_nome_programatico
      FROM atendimentos atendimento
        INNER JOIN atendimentos_status status 
          ON atendimento.id_status = status.id
        LEFT JOIN atendimentos_prioridades prioridade 
          ON atendimento.prioridade_id = prioridade.id
      WHERE 1 = 1";

    $params = [];
    $types = '';

    if (!$user->canViewDetails()) {
      $sql .= ' AND atendimento.responsavel = ?';
      $types .= 's';
      $params[] = $user->name;
    }

    if (!empty($filters['responsavel']) && $user->canViewDetails()) {
      $sql .= ' AND atendimento.responsavel = ?';
      $types .= 's';
      $params[] = $filters['responsavel'];
    }

    if (!empty($filters['status'])) {
      $sql .= ' AND atendimento.id_status = ?';
      $types .= 'i';
      $params[] = (int) $filters['status'];
    }

    if (!empty($filters['prioridade'])) {
      $sql .= ' AND atendimento.prioridade_id = ?';
      $types .= 'i';
      $params[] = (int) $filters['prioridade'];
    }

    $sql .= ' ORDER BY atendimento.codigo DESC';

    if ($types !== '') {
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param($types, ...$params);
      $stmt->execute();
      return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    $result = $this->db->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function findClosedRequests(string $ordem, CurrentUserDTO $user): array
  {
    $allowed = ['codigo', 'dataSolicita', 'dataExecuta', 'responsavel', 'demanda', 'executor'];
    $ordem = in_array($ordem, $allowed, true) ? $ordem : 'codigo';

    $sql = "SELECT * FROM atendimentos WHERE id_status = " . StatusConstants::FINALIZADO;

    if (!$user->canViewDetails()) {
      $sql .= ' AND responsavel = ? ORDER BY ' . $ordem . ' DESC';
      $stmt = $this->db->prepare($sql);
      $stmt->bind_param('s', $user->name);
      $stmt->execute();
      return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    $sql .= ' ORDER BY ' . $ordem . ' DESC';
    $result = $this->db->query($sql);

    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function findPriorities(): array
  {
    $result = $this->db->query(
      'SELECT id, nome FROM atendimentos_prioridades ORDER BY nome ASC'
    );

    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function findStatuses(): array
  {
    $result = $this->db->query(
      'SELECT id, nome, nome_programatico FROM atendimentos_status ORDER BY id'
    );

    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function findPriorityName(int $prioridadeId): ?string
  {
    $stmt = $this->db->prepare(
      'SELECT nome FROM atendimentos_prioridades WHERE id = ? LIMIT 1'
    );

    $stmt->bind_param('i', $prioridadeId);
    $stmt->execute();

    $result = $stmt->get_result()->fetch_assoc();

    return $result['nome'] ?? null;
  }

  public function updateStatus(int $codigo, int $statusId): void
  {
    $stmt = $this->db->prepare(
      'UPDATE atendimentos SET id_status = ? WHERE codigo = ?'
    );

    $stmt->bind_param('ii', $statusId, $codigo);
    if (!$stmt->execute()) {
      throw new RuntimeException('Falha ao atualizar status: ' . $stmt->error);
    }
  }

  public function setTechnicianStarted(int $codigo, string $tecnico): void
  {
    $stmt = $this->db->prepare(
      'UPDATE atendimentos SET tecnico_iniciou_atendimento = ? WHERE codigo = ?'
    );

    $stmt->bind_param('si', $tecnico, $codigo);

    if (!$stmt->execute()) {
      throw new RuntimeException('Falha ao atualizar técnico que iniciou atendimento: ' . $stmt->error);
    }
  }

  public function createTimelineEntry(array $data): void
  {
    $stmt = $this->db->prepare(
      'INSERT INTO atendimentos_timeline (atendimento_id, autor, tipo, mensagem, created_at) VALUES (?, ?, ?, ?, ?)'
    );

    $stmt->bind_param(
      'issss',
      $data['atendimento_id'],
      $data['autor'],
      $data['tipo'],
      $data['mensagem'],
      $data['created_at']
    );

    if (!$stmt->execute()) {
      throw new RuntimeException('Falha ao registrar timeline: ' . $stmt->error);
    }
  }

  public function findTimelineEntriesByTicketId(int $ticketId): array
  {
    $stmt = $this->db->prepare(
      'SELECT id, atendimento_id, autor, tipo, mensagem, created_at FROM atendimentos_timeline WHERE atendimento_id = ? ORDER BY created_at ASC, id ASC'
    );

    $stmt->bind_param('i', $ticketId);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  }
}
