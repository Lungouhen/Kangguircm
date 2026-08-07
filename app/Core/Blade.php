<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Blade-inspired template engine for PHP.
 *
 * Compiles Blade-like syntax into native PHP for execution.
 * Supports template inheritance, sections, components, and directives.
 *
 * Following php-pro skill: strict types, PHPDoc, SOLID design.
 * Following security-auditor skill: all {{ }} output is escaped.
 */
class Blade
{
    /** @var string Base path for view files */
    private static string $viewPath = '';

    /** @var array<string, string> Compiled template cache */
    private static array $cache = [];

    /** @var array<string, mixed> Shared data across all views */
    private static array $shared = [];

    /** @var array<string, string> Section content storage */
    private static array $sections = [];

    /** @var string|null Current layout being extended */
    private static ?string $parentLayout = null;

    /** @var string|null Current section being captured */
    private static ?string $currentSection = null;

    /** @var int Loop depth counter */
    private static int $loopDepth = 0;

    /**
     * Initialize the Blade engine with a view path.
     *
     * @param string $viewPath Absolute path to views directory
     */
    public static function configure(string $viewPath): void
    {
        self::$viewPath = rtrim($viewPath, '/');
    }

    /**
     * Share data with all views.
     *
     * @param string $key
     * @param mixed $value
     */
    public static function share(string $key, mixed $value): void
    {
        self::$shared[$key] = $value;
    }

    /**
     * Render a view and return the HTML output.
     *
     * @param string $view Dot-notation view name (e.g., 'dashboard' or 'cms.index')
     * @param array<string, mixed> $data Variables to pass to the view
     * @return string Rendered HTML
     */
    public static function render(string $view, array $data = []): string
    {
        // Reset state for this render cycle
        self::$sections = [];
        self::$parentLayout = null;
        self::$currentSection = null;
        self::$loopDepth = 0;

        $filePath = self::resolveViewPath($view);

        if (!file_exists($filePath)) {
            throw new \RuntimeException("View not found: {$view} (looked in {$filePath})");
        }

        // Merge shared data with view-specific data
        $allData = array_merge(self::$shared, $data);

        // First pass: execute the view (captures sections, sets parent layout)
        self::executeView($filePath, $allData);

        // If the view extended a layout, render the layout with sections
        if (self::$parentLayout !== null) {
            $layoutPath = self::resolveViewPath(self::$parentLayout);
            self::$parentLayout = null;

            if (!file_exists($layoutPath)) {
                throw new \RuntimeException("Layout not found: " . self::$parentLayout);
            }

            // Render layout (it will use @yield to pull in sections)
            ob_start();
            self::executeView($layoutPath, $allData);
            return ob_get_clean();
        }

        // No layout — return the direct output
        ob_start();
        self::executeView($filePath, $allData);
        return ob_get_clean();
    }

    /**
     * Render a view and echo it directly.
     *
     * @param string $view
     * @param array<string, mixed> $data
     */
    public static function display(string $view, array $data = []): void
    {
        echo self::render($view, $data);
    }

    /**
     * Compile Blade syntax to PHP and execute.
     *
     * @param string $filePath Absolute path to the view file
     * @param array<string, mixed> $data Variables available in the view
     */
    private static function executeView(string $filePath, array $data): void
    {
        $source = file_get_contents($filePath);
        $compiled = self::compile($source);

        // Write to project-local temp file for execution (WASM can access these)
        $tmpDir = dirname(__DIR__, 2) . '/storage/cache/blade';
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }
        $tmpFile = $tmpDir . '/' . md5($filePath . $compiled) . '.php';

        if (!file_exists($tmpFile) || filemtime($tmpFile) < filemtime($filePath)) {
            file_put_contents($tmpFile, $compiled);
        }

        extract($data, EXTR_SKIP);
        require $tmpFile;
    }

    /**
     * Compile Blade template source into valid PHP.
     *
     * @param string $source Raw Blade template source
     * @return string Compiled PHP code
     */
    public static function compile(string $source): string
    {
        $compiled = $source;

        // === Remove comments first (before echo compilation) ===
        $compiled = self::compileComments($compiled);

        // === Layout directives ===
        $compiled = self::compileExtends($compiled);
        $compiled = self::compileSections($compiled);
        $compiled = self::compileYields($compiled);

        // === Raw PHP blocks ===
        $compiled = self::compilePhpBlocks($compiled);

        // === Echo statements ===
        $compiled = self::compileEchoRaw($compiled);
        $compiled = self::compileEchoEscaped($compiled);

        // === Control structures ===
        $compiled = self::compileConditionals($compiled);
        $compiled = self::compileLoops($compiled);

        // === Directive shortcuts ===
        $compiled = self::compileDirectives($compiled);

        // === Include directive ===
        $compiled = self::compileIncludes($compiled);

        // === Auth directives ===
        $compiled = self::compileAuth($compiled);

        return $compiled;
    }

    /**
     * Compile {{-- comments --}} (remove them).
     */
    private static function compileComments(string $source): string
    {
        return preg_replace('/\{\{--.*?--\}\}/s', '', $source);
    }

    /**
     * Compile @php ... @endphp blocks.
     */
    private static function compilePhpBlocks(string $source): string
    {
        $source = preg_replace('/@php\s*(.*?)\s*@endphp/s', '<?php $1 ?>', $source);
        return $source;
    }

    // ─── Layout Compilation ──────────────────────────────────────────

    /**
     * Compile @extends('layout.name') directive.
     */
    private static function compileExtends(string $source): string
    {
        return preg_replace(
            "/@extends\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/",
            '<?php \App\Core\Blade::setParent("$1"); ?>',
            $source
        );
    }

    /**
     * Compile @section('name') ... @endsection blocks.
     */
    private static function compileSections(string $source): string
    {
        // @section('name') ... @endsection (block form)
        $source = preg_replace(
            "/@section\s*\(\s*['\"]([^'\"]+)['\"]\s*\)(.*?)@endsection/s",
            '<?php \App\Core\Blade::startSection("$1"); ?>$2<?php \App\Core\Blade::endSection(); ?>',
            $source
        );

        // @section('name', 'value') (inline form)
        $source = preg_replace(
            "/@section\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/",
            '<?php \App\Core\Blade::setSection("$1", "$2"); ?>',
            $source
        );

        return $source;
    }

    /**
     * Compile @yield('name') and @yield('name', 'default') directives.
     */
    private static function compileYields(string $source): string
    {
        // @yield('name', 'default')
        $source = preg_replace(
            "/@yield\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*['\"]([^'\"]*?)['\"]\s*\)/",
            '<?php echo \App\Core\Blade::yieldSection("$1", "$2"); ?>',
            $source
        );

        // @yield('name')
        $source = preg_replace(
            "/@yield\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/",
            '<?php echo \App\Core\Blade::yieldSection("$1"); ?>',
            $source
        );

        return $source;
    }

    // ─── Echo Compilation ────────────────────────────────────────────

    /**
     * Compile {!! $expression !!} (raw, unescaped output).
     */
    private static function compileEchoRaw(string $source): string
    {
        return preg_replace('/\{!!\s*(.+?)\s*!!\}/', '<?php echo $1; ?>', $source);
    }

    /**
     * Compile {{ $expression }} (HTML-escaped output — XSS prevention).
     */
    private static function compileEchoEscaped(string $source): string
    {
        // If expression already has null coalescing, don't add extra ??
        return preg_replace_callback(
            '/\{\{\s*(.+?)\s*\}\}/',
            function ($m) {
                $expr = trim($m[1]);
                if (str_contains($expr, '??')) {
                    return '<?php echo htmlspecialchars((string)(' . $expr . '), ENT_QUOTES, \'UTF-8\'); ?>';
                }
                return '<?php echo htmlspecialchars((string)(' . $expr . ' ?? \'\'), ENT_QUOTES, \'UTF-8\'); ?>';
            },
            $source
        );
    }

    // ─── Control Structures ──────────────────────────────────────────

    /**
     * Compile @if, @elseif, @else, @endif, @isset, @empty, @unless.
     */
    private static function compileConditionals(string $source): string
    {
        // Use a helper to match balanced parentheses for @if/@elseif/@foreach etc.
        $source = self::compileBalancedDirective($source, '@if', '<?php if(%s): ?>');
        $source = self::compileBalancedDirective($source, '@elseif', '<?php elseif(%s): ?>');
        $source = str_replace('@else', '<?php else: ?>', $source);
        $source = str_replace('@endif', '<?php endif; ?>', $source);

        // @isset($var) ... @endisset
        $source = self::compileBalancedDirective($source, '@isset', '<?php if(isset(%s)): ?>');
        $source = str_replace('@endisset', '<?php endif; ?>', $source);

        // @empty($var) ... @endempty
        $source = self::compileBalancedDirective($source, '@empty', '<?php if(empty(%s)): ?>');
        $source = str_replace('@endempty', '<?php endif; ?>', $source);

        // @unless($cond) ... @endunless
        $source = self::compileBalancedDirective($source, '@unless', '<?php if(!(%s)): ?>');
        $source = str_replace('@endunless', '<?php endif; ?>', $source);

        return $source;
    }

    /**
     * Compile a Blade directive that uses balanced parentheses.
     *
     * Handles nested parens like @if(count($items) > 0) correctly.
     *
     * @param string $source Template source
     * @param string $directive Blade directive (e.g., '@if')
     * @param string $replacement PHP replacement with %s placeholder for expression
     * @return string
     */
    private static function compileBalancedDirective(string $source, string $directive, string $replacement): string
    {
        $result = '';
        $len = strlen($source);
        $directiveLen = strlen($directive);

        for ($i = 0; $i < $len; $i++) {
            if (substr($source, $i, $directiveLen) === $directive) {
                // Found the directive. Now find the balanced parentheses.
                $j = $i + $directiveLen;

                // Skip whitespace
                while ($j < $len && $source[$j] === ' ') $j++;

                if ($j < $len && $source[$j] === '(') {
                    // Found opening paren — now match balanced
                    $depth = 0;
                    $start = $j + 1;
                    $k = $j;

                    while ($k < $len) {
                        if ($source[$k] === '(') $depth++;
                        elseif ($source[$k] === ')') {
                            $depth--;
                            if ($depth === 0) {
                                // Found the matching close paren
                                $expression = substr($source, $start, $k - $start);
                                $result .= sprintf($replacement, $expression);
                                $i = $k; // Skip past the closing paren
                                break;
                            }
                        }
                        $k++;
                    }

                    if ($depth !== 0) {
                        // Unbalanced — output literally
                        $result .= $directive;
                    }
                } else {
                    // No opening paren — output literally
                    $result .= $directive;
                }
            } else {
                $result .= $source[$i];
            }
        }

        return $result;
    }

    /**
     * Compile @foreach, @for, @while, @forelse loops.
     */
    private static function compileLoops(string $source): string
    {
        // @foreach — handled by compileForeach with $loop variable support
        $source = self::compileForeach($source);
        $source = str_replace('@endforeach', '<?php $__loopIndex++; endforeach; ?>', $source);

        // @for($i = 0; $i < 10; $i++)
        $source = self::compileBalancedDirective($source, '@for', '<?php for(%s): ?>');
        $source = str_replace('@endfor', '<?php endfor; ?>', $source);

        // @while($condition)
        $source = self::compileBalancedDirective($source, '@while', '<?php while(%s): ?>');
        $source = str_replace('@endwhile', '<?php endwhile; ?>', $source);

        return $source;
    }

    /**
     * Compile @foreach with $loop variable support.
     */
    private static function compileForeach(string $source): string
    {
        // @forelse($items as $item) ... @empty ... @endforelse
        $source = preg_replace_callback(
            '/@forelse\s*\(\s*([^)]+)\s+as\s+([^)]+)\s*\)(.*?)@empty(.*?)@endforelse/s',
            function ($m) {
                return '<?php $__forelseData = ' . trim($m[1]) . '; '
                    . 'if(!empty($__forelseData)): '
                    . '$__loopIndex = 0; $__loopCount = is_countable($__forelseData) ? count($__forelseData) : 0; '
                    . 'foreach($__forelseData as ' . trim($m[2]) . '): '
                    . '$loop = (object)[\'index\' => $__loopIndex, \'iteration\' => $__loopIndex + 1, \'first\' => $__loopIndex === 0, \'last\' => $__loopIndex === $__loopCount - 1, \'count\' => $__loopCount]; ?>'
                    . $m[3]
                    . '<?php $__loopIndex++; endforeach; else: ?>'
                    . $m[4]
                    . '<?php endif; ?>';
            },
            $source
        );
        $result = '';
        $len = strlen($source);
        $directive = '@foreach';
        $directiveLen = strlen($directive);

        for ($i = 0; $i < $len; $i++) {
            if (substr($source, $i, $directiveLen) === $directive) {
                $j = $i + $directiveLen;
                while ($j < $len && $source[$j] === ' ') $j++;

                if ($j < $len && $source[$j] === '(') {
                    $depth = 0;
                    $start = $j + 1;
                    $k = $j;

                    while ($k < $len) {
                        if ($source[$k] === '(') $depth++;
                        elseif ($source[$k] === ')') {
                            $depth--;
                            if ($depth === 0) {
                                $expression = substr($source, $start, $k - $start);
                                // Parse "collection as item"
                                $parts = preg_split('/\s+as\s+/i', $expression, 2);
                                $collection = trim($parts[0]);
                                $item = trim($parts[1] ?? '$item');

                                $result .= '<?php $__loopData = ' . $collection . '; '
                                    . '$__loopIndex = 0; '
                                    . '$__loopCount = is_countable($__loopData) ? count($__loopData) : 0; '
                                    . 'foreach($__loopData as ' . $item . '): '
                                    . '$loop = (object)[\'index\' => $__loopIndex, \'iteration\' => $__loopIndex + 1, '
                                    . '\'remaining\' => $__loopCount - $__loopIndex - 1, \'count\' => $__loopCount, '
                                    . '\'first\' => $__loopIndex === 0, \'last\' => $__loopIndex === $__loopCount - 1]; ?>';
                                $i = $k;
                                break;
                            }
                        }
                        $k++;
                    }
                } else {
                    $result .= $directive;
                }
            } else {
                $result .= $source[$i];
            }
        }

        return $result;
    }

    // ─── Directives ──────────────────────────────────────────────────

    /**
     * Compile shortcut directives: @csrf, @method, @dd, @dump, @json.
     */
    private static function compileDirectives(string $source): string
    {
        // @csrf — outputs hidden CSRF token input
        $source = str_replace(
            '@csrf',
            '<?php echo \App\Core\Blade::csrfField(); ?>',
            $source
        );

        // @method('PUT') — method spoofing
        $source = preg_replace(
            "/@method\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/",
            '<?php echo \'<input type="hidden" name="_method" value="$1">\'; ?>',
            $source
        );

        // @json($data) — output as JSON
        $source = preg_replace(
            '/@json\s*\((.+?)\)/',
            '<?php echo json_encode($1, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP); ?>',
            $source
        );

        // @class(['active' => $isActive]) — conditional CSS classes
        $source = preg_replace(
            '/@class\s*\((.+?)\)/',
            '<?php echo \App\Core\Blade::compileClass($1); ?>',
            $source
        );

        // @checked($condition) — output "checked" if true
        $source = preg_replace(
            '/@checked\s*\((.+?)\)/',
            '<?php if($1) echo \'checked\'; ?>',
            $source
        );

        // @selected($condition) — output "selected" if true
        $source = preg_replace(
            '/@selected\s*\((.+?)\)/',
            '<?php if($1) echo \'selected\'; ?>',
            $source
        );

        // @disabled($condition)
        $source = preg_replace(
            '/@disabled\s*\((.+?)\)/',
            '<?php if($1) echo \'disabled\'; ?>',
            $source
        );

        return $source;
    }

    /**
     * Compile @include('partial.name', ['key' => 'value']).
     */
    private static function compileIncludes(string $source): string
    {
        // @include('view', ['key' => 'val'])
        $source = preg_replace(
            "/@include\s*\(\s*['\"]([^'\"]+)['\"]\s*,\s*(\[[^\]]*\])\s*\)/",
            '<?php echo \App\Core\Blade::render("$1", $2); ?>',
            $source
        );

        // @include('view')
        $source = preg_replace(
            "/@include\s*\(\s*['\"]([^'\"]+)['\"]\s*\)/",
            '<?php echo \App\Core\Blade::render("$1"); ?>',
            $source
        );

        return $source;
    }

    /**
     * Compile @auth ... @endauth and @guest ... @endguest.
     */
    private static function compileAuth(string $source): string
    {
        $source = preg_replace(
            '/@auth/',
            '<?php if(\App\Core\Session::has("user_id")): ?>',
            $source
        );
        $source = str_replace('@endauth', '<?php endif; ?>', $source);

        $source = preg_replace(
            '/@guest/',
            '<?php if(!\App\Core\Session::has("user_id")): ?>',
            $source
        );
        $source = str_replace('@endguest', '<?php endif; ?>', $source);

        return $source;
    }

    // ─── Static Helper Methods (called from compiled templates) ──────

    /**
     * Set the parent layout for template inheritance.
     *
     * @param string $layout Dot-notation layout name
     * @internal Called from compiled templates
     */
    public static function setParent(string $layout): void
    {
        self::$parentLayout = $layout;
    }

    /**
     * Start capturing a named section.
     *
     * @param string $name
     * @internal
     */
    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    /**
     * End the current section and store its content.
     *
     * @internal
     */
    public static function endSection(): void
    {
        if (self::$currentSection !== null) {
            self::$sections[self::$currentSection] = ob_get_clean();
            self::$currentSection = null;
        }
    }

    /**
     * Set a section value directly (inline form).
     *
     * @param string $name
     * @param string $content
     * @internal
     */
    public static function setSection(string $name, string $content): void
    {
        self::$sections[$name] = $content;
    }

    /**
     * Yield a section's content (used in layouts).
     *
     * @param string $name
     * @param string $default Default content if section is not defined
     * @return string
     * @internal
     */
    public static function yieldSection(string $name, string $default = ''): string
    {
        return self::$sections[$name] ?? $default;
    }

    /**
     * Generate a CSRF hidden input field.
     *
     * @return string HTML input element with CSRF token
     * @internal Called from compiled templates
     */
    public static function csrfField(): string
    {
        $tokenName = $_ENV['CSRF_TOKEN_NAME'] ?? 'csrf_token';
        $token = htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="' . $tokenName . '" value="' . $token . '">';
    }

    /**
     * Compile conditional CSS class array to string.
     *
     * @param array<string, bool> $classes
     * @return string
     */
    public static function compileClass(array $classes): string
    {
        $active = [];
        foreach ($classes as $class => $condition) {
            if ($condition) {
                $active[] = $class;
            }
        }
        return htmlspecialchars(implode(' ', $active), ENT_QUOTES, 'UTF-8');
    }

    // ─── Internal Utilities ──────────────────────────────────────────

    /**
     * Resolve a dot-notation view name to a file path.
     *
     * @param string $view Dot-notation name (e.g., 'cms.index')
     * @return string Absolute file path
     */
    private static function resolveViewPath(string $view): string
    {
        $relative = str_replace('.', '/', $view);
        return self::$viewPath . '/' . $relative . '.blade.php';
    }

    /**
     * Reset all state (useful for testing).
     */
    public static function reset(): void
    {
        self::$sections = [];
        self::$parentLayout = null;
        self::$currentSection = null;
        self::$loopDepth = 0;
        self::$shared = [];
    }
}
