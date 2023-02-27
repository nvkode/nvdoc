<?php

/**
 * Helper for working with files
 * PHP Version >= 8.1
 *
 * @category Nvdoc
 * @package  Nvdoc
 * @author   Mykyta Melnyk <liswelus@gmail.com>
 * @license  MIT <https://github.com/nvkode/nvdoc/blob/development/LICENSE>
 * @link     https://github.com/nvkode/nvdoc
 */

declare(strict_types=1);

namespace Nvkode\Nvdoc\Helpers;

use Exception;
use Symfony\Component\Finder\Finder;

/**
 * FileHelper Class
 *
 * @category Helper
 * @package  Nvdoc
 * @author   Mykyta Melnyk <liswelus@gmail.com>
 * @license  MIT <https://github.com/nvkode/nvdoc/blob/development/LICENSE>
 * @link     https://github.com/nvkode/nvdoc
 */
class FileHelper
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
     * Find all files in directory
     *
     * @param string $dir Destination directory path
     *
     * @return array
     */
    public function findFiles(string $dir): array
    {
        $files = [];

        $this->_finder->files()->in($dir);

        if ($this->_finder->hasResults() === true) {
            $namespaces = $this->getDefinedNamespaces();

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
    public function getDefinedNamespaces(): array
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
