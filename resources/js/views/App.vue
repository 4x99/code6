<template>
    <div>
        <a-button type="primary">Primary</a-button>
    </div>
</template>

<script>
    import {Button} from 'ant-design-vue';
    import {ConfigProvider} from 'ant-design-vue';

    export default {
        components: {
            AButton: Button,
            AConfigProvider: ConfigProvider
        }
    }
</script>
