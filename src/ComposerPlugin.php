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
use Magento\Framework\Component\ComponentRegistrar;

final class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    const GLOB_LIST_PATH = '/app/etc/registration_globlist.php';

    private $projectRoot = null;

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

        $this->projectRoot = dirname($composer->getConfig()->get('vendor-dir'));

        $modules = $this->getNonComposerRegistrationList($io);

        foreach ($modules as $module) {
            $io->write(implode(',', $module));
        }
    }

    private function getNonComposerRegistrationList(IOInterface $io): array
    {
        $globPatterns = require $this->projectRoot . self::GLOB_LIST_PATH;
        $modules = [];

        foreach ($globPatterns as $globPattern) {
            $files = glob($this->projectRoot . DIRECTORY_SEPARATOR . $globPattern, GLOB_NOSORT);

            if ($files === false) {
                $io->writeError("glob(): error with '$this->projectRoot$globPattern'");
            }

            foreach ($files as $file) {
                $modules[] = $this->parseRegistrationFile($file, $io);
            }
        }

        return $modules;
    }

    private function parseRegistrationFile(string $filePath, $io): ?array
    {
        $content = file_get_contents($filePath);
        $pattern = '{::register\([ \r\n\t]*(?<type>[\'\\\w]+?(::)\w+)[ \r\n\t]*,[ \r\n\t]*["\'](?<name>[\/\w]+)["\']}';

        $io->write($pattern);
        $io->write($content);
        $io->write(preg_match($pattern, $content, $matches));

        if (preg_match($pattern, $content, $matches)) {

            $io->write($matches);

            include $filePath;

            $register = new ComponentRegistrar();

            return [
                'type' => $matches['type'],
                'componentName' => $matches['name'],
                'path' =>  $register->getPath($matches['type'], $matches['name'])
            ];
        }

        return null;
    }

    private function generateGlobalRegistrationFile(): void
    {
    }
}
