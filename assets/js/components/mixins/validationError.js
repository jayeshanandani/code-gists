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

export const validationError = {
    methods: {

        checkErrors(errors) {
            const errorMessage = {}

            Object.keys(errors).forEach(err => {
                errorMessage[err] = errors[err][0]
            })

            return errorMessage
        },
    },
}
