import { defineStore } from 'pinia'
import HttpApi from "@/Services/HttpApi.js";
import { useToast } from 'vue-toastification'
import { useWalletStore } from "@/Stores/Wallet";

const _toast = useToast()

export const useTowerStore = defineStore('tower', {
    state: () => ({
        game: null,
        game_settings: {
            bet: 0,
        },
        next_win: 0,
        isLoading: true,
        currentRow: 7,
        result: Array(8).fill().map(() =>
            Array(4).fill().map(() => ({
                position: 0,
                picked: false,
                bomb: false
            }))
        ),
        activeGame: false,
    }),

    actions: {
        initGame() {
            HttpApi.get('/games/tower/init').then((response) => {
                if (response.data.game !== null) {
                    this.game = response.data.game
                    this.result = this.game.result
                    this.next_win = response.data.next_win
                    this.activeGame = true
                    this.game_settings.bet = this.game.bet
                    this.currentRow = 7
                }
            })
        },

        stopGame() {
            HttpApi.post('/games/tower/stop')
            this.activeGame = false
            this.game = null
            this.currentRow = 7
            this.result = Array(8).fill().map(() =>
                Array(4).fill().map(() => ({
                    position: 0,
                    picked: false,
                    bomb: false
                }))
            )
            this.next_win = 0
            const walletStore = useWalletStore()
            walletStore.fetchWallet()
        },

        startGame() {
            if (this.game_settings.bet <= 0) {
                _toast.error('Ставка должна быть больше 0')
                return
            }

            HttpApi.post('/games/tower/start', {
                bet: this.game_settings.bet,
            }).then((response) => {
                const walletStore = useWalletStore()
                if (response.data.success) {
                    this.game = response.data.game
                    this.activeGame = true
                    this.next_win = response.data.next_win
                    this.result = response.data.result
                    this.currentRow = 7
                    walletStore.fetchWallet()
                } else {
                    _toast.error(`${response.data.message}`)
                }
            })
        },

        pick(row, position) {
            if (row !== this.currentRow) return

            HttpApi.post('/games/tower/pick', { row, position }).then((response) => {
                if (response.data.success) {
                    this.result = response.data.result
                    this.next_win = response.data.next_win
                    this.currentRow--
                    if (this.currentRow === 0) {
                        setTimeout(() => {
                            this.game = null
                            this.currentRow = 7
                            this.result = Array(8).fill().map(() =>
                                Array(4).fill().map(() => ({
                                    position: 0,
                                    picked: false,
                                    bomb: false
                                }))
                            )
                            const walletStore = useWalletStore()
                            walletStore.fetchWallet()
                            this.activeGame = false
                        }, 1000)
                    }
                } else {
                    this.result = response.data.result
                    this.game = response.data.game
                    setTimeout(() => {
                        this.game = null
                        this.currentRow = 7
                        this.result = Array(8).fill().map(() =>
                            Array(4).fill().map(() => ({
                                position: 0,
                                picked: false,
                                bomb: false
                            }))
                        )
                        this.activeGame = false
                    }, 1000)
                }
            })
        },

        playAudioClick() {
            const audio = new Audio('/assets/sounds/click.mp3')
            audio.play()
        },


    },

    getters: {
        getWallet: (state) => state.wallet,
        getIsLoading: (state) => state.isLoading,
        getGameSettings: (state) => state.game_settings,
        getCurrentRow: (state) => state.currentRow,
        getResult: (state) => (row, position) => {
            if (!state.result || !state.result[row] || !state.result[row][position]) {
                return null;
            }
            const cell = state.result[row][position];
            if (cell.picked) {
                return cell.bomb ? false : true;
            }
            return null;
        },
        getCellClass: (state) => (row, position) => {
            if (!state.result || !state.result[row] || !state.result[row][position]) {
                return '';
            }
            const cell = state.result[row][position];
            if (!cell.picked) return '';
            return cell.bomb ? '_active _lose' : '_active _win';
        },
        isRowPlayable: (state) => (rowIndex) => {
            return state.activeGame && rowIndex === state.currentRow;
        }
    },
})
