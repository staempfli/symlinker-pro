<?php
/**
 * RelativeTargetHelper
 *
 * Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\Symlinker\Helper;

class RelativeTargetHelper
{
    /**
     * @var FileHelper
     */
    protected $fileHelper;
    /**
     * @var string
     */
    protected $target;
    /**
     * @var string
     */
    protected $link;

    public function __construct()
    {
        $this->fileHelper = new FileHelper();
    }

    /**
     * @param string $target
     * @param string $link
     * @return string
     */
    public function getRelativeTarget($target, $link)
    {
        $this->initPaths($target, $link);
        return $this->getRelativeTargetToLink();
    }

    /**
     * @param string $target
     * @param string $link
     */
    protected function initPaths($target, $link)
    {
        if ($this->fileHelper->isAbsolutePath($target)) {
            $target = $this->getRelativeTargetPath($target);
        }
        if ($this->fileHelper->isAbsolutePath($link)) {
            $link = $this->getRelativeLinkPath($link);
        }
        $this->target = $target;
        $this->link = $link;
    }

    /**
     * @param string $absoluteTarget
     * @return string
     */
    protected function getRelativeTargetPath($absoluteTarget)
    {
        $targetRealPath = $this->fileHelper->getExistingRealPath($absoluteTarget);
        return $this->getRelativePathToCurrentDir($targetRealPath);
    }

    /**
     * @param string $absoluteLink
     * @return string
     */
    protected function getRelativeLinkPath($absoluteLink)
    {
        $linkParentPath = dirname($absoluteLink);
        $linkName = basename($absoluteLink);
        $linkRealPath = $this->fileHelper->getExistingRealPath($linkParentPath) . '/' . $linkName;
        return $this->getRelativePathToCurrentDir($linkRealPath);
    }

    /**
     * @param string $realPath with symlinks resolved
     * @return string
     */
    protected function getRelativePathToCurrentDir($realPath)
    {
        $currentPathSubDirs = explode('/', getcwd());
        $pathSubDirs = explode('/', $realPath);
        $pathBack = "";
        foreach ($currentPathSubDirs as $level => $subdir) {
            if (isset($pathSubDirs[$level]) && $subdir == $pathSubDirs[$level] && !$pathBack) {
                unset($pathSubDirs[$level]);
            } else {
                $pathBack .= '../';
            }
        }
        return $pathBack . implode('/', $pathSubDirs);
    }

    /**
     * @return string
     */
    protected function getRelativeTargetToLink()
    {
        $pathBack = str_repeat('../', $this->getLinkDirLevelsNumber());
        $relativeTarget = $pathBack . $this->target;
        return $relativeTarget;
    }

    /**
     * @return int
     */
    protected function getLinkDirLevelsNumber()
    {
        $linkDirname = dirname($this->link);
        if ($linkDirname == '.') {
            return 0;
        }
        return count(explode('/', $linkDirname));
    }

}
