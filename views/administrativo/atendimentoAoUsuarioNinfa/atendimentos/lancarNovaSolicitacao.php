<?php

$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

unset($_SESSION['errors'], $_SESSION['old']);

$cancelUrl = 'ninfa.php?page=home';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lançar novo chamado</title>

  <?php require __DIR__ . '/../partials/head-scripts.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-gray-100 overflow-x-hidden">

  <?= Header::render() ?>

  <div class="mx-auto w-full max-w-7xl px-4 md:px-6 py-4 md:py-6 space-y-6 overflow-x-hidden flex-1">

    <main class="w-full flex-1">

      <section class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

        <!-- HEADER -->
        <header class="border-b border-gray-200 px-4 md:px-6 py-5">
          <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

            <div class="space-y-1 max-w-prose">
              <h1 class="text-xl md:text-2xl font-bold text-gray-800">
                Lançar novo chamado
              </h1>

              <p class="text-sm text-gray-500 leading-relaxed">
                Preencha os dados abaixo para registrar um novo chamado. <span class="text-red-500 font-semibold">Campos marcados com * são obrigatórios.</span>
              </p>
            </div>

          </div>
        </header>

        <!-- ALERTAS -->
        <?php if (!empty($_SESSION['success']) || !empty($_SESSION['error'])): ?>
          <div class="px-4 md:px-6 pt-4 space-y-3">

            <?php if (!empty($_SESSION['success'])): ?>
              <div class="rounded-lg bg-green-100 p-3 text-green-700 text-sm">
                <?= $_SESSION['success'] ?>
              </div>
              <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
              <div class="rounded-lg bg-red-100 p-3 text-red-700 text-sm">
                <?= $_SESSION['error'] ?>
              </div>
              <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

          </div>
        <?php endif; ?>

        <!-- FORM -->
        <form id="create-ticket-form"
          method="POST"
          action="ninfa.php?page=atendimentos&action=tickets.store"
          enctype="multipart/form-data"
          class="space-y-6 px-4 md:px-6 py-6">

          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

          <!-- GRID -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- RESPONSÁVEL -->
            <div class="min-w-0">
              <label class="mb-2 block text-sm font-medium text-gray-700">
                Responsável (Funcionários e Professores) <span class="text-red-600">*</span>
              </label>

              <select name="responsavel"
                class="w-full max-w-full min-w-0 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">

                <?php foreach ($responsibles as $responsible): ?>
                  <option value="<?= $responsible['NomeUsu'] ?>"
                    <?= ($old['responsavel'] ?? '') === $responsible['NomeUsu'] ? 'selected' : '' ?>>
                    <?= $responsible['NomeUsu'] ?>
                  </option>
                <?php endforeach; ?>

              </select>

              <?php if (!empty($errors['responsavel'])): ?>
                <small class="text-red-600 text-sm"><?= $errors['responsavel'] ?></small>
              <?php endif; ?>
            </div>

            <!-- PRIORIDADE -->
            <div class="min-w-0">
              <label class="mb-2 block text-sm font-medium text-gray-700">
                Prioridade <span class="text-red-600">*</span>
              </label>

              <select name="prioridade_id"
                class="w-full max-w-full min-w-0 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">

                <?php foreach ($priorities as $p): ?>
                  <option value="<?= $p['id'] ?>"
                    <?= ($old['prioridade_id'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                    <?= $p['nome'] ?>
                  </option>
                <?php endforeach; ?>

              </select>
            </div>

          </div>

          <!-- DEMANDA -->
          <div class="min-w-0">
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Demanda <span class="text-red-600">*</span>
            </label>

            <select name="demanda"
              class="w-full max-w-full min-w-0 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">

              <?php
              $demandaOptions = [
                "Hardware / Computador",
                "Multimídia / Projetores",
                "Outros Chamados",
                "Portal Institucional",
                "Rede / Internet",
                "Software / Sistemas"
              ];
              ?>

              <?php foreach ($demandaOptions as $d): ?>
                <option value="<?= $d ?>"
                  <?= ($old['demanda'] ?? '') === $d ? 'selected' : '' ?>>
                  <?= $d ?>
                </option>
              <?php endforeach; ?>

            </select>

            <?php if (!empty($errors['demanda'])): ?>
              <small class="text-red-600 text-sm"><?= $errors['demanda'] ?></small>
            <?php endif; ?>
          </div>

          <!-- PATRIMÔNIO -->
          <div class="min-w-0">
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Patrimônio
            </label>

            <input type="text"
              name="patrimonio"
              value="<?= $old['patrimonio'] ?? '' ?>"
              placeholder="Digite o número do patrimônio"
              class="w-full max-w-full min-w-0 rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">

            <?php if (!empty($errors['patrimonio'])): ?>
              <small class="text-red-600 text-sm"><?= $errors['patrimonio'] ?></small>
            <?php endif; ?>
          </div>

          <!-- DESCRIÇÃO -->
          <div class="min-w-0">
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Descrição do problema <span class="text-red-600">*</span>
            </label>

            <textarea name="descricao"
              rows="6"
              class="w-full max-w-full min-w-0 rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none"><?= $old['descricao'] ?? '' ?></textarea>

            <?php if (!empty($errors['descricao'])): ?>
              <small class="text-red-600 text-sm"><?= $errors['descricao'] ?></small>
            <?php endif; ?>
          </div>

          <!-- ARQUIVO -->
          <div class="min-w-0">
            <label class="mb-2 block text-sm font-medium text-gray-700">
              Anexo
            </label>

            <input type="file"
              name="arquivo[]"
              multiple
              accept="image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip"
              title="Formatos aceitos: PNG, JPG, PDF, DOC, DOCX, ZIP"
              class="w-full max-w-full min-w-0 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-100 outline-none">

            <p class="mt-2 text-xs text-gray-500">Formatos aceitos: PNG, JPG, PDF, DOC, DOCX e ZIP. Tamanho máximo de 10MB por arquivo.</p>
            <?php if (!empty($errors['arquivo'])): ?>
              <small class="text-red-600 text-sm"><?= $errors['arquivo'] ?></small>
            <?php endif; ?>
          </div>

          <!-- AÇÕES -->
          <div class="flex flex-col gap-3 border-t border-gray-200 pt-6 sm:flex-row sm:justify-end">

            <a href="<?= $cancelUrl ?>"
              class="w-full sm:w-auto text-center rounded-xl border border-gray-300 bg-white px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Cancelar
            </a>

            <button id="submitBtn"
              type="submit"
              class="w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">

              <i data-lucide="send" class="h-4 w-4"></i>

              <span id="btnText">Abrir Chamado</span>

              <span id="spinner"
                class="hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>

            </button>

          </div>

        </form>

      </section>

    </main>
  </div>
  <?= Footer::render() ?>

  <script>
    lucide.createIcons();

    const form = document.getElementById('create-ticket-form');
    const btn = document.getElementById('submitBtn');
    const spinner = document.getElementById('spinner');
    const btnText = document.getElementById('btnText');

    let isSubmitting = false;

    form.addEventListener('submit', function(e) {
      if (isSubmitting) {
        e.preventDefault();
        return;
      }

      isSubmitting = true;

      btn.disabled = true;
      btn.classList.add('opacity-70', 'cursor-not-allowed');

      spinner.classList.remove('hidden');
      btnText.textContent = 'Enviando...';

      document.body.style.cursor = 'wait';
    });
  </script>

</body>

</html>