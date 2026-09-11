<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\StorefrontFeature;

/**
 * The optional storefront features the current channel has switched on.
 */
class StorefrontFeatureProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return [self::build()];
    }

    public static function build(): StorefrontFeature
    {
        $channel = core()->getCurrentChannel();

        $features = new StorefrontFeature;

        $features->id = $channel->code;
        $features->channel = $channel->code;
        $features->gdpr = (bool) core()->getConfigData('general.gdpr.settings.enabled');
        $features->eu_withdrawal = (bool) core()->getConfigData('sales.eu_withdrawal.general.enabled', $channel->code);

        return $features;
    }
}
