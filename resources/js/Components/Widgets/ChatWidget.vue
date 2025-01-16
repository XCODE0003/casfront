<script setup>
import { useChatStore } from '@/Stores/ChatStore'
import { useAuthStore } from "@/Stores/Auth.js";
const chatStore = useChatStore();
chatStore.getChatMessages();
const authStore = useAuthStore();
const user = authStore.user;
</script>

<template>  

<div class="fixed bottom-5 right-5 flex flex-col items-end justify-end z-50">
        <div class="flex flex-col items-center justify-center">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="transform scale-95 opacity-0"
                enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0"
            >
                <div v-if="chatStore.isOpen" 
                     class="mb-2 w-96 h-[500px] bg-white dark:bg-gray-800 rounded-lg shadow-lg  flex flex-col" style="overflow: hidden;">
                    <div class="p-4 bg-gray-100 dark:bg-gray-700 border-b dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Support</h3>
                    </div>
                    
                    <div class="flex-1 p-4 overflow-y-auto">
                        <div v-for="message in chatStore.messages" :key="message.id" :class="message.user_id == user.id ? 'flex justify-end mb-3' : 'flex justify-start mb-3'">
                            <div class="bg-blue-500 text-white rounded-lg py-2 px-4 max-w-[80%]">
                                <p class="text-sm">{{ message.message }}</p>
                                <span class="text-xs text-blue-100 block mt-1">{{ message.formatted_time }}</span>
                            </div>
                        </div>

                    </div>
                    
                    <div class="p-4 border-t dark:border-gray-600">
                        <div class="flex space-x-2">
                            <input type="text" 
                                   v-model="chatStore.message"
                                   class="flex-1 px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                   placeholder="Enter your message...">
                            <button @click="chatStore.sendMessage" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </div>
        <button @click="chatStore.toggleChat" type="button" class="bg-gray-200  hover:bg-gray-400 dark:bg-gray-600 hover:dark:bg-gray-700 w-12 h-12  rounded-full">
            <i :class="chatStore.isOpen ? 'fa-solid fa-message-slash' : 'fa-solid fa-message'"></i>
        </button>
    </div>
</template>