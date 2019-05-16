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

namespace App\Traits;

use MsLog;

trait ChangeStatus
{
    public function changeStatus($status, $uuid)
    {
        MsLog::info(null, [
            'class'  => class_basename(get_called_class()),
            'method' => class_basename(__FUNCTION__),
            'status' => $status,
            'uuid'   => $uuid,
            ]);
        if (!in_array($status, config('ms_global.status'))) {
            return response()->jsonError($this->module, config('ms_global.status.INVALID_STATUS'));
        }
        $model = $this->whereUuid($uuid)->firstOrFail();
        $model->status = $status;
        $model->save();

        return response()->jsonSuccess($this->module, config('ms_global.action.STATUS_CHANGED'), ['status' => $model->status]);
    }
}
