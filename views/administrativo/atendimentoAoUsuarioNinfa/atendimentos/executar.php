<?php
require_once APP_ROOT . '/app/Constants/StatusConstants.php';

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

unset($_SESSION['errors'], $_SESSION['old'], $_SESSION['success'], $_SESSION['error']);

if (empty($old) && isset($ticket) && (int) $ticket['id_status'] === StatusConstants::FINALIZADO) {
  $old['executor'] = $ticket['executor'] ?? '';
  $old['observacao'] = $ticket['obs'] ?? '';
  $old['procedimento'] = array_filter(array_map('trim', explode(';', $ticket['procedimento'] ?? '')), fn($item) => $item !== '');
}

$cancelUrl = 'ninfa.php?page=atendimentos';

$status = (int) $ticket['id_status'];

$isEditable = $status !== StatusConstants::FINALIZADO && $status !== StatusConstants::CANCELADO;
$isWaitingForUser = $status === StatusConstants::AGUARDANDO_USUARIO;
$canFinalize = $status !== StatusConstants::FINALIZADO && $status !== StatusConstants::CANCELADO && ($canStart ?? false);
$isEmAndamento = $status === StatusConstants::EM_ANDAMENTO;
?>


<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Executar Chamado</title>
  <?php require __DIR__ . '/../partials/head-scripts.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-gray-100">

  <?= Header::render() ?>

  <div class="mx-auto w-full max-w-7xl px-4 py-6 space-y-6 flex-1">
    <main class="w-full flex-1">
      <section class="w-full overflow-hidden rounded-2xl bg-white shadow-sm border border-gray-200">
        <!-- HEADER -->
        <header class="border-b border-gray-200 px-6 py-5">
          <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="w-full min-w-0">
              <h1 class="text-2xl font-bold text-gray-800">Executar Chamado</h1>
              <p class="mt-1 text-sm text-gray-500">
                Gerencie o chamado conforme o status atual.
              </p>
            </div>
            <button id="openTimelineBtn" type="button"
              class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 whitespace-nowrap">
              <i data-lucide="clock" class="h-4 w-4"></i>
              Ver histórico do chamado
            </button>
          </div>
        </header>

        <div class="px-6 pt-4">
          <!-- ALERTAS -->
          <?php if (!empty($_SESSION['success'])): ?>
            <div class="mb-3 rounded-lg bg-green-100 p-3 text-green-700">
              <?= $_SESSION['success'] ?>
            </div>
            <?php unset($_SESSION['success']); ?>
          <?php endif; ?>
          <?php if (!empty($_SESSION['error'])): ?>
            <div class="mb-3 rounded-lg bg-red-100 p-3 text-red-700">
              <?= $_SESSION['error'] ?>
            </div>
            <?php unset($_SESSION['error']); ?>
          <?php endif; ?>
        </div>

        <div class="px-6 pb-4">
          <!-- INICIAR CHAMADO -->
          <?php if ($ticket['id_status'] == StatusConstants::ABERTO && ($canStart ?? false)): ?>
            <form id="start-ticket-form" method="POST" action="ninfa.php?page=atendimentos&action=tickets.start">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <input type="hidden" name="cod" value="<?= (int)$ticket['codigo'] ?>">
              <button id="startTicketBtn" type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                <span id="startBtnText">Iniciar Chamado</span>
                <span id="startSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
              </button>
            </form>
          <?php endif; ?>
        </div>

        <!-- AÇÕES TÉCNICO -->
        <?php if ($isEmAndamento && ($canStart ?? false)): ?>
          <div class="px-6 py-4 border-t border-gray-200 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">
              Ações do Chamado <span class="text-red-600">*</span>
            </h2>
            <!-- ENVIAR PARA USUÁRIO -->
            <form id="requestInfoForm" method="POST"
              action="ninfa.php?page=atendimentos&action=tickets.request_information"
              class="space-y-2">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <input type="hidden" name="cod" value="<?= (int)$ticket['codigo'] ?>">
              <p class="text-sm text-gray-500">
                Escreva uma mensagem para o usuário quando quiser pedir informações, orientar sobre procedimentos ou solicitar algum dado adicional.
              </p>
              <textarea
                name="mensagem"
                placeholder="Mensagem para o usuário..."
                required
                class="w-full rounded-xl border border-gray-300 p-3 text-sm"></textarea>
              <button id="requestInfoBtn" type="submit" title="Use este botão para enviar uma mensagem ao usuário com instruções, perguntas ou informações de procedimento."
                class="inline-flex items-center gap-2 bg-yellow-600 text-white px-4 py-2 rounded-xl hover:bg-yellow-700">
                <span id="requestInfoBtnText">Retornar ao usuário</span>
                <span id="requestInfoSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
              </button>
            </form>

          </div>
        <?php endif; ?>

        <!-- RETORNO DO USUÁRIO PARA TÉCNICO -->
        <?php if ($isWaitingForUser && ($isTicketOwner ?? false)): ?>
          <div class="px-6 py-4 border-t border-gray-200 space-y-4">
            <h2 class="text-sm font-semibold text-gray-700">
              Retornar para o Técnico <span class="text-red-600">*</span>
            </h2>
            <p class="text-sm text-gray-500">
              Use este formulário quando o usuário quiser responder, pedir algo, informar que já fez um procedimento ou enviar nova informação para o técnico.
            </p>
            <form id="returnToTechnicianForm" method="POST"
              action="ninfa.php?page=atendimentos&action=tickets.return_to_technician"
              class="space-y-2">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
              <input type="hidden" name="cod" value="<?= (int)$ticket['codigo'] ?>">
              <textarea name="mensagem"
                placeholder="Mensagem para o técnico..."
                rows="5"
                class="w-full rounded-xl border border-gray-300 p-3 text-sm"></textarea>
              <button id="returnToTechnicianBtn" type="submit" title="Envie de volta ao técnico quando o usuário precisar responder, pedir ajuda ou informar o andamento do chamado."
                class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                <span id="returnToTechnicianBtnText">Retornar para o Técnico</span>
                <span id="returnToTechnicianSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
              </button>
            </form>
          </div>
        <?php endif; ?>

        <!-- DADOS -->
        <form method="POST"
          action="ninfa.php?page=atendimentos&action=tickets.finalize"
          class="space-y-6 px-6 py-6">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
          <input type="hidden" name="cod" value="<?= (int) $ticket['codigo'] ?>">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Responsável (Funcionários e Professores)</label>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm">
              <?= htmlspecialchars($ticket['responsavel']) ?>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Demanda</label>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm">
              <?= htmlspecialchars($ticket['demanda']) ?>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Patrimônio</label>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm">
              <?= htmlspecialchars($ticket['patrimonio']) ?>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição</label>
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm">
              <?= nl2br(htmlspecialchars($ticket['descricao'])) ?>
            </div>
          </div>
          <?php
            $arquivos = array_filter(array_map('trim', explode(';', $ticket['arquivo'] ?? '')), fn($item) => $item !== '');
            if (!empty($arquivos)):
          ?>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo(s)</label>
              <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-sm space-y-2">
                <?php foreach ($arquivos as $arquivo): ?>
                  <div>
                    <a href="<?= htmlspecialchars($arquivo, ENT_QUOTES) ?>" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:underline">
                      <i data-lucide="file" class="h-4 w-4"></i>
                      <?= htmlspecialchars(basename($arquivo), ENT_QUOTES) ?>
                    </a>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>
          <!-- EXECUTOR -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Técnico Responsável <span class="text-red-600">*</span></label>
            <select name="executor"
              class="w-full rounded-xl border border-gray-300 py-3 px-4 text-sm"
              <?= !$canFinalize ? 'disabled' : '' ?>>
              <option value="">Selecione</option>
              <?php foreach ($executors as $executor): ?>
                <option value="<?= htmlspecialchars($executor['NomeUsu']) ?>"
                  <?= ($old['executor'] ?? '') === $executor['NomeUsu'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($executor['NomeUsu']) ?>
                </option>
              <?php endforeach; ?>
            </select>
            <?php if (!empty($errors['executor'])): ?>
              <small class="text-red-600"><?= htmlspecialchars($errors['executor']) ?></small>
            <?php endif; ?>
          </div>
          <!-- PROCEDIMENTOS -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-4">
              Procedimentos realizados <span class="text-red-600">*</span></label>
            </label>
            <div class="space-y-3">
              <?php $firstProcediment = true;
              foreach ($procediments as $procediment): ?>
                <label class="flex items-start gap-3 rounded-xl border border-gray-200 p-4 hover:bg-gray-50">

                  <input type="checkbox"
                    name="procedimento[]"
                    value="<?= htmlspecialchars($procediment) ?>"
                    class="mt-1 h-4 w-4 rounded border-gray-300 text-green-700 focus:ring-green-600"
                    <?= in_array($procediment, $old['procedimento'] ?? []) ? 'checked' : '' ?>
                    <?= !$canFinalize ? 'disabled' : '' ?>>
                  <?php $firstProcediment = false; ?>

                  <span class="text-sm text-gray-700">
                    <?= htmlspecialchars($procediment) ?>
                  </span>

                </label>
              <?php endforeach; ?>
            </div>
            <?php if (!empty($errors['procedimento'])): ?>
              <small class="text-red-600"><?= htmlspecialchars($errors['procedimento']) ?></small>
            <?php endif; ?>
          </div>

          <!-- OBS -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Observações <span class="text-red-600">*</span></label></label>
            <textarea name="observacao"
              rows="5"
              class="w-full rounded-xl border border-gray-300 py-3 px-4 text-sm"
              <?= !$canFinalize ? 'disabled' : '' ?>><?= $old['observacao'] ?? '' ?></textarea>
            <?php if (!empty($errors['observacao'])): ?>
              <small class="text-red-600"><?= htmlspecialchars($errors['observacao']) ?></small>
            <?php endif; ?>
          </div>

          <!-- ACTIONS -->
          <div class="flex justify-end gap-3 border-t border-gray-200 pt-6">
            <a href="ninfa.php?page=atendimentos&action=tickets.index"
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
              <i data-lucide="arrow-left" class="h-4 w-4"></i>
              Voltar
            </a>
            <?php if ($canFinalize): ?>
              <button id="finalizeTicketBtn" type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-green-700 px-5 py-3 text-sm font-medium text-white hover:bg-green-800">
                <i data-lucide="check-circle" class="h-4 w-4"></i>
                <span id="finalizeBtnText">Finalizar Chamado</span>
                <span id="finalizeSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
              </button>
            <?php endif; ?>
          </div>
        </form>

        <?php if ($canCancel): ?>
          <form id="cancel-ticket-form" method="POST" action="ninfa.php?page=atendimentos&action=tickets.close" class="space-y-4 px-6 py-6 border-t border-gray-200">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="cod" value="<?= (int) $ticket['codigo'] ?>">

            <div>
              <h2 class="text-sm font-semibold text-gray-700 mb-3">Cancelar Chamado</h2>
              <p class="text-sm text-gray-500 mb-3">
                Somente quem abriu o chamado pode cancelar. Preencha apenas as observações e deixe os procedimentos em branco.
              </p>
              <label class="block text-sm font-medium text-gray-700 mb-2">Observações de Cancelamento <span class="text-red-600">*</span></label>
              <textarea name="mensagem"
                rows="5"
                class="w-full rounded-xl border border-gray-300 py-3 px-4 text-sm"
                placeholder="Explique o motivo do cancelamento..."></textarea>
              <?php if (!empty($errors['mensagem'])): ?>
                <small class="block mt-2 text-red-600"><?= htmlspecialchars($errors['mensagem']) ?></small>
              <?php endif; ?>
            </div>

            <div class="flex justify-end">
              <button id="cancelTicketBtn" type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-medium text-white hover:bg-red-700">
                <i data-lucide="x-circle" class="h-4 w-4"></i>
                <span id="cancelBtnText">Cancelar Chamado</span>
                <span id="cancelSpinner" class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
              </button>
            </div>
          </form>
        <?php endif; ?>
      </section>
    </main>
  </div>
  <?= Footer::render() ?>

  <div id="timelineModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/40 p-4">
    <div class="mx-auto max-w-3xl overflow-hidden rounded-[30px] bg-white shadow-2xl">
      <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
        <div>
          <h2 class="text-lg font-semibold text-gray-900">Histórico do Chamado</h2>
          <p class="mt-1 text-sm text-gray-500">Acompanhe a troca de mensagens e principais eventos do chamado.</p>
        </div>
        <button id="closeTimelineBtn" type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200">
          <i data-lucide="x" class="h-5 w-5"></i>
        </button>
      </div>
      <div class="max-h-[70vh] space-y-4 overflow-y-auto px-6 py-5">
        <?php if (empty($timeline)): ?>
          <div class="rounded-3xl bg-gray-50 p-6 text-center text-sm text-gray-600">
            Nenhuma atividade registrada no histórico do chamado.
          </div>
        <?php else: ?>
          <?php
          $timelineTypeLabels = [
            'ABERTURA' => 'Abertura do chamado',
            'INICIO' => 'Início do atendimento',
            'MENSAGEM_TECNICO' => 'Mensagem do técnico',
            'MENSAGEM_USUARIO' => 'Resposta do usuário',
            'FINALIZACAO' => 'Finalização do atendimento',
            'ENCERRAMENTO' => 'Encerramento',
          ];
          ?>
          <?php foreach ($timeline as $entry): ?>
            <?php
            $label = $timelineTypeLabels[$entry['tipo']] ?? ucwords(strtolower(str_replace('_', ' ', $entry['tipo'])));
            $createdAt = DateTime::createFromFormat('YmdHi', $entry['created_at']);
            ?>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm">
              <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <span class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold uppercase text-blue-700">
                    <?= htmlspecialchars($label) ?>
                  </span>
                  <p class="mt-2 text-sm text-gray-700"><strong>Autor:</strong> <?= htmlspecialchars($entry['autor']) ?></p>
                </div>
                <p class="text-sm text-gray-500">
                  <?= $createdAt ? $createdAt->format('d/m/Y H:i') : htmlspecialchars($entry['created_at']) ?>
                </p>
              </div>
              <div class="mt-4 text-sm leading-6 text-gray-700 whitespace-pre-line">
                <?= nl2br(htmlspecialchars($entry['mensagem'])) ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    lucide.createIcons();

    const timelineModal = document.getElementById('timelineModal');
    const openTimelineBtn = document.getElementById('openTimelineBtn');
    const closeTimelineBtn = document.getElementById('closeTimelineBtn');

    if (openTimelineBtn) {
      openTimelineBtn.addEventListener('click', function() {
        timelineModal.classList.remove('hidden');
      });
    }

    if (closeTimelineBtn) {
      closeTimelineBtn.addEventListener('click', function() {
        timelineModal.classList.add('hidden');
      });
    }

    if (timelineModal) {
      timelineModal.addEventListener('click', function(event) {
        if (event.target === timelineModal) {
          timelineModal.classList.add('hidden');
        }
      });
    }

    const startForm = document.getElementById('start-ticket-form');
    const startBtn = document.getElementById('startTicketBtn');
    const startSpinner = document.getElementById('startSpinner');
    const startBtnText = document.getElementById('startBtnText');

    if (startForm) {
      let isSubmitting = false;

      startForm.addEventListener('submit', function(e) {
        if (isSubmitting) {
          e.preventDefault();
          return;
        }

        isSubmitting = true;
        startBtn.disabled = true;
        startBtn.classList.add('opacity-70', 'cursor-not-allowed');
        startSpinner.classList.remove('hidden');
        startBtnText.textContent = 'Carregando...';
        document.body.style.cursor = 'wait';
      });
    }

    const requestInfoForm = document.getElementById('requestInfoForm');
    const requestInfoBtn = document.getElementById('requestInfoBtn');
    const requestInfoSpinner = document.getElementById('requestInfoSpinner');
    const requestInfoBtnText = document.getElementById('requestInfoBtnText');

    if (requestInfoForm) {
      let isSubmittingRequest = false;

      requestInfoForm.addEventListener('submit', function(e) {
        // Validar se há mensagem
        const mensagemInput = requestInfoForm.querySelector('textarea[name="mensagem"]');
        if (!mensagemInput || !mensagemInput.value.trim()) {
          e.preventDefault();
          alert('Por favor, preencha a mensagem antes de enviar.');
          return;
        }

        if (isSubmittingRequest) {
          e.preventDefault();
          return;
        }

        isSubmittingRequest = true;
        requestInfoBtn.disabled = true;
        requestInfoBtn.classList.add('opacity-70', 'cursor-not-allowed');
        requestInfoSpinner.classList.remove('hidden');
        requestInfoBtnText.textContent = 'Carregando...';
        document.body.style.cursor = 'wait';
      });
    }

    const returnToTechnicianForm = document.getElementById('returnToTechnicianForm');
    const returnToTechnicianBtn = document.getElementById('returnToTechnicianBtn');
    const returnToTechnicianSpinner = document.getElementById('returnToTechnicianSpinner');
    const returnToTechnicianBtnText = document.getElementById('returnToTechnicianBtnText');

    if (returnToTechnicianForm) {
      let isReturning = false;

      returnToTechnicianForm.addEventListener('submit', function(e) {
        if (isReturning) {
          e.preventDefault();
          return;
        }

        isReturning = true;
        returnToTechnicianBtn.disabled = true;
        returnToTechnicianBtn.classList.add('opacity-70', 'cursor-not-allowed');
        returnToTechnicianSpinner.classList.remove('hidden');
        returnToTechnicianBtnText.textContent = 'Carregando...';
        document.body.style.cursor = 'wait';
      });
    }

    const finalizeForm = document.querySelector('form[action*="tickets.finalize"]');
    const finalizeBtn = document.getElementById('finalizeTicketBtn');
    const finalizeSpinner = document.getElementById('finalizeSpinner');
    const finalizeBtnText = document.getElementById('finalizeBtnText');

    if (finalizeForm && finalizeBtn) {
      let isFinalizing = false;

      finalizeForm.addEventListener('submit', function(e) {
        if (isFinalizing) {
          e.preventDefault();
          return;
        }

        isFinalizing = true;
        finalizeBtn.disabled = true;
        finalizeBtn.classList.add('opacity-70', 'cursor-not-allowed');
        finalizeSpinner.classList.remove('hidden');
        finalizeBtnText.textContent = 'Carregando...';
        document.body.style.cursor = 'wait';
      });
    }

    const cancelForm = document.getElementById('cancel-ticket-form');
    const cancelBtn = document.getElementById('cancelTicketBtn');
    const cancelSpinner = document.getElementById('cancelSpinner');
    const cancelBtnText = document.getElementById('cancelBtnText');

    if (cancelForm && cancelBtn) {
      let isCancelling = false;

      cancelForm.addEventListener('submit', function(e) {
        if (isCancelling) {
          e.preventDefault();
          return;
        }

        isCancelling = true;
        cancelBtn.disabled = true;
        cancelBtn.classList.add('opacity-70', 'cursor-not-allowed');
        if (cancelSpinner) cancelSpinner.classList.remove('hidden');
        if (cancelBtnText) cancelBtnText.textContent = 'Carregando...';
        document.body.style.cursor = 'wait';
      });
    }
  </script>

</body>

</html>