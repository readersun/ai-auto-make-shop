<template>
    <div class="min-h-screen bg-gray-50">
        <nav class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="/" class="text-xl font-bold text-gray-900">
                            AI Auto Shop
                        </a>
                    </div>
                    <div class="flex items-center space-x-4">
                        <slot name="nav" />
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <slot />
        </main>

        <footer class="bg-gray-800 text-white py-8 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <p>&copy; 2026 AI Auto Shop. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<script setup>
// Layout component
</script>
