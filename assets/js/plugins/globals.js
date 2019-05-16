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

const Globals = {
    install(Vue) {
        // **************************************
        //              Filters
        // **************************************

        // capitalizes first letter of a string.
        Vue.filter('capitalize', value => {
            if (!value) return ''
            const stringValue = value.toString()
            return `${stringValue.charAt(0).toUpperCase()}${stringValue.slice(1)}`
        })

        // cleans up JSON strings to look 'pretty'.
        Vue.filter('pretty', value => JSON.stringify(JSON.parse(value), null, 2))

        // search filter highlighting for vuetables2 custom column templates
        // to be used with 'v-html="$options.filters.highlight(*stringVariable*, filterValue)'
        // filterValue needs to be a data property set in response to VueTables "@filter" event.
        Vue.filter('highlight', (words, query) => {
            const regExpQuery = new RegExp(query, 'ig');
            return words.toString().replace(
                regExpQuery,
                matchedTxt => (`<b class="text-primary" dusk="vue-tables-custom-highlight">${matchedTxt}</b>`)
            )
        })

        // converts a boolean value to a more user-friendly 'Yes' or 'No'
        Vue.filter('convertBool', bool => (bool ? 'Yes' : 'No'))

        // **************************************
        //              Directives
        // **************************************

        // initialize a data value in vue from blade template
        Vue.directive('init', {
            bind(el, binding, vnode) {
                vnode.context[binding.arg] = binding.value
            },
        })

        // triggers when there is a click outside of target element (useful for hiding a custom dropdown)
        Vue.directive('click-outside', {
            bind(el, binding, vnode) {
                el.clickOutsideEvent = event => {
                    if (!(el === event.target || el.contains(event.target))) {
                        vnode.context[binding.expression](event);
                    }
                };
                document.body.addEventListener('click', el.clickOutsideEvent)
            },
            unbind(el) {
                document.body.removeEventListener('click', el.clickOutsideEvent)
            },
        });
    },
}

export default Globals
