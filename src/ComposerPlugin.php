<?php
/**
 * Copyright © OpenGento, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Opengento\ComposerRegistrationPlugin;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

final class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'compileRegistration',
            ScriptEvents::POST_UPDATE_CMD => 'compileRegistration'
        ];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {

    }

    public function compileRegistration(Event $event): void
    {
        $io = $event->getIO();
        $composer = $event->getComposer();

        $io->write('<info>Dump components registration file:</info>');

        $packages = $composer->getRepositoryManager()->getLocalRepository()->getPackages();


        // ToDo: To Implement
    }
}
