<?php
/**
 * CreateLinkCommand
 *
 * Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\Symlinker\Command\Create;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLinkCommand extends AbstractCreateCommand
{
    const ARG_SOURCE = 'source';
    const ARG_DESTINATION = 'destination';

    protected function configure()
    {
        parent::configure();

        $this->setName('create:link')
            ->setDescription('Create a relative symlink between source and target')
            ->addArgument(
                self::ARG_SOURCE,
                InputArgument::REQUIRED,
                'Path to source file or dir'
            )->addArgument(
                self::ARG_DESTINATION,
                InputArgument::REQUIRED,
                'Path to destination'
            );
    }

    /**
     * @inheritdoc
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        if (!$input->getArgument(self::ARG_SOURCE)) {
            $sourceInput = $this->symfonyStyle->ask('Source Path:');
            $input->setArgument(self::ARG_SOURCE, $sourceInput);
        }
        if (!$input->getArgument(self::ARG_DESTINATION)) {
            $destInput = $this->symfonyStyle->ask('Destination Path:');
            $input->setArgument(self::ARG_DESTINATION, $destInput);
        }
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $source = $input->getArgument(self::ARG_SOURCE);
        $dest = $input->getArgument(self::ARG_DESTINATION);
        $this->symlinkTask->createSymlink($source, $dest);

        $this->symfonyStyle->success('Symlink successfully created!');
    }
}