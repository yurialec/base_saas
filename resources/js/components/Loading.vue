<!-- src/components/LoadingOverlay.vue -->

<template>
    <div v-if="loading" ref="overlay" class="loading-overlay d-flex flex-column justify-content-center align-items-center text-white"
        :class="{ 'loading-fullscreen': fullscreen }">
        <div class="spinner-border text-primary" role="status" :style="{ width: size, height: size }">
            <span class="visually-hidden"></span>
        </div>
    </div>
</template>

<script setup>
import { nextTick, onBeforeUnmount, ref, watch } from 'vue'

const props = defineProps({
    loading: {
        type: Boolean,
        default: false
    },
    fullscreen: {
        type: Boolean,
        default: false
    },
    size: {
        type: String,
        default: '3rem'
    }
})

const overlay = ref(null)
let container = null
let previousInlinePosition = ''
let changedContainerPosition = false

function restoreContainerPosition() {
    if (container && changedContainerPosition) {
        container.style.position = previousInlinePosition
    }

    container = null
    previousInlinePosition = ''
    changedContainerPosition = false
}

async function configureContainer() {
    await nextTick()
    restoreContainerPosition()

    if (props.fullscreen || !overlay.value) {
        return
    }

    container = overlay.value.parentElement

    if (container && window.getComputedStyle(container).position === 'static') {
        previousInlinePosition = container.style.position
        container.style.position = 'relative'
        changedContainerPosition = true
    }
}

watch([() => props.loading, () => props.fullscreen], configureContainer, {
    immediate: true,
    flush: 'post'
})

onBeforeUnmount(restoreContainerPosition)
</script>

<style scoped>
.loading-overlay {
    position: absolute;
    inset: 0;
    z-index: 10;
    transition: opacity 0.5s ease;
    backdrop-filter: blur(2px);
    border-radius: inherit;
}

.loading-fullscreen {
    position: fixed;
    z-index: 9999;
}
</style>
