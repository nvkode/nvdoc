<?php

/**
 * Base library class
 * PHP Version >= 8.1
 *
 * @category Nvdoc
 * @package  Nvdoc
 * @author   Mykyta Melnyk <liswelus@gmail.com>
 * @license  MIT <https://github.com/nvkode/nvdoc/blob/development/LICENSE>
 * @link     https://github.com/nvkode/nvdoc
 */

declare(strict_types=1);

namespace Nvkode\Nvdoc;

use Composer\InstalledVersions;
use Exception;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

/**
 * Nvdoc Class
 *
 * @category Base
 * @package  Nvdoc
 * @author   Mykyta Melnyk <liswelus@gmail.com>
 * @license  MIT <https://github.com/nvkode/nvdoc/blob/development/LICENSE>
 * @link     https://github.com/nvkode/nvdoc
 */
class Nvdoc
{


    /**
     * Project root directory
     *
     * @var string $_rootDirectory
     */
    private string $_rootDirectory;


    /**
     * Symfony Finder for searching files' namespaces
     *
     * @var Finder $_finder
     */
    private Finder $_finder;


    /**
     * Constructor
     *
     * @param string $rootDirectory Project root directory
     */
    public function __construct(
        string $rootDirectory
    ) {
        $this->_rootDirectory = $rootDirectory;
        $this->_finder        = new Finder();
    }


    /**
     * Get version from composer.json
     *
     * @return string composer.json version
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
     * Find all files and parse with ReflectionClass.
     * These information can be used for generating
     * docs templates in the future.
     *
     * @param string $dir Destination directory path
     *
     * @return array<string, mixed> Return all necessary elements from ReflectionClass
     */
    public function getFilesInformation(string $dir): array
    {
        $information = [];

        foreach ($this->_findFiles($dir) as $file) {
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
     * Find all files in directory
     *
     * @param string $dir Destination directory path
     *
     * @return array
     */
    private function _findFiles(string $dir): array
    {
        $files = [];

        $this->_finder->files()->in($dir);

        if ($this->_finder->hasResults() === true) {
            $namespaces = $this->_getDefinedNamespaces();

            foreach ($this->_finder as $file) {
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
     * Get all defined PSR-4 namespaces in composer.json
     *
     * @return array<string, string>
     */
    private function _getDefinedNamespaces(): array
    {
        try {
            $composerConfig = file_get_contents(sprintf('%s/composer.json', $this->_rootDirectory));

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
