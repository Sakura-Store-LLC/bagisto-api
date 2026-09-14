<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class StorefrontFeatureTest extends GraphQLTestCase
{
    private function query(): string
    {
        return <<<'GQL'
            query {
              storefrontFeature {
                channel
                gdpr
                euWithdrawal
              }
            }
        GQL;
    }

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

    public function test_query_returns_the_flags_of_the_current_channel(): void
    {
        $this->seedRequiredData();

        $response = $this->graphQL($this->query());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.storefrontFeature.channel'))->toBe(core()->getCurrentChannel()->code);
        expect($response->json('data.storefrontFeature'))->toHaveKeys(['gdpr', 'euWithdrawal']);
    }

    public function test_query_reports_gdpr_as_enabled_when_the_store_enables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('general.gdpr.settings.enabled', '1');

        $response = $this->graphQL($this->query());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.storefrontFeature.gdpr'))->toBeTrue();
    }

    public function test_query_reports_gdpr_as_disabled_when_the_store_disables_it(): void
    {
        $this->seedRequiredData();
        $this->setFlag('general.gdpr.settings.enabled', null);

        $response = $this->graphQL($this->query());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.storefrontFeature.gdpr'))->toBeFalse();
    }

    public function test_query_reports_eu_withdrawal_from_the_store_setting(): void
    {
        $this->seedRequiredData();
        $this->setFlag('sales.eu_withdrawal.general.enabled', '1', false);

        $response = $this->graphQL($this->query());

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect($response->json('data.storefrontFeature.euWithdrawal'))->toBeTrue();
    }
}
