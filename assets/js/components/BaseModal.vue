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

<template>
    <transition name="modal-fade">
        <div class="modal-mask">
            <div
                class="modal d-block"
                role="dialog">
                <div
                    class="modal-dialog"
                    role="document">
                    <div
                        class="modal-content"
                        dusk="modal-content">

                        <div class="modal-header">
                            <slot name="header"/>
                            <button
                                type="button"
                                class="close"
                                aria-label="Close"
                                dusk="modal-close-icon"
                                @click="close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                        <div class="modal-body modal__scroll-content">
                            <slot name="body"/>
                        </div>

                        <div class="modal-footer">
                            <slot
                                :close="close"
                                name="footer"/>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
export default {
    beforeCreate() {
        document.body.classList.add('open-modal-no-scroll')
    },
    destroyed() {
        document.body.classList.remove('open-modal-no-scroll')
    },
    methods: {
        close() {
            this.$emit('close');
        },
    },
};
</script>
