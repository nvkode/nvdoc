<?php

declare(strict_types=1);

namespace Nvkode\Nvdoc;

use Composer\InstalledVersions;
use Exception;
use ReflectionClass;
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
     * @return array<string, ReflectionClass>
     */
    public function getFilesInformation(string $dir): array
    {
        $information = [];

        foreach ($this->findFiles($dir) as $file) {
            try {
                $class = new ReflectionClass($file);

                $information[$class->getName()] = [
                    'methods'     => $class->getMethods(),
                    'attributes'  => $class->getAttributes(),
                    'constants'   => $class->getConstants(),
                    'doc_comment' => $class->getDocComment(),
                    'interfaces'  => $class->getInterfaceNames(),
                    'namespace'   => $class->getNamespaceName(),
                    'properties'  => $class->getProperties(),
                    'traits'      => $class->getTraits(),
                ];
            } catch (Exception) {
                // Do nothing.
            }
        }

        return $information;

    }//end getFilesInformation()


    /**
     * @param string $dir
     *
     * @return array
     */
    private function findFiles(string $dir): array
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
