<?php
/**
 * CreateFromFileCommand
 *
 * Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\Symlinker\Command\Create;

use Staempfli\Symlinker\Helper\FileHelper;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateFromFileCommand extends AbstractCreateCommand
{
    const ARG_FILE_PATH = 'path';
    const OPTION_DESTINATION_PREFIX = 'dest-prefix-path';

    /**
     * @var FileHelper
     */
    protected $fileHelper;

    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->fileHelper = new FileHelper();
    }

    protected function configure()
    {
        parent::configure();

        $this->setName('create:from:file')
            ->setDescription('Create bunch of symlinks defined in a file content')
            ->addArgument(
                self::ARG_FILE_PATH,
                InputArgument::REQUIRED,
                'File path containing symlinks information'
            )->addOption(
                self::OPTION_DESTINATION_PREFIX,
                'p',
                InputOption::VALUE_OPTIONAL,
                'Append custom prefix path to append to destination link, If Destination link definition is not relative to same root as source.'
            );
    }

    /**
     * @inheritdoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);
        if (!$input->getArgument(self::ARG_FILE_PATH)) {
            $question = new Question('<question>File Path:</question>');
            $fileInput = $this->questionHelper->ask($input, $output, $question);
            $input->setArgument(self::ARG_FILE_PATH, $fileInput);
        }
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $fileLines = $this->getFileLines($input);
        foreach ($fileLines as $line) {
            list($source, $dest) = $this->getSourceAndDestFromTextLine($line);
            $dest = $this->getDestPathWithPrefixAppended($dest, $input);
            $this->symlinkTask->createSymlink($source, $dest);
        }
        $output->writeln('<bg=green;options=bold>Symlinks successfully created!</>');
    }

    /**
     * @param InputInterface $input
     * @return array
     */
    protected function getFileLines(InputInterface $input)
    {
        $filePath = $input->getArgument(self::ARG_FILE_PATH);
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException(sprintf('File not found in %s', $filePath));
        }
        return file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * @param string $line
     * @return array pair [$source, $dest]
     * @throws \Exception
     */
    protected function getSourceAndDestFromTextLine($line)
    {
        $symlinkData = explode('=>', $line);
        if (count($symlinkData) != 2) {
            throw new \Exception(sprintf('Symlink definition not matching "{source}=>{dest}". Error Line: %s', $line));
        }
        $source = $symlinkData[0];
        $dest = $symlinkData[1];
        return [$source, $dest];
    }

    protected function getDestPathWithPrefixAppended($path, InputInterface $input)
    {
        $prefixPath = $input->getOption(self::OPTION_DESTINATION_PREFIX);
        if ($prefixPath && !$this->fileHelper->isAbsolutePath($path)) {
            $prefixPath .= (strpos($prefixPath, -1) != '/') ? '/' : '';
            return $prefixPath . $path;
        }
        return $path;
    }

}