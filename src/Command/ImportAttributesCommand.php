<?php

declare(strict_types=1);

namespace Synolia\SyliusAkeneoPlugin\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Synolia\SyliusAkeneoPlugin\Client\ClientFactory;
use Synolia\SyliusAkeneoPlugin\Factory\AttributePipelineFactory;
use Synolia\SyliusAkeneoPlugin\Factory\OptionPipelineFactory;
use Synolia\SyliusAkeneoPlugin\Payload\Attribute\AttributePayload;

final class ImportAttributesCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'akeneo:import:attributes';

    /** @var \Synolia\SyliusAkeneoPlugin\Factory\AttributePipelineFactory */
    private $attributePipelineFactory;

    /** @var \Synolia\SyliusAkeneoPlugin\Factory\OptionPipelineFactory */
    private $optionPipelineFactory;

    /** @var \Synolia\SyliusAkeneoPlugin\Client\ClientFactory */
    private $clientFactory;

    public function __construct(
        AttributePipelineFactory $attributePipelineFactory,
        OptionPipelineFactory $optionPipelineFactory,
        ClientFactory $clientFactory,
        string $name = null
    ) {
        parent::__construct($name);
        $this->attributePipelineFactory = $attributePipelineFactory;
        $this->optionPipelineFactory = $optionPipelineFactory;
        $this->clientFactory = $clientFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        /** @var \League\Pipeline\Pipeline $attributePipeline */
        $attributePipeline = $this->attributePipelineFactory->create();

        /** @var \Synolia\SyliusAkeneoPlugin\Payload\Attribute\AttributePayload $attributePayload */
        $attributePayload = new AttributePayload($this->clientFactory->createFromApiCredentials());
        $payload = $attributePipeline->process($attributePayload);

        /** @var \League\Pipeline\Pipeline $optionPipeline */
        $optionPipeline = $this->optionPipelineFactory->create();
        $optionPipeline->process($payload);

        return 0;
    }
}