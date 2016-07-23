<?php
namespace Robo\Task\Development;

use Robo\Common\Timer;
use Robo\Result;

/**
 * Publishes new GitHub release.
 *
 * ``` php
 * <?php
 * $this->taskGitHubRelease('0.1.0')
 *   ->uri('Codegyre/Robo')
 *   ->description('Add stuff people need.')
 *   ->change('Fix #123')
 *   ->change('Add frobulation method to all widgets')
 *   ->run();
 * ?>
 * ```
 */
class GitHubRelease extends GitHub
{
    use Timer;

    protected $tag;
    protected $name;
    protected $description = '';
    protected $changes = [];
    protected $draft = false;
    protected $prerelease = false;
    protected $comittish = 'master';

    public function __construct($tag)
    {
        $this->tag = $tag;
    }

    public function tag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    public function draft($draft)
    {
        $this->draft = $draft;
        return $this;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function description($description)
    {
        $this->description = $description;
        return $this;
    }

    public function prerelease($prerelease)
    {
        $this->prerelease = $prerelease;
        return $this;
    }

    public function comittish($comittish)
    {
        $this->comittish = $comittish;
        return $this;
    }

    public function appendDescription($description)
    {
        if (!empty($this->description)) {
            $$this->description .= "\n\n";
        }
        $this->description .= $description;
        return $this;
    }

    public function changes(array $changes)
    {
        $this->changes = array_merge($this->changes, $changes);
        return $this;
    }

    public function change(string $change)
    {
        $this->changes[] = $change;
        return $this;
    }

    protected function getBody()
    {
        $body = $this->description;
        if (!empty($this->changes)) {
            $changes = array_map(
                function ($line) {
                    return "* $line";
                },
                $this->changes
            );
            $changesText = implode("\n", $changes);
            $body .= "### Changelog \n\n$changesText";
        }
        return $body;
    }

    public function run()
    {
        $this->printTaskInfo('Releasing {tag}', ['tag' => $this->tag]);
        $this->startTimer();
        // https://developer.github.com/v3/repos/releases/#create-a-release
        $result = $this->sendRequest(
            'POST /repos/:owner/:repo/releases',
            [
                "tag_name" => $this->tag,
                "target_commitish" => $this->comittish,
                "name" => $this->tag,
                "body" => $this->getBody(),
                "draft" => $this->draft,
                "prerelease" => $this->prerelease
            ]
        );
        $this->stopTimer();
        $result['time'] = $this->getExecutionTime();
        return $result;
    }
}
