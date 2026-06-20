<script setup lang="ts">
import { useSite } from '../composables/useSite';

const { setting } = useSite();
</script>

<template>
    <footer class="py-2">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <p class="mb-0">{{ setting('site.footer.copy') }}</p>
                </div>
                <div class="col-lg-6 text-lg-end">
                    <p class="mb-0">{{ setting('site.footer.address') }}</p>
                </div>
            </div>
        </div>
    </footer>
</template>
