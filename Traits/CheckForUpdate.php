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

trait CheckForUpdate
{
    public function checkResponse($module, $type, $action)
    {
        MsLog::info(null, [
            'class'                       => class_basename(get_called_class()),
            'method'                      => class_basename(__FUNCTION__),
            'module'                      => $module,
            'type'                        => $type,
            'action'                      => $action,
        ]);
        if (!in_array($type, ['success', 'warning'])) {
            return response()->jsonError($type, $this->actionNotFound);
        }

        $result = $this->checkAction($action);
        if (!$result['success']) {
            return response()->jsonError($action, $this->actionNotFound);
        }

        if ($type === 'success') {
            return response()->jsonSuccess($module, $result['action']);
        } elseif ($type === 'warning') {
            return response()->jsonInfo($module, $result['action']);
        }
    }

    public function checkAction($action)
    {
        MsLog::info(null, [
            'class'                       => class_basename(get_called_class()),
            'method'                      => class_basename(__FUNCTION__),
            'action'                      => $action,
        ]);
        $success = true;
        switch ($action) {
            case 'create':
                $action = $this->actionSave;

                break;
            case 'update':
                $action = $this->actionUpdate;

                break;
            case 'link':
                $action = $this->actionLink;

                break;
            case 'unlink':
                $action = $this->actionUnlink;

            break;
            case 'delete':
                $action = $this->actionDelete;

            break;
            case 'no-change':
                $action = $this->notChanged;

            break;
            case 'duplicate':
                $action = $this->actionDuplicate;

            break;
            default:
                $success = false;

            break;
        }

        return  [
            'success' => $success,
            'action'  => $action,
        ];
    }
}
