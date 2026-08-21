<?php

class Table
{
  private string $id;
  private array $columns;
  private array $data;
  private int $rowsPerPage;
  private bool $showColumnMenu;
  private string $emptyMessage;

  public function __construct(array $columns, array $data, array $options = [])
  {
    $this->id = $options['id'] ?? 'table_' . uniqid();
    $this->columns = $columns;
    $this->data = $data;
    $this->rowsPerPage = $options['rowsPerPage'] ?? 10;
    $this->showColumnMenu = $options['showColumnMenu'] ?? true;
    $this->emptyMessage = $options['emptyMessage'] ?? 'Nenhum registro encontrado.';
  }

  public function render(): string
  {
    return $this->toHtml();
  }

  public function toHtml(): string
  {
    return $this->renderRoot();
  }

  private function renderRoot(): string
  {
    return sprintf(
      '<div id="%s-root" class="overflow-visible">%s%s%s%s%s</div>',
      htmlspecialchars($this->id, ENT_QUOTES),
      $this->renderControls(),
      $this->renderTable(),
      $this->renderCards(),
      $this->renderFooter(),
      $this->renderScript()
    );
  }

  private function renderControls(): string
  {
    if (!$this->showColumnMenu) {
      return '';
    }

    $options = [5, 10, 20, 50];

    $rowsOptions = array_map(function ($value) {
      $selected = $value === $this->rowsPerPage ? ' selected' : '';
      return sprintf('<option value="%d"%s>%d</option>', $value, $selected, $value);
    }, $options);

    return sprintf(
      '<div class="border-b border-gray-200 px-6 py-5">' .
        '<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between overflow-visible">' .
        '<div class="space-y-1">' .
        '<p class="text-sm text-gray-500">Gerencie a tabela com ordenação, paginação e colunas visíveis.</p>' .
        '</div>' .
        '<div class="flex flex-wrap items-center gap-3 justify-end">' .
        '%s' .
        '<div class="flex items-center gap-2">' .
        '<span class="text-sm text-gray-600">Linhas:</span>' .
        '<select id="%s-rowsPerPage" class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm">%s</select>' .
        '</div>' .
        '</div>' .
        '</div>' .
        '</div>',
      $this->renderColumnMenu(),
      htmlspecialchars($this->id, ENT_QUOTES),
      implode('', $rowsOptions)
    );
  }

  private function renderColumnMenu(): string
  {
    $checkboxes = '';

    foreach ($this->columns as $column) {
      $checked = $column->isHidden() ? '' : ' checked';
      $checkboxes .= sprintf(
        '<label class="flex items-center gap-2 text-sm"><input type="checkbox" class="toggle-col" data-col="%s"%s>%s</label>',
        htmlspecialchars($column->getField(), ENT_QUOTES),
        $checked,
        htmlspecialchars($column->getLabel(), ENT_QUOTES)
      );
    }

    return sprintf(
      '<div class="relative overflow-visible">' .
        '<button id="%s-colBtn" class="flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100">' .
        '<i data-lucide="columns-3"></i>Colunas</button>' .
        '<div id="%s-colMenu" class="absolute right-0 z-50 mt-2 hidden w-60 overflow-visible rounded-xl border border-gray-200 bg-white p-4 shadow-xl col-menu">%s</div>' .
        '</div>',
      htmlspecialchars($this->id, ENT_QUOTES),
      htmlspecialchars($this->id, ENT_QUOTES),
      $checkboxes
    );
  }

  private function renderTable(): string
  {
    return sprintf(
      '<div class="hidden lg:block rounded-lg overflow-y-auto">' .
        '<table id="%s" class="w-full">' .
        '<thead class="bg-gradient-to-r from-blue-50 to-blue-100"><tr>%s</tr></thead>' .
        '<tbody id="%s-body" class="divide-y divide-gray-100">%s</tbody>' .
        '</table>' .
        '</div>',
      htmlspecialchars($this->id, ENT_QUOTES),
      $this->renderHeaders(),
      htmlspecialchars($this->id, ENT_QUOTES),
      $this->renderRows()
    );
  }

  private function renderHeaders(): string
  {
    $content = '';

    foreach ($this->columns as $column) {
      $content .= $column->renderHeader();
    }

    return $content;
  }

  private function renderRows(): string
  {
    if (empty($this->data)) {
      return sprintf(
        '<tr><td colspan="%d" class="px-4 py-6 text-center text-gray-500">%s</td></tr>',
        max(1, count($this->columns)),
        htmlspecialchars($this->emptyMessage, ENT_QUOTES)
      );
    }

    $content = '';

    foreach ($this->data as $item) {
      $content .= $this->renderRow($item);
    }

    return $content;
  }

  private function renderRow(array $item): string
  {
    $cells = '';

    foreach ($this->columns as $column) {
      $cells .= $column->renderCell($item);
    }

    return sprintf(
      '<tr class="table-row border-b border-gray-100 hover:bg-blue-50 transition">%s</tr>',
      $cells
    );
  }

  private function renderCards(): string
  {
    if (empty($this->data)) {
      return sprintf(
        '<div id="%s-cards" class="lg:hidden space-y-4 px-4 py-6">' .
          '<div class="rounded-3xl border border-gray-200 bg-white p-6 text-center text-sm text-gray-600">%s</div>' .
          '</div>',
        htmlspecialchars($this->id, ENT_QUOTES),
        htmlspecialchars($this->emptyMessage, ENT_QUOTES)
      );
    }

    $cards = '';
    $index = 0;

    foreach ($this->data as $item) {
      $fields = '';
      foreach ($this->columns as $column) {
        $hiddenStyle = $column->isHidden() ? ' style="display:none;"' : '';

        $fields .= sprintf(
          '<div class="grid gap-1" data-col="%s"%s>' .
            '<span class="text-xs font-semibold uppercase tracking-wide text-gray-500">%s</span>' .
            '<div class="text-sm text-gray-800">%s</div>' .
            '</div>',
          htmlspecialchars($column->getField(), ENT_QUOTES),
          $hiddenStyle,
          htmlspecialchars($column->getLabel(), ENT_QUOTES),
          $column->renderCardField($item)
        );
      }

      $cards .= sprintf(
        '<article class="table-card rounded-3xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md" data-row-index="%d">' .
          '<div class="space-y-4">%s</div>' .
          '</article>',
        $index,
        $fields
      );

      $index++;
    }

    return sprintf(
      '<div id="%s-cards" class="lg:hidden space-y-4 px-4 py-6">%s</div>',
      htmlspecialchars($this->id, ENT_QUOTES),
      $cards
    );
  }

  private function renderFooter(): string
  {
    return sprintf(
      '<footer class="grid gap-4 lg:grid-cols-3 items-center border-t border-gray-200 bg-gradient-to-r from-gray-50 to-blue-50 px-6 py-4">' .
        '<div><p class="text-sm font-medium text-gray-700">Total: <span class="text-blue-600">%d</span></p></div>' .
        '<div id="%s-pagination" class="flex flex-wrap items-center justify-center gap-2 overflow-x-auto py-2"></div>' .
        '<div class="flex justify-end text-xs text-gray-500">Página <span id="%s-pageInfo">1</span></div>' .
        '</footer>',
      count($this->data),
      htmlspecialchars($this->id, ENT_QUOTES),
      htmlspecialchars($this->id, ENT_QUOTES)
    );
  }

  private function renderScript(): string
  {
    $id = htmlspecialchars($this->id, ENT_QUOTES);

    return sprintf(
      '<script>' .
        '(function () {' .
        '  const root = document.getElementById("%s-root");' .
        '  if (!root) return;' .
        '  const btn = root.querySelector("#%s-colBtn");' .
        '  const menu = root.querySelector("#%s-colMenu");' .
        '  const rowsPerPageSelect = root.querySelector("#%s-rowsPerPage");' .
        '  const pagination = root.querySelector("#%s-pagination");' .
        '  const tableBody = root.querySelector("#%s-body");' .
        '  const cardsContainer = root.querySelector("#%s-cards");' .
        '  const rows = Array.from(tableBody.querySelectorAll("tr.table-row"));' .
        '  const cards = cardsContainer ? Array.from(cardsContainer.querySelectorAll(".table-card")) : [];' .
        '  const items = rows.map((row, index) => ({ row, card: cards[index] }));' .
        '  let rowsPerPage = Number(rowsPerPageSelect.value);' .
        '  let currentPage = 1;' .
        '  let currentSort = { column: null, asc: true };' .
        '  function renderTable() {' .
        '    const start = (currentPage - 1) * rowsPerPage;' .
        '    const end = start + rowsPerPage;' .
        '    items.forEach(({ row, card }, index) => {' .
        '      const visible = index >= start && index < end;' .
        '      row.style.display = visible ? "" : "none";' .
        '      if (card) card.style.display = visible ? "" : "none";' .
        '    });' .
        '  }' .
        '  function createButton(label, page, disabled = false, active = false) {' .
        '    const button = document.createElement("button");' .
        '    button.textContent = label;' .
        '    button.disabled = disabled;' .
        '    button.className = active ? "rounded-lg bg-blue-600 px-4 py-2 min-w-[44px] text-sm text-white font-medium" : "rounded-lg border border-gray-300 bg-white px-4 py-2 min-w-[44px] text-sm text-gray-700 transition hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-50";' .
        '    button.onclick = () => { currentPage = page; renderTable(); renderPagination(); };' .
        '    return button;' .
        '  }' .
        '  function renderPagination() {' .
        '    pagination.innerHTML = "";' .
        '    const totalPages = Math.max(1, Math.ceil(items.length / rowsPerPage));' .
        '    currentPage = Math.min(Math.max(1, currentPage), totalPages);' .
        '    pagination.appendChild(createButton("Anterior", currentPage - 1, currentPage === 1));' .
        '    for (let i = 1; i <= totalPages; i++) {' .
        '      pagination.appendChild(createButton(i, i, false, currentPage === i));' .
        '    }' .
        '    pagination.appendChild(createButton("Próximo", currentPage + 1, currentPage === totalPages));' .
        '    const pageInfo = root.querySelector("#%s-pageInfo");' .
        '    if (pageInfo) pageInfo.textContent = currentPage.toString();' .
        '  }' .
        '  if (btn && menu) {' .
        '    const isMobile = () => window.matchMedia("(max-width: 640px)").matches;' .
        '    const resetMenuPosition = () => {' .
        '      menu.style.position = "absolute";' .
        '      menu.style.top = "";' .
        '      menu.style.left = "";' .
        '      menu.style.width = "";' .
        '      menu.style.maxWidth = "";' .
        '      menu.style.right = "";' .
        '    };' .
        '    const positionMenuFixed = () => {' .
        '      if (!isMobile()) {' .
        '        resetMenuPosition();' .
        '        return;' .
        '      }' .
        '      const rect = btn.getBoundingClientRect();' .
        '      const maxWidth = Math.min(rect.width, window.innerWidth - 24);' .
        '      let left = rect.left;' .
        '      if (left + maxWidth > window.innerWidth - 12) {' .
        '        left = Math.max(12, window.innerWidth - maxWidth - 12);' .
        '      }' .
        '      menu.style.position = "fixed";' .
        '      menu.style.top = `${rect.bottom + 8}px`;' .
        '      menu.style.left = `${left}px`;' .
        '      menu.style.width = `${maxWidth}px`;' .
        '      menu.style.maxWidth = "calc(100vw - 24px)";' .
        '      menu.style.right = "auto";' .
        '    };' .
        '    btn.addEventListener("click", (event) => {' .
        '      event.stopPropagation();' .
        '      menu.classList.toggle("hidden");' .
        '      if (!menu.classList.contains("hidden")) {' .
        '        positionMenuFixed();' .
        '      } else {' .
        '        resetMenuPosition();' .
        '      }' .
        '    });' .
        '    window.addEventListener("resize", () => { if (!menu.classList.contains("hidden")) positionMenuFixed(); });' .
        '    window.addEventListener("scroll", () => { if (!menu.classList.contains("hidden")) positionMenuFixed(); }, true);' .
        '    document.addEventListener("click", (event) => {' .
        '      if (!btn.contains(event.target) && !menu.contains(event.target)) {' .
        '        menu.classList.add("hidden");' .
        '        resetMenuPosition();' .
        '      }' .
        '    });' .
        '  }' .
        '  root.querySelectorAll(".toggle-col").forEach(input => {' .
        '    input.addEventListener("change", function () {' .
        '      const col = this.dataset.col;' .
        '      root.querySelectorAll(`[data-col="${col}"]`).forEach(el => { el.style.display = this.checked ? "" : "none"; });' .
        '    });' .
        '  });' .
        '  root.querySelectorAll("[data-sort]").forEach(header => {' .
        '    if (!header.dataset.sort) return;' .
        '    header.addEventListener("click", () => {' .
        '      const col = header.dataset.sort;' .
        '      const asc = currentSort.column === col ? !currentSort.asc : true;' .
        '      currentSort = { column: col, asc };' .
        '      items.sort((a, b) => {' .
        '        const A = (a.row.querySelector(`[data-col="${col}"]`)?.textContent || "").trim().toLowerCase();' .
        '        const B = (b.row.querySelector(`[data-col="${col}"]`)?.textContent || "").trim().toLowerCase();' .
        '        const aNum = Number(A); const bNum = Number(B);' .
        '        if (!Number.isNaN(aNum) && !Number.isNaN(bNum)) { return asc ? aNum - bNum : bNum - aNum; }' .
        '        return asc ? A.localeCompare(B) : B.localeCompare(A);' .
        '      });' .
        '      items.forEach(({ row, card }) => {' .
        '        tableBody.appendChild(row);' .
        '        if (card) cardsContainer.appendChild(card);' .
        '      });' .
        '      currentPage = 1; renderTable(); renderPagination();' .
        '    });' .
        '  });' .
        '  rowsPerPageSelect.addEventListener("change", () => {' .
        '    rowsPerPage = Number(rowsPerPageSelect.value); currentPage = 1; renderTable(); renderPagination();' .
        '  });' .
        '  renderTable(); renderPagination(); lucide.createIcons();' .
        '})();' .
        '</script>',
      $id,
      $id,
      $id,
      $id,
      $id,
      $id,
      $id,
      $id
    );
  }
}
