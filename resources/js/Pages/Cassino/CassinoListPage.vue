<template>
    <BaseLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="text-center">
                <span>Carregando os jogos</span>
            </div>
        </LoadingComponent>

        <div v-if="!isLoading" class="md:w-4/6 2xl:w-4/6 mx-auto">
            <div class="px-4 py-5">
                <HeaderComponent>
                    <template #header>
                        {{ $t('List of') }} <span
                            class=" bg-blue-100 text-blue-800 text-2xl font-semibold me-2 px-2.5 py-0.5 rounded dark:bg-blue-200 dark:text-blue-800 ms-2">{{
                                $t('Games') }}</span>
                    </template>

                    <p class="text-2xl flex items-center justify-center">{{ $t('Total') }} <strong>({{ games?.total ?? 0
                            }})</strong></p>
                </HeaderComponent>

                <form class="mb-5 mt-5">
                    <label for="search" class="mb-2 text-sm font-medium text-gray-900 sr-only dark:text-white">
                        {{ $t('Search') }}
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z" />
                            </svg>
                        </div>
                        <input v-model="searchTerm" @input="searchGames" type="search" id="search"
                            class="block w-full p-4 pl-10 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700/20 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            :placeholder="$t('Search')" required>
                    </div>
                </form>
                <div v-if="category == 'slots'" class="mb-5">
                    <div class="mt-5 flex flex-col gap-3">
                        <h1 class="text-xl font-bold">Popular games {{ category }}</h1>
                        <div class="grid grid-cols-4 justify-center items-center gap-3 ">
                            <RouterLink to="/progmatik/game/fruit"><img
                                    class="rounded-lg transition-all duration-300 hover:scale-105 cursor-pointer active:scale-95"
                                    :src="`/assets/images/games/fruit_party_2.webp`" alt="" srcset=""></RouterLink>
                            <RouterLink to="/progmatik/game/olympus"><img
                                    class="rounded-lg transition-all duration-300 hover:scale-105 cursor-pointer active:scale-95"
                                    :src="`/assets/images/games/gates_of_olympus.webp`" alt="" srcset=""></RouterLink>
                            <RouterLink to="/progmatik/game/thedoghouse"><img
                                    class="rounded-lg transition-all duration-300 hover:scale-105 cursor-pointer active:scale-95"
                                    :src="`/assets/images/games/the_dog_house.webp`" alt="" srcset=""></RouterLink>
                            <RouterLink to="/progmatik/game/sweetbonanza"><img
                                    class="rounded-lg transition-all duration-300 hover:scale-105 cursor-pointer active:scale-95"
                                    :src="`/assets/images/games/sweet_bonanza_1000.webp`" alt="" srcset=""></RouterLink>
                        </div>
                        <div class="grid grid-cols-3 justify-center items-center gap-3 ">
                            <RouterLink to="/games/coin"><img
                                    class="rounded-lg transition-all duration-300 hover:scale-105 cursor-pointer active:scale-95"
                                    :src="`/assets/images/games/сoin_flip.png`" alt="" srcset=""></RouterLink>
                            <RouterLink to="/games/mines"><img
                                    class="rounded-lg transition-all duration-300 hover:scale-105 cursor-pointer active:scale-95"
                                    :src="`/assets/images/games/mines.png`" alt="" srcset=""></RouterLink>
                            <RouterLink to="/games/towers"><img
                                    class="rounded-lg transition-all duration-300 hover:scale-105 cursor-pointer active:scale-95"
                                    :src="`/assets/images/games/tower.png`" alt="" srcset=""></RouterLink>
                        </div>
                    </div>
                </div>
                <div v-if="games && games?.total > 0">
                    <div class="relative w-full">
                        <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-5">
                            <CassinoGameCard v-for="(game, index) in games.data" :index="index" :title="game.game_name"
                                :cover="game.cover" :gamecode="game.game_code" :type="game.distribution" :game="game" />
                        </div>
                    </div>

                    <div class="mt-[50px] relative">
                        <CustomPagination :data="games" @pagination-change-page="getGameData" />
                    </div>
                </div>
                <div v-else class="empty-data flex flex-col justify-center items-center text-center my-36">
                    <img :src="`/assets/images/no-results.png`" alt="" class="w-auto h-auto max-h-[300px]">
                    <h3>{{ $t('No data to show') }}</h3>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>


<script>

import BaseLayout from "@/Layouts/BaseLayout.vue";
import HttpApi from "@/Services/HttpApi.js";
import CassinoGameCard from "@/Pages/Cassino/Components/CassinoGameCard.vue";
import CustomPagination from "@/Components/UI/CustomPagination.vue";
import { useRoute, useRouter } from "vue-router";
import { computed, ref, watch } from "vue";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
import HeaderComponent from "@/Components/UI/HeaderComponent.vue";

export default {
    props: [],
    components: { HeaderComponent, LoadingComponent, CustomPagination, CassinoGameCard, BaseLayout },
    data() {
        return {
            isLoading: false,
            games: null,
            searchTerm: '',
            provider: null,
            category: null,
        }
    },
    setup(props) {
        const route = useRoute();

        watch(() => route.params.provider, (newProvider, oldProvider) => {

        });

        return {
            route
        };
    },
    computed: {},
    mounted() { },
    beforeUnmount() { },
    methods: {
        searchGames: async function () {
            const _this = this;
            if (_this.searchTerm.length > 2) {
                await _this.getGameData(1, false);
            } else {
                await _this.getGameData(1, false);
            }
        },
        getGameData: async function (page = 1, loading = true) {
            const _this = this;
            _this.isLoading = loading;
            const provider = _this.route.params.provider;
            const category = _this.route.params.category;

            this.provider = provider;
            this.category = category;

            await HttpApi.get('/casinos/games?page=' + page + '&searchTerm=' + _this.searchTerm + '&category=' + _this.category + '&provider=' + _this.provider)
                .then(response => {
                    _this.games = response.data.games;
                    _this.isLoading = false;
                })
                .catch(error => {
                    _this.isLoading = false;
                });
        },
    },
    async created() {

        await this.getGameData(1, false);
    },
    watch: {
        'route.params.provider'(newGame, oldGame) {
            this.getGameData(1, true);
        },
        'route.params.category'(newGame, oldGame) {
            this.getGameData(1, true);
        }
    },
};
</script>

<style scoped></style>
