<script setup>
import GameLayout from "@/Layouts/GameLayout.vue";
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/Stores/Auth.js';
import { useWalletStore } from '@/Stores/Wallet.js';

const authStore = useAuthStore();
const walletStore = useWalletStore();
setInterval(() => {
    walletStore.fetchWallet();
}, 500);

const route = useRoute();

const games = {
    fruit: '/FruitParty/gs2c/html5Game.html',
    olympus: '/GatesofOlympus1000/gs2c/html5Game.html',
    thedoghouse: '/TheDogHouse/gs2c/html5Game.html',
    sweetbonanza: '/sweetbananza',
}

const game = ref(games[route.params.game]);

onMounted(() => {
    if (!game.value) {
        console.error('Game URL not found');
    }
});

</script>

<template>
    <GameLayout>
        <div class="h-screen w-screen max-w-[900px] rounded-xl max-h-[550px]  mt-24 mx-auto">
            <iframe class="rounded-xl" :src="game" width="100%" height="100%"></iframe>
        </div>
    </GameLayout>
</template>


<style>
.game-screen {
    margin-top: 30px;
    width: 100%;
    min-height: 650px;
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
</style>
