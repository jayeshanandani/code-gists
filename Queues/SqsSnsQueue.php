<?php

//                             _
//                           .' `'.__
//                          /      \ `'"-,
//         .-''''--...__..-/ .     |      \
//       .'               ; :'     '.  a   |
//      /                 | :.       \     =\
//     ;                   \':.      /  ,-.__;.-;`
//    /|     .              '--._   /-.7`._..-;`     THIS FILE WILL BE OVERWRITTEN BY MS-TEMPLATE.
//   ; |       '                |`-'      \  =|      PLEASE EDIT THERE IF YOU WISH TO MAKE CHANGES.
//   |/\        .   -' /     /  ;         |  =/
//   (( ;.       ,_  .:|     | /     /\   | =|
//    ) / `\     | `""`;     / |    | /   / =/
//      | ::|    |      \    \ \    \ `--' =/
//     /  '/\    /       )    |/     `-...-`
//    /    | |  `\    /-'    /;
//    \  ,,/ |    \   D    .'  \
//     `""`   \  nnh  D_.-'L__nnh

namespace App\Queue;

use Aws\Sqs\SqsClient;
use App\Queue\Jobs\SqsSnsJob;
use Illuminate\Queue\SqsQueue;

class SqsSnsQueue extends SqsQueue
{
    /**
     * The Job command routes by Subject.
     *
     * @var array
     */
    protected $routes;

    /**
     * Create a new Amazon SQS SNS subscription queue instance.
     *
     * @param \Aws\Sqs\SqsClient $sqs
     * @param string             $default
     * @param string             $prefix
     * @param array              $routes
     */
    public function __construct(SqsClient $sqs, $default, $prefix = '', $routes = [])
    {
        parent::__construct($sqs, $default, $prefix);

        $this->routes = $routes;
    }

    /**
     * Pop the next job off of the queue.
     *
     * @param string $queue
     *
     * @return App\Queue\Jobs\SqsSnsJob
     */
    public function pop($queue = null)
    {
        $queue = $this->getQueue($queue);

        $response = $this->sqs->receiveMessage([
            'QueueUrl'       => $queue,
            'AttributeNames' => ['ApproximateReceiveCount'],
            ]);

        if (isset($response['Messages'])) {
            return new SqsSnsJob(
                $this->container,
                $this->sqs,
                $response['Messages'][0],
                $this->connectionName,
                $queue,
                $this->routes
            );
        }
    }
}
