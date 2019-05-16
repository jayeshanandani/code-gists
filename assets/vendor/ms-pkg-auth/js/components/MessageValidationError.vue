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
// NOTE: If you are editing this file in the MS-PKG-AUTH repo, continue to make
// the changes here. They will be copied across the individual microservices by MS-TEMPLATE.

<template>
    <div v-cloak>
        <span class="help-block login__text--invalid d-inline-block">
            {{ msg }}
        </span>
    </div>
</template>

<script>
export default {
    props: {
        msg: {
            type: String,
            default: 'Invalid input.',
        },
    },
}
</script>
