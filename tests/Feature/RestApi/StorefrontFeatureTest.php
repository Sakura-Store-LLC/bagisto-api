<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class StorefrontFeatureTest extends RestApiTestCase
{
    private string $url = '/api/shop/features';

    private function setFlag(string $code, ?string $value, bool $localeScoped = true): void
    {
        DB::table('core_config')->where('code', $code)->delete();

        if ($value !== null) {
            DB::table('core_config')->insert([
                'code' => $code,
                'value' => $value,
                'channel_code' => core()->getRequestedChannelCode(),
                'locale_code' => $localeScoped ? core()->getRequestedLocaleCode() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->forgetCoreConfigCache();
    }

    public function test_returns_the_flags_of_the_current_channel(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();

        $body = $response->json();

        expect($body)->toBeArray();
        expect(count($body))->toBe(1);
        expect($body[0])->toHaveKeys(['id', 'channel', 'gdpr', 'euWithdrawal']);
        expect($body[0]['channel'])->toBe(core()->getCurrentChannel()->code);
        expect($body[0]['id'])->toBe($body[0]['channel']);
    }

    public function test_reports_gdpr_as_enabled_when_the_store_enables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('general.gdpr.settings.enabled', '1');

        $response = $this->publicGet($this->url);

        $response->assertOk();
        expect($response->json('0.gdpr'))->toBeTrue();
    }

    public function test_reports_gdpr_as_disabled_when_the_store_disables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('general.gdpr.settings.enabled', null);

        $response = $this->publicGet($this->url);

        $response->assertOk();
        expect($response->json('0.gdpr'))->toBeFalse();
    }

    public function test_reports_eu_withdrawal_as_enabled_when_the_store_enables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('sales.eu_withdrawal.general.enabled', '1', false);

        $response = $this->publicGet($this->url);

        $response->assertOk();
        expect($response->json('0.euWithdrawal'))->toBeTrue();
    }

    public function test_reports_eu_withdrawal_as_disabled_when_the_store_disables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('sales.eu_withdrawal.general.enabled', null, false);

        $response = $this->publicGet($this->url);

        $response->assertOk();
        expect($response->json('0.euWithdrawal'))->toBeFalse();
    }

    public function test_is_public_and_needs_no_customer_token(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->url);

        $response->assertOk();
    }
}
