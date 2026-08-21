<?php

class Header
{
  private static array $items = [
    [
      'label' => 'Central de atendimento Ninfa',
      'href' => null,
      'children' => [
        [
          'label' => 'Histórico de chamados',
          'href' => 'ninfa.php?page=atendimentos&action=tickets.index',
        ],
        [
          'label' => 'Lançar novo chamado',
          'href' => 'ninfa.php?page=atendimentos&action=tickets.create',
        ]
      ],
    ],
  ];

  public static function setItems(array $items): void
  {
    self::$items = $items;
  }

  public static function getItems(): array
  {
    return self::$items;
  }

  public static function render(): string
  {
    $items = self::getItems();
    $userName = htmlspecialchars($_SESSION['usuario'][0] ?? 'Perfil', ENT_QUOTES);

    ob_start();
?>

    <header class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm"
      x-data="{ mobileOpen: false }">

      <div class="mx-auto max-w-7xl px-4 h-16 flex items-center justify-between">

        <!-- LEFT -->
        <div class="flex items-center gap-4">

          <!-- Mobile button -->
          <button class="lg:hidden p-2 rounded hover:bg-gray-100"
            @click="mobileOpen = true">
            <i data-lucide="menu" class="w-5 h-5"></i>
          </button>

          <!-- Logo -->
          <a href="ninfa.php?page=home" class="flex items-center">
            <img src="images/logoninfaabelha.jpg" alt="NINFA" class="h-10 w-auto" />
          </a>

          <!-- DESKTOP MENU -->
          <nav class="hidden lg:flex items-center gap-1 ml-6">
            <?php foreach ($items as $item): ?>
              <?= self::renderDesktopItem($item) ?>
            <?php endforeach; ?>
          </nav>

        </div>

        <!-- RIGHT -->
        <div class="flex items-center gap-3">

          <div class="hidden md:flex items-center gap-2 text-sm text-gray-600">
            <i data-lucide="user" class="w-4 h-4"></i>
            <span><?= $userName ?></span>
          </div>

          <a href="ninfa.php?action=logout"
            class="px-3 py-2 text-sm rounded-lg hover:bg-gray-100 flex items-center gap-2">
            <i data-lucide="log-out" class="w-4 h-4"></i>
            Sair
          </a>

        </div>
      </div>

      <!-- MOBILE OVERLAY -->
      <div x-show="mobileOpen"
        x-cloak
        class="fixed inset-0 bg-black/40 z-40"
        @click="mobileOpen = false"
        x-transition></div>

      <!-- MOBILE DRAWER -->
      <aside x-show="mobileOpen"
        x-cloak
        x-transition
        class="fixed left-0 top-0 w-72 h-full bg-white z-50 shadow-lg p-4 overflow-y-auto">

        <div class="flex items-center justify-between mb-4">
          <span class="font-bold text-lg">Menu</span>

          <button @click="mobileOpen = false">
            <i data-lucide="x" class="w-5 h-5"></i>
          </button>
        </div>

        <nav class="space-y-2">
          <?php foreach ($items as $item): ?>
            <?= self::renderMobileItem($item) ?>
          <?php endforeach; ?>
        </nav>

      </aside>

    </header>

    <script>
      document.addEventListener("DOMContentLoaded", () => {
        lucide.createIcons();
      });
    </script>

  <?php
    return ob_get_clean();
  }

  /* =========================
   * DESKTOP ITEM (MULTI LEVEL FIXED)
   * ========================= */
  private static function renderDesktopItem(array $item): string
  {
    $label = htmlspecialchars($item['label'], ENT_QUOTES);
    $href = $item['href'] ?? null;
    $children = $item['children'] ?? [];

    // ITEM SIMPLES
    if (empty($children)) {
      $url = htmlspecialchars($href ?? '#', ENT_QUOTES);

      return "
        <a href='{$url}'
           class='px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg'>
          {$label}
        </a>
      ";
    }

    // DROPDOWN
    ob_start();
  ?>

    <div class="relative" x-data="{ open: false }" @click.outside="open = false">

      <button class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg flex items-center gap-1"
        @click="open = !open">

        <?= $label ?>
        <i data-lucide="chevron-down" class="w-4 h-4"></i>
      </button>

      <div x-show="open"
        x-cloak
        x-transition
        class="absolute left-0 mt-2 w-64 bg-white border rounded-lg shadow-lg z-50">

        <?php foreach ($children as $child): ?>
          <?= self::renderMenuItem($child) ?>
        <?php endforeach; ?>

      </div>

    </div>

  <?php
    return ob_get_clean();
  }

  /* =========================
   * RECURSIVE MENU ITEM (FIX PRINCIPAL)
   * ========================= */
  private static function renderMenuItem(array $item): string
  {
    $label = htmlspecialchars($item['label'], ENT_QUOTES);
    $href = $item['href'] ?? null;
    $children = $item['children'] ?? [];

    // LINK FINAL
    if (empty($children)) {
      $url = htmlspecialchars($href ?? '#', ENT_QUOTES);

      return "
        <a href='{$url}'
           class='block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50'>
          {$label}
        </a>
      ";
    }

    // SUBMENU LATERAL (MULTI-NÍVEL FUNCIONA AQUI)
    ob_start();
  ?>

    <div class="relative group">

      <div class="flex justify-between items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer">

        <span><?= $label ?></span>
        <i data-lucide="chevron-right" class="w-4 h-4"></i>

      </div>

      <div class="absolute top-0 left-full ml-1 hidden group-hover:block w-64 bg-white border rounded-lg shadow-lg z-50">

        <?php foreach ($children as $child): ?>
          <?= self::renderMenuItem($child) ?>
        <?php endforeach; ?>

      </div>

    </div>

  <?php
    return ob_get_clean();
  }

  /* =========================
   * MOBILE (ACCORDION MULTI LEVEL)
   * ========================= */
  private static function renderMobileItem(array $item): string
  {
    $label = htmlspecialchars($item['label'], ENT_QUOTES);
    $href = $item['href'] ?? null;
    $children = $item['children'] ?? [];

    // LINK FINAL
    if (empty($children)) {
      $url = htmlspecialchars($href ?? '#', ENT_QUOTES);
      return "<a href='{$url}' class='block py-2 text-sm text-gray-700'>{$label}</a>";
    }

    ob_start();
  ?>

    <div x-data="{ open: false }" class="border-b pb-2">

      <button class="w-full flex justify-between py-2 text-sm font-medium text-gray-800"
        @click="open = !open">

        <?= $label ?>
        <i data-lucide="chevron-down" class="w-4 h-4"></i>
      </button>

      <div x-show="open" x-cloak class="pl-3 space-y-1">
        <?php foreach ($children as $child): ?>
          <?= self::renderMobileItem($child) ?>
        <?php endforeach; ?>
      </div>

    </div>

<?php
    return ob_get_clean();
  }
}
