<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Support\HtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_rich_html_removes_scripts_events_and_unsafe_links(): void
    {
        $html = '<p onclick="alert(1)">Safe <strong>content</strong></p>'
            .'<script>alert(2)</script>'
            .'<a href="javascript:alert(3)" target="_blank">Bad link</a>'
            .'<a href="https://example.com" target="_blank">Good link</a>';

        $clean = HtmlSanitizer::clean($html);

        $this->assertStringContainsString('<strong>content</strong>', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('alert(2)', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_frontend_responses_include_browser_security_headers(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Content-Security-Policy', "object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'");
    }

    public function test_published_rich_content_is_sanitized_at_the_api_boundary(): void
    {
        Page::create([
            'slug' => 'security-test',
            'page_type' => 'standard',
            'status' => 'published',
            'translations' => ['en' => ['title' => 'Security', 'body' => '<p onmouseover="alert(1)">Body</p><script>alert(2)</script>']],
            'blocks' => [[
                'type' => 'content',
                'translations' => ['en' => ['body' => '<a href="javascript:alert(3)">Open</a><strong>Allowed</strong>']],
            ]],
        ]);

        $response = $this->getJson('/api/v1/pages/security-test')->assertOk()
            ->assertJsonPath('data.blocks.0.content.body', '<a>Open</a><strong>Allowed</strong>');
        $content = $response->getContent();

        $this->assertStringNotContainsString('onmouseover', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('javascript:', $content);
    }
}
