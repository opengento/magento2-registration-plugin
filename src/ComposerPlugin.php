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
use Laminas\Code\Generator\BodyGenerator;
use Laminas\Code\Generator\FileGenerator;
use Laminas\Code\Generator\GeneratorInterface;
use Laminas\Code\Generator\ValueGenerator;
use Magento\Framework\Component\ComponentRegistrar;
use ReflectionClass;
use function fclose;
use function fopen;
use function fwrite;
use function getcwd;
use function glob;
use function rename;
use function sprintf;
use const DIRECTORY_SEPARATOR;
use const GLOB_NOSORT;
use const PHP_EOL;

final class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    private const REGISTRATION_GLOB_LIST = 'app/etc/registration_globlist.php';
    private const NON_COMPOSER_COMPONENT_REGISTRATION = 'app/etc/NonComposerComponentRegistration.php';
    private const BACKUP_NON_COMPOSER_COMPONENT_REGISTRATION = 'app/etc/NonComposerComponentRegistration.php.backup';

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->renameRegistrationFile(
            self::NON_COMPOSER_COMPONENT_REGISTRATION,
            self::BACKUP_NON_COMPOSER_COMPONENT_REGISTRATION
        );
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        $this->renameRegistrationFile(
            self::BACKUP_NON_COMPOSER_COMPONENT_REGISTRATION,
            self::NON_COMPOSER_COMPONENT_REGISTRATION
        );
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $this->deactivate($composer, $io);
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
        $fileName = self::NON_COMPOSER_COMPONENT_REGISTRATION;

        $this->dumpRegistration($basePath, $fileName);

        $io->write('<info>Dumped at <comment>`' . $basePath . DIRECTORY_SEPARATOR . $fileName . '`</comment>!</info>');
    }

    private function renameRegistrationFile(string $oldFileName, string $newFileName): void
    {
        $basePath = getcwd();
        rename($basePath . DIRECTORY_SEPARATOR . $oldFileName, $basePath . DIRECTORY_SEPARATOR . $newFileName);
    }

    private function dumpRegistration(string $basePath, string $fileName): void
    {
        $filePath = $basePath . DIRECTORY_SEPARATOR . $fileName;
        $registrar = fopen($filePath, 'w+b');

        $fileGenerator = new FileGenerator();
        $fileGenerator->setDeclares([DeclareStatement::strictTypes(1)]);
        $fileGenerator->setUse(ComponentRegistrar::class);
        fwrite($registrar, $fileGenerator->generate());

        foreach ($this->resolveComponents($basePath) as $type => $components) {
            foreach ($components as $name => $path) {
                fwrite($registrar, $this->registrarGenerator($type, $name, $path)->generate() . PHP_EOL);
            }
        }
        fclose($registrar);
    }

    private function registrarGenerator(string $type, string $name, string $path): GeneratorInterface
    {
        return (new BodyGenerator())->setContent(sprintf(
            'ComponentRegistrar::register(%s, %s, %s);',
            (new ValueGenerator($type))->generate(),
            (new ValueGenerator($name))->generate(),
            (new ValueGenerator($path))->generate()
        ));
    }

    private function resolveComponents(string $basePath): array
    {
        foreach ($this->globRegistrations($basePath) as $registration) {
            include $registration;
        }

        $reflection = new ReflectionClass(ComponentRegistrar::class);
        $pathsProperty = $reflection->getProperty('paths');
        $pathsProperty->setAccessible(true);

        return $pathsProperty->getValue();
    }

    private function globRegistrations(string $basePath): Generator
    {
        $globList = include $basePath . DIRECTORY_SEPARATOR . self::REGISTRATION_GLOB_LIST;

        foreach ($globList as $glob) {
            yield from glob($glob, GLOB_NOSORT);
        }
    }
}
