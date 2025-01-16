<template>
    <div class="footer pb-32 md:pb-5 mt-5 footer-color p-4 md:p-8">
        <div class="md:w-4/6 2xl:w-4/6 m-auto">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <!-- <div class="col-span-2 md:col-span-1 flex flex-col">
                 
                    <div v-if="setting">
                        <div class="">
                            {{ setting.software_description }}
                        </div>

                        <div class="flex  mt-5">
                            <div class="flex pl-2">
                                <a href="" class="text-3xl text-gray-400">
                                    <i class="fa-brands fa-instagram"></i>
                                </a>
                            </div>
                            <div class="flex pl-2">
                                <a href="" class="text-3xl text-gray-400">

                                </a>
                            </div>
                            <div class="flex pl-2">
                                <a href="" class="text-3xl text-gray-400">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div class="footer-list">
                    <h3 class="text-base md:text-lg text-gray-600 dark:text-white font-bold">USEFUL LINKS</h3>
                    <ul class="list-none mt-5">
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]">
                            <a href="">Payment Methods</a>
                        </li>
                    </ul>
                </div>
                <div class="footer-list">
                    <h3 class="text-base md:text-lg text-gray-600 dark:text-white font-bold">ABOUT US</h3>
                    <ul class="list-none mt-5">
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]">
                            <a href="">Terms of Service</a>
                        </li>
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]">
                            <a href="">Privacy Policy</a>
                        </li>
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]">
                            <a href="">Bonus Terms</a>
                        </li>
                        <li class="transition duration-700 hover:dark:text-white text-gray-600 dark:text-gray-400 hover:text-gray-800 text-[12px]">
                            <a href="">Welcome Bonus</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <hr class="border-t border-gray-200 dark:border-gray-600 mt-5">

        <div v-if="setting" class="mt-5 flex flex-col justify-start ">
            <p class="text-[12px] w-full">© {{ year }} {{ setting.software_name }} {{ $t('All rights reserved') }}.</p>
            <p class="flex text-[10px] md:text-[12px]  w-full">
<!--                <strong>❤️ Developed by</strong>-->
<!--                <a href="https://www.instagram.com/victormsalatiel/" target="_blank" class="ml-2 dark:text-gray-500 dark:hover:text-white transition duration-700 cursor-pointer">-->
<!--                    Victor Salatiel-->
<!--                </a>-->
            </p>
        </div>
    </div>

    <ChatWidget />
</template>

<script>
    import HttpApi from "@/Services/HttpApi.js";
    import ChatWidget from "@/Components/Widgets/ChatWidget.vue";
    import {useSettingStore} from "@/Stores/SettingStore.js";

    export default {
        props: [],
        components: {ChatWidget},
        data() {
            return {
                isLoading: false,
                year: new Date().getFullYear(),
                setting: null,
            }
        },
        setup(props) {


            return {};
        },
        computed: {

        },
        mounted() {

        },
        methods: {
            getSetting: function() {
                const _this = this;
                const settingStore = useSettingStore();
                const settingData = settingStore.setting;

                if(settingData) {
                    _this.setting = settingData;
                }
            },
        },
        created() {
            this.getSetting();
        },
        watch: {

        },
    };
</script>

<style scoped>

</style>
