create table agronomia2.atendimentos_prioridades (
  id int auto_increment primary key,
  nome varchar(50) not null,
  nome_programatico varchar(50) not null
);

-----

insert into agronomia2.atendimentos_prioridades (nome, nome_programatico)
values
('', 'BAIXA'),
('MédBaixaia', 'MEDIA'),
('Alta', 'ALTA'),
('Urgente', 'URGENTE');

-----

alter table agronomia2.atendimentos
add prioridade_id int;

alter table agronomia2.atendimentos
add constraint fk_atendimento_prioridade
foreign key (prioridade_id)
references agronomia2.atendimentos_prioridades(id);

-----

alter table agronomia2.atendimentos
add column arquivo varchar(255) null;

-----

create table agronomia2.atendimentos_status (
  id int auto_increment primary key,
  nome varchar(50) not null,
  nome_programatico varchar(50) not null
);

-----

insert into agronomia2.atendimentos_status (nome, nome_programatico)
values
('Aberto', 'ABERTO'),
('Em Andamento', 'EM_ANDAMENTO'),
('Aguardando usuário', 'AGUARDANDO_USUARIO'),
('Finalizado', 'FINALIZADO'),
('Cancelado', 'CANCELADO')

-----

alter table agronomia2.atendimentos
add column id_status int null;

alter table agronomia2.atendimentos
add constraint fk_atendimentos_status
foreign key (id_status)
references agronomia2.atendimentos_status(id);

-----

CREATE TABLE agronomia2.atendimentos_timeline (
  id INT AUTO_INCREMENT PRIMARY KEY,
  atendimento_id SMALLINT(6) NOT NULL,
  autor VARCHAR(99) NOT NULL,
  tipo VARCHAR(30) NOT NULL, -- STATUS | COMENTARIO | SISTEMA
  mensagem TEXT NOT NULL,
  created_at VARCHAR(12) NOT NULL,

  CONSTRAINT fk_timeline_atendimento
  FOREIGN KEY (atendimento_id)
  REFERENCES agronomia2.atendimentos(codigo)
);

-----

CREATE TABLE agronomia2.atendimentos_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  level VARCHAR(20) NOT NULL,
  message LONGTEXT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_level (level),
  INDEX idx_created_at (created_at)
);

-----

alter table agronomia2.atendimentos
add tecnico_iniciou_atendimento varchar(99) null;

-----