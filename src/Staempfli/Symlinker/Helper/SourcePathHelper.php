<?php
/**
 * SourcePathHelper
 *
 * Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\Symlinker\Helper;


class SourcePathHelper
{
    const WILDCARD_RECURSIVE_FILES = '/**';
    const WILDCARD_DIR_CONTENT = '/*';

    /**
     * @var string
     */
    protected $path;

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return bool|string
     */
    public function getRecursiveDirPath()
    {
        if ($this->hasPathEndWildCard(self::WILDCARD_RECURSIVE_FILES)) {
            return $this->getDirPathWithoutEndWildcard(self::WILDCARD_RECURSIVE_FILES);
        }
        return false;
    }

    /**
     * @return bool|string
     */
    public function getContentDirPath()
    {
        if ($this->hasPathEndWildCard(self::WILDCARD_DIR_CONTENT)) {
            return $this->getDirPathWithoutEndWildcard(self::WILDCARD_DIR_CONTENT);
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isWildCardPath()
    {
        if ($this->hasPathEndWildCard(self::WILDCARD_DIR_CONTENT)) {
            return true;
        }
        if ($this->hasPathEndWildCard(self::WILDCARD_RECURSIVE_FILES)) {
            return true;
        }
        return false;
    }

    /**
     * @param string $wildcard
     * @return bool
     */
    protected function hasPathEndWildCard($wildcard)
    {
        if (substr($this->path, -strlen($wildcard)) == $wildcard) {
            return true;
        }
        return false;
    }

    /**
     * @param string $wildcard
     * @return string
     * @throws \Exception
     */
    protected function getDirPathWithoutEndWildcard($wildcard)
    {
        if (!$this->hasPathEndWildCard($this->path, $wildcard)) {
            throw new \Exception(sprintf('Path %s does not ends with wildcard: %s', $this->path, $wildcard));
        }
        $dirPath = substr($this->path, 0, -strlen($wildcard));
        if (!is_dir($dirPath)) {
            throw new \Exception(sprintf('Source directory does not exists: %s', $dirPath));
        }
        return $dirPath;
    }

}