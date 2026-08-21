<?php

class Footer
{
  public static function render(): string
  {
    ob_start();
?>
    <footer class="border-t border-slate-200 bg-white py-4">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center text-sm text-slate-500">
        Equipe de desenvolvimento de sistemas - NINFA &copy; 2026
      </div>
    </footer>
<?php
    return ob_get_clean();
  }
}
