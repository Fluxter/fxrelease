<?php

namespace Fluxter\FXRelease\Service;

use Cz\Git\GitRepository;

class GitCliService
{
    private GitRepository $git;
    
    public function __construct()
    {
        $this->git = new GitRepository(getcwd());
    }
    
    private function getBranches(): array
    {
        return $this->git->getBranches();
    }

    public function getCurrentBranch(): string
    {
        return $this->git->getCurrentBranchName();
    }

    public function isDirty(): bool
    {
        return $this->git->hasChanges();
    }

    public function merge(string $branch): void
    {
        $current = $this->getCurrentBranch();
        $this->git->checkout($branch);
        $this->git->pull();
        $this->git->checkout($current);
        $this->git->merge($branch);
    }

    public function checkout(string $branch): void
    {
        $existingBranches = $this->getBranches();
        if (in_array($branch, $existingBranches)) {
            $this->git->checkout($branch);
        } else {
            $this->git->createBranch($branch, true);
        }
    }

    public function push(): void
    {
        $this->git->push("origin", [$this->getCurrentBranch(), "--set-upstream"]);
    }

    public function commit(string $message): void
    {
        if ($this->git->hasChanges()) {
            $this->git->addAllChanges();
            $this->git->commit($message);
        }
    }
}
