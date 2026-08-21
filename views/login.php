<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - NINFA</title>

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="min-h-screen bg-gray-50 flex flex-col">
  <main class="flex flex-1 items-center justify-center px-4">
    <div class="w-full max-w-md">
      <div class="mb-8 text-center">
        <div class="mx-auto mb-4 w-24">
          <img src="images/logoninfaabelha.jpg"
            alt="NINFA"
            class="mx-auto h-24 w-auto" />
        </div>
        <p class="mt-2 text-sm text-gray-600">
          <span class="text-red-500 font-medium">Entre com suas credenciais da intranet para acessar o sistema.</span>
        </p>
      </div>
      <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
        <?php if (!empty($loginError)): ?>
          <div
            role="alert"
            aria-live="assertive"
            class="mb-5 flex gap-3 rounded-xl border border-red-200 bg-red-50 p-4">
            <i data-lucide="alert-circle"
              class="mt-0.5 h-5 w-5 shrink-0 text-red-600"
              aria-hidden="true"></i>
            <div class="text-sm text-red-700">
              <?= htmlspecialchars($loginError, ENT_QUOTES, 'UTF-8') ?>
            </div>
          </div>
        <?php endif; ?>
        <form method="POST" action="ninfa.php?action=login" class="space-y-5">
          <input
            type="hidden"
            name="csrf_token"
            value="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
          <div>
            <label for="name"
              class="mb-2 block text-sm font-medium text-gray-700">
              Usuário
            </label>
            <input
              id="name"
              name="name"
              type="text"
              required
              autocomplete="username"
              value="<?= htmlspecialchars($loginValues['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
              placeholder="Digite seu usuário"
              class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
          </div>
          <div>
            <label for="password"
              class="mb-2 block text-sm font-medium text-gray-700">
              Senha
            </label>
            <div class="relative">
              <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="current-password"
                placeholder="Digite sua senha"
                class="w-full rounded-xl border border-gray-300 px-4 py-3 pr-12 text-sm text-gray-900 placeholder-gray-400 transition focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
              <button
                type="button"
                id="togglePassword"
                aria-label="Mostrar senha"
                class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-500 hover:text-gray-700">
              </button>
            </div>
          </div>
          <button
            type="submit"
            class="flex min-h-[48px] w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
            <i data-lucide="log-in"
              class="h-4 w-4"
              aria-hidden="true"></i>
            Entrar
          </button>
        </form>
      </div>
    </div>
  </main>
  <?= Footer::render() ?>
  <script>
    lucide.createIcons();
  </script>
</body>

</html>