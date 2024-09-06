<template>
    <div class="c-emoji-keyboard">
        <div
            v-for="emoji in emojis"
            :key="emoji"
            @click="$emit('selectEmoji', emoji)"
        >
            {{ emoji }}
        </div>
    </div>
</template>

<script>

export default {
    name: 'EmojiKeyboard',
    components: {

    },
    mixins: [
    ],
    props: {
    },
    emits: [
        'selectEmoji',
    ],
    data() {
        return {
            emojiRange: [
                [0x1F601, 0x1F64F], [0x1F680, 0x1F6C0], [0x1F300, 0x1F5FF],
            ],
        };
    },
    computed: {
        emojis() {
            const emojis = [];
            for (let i = 0; i < this.emojiRange.length; i += 1) {
                const range = this.emojiRange[i];
                for (let x = range[0]; x < range[1]; x += 1) {
                    const p1 = Math.floor((x - 0x10000) / 0x400) + 0xD800;
                    const p2 = ((x - 0x10000) % 0x400) + 0xDC00;
                    emojis.push(String.fromCharCode(p1, p2));
                }
            }
            return emojis;
        },
    },
    methods: {
    },
    watch: {
    },
    created() {

    },
};
</script>
