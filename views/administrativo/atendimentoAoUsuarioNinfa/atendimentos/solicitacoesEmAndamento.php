<?php
require_once APP_ROOT . '/app/Helpers/PrioridadeHelper.php';
require_once APP_ROOT . '/app/Helpers/StatusHelper.php';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Chamados</title>
  <?php require __DIR__ . '/../partials/head-scripts.php'; ?>
  <link rel="stylesheet" href="css/tailwind-custom.css" />
</head>

<body class="min-h-screen flex flex-col bg-gray-100">
  <?= Header::render() ?>
  <div class="mx-auto w-full max-w-7xl px-4 py-6 flex-1">
    <main class="w-full flex-1">
      <section class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <header class="border-b border-gray-200 px-6 py-5">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
              <h1 class="text-2xl font-bold text-gray-800">Histórico de Chamados</h1>
              <p class="mt-1 text-sm text-gray-500">
                Utilize os filtros abaixo para buscar por chamados específicos. <span class="text-red-500 font-semibold">Clique no número do chamado para ver detalhes e acompanhar o andamento.</span>
              </p>
            </div>
          </div>
        </header>
        <div class="px-6 py-6 space-y-6">
          <div class="border-b border-gray-200 bg-gray-50 px-6 py-5">
            <?php
            $selectedResponsavel = htmlspecialchars($selectedFilters['responsavel'] ?? '', ENT_QUOTES);
            $selectedStatus = htmlspecialchars($selectedFilters['status'] ?? '', ENT_QUOTES);
            $selectedPrioridade = htmlspecialchars($selectedFilters['prioridade'] ?? '', ENT_QUOTES);
            ?>
            <form method="get" action="ninfa.php" class="space-y-5">
              <input type="hidden" name="page" value="atendimentos">
              <input type="hidden" name="action" value="tickets.index">
              <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="space-y-2">
                  <label for="responsavel">Responsável (Funcionários e Professores)</label>
                  <select id="responsavel" name="responsavel" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none placeholder:text-gray-400">
                    <option value="">Todos</option>
                    <?php foreach ($responsibles as $responsible): ?>
                      <?php $name = htmlspecialchars($responsible['NomeUsu'] ?? '', ENT_QUOTES); ?>
                      <option value="<?= $name ?>" <?= $selectedResponsavel === $name ? 'selected' : '' ?>>
                        <?= $name ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="space-y-2">
                  <label for="status">Status</label>
                  <select id="status" name="status" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none placeholder:text-gray-400">
                    <option value="">Todos</option>
                    <?php foreach ($statuses as $status): ?>
                      <?php $statusId = (int) $status['id']; ?>
                      <?php $statusName = htmlspecialchars($status['nome'] ?? '', ENT_QUOTES); ?>
                      <option value="<?= $statusId ?>" <?= (string)$statusId === $selectedStatus ? 'selected' : '' ?>>
                        <?= $statusName ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="space-y-2">
                  <label for="prioridade">Prioridade</label>
                  <select id="prioridade" name="prioridade" class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm text-gray-900 transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none placeholder:text-gray-400">
                    <option value="">Todas</option>
                    <?php foreach ($priorities as $priority): ?>
                      <?php $priorityId = (int) $priority['id']; ?>
                      <?php $priorityName = htmlspecialchars($priority['nome'] ?? '', ENT_QUOTES); ?>
                      <option value="<?= $priorityId ?>" <?= (string)$priorityId === $selectedPrioridade ? 'selected' : '' ?>>
                        <?= $priorityName ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="flex justify-end gap-3">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                  <i data-lucide="filter" class="h-4 w-4"></i>
                  Filtrar
                </button>
                <a href="ninfa.php?page=atendimentos&action=tickets.index" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-4 focus:ring-gray-200">
                  <i data-lucide="x" class="h-4 w-4"></i>
                  Limpar
                </a>
              </div>
            </form>
          </div>
          <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <?= (new Table(
              columns: [
                Column::make('codigo', 'Nº', [
                  'sortable' => true,
                  'cellClass' => 'font-semibold text-green-700',
                ])->withCustomRender(function (array $item) {
                  $code = htmlspecialchars($item['codigo'] ?? '', ENT_QUOTES);
                  return sprintf(
                    '<a href="ninfa.php?page=atendimentos&action=tickets.show&id=%s" class="hover:underline">%s</a>',
                    $code,
                    $code
                  );
                }),
                Column::make('responsavel', 'Responsável', ['sortable' => true]),
                Column::badge('prioridade', 'Prioridade', ['sortable' => true])
                  ->withCustomRender(function (array $item) {
                    $value = $item['prioridade_nome'] ?? null;
                    if (empty($value)) return '<span class="text-gray-400">-</span>';
                    $class = PrioridadeHelper::cssClass($value);
                    $label = PrioridadeHelper::label($value);
                    return sprintf(
                      '<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold %s">%s</span>',
                      htmlspecialchars($class, ENT_QUOTES),
                      htmlspecialchars($label, ENT_QUOTES)
                    );
                  }),
                Column::badge('status', 'Status', ['sortable' => true])
                  ->withCustomRender(function (array $item) {
                    $value = $item['status_nome_programatico'] ?? null;
                    if (empty($value)) return '<span class="text-gray-400">-</span>';
                    $class = StatusHelper::cssClass($value);
                    $label = StatusHelper::label($value);
                    return sprintf(
                      '<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold %s">%s</span>',
                      htmlspecialchars($class, ENT_QUOTES),
                      htmlspecialchars($label, ENT_QUOTES)
                    );
                  }),
                Column::make('descricao', 'Descrição', [
                  'sortable' => true,
                  'cellClass' => 'max-w-md whitespace-normal break-words text-sm leading-6 text-gray-600',
                  'attributes' => ['preserveLineBreaks' => true],
                ]),
                Column::make('arquivo', 'Arquivo', [
                  'sortable' => false,
                  'cellClass' => 'text-sm',
                ])->withCustomRender(function (array $item) {
                  $arquivo = $item['arquivo'] ?? null;
                  $arquivos = array_filter(array_map('trim', explode(';', $arquivo ?? '')), fn($it) => $it !== '');
                  if (empty($arquivos)) {
                    return '<span class="text-gray-400">-</span>';
                  }
                  $links = array_map(function ($a) {
                    $nomeArquivo = basename($a);
                    return sprintf(
                      '<a href="%s" target="_blank" class="text-blue-600 hover:underline" title="%s">📄 %s</a>',
                      htmlspecialchars($a, ENT_QUOTES),
                      htmlspecialchars($nomeArquivo, ENT_QUOTES),
                      htmlspecialchars(strlen($nomeArquivo) > 20 ? substr($nomeArquivo, 0, 17) . '...' : $nomeArquivo, ENT_QUOTES)
                    );
                  }, $arquivos);

                  return implode('<br>', $links);
                }),
              ],
              data: $tickets,
              options: [
                'rowsPerPage' => 10,
                'showColumnMenu' => true,
                'emptyMessage' => 'Nenhuma solicitação encontrada.',
              ]
            ))->render() ?>
          </div>
          <div class="border-t border-gray-200 bg-gray-50 px-6 py-5 flex justify-end gap-3">
            <a href="ninfa.php?page=home" class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
              <i data-lucide="arrow-left" class="h-4 w-4"></i>
              Voltar
            </a>
          </div>
        </div>
      </section>
    </main>
  </div>
  <?= Footer::render() ?>
  <script>
    lucide.createIcons();
  </script>
</body>

</html>