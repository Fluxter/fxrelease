<?php

namespace Fluxter\FXRelease\Command\Abstraction;

use Fluxter\FXRelease\Service\GitCliService;
use Fluxter\FXRelease\Service\GitPlatformService;
use Fluxter\FXRelease\Service\GitPlatform\ReleasePlatformProviderInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractReleaseCommand extends AbstractCommand
{
    protected ReleasePlatformProviderInterface $platform;
    protected GitCliService $git;

    public function __construct()
    {
        parent::__construct();
        $this->git = new GitCliService();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);
        $platformService = new GitPlatformService();
        $this->platform = $platformService->getPlatform($this->config);
    }

    protected function getMilestone()
    {
        $milestones = $this->platform->getMilestones();

        $this->ss->text("Please select a milestone!");
        $this->ss->text("Current available versions / milestones:");
        foreach ($milestones as $m) {
            $this->ss->text(" - [{$m->getId()}]: {$m->getName()} (Global ID: {$m->getGlobalId()})");
        }

        if (count($milestones) == 0) {
            throw new \Exception("No milestones found!");
        }

        $id = $this->ss->ask("Milestone:");

        return $milestones[$id];
    }
}
