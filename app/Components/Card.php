<?php

class Card
{
  private string $title;
  private string $description = '';
  private string $icon = '';
  private string $size = 'default';

  public function __construct(string $title)
  {
    $this->title = $title;
  }

  public static function make(string $title): self
  {
    return new self($title);
  }

  public function withDescription(string $description): self
  {
    $this->description = $description;
    return $this;
  }

  public function withIcon(string $icon): self
  {
    $this->icon = $icon;
    return $this;
  }

  public function render(): string
  {
    return sprintf(
      '<section class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden transition hover:shadow-md">%s</section>',
      $this->renderHeader()
    );
  }

  public function __toString(): string
  {
    return $this->render();
  }

  private function renderHeader(): string
  {
    $iconHtml = '';
    if (!empty($this->icon)) {
      $iconHtml = sprintf(
        '<i data-lucide="%s" class="h-6 w-6 text-blue-600"></i>',
        htmlspecialchars($this->icon, ENT_QUOTES)
      );
    }

    $descriptionHtml = '';
    if (!empty($this->description)) {
      $descriptionHtml = sprintf(
        '<p class="mt-2 text-sm text-gray-600">%s</p>',
        htmlspecialchars($this->description, ENT_QUOTES)
      );
    }

    return sprintf(
      '<header class="border-b border-gray-200 px-6 py-5">%s<div><h1 class="text-3xl font-bold text-gray-900">%s</h1>%s</div></header>',
      $iconHtml ? '<div class="mb-3">' . $iconHtml . '</div>' : '',
      htmlspecialchars($this->title, ENT_QUOTES),
      $descriptionHtml
    );
  }
}
