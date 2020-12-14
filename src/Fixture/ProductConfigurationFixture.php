<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Synolia\SyliusAkeneoPlugin\Entity\ProductConfiguration;
use Synolia\SyliusAkeneoPlugin\Entity\ProductConfigurationAkeneoImageAttribute;
use Synolia\SyliusAkeneoPlugin\Entity\ProductConfigurationImageMapping;

class ProductConfigurationFixture extends AbstractFixture
{
    private ObjectManager $objectManager;

    private FactoryInterface $productConfigurationFactory;

    private FactoryInterface $productImageAttributeConfigurationFactory;

    private FactoryInterface $productImageMappingConfigurationFactory;

    public function __construct(
        ObjectManager $objectManager,
        FactoryInterface $productConfigurationFactory,
        FactoryInterface $productImageAttributeConfigurationFactory,
        FactoryInterface $productImageMappingConfigurationFactory
    ) {
        $this->objectManager = $objectManager;
        $this->productConfigurationFactory = $productConfigurationFactory;
        $this->productImageAttributeConfigurationFactory = $productImageAttributeConfigurationFactory;
        $this->productImageMappingConfigurationFactory = $productImageMappingConfigurationFactory;
    }

    public function load(array $options): void
    {
        /** @var ProductConfiguration $productConfiguration */
        $productConfiguration = $this->productConfigurationFactory->createNew();
        $productConfiguration->setAkeneoEnabledChannelsAttribute($options['akeneo_sylius_enabled_channels_attribute']);
        $productConfiguration->setAkeneoPriceAttribute($options['akeneo_price_attribute']);
        $productConfiguration->setRegenerateUrlRewrites($options['regenerate_url']);
        $productConfiguration->setImportMediaFiles($options['import_media_files']);
        $this->objectManager->persist($productConfiguration);

        foreach ($options['images_attributes'] as $imagesAttribute) {
            /** @var ProductConfigurationAkeneoImageAttribute $productImageAttribute */
            $productImageAttribute = $this->productImageAttributeConfigurationFactory->createNew();
            $productImageAttribute->setAkeneoAttributes($imagesAttribute);
            $productConfiguration->addAkeneoImageAttribute($productImageAttribute);
            $this->objectManager->persist($productImageAttribute);
        }

        foreach ($options['images_type_mapping'] as $imagesTypeMapping) {
            /** @var ProductConfigurationImageMapping $productImageMapping */
            $productImageMapping = $this->productImageMappingConfigurationFactory->createNew();
            $productImageMapping->setAkeneoAttribute($imagesTypeMapping['akeneo_attribute']);
            $productImageMapping->setSyliusAttribute($imagesTypeMapping['type']);
            $productConfiguration->addProductImagesMapping($productImageMapping);
        }

        $this->objectManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'akeneo_product_configuration';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->scalarNode('akeneo_sylius_enabled_channels_attribute')->defaultNull()->end()
                ->scalarNode('akeneo_price_attribute')->end()
                ->booleanNode('regenerate_url')->defaultFalse()->end()
                ->booleanNode('import_media_files')->defaultFalse()->end()
                ->arrayNode('images_attributes')
                    ->scalarPrototype()->defaultValue([])->end()
                ->end()
                ->arrayNode('images_type_mapping')
                    ->arrayPrototype()
                    ->children()
                        ->scalarNode('akeneo_attribute')->end()
                        ->scalarNode('type')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
