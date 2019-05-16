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
//

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class QueryBuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Builder $factory)
    {
        $factory->macro('whereIf', function ($operand, $column, $value, $operator = '=') {
            if ((is_array($value) && !empty(array_filter($value))) || isset($value,$operand, $column)) {
                switch ($operand) {
                    case 'where':
                        return $this->where($column, $operator, $value);

                    case 'whereIn':
                        return $this->whereIn($column, $value);

                    default:
                        return $this;
                }
            }
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
    }
}
