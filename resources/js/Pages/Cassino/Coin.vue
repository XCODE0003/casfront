<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/Stores/Auth.js";
import GameLayout from "@/Layouts/GameLayout.vue";
import HttpApi from "@/Services/HttpApi.js";
import { useCoinStore } from "@/Stores/Games/CoinStore.js";

const coinStore = useCoinStore()
const coinRef = ref(null);

const animateCoinFlip = (result) => {
    const coin = coinRef.value;

    coin.style.transition = 'none';
    coin.style.transform = 'rotateY(0)';

    void coin.offsetHeight;

    const rotations = 5;
    const finalRotation = result === 'heads' ? 0 : 180;
    const totalDegrees = (rotations * 360) + finalRotation;

    coin.style.transition = 'transform 3s ease-out';
    coin.style.transform = `rotateY(${totalDegrees}deg)`;

    return new Promise((resolve) => {
        setTimeout(() => {
            resolve(result);
        }, 3000);
    });
};

onMounted(() => {
    coinStore.setAnimationFunction(animateCoinFlip);
});

</script>

<template>
    <GameLayout>
        <div style="max-width: 600px;" class="mx-auto px-2 lg:px-4 py-2 lg:py-6 relative">
            <div class="bg-gray-300/20 dark:bg-gray-700 rounded flex justify-between px-4 py-2">
                <div class="flex items-center justify-center gap-3">
                    <a href="">LOGO</a>
                    <i class="fa-regular fa-angle-right text-gray-500"></i>
                    <p class="text-gray-500">Mines</p>
                </div>
                <div></div>
            </div>

            <div style="border-radius: 24px; " class="py-10 px-24 game-screen">
                <div class="flex flex-col gap-12 h-full">
                    <div class="p-2 rounded-full coin-wrapper  w-fit h-full mx-auto">
                        <div class="coin" ref="coinRef">
                            <div class="coin-front">
                                <img :src="`/assets/images/heads.svg`" class="w-48 h-48" alt="">
                            </div>
                            <div class="coin-back">
                                <img :src="`/assets/images/tails.svg`" class="w-48 h-48" alt="">
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 w-full items-center">
                        <div class="grid grid-cols-2 items-center w-full  gap-2">
                            <div :class="{ 'active': coinStore.game_settings.coin === 'heads' }"
                                @click="coinStore.setCoin('heads')"
                                class="py-4 coin_select justify-center bg-gray-600/20  rounded-xl flex items-center gap-2">
                                <img :src="`/assets/images/heads.svg`" class="w-8 h-8" alt="" srcset="">
                                <p class="text-white text-xl font-bold">Heads</p>
                            </div>
                            <div :class="{ 'active': coinStore.game_settings.coin === 'tails' }"
                                @click="coinStore.setCoin('tails')"
                                class="py-4 coin_select justify-center  bg-gray-600/20  rounded-xl flex items-center gap-2">
                                <img :src="`/assets/images/tails.svg`" class="w-8 h-8" alt="" srcset="">
                                <p class="text-white text-xl font-bold">Tails</p>
                            </div>
                        </div>
                        <div class="bet-panel">
                            <div class="bet-panel__actions">
                                <div class="flex flex-wrap flex-row mobile:flex-col mobile:gap-y-3">
                                    <div class="basis-2/3">
                                        <div class="input-state-panel base-input">
                                            <div class="flex w-full"><button type="button" id="decrease_bet_btn"
                                                    class="bet-panel__control" @click="coinStore.subtractBet()">
                                                    <div class="bet-panel__control-border right"></div><span
                                                        class="bet-panel__control-icon">-</span>
                                                </button>
                                                <div class="app-input flex flex-1 justify-center"><input
                                                        id="amount_field" maxlength="8"
                                                        v-model="coinStore.game_settings.bet"
                                                        class="border-none bg-transparent outline-none"
                                                        autocomplete="off" style="width: 70px;">
                                                    <div class="ml-1 my-auto">
                                                        <div id="currentBetCurrency" class="app-input__currency">$</div>
                                                    </div>
                                                </div><button type="button" id="increase_bet_btn"
                                                    class="bet-panel__control" @click="coinStore.addBet()">
                                                    <div class="bet-panel__control-border left"></div><span
                                                        class="bet-panel__control-icon">+</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="basis-1/3 pl-2 mobile:pl-0">
                                        <button type="button" @click="coinStore.flipCoin()"
                                            :disabled="coinStore.activeGame || coinStore.game_settings.bet === 0 || coinStore.game_settings.coin === null"
                                            class="hidden md:block w-full ui-button-blue mr-3 rounded">Flip</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>




        </div>
    </GameLayout>
</template>

<style>
.coin-wrapper {
    position: relative;
}

.coin-wrapper::before {
    background: linear-gradient(90deg, #3d445c, rgba(61, 68, 92, 0), #3d445c);
    border-radius: 120px;
    content: "";
    inset: 0;
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    padding: 3px;
    position: absolute;
}

button:disabled {
    cursor: not-allowed;

    opacity: 0.3;
}

button {
    transition: all 0.3s ease;
}

.game-screen {
    margin-top: 30px;
    width: 100%;
    border-radius: 24px;
    padding: 24px;
    min-height: 500px;
}

.play-button {
    padding: 1rem 3rem;
    background: #24abf8;
    box-shadow: inset 0px 3px 8px #6dcaff;
    border-radius: 8px;
    color: #fff;
}

.count-button {
    padding: 0.3rem;
    background: #23274e;
    border-radius: 5px;
    font-weight: 400;
    font-size: 12px;
    text-align: center;
    color: #858cab;
}

.bid-button-block {
    font-family: 'Halvar Breitschrift', sans-serif;
    display: flex;
    flex-direction: row;
    justify-content: center;
    margin-bottom: 1.5rem;
    gap: 0.5rem;
    transition: all 0.3s;
    pointer-events: all;
}

.bid-button-block[type='button'] {
    pointer-events: all;
}

.bid-button-block.disabled {
    filter: brightness(0.3);
    pointer-events: none;
}

.bid-button__item {
    font-weight: 700;
    font-size: 14px;
    line-height: 16px;
    text-align: center;
    text-transform: uppercase;
    color: #ffffff;
    padding: 0.8rem;
    min-width: 210px;
    transition: all 0.3s;
    cursor: pointer;
    background: #1E2430;
}

@media (max-width: 768px) {
    .bid-button__item {
        min-width: 0;
    }
}

.bid-button__item:disabled {
    cursor: default;
    opacity: 0.3;
}

.bid-button__item:hover {
    opacity: 0.8;
}

.bid-button__item:active,
.bid-button__item:focus {
    padding: calc(0.8rem - 2px);
    background: linear-gradient(to right, #2A3145, #1D2439);
    border: 2px solid transparent;
}

.bid-button__item-row {
    display: flex;
    align-items: center;
    justify-content: center;
}

.bid-button__item-row__text {
    margin-left: 0.3rem;
}

.bid-button__item-row__icon {
    width: 40px;
    height: 40px;
}

.bid-button__item-row__icon svg {
    width: 100%;
    height: 100%;
}

.bid-button-head {
    border-radius: 12px 8px 8px 12px;
}

.bid-button-tail {
    border-radius: 8px 12px 12px 8px;
}

button.app-button {
    transition: background 0.3s ease, opacity 0.3s ease;
}

.coin_select {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.coin_select.active {
    border: 1px solid #24abf8;
}

button.app-button.big {
    padding: 5px;
    font-style: normal;
    font-weight: 400;
    font-size: 14px;
    line-height: 18px;
    text-align: center;
    border-radius: 8px;
}

button.app-button.big:hover,
button.app-button.big.btn-waiting,
button.app-button.big:focus {
    opacity: 0.8;
}

button.app-button.big:disabled {
    opacity: 0.3;
}

button.app-button.big.cashout:focus {
    opacity: 0.3;
}

button.app-button.medium {
    padding: 5px 3px;
    flex: 1;
    background: #2a3145;
    border-radius: 5px;
    font-style: normal;
    font-weight: 600;
    font-size: 12px;
    line-height: 17px;
    text-align: center;
    color: #97a3cb;
    text-transform: uppercase;
}

button.app-button.medium:hover {
    opacity: 0.8;
}

button.app-button.medium:focus {
    background: #1d2439;
    color: rgba(151, 163, 203, 0.3);
}

button.app-button.medium:disabled {
    background: #1d2439;
    color: rgba(151, 163, 203, 0.3);
}

button.app-button.small {
    background: #1E2430;
    border-radius: 8px;
    padding: 5px;
    mix-blend-mode: normal;
    color: #fafafa;
    font-style: normal;
    font-weight: 500;
    font-size: 13px;
    line-height: 18px;
}

button.app-button.small svg {
    fill: #858cab;
}

button.app-button.small:hover {
    opacity: 0.8;
}

button.app-button.small:enabled:active {
    opacity: 0.3;
}

.game-screen .game-full {
    width: 100%;
    min-height: 650px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.bet-panel {
    padding: 15px;
    width: auto;
    min-width: 510px;
    background: #1E2430;
    border-radius: 16px;
}

@media (max-width: 768px) {
    .bet-panel {
        min-width: 0;
        width: 100%;
    }
}

.bet-panel__actions .input-state-panel {
    display: flex;
    font-size: 18px;
    line-height: 24px;
    font-weight: 500;
    color: #f3f3f3;
    height: 44px;
}

.bet-panel__actions .input-state-panel .app-input {
    padding: 0.7rem 0;
}

.bet-panel__actions .input-state-panel .app-input input {
    width: 100%;
    text-align: right;
    font-weight: 500;
    font-size: 20px;
    line-height: 24px;
    margin-right: 2%;
    -moz-appearance: textfield;
}

.bet-panel__actions .input-state-panel .app-input input::-webkit-outer-spin-button,
.bet-panel__actions .input-state-panel .app-input input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.bet-panel__actions .input-state-panel .app-input input::placeholder {
    color: #fff;
}

.bet-panel__actions .input-state-panel .app-input input::selection {
    background: #d9d9d9;
}

.bet-panel__actions .input-state-panel .app-input input::-moz-selection {
    background: #d9d9d9;
}

.bet-panel__actions .input-state-panel .app-input__currency {
    text-align: left;
    font-weight: 500;
    color: rgba(255, 255, 255, 0.25);
}

.bet-panel__actions .input-state-panel .state {
    text-align: center;
}

.bet-panel__actions .input-state-panel .state.win-lose {
    height: 100%;
}

.bet-panel__actions .input-state-panel .state.win-lose .game-state-animate {
    padding: 0.1rem;
    display: flex;
    width: 100%;
    justify-content: center;
    align-items: center;
    height: 100%;
}

@media (max-width: 768px) {
    .bet-panel__actions .input-state-panel .state.win-lose .game-state-animate {
        font-size: 15px;
        line-height: 14px;
    }
}

.bet-panel__actions .input-state-panel .state.win-lose.win {
    color: #7FBA7A;
}

.bet-panel__actions .input-state-panel .state.win-lose.lose {
    color: #858CAB;
}

.bet-panel__actions .input-state-panel .state.rounds .game-state-animate {
    padding: 0.6rem 0.7rem;
}

.bet-panel__actions .input-state-panel .state.rounds .round-title {
    line-height: 17px;
}

.bet-panel__actions .input-state-panel .state.rounds .circles {
    display: flex;
    justify-content: center;
    margin-top: 0.4rem;
}

.bet-panel__actions .input-state-panel .state.rounds .circles>.circle {
    margin: 0 0.4rem;
    height: 4px;
    width: 4px;
    border-radius: 100%;
    background: #858CAB;
}

.bet-panel__actions .input-state-panel .state.rounds .circles>.circle.completed {
    background: #7FBA7A;
}

.bet-panel__actions .input-state-panel .state.amount.session-bet-amount {
    padding: 4px 10px;
}

.bet-panel__actions .input-state-panel .state.amount .game-state-animate {
    padding: 4px 10px;
}

.bet-panel__actions .input-state-panel .state.amount .bet-amount {
    font-weight: 700;
}

.bet-panel__actions .input-state-panel .state.amount .bet-amount>span,
.bet-panel__actions .input-state-panel .state.amount .bet-amount>.currency-label {
    font-size: 16px;
    line-height: 19px;
}

.bet-panel__actions .input-state-panel .state.amount .bet-amount .currency-label {
    margin-left: 0.3rem;
    color: rgba(255, 255, 255, 0.25);
}

.bet-panel__actions .input-state-panel .state.amount .bet-amount-label {
    font-family: 'FS Elliot Pro';
    font-weight: 400;
    font-size: 12px;
    line-height: 17px;
    color: rgba(133, 140, 171, 0.6);
}

.bet-panel__quick-bids {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 0.7rem;
}

.bet-panel__auto-betting {
    border-top: 1px solid #252b40;
    padding-top: 0.8rem;
    margin-top: 0.8rem;
    font-family: 'FS Elliot Pro';
    font-size: 12px;
    line-height: 17px;
}

.bet-panel__auto-betting__switcher {
    font-weight: 700;
    color: #ffffff;
}

.bet-panel__auto-betting__switcher .checkbox {
    position: relative;
    background: #0a0f1e;
    border-radius: 8px;
    height: 28px;
    width: 50px;
    padding: 2px;
}

.bet-panel__auto-betting__switcher .checkbox:after {
    position: absolute;
    content: '';
    background: #2a3145;
    mix-blend-mode: normal;
    border-radius: 6px;
    height: 24px;
    width: 24px;
    left: 2px;
    top: 2px;
    transition: background 0.1s, transform 0.3s;
}

.bet-panel__auto-betting__switcher .checkbox.active:after {
    background: linear-gradient(93.73deg, #108de7 0%, #0855c4 100%);
    box-shadow: 0px 2.07008px 8.28032px rgba(0, 0, 0, 0.15);
    transform: translateX(22px);
}

.bet-panel__auto-betting__switcher .checkbox input[type='checkbox'] {
    position: absolute;
    display: block;
    opacity: 0;
    cursor: pointer;
    height: 100%;
    width: 100%;
    z-index: 2;
}

.bet-panel__auto-betting__field {
    padding: 5px 12px;
    font-weight: 400;
    color: #97a3cb;
}

.bet-panel__auto-betting__field .infinity {
    position: relative;
}

.bet-panel__auto-betting__field .infinity::after,
.bet-panel__auto-betting__field .infinity::before {
    content: "";
    box-sizing: content-box;
    position: absolute;
    top: 4px;
    right: 10px;
    width: 5px;
    height: 5px;
    border: 2px solid #6c6f78;
    border-radius: 50px 50px 0 50px;
    transform: rotate(-45deg);
}

.bet-panel__auto-betting__field .infinity::after {
    border-radius: 50px 50px 50px 0;
    transform: rotate(45deg);
    right: 0;
}

.bet-panel__auto-betting__field.active .infinity::after,
.bet-panel__auto-betting__field.active .infinity::before {
    border-color: #fff;
}

.bet-panel__auto-betting__field-input {
    color: #fff;
    opacity: 0.4;
    -moz-appearance: textfield;
}

.bet-panel__auto-betting__field-input::-webkit-outer-spin-button,
.bet-panel__auto-betting__field-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.bet-panel__auto-betting__field-label {
    opacity: 0.5;
}

.bet-panel__auto-betting__field.active .bet-panel__auto-betting__field-label,
.bet-panel__auto-betting__field.active .bet-panel__auto-betting__field-input {
    opacity: 1;
}

.bet-panel__control {
    position: relative;
    width: 44px;
    height: 44px;
    padding-left: 1.25rem;
    padding-right: 1.25rem;
    display: flex;
    justify-content: center;
    align-items: center;
}

.bet-panel__control:disabled {
    opacity: 0.3;
}

.bet-panel__control-border {
    position: absolute;
    width: 1px;
    height: 60%;
    background: #97A3CB;
    opacity: 0.2;
    border-radius: 0.75rem;
}

.bet-panel__control-border.left {
    left: 0;
}

.bet-panel__control-border.right {
    right: 0;
}

.bet-panel__control-icon {
    color: #97A3CB;
}

.bet-panel__control-iconBG {
    color: #97A3CB;
    font-size: 12px;
}

.bet-panel__control:hover .bet-panel__control-icon {
    opacity: 0.8;
}

.bet-panel__control:active .bet-panel__control-icon {
    opacity: 0.3;
}

.game-footer {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

.game-screen {
    background-image: linear-gradient(315deg, #16191d 0%, #252a2e 100%);
}


.tower .game-tile {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    height: 40px;
    width: 100%;
    text-align: center;
    line-height: 100px;
    background-image: linear-gradient(320.64deg, #17191c -42.09%, #32383e 167.71%);
    box-shadow: 0px 2px 3px rgba(10, 9, 9, 0.400896) !important;
    border-radius: 6px !important;
    aspect-ratio: 1;

}

.game-tile__inner {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease-in;
}

.game-tile__inner-possible-win {
    display: flex;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    align-items: center;
    justify-content: center;
    z-index: 5;
    font-weight: 600;
    font-size: 13px;
    line-height: 18px;
    color: rgba(255, 255, 255, 0.3);
    opacity: 0;
    transition: opacity 0.1s ease-in;
}

.game-tile._win .game-tile__inner-possible-win,
.game-tile._lose .game-tile__inner-possible-win {
    display: none;
}

.game-tiles._active .game-tile:not(._active) {
    cursor: pointer;
}

.game-tiles._active .game-tile:not(._active):hover:not(._loading) .game-tile__inner-possible-win {
    opacity: 1;
}

.game-tiles.locked .game-tile:before {
    content: "";
    background-image: url(/assets/images/lock.svg);
    background-size: contain;
    background-repeat: no-repeat;
    background-position: center;
    position: absolute;
    width: 24px;
    height: 24px;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.game-tiles._active .game-tile:not(._active):hover {
    background-image: linear-gradient(320.64deg,
            #17191c -42.09%,
            #444b51 167.71%);
    box-shadow: -4px -3px 11px rgba(10, 9, 9, 0.2),
        7px 7px 11px rgba(10, 9, 9, 0.25);
}

.game-tile._win::after,
.game-tile._lose::after {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 13px;
    background-repeat: no-repeat;
    background-position: 0 center;
}

.game-tile._win {
    background-image: radial-gradient(33.95% 33.95% at -8.16% 104.08%,
            rgba(148, 226, 251, 0.37) 5.7%,
            rgba(33, 38, 42, 0) 100%),
        radial-gradient(33.68% 33.68% at 113.68% 107.89%,
            rgba(148, 226, 251, 0.37) 0%,
            rgba(33, 38, 42, 0) 100%),
        radial-gradient(62.12% 48.25% at 49.48% -8.25%,
            #5cd9f5 0%,
            rgba(38, 42, 46, 0) 100%),
        linear-gradient(129.86deg,
            #242526 -1.52%,
            rgba(29, 33, 36, 0.21) 107.51%);
}

.game-tile._lose {
    background-image: radial-gradient(54.74% 54.74% at 116.84% 108.42%,
            rgba(221, 28, 28, 0.79) 0%,
            rgba(30, 33, 37, 0) 100%),
        radial-gradient(47.37% 40% at 9.47% -4.74%,
            rgba(242, 137, 41, 0.63) 0%,
            rgba(24, 26, 29, 0) 100%),
        linear-gradient(119.79deg, #3a3e41 -9.65%, rgba(15, 16, 18, 0.58) 113%);
}

.game-tile._lose._opened::after {
    background-repeat: no-repeat;
    background-image: url(./images/bomb-sprite.png);
    background-size: auto 100%;
    -webkit-animation: play-bomb 0.5s steps(9) forwards;
    animation: play-bomb 0.5s steps(9) forwards;
}

@media (max-width: 768px) {
    .game-tile._lose._opened::after {
        width: 62px;
        height: 62px;
        top: 50%;
        left: 50%;
        margin-left: 0;
        transform: translate(-50%, -50%);
    }
}

.game-tile._lose .game-tile__inner {
    transition-delay: 0.3s;
}

.game-tile._active {
    box-shadow: none;
}

.game-tile._active .game-tile__inner {
    width: calc(100% - 2px);
    height: calc(100% - 2px);
    background-image: linear-gradient(317.11deg,
            #0a0b0d -17.46%,
            #32383e 197.04%);
    box-shadow: inset -2px -2px 6px rgba(76, 79, 81, 0.26),
        inset 4px 4px 3px rgba(10, 9, 9, 0.49);
    border-radius: 12px;
    opacity: 1;
}

.game-tile._loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    background-image: url(./images/puff.svg);
    background-repeat: no-repeat;
    background-position: center;
    background-size: contain;
}

@media (max-width: 768px) {
    .game-tile._loading::after {
        width: 30px;
        height: 30px;
    }
}

.game-tile .diamond,
.game-tile .bomb {
    position: relative;
    z-index: 2;
    height: 80%;
    width: 80%;
    background-repeat: no-repeat;
    background-size: contain;
    background-position: center bottom;
}

.game-tile .diamond {
    background-image: url(/assets/images/diamond_shadow.svg);
}

.game-tile .bomb {
    background-image: url(/assets/images/bomb_shadow.svg);
}

@supports not (aspect-ratio: 1 / 1) {

    .game-tile .bomb::before,
    .game-tile .diamond::before,
    .game-tile__inner::before,
    .game-tile::before {
        float: left;
        padding-top: 100%;
        content: "";
    }

    .game-tile .bomb::after,
    .game-tile .diamond::after,
    .game-tile__inner::after,
    .game-tile::after {
        display: block;
        content: "";
        clear: both;
    }
}

@keyframes scale {
    0% {
        transform: scale(0.8);
    }

    100% {
        transform: scale(1);
    }
}

@-moz-keyframes play-bomb {
    100% {
        background-position: calc(100% + (100% / 8));
        opacity: 0;
    }
}

@-webkit-keyframes play-bomb {
    100% {
        background-position: calc(100% + (100% / 8));
        opacity: 0;
    }
}

@keyframes play-bomb {
    100% {
        background-position: calc(100% + (100% / 8));
        opacity: 0;
    }
}

.switcher {
    position: relative;
    padding: 1px;
    margin-right: 15px;
    border-radius: 16px;
    background: linear-gradient(98deg, #c8d5e1 -163%, rgba(0, 0, 0, 0) 102%);
    background: linear-gradient(135deg, #3b4249 0%, #22282d 100%);
    height: 28px;
}

.switcher__inner {
    position: relative;
    display: inline-block;
}

.switcher__inner.disabled {
    opacity: 0.4;
}

.switcher__inner:before,
.switcher__inner:after {
    content: "";
    position: absolute;
    right: -15px;
    top: 50%;
    transform: translateY(-50%);
    transition: all 0.3s linear;
}

.switcher__inner:before {
    width: 7px;
    height: 7px;
    border-radius: 3.5px;
    z-index: 1;
    background-image: linear-gradient(311deg, #5b666f 0%, #0b0f15 100%);
}

.switcher__inner:after {
    width: 5px;
    height: 5px;
    border-radius: 2.5px;
    right: -14px;
    z-index: 2;
    background-color: #272b30;
}

.switcher__inner.active:after {
    background-image: radial-gradient(circle at 88% 115%, #81df49, #8de15e 70%);
}

.switcher__input {
    height: 0;
    width: 0;
    display: none;
}

.switcher__input:checked+.switcher__label:after {
    left: calc(100% - 1px);
    transform: translateX(-100%);
}

.switcher__label {
    position: relative;
    display: inline-block;
    width: 44px;
    min-width: 44px !important;
    height: 26px;
    border-radius: 13px;
    box-shadow: inset 2px 2px 8px 0 rgba(4, 4, 5, 0.6);
    background-image: linear-gradient(135deg, #1c2024 0%, #1c2023 100%);
    transition: all 0.3s linear;
    cursor: pointer;
    text-indent: -9999px;
}

.switcher__label:after {
    content: "";
    position: absolute;
    top: 1px;
    left: 1px;
    width: 24px;
    height: 24px;
    border-radius: 14px;
    box-shadow: inset 0 -1px 1px 0 #181a1d;
    background-color: #272b30;
    transition: all 0.3s linear;
}

.btn-bg {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    transition: opacity 0.5s linear;
}

.input-text__wrapper {
    position: relative;
    text-align: center;
}

.input-text__wrapper .games-input__wrapper input,
.input-text__wrapper .games-input__wrapper .games-input__number {
    padding-left: 95px;
    padding-right: 95px;
    text-align: center;
}

.input-text__wrapper .games-input__wrapper label {
    left: 50%;
    transform: translateX(-50%);
}

.input-text__wrapper .input-button,
.input-text__wrapper .button {
    position: absolute;
    z-index: 3;
    top: 4px;
}

.input-text__wrapper .input-button:nth-child(1),
.input-text__wrapper .button:nth-child(1) {
    left: 4px;
}

.input-text__wrapper .input-button:nth-child(2),
.input-text__wrapper .button:nth-child(2) {
    left: 52px;
}

.input-text__wrapper .input-button:nth-child(3),
.input-text__wrapper .button:nth-child(3) {
    right: 52px;
}

.input-text__wrapper .input-button:nth-child(4),
.input-text__wrapper .button:nth-child(4) {
    right: 4px;
}

.games-input__wrapper {
    transition: opacity 0.5s cubic-bezier(0.075, 0.82, 0.165, 1);
}

.games-input__wrapper .error {
    display: none;
    position: absolute;
    bottom: -19px;
    left: 0;
    width: 100%;
    padding: 0 17px;
    font-size: 11px;
    line-height: 0.91;
    color: rgba(227, 113, 113, 0.64);
    text-align: left;
}

.input--warning .error {
    display: block;
}

.input--disabled {
    opacity: 0.4;
}

@media screen and (-webkit-min-device-pixel-ratio: 0) {
    @media (max-width: 767px) {

        select:focus,
        textarea:focus,
        input:focus {
            font-size: 16px;
        }
    }
}

@media (min-width: 1024px) {
    .input-text__wrapper {
        display: grid;
        grid-gap: 0;
        grid-template-columns: 1fr 1fr 1fr 1fr;
        grid-template-rows: max-content max-content;
        grid-template-areas:
            "input input input input"
            "btn1 btn2 btn3 btn4";
    }

    .input-text__wrapper .games-input__wrapper {
        grid-area: input;
    }

    .input-text__wrapper .games-input__wrapper input,
    .input-text__wrapper .games-input__wrapper .games-input__number {
        padding-left: 16px;
        padding-right: 16px;
        text-align: left;
    }

    .input-text__wrapper .games-input__wrapper label {
        left: 18px;
        transform: unset;
    }

    .input-text__wrapper .input-button,
    .input-text__wrapper .button {
        display: flex;
        width: auto;
        position: static;
        flex: 1;
        margin-top: -10px;
        margin-bottom: 20px;
        height: 28px;
    }

    .input-text__wrapper .input-button:nth-child(1),
    .input-text__wrapper .button:nth-child(1) {
        grid-area: btn1;
        margin-right: 5px;
    }

    .input-text__wrapper .input-button:nth-child(2),
    .input-text__wrapper .button:nth-child(2) {
        grid-area: btn2;
        margin-right: 5px;
        margin-left: 5px;
    }

    .input-text__wrapper .input-button:nth-child(3),
    .input-text__wrapper .button:nth-child(3) {
        grid-area: btn3;
        margin-right: 5px;
        margin-left: 5px;
    }

    .input-text__wrapper .input-button:nth-child(4),
    .input-text__wrapper .button:nth-child(4) {
        grid-area: btn4;
        margin-left: 5px;
    }
}

.btn-new {
    flex: 1;
    position: relative;
    height: 64px;
    padding: 3px;
    border-radius: 10px;
    box-shadow: -4px -2px 16px 0 rgba(195, 200, 205, 0.09),
        4px 4px 18px 0 rgba(0, 0, 0, 0.5);
    overflow: hidden;
    text-align: center;
    cursor: pointer;
    -webkit-backface-visibility: hidden;
    -moz-backface-visibility: hidden;
    -webkit-transform: translate3d(0, 0, 0);
    -moz-transform: translate3d(0, 0, 0);
}

.btn-new__border {
    height: 100%;
    position: relative;
    border-radius: 8px;
    padding: 1px;
    transition: opacity 0.5s linear;
    overflow: hidden;
    -webkit-backface-visibility: hidden;
    -moz-backface-visibility: hidden;
    -webkit-transform: translate3d(0, 0, 0);
    -moz-transform: translate3d(0, 0, 0);
}

.btn-new__inner {
    height: 100%;
    position: relative;
    overflow: hidden;
    border-radius: 7px;
    background-image: linear-gradient(320.64deg,
            #17191c -42.09%,
            #32383e 167.71%);
    -webkit-backface-visibility: hidden;
    -moz-backface-visibility: hidden;
    -webkit-transform: translate3d(0, 0, 0);
    -moz-transform: translate3d(0, 0, 0);
}

.btn-new__text {
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 100%;
    padding: 10px 0;
    position: relative;
    z-index: 2;
}

.btn-new__text ._small {
    margin-top: 2px;
}

.btn-new .text-wrapper {
    display: flex;
    flex-direction: column;
    justify-content: center;
    height: 28px;
}

.btn-new .text {
    font-size: 16px;
    font-weight: 800;
    color: #d6e1ef;
}

.btn-new._cashout ._bg1 {
    background-image: radial-gradient(48.81% 101.72% at 50% -10.34%,
            rgba(244, 157, 76, 0.243) 0%,
            rgba(255, 125, 5, 0.189) 0.01%,
            rgba(225, 155, 90, 0) 100%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._cashout ._bg11 {
    background-image: radial-gradient(48.81% 101.72% at 50% -10.34%,
            rgba(244, 157, 76, 0.243) 0%,
            rgba(255, 125, 5, 0.189) 0.01%,
            rgba(225, 155, 90, 0) 100%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._cashout ._bg2,
.btn-new._cashout ._bg22 {
    background-image: radial-gradient(155.77% 155.77% at 0% 125%,
            #ff9838 0%,
            rgba(27, 21, 15, 0) 100%),
        linear-gradient(94.46deg,
            rgba(225, 215, 200, 0.21) 45.13%,
            rgba(0, 0, 0, 0) 123.58%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._placebet ._bg1 {
    background-image: radial-gradient(48.81% 101.72% at 50% -10.34%,
            rgba(18, 194, 250, 0.9) 0%,
            rgba(82, 195, 243, 0.189) 0.01%,
            rgba(40, 45, 49, 0.108) 100%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._placebet ._bg11 {
    background-image: radial-gradient(50% 91.38% at 50% 0%,
            rgba(82, 195, 243, 0.189) 0.01%,
            rgba(18, 194, 250, 0.54) 0.02%,
            rgba(40, 45, 49, 0.18) 100%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._placebet ._bg2,
.btn-new._placebet ._bg22 {
    background-image: radial-gradient(148.08% 148.08% at 1.81% 132.69%,
            #66a1e5 0%,
            rgba(38, 74, 112, 0) 100%),
        linear-gradient(94.46deg,
            rgba(200, 213, 225, 0.21) 45.13%,
            rgba(0, 0, 0, 0) 123.58%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._cancel ._bg1 {
    background: radial-gradient(58.03% 100% at 50% 0%,
            rgba(255, 82, 92, 0.225) 0%,
            rgba(40, 45, 49, 0.108) 100%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._cancel ._bg11 {
    background: radial-gradient(58.03% 100% at 50% 0%,
            rgba(255, 82, 92, 0.405) 0%,
            rgba(40, 45, 49, 0.108) 100%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._cancel ._bg2,
.btn-new._cancel ._bg22 {
    background: radial-gradient(86.54% 994.53% at 13.55% 115.38%,
            rgba(254, 65, 75, 0.6) 0%,
            rgba(204, 51, 79, 0) 100%),
        linear-gradient(276.15deg,
            rgba(255, 35, 48, 0.38) -24.51%,
            rgba(91, 35, 35, 0.2) 82.05%),
        linear-gradient(315.81deg, #17191c -42.75%, #32383e 123.05%);
}

.btn-new._cancel ._bg22 {
    transform: rotate(180deg);
}

.btn-new ._bg11 {
    opacity: 0;
}

.btn-new ._bg22 {
    opacity: 0;
    transform: rotate(180deg);
}

.btn-new._disabled {
    opacity: 0.5;
}

.btn-new._hidden {
    display: none;
}

.btn-new:first-child:not(:last-child) {
    margin-right: 5px;
}

.btn-new:last-child:not(:first-child) {
    margin-left: 5px;
}

.btn-new._pressed:not(._disabled) ._bg1,
.btn-new:active:not(._disabled) ._bg1,
.btn-new:focus:not(._disabled) ._bg1,
.btn-new._pressed:not(._disabled) ._bg11,
.btn-new:active:not(._disabled) ._bg11,
.btn-new:focus:not(._disabled) ._bg11 {
    transition: none;
}

.btn-new._pressed:not(._disabled) ._bg1,
.btn-new:active:not(._disabled) ._bg1,
.btn-new:focus:not(._disabled) ._bg1 {
    opacity: 1;
    background-image: linear-gradient(135deg, #1b1e21 0%, #282c31 100%);
}

.btn-new._pressed:not(._disabled) ._bg11,
.btn-new:active:not(._disabled) ._bg11,
.btn-new:focus:not(._disabled) ._bg11 {
    opacity: 0;
}

.btn-new__inner .indicator {
    position: relative;
    z-index: 2;
    margin: 5px auto 0;
    width: 24px;
    height: 3px;
}

.btn-new__inner .indicator._cashout:after {
    background-image: linear-gradient(132.19deg,
            #c8d5e1 -160.75%,
            rgba(0, 0, 0, 0.0001) 169.75%);
}

.btn-new__inner .indicator._cashout:before {
    background: linear-gradient(136.14deg, #ee9644 -0.24%, #f9e1b2 91.03%);
    box-shadow: 4px 10px 32px rgba(63, 208, 164, 0.4),
        -6px -6px 16px rgba(0, 0, 0, 0.6);
}

.btn-new__inner .indicator._placebet:after {
    background-image: linear-gradient(136.14deg,
            #44c5ee -0.24%,
            #63e6fc 91.03%);
    box-shadow: 4px 10px 32px rgba(63, 208, 164, 0.4),
        -6px -6px 16px rgba(0, 0, 0, 0.6);
}

.btn-new__inner .indicator._placebet:before {
    box-shadow: inset 2px 2px 2px rgba(26, 32, 38, 0.4);
    background-image: linear-gradient(132.19deg,
            #c8d5e1 -160.75%,
            rgba(0, 0, 0, 0.0001) 169.75%);
}

.btn-new__inner .indicator._cancel:after {
    background-image: linear-gradient(262deg, #f9718e 100%, #f64444 0%),
        linear-gradient(to right,
            rgba(179, 179, 179, 0.45) -90%,
            rgba(0, 0, 0, 0.85) 141%);
}

.btn-new__inner .indicator._cancel:before {
    box-shadow: -1px 0 6px 0 rgba(248, 179, 134, 0.42);
    background-image: linear-gradient(to right,
            rgba(179, 179, 179, 0.45) -90%,
            rgba(0, 0, 0, 0.85) 141%);
}

.btn-new__inner .indicator:before,
.btn-new__inner .indicator:after {
    content: "";
    position: absolute;
    background-image: linear-gradient(92deg, #777f85 0%, #363b3f 100%);
}

.btn-new__inner .indicator:after {
    z-index: 2;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    border-radius: 1.5px;
}

.btn-new__inner .indicator:before {
    z-index: 1;
    top: -1px;
    left: -1px;
    right: -1px;
    bottom: -1px;
    border-radius: 2.5px;
}

@media (min-width: 1024px) {
    .btn-new:hover .text {
        color: #ffffff;
    }

    .btn-new:hover:not(:active) ._bg1 {
        opacity: 0;
    }

    .btn-new:hover:not(:active) ._bg11 {
        opacity: 1;
    }

    .btn-new:hover:not(:active) ._bg2 {
        opacity: 0;
    }

    .btn-new:hover:not(:active) ._bg22 {
        opacity: 1;
    }
}

@media (max-width: 767px) {
    .btn-new .text {
        color: #ffffff;
    }

    .btn-new ._bg1 {
        opacity: 0;
    }

    .btn-new ._bg11 {
        opacity: 1;
    }

    .btn-new ._bg2 {
        opacity: 0;
    }

    .btn-new ._bg22 {
        opacity: 1;
    }
}

.settings-input__wrapper-inner {
    display: flex;
    flex: 1px;
    padding: 1px;
    border-radius: 10px;
    background-image: linear-gradient(315deg, #16191d 0%, #252a2e 100%);
}

    {
    border-radius: 11px;
    padding: 1px;
    box-shadow: inset 2px 2px 2px 0 rgba(26, 32, 38, 0.4);
    background: linear-gradient(98deg,
            rgba(200, 213, 225, 0.25) 0%,
            rgba(0, 0, 0, 0) 100%);
    position: relative;
}

._error {
    background-image: linear-gradient(98deg, #e17671 -81%, #31141412 182%);
    margin-bottom: 40px;
}

._error .games-input__wrapper.input--warning {
    margin: 0 !important;
}

.button {
    top: 3px;
    z-index: 1;
    height: 45px;
    border-radius: 7px;
}

.button__inner {
    border-radius: 7px;
}

.button:nth-child(1) {
    left: 3px;
}

.button:nth-child(1) .button__inner {
    box-shadow: 3px 3px 4px rgba(10, 9, 9, 0.400896);
}

.button:nth-child(2) {
    left: 47px;
    height: 45px;
    top: 3px;
    background-image: linear-gradient(270deg,
            rgba(97, 50, 31, 0.37) 0%,
            rgba(39, 35, 34, 0) 100%);
    filter: drop-shadow(3px 3px 4px rgba(10, 9, 9, 0.400896));
}

.button:nth-child(3) {
    right: 47px;
    height: 45px;
    top: 3px;
    background-image: linear-gradient(270deg,
            rgba(39, 35, 34, 0) 0%,
            rgba(97, 50, 31, 0.37) 100%);
    filter: drop-shadow(3px 3px 4px rgba(10, 9, 9, 0.400896));
}

.button:nth-child(4) {
    right: 3px;
}

.button:nth-child(4) .button__inner {
    box-shadow: 3px 3px 4px rgba(10, 9, 9, 0.400896);
}

.button:nth-child(5) {
    left: 92px;
}

.button:nth-child(6) {
    right: 92px;
}

.button._golden {
    background-image: none;
    background-color: transparent;
    box-shadow: none;
}

.button._golden:not(.disabled):active .button__inner {
    background: rgba(171, 95, 59, 0.6);
    box-shadow: -1px -1px 10px rgba(139, 55, 20, 0.15);
}

.button._golden .button__inner {
    background-color: rgba(209, 116, 72, 0.1);
    background-image: none;
    height: 100%;
    justify-content: center;
    align-items: center;
    display: flex;
    font-size: 0.7rem;
    cursor: pointer;
    border: 1px solid #a35231;
    box-shadow: -1px -1px 10px rgba(141, 68, 37, 0.15);
}

.button._golden .button__text {
    color: #d26d3d;
}

.games-input__wrapper {
    border-radius: 9px;
    height: 47px;
    background: radial-gradient(39.88% 38.48% at 50% 109.2%,
            #ba6238 0%,
            rgba(12, 12, 14, 0) 100%),
        radial-gradient(39.64% 68.64% at 50% -18.72%,
            #ba6238 0%,
            rgba(12, 12, 14, 0) 100%),
        #0c0c0e;
}

.games-input__wrapper label {
    top: 10px;
    left: 50%;
    transform: translateX(-50%);
    justify-content: center;
    font-size: 10px;
    color: #d26d3d;
}

.games-input__wrapper input {
    padding-top: 25px;
    text-align: center;
    font-weight: bold;
    font-size: 17px;
    color: #d26d3d;
    background-color: #0c0c0e;
    background-image: none;
    border: none;
}

@media (min-width: 1024px) {
    .button._golden:not(:active):hover .button__inner {
        background-color: rgba(209, 116, 72, 0.2);
    }
}

.coin {
    position: relative;
    transform-style: preserve-3d;
    width: 192px;
    /* w-48 = 12rem = 192px */
    height: 192px;
}

.coin-front,
.coin-back {
    position: absolute;
    width: 100%;
    height: 100%;
    backface-visibility: hidden;
}

.coin-back {
    transform: rotateY(180deg);
}
</style>
