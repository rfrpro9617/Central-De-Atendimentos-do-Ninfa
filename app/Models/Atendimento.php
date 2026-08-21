<?php

class Atendimento
{
  public int $codigo;
  public string $responsavel;
  public string $demanda;
  public string $patrimonio;
  public string $descricao;
  public string $dataSolicita;
  public string $lancaSolicita;
  public ?int $prioridade_id;
  public ?string $prioridade_nome;
  public int $id_status;
  public ?string $status;
  public ?string $executor;
  public ?string $procedimento;
  public ?string $obs;
  public ?string $dataExecuta;
  public ?string $lancaExecuta;
  public ?string $tecnico_iniciou_atendimento;
  public ?string $arquivo;

  public function __construct(array $data)
  {
    $this->codigo = (int) ($data['codigo'] ?? 0);
    $this->responsavel = (string) ($data['responsavel'] ?? '');
    $this->demanda = (string) ($data['demanda'] ?? '');
    $this->patrimonio = (string) ($data['patrimonio'] ?? '');
    $this->descricao = (string) ($data['descricao'] ?? '');
    $this->dataSolicita = (string) ($data['dataSolicita'] ?? '');
    $this->lancaSolicita = (string) ($data['lancaSolicita'] ?? '');
    $this->prioridade_id = isset($data['prioridade_id']) ? (int) $data['prioridade_id'] : null;
    $this->prioridade_nome = $data['prioridade_nome'] ?? null;
    $this->id_status = (int) ($data['id_status'] ?? 0);
    $this->status = $data['status'] ?? null;
    $this->executor = $data['executor'] ?? null;
    $this->procedimento = $data['procedimento'] ?? null;
    $this->obs = $data['obs'] ?? null;
    $this->dataExecuta = $data['dataExecuta'] ?? null;
    $this->lancaExecuta = $data['lancaExecuta'] ?? null;
    $this->tecnico_iniciou_atendimento = $data['tecnico_iniciou_atendimento'] ?? null;
    $this->arquivo = $data['arquivo'] ?? null;
  }

  public static function fromArray(array $data): self
  {
    return new self($data);
  }

  public function toArray(): array
  {
    return [
      'codigo' => $this->codigo,
      'responsavel' => $this->responsavel,
      'demanda' => $this->demanda,
      'patrimonio' => $this->patrimonio,
      'descricao' => $this->descricao,
      'dataSolicita' => $this->dataSolicita,
      'lancaSolicita' => $this->lancaSolicita,
      'prioridade_id' => $this->prioridade_id,
      'prioridade_nome' => $this->prioridade_nome,
      'id_status' => $this->id_status,
      'status' => $this->status,
      'executor' => $this->executor,
      'procedimento' => $this->procedimento,
      'obs' => $this->obs,
      'dataExecuta' => $this->dataExecuta,
      'lancaExecuta' => $this->lancaExecuta,
      'tecnico_iniciou_atendimento' => $this->tecnico_iniciou_atendimento,
      'arquivo' => $this->arquivo,
    ];
  }
}
