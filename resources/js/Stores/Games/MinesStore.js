import { defineStore } from 'pinia'
import HttpApi from "@/Services/HttpApi.js";
import { useToast } from 'vue-toastification'
import { useWalletStore } from "@/Stores/Wallet";

const _toast = useToast()

export const useMinesStore = defineStore('mines', {
    state: () => ({
        game: null,
        game_settings: {
            bet: 0,
            mines: 3,
        },
        next_win: 0,
        isLoading: true,
        result: Array(25).fill().map(() => ({ picked: false, mine: false })),
        activeGame: false,

    }),

    actions: {
        initGame() {
            HttpApi.get('/games/mines/init').then((response) => {
                if (response.data.game !== null) {
                    this.game = response.data.game;
                    this.result = this.game.result;
                    this.next_win = this.game.next_win;
                    this.activeGame = true;
                    this.game_settings.bet = this.game.bet;
                    this.game_settings.mines = this.game.mines;

                }
            });
        },
        stopGame() {
            HttpApi.post('/games/mines/stop');
            this.activeGame = false;
            this.game = null;
            this.result = Array(25).fill().map(() => ({ picked: false, mine: false }));
            this.next_win = 0;
            const walletStore = useWalletStore();
            walletStore.fetchWallet();
        },
        startGame() {
            if (this.game_settings.bet <= 0) {
                _toast.error('Bet must be greater than 0');
                return;
            }
            HttpApi.post('/games/mines/start', {
                bet: this.game_settings.bet,
                mines: this.game_settings.mines,
            }).then((response) => {
                const walletStore = useWalletStore();
                if (response.data.success) {
                    this.game = response.data.game;
                    this.activeGame = true;
                    this.next_win = response.data.next_win;
                    this.result = response.data.result;
                    walletStore.fetchWallet();
                }
                else {
                    _toast.error(`${response.data.message}`);
                }
            });
        },
        pick(id) {
            HttpApi.post('/games/mines/pick', { id }).then((response) => {
                if (response.data.success) {
                    this.result = Array.isArray(response.data.game)
                        ? response.data.game
                        : Object.values(response.data.game);
                    this.next_win = response.data.next_win;
                } else {
                    if (response.data.result) {
                        this.result = Array.isArray(response.data.result)
                            ? response.data.result
                            : Object.values(response.data.result);
                    }
                    
                    const walletStore = useWalletStore();
                    walletStore.fetchWallet();
                    
                    setTimeout(() => {
                        this.activeGame = false;
                        this.game = null;
                        this.result = Array(25).fill().map(() => ({ picked: false, mine: false }));
                    }, 2000);
                }
            }).catch(error => {
                console.error('Error in pick action:', error);
                this.result = Array(25).fill().map(() => ({ picked: false, mine: false }));
                this.activeGame = false;
                this.game = null;
            });
        },
        addMines() {
            this.playAudioClick();
            if (this.game_settings.mines === 24) {
                return;
            }
            this.game_settings.mines += 1;
        },
        removeMines() {
            this.playAudioClick();
            if (this.game_settings.mines === 1) {
                return;
            }
            this.game_settings.mines -= 1;
        },

        playAudioClick() {
            const audio = new Audio('/assets/sounds/click.mp3');
            audio.play();
        },
    },

    getters: {
        getWallet: (state) => state.wallet,
        getIsLoading: (state) => state.isLoading,
        getGameSettings: (state) => state.game_settings,
        getResult: (state) => (index) => {
            if (!state.result?.[index]) return null;
            
            if (state.result.some(cell => cell.picked && cell.mine)) {
                if (state.result[index].mine) return false;
                if (state.result[index].picked) return true;
                return null;
            }
            
            return state.result[index].picked ? !state.result[index].mine : null;
        },
        getCellClass: (state) => (index) => {
            const cell = state.result?.[index];
            if (!cell) return '';
            
            if (state.result.some(c => c.picked && c.mine)) {
                if (cell.mine) return '_active _lose';
                if (cell.picked) return '_active _win';
                return '';
            }
            
            if (!cell.picked) return '';
            return cell.mine ? '_active _lose' : '_active _win';
        },
    },
})
