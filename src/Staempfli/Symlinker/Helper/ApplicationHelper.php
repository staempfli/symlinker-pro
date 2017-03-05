<?php
/**
 * ApplicationHelper
 *
 * Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\Symlinker\Helper;


class ApplicationHelper
{
    const DEFAULT_APPLICATION_FILENAME = "symlinker-pro";

    /**
     * @return mixed
     */
    public function getProjectBaseDir()
    {
        return BP;
    }

    /**
     * @return string
     */
    public function getPharPath()
    {
        return \Phar::running(false);
    }

    /**
     * @return string
     */
    public function getApplicationFileName()
    {
        if ($this->getPharPath()) {
            return basename($this->getPharPath());
        }
        if (defined('COMMAND_NAME')) {
            return COMMAND_NAME;
        }
        return self::DEFAULT_APPLICATION_FILENAME;
    }
}