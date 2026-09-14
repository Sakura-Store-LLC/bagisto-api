<?php

namespace Webkul\BagistoApi\Models;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\Query;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use Webkul\BagistoApi\Resolver\StorefrontFeatureQueryResolver;
use Webkul\BagistoApi\State\StorefrontFeatureProvider;

/**
 * The optional storefront features this channel has switched on.
 */
#[ApiResource(
    routePrefix: '/api/shop',
    shortName: 'StorefrontFeature',
    paginationEnabled: false,
    normalizationContext: ['skip_null_values' => false],
    operations: [
        new GetCollection(
            uriTemplate: '/features',
            provider: StorefrontFeatureProvider::class,
            paginationEnabled: false,
            openapi: new Operation(
                tags: ['Storefront'],
                summary: 'Which optional features this channel has switched on',
                description: 'Tells a storefront which optional features the store has enabled, so it can decide what to render before calling anything that might refuse. Both flags are read per channel from the admin configuration. Call this once on boot rather than probing an endpoint and reading its error. Returns: `gdpr` — whether customers may raise GDPR data requests (`/api/shop/gdpr-requests`); `euWithdrawal` — whether the EU right-of-withdrawal form is offered on orders (`/api/shop/eu-withdrawals`). Returns are not listed: Bagisto has no master switch for them, so whether a customer can return anything is answered per order by `/api/shop/returnable-orders`. Public endpoint.',
                responses: [
                    '200' => new Response(
                        description: 'The feature flags of the current channel.',
                        content: new \ArrayObject([
                            'application/json' => [
                                'example' => [
                                    [
                                        'id' => 'default',
                                        'channel' => 'default',
                                        'gdpr' => true,
                                        'euWithdrawal' => false,
                                    ],
                                ],
                            ],
                        ]),
                    ),
                ],
            ),
        ),
    ],
    graphQlOperations: [
        new Query(
            resolver: StorefrontFeatureQueryResolver::class,
            args: [],
        ),
    ],
)]
class StorefrontFeature
{
    #[ApiProperty(identifier: true)]
    public ?string $id = null;

    public ?string $channel = null;

    public ?bool $gdpr = null;

    public ?bool $eu_withdrawal = null;
}
