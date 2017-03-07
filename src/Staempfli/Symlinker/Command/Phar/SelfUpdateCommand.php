<?php
/**
 * UpdateSelfCommand
 *
 * Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\Symlinker\Command\Phar;

use Humbug\SelfUpdate\Updater as PharUpdater;
use Staempfli\Symlinker\Helper\ApplicationHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommand extends Command
{
    /**
     * @var PharUpdater
     */
    protected $updater;
    /**
     * @var ApplicationHelper
     */
    protected $applicationHelper;

    /**
     * SelfUpdateCommand constructor.
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->updater = new PharUpdater(null, false, PharUpdater::STRATEGY_GITHUB);
        $this->applicationHelper = new ApplicationHelper();
        parent::__construct($name);
    }

    public function configure()
    {
        $applicationFileName = $this->applicationHelper->getApplicationFileName();

        $this->setName('self-update')
            ->setAliases(['selfupdate'])
            ->setDescription(sprintf('Updates "%s" to the latest stable version', $applicationFileName))
            ->setHelp(<<<EOT

The <info>self-update</info> command checks github for newer
versions of {$applicationFileName} and if found, installs the latest.

<info>php {$applicationFileName} self-update</info>

EOT
            );
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        $pharFilePath = $this->applicationHelper->getPharPath();
        if ($pharFilePath) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->updater->getStrategy()->setPackageName('staempfli/symlinker-pro');
        $this->updater->getStrategy()->setPharName('symlinker-pro.phar');
        $this->updater->getStrategy()->setCurrentLocalVersion('@git-version@');

        try {
            $result = $this->updater->update();
            if ($result) {
                $newVersion = $this->updater->getNewVersion();
                $oldVersion = $this->updater->getOldVersion();
                $output->writeln(sprintf('<bg=green;options=bold>Updated to version %s from %s</>', $newVersion, $oldVersion));
            } else {
                $output->writeln('<info>No update needed!</info>');
            }
        } catch (\Exception $e) {
            $output->writeln('<error>There was an error while updating. Please try again later</error>');
        }
    }

}