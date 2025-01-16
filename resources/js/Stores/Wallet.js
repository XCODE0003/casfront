import { defineStore } from 'pinia'
import HttpApi from "@/Services/HttpApi.js";

export const useWalletStore = defineStore('walletStore', {
    state: () => ({
        wallet: null,
        isLoading: true,
        updateInterval: null
    }),

    actions: {
        async fetchWallet() {
            try {
                const response = await HttpApi.get('profile/wallet');
                this.wallet = response.data.wallet;
                this.isLoading = false;
            } catch (error) {
                console.error('Ошибка при получении баланса:', error);
                this.isLoading = false;
            }
        },

        startAutoUpdate() {
            this.stopAutoUpdate(); // Очищаем предыдущий интервал если есть
            this.updateInterval = setInterval(() => {
                this.fetchWallet();
            }, 5000);
        },

        stopAutoUpdate() {
            if (this.updateInterval) {
                clearInterval(this.updateInterval);
                this.updateInterval = null;
            }
        }
    },

    getters: {
        getWallet: (state) => state.wallet,
        getIsLoading: (state) => state.isLoading
    }
})
