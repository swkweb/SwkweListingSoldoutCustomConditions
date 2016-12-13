<?php

namespace Shopware\SwkweListingSoldoutCustomConditions\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\SwkweListingSoldoutCustomConditions\Services;

class Container implements SubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Bootstrap_AfterInitResource_swkwe_listing_soldout.condition_service' => 'decorateConditionService',
        ];
    }

    public function decorateConditionService()
    {
        $container = Shopware()->Container();

        $container->set(
            'swkwe_listing_soldout.condition_service',
            new Services\CustomConditionService(
                $container->get('swkwe_listing_soldout.condition_service'),
                $container->get('config')
            )
        );
    }
}
