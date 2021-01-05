<?php

namespace Fluxter\FXRelease\Command;

use Fluxter\FXRelease\Command\Abstraction\AbstractCommand;
use Fluxter\FXRelease\Command\Abstraction\AbstractReleaseCommand;
use Fluxter\FXRelease\Model\Configuration;
use Fluxter\FXRelease\Model\ConfigurationVersionFile;
use Fluxter\FXRelease\Model\PlatformMilestone;
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

        if ($this->git->isDirty()) {
            $this->ss->error("Please commit your current changes!");
            return 1;
        }
        
        $projectName = $this->platform->getProjectName();
        $this->ss->text("Found project " . $projectName);

        $version = $this->getMilestone();
        $releaseBranch = "release/" . $version->getName();

        $branch = $this->git->getCurrentBranch();
        $this->ss->text("Checking out release branch...");
        $this->git->checkout($releaseBranch);

        $this->setVersionNumbers($version);
        $this->ss->text("Merging $branch into $releaseBranch...");
        $this->git->merge($branch);
        $this->ss->text("Merging {$this->config->getMasterBranch()} into $releaseBranch...");
        $this->git->merge($this->config->getMasterBranch());

        $this->ss->text("Pushing release branch...");
        $this->git->push();

        $this->ss->text("Creating merge request...");
        $mr = $this->platform->createMergeRequest($version, $releaseBranch, $this->config->getMasterBranch());
        $this->ss->info("Created merge request. Please review it at " . $mr->getUrl());

        $release = $this->ss->ask("Preparation done. Do you want to release? (y/n)");
        if (strtolower($release) != "y") {
            $this->ss->info("Stopping! Just re-run the command if you are ready to release.");
            return 0;
        }

        $this->ss->info("Waiting for the MergeRequest to be marked as ready...");
        while (!$this->platform->isMergeRequestReady($mr)) {
            $this->output->write(".");
            sleep(2);
        }

        $this->ss->text("Finishing the release...");
        $this->platform->finishRelease($mr, $version);

        return 0;
    }

    private function setVersionNumbers(PlatformMilestone $milestone): void
    {
        $version = $milestone->getName();
        foreach ($this->config->getVersionFiles() as $file) {
            $this->ss->text("Setting version in file {$file->getFile()}...");
            $this->setVersionNumber($file, $version);
        }

        $this->git->commit("Bump version to " . $version);
    }

    private function setVersionNumber(ConfigurationVersionFile $file, string $version)
    {
        $filepath = getcwd() . "/" . $file->getFile();
        if (!file_exists($filepath)) {
            throw new \Exception("The versionfile does not exist: " . realpath($filepath));
        }

        $content = file_get_contents($filepath);
        $pattern = "/" . str_replace("FXRELEASE_VERSION_HERE", "(.*?)", $file->getPattern()) . "/";
        $content = preg_replace($pattern, str_replace("FXRELEASE_VERSION_HERE", $version, $file->getPattern()), $content);
        file_put_contents($filepath, $content);
    }
}
