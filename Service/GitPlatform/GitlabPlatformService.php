<?php

namespace Fluxter\FXRelease\Service\GitPlatform;

use Fluxter\FXRelease\Model\Configuration;
use Fluxter\FXRelease\Model\PlatformMergeRequest;
use Fluxter\FXRelease\Model\PlatformMilestone;
use Fluxter\FXRelease\Service\GitPlatform\ReleasePlatformProviderInterface;
use Gitlab\Client;

class GitlabPlatformService implements ReleasePlatformProviderInterface
{
    private Configuration $config;
    private Client $client;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
        $this->initClient();
    }

    private function initClient()
    {
        $this->client = new Client();
        if ($this->config->getUrl() !== null) {
            $this->client->setUrl($this->config->getUrl());
        }
        $this->client->authenticate($this->config->getApiKey(), Client::AUTH_HTTP_TOKEN);
    }

    /** @inheritdoc */
    public function getMilestones(): array
    {
        $milestones = [];

        $response = $this->client->milestones()->all($this->config->getProjectId(), [
            "state" => "active"
        ]);
        foreach ($response as $remote) {
            $milestones[$remote["iid"]] = new PlatformMilestone($remote["iid"], $remote["id"], $remote["title"]);
        }

        return $milestones;
    }

    public function getProjectName(): string
    {
        $project = $this->client->projects()->show($this->config->getProjectId());
        return $project["name_with_namespace"];
    }

    private function getPlatformMergeRequestFromGitlabMergeRequet(array $mrData): PlatformMergeRequest
    {
        return new PlatformMergeRequest($mrData["iid"], $mrData["web_url"]);
    }

    private function getIssuesForMilestone(PlatformMilestone $milestone): array
    {
        // return $this->client->milestones()->issues($this->config->getProjectId(), $milestone->getId());
        $allIssues =  $this->client->issues()->all($this->config->getProjectId(), [
            "milestone" => $milestone->getName(),
        ]);

        return array_values(array_filter($allIssues, fn($issue) => !$issue["confidential"]));
    }

    private function getDescriptionForMilestone(PlatformMilestone $milestone): string
    {
        $issues = $this->getIssuesForMilestone($milestone);
        $desc = "This is the auto-generated Changelog for version v{$milestone->getName()}";

        foreach ($issues as $issue) {
            $desc .= "\n* [Issue #{$issue['iid']}]({$issue['web_url']}): \t{$issue['title']}";
        }

        return $desc;
    }

    public function createMergeRequest(PlatformMilestone $milestone, string $sourceBranch, string $targetBranch): PlatformMergeRequest
    {
        $title = "WIP: Release v" . $milestone->getName();
        $description = $this->getDescriptionForMilestone($milestone);
        $existing = $this->client->mergeRequests()->all($this->config->getProjectId(), [
            "state" => "opened",
            "source_branch" => $sourceBranch,
            "target_branch" => $targetBranch
        ]);

        $mr = null;
        if (count($existing)) {
            $mr = $this->getPlatformMergeRequestFromGitlabMergeRequet($existing[0]);
        } else {
            $result = $this->client->mergeRequests()->create($this->config->getProjectId(), $sourceBranch, $targetBranch, $title, [
                "remove_source_branch" => true
            ]);
            $mr = $this->getPlatformMergeRequestFromGitlabMergeRequet($result);
        }

        $this->client->mergeRequests()->update($this->config->getProjectId(), $mr->getId(), [
            "title" => $title,
            "description" => $description,
            'milestone_id' => $milestone->getGlobalId()
        ]);

        return $mr;
    }

    public function isMergeRequestReady(PlatformMergeRequest $mr): bool
    {
        $remoteMr = $this->client->mergeRequests()->show($this->config->getProjectId(), $mr->getId());
        return strpos($remoteMr["title"], "WIP:") !== 0;
    }

    public function finishRelease(PlatformMergeRequest $mr, PlatformMilestone $milestone): void
    {
        // Accept the merge request
        $this->client->mergeRequests()->merge($this->config->getProjectId(), $mr->getId());

        // Create the tag
        $this->client->tags()->create($this->config->getProjectId(), [
            'tag_name' => 'v' . $milestone->getName(),
            "release_description" => $this->getDescriptionForMilestone($milestone),
            "ref" => $this->config->getMasterBranch(),
        ]);

        // // Create the Release
        // $this->client->tags()->createRelease($this->config->getProjectId(), "v" . $milestone->getName(), [
        //     "release_description" => $this->getDescriptionForMilestone($milestone),
        //     "ref" => $this->config->getMasterBranch(),
        // ]);

        // Close the milestone
        $this->client->milestones()->update($this->config->getProjectId(), $milestone->getGlobalId(), [
            "state_event" => "close",
        ]);
    }
}
