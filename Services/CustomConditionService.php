<?php

namespace Shopware\SwkweListingSoldoutCustomConditions\Services;

use Shopware\SwkweListingSoldout\Services\ConditionServiceInterface;
use Shopware_Components_Config;

class CustomConditionService implements ConditionServiceInterface
{
    /**
     * @var Shopware\SwkweListingSoldout\Services\ConditionServiceInterface
     */
    private $service;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function __construct(
        ConditionServiceInterface $service,
        Shopware_Components_Config $config
    ) {
        $this->service = $service;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionSql()
    {
        $condition = trim($this->config->get('SwkweListingSoldoutCustomCondition'));

        return $condition ?: $this->service->getConditionSql();
    }
}
