<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const stats = ref([
    { label: 'Calls Handled', value: '50K+' },
    { label: 'Messages Sent', value: '1M+' },
    { label: 'Uptime', value: '99.9%' },
    { label: 'Countries', value: '195' }
]);

const features = ref([
    {
        icon: '📞',
        title: 'SIP Authentication',
        description: 'Secure SIP connections with advanced authentication protocols'
    },
    {
        icon: '💬',
        title: 'SMS Forwarding',
        description: 'Intelligent message routing and forwarding capabilities'
    },
    {
        icon: '🔒',
        title: 'Webhook Security',
        description: 'Encrypted webhook endpoints with comprehensive logging'
    },
    {
        icon: '📊',
        title: 'Real-time Analytics',
        description: 'Monitor your communication metrics in real-time'
    }
]);
</script>

<template>
    <Head title="Welcome - SIP Call & SMS Platform">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    
    <!-- Main Container -->
    <div class="min-h-screen bg-black text-white overflow-x-hidden">
        <!-- Navigation Header -->
        <header class="relative z-50 w-full">
            <nav class="flex items-center justify-between px-6 py-4 lg:px-8">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center">
                        <span class="text-black font-bold text-sm">S</span>
                    </div>
                    <span class="text-xl font-bold bg-gradient-to-r from-white to-green-400 bg-clip-text text-transparent">
                        SIPCall
                    </span>
                </div>
                
                <div class="flex items-center gap-4">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-black font-medium rounded-lg transition-all duration-200 transform hover:scale-105"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="inline-flex items-center px-4 py-2 text-green-400 hover:text-white border border-green-400 hover:bg-green-400 rounded-lg transition-all duration-200"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-black font-medium rounded-lg transition-all duration-200 transform hover:scale-105"
                        >
                            Get Started
                        </Link>
                    </template>
                </div>
            </nav>
        </header>
        <!-- Hero Section -->
        <main class="relative flex-1 overflow-hidden">
            <!-- Background Grid -->
            <div class="absolute inset-0 bg-gradient-to-b from-black via-gray-900 to-black">
                <div class="absolute inset-0 bg-[linear-gradient(rgba(16,185,129,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(16,185,129,0.05)_1px,transparent_1px)] bg-[size:50px_50px]"></div>
            </div>
            
            <!-- Hero Content -->
            <div class="relative z-10 px-6 py-20 lg:px-8 lg:py-32">
                <div class="mx-auto max-w-7xl">
                    <!-- Hero Text -->
                    <div class="text-center">
                        <h1 class="text-5xl font-bold tracking-tight text-white sm:text-7xl lg:text-8xl">
                            Enterprise
                            <span class="bg-gradient-to-r from-green-400 to-green-600 bg-clip-text text-transparent">
                                SIP
                            </span>
                            Solutions
                        </h1>
                        <p class="mt-6 text-xl leading-8 text-gray-300 sm:text-2xl lg:text-3xl">
                            Secure, scalable communication infrastructure powered by Twilio
                        </p>
                        <div class="mt-10 flex items-center justify-center gap-x-6">
                            <Link
                                v-if="!$page.props.auth.user"
                                :href="route('register')"
                                class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-black bg-gradient-to-r from-green-400 to-green-600 rounded-lg transition-all duration-300 hover:from-green-500 hover:to-green-700 hover:scale-105 hover:shadow-lg hover:shadow-green-500/25"
                            >
                                <span class="relative z-10">Start Building</span>
                                <svg class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/>
                                </svg>
                            </Link>
                            <Link
                                v-else
                                :href="route('dashboard')"
                                class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-black bg-gradient-to-r from-green-400 to-green-600 rounded-lg transition-all duration-300 hover:from-green-500 hover:to-green-700 hover:scale-105 hover:shadow-lg hover:shadow-green-500/25"
                            >
                                <span class="relative z-10">Go to Dashboard</span>
                                <svg class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/>
                                </svg>
                            </Link>
                            <a
                                href="#features"
                                class="inline-flex items-center px-8 py-4 text-lg font-semibold text-green-400 border border-green-400 rounded-lg transition-all duration-300 hover:bg-green-400 hover:text-black hover:scale-105"
                            >
                                Learn More
                            </a>
                        </div>
                    </div>

                    <!-- Stats Section -->
                    <div class="mt-20 grid grid-cols-2 gap-8 lg:grid-cols-4">
                        <div 
                            v-for="(stat, index) in stats" 
                            :key="index"
                            class="text-center group"
                        >
                            <div class="text-4xl font-bold text-green-400 transition-all duration-300 group-hover:scale-110">
                                {{ stat.value }}
                            </div>
                            <div class="mt-2 text-sm text-gray-400 uppercase tracking-wider">
                                {{ stat.label }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Features Section -->
        <section id="features" class="relative py-24 bg-gradient-to-b from-black to-gray-900">
            <div class="absolute inset-0 bg-[linear-gradient(rgba(16,185,129,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(16,185,129,0.03)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
            
            <div class="relative z-10 mx-auto max-w-7xl px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">
                        Powerful Features
                    </h2>
                    <p class="mt-4 text-xl text-gray-300">
                        Everything you need for enterprise-grade communication
                    </p>
                </div>

                <div class="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div 
                        v-for="(feature, index) in features" 
                        :key="index"
                        class="group relative p-8 bg-gray-900/50 backdrop-blur-sm border border-gray-800 rounded-xl transition-all duration-300 hover:border-green-400/50 hover:bg-gray-900/80 hover:scale-105"
                    >
                        <div class="text-4xl mb-4 transition-transform duration-300 group-hover:scale-110">
                            {{ feature.icon }}
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-3">
                            {{ feature.title }}
                        </h3>
                        <p class="text-gray-400 leading-relaxed">
                            {{ feature.description }}
                        </p>
                        <div class="absolute inset-0 bg-gradient-to-r from-green-400/0 via-green-400/5 to-green-400/0 opacity-0 transition-opacity duration-300 group-hover:opacity-100 rounded-xl"></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative py-24 bg-gradient-to-b from-gray-900 to-black">
            <div class="absolute inset-0 bg-gradient-to-r from-green-400/10 via-transparent to-green-400/10"></div>
            
            <div class="relative z-10 mx-auto max-w-4xl px-6 lg:px-8 text-center">
                <h2 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">
                    Ready to Transform Your Communication?
                </h2>
                <p class="mt-6 text-xl text-gray-300">
                    Join thousands of businesses using our SIP platform for reliable, secure communications.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <Link
                        v-if="!$page.props.auth.user"
                        :href="route('register')"
                        class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-black bg-gradient-to-r from-green-400 to-green-600 rounded-lg transition-all duration-300 hover:from-green-500 hover:to-green-700 hover:scale-105 hover:shadow-lg hover:shadow-green-500/25"
                    >
                        <span class="relative z-10">Get Started Free</span>
                        <svg class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/>
                        </svg>
                    </Link>
                    <Link
                        v-else
                        :href="route('dashboard')"
                        class="group relative inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-black bg-gradient-to-r from-green-400 to-green-600 rounded-lg transition-all duration-300 hover:from-green-500 hover:to-green-700 hover:scale-105 hover:shadow-lg hover:shadow-green-500/25"
                    >
                        <span class="relative z-10">Go to Dashboard</span>
                        <svg class="ml-2 h-5 w-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5-5 5M6 12h12"/>
                        </svg>
                    </Link>
                    <a
                        href="mailto:support@sipcall.com"
                        class="inline-flex items-center px-8 py-4 text-lg font-semibold text-green-400 border border-green-400 rounded-lg transition-all duration-300 hover:bg-green-400 hover:text-black hover:scale-105"
                    >
                        Contact Sales
                    </a>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-black border-t border-gray-800">
            <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                <div class="text-center">
                    <div class="flex items-center justify-center space-x-2 mb-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center">
                            <span class="text-black font-bold text-sm">S</span>
                        </div>
                        <span class="text-xl font-bold bg-gradient-to-r from-white to-green-400 bg-clip-text text-transparent">
                            SIPCall
                        </span>
                    </div>
                    <p class="text-gray-400">
                        © 2024 SIPCall. Built with Laravel & Vue.js. Powered by Twilio.
                    </p>
                </div>
            </div>
        </footer>
               
    </div>
</template>
