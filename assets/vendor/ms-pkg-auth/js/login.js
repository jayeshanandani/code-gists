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

import Vue from 'vue'

// components
import MessageValidationError from './components/MessageValidationError.vue'

// mixins
import { validationError } from './components/mixins/validationError'

new Vue({ // eslint-disable-line
    el: '#login',
    components: {
        MessageValidationError,
    },
    mixins: [ validationError ],
    data() {
        return {
            errors: {},
            login: {
                username: '',
                password: '',
            },
            isSubmitting: false,
        }
    },
    methods: {
        submit() {
            const { username, password } = this.login

            this.isSubmitting = true

            // If a user tries to access a specific page without being logged in this will
            // pull the redirect_url from the query params
            let redirectUrl = null
            const urlParams = new RegExp('[\\?&]redirect_url=([^&#]*)').exec(window.location.search)
            if (urlParams) {
                redirectUrl = decodeURIComponent(urlParams[1].replace(/\+/g, ' '))
            }

            axios({
                method: 'post',
                url: '/authenticate',
                data: { username, password },
            }).then(() => {
                this.isSubmitting = false
                const url = redirectUrl || '/'
                window.location.href = url
            }).catch(error => {
                this.isSubmitting = false
                this.errors = this.checkErrors(error.response.data.errors)
            })
        },
    },
})
