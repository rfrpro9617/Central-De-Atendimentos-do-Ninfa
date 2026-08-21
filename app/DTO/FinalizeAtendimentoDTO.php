<?php

class FinalizeAtendimentoDTO
{
  public int $codigo;
  public string $executor;
  public array $procedimentos;
  public string $observacao;

  private function __construct(int $codigo, string $executor, array $procedimentos, string $observacao)
  {
    $this->codigo = $codigo;
    $this->executor = $executor;
    $this->procedimentos = $procedimentos;
    $this->observacao = $observacao;
  }

  public static function fromArray(array $data): self
  {
    return new self(
      (int) ($data['cod'] ?? 0),
      trim((string) ($data['executor'] ?? '')),
      is_array($data['procedimento']) ? array_map('trim', $data['procedimento']) : [],
      trim((string) ($data['observacao'] ?? ''))
    );
  }

  public function toRepositoryPayload(CurrentUserDTO $user): array
  {
    return [
      'codigo' => $this->codigo,
      'executor' => $this->executor,
      'procedimento' => implode('; ', array_filter($this->procedimentos)),
      'obs' => $this->observacao,
      'dataExecuta' => date('YmdHi'),
      'lancaExecuta' => $user->name,
    ];
  }
}
