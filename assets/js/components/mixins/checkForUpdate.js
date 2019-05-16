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

import { toastedMessages } from './toastedMessages'

export const checkForUpdate = {
    mixins: [ toastedMessages ],
    methods: {
        checkForUpdate(route, uuid) {
            const urlParams = new URLSearchParams(window.location.search)
            const action = urlParams.get('action')
            const type = urlParams.get('type')
            const module = urlParams.get('module')

            if (type && action) {
                axios.get(`${route}/response`, {
                    params: {
                        action,
                        type,
                        module,
                    },
                })
                    .then(response => {
                        if (response.data.success) {
                            this.toastedSuccessMessage(response.data.success)
                        } else {
                            this.toastedWarningMessage(response.data.info)
                        }
                        (uuid) ? window.history.replaceState(route, null, uuid) : window.history.replaceState(null, null, route) //eslint-disable-line
                    })
                    .catch(err => {
                        console.error(err)
                    })
            }
        },

    },
}
