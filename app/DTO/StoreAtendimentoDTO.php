<?php

class StoreAtendimentoDTO
{
  public string $responsavel;
  public string $demanda;
  public string $patrimonio;
  public string $descricao;
  public ?int $prioridadeId;
  public ?string $arquivo;

  private function __construct(string $responsavel, string $demanda, string $patrimonio, string $descricao, ?int $prioridadeId, ?string $arquivo)
  {
    $this->responsavel = $responsavel;
    $this->demanda = $demanda;
    $this->patrimonio = $patrimonio;
    $this->descricao = $descricao;
    $this->prioridadeId = $prioridadeId;
    $this->arquivo = $arquivo;
  }

  public static function fromArray(array $data): self
  {
    return new self(
      trim((string) ($data['responsavel'] ?? '')),
      trim((string) ($data['demanda'] ?? '')),
      trim((string) ($data['patrimonio'] ?? '')),
      trim((string) ($data['descricao'] ?? '')),
      isset($data['prioridade_id']) && is_numeric($data['prioridade_id']) ? (int) $data['prioridade_id'] : null,
      isset($data['arquivo']) ? trim((string) $data['arquivo']) : null
    );
  }

  public function toRepositoryPayload(CurrentUserDTO $user, ?string $prioridadeNome): array
  {
    return [
      'responsavel' => $this->responsavel,
      'demanda' => $this->demanda,
      'patrimonio' => $this->patrimonio,
      'descricao' => AtendimentoHelper::transform($this->descricao),
      'dataSolicita' => date('YmdHi'),
      'lancaSolicita' => $user->name,
      'prioridade_id' => $this->prioridadeId,
      'prioridade' => $prioridadeNome,
      'id_status' => StatusConstants::ABERTO,
      'arquivo' => $this->arquivo,
    ];
  }
}
