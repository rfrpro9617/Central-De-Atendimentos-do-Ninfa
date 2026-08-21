<?php

abstract class Column
{
  protected $field;
  protected $label;
  protected $sortable;
  protected $hidden;
  protected $headerClass;
  protected $cellClass;
  protected $formatter;
  protected $customRender;
  protected $attributes;

  public function __construct(string $field, string $label, array $options = [])
  {
    $this->field = $field;
    $this->label = $label;
    $this->sortable = $options['sortable'] ?? false;
    $this->hidden = $options['hidden'] ?? false;
    $this->headerClass = $options['headerClass'] ?? '';
    $this->cellClass = $options['cellClass'] ?? '';
    $this->formatter = $options['formatter'] ?? null;
    $this->customRender = $options['customRender'] ?? null;
    $this->attributes = $options['attributes'] ?? [];
  }

  public static function make(string $field, string $label, array $options = []): TextColumn
  {
    return new TextColumn($field, $label, $options);
  }

  public static function badge(string $field, string $label, array $options = []): BadgeColumn
  {
    return new BadgeColumn($field, $label, $options);
  }

  public static function link(string $field, string $label, array $options = []): LinkColumn
  {
    return new LinkColumn($field, $label, $options);
  }

  public static function action(string $field, string $label, array $options = []): ActionColumn
  {
    return new ActionColumn($field, $label, $options);
  }

  public function withFormatter(callable $formatter): self
  {
    $this->formatter = $formatter;
    return $this;
  }

  public function withCustomRender(callable $render): self
  {
    $this->customRender = $render;
    return $this;
  }

  public function sortable(bool $value = true): self
  {
    $this->sortable = $value;
    return $this;
  }

  public function hidden(bool $value = true): self
  {
    $this->hidden = $value;
    return $this;
  }

  public function headerClass(string $class): self
  {
    $this->headerClass = $class;
    return $this;
  }

  public function cellClass(string $class): self
  {
    $this->cellClass = $class;
    return $this;
  }

  public function getField(): string
  {
    return $this->field;
  }

  public function getLabel(): string
  {
    return $this->label;
  }

  public function isSortable(): bool
  {
    return $this->sortable;
  }

  public function isHidden(): bool
  {
    return $this->hidden;
  }

  public function getHeaderClass(): string
  {
    return $this->headerClass;
  }

  public function getCellClass(): string
  {
    return $this->cellClass;
  }

  public function renderHeader(): string
  {
    $classes = trim('px-4 py-3 text-left text-xs font-semibold uppercase ' . $this->headerClass);
    $content = $this->renderHeaderContent();

    if ($this->hidden) {
      return sprintf(
        '<th data-col="%s" data-sort="%s" class="%s" style="display:none;">%s</th>',
        htmlspecialchars($this->field, ENT_QUOTES),
        htmlspecialchars($this->sortable ? $this->field : '', ENT_QUOTES),
        $classes,
        $content
      );
    }

    return sprintf(
      '<th data-col="%s" data-sort="%s" class="%s">%s</th>',
      htmlspecialchars($this->field, ENT_QUOTES),
      htmlspecialchars($this->sortable ? $this->field : '', ENT_QUOTES),
      $classes,
      $content
    );
  }

  protected function renderHeaderContent(): string
  {
    $title = htmlspecialchars($this->label, ENT_QUOTES);

    if (!$this->sortable) {
      return $title;
    }

    return sprintf(
      '<div class="flex items-center gap-2">%s<i data-lucide="arrow-up-down" class="h-4 w-4 opacity-60 transition group-hover:opacity-100"></i></div>',
      $title
    );
  }

  public function renderCell(array $item): string
  {
    return $this->renderCellWrapper($this->renderValue($item));
  }

  public function renderCardField(array $item): string
  {
    return $this->renderValue($item);
  }

  abstract protected function renderValue(array $item): string;

  protected function resolveValue(array $item)
  {
    $value = $item[$this->field] ?? '';

    if ($this->formatter !== null) {
      return ($this->formatter)($value, $item);
    }

    return $value;
  }

  protected function renderCellWrapper(string $content): string
  {
    $style = $this->hidden ? ' style="display:none;"' : '';
    $class = trim('px-4 py-4 ' . $this->cellClass);

    return sprintf(
      '<td data-col="%s" class="%s"%s>%s</td>',
      htmlspecialchars($this->field, ENT_QUOTES),
      $class,
      $style,
      $content
    );
  }
}

class TextColumn extends Column
{
  protected function renderValue(array $item): string
  {
    if ($this->customRender !== null) {
      return (string) ($this->customRender)($item);
    }

    $value = $this->resolveValue($item);

    $extraClass = ($this->attributes['preserveLineBreaks'] ?? false)
      ? ' whitespace-pre-line'
      : '';

    return sprintf(
      '<span class="block text-sm text-gray-800%s">%s</span>',
      $extraClass,
      htmlspecialchars((string) $value, ENT_QUOTES)
    );
  }
}

class BadgeColumn extends Column
{
  protected function renderValue(array $item): string
  {
    if ($this->customRender !== null) {
      return (string) ($this->customRender)($item);
    }

    $value = $this->resolveValue($item);
    $badgeClass = $this->attributes['badgeClass'] ?? 'inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700';

    if ($value === '' || $value === null) {
      return '<span class="text-gray-400">-</span>';
    }

    return sprintf(
      '<span class="%s">%s</span>',
      htmlspecialchars($badgeClass, ENT_QUOTES),
      htmlspecialchars((string) $value, ENT_QUOTES)
    );
  }
}

class LinkColumn extends Column
{
  protected function renderValue(array $item): string
  {
    if ($this->customRender !== null) {
      return (string) ($this->customRender)($item);
    }

    $value = $this->resolveValue($item);
    $href = $this->attributes['href'] ?? '#';

    if (is_callable($href)) {
      $href = $href($item);
    }

    $target = $this->attributes['target'] ?? '_self';
    $linkClass = $this->attributes['linkClass'] ?? 'text-green-700 hover:underline';

    return sprintf(
      '<a href="%s" target="%s" class="%s">%s</a>',
      htmlspecialchars((string) $href, ENT_QUOTES),
      htmlspecialchars((string) $target, ENT_QUOTES),
      htmlspecialchars($linkClass, ENT_QUOTES),
      htmlspecialchars((string) $value, ENT_QUOTES)
    );
  }
}

class ActionColumn extends Column
{
  protected function renderValue(array $item): string
  {
    if ($this->customRender !== null) {
      return (string) ($this->customRender)($item);
    }

    $value = $this->resolveValue($item);
    return sprintf('<span class="text-sm text-gray-700">%s</span>', htmlspecialchars((string) $value, ENT_QUOTES));
  }
}
