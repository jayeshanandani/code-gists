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

namespace App;

use Illuminate\Support\Facades\Log;

class MsLogClass
{
    /**
     * Get logs for all crud and model interaction.
     *
     * @param  object $model
     * @param  string $action
     * @return void
     */
    public function logModelAction($model, $action)
    {
        $message = getClassShortName($model) . $action;
        Log::info($this->prepareParams($message, ['uuid' => $model->uuid]));
    }

    /**
     * Get info logs for rest of function which are not interaction with model.
     *
     * @param  string $message
     * @param  array  $params
     * @return void
     */
    public function info($message, $params = [])
    {
        Log::info($this->prepareParams($message, $params));
    }

    /**
     * Get warning logs.
     *
     * @param  string $message
     * @param  array  $params
     * @return void
     */
    public function warning($message, $params = [])
    {
        Log::warning($this->prepareParams($message, $params));
    }

    /**
     * Get error logs.
     *
     * @param  string $message
     * @param  array  $params
     * @return void
     */
    public function error($message, $params = [])
    {
        Log::error($this->prepareParams($message, $params));
    }

    /**
     * Preparing parameters.
     *
     * @param  string $message
     * @param  array  $params
     * @return void
     */
    public function prepareParams($message, $params = [])
    {
        return json_encode(array_merge([
            'auditUuid' => request('auditUuid'),
            'message'   => $message,
        ], $params));
    }
}
