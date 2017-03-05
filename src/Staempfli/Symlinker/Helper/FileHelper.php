<?php
/**
 * FileHelper
 *
 * Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */
namespace Staempfli\Symlinker\Helper;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FileHelper
{
    /**
     * @param string $path
     * @return bool
     */
    public function isAbsolutePath($path)
    {
        return (strpos($path, '/') === 0);
    }

    /**
     * @param string $path
     * @return bool|mixed
     * @throws \Exception
     */
    public function removeExitingPath($path)
    {
        if (is_file($path) || is_link($path)) {
            return $this->removeFile($path);
        } elseif (is_dir($path)) {
            return $this->removeDirRecursively($path);
        }
        throw new \Exception(sprintf('Non existing path cannot be removed: %s', $path));
    }


    /**
     * @param string $dir
     * @return mixed
     */
    public function removeDirRecursively($dir)
    {
        $this->validateDirToRemoveRecursive($dir);
        $directoryIterator = $this->getDirectoryIterator($dir);
        foreach ($directoryIterator as $fileInfo) {
            if ($fileInfo->isFile() || $fileInfo->isLink()) {
                $this->removeFile($fileInfo->getPathName());
            } elseif ($fileInfo->isDir()) {
                $this->removeDirRecursively($fileInfo->getPathName());
            }
        }
        return $this->removeDir($dir);
    }

    protected function validateDirToRemoveRecursive($dir)
    {
        if (is_link($dir)) {
            throw new \Exception(sprintf('Symlink directory cannot be removed recursively: %s', $dir));
        }
    }

    /**
     * @param string $dir
     * @return mixed
     * @throws \Exception
     */
    public function removeDir($dir)
    {
        $removeDirResult = rmdir($dir);
        if (!$removeDirResult) {
            throw new \Exception(sprintf('Directory could not be removed: %s', $dir));
        }
        return $removeDirResult;
    }

    /**
     * @param string $file
     * @return bool
     * @throws \Exception
     */
    public function removeFile($file)
    {
        $removeFileResult = unlink($file);
        if (!$removeFileResult) {
            throw new \Exception(sprintf('File could not be removed: %s', $file));
        }
        return $removeFileResult;
    }

    /**
     * @param string $dir
     * @return RecursiveDirectoryIterator
     */
    public function getDirectoryIterator($dir)
    {
        $directoryIterator = new RecursiveDirectoryIterator($dir);
        $directoryIterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
        return $directoryIterator;
    }

    /**
     * @param string $path
     * @return string
     * @throws \Exception
     */
    public function getExistingRealPath($path)
    {
        $realPath = realpath($path);
        if ($realPath) {
            return $realPath;
        }
        throw new \Exception(sprintf('Real path does not exist %s', $path));
    }
}