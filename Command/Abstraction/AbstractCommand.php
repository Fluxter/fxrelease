<?php

namespace Fluxter\FXRelease\Command\Abstraction;

use Fluxter\FXRelease\Model\Configuration;
use Fluxter\FXRelease\Service\ConfigurationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{
    protected ConfigurationService $configService;
    protected Configuration $config;
    protected SymfonyStyle $ss;
    protected OutputInterface $output;

    public function __construct()
    {
        parent::__construct();
        $this->configService = new ConfigurationService();
        $this->config = $this->configService->getConfiguration();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->ss = new SymfonyStyle($input, $output);
    }
}
