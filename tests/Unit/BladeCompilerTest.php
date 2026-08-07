<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Blade;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the Blade template compiler.
 *
 * Validates that Blade syntax compiles to valid PHP
 * following the php-pro skill patterns.
 */
class BladeCompilerTest extends TestCase
{
    public function test_escaped_echo_compiles(): void
    {
        $result = Blade::compile('{{ $name }}');
        $this->assertStringContainsString('htmlspecialchars', $result);
        $this->assertStringContainsString('$name', $result);
    }

    public function test_raw_echo_compiles(): void
    {
        $result = Blade::compile('{!! $html !!}');
        $this->assertStringContainsString('echo $html', $result);
        $this->assertStringNotContainsString('htmlspecialchars', $result);
    }

    public function test_if_compiles_with_nested_parens(): void
    {
        $result = Blade::compile('@if(count($items) > 0)');
        $this->assertStringContainsString('if(count($items) > 0)', $result);
    }

    public function test_elseif_compiles(): void
    {
        $result = Blade::compile('@elseif($x === true)');
        $this->assertStringContainsString('elseif($x === true)', $result);
    }

    public function test_csrf_compiles_to_helper_call(): void
    {
        $result = Blade::compile('@csrf');
        $this->assertStringContainsString('Blade::csrfField()', $result);
    }

    public function test_method_directive_compiles(): void
    {
        $result = Blade::compile("@method('PUT')");
        $this->assertStringContainsString('_method', $result);
        $this->assertStringContainsString('PUT', $result);
    }

    public function test_extends_compiles(): void
    {
        $result = Blade::compile("@extends('layouts.app')");
        $this->assertStringContainsString('setParent', $result);
        $this->assertStringContainsString('layouts.app', $result);
    }

    public function test_section_compiles(): void
    {
        $result = Blade::compile("@section('content')Hello@endsection");
        $this->assertStringContainsString('startSection', $result);
        $this->assertStringContainsString('endSection', $result);
        $this->assertStringContainsString('Hello', $result);
    }

    public function test_yield_compiles(): void
    {
        $result = Blade::compile("@yield('content')");
        $this->assertStringContainsString('yieldSection', $result);
    }

    public function test_auth_directives_compile(): void
    {
        $result = Blade::compile('@auth LOGGED @endauth');
        $this->assertStringContainsString('Session::has', $result);

        $result2 = Blade::compile('@guest NOT LOGGED @endguest');
        $this->assertStringContainsString('Session::has', $result2);
    }

    public function test_checked_directive(): void
    {
        $result = Blade::compile('@checked($isActive)');
        $this->assertStringContainsString('checked', $result);
    }

    public function test_json_directive(): void
    {
        $result = Blade::compile('@json($data)');
        $this->assertStringContainsString('json_encode', $result);
    }

    public function test_include_directive(): void
    {
        $result = Blade::compile("@include('partials.sidebar')");
        $this->assertStringContainsString('Blade::render', $result);
    }

    public function test_unless_compiles(): void
    {
        $result = Blade::compile('@unless($hidden)');
        $this->assertStringContainsString('if(!($hidden))', $result);
    }

    public function test_isset_compiles(): void
    {
        $result = Blade::compile('@isset($user)');
        $this->assertStringContainsString('isset($user)', $result);
    }

    public function test_empty_compiles(): void
    {
        $result = Blade::compile('@empty($items)');
        $this->assertStringContainsString('empty($items)', $result);
    }
}
