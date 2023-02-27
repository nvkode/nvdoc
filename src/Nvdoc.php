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
use Nvkode\Nvdoc\Helpers\FileHelper;
use ReflectionClass;

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
     * @var FileHelper $fileHelper Helper for getting namespaces and files
     */
    private FileHelper $fileHelper;


    /**
     * Constructor
     *
     * @param string $rootDirectory Project root directory
     */
    public function __construct(
        string $rootDirectory
    ) {
        $this->fileHelper = new FileHelper($rootDirectory);
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

        foreach ($this->fileHelper->findFiles($dir) as $file) {
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


}//end class
