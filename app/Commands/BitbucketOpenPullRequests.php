<?php

namespace App\Commands;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use LaravelZero\Framework\Commands\Command;

class BitbucketOpenPullRequests extends Command
{

    protected $bitbucketApiUrl = "https://api.bitbucket.org/2.0/repositories/%s/pullrequests";

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'bitbucket:open-prs {project} {--username=} {--token=} {--raw} {--ttl=300}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Retrieve the amount of open pull requests in bitbucket.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (empty($username = $this->option('username')) || empty($token = $this->option('token'))) {
            $this->output->error("Please specify both your username "
                . "and token using the --username and --token options");

            return 1;
        }

        $raw = $this->option('raw');
        $project = $this->argument('project');

        try {
            $amount = Cache::remember(
                md5("bitbucket-prs-open-{$project}"),
                $this->option('ttl'),
                function () use ($project, $username, $token) {
                    $url = sprintf($this->bitbucketApiUrl, $project);
                    $client = new Client();

                    return count(\json_decode($client->request('GET', $url, [
                        'headers' => [['Content-Type' => 'application/json']],
                        'auth' => [$username, $token]
                    ])->getBody()->getContents())->values);
                }
            );

            if ($raw) {
                echo $amount;
            } else {
                if ($amount === 0) {
                    $this->output->success("There are no open pull requests in {$project}!");
                } else {
                    $this->output->note("You have {$amount} open pull requests in {$project}");
                }
            }

            return 0;
        } catch (\Exception $e) {
            if ($raw) {
                echo "-";

                return 1;
            }

            $this->output->error($e->getMessage());

            return 1;
        }
    }
}
