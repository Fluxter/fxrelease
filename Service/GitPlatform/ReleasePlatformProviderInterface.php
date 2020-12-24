<?php

namespace Fluxter\FXRelease\Service\GitPlatform;

use Fluxter\FXRelease\Model\PlatformMergeRequest;
use Fluxter\FXRelease\Model\PlatformMilestone;

interface ReleasePlatformProviderInterface
{
    /** @return PlatformMilestone[] */
    public function getMilestones(): array;

    public function getProjectName(): string;

    public function createMergeRequest(PlatformMilestone $milestone, string $sourceBranch, string $targetBranch): PlatformMergeRequest;

    public function isMergeRequestReady(PlatformMergeRequest $mr): bool;

    public function finishRelease(PlatformMergeRequest $mr, PlatformMilestone $milestone): void;
}
