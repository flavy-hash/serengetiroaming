<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/** The floating WeChat button (bottom left) shows the WeChat ID and QR code; it needs an ID to appear. */
class WechatButtonTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config(['site.wechat_id' => null]);
    }

    public function test_there_is_no_button_until_a_wechat_id_is_set(): void
    {
        $this->get('/')->assertOk()->assertDontSee('data-wechat-fab', false);
    }

    public function test_the_button_shows_the_id_from_the_admin_on_every_page(): void
    {
        Setting::put('wechat_id', 'serengetiroaming');

        foreach (['/', '/about', '/contact', '/safaris'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('data-wechat-fab', false)
                ->assertSee('Chat with us on WeChat')
                ->assertSee('serengetiroaming')
                ->assertSee('data-wechat-copy', false)
                ->assertDontSee('wechat-fab__qr', false);
        }
    }

    public function test_the_config_id_is_the_fallback_and_the_admin_value_wins(): void
    {
        config(['site.wechat_id' => 'from_env']);
        $this->get('/')->assertSee('from_env');

        Setting::put('wechat_id', 'from_admin');
        $this->get('/')->assertSee('from_admin')->assertDontSee('from_env');
    }

    public function test_the_qr_code_is_shown_when_uploaded(): void
    {
        Setting::put('wechat_id', 'serengetiroaming');
        Setting::put('wechat_qr', 'site/wechat-qr.png');

        $this->get('/')->assertSee('wechat-fab__qr', false)->assertSee('/storage/site/wechat-qr.png', false);
    }

    public function test_admin_saves_the_wechat_id_and_qr_in_site_settings(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(SiteSettings::class)
            ->fillForm([
                'wechat_id' => 'serengeti_wechat',
                'wechat_qr' => [UploadedFile::fake()->image('qr.png', 400, 400)],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $wechat = Setting::wechat();
        $this->assertSame('serengeti_wechat', $wechat['id']);
        $this->assertNotNull($wechat['qr']);

        Livewire::test(SiteSettings::class)->fillForm(['wechat_id' => ''])->call('save')->assertHasNoFormErrors();
        $this->assertNull(Setting::wechat()['id']);
    }
}
