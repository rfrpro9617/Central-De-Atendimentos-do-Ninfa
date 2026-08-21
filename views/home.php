<?php
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home - NINFA</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
  <?php require __DIR__ . '/administrativo/atendimentoAoUsuarioNinfa/partials/head-scripts.php'; ?>
</head>

<body class="min-h-screen flex flex-col bg-gradient-to-br from-slate-50 to-slate-100 text-slate-900">
  <?= Header::render() ?>
  <main class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-8 sm:py-10 lg:py-14 flex-1">
    <section class="rounded-2xl sm:rounded-[32px] border border-slate-200 bg-white shadow-xl">
      <div class="p-5 sm:p-8 lg:p-10">
        <div class="max-w-3xl mx-auto text-center sm:text-left">
          <p class="text-xs sm:text-sm uppercase tracking-[0.2em] text-blue-600">
            Bem-vindo ao NINFA
          </p>
          <h1 class="mt-3 sm:mt-4 text-3xl sm:text-4xl lg:text-5xl font-semibold leading-tight text-slate-900">
            Central de Atendimentos do NINFA.
          </h1>
          <p class="mt-5 sm:mt-6 text-base sm:text-lg leading-relaxed text-slate-600">
            Utilize a central de atendimentos do NINFA para abrir chamados, acompanhar solicitações e acessar recursos de suporte.
            <span class="text-red-500 font-semibold">Clique no menu superior para navegar pelos módulos e funcionalidades disponíveis.</span>
          </p>
        </div>
      </div>
    </section>
  </main>
  <?= Footer::render() ?>
  <script>
    lucide.createIcons();
  </script>
</body>

</html>