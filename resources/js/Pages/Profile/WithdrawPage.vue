<script setup>
import { useSettingStore } from "@/Stores/SettingStore";
import { onMounted } from "vue";
import { useToast } from "vue-toastification";
import { useRoute } from "vue-router";
import WalletSideMenu from "@/Pages/Profile/Components/WalletSideMenu.vue";
import { ref } from "vue";
import { useWalletStore } from "@/Stores/Wallet";
import BaseLayout from "@/Layouts/BaseLayout.vue";
import { useAuthStore } from "@/Stores/Auth.js";

const walletStore = useWalletStore();

onMounted(() => {
    walletStore.fetchWallet();
});

function setPercentAmount(percent) {
    withdraw_deposit.value.amount = (percent / 100) * walletStore.wallet?.balance;
}

function setMinAmount() {
    withdraw_deposit.value.amount = setting.min_withdrawal;
}

function setMaxAmount() {
    withdraw_deposit.value.amount = setting.max_withdrawal;
}
const authStore = useAuthStore();
const user = authStore.user;
const selectedCurrency = ref('BTC');

const withdraw_deposit = ref({
    name: '',
    bank_info: '',
    amount: '',
    type: 'bank',
    currency: '',
    symbol: '',
    accept_terms: false,
});
const setting = {
    min_withdrawal: 10,
    max_withdrawal: 1000000,

};
</script>
<template>
    <BaseLayout>
        <div class="md:w-4/6 2xl:w-4/6 mx-auto p-4 mt-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-1 hidden md:block">
                    <WalletSideMenu />
                </div>
                <div class="col-span-2 relative">
                    <div :class="user.is_verification ? '' : 'backdrop-blur'" class="absolute top-0 right-0 w-full h-full flex justify-center items-center rounded-xl ">
                        <div class="flex flex-col items-center">
                            <h1 class="dark:text-white text-black  text-2xl font-bold">Withdrawals are disabled</h1>
                            <p class="dark:text-white text-black text-sm">Please complete verification to enable withdrawals</p>
                            <RouterLink to="/profile/verification" class="ui-button-blue mt-2 rounded-lg flex items-center justify-center">Complete verification</RouterLink>
                        </div>
                    </div>
                    <div
                       
                       
                        class="flex flex-col w-full bg-gray-200 shadow-lg border border-gray-300 dark:border-gray-700 hover:bg-gray-300/20 dark:bg-gray-700 p-4 rounded"
                    >
                       
                     
                           <div class="mt-5">
                            <select name="" id="" v-model="selectedCurrency" class="input">
                                    <option value="BTC">BTC</option>
                                    <option value="ETH">ETH</option>
                                    <option value="USDT">USDT TRC20</option>
                                </select>
                           </div>
                            <div class="mt-5">
                               
                                <div class="dark:text-gray-400 mb-3 text-sm">
                                    <label for=""
                                        >
                                        Enter address withdrawal
                                        </label
                                    >
                                    <input
                                        type="text"
                                        class="input"
                                        placeholder="Enter address withdrawal"
                                    />
                                </div>

                                

                                <div class="dark:text-gray-400 mb-3 mt-4">
                                 
                                    <div class="flex bg-white dark:bg-gray-900">
                                        <input
                                            type="text"
                                            class="input"
                                            v-model="withdraw_deposit.amount"
                                            :min="setting.min_withdrawal"
                                            :max="setting.max_withdrawal"
                                            placeholder=""
                                            required
                                        />
                                        <div class="flex items-center pr-1">
                                            <div
                                                class="inline-flex shadow-sm"
                                                role="group"
                                            >
                                                <button
                                                    @click.prevent="
                                                        setMinAmount
                                                    "
                                                    type="button"
                                                    class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                >
                                                    min
                                                </button>
                                                <button
                                                    @click.prevent="
                                                        setPercentAmount(50)
                                                    "
                                                    type="button"
                                                    class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border-t border-b border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                >
                                                    50%
                                                </button>
                                                <button
                                                    @click.prevent="
                                                        setPercentAmount(100)
                                                    "
                                                    type="button"
                                                    class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                >
                                                    100%
                                                </button>
                                                <button
                                                    @click.prevent="
                                                        setMaxAmount
                                                    "
                                                    type="button"
                                                    class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                >
                                                    max
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div
                                        class="flex justify-between mt-2 text-sm"
                                    >
                                        <p>
                                            {{ $t("Available") }}:
                                            {{ walletStore.wallet?.balance }}
                                        </p>

                                      
                                    </div>
                                </div>

                                <div class="mb-3 mt-5">
                                    <div class="flex items-center mb-4">
                                        <input
                                            id="accept_terms_checkbox"
                                            v-model="
                                                withdraw_deposit.accept_terms
                                            "
                                            type="checkbox"
                                            value=""
                                            class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 dark:focus:ring-green-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                        />
                                        <label
                                            for="accept_terms_checkbox"
                                            class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-400"
                                        >
                                            {{
                                                $t(
                                                    "I accept the transfer terms"
                                                )
                                            }}
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-5 w-full flex items-center justify-center"
                            >
                                <button
                                    type="submit"
                                    class="ui-button-blue w-full"
                                >
                                    <span
                                        class="uppercase font-semibold text-sm"
                                        >{{ $t("Request withdrawal") }}</span
                                    >
                                </button>
                            </div>
                 
                    
                    
                    </div>
              
                </div>
            </div>
        </div>
    </BaseLayout>
</template>
<!-- 
<script>
import { RouterLink, useRouter } from "vue-router";
import BaseLayout from "@/Layouts/BaseLayout.vue";
import WalletSideMenu from "@/Pages/Profile/Components/WalletSideMenu.vue";
import HttpApi from "@/Services/HttpApi.js";
import { useToast } from "vue-toastification";
import { useSettingStore } from "@/Stores/SettingStore.js";

export default {
    props: [],
    components: { WalletSideMenu, BaseLayout, RouterLink },
    data() {
        return {
            isLoading: false,
            setting: null,
            wallet: null,
            withdraw: {
                name: "",
                pix_key: "",
                pix_type: "",
                amount: "",
                type: "pix",
                currency: "",
                symbol: "",
                accept_terms: false,
            },
            withdraw_deposit: {
                name: "",
                bank_info: "",
                amount: "",
                type: "bank",
                currency: "",
                symbol: "",
                accept_terms: false,
            },
        };
    },
    setup(props) {
        const router = useRouter();
        return {
            router,
        };
    },
    computed: {},
    mounted() {},
    methods: {
        setMinAmount: function () {
            this.withdraw.amount = this.setting.min_withdrawal;
        },
        setMaxAmount: function () {
            this.withdraw.amount = this.setting.max_withdrawal;
        },
        setPercentAmount: function (percent) {
            this.withdraw.amount =
                (percent / 100) * this.wallet.balance_withdrawal;
        },
        getWallet: function () {
            const _this = this;
            const _toast = useToast();
            _this.isLoadingWallet = true;

            HttpApi.get("profile/wallet")
                .then((response) => {
                    _this.wallet = response.data.wallet;

                    _this.withdraw.currency = response.data.wallet.currency;
                    _this.withdraw.symbol = response.data.wallet.symbol;

                    _this.withdraw_deposit.currency =
                        response.data.wallet.currency;
                    _this.withdraw_deposit.symbol = response.data.wallet.symbol;

                    _this.isLoadingWallet = false;
                })
                .catch((error) => {
                    const _this = this;
                    Object.entries(
                        JSON.parse(error.request.responseText)
                    ).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoadingWallet = false;
                });
        },
        getSetting: function () {
            const _this = this;
            const settingStore = useSettingStore();
            const settingData = settingStore.setting;

            if (settingData) {
                _this.setting = settingData;
                _this.withdraw.amount = settingData.min_withdrawal;
                _this.withdraw_deposit.amount = settingData.min_withdrawal;
            }

            _this.isLoading = false;
        },
        submitWithdrawBank: function (event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoading = true;

            HttpApi.post("wallet/withdraw/request", _this.withdraw_deposit)
                .then((response) => {
                    _this.isLoading = false;
                    _this.withdraw_deposit = {
                        name: "",
                        bank_info: "",
                        amount: "",
                        type: "",
                        accept_terms: false,
                    };

                    _this.router.push({ name: "profileTransactions" });
                    _toast.success(response.data.message);
                })
                .catch((error) => {
                    Object.entries(
                        JSON.parse(error.request.responseText)
                    ).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
        submitWithdraw: function (event) {
            const _this = this;
            const _toast = useToast();
            _this.isLoading = true;

            HttpApi.post("wallet/withdraw/request", _this.withdraw)
                .then((response) => {
                    _this.isLoading = false;
                    _this.withdraw = {
                        name: "",
                        pix_key: "",
                        pix_type: "",
                        amount: "",
                        type: "",
                        accept_terms: false,
                    };

                    _this.router.push({ name: "profileTransactions" });
                    _toast.success(response.data.message);
                })
                .catch((error) => {
                    Object.entries(
                        JSON.parse(error.request.responseText)
                    ).forEach(([key, value]) => {
                        _toast.error(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
    },
    created() {
        this.getWallet();
        this.getSetting();
    },
    watch: {},
};
</script> -->

<style scoped></style>
