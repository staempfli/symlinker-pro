<?php
/**
 * SymlinkHelper
 *
 * Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */
namespace Staempfli\Symlinker\Task;

use Staempfli\Symlinker\Helper\FileHelper;
use Staempfli\Symlinker\Helper\RelativeTargetHelper;
use Staempfli\Symlinker\Helper\SourcePathHelper;
use Symfony\Component\Console\Output\OutputInterface;

class SymlinkTask
{
    /**
     * @var FileHelper
     */
    protected $fileHelper;
    /**
     * @var SourcePathHelper
     */
    protected $sourcePathHelper;
    /**
     * @var RelativeTargetHelper
     */
    protected $relativeTargetHelper;
    /**
     * @var OutputInterface
     */
    protected $consoleOutput;
    /**
     * @var string
     */
    protected $source;
    /**
     * @var string
     */
    protected $dest;
    /**
     * @var bool
     */
    protected $wildcardsEnabled = false;
    /**
     * @var bool
     */
    protected $overwriteEnabled = false;
    /**
     * @var bool
     */
    protected $dryRunEnabled = false;
    /**
     * @var bool
     */
    protected $createDirectory = false;

    /**
     * SymlinkTask constructor.
     */
    public function __construct()
    {
        $this->fileHelper = new FileHelper();
        $this->sourcePathHelper = new SourcePathHelper();
        $this->relativeTargetHelper = new RelativeTargetHelper();
    }

    /**
     * @param OutputInterface $consoleOutput
     */
    public function setConsoleOutput(OutputInterface $consoleOutput)
    {
        $this->consoleOutput = $consoleOutput;
    }

    public function enableWildcards()
    {
        $this->wildcardsEnabled = true;
    }
    
    public function enableOverwrite()
    {
        $this->overwriteEnabled = true;
    }

    public function enableDryRun()
    {
        $this->dryRunEnabled = true;
    }

    public function enableCreateDirectory()
    {
        $this->createDirectory = true;
    }

    /**
     * @param string $source
     * @param string $dest
     * @return string
     */
    public function createSymlink($source, $dest)
    {
        $this->init($source, $dest);
        if ($this->sourcePathHelper->isWildCardPath()) {
            $this->createSymlinksUsingWildcards();
        } else {
            $this->createRelativeTargetLink($this->source, $this->dest);
        }
    }

    /**
     * @param string $source
     * @param string $dest
     */
    protected function init($source, $dest)
    {
        $this->source = rtrim($source, '/');
        $this->dest = rtrim($dest, '/');
        $this->sourcePathHelper->setPath($this->source);
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function createSymlinksUsingWildcards()
    {
        if (!$this->wildcardsEnabled) {
            throw new \Exception('"*" and "**" wildcards are not enabled. Try using --enable-wildcards');
        }
        $recursivePath = $this->sourcePathHelper->getRecursiveDirPath();
        if ($recursivePath) {
            return $this->createRecursiveFileSymlinks($recursivePath, $this->dest);
        }
        $dirPath = $this->sourcePathHelper->getContentDirPath();
        if ($dirPath) {
            return $this->createSymlinksInDir($dirPath);
        }
    }

    /**
     * @param string $recursivePath
     * @param string $dest
     */
    protected function createRecursiveFileSymlinks($recursivePath, $dest)
    {
        $directoryIterator = $this->fileHelper->getDirectoryIterator($recursivePath);
        foreach ($directoryIterator as $fileInfo) {
            $link = $dest . '/' . $fileInfo->getFilename();
            if ($fileInfo->isDir()) {
                $this->createRecursiveFileSymlinks($fileInfo->getPathname(), $link);
                continue;
            } elseif ($fileInfo->isFile()) {
                $this->createRelativeTargetLink($fileInfo->getPathname(), $link);
            }
        }
    }

    /**
     * @param string $dir
     */
    protected function createSymlinksInDir($dir)
    {
        $directoryIterator = $this->fileHelper->getDirectoryIterator($dir);
        foreach ($directoryIterator as $fileInfo) {
            $link = $this->dest . '/' . $fileInfo->getFilename();
            $this->createRelativeTargetLink($fileInfo->getPathname(), $link);
        }
    }

    /**
     * @param string $target
     * @param string $link
     * @throws \Exception
     */
    protected function createRelativeTargetLink($target, $link)
    {
        $this->validate($target, $link);
        $relativeTarget = $this->relativeTargetHelper->getRelativeTarget($target, $link);
        if (!$this->dryRunEnabled) {
            $this->prepareDestination($link);
            if (!symlink($relativeTarget, $link)) {
                throw new \Exception(sprintf('There was an error creating symlink %s -> %s', $relativeTarget, $link));
            }
        }
        $this->consoleOutput->writeln(sprintf('- Symlink Created: %s -> %s', $relativeTarget, $link));
    }

    /**
     * @param string $target
     * @param string $link
     * @throws \Exception
     */
    protected function validate($target, $link)
    {
        if (!file_exists($target)) {
            throw new \Exception(sprintf('Source Target does not exists: %s', $target));
        }
        $parentDirForLink = dirname($link);
        if (!is_dir($parentDirForLink)) {
            if($this->createDirectory) {
                mkdir($parentDirForLink, 0755, true);
            } else {
                throw new \Exception(sprintf('Destination Parent dir not existing: %s', $parentDirForLink));
            }
        }
    }

    /**
     * @param string $link
     * @throws \Exception
     */
    protected function prepareDestination($link)
    {
        if (file_exists($link) || is_link($link)) {
            if (!$this->overwriteEnabled) {
                throw new \Exception(sprintf('Destination exists: %s. Use --force to overwrite', $link));
            }
            $this->fileHelper->removeExitingPath($link);
            $this->consoleOutput->writeln(sprintf('- Path removed: %s', $link));
        }
    }

}