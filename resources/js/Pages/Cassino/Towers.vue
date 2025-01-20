<script setup>
import { ref, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/Stores/Auth.js";
import GameLayout from "@/Layouts/GameLayout.vue";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
import HttpApi from "@/Services/HttpApi.js";
import { useTowerStore } from "@/Stores/Games/TowerGame.js";
// При использовании <script setup> не нужно явно определять компоненты
// Они автоматически доступны в шаблоне

// Состояние
const isLoading = ref(true);
const game = ref(null);
const gameUrl = ref(null);
const token = ref(null);
const gameId = ref(null);
const undermaintenance = ref(false);

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const towerStore = useTowerStore();
// Методы
const getGame = async () => {
    try {
        const response = await HttpApi.get(`games/single/${gameId.value}`);

        if (response.data?.action === "deposit") {
            router.push({ name: "profileDeposit" });
            return;
        }

        game.value = response.data.game;
        gameUrl.value = response.data.gameUrl;
        token.value = response.data.token;
        isLoading.value = false;
    } catch (error) {
        isLoading.value = false;
        undermaintenance.value = true;
        console.error("Error loading game:", error);
    }
};

const toggleFavorite = async () => {
    try {
        await HttpApi.post(`games/favorite/${game.value.id}`);
        await getGame();
    } catch (error) {
        console.error("Error toggling favorite:", error);
    }
};

const toggleLike = async () => {
    try {
        await HttpApi.post(`games/like/${game.value.id}`);
        await getGame();
    } catch (error) {
        console.error("Error toggling like:", error);
    }
};

onMounted(() => {
    towerStore.initGame();
});

function toggleGame() {
    if (towerStore.activeGame) {
        towerStore.stopGame();
    } else {
        towerStore.startGame();
    }
}

function pickTile(row, position) {
    if (!towerStore.activeGame) return
    towerStore.pick(row, position)
}
</script>

<template>
    <GameLayout>
        <div :class="{ 'w-full': modeMovie, 'lg:w-2/3': !modeMovie }"
            class="mx-auto px-2 lg:px-4 py-2 lg:py-6 relative">
            <div class="bg-gray-300/20 dark:bg-gray-700 rounded flex justify-between px-4 py-2">
                <div class="flex items-center justify-center gap-3">
                    <a href="">Games</a>
                    <i class="fa-regular fa-angle-right text-gray-500"></i>
                    <p class="text-gray-500">Tower</p>
                </div>
                <div></div>
            </div>

            <div class="game-screen">
                <div class="flex h-full flex-col mx-auto items-center gap-4" style="max-width: 400px">
                    <div class="w-full flex flex-col gap-2" :class="towerStore.activeGame ? '_active' : ''"
                        style="margin: 0 auto; min-height: 400px">


                        <div v-for="(row, rowIndex) in 8" :key="rowIndex"
                            class="grid game-tiles tower flex-shrink-0 h-full gap-2 grid-cols-4" :class="{
                                'locked': !towerStore.isRowPlayable(rowIndex),
                                '_active': towerStore.isRowPlayable(rowIndex)
                            }">
                            <div class="game-tile" v-for="position in 4" :key="position"
                                @click="pickTile(rowIndex, position - 1)"
                                :class="towerStore.getCellClass(rowIndex, position - 1)">
                                <div class="game-tile__inner-possible-win">
                                    <span v-if="rowIndex === towerStore.getCurrentRow && towerStore.activeGame">
                                        {{ towerStore.next_win }} $
                                    </span>
                                </div>
                                <div class="game-tile__inner">
                                    <div v-if="towerStore.getResult(rowIndex, position - 1) === true" class="diamond">
                                    </div>
                                    <div v-if="towerStore.getResult(rowIndex, position - 1) === false" class="bomb">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="w-full p-5 bg-gray-800 rounded-xl">
                        <div class="flex justify-between items-center">
                            <div class="flex flex-col gap-1">
                                <p class="text-sm leading-none">Your bet</p>
                                <input
                                    class="block dark:focus:border-green-500 p-2.5 w-full z-20 text-sm text-gray-900 rounded-lg input-color-primary border-none focus:outline-none dark:border-s-gray-800 dark:border-gray-800 dark:placeholder-gray-400 dark:text-white"
                                    placeholder="Enter your bet" v-model="towerStore.game_settings.bet" />
                            </div>

                        </div>
                    </div>
                    <div :class="[
                        towerStore.activeGame ? '_cancel' : '_placebet',
                        towerStore.game_settings.bet > 0 ? '' : '_disabled'
                    ]" class="btn-new w-full" @click="toggleGame()">
                        <div class="btn-bg _bg1"></div>
                        <div class="btn-bg _bg11"></div>
                        <div class="btn-new__border">
                            <div class="btn-bg _bg2"></div>
                            <div class="btn-bg _bg22"></div>
                            <div class="btn-new__inner">
                                <div class="btn-bg _bg1"></div>
                                <div class="btn-bg _bg11"></div>
                                <div class="btn-new__text">
                                    <div class="text-wrapper">
                                        <div class="text">
                                            {{ towerStore.activeGame ? "Stop Game" : "Start Game" }}
                                        </div>
                                    </div>
                                    <div :class="towerStore.activeGame ? '_cancel' : '_placebet'" class="indicator">
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
.game-screen {
    margin-top: 30px;
    width: 100%;
    border-radius: 24px;
    padding: 24px;
    min-height: 500px;
}

.game-screen .game-full {
    width: 100%;
    min-height: 650px;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
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
</style>
