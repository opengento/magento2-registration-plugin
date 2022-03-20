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
use Generator;
use Laminas\Code\DeclareStatement;
use Laminas\Code\Generator\FileGenerator;
use function fclose;
use function fopen;
use function fwrite;
use function getcwd;
use function glob;
use function sprintf;
use const DIRECTORY_SEPARATOR;
use const GLOB_NOSORT;

final class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        // Silence is golden...
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // Silence is golden...
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // Silence is golden...
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'compileRegistration',
            ScriptEvents::POST_UPDATE_CMD => 'compileRegistration'
        ];
    }

    public function compileRegistration(Event $event): void
    {
        $io = $event->getIO();

        $io->write('<info>Dump components registration file...</info>');

        $basePath = getcwd();
        $filePath = $basePath . '/app/etc/registration.php';
        $registrar = fopen($filePath, 'w+b');

        $fileGenerator = new FileGenerator();
        $fileGenerator->setDeclares([DeclareStatement::strictTypes(1)]);
        fwrite($registrar, $fileGenerator->generate());

        foreach ($this->globRegistrations($basePath) as $registration) {
            fwrite($registrar, sprintf("include '%s';\n", $basePath . DIRECTORY_SEPARATOR . $registration));
        }

        fclose($registrar);

        $io->write('<info>Dumped at <comment>`' . $filePath . '`</comment>!</info>');
    }

    private function globRegistrations(string $basePath): Generator
    {
        $globList = include $basePath . '/app/etc/registration_globlist.php';

        foreach ($globList as $glob) {
            yield from glob($glob, GLOB_NOSORT);
        }
    }
}
