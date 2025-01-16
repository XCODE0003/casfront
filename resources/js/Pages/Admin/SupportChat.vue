<script setup>
import { useChatStore } from "@/Stores/ChatStoreSupport.js";
import { useAuthStore } from "@/Stores/Auth.js";
const chatStore = useChatStore();
const authStore = useAuthStore();
const user = authStore.user;
chatStore.getChats();


</script>
<template>

  <section class="h-screen overflow-hidden flex items-center justify-center w-full" style="background: #edf2f7;">
    <div class="flex h-screen antialiased text-gray-800 w-full">
      <div class="flex flex-row h-full w-full overflow-x-hidden">
        <div class="flex flex-col py-8 pl-6 pr-2 w-64 bg-white flex-shrink-0">


          <div class="flex flex-col mt-8">
            <h1 class="text-2xl font-bold pb-10">
              Чаты
            </h1>
            <div 
              class="flex flex-col space-y-1 gap-1   overflow-y-auto">
              <button @click="chatStore.selectChat(chat.id)" v-for="chat in chatStore.chats" :class="chatStore.activeChat == chat.id ? 'bg-gray-100' : ''"
                class="flex flex-row items-center hover:bg-gray-100 rounded-xl p-2">
                <div class="flex items-center justify-center h-8 w-8 bg-indigo-200 rounded-full">
                  {{ chat.user.name[0] }}
                </div>
                <div class="ml-2 text-sm font-semibold">{{ chat.user.name }}</div>
              </button>

            </div>

          </div>
        </div>
        <div class="flex flex-col flex-auto h-full p-6">
          <div class="flex flex-col flex-auto flex-shrink-0 rounded-2xl bg-gray-100 h-full p-4">
            <div class="flex flex-col h-full overflow-x-auto mb-4">
              <div class="flex flex-col h-full">
                <div class="grid grid-cols-12 gap-y-2">
                  <div v-for="message in chatStore.messages" :key="message.id"
                    :class="message.user_id == user.id ? 'col-start-6 col-end-13 p-3 rounded-lg' : 'col-start-1 col-end-8 p-3 rounded-lg'">
                    <div :class="message.user_id == user.id ? 'flex items-center justify-start flex-row-reverse' : 'flex flex-row items-center'">
                      <div class="flex items-center justify-center h-10 w-10 rounded-full bg-indigo-500 flex-shrink-0">
                        {{ message.user.name[0] }}
                      </div>
                      <div 
                        :class="message.user_id == user.id ? 'relative mr-3 text-sm bg-indigo-100 py-2 px-4 shadow rounded-xl' : 'relative ml-3 text-sm bg-white py-2 px-4 shadow rounded-xl'"
                      >
                        <div>{{ message.message }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                          {{ message.formatted_time }}
                        </div>
                      </div>
                    </div>
                  </div>


                </div>
              </div>
            </div>
            <div class="flex flex-row items-center h-16 rounded-xl bg-white w-full px-4">
           
              <div class="flex-grow ml-4">
                <div class="relative w-full">
                  <input type="text"
                    placeholder="Напишите сообщение..."
                    v-model="chatStore.message"
                    class="flex w-full border rounded-xl focus:outline-none focus:border-indigo-300 pl-4 h-10" />
                  
                </div>
              </div>
              <div class="ml-4">
                <button
                  @click="chatStore.sendMessage"
                  class="flex items-center justify-center bg-indigo-500 hover:bg-indigo-600 rounded-xl text-white px-4 py-1 flex-shrink-0">
                  <span>Отправить
                  </span>
                 
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
<style scoped></style>
