<?php

declare(strict_types=1);

namespace Nvkode\Nvdoc;

use Composer\InstalledVersions;

class Nvdoc
{


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


}//end class
