<?php

declare(strict_types=1);

namespace Nvkode\Nvdoc;

use Composer\InstalledVersions;
use Exception;
use Symfony\Component\Finder\Finder;

class Nvdoc
{


    /**
     * @var string $rootDirectory
     */
    private string $rootDirectory;


    /**
     * @var Finder $finder
     */
    private Finder $finder;


    /**
     * @param string $rootDirectory
     */
    public function __construct(
        string $rootDirectory
    ) {
        $this->rootDirectory = $rootDirectory;
        $this->finder        = new Finder();
    }


    /**
     * @return string
     */
    public function getVersion(): string
    {
        $rootPackage = InstalledVersions::getRootPackage();

        if (array_key_exists('version', $rootPackage) === true) {
            return $rootPackage['version'];
        }

        return '0.0.1';

    }//end getVersion()

    /**
     * @param string $dir
     *
     * @return array
     */
    public function findFiles(string $dir): array
    {
        $files = [];

        $this->finder->files()->in($dir);

        if ($this->finder->hasResults() === true) {
            $namespaces = $this->getDefinedNamespaces();

            foreach ($this->finder as $file) {
                foreach ($namespaces as $namespace => $path) {
                    $className = sprintf(
                        "%s\\%s",
                        rtrim($namespace, '\\'),
                        str_replace('/', '\\', str_replace('.php', '', $file->getRelativePathname()))
                    );

                    if (class_exists($className) === true) {
                        $files[] = $className;
                    }
                }
            }
        }

        return $files;

    }//end findFiles()


    /**
     * @return array<string, string>
     */
    private function getDefinedNamespaces(): array
    {
        try {
            $composerConfig = file_get_contents(sprintf('%s/composer.json', $this->rootDirectory));

            if ($composerConfig !== false) {
                $composerConfig = json_decode($composerConfig);

                return (array) $composerConfig->autoload->{'psr-4'};
            }
        } catch (Exception) {
            // Do nothing on error.
        }

        return [];

    }//end getDefinedNamespaces()


}//end class
