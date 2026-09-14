<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Models\StorefrontFeature;
use Webkul\BagistoApi\State\StorefrontFeatureProvider;

class StorefrontFeatureQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): StorefrontFeature
    {
        return StorefrontFeatureProvider::build();
    }
}
