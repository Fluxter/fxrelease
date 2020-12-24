<?php

namespace Fluxter\FXRelease\Command;

use Fluxter\FXRelease\Command\Abstraction\AbstractCommand;
use Fluxter\FXRelease\Command\Abstraction\AbstractReleaseCommand;
use Fluxter\FXRelease\Model\Configuration;
use Fluxter\FXRelease\Service\ConfigurationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ReleaseCommand extends AbstractReleaseCommand
{
    protected static $defaultName = 'release';

    protected function configure()
    {
        $this
            ->setDescription('Creates the release branch')
        ;
    }

    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->ss->section("Preparing release...");
        
        $projectName = $this->platform->getProjectName();
        $this->ss->text("Found project " . $projectName);

        $version = $this->getMilestone();
        $releaseBranch = "release/" . $version->getName();
        
        $branch = $this->git->getCurrentBranch();
        $this->ss->text("Checking out release branch...");
        $this->git->checkout($releaseBranch);

        $this->ss->text("Merging $branch into $releaseBranch...");
        $this->git->merge($branch);

        if ($this->config->getVersionFile() && $this->config->getVersionPattern()) {
            $this->ss->text("Setting version in file {$this->config->getVersionFile()}...");
            $this->setVersionNumber($version->getName());
        }

        $this->ss->text("Pushing release branch...");
        $this->git->push();

        $this->ss->text("Creating merge request...");
        $mr = $this->platform->createMergeRequest($version, $releaseBranch, $this->config->getMasterBranch());
        $this->ss->info("Created merge request. Please review it and resolve the WIP status at " . $mr->getUrl());

        while (!$this->platform->isMergeRequestReady($mr)) {
            $this->output->write(".");
            sleep(2);
        }

        $this->ss->text("Finishing the release...");
        $this->platform->finishRelease($mr, $version);

        return 0;
    }

    private function setVersionNumber(string $version)
    {
        $file = getcwd() . "/" . $this->config->getVersionFile();
        if (!file_exists($file)) {
            throw new \Exception("the Version file does not exist! searched for " . realpath($file));
        }

        $content = file_get_contents($file);
        $pattern = "/" . str_replace("FXRELEASE_VERSION_HERE", "(.*?)", $this->config->getVersionPattern()) . "/";
        $content = preg_replace($pattern, str_replace("FXRELEASE_VERSION_HERE", $version, $this->config->getVersionPattern()), $content);
        file_put_contents($file, $content);

        $this->git->commit("Bump version to " . $version);
    }
}
