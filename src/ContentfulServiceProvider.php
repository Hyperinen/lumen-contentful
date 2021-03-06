<?php

namespace Nord\Lumen\Contentful;

use Contentful\Delivery\Client;
use Contentful\Delivery\Client\ClientInterface;
use Contentful\Delivery\ClientOptions;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

/**
 * Class ContentfulServiceProvider
 * @package Nord\Lumen\Contentful
 */
class ContentfulServiceProvider extends ServiceProvider
{

    const CONFIG_KEY = 'contentful';

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->configure(self::CONFIG_KEY);

        $this->registerBindings($this->app, $this->app['config']);
    }

    /**
     * @param Container        $container
     * @param ConfigRepository $config
     */
    protected function registerBindings(Container $container, ConfigRepository $config)
    {
        $container->singleton(ContentfulServiceContract::class, function () use ($config) {
            $apiKey        = $config->get('contentful.api_key');
            $spaceId       = $config->get('contentful.space_id');
            $environmentId = $config->get('contentful.environment_id');

            $clientOptions = $this->createClientOptions($config);
            $client        = $this->createClient($apiKey, $spaceId, $environmentId, $clientOptions);

            return new ContentfulService($client);
        });

        $container->alias(ContentfulServiceContract::class, ContentfulService::class);
    }

    /**
     * @param string        $apiKey
     * @param string        $spaceId
     * @param string        $environmentId
     * @param ClientOptions $clientOptions
     *
     * @return ClientInterface
     */
    protected function createClient(
        string $apiKey,
        string $spaceId,
        string $environmentId,
        ClientOptions $clientOptions
    ): ClientInterface {
        return new Client($apiKey, $spaceId, $environmentId, $clientOptions);
    }

    /**
     * @param ConfigRepository $config
     *
     * @return ClientOptions
     */
    protected function createClientOptions(ConfigRepository $config): ClientOptions
    {
        $preview               = $config->get('contentful.preview');
        $defaultLocale         = $config->get('contentful.default_locale');
        $lowMemoryResourcePool = $config->get('contentful.low_memory_resource_pool');

        $clientOptions = new ClientOptions();

        if ($preview === true) {
            $clientOptions->usingPreviewApi();
        }

        if ($defaultLocale !== null) {
            $clientOptions->withDefaultLocale($defaultLocale);
        }

        if ($lowMemoryResourcePool === true) {
            $clientOptions->withLowMemoryResourcePool();
        }

        return $clientOptions;
    }
}
