<template>
    <div class="slot" :class="available === true ? 'slot-available' : 'slot-standard'">
        {{ timeSlot }}
        <input type="checkbox" @change="toggle" :checked="available === true">
        <button class="btn-close" v-if="isHead" @click="deleteSlotAndNext"></button>
    </div>
</template>

<script>
export default {
    name: 'time-slot',
    props: {
        timeSlot: { type: String },
        available: { type: Boolean },
        isHead: { type: Boolean},
    },
    methods: {
        toggle: function() {
            this.$emit('update', this.timeSlot, !this.available);
        },
        deleteSlotAndNext: function() {
            this.$emit('delete-time-slot-and-next', this.timeSlot);
        },
    },
}
</script>

<style>
    .slot {
        min-height: 30px;
        max-width: 200px;
        border: 0px;
    }

    .slot-available {
        background-color: green;
    }

    .slot-standard {
        background-color: red;
    }
</style>