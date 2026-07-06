<script setup lang="ts">
import { useSite } from '../composables/useSite';

const { setting } = useSite();
</script>

<template>
    <footer class="jf-footer jf-dark-bg py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6">
                    <p class="mb-0 jf-footer-text">{{ setting('site.footer.copy') }}</p>
                </div>
                <div class="col-12 col-md-6 text-md-end mt-2 mt-md-0">
                    <p class="mb-0 jf-footer-address">{{ setting('site.footer.address') }}</p>
                </div>
            </div>
        </div>
    </footer>
</template>

<style scoped>
.jf-footer {
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.jf-footer-text {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.9rem;
}

.jf-footer-address {
    color: rgba(255, 255, 255, 0.45);
    font-size: 0.8rem;
}
</style>
