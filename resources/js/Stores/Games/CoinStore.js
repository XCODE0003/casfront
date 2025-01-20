import { defineStore } from 'pinia'
import HttpApi from "@/Services/HttpApi.js";
import { useToast } from 'vue-toastification'
import { useWalletStore } from "@/Stores/Wallet";

const _toast = useToast()

export const useCoinStore = defineStore('coin', {
    state: () => ({
        game: null,
        game_settings: {
            bet: 1,
            coin: null,
        },
        isLoading: true,
        activeGame: false,
        animationFunction: null,
    }),

    actions: {
        addBet() {
            const currentBet = parseFloat(this.game_settings.bet.toFixed(1));

            if (currentBet < 1) {
                this.game_settings.bet = parseFloat((currentBet + 0.1).toFixed(1));
            } else {
                this.game_settings.bet = parseFloat((currentBet + 0.5).toFixed(1));
            }
        },

        subtractBet() {
            const currentBet = parseFloat(this.game_settings.bet.toFixed(1));

            if (currentBet <= 1) {
                const newBet = parseFloat((currentBet - 0.1).toFixed(1));
                this.game_settings.bet = Math.max(0, newBet);
            } else {
                this.game_settings.bet = parseFloat((currentBet - 0.5).toFixed(1));
            }
        },

        setCoin(coin) {
            this.game_settings.coin = coin;
        },

        setAnimationFunction(fn) {
            this.animationFunction = fn;
        },

        async flipCoin() {
            if (this.activeGame || !this.game_settings.coin || !this.animationFunction) return;

            this.activeGame = true;

            try {
                useWalletStore().fetchWallet();
                const response = await HttpApi.post('games/coin/start', {
                    bet: this.game_settings.bet,
                    coin: this.game_settings.coin,
                });
                const result = response.data.coin_result;

                await this.animationFunction(result);

                useWalletStore().fetchWallet();
            } catch (error) {
                console.log(error);
            } finally {
                this.activeGame = false;
            }
        }
    },

    getters: {
        getWallet: (state) => state.wallet,
        getIsLoading: (state) => state.isLoading,

    },
})
