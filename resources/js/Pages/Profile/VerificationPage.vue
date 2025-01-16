<script setup>
import { ref, onMounted } from "vue";
import { useToast } from "vue-toastification";
import BaseLayout from "@/Layouts/BaseLayout.vue";
import WalletSideMenu from "@/Pages/Profile/Components/WalletSideMenu.vue";
import HttpApi from "@/Services/HttpApi.js";

const toast = useToast();
const verification = ref(null);
const isLoading = ref(false);
const hasCheckedVerification = ref(false);

const form = ref({
    first_name: '',
    last_name: '',
    country: '',
    date_of_birth: '',
});

const countries = [
    'United States', 'United Kingdom', 'Russia', 'Germany', 'France', 
    'Italy', 'Spain', 'Ukraine', 'Poland', 'Romania'
];

onMounted(async () => {
    await checkVerification();
});

async function checkVerification() {
    try {
        const response = await HttpApi.get('verification');
        verification.value = response.data.verification;
        hasCheckedVerification.value = true;
    } catch (error) {
        toast.error('Error checking verification status');
        hasCheckedVerification.value = true;
    }
}

async function submitVerification() {
    if (isLoading.value) return;
    
    isLoading.value = true;
    try {
        const response = await HttpApi.post('start-verification', form.value);
        toast.success(response.data.message);
        await checkVerification();
    } catch (error) {
        if (error.response?.data?.message) {
            toast.error(error.response.data.message);
        } else {
            toast.error('Error submitting verification');
        }
    } finally {
        isLoading.value = false;
    }
}
</script>

<template>
    <BaseLayout>
        <div class="md:w-4/6 2xl:w-4/6 mx-auto p-4 mt-20">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="col-span-1 hidden md:block">
                    <WalletSideMenu />
                </div>
                <div class="col-span-2">
                    <div v-if="!hasCheckedVerification" class="text-center p-4">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900 dark:border-white mx-auto"></div>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">Loading verification status...</p>
                    </div>

                    <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 class="text-2xl font-bold mb-6 dark:text-white">Verification</h2>

                        <div v-if="verification" class="mb-6">
                            <div class="p-4 rounded-lg" 
                                 :class="{
                                    'bg-yellow-100 text-yellow-700': verification.verification_status === 'pending',
                                    'bg-green-100 text-green-700': verification.verification_status === 'approved',
                                    'bg-red-100 text-red-700': verification.verification_status === 'rejected'
                                 }">
                                <div class="flex items-center">
                                    <div v-if="verification.verification_status === 'pending'" class="mr-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div v-else-if="verification.verification_status === 'approved'" class="mr-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                    <div v-else-if="verification.verification_status === 'rejected'" class="mr-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="font-medium text-lg">
                                            {{ verification.verification_status === 'pending' ? 'Verification in Progress' :
                                               verification.verification_status === 'approved' ? 'Account Verified' :
                                               'Verification Rejected' }}
                                        </p>
                                        <p class="text-sm mt-1">
                                            <template v-if="verification.verification_status === 'pending'">
                                                Your verification is being processed. Please wait for approval.
                                            </template>
                                            <template v-else-if="verification.verification_status === 'approved'">
                                                Your account has been successfully verified. You now have full access to all features.
                                            </template>
                                            <template v-else-if="verification.verification_status === 'rejected'">
                                                Your verification was rejected. Please contact support for more information.
                                            </template>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div v-if="verification.verification_status === 'approved'" class="mt-6 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                <h3 class="text-lg font-medium mb-4 dark:text-white">Verification Details</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Full Name</p>
                                        <p class="font-medium dark:text-white">{{ verification.first_name }} {{ verification.last_name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Country</p>
                                        <p class="font-medium dark:text-white">{{ verification.country }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form v-else @submit.prevent="submitVerification" class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        First Name
                                    </label>
                                    <input 
                                        v-model="form.first_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Last Name
                                    </label>
                                    <input 
                                        v-model="form.last_name"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                    />
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Country
                                </label>
                                <select 
                                    v-model="form.country"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                >
                                    <option value="">Select country</option>
                                    <option v-for="country in countries" :key="country" :value="country">
                                        {{ country }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Date of Birth
                                </label>
                                <input 
                                    v-model="form.date_of_birth"
                                    type="date"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                />
                            </div>

                            <div class="flex justify-end">
                                <button 
                                    type="submit"
                                    :disabled="isLoading"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50"
                                >
                                    <span v-if="isLoading">Processing...</span>
                                    <span v-else>Submit Verification</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BaseLayout>
</template>

<style scoped>
.input-error {
    @apply border-red-500 focus:border-red-500 focus:ring-red-500;
}
</style>
