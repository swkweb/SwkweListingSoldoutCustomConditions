<?php

use Shopware\SwkweListingSoldoutCustomConditions\Subscriber;

final class Shopware_Plugins_Frontend_SwkweListingSoldoutCustomConditions_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    private $invalidateCache = [
        'proxy',
    ];

    private $requiredPlugins = [
        'SwkweListingSoldoutPremium' => ['Frontend', '1.2.0'], // FIXME: Richtige Version
    ];

    private $dev = true;


    public function getInfo()
    {
        $info = json_decode(file_get_contents($this->Path() . 'plugin.json'), true);

        if ($info) {
            return array(
                'version' => $info['currentVersion'],
                'label' => $info['label']['de'],
                'description' => 'Benutzerdefinierte Bedingungen für SwkweListingSoldoutPremium',
                'autor' => $info['author'],
                'supplier' => $info['author'],
                'support' => $info['author'],
                'link' => $info['link'],
            );
        } else {
            throw new \Exception('The plugin has an invalid version file.');
        }
    }

    /**
     * Returns the plugin label which displayed in the plugin information and
     * in the plugin manager.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->getInfo()['label'];
    }

    /**
     * Returns the plugin version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->getInfo()['version'];
    }


    public function uninstall()
    {
        return ['success' => true, 'invalidateCache' => $this->invalidateCache];
    }

    public function update($oldVersion)
    {
        $this->checkPluginDependencies();

        return ['success' => true, 'invalidateCache' => $this->invalidateCache];
    }

    public function install()
    {
        $this->checkLicense();
        $this->checkPluginDependencies();

        if (!$this->assertMinimumVersion('5.1.0')) {
            throw new \RuntimeException('At least Shopware 5.1.0 is required');
        }

        $this->createConfiguration();
        $this->subscribeEvents();

        return true;
    }

    public function enable()
    {
        $this->checkLicense();
        $this->checkPluginDependencies();

        return ['success' => true, 'invalidateCache' => $this->invalidateCache];
    }

    /**
     * checkLicense()-method for SwkweListingSoldoutCustomConditions
     */
    private function checkLicense($throwException = true)
    {
        return true;
    }

    private function checkPluginDependencies()
    {
        if ($this->assertRequiredPluginsPresent(array_keys($this->requiredPlugins))) {
            $pluginManager = $this->get('plugins');

            foreach ($this->requiredPlugins as $plugin => $conf) {
                list($module, $version) = $conf;

                if (version_compare($pluginManager->$module()->$plugin()->getVersion(), $version, '<')) {
                    throw new \RuntimeException("This plugin requires $plugin >= $version");
                }
            }
        } else {
            throw new \RuntimeException('This plugin requires the following plugins to be installed and actived: ' . implode(', ', array_keys($this->requiredPlugins)));
        }
    }

    private function createConfiguration()
    {
        $form = $this->Form();

        $form->setElement(
            'text',
            'SwkweListingSoldoutCustomCondition',
            [
                'label' => 'Join Bedingung SQL',
                'value' => '',
                'required' => false,
                'description' => '', // TODO
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP,
            ]
        );

        $form->setElement(
            'text',
            'swkweListingSoldoutCustomAttributeCondition',
            [
                'label' => 'Attribut Join Bedingung SQL',
                'value' => '',
                'required' => false,
                'description' => '', // TODO
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP,
            ]
        );

        $form->setElement(
            'text',
            'swkweListingSoldoutCustomWhere',
            [
                'label' => 'Where Bedingung SQL',
                'value' => '',
                'required' => false,
                'description' => '', // TODO
                'scope' => Shopware\Models\Config\Element::SCOPE_SHOP,
            ]
        );
    }

    private function subscribeEvents()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_DispatchLoopStartup',
            'onStartDispatch'
        );
    }

    public function afterInit()
    {
        $this->Application()->Loader()->registerNamespace(
            'Shopware\SwkweListingSoldoutCustomConditions',
            $this->Path()
        );
    }

    /**
     * This callback function is triggered at the very beginning of the dispatch process and allows
     * us to register additional events on the fly. This way you won't ever need to reinstall you
     * plugin for new events - any event and hook can simply be registerend in the event subscribers
     */
    public function onStartDispatch(\Enlight_Event_EventArgs $args)
    {
        if (!$this->checkLicense(false)) {
            return;
        }

        $subscribers = [
            new Subscriber\Container(),
        ];

        foreach ($subscribers as $subscriber) {
            $this->Application()->Events()->addSubscriber($subscriber);
        }
    }
}
